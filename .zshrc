if [[ -r "${XDG_CACHE_HOME:-$HOME/.cache}/p10k-instant-prompt-${(%):-%n}.zsh" ]]; then
  source "${XDG_CACHE_HOME:-$HOME/.cache}/p10k-instant-prompt-${(%):-%n}.zsh"
fi

if ! [[ "$PATH" =~ "$HOME/.local/bin:" ]]; then
  export PATH="$HOME/.local/bin:$PATH"
fi

export ZSH="/home/breier/.oh-my-zsh"
ZSH_THEME="powerlevel10k/powerlevel10k"
plugins=(aws chucknorris colorize copyfile docker history-substring-search laravel sudo zsh-autosuggestions zsh-syntax-highlighting)

source $ZSH/oh-my-zsh.sh

unalias ls 2> /dev/null

alias c='clear'
alias ls='ls --color=tty --group-directories-first'
alias psf='ps fo user,pid,pcpu,pmem,start,time,command -C'

[[ ! -f ~/.p10k.zsh ]] || source ~/.p10k.zsh
