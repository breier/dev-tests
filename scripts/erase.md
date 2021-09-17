# Erase your traces
Delete last command from shell history.

## BASH
```bash
for i in `history | tail -n 2 | awk '{print $1}' | sort -r`; do history -d $i; done
```

## ZSH alternative (not so good)
```zsh
sed -i "$(expr $(wc -l ${HISTFILE} | awk '{print $1}') - 2),\$d" ${HISTFILE}
```
_*You have to logout and login again for it to take effect_