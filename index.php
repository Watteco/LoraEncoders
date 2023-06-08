<!DOCTYPE html>
<html lang="fr">
    <HEAD>
        <link rel="stylesheet" type="text/css" href="stylecss.css">
        <link href="prism.css" rel="stylesheet" />
		<!--<link rel="stylesheet" id="parent-style-css" href="https://support.nke-watteco.com/wp-content/themes/shootingstar/style.css?ver=4.5.4" type="text/css" media="all">-->
        <script src="jquery-3.4.1.min.js"></script>
        <meta charset="utf-8"/>
        <TITLE>Watteco - Décodeur de trames ZCL</TITLE>
        <link rel="shortcut icon" href="https://support.watteco.com/wp-content/uploads/2022/11/cropped-cropped-favicon-300x300-1-32x32.png">
    </HEAD>
    <BODY>
		<div id="menu">
			<ul id="onglets">
				<li class="active"><a href="index.php">  Lora Decoder  </a></li>
				<li><a href="LoraEncoder.php">  Lora Encoder  </a></li>
				<li><a href="LoraEncoder">  Sensor Frame Encoder  </a></li>
			</ul>
		</div>
            <!--<div class="tabs">
                <ul class="tab-links">
                	<li class="active"><a href="#tab1">ZCL frame decoder</a></li>
                	<li><a href="#tab2">Batch frame decoder</a></li>
                </ul>

                <div class="tab-content">
                	<div id="tab1" class="tab active">-->
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
                            switch ( $selected_val  ) {
                            case "0": // JSON
                            	$s = 'C:\Users\administrateur.MICREL\AppData\Local\Programs\Python\Python36\python.exe .\cgi-bin\src\Main.py -if '.escapeshellarg($user_statement).' -of json 2>&1';
                            	echo( "Decoded frame (json) : " .$user_statement );
                            break;
                            case "1": // XMLP
                            	$s = 'C:\Users\administrateur.MICREL\AppData\Local\Programs\Python\Python36\python.exe .\cgi-bin\src\Main.py -if '.escapeshellarg($user_statement).' -of xmlp 2>&1';
                            	echo( "Decoded frame (xmlp) : " .$user_statement );
                            break;
                            case "2": // XMLL
                            	$s = 'C:\Users\administrateur.MICREL\AppData\Local\Programs\Python\Python36\python.exe .\cgi-bin\src\Main.py -if '.escapeshellarg($user_statement).' -of xmll 2>&1';
                            	echo( "Decoded frame (xmll) : " .$user_statement );
                            break;
                            case "3": // STD
                            	$s = 'C:\Users\administrateur.MICREL\AppData\Local\Programs\Python\Python36\python.exe .\cgi-bin\src\Main.py -if '.escapeshellarg($user_statement).' -of std 2>&1';
                            	echo( "Decoded frame (std) : " .$user_statement );
                            break;
                            case "4": // JSON-VERIF
                            	$s = 'C:\Users\administrateur.MICREL\AppData\Local\Programs\Python\Python36\python.exe .\cgi-bin\src\Main.py -if '.escapeshellarg($user_statement).' -of json-verif 2>&1';
                            	echo( "Decoded frame (json-verif) : " .$user_statement );
                            break;
                            }

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
                        <img src="./help.png" alt=" ? " height=20px width=20px/>
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
                        // Tableau des capteurs
                        var array = [
                            {name: "custom",
                                attributes: ""},
                            {name: "50-70-001 S0",
                                attributes: "1 0,1,10,Index1 1,100,6,BatteryLevel"},
							{name: "50-70-[003/021] SP",
                                attributes: "1 0,1,9,ActiveEnergy"},
                            {name: "50-70-007 THr harvesting",
                                attributes: "3 0,10,7,Temperature 1,100,6,RelativeHumidity 2,10,12,Luminosity 3,100,6,Disposable_BatteryLevel 4,100,6,Rechargeable_BatteryLevel"},
                            {name: "50-70-011 Senso",
                                attributes: "3 0,1,4,Status 1,1,11,Index 2,1,5,MinFlow 3,1,5,MaxFlow"},
							{name: "50-70-[014/039/051/072/079/160] PulseSenso",
								attributes: "4 0,1,10,Index1 1,1,10,Index2 2,1,10,Index3 3,1,1,State1 4,1,1,State2 5,1,1,State3 6,100,6,BatteryLevel 7,1,6,MultiState"},
                            {name: "50-70-017 Presso",
                                attributes: "3 0,0.004,12,4-20mA 1,1,12,0-10V 2,100,6,BatteryLevel 3,100,6,ExtPowerLevel 4,1,10,Index"},
                            {name: "50-70-[043/142] Remote temperature",
                                attributes: "1 0,10,7,Temperature 1,100,6,BatteryLevel"},
                            {name: "50-70-049 Celso",
                                attributes: "1 0,10,7,Temperature 1,100,6,BatteryLevel"},
                            {name: "50-70-[053/162] TH",
                                attributes: "2 0,10,7,Temperature 1,100,6,RelativeHumidity 2,1,6,BatteryLevel 3,1,1,OpenCase"},
                            {name: "50-70-071 Flasho",
                                attributes: "1 0,1,10,Index 1,100,6,BatteryLevel"},
                            {name: "50-70-074 VAQAO+Plus",
                                attributes: "3 0,1,4,OCC 1,10,7,T 2,100,6,H 3,10,6,CO2 4,10,6,COV 5,10,6,LUX 6,10,6,P"},
                            {name: "50-70-[085/167] T",
                                attributes: "2 0,10,7,Temperature  2,1,6,BatteryLevel 3,1,1,OpenCase"},
                            {name: "50-70-098 Intenso",
                                attributes: "2 0,0.1,12,Current 1,100,6,BatteryLevel"},
							{name: "50-70-099 Atmo",
                                attributes: "3 0,1,7,Temperature 1,1,6,RelativeHumidity 2,1,7,Pressure 3,1,10,Index1 4,1,10,Index2 5,1,6,BatteryLevel"},
                            {name: "50-70-[064/101/166] Ventilo",
                                attributes: "3 0,1,7,Mean_differential_pressure_since_last_report 1,1,7,Minimal_differential_pressure_since_last_report 2,1,7,Maximal_differential_pressure_since_last_report  3,1,6,BatteryLevel 4,10,7,Temperature 5,1,7,DifferentialPressure 6,1,10,Index 7,1,1,State"},
                            {name: "50-70-108 Closo",
                                attributes: "2 0,1,1,OpenClose 1,100,6,BatteryLevel"},
							{name: "50-70-123 PulseSenso Atex Zone 1",
								attributes: "4 0,1,10,Index1 1,1,10,Index2 2,1,10,Index3 3,1,1,State1 4,1,1,State2 5,1,1,State3 6,100,6,BatteryLevel 7,1,6,MultiState"},
							{name: "50-70-124 Torano Atex Zone 1",
								attributes: "4 0,1,10,Index1 1,1,10,Index2 2,1,10,Index3 3,1,1,State1 4,1,1,State2 5,1,1,State3 6,0.004,12,4-20mA 7,1,12,0-5V[Measure-1] 8,1,12,0-5V[Measure-2] 9,1,12,ratio[Measure-1] 10,1,12,ratio[Measure-1] 11,100,6,BatteryLevel"},
							{name: "50-70-[139/163/184] Remote temperature 2CTN",
                                attributes: "3 0,10,7,Temperature1 1,10,7,Temperature2 5,100,6,BatteryLevel"},
							{name: "50-70-141 Monito",
                                attributes: "3 0,0.02,12,0-100mV 1,17,12,0-70V 2,100,6,BatteryLevel 3,100,6,ExternalLevel"},
							{name: "50-70-164 Outdoor temperature",
                                attributes: "3 0,10,7,Temperature 5,100,6,BatteryLevel"},
                            {name: "50-70-168 VAQAO",
                                attributes: "3 1,10,7,T 2,100,6,H 3,10,6,CO2 4,10,6,COV"},
                            {name: "50-70-206 Flash'O 3 probes",
								attributes: "4 0,1,10,Index1 1,1,10,Index2 2,1,10,Index3 3,1,1,State1 4,1,1,State2 5,1,1,State3 6,100,6,BatteryLevel 7,1,6,MultiState"},
                            {name: "50-70-220-000 Move'o",
                                attributes: "3 0,1,4,OCC 1,10,7,T 2,100,6,H 5,10,6,LUX 6,10,6,P"}

                        ];

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
                    <img src="./help.png" alt=" ? " height=20px width=20px/>
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
                        $s = 'C:\Users\administrateur.MICREL\AppData\Local\Programs\Python\Python36\python.exe .\cgi-bin\br_uncompress.py -if '.escapeshellarg($user_statement_trame).' -a '.$user_statement_attributes.' 2>&1';
                                                }
                        else
                        {
                        $s = 'C:\Users\administrateur.MICREL\AppData\Local\Programs\Python\Python36\python.exe .\cgi-bin\br_uncompress.py -if '.escapeshellarg($user_statement_trame).' -t '.$user_statement_timestamp.' -a '.$user_statement_attributes.' 2>&1';
                        }
                        echo( "Frame to decode (FrmPayload): " .$user_statement_trame );
                        echo( "<br>");
                        echo( "TimeStamp: " .$user_statement_timestamp );
                        echo( "<br>");
                        echo( "Batch attributes: " .$user_statement_attributes );

                        //$s = 'C:\Users\administrateur.MICREL\AppData\Local\Programs\Python\Python36\python.exe C:\xampp\htdocs\Intranet\Configurateur\cgi-bin\src\Main.py -if '.$user_statement.' -of json 2>&1';
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
                <!--		</div>
                    </div>
                    </div>-->
                <!--<hr>-->
                <!--<a href="https://support.nke-watteco.com/">support.nke-watteco.com </a> - <a href="http://www.nke-watteco.fr/">www.nke-watteco.fr</a> -->
            </DIV>
        <!--<script src="jquery.js"></script>-->
        <script>
            jQuery(document).ready(function() {
            	jQuery('.tabs .tab-links a').on('click', function(e) {
            		var currentAttrValue = jQuery(this).attr('href');

            		// Show/Hide Tabs
            		jQuery('.tabs ' + currentAttrValue).show().siblings().hide();

            		// Change/remove current tab to active
            		jQuery(this).parent('li').addClass('active').siblings().removeClass('active');

            		e.preventDefault();
            	});
            });

        </script>
        <script src="prism.js"></script>
    </BODY>
</HTML>
