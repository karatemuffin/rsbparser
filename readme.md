# RSb-Parser Installation
## Debian packages
        sudo apt update
        sudo apt install wget php-cgi php-cli php-zip unzip poppler-utils php-mbstring php-gd


## PHP dependencies
  - [Composer](https://getcomposer.org/) 
  - [pdftotext](https://github.com/spatie/pdf-to-text)
  - [mPDF](https://mpdf.github.io/)

        wget -O composer-setup.php https://getcomposer.org/installer
        php ./composer-setup.php --install-dir=.
        php ./composer.phar require spatie/pdf-to-text
        php ./composer.phar require mpdf/mpdf

# Usage
Configure the parser in the `config.php` file, and follow the instructions after loading the `index.html`.
