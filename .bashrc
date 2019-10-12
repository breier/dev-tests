# .bashrc

# Source global definitions
if [ -f /etc/bashrc ]; then
	. /etc/bashrc
fi

# User specific environment
if ! [[ "$PATH" =~ "$HOME/.local/bin:$HOME/Apps/bin:" ]]
then
    PATH="$HOME/.local/bin:$HOME/Apps/bin:$PATH"
fi
export PATH

# Uncomment the following line if you don't like systemctl's auto-paging feature:
# export SYSTEMD_PAGER=

# User specific aliases and functions

#unalias c
unalias ll
unalias ls

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
	export PS1='[\u@\h \W]$(__git_ps1 " (%s)")\$ '
fi
