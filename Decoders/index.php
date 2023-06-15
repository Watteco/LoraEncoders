<!DOCTYPE html>
<html lang="fr">
    <HEAD>
        <link rel="stylesheet" type="text/css" href="../Common/stylecss.css">
        <!-- <script src="../Common/jquery-3.4.1.min.js"></script> -->
        <meta charset="utf-8"/>
        <TITLE>Watteco - Décodeurs de trames ZCL</TITLE>
        <link rel="shortcut icon" href="Common.png">
        
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
        <DIV">
            <!-- <IMG SRC="LOGO-NKE-WATTECO-CMJN.png" height=120px width=300px >-->
            <H2>ZCL frame decoder</H2>
            <form method="get" action="" name="statement_user">
                <p>
                    <label for="trame" >Frame to decode (FrmPayload)</label> : <input type="test" name="trame" id="trame" placeholder="" size="100"/>
                    <select name="MySelectMenu" id="MySelectMenu">
                        <OPTION selected value="0">JSON</OPTION>
                        <OPTION value="1">XMLP</OPTION>
                        <OPTION value="2">XMLL</OPTION>
                        <OPTION value="3">STD</OPTION>
                        <OPTION value="4">JSON-VERIF</OPTION>
                    </select>
                    <label>
                    base 64
                    <input type="checkbox" name="checkbase" id ="checkbase" value="checkox_value">
                    </label>
                    <button type="submit" value="Submit" id="Submit" name=" submit" > Decode</button>
                    <br>
                    <?php
                        if (isset($_GET['trame']))
                        {
                        $user_statement = strip_tags($_GET['trame']);
                        $user_statement = str_replace(' ','',$user_statement);
                        $pattern = "/[g-z=]/i";

                        // Simple Hack to keep TIC parameter passing for rev option ";rev=xxx"
                        // TODO : Mind about modifying the HMI to allow any parameters in an other text field for example ?
                        $patternRev = "/;[ \t]*rev[ \t]*=[ \t]*[0-9]{4}/i";
                        $withTICRev = preg_match($patternRev, $user_statement);

                        if((isset($_GET['checkbase']) || preg_match($pattern, $user_statement)) && (! $withTICRev)){
                            $user_statement = bin2hex(base64_decode($user_statement));
                        }
                        if (isset($_GET['MySelectMenu']))
                        {
                        echo( "<hr size=2 align=center width='100%'>");
                        echo( "<br>");

                        $selected_val = strip_tags($_GET['MySelectMenu']);
                        
                        $sf = "";
                        switch ( $selected_val  ) {
                            case "0": // JSON
                                $sf = ' -of json 2>&1';
                                echo( "Decoded frame (json) : " .$user_statement );
                            break;
                            case "1": // XMLP
                                $sf =' -of xmlp 2>&1';
                                echo( "Decoded frame (xmlp) : " .$user_statement );
                            break;
                            case "2": // XMLL
                                $sf =' -of xmll 2>&1';
                                echo( "Decoded frame (xmll) : " .$user_statement );
                            break;
                            case "3": // STD
                                $sf = ' -of std 2>&1';
                                echo( "Decoded frame (std) : " .$user_statement );
                            break;
                            case "4": // JSON-VERIF
                                $sf = ' -of json-verif 2>&1';
                                echo( "Decoded frame (json-verif) : " .$user_statement );
                            break;
                        }

                        $s = $Python.' '.$pathPythonStdCodec.' -if '.escapeshellarg($user_statement).$sf;

                        echo( "<br>" );
                        $output = shell_exec( $s );
                        echo( "<br>");
                        echo( "<textarea rows=25 cols=150 readonly wrap='hard'>$output</textarea>" );
                        echo( "<br>");
                        }
                        }
                        ?>
                </p>
            </form>
        </DIV>
        <br>
        <!--<hr size=5 noshade>-->
        <!--</div>
            <div id="tab2" class="tab">-->
        <DIV>
            <H2>Batch frame decoder</H2>
            <form method="get" action="" name="statement_user2">
                <p>
                    <label for="trameBatch" >Frame to decode (FrmPayload)</label> : <input type="text" name="trameBatch" id="trameBatch" placeholder="" size="80"/>
                    <br>
                    <label for="timestamp"> TimeStamp</label> : <input type="text" name="timestamp" id="timestamp" placeholder="" size="30"/>
                    <a class="tooltip">
                    <img src="../Common/help.png" alt=" ? " height=20px width=20px/>
                    <span class="tooltiptext">
                    Format: yyyy-MM-ddTDD:mm:ss.SSSZ
                    </span>
                    </a>
                    <br>
                    <label for="BatchAttributes" >Batch attributes</label> :
                <td>
                    <!--Liste de choix!-->
                    <select id="selectSensor">
                    </select>
                </td>
                <input type="text" name="BatchAttributes" id="BatchAttributes" placeholder="" size="90"/>
                <script type="text/javascript">
                    // Tableau des capteurs récupéré en php(server) plutot que javascript(requête locale) pour test 
                    var array = <?php
                        $APLJson=file_get_contents("../WattecoSensors/AvailableProductsList.json");
                        //$APLJson=fixJSON($APLJson);
                        $APLJson = str_replace("'","",$APLJson); 
                        //echo $APLJson;
                        $APL = json_decode($APLJson);
                        //var_dump($APL);
                        
                        echo "[".' {name: "custom", attributes: ""}';
                        foreach($APL->products as $product) {
                            $displayed=true;
                            if (property_exists($product, 'apps'))
                                if (! in_array("Decoder", $product->apps))
                                    $displayed=false;
                            if ($displayed && (property_exists($product,'BatchUncompressParams'))) {
                                echo ', {name: "'.
                                    (is_array($product->name) ? $product->name[0]  : $product->name).'", attributes: "'.
                                    $product->BatchUncompressParams.'"}'.
                                    "\n";
                            }
                        }
                        echo "]";
                    ?>;
                    
                    // Pour chaque key (name) de ton tableau tu créais une balise <option> que tu insères dans le HTML
                    for(var i in array){
                        var option = document.createElement('option');
                        option.setAttribute('value', array[i].name);
                        option.innerHTML = array[i].name;
                        document.getElementById('selectSensor').appendChild(option);
                    }
                    document.getElementById('selectSensor').value = localStorage.getItem("selectSensor");
                    document.getElementById('BatchAttributes').value = localStorage.getItem("BatchAttributes");

                    // Lors d'une sélection la function callback de l'évènement change est appelée
                    document.getElementById('selectSensor').addEventListener('change', function(e){
                        var selectUser = e.target.value;
                        localStorage.setItem('selectSensor', selectUser)
                        for(var i in array){ // ici on parcours le tableau afin de chercher le prix correspondant à la sélection
                            if(array[i].name === selectUser) document.getElementById('BatchAttributes').value = array[i].attributes;
                        }
                        localStorage.setItem('BatchAttributes', document.getElementById('BatchAttributes').value)

                    }, false);

                </script>
                <a class="tooltip">
                <img src="../Common/help.png" alt=" ? " height=20px width=20px/>
                <span class="tooltiptext">
                tagsz "taglbl,resol,sampletype,[lblname]" "..."<br>
                <br>
                ex: <br>
                3 2,10,9 1,10,7 4,30,10 3,10,4 5,10,6 6,1,4 <br>
                2 2,10,9,temperature 1,10,7,pressure<br>
                <br>
                Where SampleType can be:<br>
                1: Boolean<br>
                2: Unsigned int U4<br>    3: Signed int I4<br>
                4: Unsigned int U8<br>    5: Signed int I8<br>
                6: Unsigned int U16<br>   7: Signed int I16<br>
                8: Unsigned int U24<br>   9: Signed int I24<br>
                10: Unsigned int U32<br>  11: Signed int I32<br>
                12: Float<br>
                </span>
                </a>
                <label>
                    base 64
                    <input type="checkbox" name="checkbasebatch" id ="checkbasebatch" value="checkox_value">
                </label>
                <button type="submit" value="Submit2" id="Submit2" name=" submit" > Decode</button>
                <br>
                <?php
                    if (isset($_GET['trameBatch']))
                    {
                    $user_statement_trame = strip_tags($_GET['trameBatch']) ;
                    $user_statement_trame=str_replace(' ','',$user_statement_trame);
                    $user_statement_attributes = strip_tags($_GET['BatchAttributes']) ;
                    $user_statement_timestamp = strip_tags($_GET['timestamp']) ;
                    echo( "<hr size=2 align=center width='100%'>");
                    echo( "<br>");

                    $pattern = "/[g-z=]/i";

                    if(isset($_GET['checkbasebatch']) || preg_match($pattern, $user_statement_trame)){
                        $user_statement_trame = bin2hex(base64_decode($user_statement_trame));
                    }

                    if(empty($_GET['timestamp']))
                    {
                    $s = $Python.' '.$pathPythonBatchDecoder.' -if '.escapeshellarg($user_statement_trame).' -a '.$user_statement_attributes.' 2>&1';
                    }
                    else
                    {
                    $s = $Python.' '.$pathPythonBatchDecoder.' -if '.escapeshellarg($user_statement_trame).' -t '.$user_statement_timestamp.' -a '.$user_statement_attributes.' 2>&1';
                    }
                    echo( "Frame to decode (FrmPayload): " .$user_statement_trame );
                    echo( "<br>");
                    echo( "TimeStamp: " .$user_statement_timestamp );
                    echo( "<br>");
                    echo( "Batch attributes: " .$user_statement_attributes );

                    echo( "<br>" );
                    //echo( "<br>");
                    $output2 = shell_exec( $s );
                    echo( "<br>");
                    echo( "<textarea rows=25 cols=150 readonly wrap='hard'>$output2</textarea>" );
                    echo( "<br>");
                    }
                    ?>
                </p>
            </form>
        </DIV>
    </BODY>
</HTML>
