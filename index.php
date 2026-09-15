<?php
header('Cache-Control: no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');
?>

<!DOCTYPE html>
<html lang="fr">
    <HEAD>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="Common/stylecss.css">
        <TITLE>Watteco - Online Codec</TITLE>
        <link rel="shortcut icon" href="Common/cropped-cropped-favicon-300x300-1-32x32.png">

		<script type="text/javascript">
            function TabSelect(id) {
                document.getElementById("Decoders").className="";
                document.getElementById("JSONTohexFrame").className="";
                document.getElementById("Encoders").className="";
                document.getElementById("EasyCodec").className="";
                document.getElementById(id).className="active";
            }
        </script>
    </HEAD>
    <BODY class="app-shell">
        <header id="header">
            <a href="https://support.watteco.com/"  target="_blank">
                <img class="header-logo" src="Common/Logo-Watteco-rdm.png" style="float: left" width="100" height="auto" alt="">
            </a>   
            <h2 id="mainMainTitle" style="text-align: center;">
                <span style="text-decoration: underline;">ONLINE SENSORS CODECS</span>
            </h2>
        </header>
		<div id="menu">
			<ul id="onglets">
				<li id="Decoders" class="active" ><a href="Decoders/index.php" target="iframe_a" onClick="TabSelect('Decoders');">  Decoders  </a></li>
				<li id="JSONTohexFrame" ><a href="LoraEncoder/JSONTohexFrame.php" target="iframe_a" onClick="TabSelect('JSONTohexFrame');">  JSON to Hex encoder</a></li>
                <li id="Encoders" ><a href="LoraEncoder/index.php" target="iframe_a" onClick="TabSelect('Encoders');">  Encoder assistant  </a></li>
				<li id="EasyCodec" ><a href="https://lora.watteco.fr/EasyCodec/tabs/downlink" target="iframe_a" onClick="TabSelect('EasyCodec');">  Easy Codec  </a></li>
			</ul>
		</div>
		<div id="wrapper">
            <iframe name="iframe_a" title="Iframe container" src="Decoders/index.php"></iframe>
        </div>
    </BODY>
</HTML>
