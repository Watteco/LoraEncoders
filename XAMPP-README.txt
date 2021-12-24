Après avoir décompressé le présent répertoire:

CODEC PYTHON/CONSTRUCT:
-----------------------
. install python 3.7 or more : https://www.python.org/
. install dicctoxml: From current distribution or download
	- go to dicttoxml-1.7.4/
	- command to execute: py setup.py install
	
. install construct: From current distribution or download
	- go to construct-2.8.12/
	- command to execute: py setup.py install
	
Verify python path or set it in "configration.json" ...


SERVEUR WEB: 
------------
install xampp : https://www.apachefriends.org/fr/index.html

in httpd.conf modify "DocumentRoot": 

DocumentRoot "C:\xxxxx\v3.x\tools\wtc-tools\usr\share\wtc-tools\wtc-codec_ihm_web\www"
<Directory "C:\xxxxx\v3.x\tools\wtc-tools\usr\share\wtc-tools\wtc-codec_ihm_web\www">
    Options Indexes FollowSymLinks Includes ExecCGI
    AllowOverride All
    Require all granted
</Directory>

Use any WEB browser with URL: localhost/LoraEncoder


REMARQUE:
---------
It is also possible to configure a "virtual host" on Apache from XAMP: 
Modify this configurtion file: C:\xampp\apache\conf\extra\httpd-vhosts.conf

<VirtualHost *:80>
    DocumentRoot "C:\xxxxx\v3.x\tools\wtc-tools\usr\share\wtc-tools\wtc-codec_ihm_web\www"
    ServerName nke.localhost
    SetEnv NS_ENV variable_value
</VirtualHost>

In tha case the URL will be: nke.localhost/LoraEncoder

#Some other virtal wen servers can be installed
<VirtualHost *:80>
    DocumentRoot "yyyyy"
    ServerName zzz.localhost
</VirtualHost>