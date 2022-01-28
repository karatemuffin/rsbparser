# RSb-Parser Installation
## Debian packages
        sudo apt update
        sudo apt install wget php-cgi php-cli php-zip unzip poppler-utils at texlive-fonts-recommended texlive-fonts-extra texlive-latex-recommended texlive-lang-german texlive-latex-extra texlive-latex-extra-utils 
## LaTeX dependencies
        export HOME='/var/www'
        mktexpk --destdir /usr/share/texmf-texlive/fonts/pk/ljfour/jknappen/ec/ --mfmode / --bdpi 600 --mag 1+0/600 --dpi 600 ecrm1000
	
## Permissions
	Make sure that the user running the php script has the permission to run the 'at' command
	
        sudo vim /etc/at.deny
	
	Also make sure that the user running the php script has access to an shell 
	
	sudo vim /etc/passwd
	

## PHP dependencies
  - [Composer](https://getcomposer.org/) 
  - [pdftotext](https://github.com/spatie/pdf-to-text)
 
        wget -O composer-setup.php https://getcomposer.org/installer
        
If used globally 
      
        sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
        sudo php composer require spatie/pdf-to-text
        
if used only in this project

        php composer-setup.php --install-dir=.
        php composer.phar require spatie/pdf-to-text
        

# Usage
Open the page and follow the help instructions
