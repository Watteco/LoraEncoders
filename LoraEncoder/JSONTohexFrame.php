<!DOCTYPE html>
<html lang="fr">
    <HEAD>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="../Common/stylecss.css">

		<TITLE>Watteco - Encodeur de trames ZCL</TITLE>
        <link rel="shortcut icon" href="../Common/cropped-cropped-favicon-300x300-1-32x32.png">
        
        <?php
            //Get install parameters
            $installDir=__DIR__.'/../';
            $installFile = file_exists($installDir."install-local.json") ? "install-local.json" : "install.json";
            $strJsonFileContents = file_exists($installDir.$installFile) ? file_get_contents($installDir.$installFile) : '{}';
            $InstArrayParams = json_decode($strJsonFileContents, true);
            if (!is_array($InstArrayParams)) { $InstArrayParams = array(); }

            $Python = (array_key_exists("pathPython",$InstArrayParams) ? $InstArrayParams["pathPython"] : "python.exe");

            function processPath($array, $pathName, $default) {
                global $installDir;
                $path = (array_key_exists($pathName,$array) ? $array[$pathName] : $default);
                $path = (substr($path, 0, 1) === "/" ? "" : $installDir  ).$path;
                return $path;
            }

            $pathPythonStdCodec = processPath($InstArrayParams,'pathPythonStdCodec', './cgi-bin/src/Main.py');
            $pathPythonBatchDecoder = processPath($InstArrayParams,'pathPythonBatchDecoder', './cgi-bin/src/br_uncompress.py');
        ?>

    </HEAD>
    <BODY class="compact-tool">
        <DIV ALIGN="Left" class="wtc-panel">
            <!--<IMG SRC="LOGO-NKE-WATTECO-CMJN.png" height=120px width=300px >-->
            <H2>ZCL frame encoder</H2>
            <form method="get" action="" name="statement_user">
                <p>
                    <label for="trame" >Frame to encode</label> :
                    <textarea id="trame" name="trame" rows=20 cols=100></textarea>
                    <button class="wtc-button wtc-button--primary" type="submit" value="Submit" id="Submit" name=" submit" > Encode</button>
                    <br>
                    <?php
						//Fonction permettant de verifier la validite du JSON recupere
						function isValidJSON($dataString) {
							json_decode($dataString);
							return (json_last_error() === JSON_ERROR_NONE);
						}

                        if (isset($_GET['trame']))
                        {
							$user_statement = strip_tags($_GET['trame']);

							$user_statement = str_replace( array("\r\n","\n"), " ", $user_statement);

							echo("<hr>");
							echo("<br>");

							//echo( "<textarea rows=20 cols=100 readonly wrap='hard'>$user_statement</textarea>" );
							echo( "Encoded frame : " . htmlspecialchars($user_statement, ENT_QUOTES) );

							if(isValidJSON($user_statement)) {

								// SECURITE : $user_statement est desormais passe a escapeshellarg()
								// avant d'etre injecte dans la commande shell. Auparavant la chaine
								// JSON brute etait concatenee telle quelle, ce qui permettait une
								// injection de commande arbitraire (ex: trame=";rm -rf / #").
								$s = 'echo ' . escapeshellarg($user_statement) . ' | ' . escapeshellarg($Python) . ' ' . escapeshellarg($pathPythonStdCodec) . ' -m e 2>&1';

								$output = shell_exec( $s );
								//$output = preg_replace("/(\\\r)?\\\n/", "\n", $output);
								//$output = str_replace('\n', '<BR>', $output );
								//$output = preg_replace ("/\r\n|\n\r|\n|\r/", "<br>", $output );

								// supprime les 2 premiers caracteres b' puis le dernier caractere '
								// (protege contre une sortie plus courte que prevu)
								if (is_string($output) && strlen($output) >= 3) {
									$output = substr( $output, 2, -1);
									$output = substr( $output, 0, -1);
								} else {
									$output = '';
								}
							} else {
								$output = "The input JSON is not valid";
							}

							echo( "<br>" );
							echo( "<br>");

							//echo( '<code class="language-markup">' );
							// SECURITE : la sortie est echappee avant affichage (XSS reflechie/stockee)
							echo( "Result : " . htmlspecialchars($output, ENT_QUOTES) );
							//echo( "</code>" );
							//echo( '<div id="trame">');
							//echo( '<code class="language-markup">' );
							//echo( "<code class='language-markup'>" );
							//echo( "<textarea rows=20 cols=100 readonly wrap='hard'>$output</textarea>" );
							//echo( "</code>" );
							echo( "<br>");
							//echo( '<code class="language-markup">' );
							//	echo( "<pre>$output</pre>" );
							//echo( "</code>" );
							//	echo( "<br>");
							//echo( "<section class='language-markup'>" );
							//	echo( '<section class="language-markup">' );
							//echo( "<code class='language-markup'>" );
							//	echo( "<pre><code>$output</code></pre>");
							//echo( "</code>" );
							//	echo( "</section>" );
							//echo( "</div>" );
                        }
                    ?>
                </p>
            </form>
            <!--<hr>-->
            <!--<a href="https://support.nke-watteco.com/">support.nke-watteco.com </a> - <a href="https://www.nke-watteco.fr/">www.nke-watteco.fr</a> -->
        </DIV>
    </BODY>
</HTML>
