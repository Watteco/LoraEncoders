<!DOCTYPE html>
<html lang="fr">
    <HEAD>
        <link rel="stylesheet" type="text/css" href="../Common/stylecss.css">
		<TITLE>Watteco - Encodeur de trames ZCL</TITLE>
        <link rel="shortcut icon" href="../Common/cropped-cropped-favicon-300x300-1-32x32.png">
        
        <?php
            //Get install parameters
            $installDir=__DIR__.'/../';
            $strJsonFileContents = file_get_contents($installDir."install.json");
            $InstArrayParams = json_decode($strJsonFileContents, true); 
            
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
    <BODY>
        <DIV ALIGN="Left">
            <!--<IMG SRC="LOGO-NKE-WATTECO-CMJN.png" height=120px width=300px >-->
            <H2>ZCL frame encoder</H2>
            <form method="get" action="" name="statement_user">
                <p>
                    <label for="trame" >Frame to encode</label> :
                    <textarea id="trame" name="trame" rows=20 cols=100></textarea>
                    <button type="submit" value="Submit" id="Submit" name=" submit" > Encode</button>
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
							echo( "Encoded frame : " .$user_statement );

							if(isValidJSON($user_statement)) {
								
								$s = 'echo '.$user_statement.' | '.$Python.' '.$pathPythonStdCodec.' -m e 2>&1';

								$output = shell_exec( $s );
								//$output = preg_replace("/(\\\r)?\\\n/", "\n", $output);
								//$output = str_replace('\n', '<BR>', $output );
								//$output = preg_replace ("/\r\n|\n\r|\n|\r/", "<br>", $output );

								// supprime les 2 premiers caractères b'
								$output = substr( $output, 2, -1);
								// supprime le dernier caractère '
								$output = substr( $output, 0, -1);
							} else {
								$output = "The input JSON is not valid";
							}

							echo( "<br>" );
							echo( "<br>");

							//echo( '<code class="language-markup">' );
							echo( "Result : ".$output );
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
