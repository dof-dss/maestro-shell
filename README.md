# Maestro
Maestro is a command line tool to manage multi-site projects.

## Requirements
- PHP 8.1+ CLI
- Composer 2.1+

### PHP

To see which version of PHP you have installed, from the Mac shell run: 
```shell
php --version
```
If you don't have PHP installed or are using an older version I would 
recommend using Brew (https://brew.sh)  
With Brew installed run:
```shell
brew install php
```

### Composer

To see which version of Composer you have installed, from the Mac shell run:
```shell
composer --version
```
If you don't have Composer installed, using brew run:
```shell
brew install composer
```
If you do have Composer installed run: 
```shell
composer self-update
```

## Installing

Add the Maestro shell and hosting packages to your project dev dependencies

```shell
composer require --dev dof-dss/maestro-shell dof-dss/maestro-hosting 
```

To allow use of the Maestro command from the root directory of your project 
without having to directly reference the Maestro executable 
(e.g. vendor/bin/maestro) I recommend adding the vendor/bin directory to your 
shell $PATH.  
As an example I'm using zsh which is the default shell for MacOS. 

Edit .zshrc in your home directory and add the following line:

```shell
# Maestro Shell (allow vendor bin execution from project root)
export PATH="vendor/bin:${COMPOSER_HOME}/vendor/bin:${PATH}"
```

Once saved you will need to run 
```shell
source ~/.zshrc
```

## Drupal composer file requirements

You must ensure the following script is included in the project composer file
when using Maestro Shell.

```json
"scripts": {
        "post-package-update": [
            "Maestro\\Shell\\Events\\ComposerEventListener::postPackageUpdate"
        ]
    }
```

