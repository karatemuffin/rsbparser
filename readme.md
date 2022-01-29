# RSb-Parser Installation
## Debian packages
        sudo apt update
        sudo apt install wget php-cgi php-cli php-zip unzip poppler-utils php-mbstring


## PHP dependencies
  - [Composer](https://getcomposer.org/) 
  - [pdftotext](https://github.com/spatie/pdf-to-text)
  - [tPDF](http://www.fpdf.org/en/script/script92.php)

        wget -O composer-setup.php https://getcomposer.org/installer
        wget -O tpdf.zip http://www.fpdf.org/en/script/dl.php?id=92&f=zip
        
If used globally 
      
        sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
        sudo php composer require spatie/pdf-to-text
        
if used only in this project

        php composer-setup.php --install-dir=.
        php composer.phar require spatie/pdf-to-text

finally extract tPDF (unicode version of fPDF)

        unzip tpdf.zip

# Usage
Open the page and follow the help instructions
