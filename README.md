# confs
Home Linux Environment Configuration Files

## Commands used to build Nano RC:
```
echo -e "set autoindent\nset smooth\nset undo\n" > ~/.nanorc
ls -1 /usr/share/nano/*.nanorc >> ~/.nanorc
sed -i 's:/usr/:include /usr/:' ~/.nanorc
```

## Commands used to backup and restore Tilix settings:
```
dconf dump /com/gexperts/Tilix/ > tilix.dconf
dconf load /com/gexperts/Tilix/ < tilix.dconf
```

## VS Code Snippets Folder
Should be placed at `~/.config/Code/User/snippets/php.json`
