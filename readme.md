#Parser Installation
## Debian Pakete updaten
        sudo apt update
        sudo apt install wget php-cli php-zip unzip poppler-utils

## PHP Dependencies
  - [Composer](https://getcomposer.org/) 
  - [pdftotext](https://github.com/spatie/pdf-to-text)
 
        wget -O composer-setup.php https://getcomposer.org/installer
        
If used globally 
        sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
        sudo php composer require spatie/pdf-to-text
        
if used only in this project
        php composer-setup.php --install-dir=.
        php composer.phar require spatie/pdf-to-text
        
