# Sokrates RSb Parser
Der RSb Parser ist ein in PHP geschriebenes Tool und dient dazu Behördenbriefe der österreichischen Post im Schulalltag zu bedrucken. Es analysiert die von [Sokrates](https://www.sokrates-bund.at/) generierte Frühwarnungen und erzeugt ein PDF um die *RSb Klebeettiketten* entsprechend zu bedrucken.

The RSb Parser is a tool written in PHP to be used in austrian schools. It analyzes letters (early warnings to parents) generated by [Sokrates](https://www.sokrates-bund.at/) and creates a PDF so you can print on [RSb Klebeettiketten](https://www.post.at/g/c/behoerdenbrief-rsa-rsb-geschaeftlich). 

## Installation
### Debian
        sudo apt update
        sudo apt install wget php-cgi php-cli php-zip unzip poppler-utils php-mbstring php-gd


### HTML/PHP dependencies
  - [Composer](https://getcomposer.org/) 
  - [pdftotext](https://github.com/spatie/pdf-to-text)
  - [mPDF](https://mpdf.github.io/)
  - [W3.CSS](https://www.w3schools.com/w3css/w3css_downloads.asp)
  
        wget -O composer-setup.php https://getcomposer.org/installer
        mkdir -p css
        wget -O css/w3.css https://www.w3schools.com/w3css/4/w3.css
        php ./composer-setup.php --install-dir=.
        php ./composer.phar require spatie/pdf-to-text
        php ./composer.phar require mpdf/mpdf


## Usage
Configure the parser in the `config.php` file, and follow the instructions after loading the `index.html`.
