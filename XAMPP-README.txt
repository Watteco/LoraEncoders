

[1] PYTHON/CONSTRUCT ZCL STANDARD CODEC
=======================================
. install python 3.7 or more (tested with 3.11.3) : https://www.python.org/
  Preferably set the path to Python.exe in your systeme path,
  or define precisely the python path in the "install.json" file from [3]
  
. Install locally on the webserver the distribution of decoder from
  git repository https://github.com/Watteco/Codec-Report-Standard-Python

. install dicctoxml: From current distribution or download
	- go to dicttoxml-1.7.4/
	- command to execute: py setup.py install

. install construct: From Codec local directory
	- go to construct-2.8.12/
	- command to execute: py setup.py install
	

[2] PYTHON ZCL BATCH DECODER
============================

. Install locally on the webserver the distribution of decoder from
  git repository https://github.com/Watteco/Codec-Report-Batch-Python	


[3] PYTHON ZCL BATCH DECODER
============================
IMPORTANT Notice : Number of links inside sources are hardcoded to <URLRoot>/Lora !!

Therfore git repository https://github.com/Watteco/LoraEncoders MUST be cloned (or copied) locally in
a directory named '<AnyPath>\Lora'.

Using following XAMPP Webserver configuration example, the Watteco sensors codec website will be accessible through URL:
http://localhost/Lora/

IMPORTANT: Adapt all necesssaries paths in "install.json" file !!
  

[4] WebServer site: (Apache XAMPP example)
==================
install xampp : https://www.apachefriends.org/fr/index.html

In "httpd.conf" file, ADD <Directory> to "DocumentRoot" to set the www server as serving also from "<AnyPath>". ie:
DocumentRoot "C:/xampp/htdocs"
...
<Directory "C:/xampp/htdocs">
    Options Indexes FollowSymLinks Includes ExecCGI
    AllowOverride All
    Require all granted
</Directory>
... 
# Added directory below that contains the Lora webserver files
<Directory "<AnyPath\Lora>">
    Options Indexes FollowSymLinks Includes ExecCGI
    AllowOverride All
    Require all granted
</Directory>
...

Still in "httpd.conf" ADD  Alias entry

...
<IfModule alias_module>
	...
	# Set Alias for Lora server:
	# Warning:
	# Pb : With Alias xampl is case sensitive ? http://localhost/lora ne marche pas
	# Alias /Lora "C:\Users\pegoudet.MICREL\Desktop\Watteco-Work\10_Developpements\Codecs\Lora"
	# Solution : Use AliasMatch for this server to stay case isensitive
	AliasMatch (?i)^/Lora/(.*)$ "<AnyPath>\Lora/$1"

</IfModule>


NOTE:
-----
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