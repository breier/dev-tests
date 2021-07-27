# .bashrc

# Source global definitions
if [ -f /etc/bashrc ]; then
	. /etc/bashrc
fi

# User specific environment
if ! [[ "$PATH" =~ "$HOME/.local/bin:" ]]
then
    PATH="$HOME/.local/bin:$PATH"
fi

if ! [[ "$PATH" =~ "$HOME/Apps/bin:" ]]
then
    PATH="$HOME/Apps/bin:$PATH"
fi

export PATH

# Uncomment the following line if you don't like systemctl's auto-paging feature:
# export SYSTEMD_PAGER=

# User specific aliases and functions

unalias ll 2> /dev/null
unalias ls 2> /dev/null

alias c='clear'
alias ll='ls -l --color=auto --group-directories-first'
alias ls='ls --color=auto --group-directories-first'
alias psf='ps fo user,pid,pcpu,pmem,start,time,command -C'

if [ $TILIX_ID ] || [ $VTE_VERSION ]; then
        source /etc/profile.d/vte.sh
fi

if [ -f /usr/share/git-core/contrib/completion/git-prompt.sh ]; then
	. /usr/share/git-core/contrib/completion/git-prompt.sh
	export GIT_PS1_SHOWDIRTYSTATE=1
	GIT_STATUS='\[\033[01;33m\]$(__git_ps1 " (%s)")\[\033[00m\]'
fi

USER_COLOR=$(( 31 + $(( ! 0 ^ ! ${UID} )) ))
USER_NAME="\\[\\033[01;${USER_COLOR}m\\]\\u\\[\\033[00m\\]"
DIR_NAME="\\[\\033[01;34m\\]\\W\\[\\033[00m\\]"

export PS1="[${USER_NAME}@\\h ${DIR_NAME}]${GIT_STATUS}\\$ "
