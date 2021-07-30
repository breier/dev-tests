#!/bin/bash
#
# Script to install my bash profile and all customizations

# Don't run as root
if [[ $(whoami) == "root" ]]; then
    echo "Don't run this as root, but have sudo ready!"
    exit 1
fi

# Setting package manager
which dnf > /dev/null 2>&1
if [[ $? == 0 ]]; then
    PKG="dnf"
else
    PKG="apt"
fi

# Set current folder
SCRIPT_PATH="$(cd "$(dirname "$0")"; pwd -P)"
BIN_PATH="${HOME}/.local/bin"

# Installing bash profile
cp -rvf ${SCRIPT_PATH}/../.bashrc ~/
echo "Copying bash profile to root as well..."
sudo cp -rvf ~/.bashrc /root/

# Installing git if missing
which git > /dev/null 2>&1
if [[ $? != 0 ]]; then
    sudo ${PKG} install git
fi

# Installing git completion if missing
if [ ! -f /usr/share/git-core/contrib/completion/git-prompt.sh ]; then
    sudo mkdir -p /usr/share/git-core/contrib/completion
    sudo curl https://raw.githubusercontent.com/git/git/master/contrib/completion/git-prompt.sh \
        -o /usr/share/git-core/contrib/completion/git-prompt.sh
fi

# Installing git config
read -p "GIT: What's your full name? " GIT_NAME
read -p "GIT: What's your e-mail? " GIT_MAIL
cp -rvf ${SCRIPT_PATH}/../.gitconfig ~/
sed -i -e "s/GIT_NAME/${GIT_NAME}/" -e "s/GIT_MAIL/${GIT_MAIL}/" ~/.gitconfig

# Installing nano profile
which nano > /dev/null 2>&1
if [[ $? != 0 ]]; then
    sudo ${PKG} install nano
fi
echo -e "set autoindent\nset smooth\n#set undo\n" > ~/.nanorc
ls -1 /usr/share/nano/*.nanorc >> ~/.nanorc
sed -i 's:/usr/:include /usr/:' ~/.nanorc
sudo cp -rvf ~/.nanorc /root/

# Installing custom scripts to local bin
mkdir -p ${BIN_PATH}
install -v -T -D -m 755 "${SCRIPT_PATH}/git-all" "${BIN_PATH}/git-all"
install -v -T -D -m 755 "${SCRIPT_PATH}/composer-all" "${BIN_PATH}/composer-all"

read -p "Do you want to replace \`vi\` with \`nano\` [Y,n]? " REPLY
if [[ ${REPLY} =~ [Nn] ]]; then
    echo OK
else
    install -v -T -D -m 755 "${SCRIPT_PATH}/vi" "${BIN_PATH}/vi"
    if [[ ${PKG} == "apt" ]]; then
        sudo apt remove vim && sudo apt autoremove
    else
        sudo dnf remove vi
    fi
fi

which code > /dev/null 2>&1
if [[ $? == 0 ]]; then
    read -p "Do you want to install VScode Extensions [Y,n]? " REPLY
    if [[ ${REPLY} =~ [Nn] ]]; then
        echo OK
    else
        for EXT_NAME in $(cat ${SCRIPT_PATH}/../.vscode/extensions.txt | cut -f2 -d'>' | awk '{print $1}'); do
            code --install-extension ${EXT_NAME}
        done

        if [[ ${PKG} == "apt" ]]; then
            sudo apt update
            sudo apt install php php-codesniffer php-mysql php-xdebug composer
            composer global require friendsofphp/php-cs-fixer composer/composer
            (cd ~/.local/bin && ln -s ~/.config/composer/vendor/bin/php-cs-fixer)
            (cd ~/.local/bin && ln -s ~/.config/composer/vendor/bin/composer)
            sudo apt remove composer && sudo apt autoremove
        else
            echo dnf install stuff
        fi
    fi
fi
