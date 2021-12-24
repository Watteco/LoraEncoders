<?php

function myExec($command, $stdinStr="", &$stdoutStr, &$stderrStr) {
	
	// Line below: Simple but insufficient way to run a prog from php. We need STDIN and STDOUT for tictobin
	// $output = shell_exec(__DIR__.'/tictobin.exe -v 2>&1');
	// proc_open API is the good one for us
	
	$descriptorspec = array(
	   0 => array("pipe", "r"),// stdin est un pipe où le processus va lire
	   1 => array("pipe", "w"),// stdout est un pipe où le processus va écrire
	   2 => array("pipe", "w") // stderr est un pipe où le processus va écrire
	   //2 => array("file", __DIR__."/tictobin-error-output.txt", "a") // stderr est un fichier
	);

	$cwd = __DIR__;
	$env = NULL;

	$process = proc_open($command, $descriptorspec, $pipes, $cwd, $env);
	$stdoutStr = ""; $stderrStr = "";
	if (is_resource($process)) {
		
		fwrite($pipes[0], $stdinStr);
		fclose($pipes[0]);
		$stdoutStr = stream_get_contents($pipes[1]);
		$stderrStr = stream_get_contents($pipes[2]);

// echo "CMD:    ==>\r\n".$command;
// echo "STDIN:  ==>\r\n".$stdinStr;
// echo "STDOUT: ==>\r\n".$stdoutStr;
// echo "STDERR: ==>\r\n".$stderrStr;

		// Must Close pipes before process
		fclose($pipes[1]);
		fclose($pipes[2]);
		$return_value = proc_close($process);
	}

	return $return_value;
}


//Fonction permettant de verifier la validite du JSON recupere
function isValidJSON($dataString) {
    json_decode($dataString);
    return (json_last_error() === JSON_ERROR_NONE);
}
//On vient lire le fichier configuration pour associer le chemin de python.exe à la variable $path
$strJsonFileContents = file_get_contents(__DIR__."/../configuration.json");
$array = json_decode($strJsonFileContents, true); // show contents

$path = $array["pathPython"];
//Verification de l'argument et lancement du script
if(isset($_GET['json']) && !empty($_GET['json'])) {
    $jsonString = $_GET['json'];
	if(isValidJSON($jsonString)) {
		
		$jsonData = json_decode($jsonString, true);

		$output = shell_exec($path.' '.__DIR__.'/encoder.py '.escapeshellarg(base64_encode($jsonString)));		
		// supprime les 2 premiers caractères b'
		$output = substr( $output, 2, -1);
		// supprime le dernier caractère '
		$output = substr( $output, 0, -1);
				
		echo $output;	
	} else {
		echo "errorMsg1";
	}
} else {
	echo "errorMsg2";

}

?>