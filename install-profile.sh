#!/bin/bash
#
# Script to install my bash profile

# Don't run as root
if [[ $(whoami) == "root" ]]; then
    echo "Don't run this as root, but have sudo ready!"
    exit 1
fi

# Set current folder
SCRIPT_PATH="$(cd "$(dirname "$0")"; pwd -P)"

# Set Local Bin Path
if [[ "$(basename "${SCRIPT_PATH}")" == "bin" ]]; then
    BIN_PATH="${SCRIPT_PATH}"
elif [[ -d "${SCRIPT_PATH}/../bin" ]]; then
    BIN_PATH="$(realpath "${SCRIPT_PATH}/../bin")"
elif [[ -d "${SCRIPT_PATH}/../../bin" ]]; then
    BIN_PATH="$(realpath "${SCRIPT_PATH}/../../bin")"
else
    BIN_PATH="$(realpath "${HOME}/.local/bin")"
fi

# Clone my repo at tmp folder
if [[ ! -x "$(which git)" ]]; then
    echo "installing git (you gonna need it)..."
    sudo yum -q -y install git
fi
TMP_BASE_DIR=$(mktemp -d)
git clone -q -b confs https://github.com/breier/dev-tests.git ${TMP_BASE_DIR}/confs

# Installing bash profile
cp -rvf ${TMP_BASE_DIR}/confs/.bashrc ~/
sed -i "s:\$HOME/Apps/bin:${BIN_PATH}:" ~/.bashrc
sudo cp -rvf ~/.bashrc /root/

# Installing nano profile
if [[ ! -x "$(which nano)" ]]; then
    echo "installing nano (you like it)..."
    sudo yum -q -y install nano
fi
echo -e "set autoindent\nset smooth\nset undo\n" > ~/.nanorc
ls -1 /usr/share/nano/*.nanorc >> ~/.nanorc
sed -i 's:/usr/:include /usr/:' ~/.nanorc
sudo cp -rvf ~/.nanorc /root/

# Collecting garbage
rm -rf ${TMP_BASE_DIR}
