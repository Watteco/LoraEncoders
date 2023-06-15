<!DOCTYPE html>
<html lang="fr">
    <HEAD>
        <link rel="stylesheet" type="text/css" href="Common/stylecss.css">
        <meta charset="utf-8"/>
        <TITLE>Watteco - Décodeur de trames ZCL</TITLE>
        <link rel="shortcut icon" href="Common/cropped-cropped-favicon-300x300-1-32x32.png">
    </HEAD>
    <BODY>  
        <header id="header">
            <a href="https://support.watteco.com/"  target="_blank">
                <img class="header-logo" src="Common/Logo-Watteco-rdm.png" style="float: left" width="100" height="auto" alt="">
            </a>   
            <h2 style="margin-right: 100px; text-align: center;" id="mainMainTitle">
                <span style="text-decoration: underline;">Online Sensors CoDecs</span>
            </h2>
        </header>
		<div id="menu">
			<ul id="onglets">
				<li class="active"><a href="Decoders/index.php" target="iframe_a">  Decoders  </a></li>
				<li><a href="LoraEncoder/JSONTohexFrame.php" target="iframe_a">  JSON to Hex encoder</a></li>
				<li><a href="LoraEncoder/index.html" target="iframe_a">  Encoder assistant  </a></li>
			</ul>
		</div>
        <div id="wrapper" style="position:relative">
            <iframe style="position:absolute;top:0px;width:100%;height:100vh;" name="iframe_a" title="Iframe container" src="Decoders/index.php"></iframe>
        </div>
    </BODY>
</HTML>
