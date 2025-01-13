<!DOCTYPE html>
<html lang="fr">

    <HEAD>
        <link rel="stylesheet" type="text/css" href="../Common/stylecss.css">
        
		<script>var loraEncoderType = "Decoder";</script>
		<script type="text/javascript" src="../Common/commonTools.js?v=1.2a"></script>
		<script type="text/javascript" src="../Common/buildProductSelect.js?v=1.2a"></script>

        <!-- <script src="../Common/jquery-3.4.1.min.js"></script> -->
        <meta charset="utf-8"/>
        <TITLE>Watteco - Décodeurs de trames ZCL</TITLE>
        <link rel="shortcut icon" href="Common.png">
        
        <?php
            // Init submited parameters to empty (used for default init of fields when they exists, before eventual localstorage values)
            $submited_trame='';
            $submited_MySelectMenu='';
            $submited_checkbase='';
            $submited_productSelectIndex="";
            $submited_BatchAttributes="";
            $submited_trameBatch="";
            $submited_timestamp="";
        ?>
        
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
        <DIV>
            <!-- <IMG SRC="LOGO-NKE-WATTECO-CMJN.png" height=120px width=300px >-->
            <H2>ZCL frame decoder</H2>
            <form method="get" action="" name="statement_user"  onsubmit="return doBeforeSubmit()">
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
                        $submited_trame='';
                        if (isset($_GET['trame']))
                        {
                        $user_statement = strip_tags($_GET['trame']);
                        $submited_trame = htmlspecialchars($user_statement);
                        $user_statement = str_replace(' ','',$user_statement);
                        $pattern = "/[g-z=]/i";

                        // Simple Hack to keep TIC parameter passing for rev option ";rev=xxx"
                        // TODO : Mind about modifying the HMI to allow any parameters in an other text field for example ?
                        $patternRev = "/;[ \t]*rev[ \t]*=[ \t]*[0-9]{4}/i";
                        $withTICRev = preg_match($patternRev, $user_statement);

                        if((isset($_GET['checkbase']) || preg_match($pattern, $user_statement)) && (! $withTICRev)){
                            $submited_checkbase=htmlspecialchars($_GET['checkbase']);
                            $user_statement = bin2hex(base64_decode($user_statement));
                        }
                        if (isset($_GET['MySelectMenu']))
                        {
                        $submited_MySelectMenu=htmlspecialchars($_GET['MySelectMenu']);
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
            <form method="get" action="" name="statement_user2" onsubmit="return doBeforeSubmit()">
                <p>
                    <label for="trameBatch" >Frame to decode (FrmPayload)</label> : <input type="text" name="trameBatch" id="trameBatch" placeholder="" size="80"/>
                    <br>
                    <label for="timestamp"> TimeStamp</label> : <input type="text" name="timestamp" id="timestamp" placeholder="yyyy-MM-ddThh:mm:ss.000Z" data-slots="yMdhms" size="30"/>
                    <a class="tooltip">
                    <img src="../Common/help.png" alt=" ? " height=20px width=20px/>
                    <span class="tooltiptext">
                    Format: yyyy-MM-ddTDD:mm:ss.SSSZ
                    </span>
                    </a>
                    <button type="button" id="btnNow" name="btnNow" onclick="setTimestamp()">Now</button>
                    <br>
                    <label for="BatchAttributes" >Batch attributes</label> :
                <td>
                    <!--Liste de choix!-->
                    <select id="productSelect">
                    </select>
                    <!-- Hidden input to store the index -->
                    <input type="hidden" id="productSelectIndex" name="productSelectIndex">
                </td>
                <input type="text" name="BatchAttributes" id="BatchAttributes" placeholder="" size="90"/>
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
                    $submited_trameBatch = htmlspecialchars($user_statement_trame);
                    $submited_productSelectIndex = htmlspecialchars(($_GET['productSelectIndex']));
                    $user_statement_trame=str_replace(' ','',$user_statement_trame);
                    $user_statement_attributes = strip_tags($_GET['BatchAttributes']) ;
                    $submited_BatchAttributes=htmlspecialchars($user_statement_attributes);
                    $user_statement_timestamp = strip_tags($_GET['timestamp']) ;
                    $submited_timestamp=htmlspecialchars($user_statement_timestamp);
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
        <div align="right">
            <select onchange="switchLang();" id="langSelect" style="font-size: 10px;width:100px;margin-top:8px;">
                <option id="langOption0" value="0">English</option>
                <option id="langOption1" value="1">Français</option>
            </select>
        </div>
        <div id="tooltip"></div>
        <script type="text/javascript"> /** Managed Events **/

            // Lors d'une sélection la function callback de l'évènement change est appelée
            document.getElementById('productSelect').addEventListener('change', function(e){
                document.getElementById('BatchAttributes').value = e.target.value;
            }, false);

            function doBeforeSubmit() 
            {
                // Update hidden producSelectIndex input from productSelect
                document.getElementById("productSelectIndex").value = document.getElementById("productSelect").selectedIndex;

                localStorage.setItem('MySelectMenuIndex', document.getElementById('MySelectMenu').selectedIndex);
                localStorage.setItem('checkbaseValue', document.getElementById('checkbase').value);
                localStorage.setItem('trameValue', document.getElementById('trame').value);

                localStorage.setItem('productSelectIndex', document.getElementById("productSelect").selectedIndex);
                localStorage.setItem('BatchAttributesValue', document.getElementById('BatchAttributes').value);
                localStorage.setItem('trameBatchValue', document.getElementById('trameBatch').value);
                localStorage.setItem('timestampValue', document.getElementById('timestamp').value);

                return true;
            }

            function setTimestamp()
            {
                document.getElementById('timestamp').value = getCurrentDateBatchFormatted();
            }

        </script>
		<script type="text/javascript"> /** Executed at each load : Init fields either from submit parameters or last recoded in localstorage**/
			document.getElementById('langOption' + lang).defaultSelected = true;

            // For standard decoder form
            submited_MySelectMenu="<?php echo $submited_MySelectMenu; ?>";
            document.getElementById('MySelectMenu').selectedIndex = ((!(submited_MySelectMenu?.trim())) ? localStorage.getItem("MySelectMenuIndex") :submited_MySelectMenu);
            submited_checkbase="<?php echo $submited_checkbase; ?>";
            document.getElementById('checkbase').value = ((!(submited_checkbase?.trim())) ? localStorage.getItem("checkbaseValue") :submited_checkbase);
            submited_trame="<?php echo $submited_trame; ?>";
            document.getElementById('trame').value = ((!(submited_trame?.trim())) ? localStorage.getItem("trameValue") :submited_trame);

            // For batch decoder form
            submited_productSelectIndex="<?php echo $submited_productSelectIndex; ?>";
			getAllAvailableProducts(optSelectedIndex = ((!(submited_productSelectIndex?.trim())) ? localStorage.getItem("productSelectIndex") :submited_productSelectIndex));

            submited_BatchAttributes="<?php echo $submited_BatchAttributes; ?>";
            document.getElementById('BatchAttributes').value = ((!(submited_BatchAttributes?.trim())) ? localStorage.getItem("BatchAttributesValue") :submited_BatchAttributes);
            
            submited_trameBatch="<?php echo $submited_trameBatch; ?>";
            document.getElementById('trameBatch').value = ((!(submited_trameBatch?.trim())) ? localStorage.getItem("trameBatchValue") :submited_trameBatch);

            submited_timestamp="<?php echo $submited_timestamp; ?>";
            document.getElementById('timestamp').value = ((!(submited_timestamp?.trim())) ? localStorage.getItem("timestampValue") :submited_timestamp);
            document.getElementById('timestamp').value = localStorage.getItem("timestampValue");

            SetInputMaskMngt();
		</script>
    </BODY>
</HTML>
