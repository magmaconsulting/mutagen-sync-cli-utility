# Mutagen Sync sessions cli utility

This php utility helps to manage Mutagen Sync sessions and allows to run some commands on multiple sessions.

## Install phar command 

Download file pahr [bin/msu](https://github.com/magmaconsulting/mutagen-sync-cli-utility/raw/main/bin/msu) and save into ~/bin/msu or in a directory in your execution path.

On linux/macos:

```
wget "https://raw.github.com/magmaconsulting/mutagen-sync-cli-utility/main/bin/msu" -O ~/bin/msu
chmod +x ~/bin/msu 
```

## Usage examples

List all sessions:

```shell
msu  
```

List all active sessions:
```shell
msu -a
```
![](doc/msu-a.png)

List all sessions containing "tos":

```shell
msu tos
```
![](doc/msu-tos.png)

Pause all sessions containing "tos":

```shell
msu tos --pause
```
![](doc/msu-tos-pause.png)

Pause all sessions without asking:

```shell
msu --pause -n 
```

Resume session "tos-co1d":

```shell
msu tos-co1d --resume
```


Show help:

```shell
msu --help
```

## Generate a new phar

Create new phar:

```
cd src/
php -d phar.readonly=0 create-phar.php
```

Check: 

```
../bin/msu --help
```

Install to your $HOME/bin: 

```
php install.phar.php
```