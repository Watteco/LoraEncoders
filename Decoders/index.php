<?php
header('Cache-Control: no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

$decoderLanguage = isset($_COOKIE['lang']) && (int)$_COOKIE['lang'] === 1 ? 1 : 0;
$decoderTranslations = array(
    array(
        'pageTitle' => 'Watteco - ZCL frame decoders',
        'modeLabel' => 'Decoder mode',
        'standardTitle' => 'ZCL frame decoder',
        'batchTitle' => 'Batch frame decoder',
        'frameToDecode' => 'Frame to decode (FrmPayload)',
        'timestamp' => 'TimeStamp',
        'now' => 'Now',
        'batchAttributes' => 'Batch attributes',
        'batchParameters' => 'Batch attributes parameters',
        'decode' => 'Decode',
        'share' => 'Share',
        'decodedFrame' => 'Decoded frame',
        'sampleTypeHelp' => 'Where SampleType can be:',
        'boolean' => 'Boolean',
        'unsignedInteger' => 'Unsigned int',
        'signedInteger' => 'Signed int',
        'float' => 'Float'
    ),
    array(
        'pageTitle' => 'Watteco - Décodeurs de trames ZCL',
        'modeLabel' => 'Mode de décodage',
        'standardTitle' => 'Décodeur de trame ZCL',
        'batchTitle' => 'Décodeur de trame Batch',
        'frameToDecode' => 'Trame à décoder (FrmPayload)',
        'timestamp' => 'Horodatage',
        'now' => 'Maintenant',
        'batchAttributes' => 'Attributs Batch',
        'batchParameters' => 'Paramètres des attributs Batch',
        'decode' => 'Décoder',
        'share' => 'Partager',
        'decodedFrame' => 'Trame décodée',
        'sampleTypeHelp' => 'Où SampleType peut être :',
        'boolean' => 'Booléen',
        'unsignedInteger' => 'Entier non signé',
        'signedInteger' => 'Entier signé',
        'float' => 'Flottant'
    )
);
$decoderText = $decoderTranslations[$decoderLanguage];
?>

<!DOCTYPE html>
<html lang="<?php echo $decoderLanguage === 1 ? 'fr' : 'en'; ?>">

    <HEAD>
        <?php require_once __DIR__ . '/../Common/assetVersion.php'; ?>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="../Common/stylecss.css">

		<script>var loraEncoderType = "Decoder";</script>
        <script type="text/javascript" src="<?php echo versionedAssetUrl('../Common/commonTools.js', __DIR__); ?>"></script>
        <script type="text/javascript" src="<?php echo versionedAssetUrl('../Common/buildProductSelect.js', __DIR__); ?>"></script>

        <!-- <script src="../Common/jquery-3.4.1.min.js"></script> -->
        <meta charset="utf-8"/>
        <TITLE><?php echo htmlspecialchars($decoderText['pageTitle'], ENT_QUOTES, 'UTF-8'); ?></TITLE>
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
            $installFile = file_exists($installDir."install-local.json") ? "install-local.json" : "install.json";
            $strJsonFileContents = file_get_contents($installDir.$installFile);
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
    <BODY class="compact-tool decoder-tool">
        <div class="decoder-toolbar">
            <div class="decoder-mode-switch" role="tablist" aria-label="<?php echo htmlspecialchars($decoderText['modeLabel'], ENT_QUOTES, 'UTF-8'); ?>">
                <button type="button" class="decoder-mode-button" id="standardModeButton"
                        role="tab" aria-controls="standardDecoderPanel"
                        onclick="setDecoderMode('standard')">Standard</button>
                <button type="button" class="decoder-mode-button" id="batchModeButton"
                        role="tab" aria-controls="batchDecoderPanel"
                        onclick="setDecoderMode('batch')">Batch</button>
            </div>
        </div>
        <DIV class="wtc-panel decoder-panel" id="standardDecoderPanel" role="tabpanel" aria-labelledby="standardModeButton">
            <!-- <IMG SRC="LOGO-NKE-WATTECO-CMJN.png" height=120px width=300px >-->
            <H2><?php echo htmlspecialchars($decoderText['standardTitle'], ENT_QUOTES, 'UTF-8'); ?></H2>
            <form method="get" action="" name="statement_user"  onsubmit="return doBeforeSubmit()">
                <div class="decoder-form-content">
                    <div class="decoder-form-fields">
                        <div class="decoder-form-line">
                            <label for="trame"><?php echo htmlspecialchars($decoderText['frameToDecode'], ENT_QUOTES, 'UTF-8'); ?></label> :
                            <input type="test" name="trame" id="trame" placeholder="" size="100"/>
                        </div>
                        <div class="decoder-form-line">
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
                            <button class="wtc-button wtc-button--primary" type="submit" value="Submit" id="Submit" name=" submit"><?php echo htmlspecialchars($decoderText['decode'], ENT_QUOTES, 'UTF-8'); ?></button>
                        </div>
                    </div>
                    <?php
                        $submited_trame='';
                        if (isset($_GET['trame']))
                        {
                        $user_statement = strip_tags($_GET['trame']);
                        $submited_trame = htmlspecialchars($user_statement, ENT_QUOTES);
                        $user_statement = str_replace(' ','',$user_statement);
                        $pattern = "/[g-z=]/i";

                        // Simple Hack to keep TIC parameter passing for rev option ";rev=xxx"
                        // TODO : Mind about modifying the HMI to allow any parameters in an other text field for example ?
                        $patternRev = "/;[ \t]*rev[ \t]*=[ \t]*[0-9]{4}/i";
                        $withTICRev = preg_match($patternRev, $user_statement);

                        if((isset($_GET['checkbase']) || preg_match($pattern, $user_statement)) && (! $withTICRev)){
                            $submited_checkbase=htmlspecialchars($_GET['checkbase'], ENT_QUOTES);
                            $user_statement = bin2hex(base64_decode($user_statement));
                        }
                        if (isset($_GET['MySelectMenu']))
                        {
                        $submited_MySelectMenu=htmlspecialchars($_GET['MySelectMenu'], ENT_QUOTES);
                        echo( "<hr size=2 align=center width='100%'>");
                        echo('<div class="decoder-result-actions"><span class="shareStatus" role="status" aria-live="polite"></span><button class="wtc-button wtc-button--secondary shareDecoderButton" type="button" onclick="shareDecoderState(this)">' . htmlspecialchars($decoderText['share'], ENT_QUOTES, 'UTF-8') . '</button></div>');

                        $selected_val = strip_tags($_GET['MySelectMenu']);
                        
                        $sf = "";
                        switch ( $selected_val  ) {
                            case "0": // JSON
                                $sf = ' -of json 2>&1';
                                echo( htmlspecialchars($decoderText['decodedFrame'], ENT_QUOTES, 'UTF-8') . " (json) : " . htmlspecialchars($user_statement, ENT_QUOTES) );
                            break;
                            case "1": // XMLP
                                $sf =' -of xmlp 2>&1';
                                echo( htmlspecialchars($decoderText['decodedFrame'], ENT_QUOTES, 'UTF-8') . " (xmlp) : " . htmlspecialchars($user_statement, ENT_QUOTES) );
                            break;
                            case "2": // XMLL
                                $sf =' -of xmll 2>&1';
                                echo( htmlspecialchars($decoderText['decodedFrame'], ENT_QUOTES, 'UTF-8') . " (xmll) : " . htmlspecialchars($user_statement, ENT_QUOTES) );
                            break;
                            case "3": // STD
                                $sf = ' -of std 2>&1';
                                echo( htmlspecialchars($decoderText['decodedFrame'], ENT_QUOTES, 'UTF-8') . " (std) : " . htmlspecialchars($user_statement, ENT_QUOTES) );
                            break;
                            case "4": // JSON-VERIF
                                $sf = ' -of json-verif 2>&1';
                                echo( htmlspecialchars($decoderText['decodedFrame'], ENT_QUOTES, 'UTF-8') . " (json-verif) : " . htmlspecialchars($user_statement, ENT_QUOTES) );
                            break;
                        }

                        $s = escapeshellarg($Python).' '.escapeshellarg($pathPythonStdCodec).' -if '.escapeshellarg($user_statement).$sf;

                        echo( "<br>" );
                        $output = shell_exec( $s );
                        echo( "<br>");
                        // SECURITE : echappement de la sortie avant insertion dans le <textarea>
                        echo( "<textarea rows=25 cols=150 readonly wrap='hard'>" . htmlspecialchars($output, ENT_QUOTES) . "</textarea>" );
                        echo( "<br>");
                        }
                        }
                        ?>
                </div>
            </form>
        </DIV>
        <!--<hr size=5 noshade>-->
        <!--</div>
            <div id="tab2" class="tab">-->
        <DIV class="wtc-panel decoder-panel" id="batchDecoderPanel" role="tabpanel" aria-labelledby="batchModeButton" hidden>
            <H2><?php echo htmlspecialchars($decoderText['batchTitle'], ENT_QUOTES, 'UTF-8'); ?></H2>
            <form method="get" action="" name="statement_user2" onsubmit="return doBeforeSubmit()">
                <div class="decoder-form-content">
                    <div class="decoder-form-fields">
                        <div class="decoder-form-line">
                            <label for="trameBatch"><?php echo htmlspecialchars($decoderText['frameToDecode'], ENT_QUOTES, 'UTF-8'); ?></label> :
                            <input type="text" name="trameBatch" id="trameBatch" placeholder="" size="80"/>
                        </div>
                        <div class="decoder-form-line">
                            <label for="timestamp"><?php echo htmlspecialchars($decoderText['timestamp'], ENT_QUOTES, 'UTF-8'); ?></label> :
                            <input type="text" name="timestamp" id="timestamp" placeholder="yyyy-MM-ddThh:mm:ss.000Z" data-slots="yMdhms" size="30"/>
                            <a class="tooltip">
                                <img src="../Common/help.png" alt=" ? " height=20px width=20px/>
                                <span class="tooltiptext">Format: yyyy-MM-ddTDD:mm:ss.SSSZ</span>
                            </a>
                            <button class="wtc-button wtc-button--secondary" type="button" id="btnNow" name="btnNow" onclick="setTimestamp()"><?php echo htmlspecialchars($decoderText['now'], ENT_QUOTES, 'UTF-8'); ?></button>
                        </div>
                        <div class="decoder-form-line">
                            <label for="productSelect"><?php echo htmlspecialchars($decoderText['batchAttributes'], ENT_QUOTES, 'UTF-8'); ?></label> :
                            <select id="productSelect"></select>
                            <input type="hidden" id="productSelectIndex" name="productSelectIndex">
                        </div>
                        <div class="decoder-form-line">
                            <input type="text" name="BatchAttributes" id="BatchAttributes" aria-label="<?php echo htmlspecialchars($decoderText['batchParameters'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="" size="90"/>
                            <a class="tooltip">
                                <img src="../Common/help.png" alt=" ? " height=20px width=20px/>
                                <span class="tooltiptext">
                                    tagsz "taglbl,resol,sampletype,[lblname]" "..."<br>
                                    <br>
                                    ex: <br>
                                    3 2,10,9 1,10,7 4,30,10 3,10,4 5,10,6 6,1,4 <br>
                                    2 2,10,9,temperature 1,10,7,pressure<br>
                                    <br>
                                    <?php echo htmlspecialchars($decoderText['sampleTypeHelp'], ENT_QUOTES, 'UTF-8'); ?><br>
                                    1: <?php echo htmlspecialchars($decoderText['boolean'], ENT_QUOTES, 'UTF-8'); ?><br>
                                    2: <?php echo htmlspecialchars($decoderText['unsignedInteger'], ENT_QUOTES, 'UTF-8'); ?> U4<br>    3: <?php echo htmlspecialchars($decoderText['signedInteger'], ENT_QUOTES, 'UTF-8'); ?> I4<br>
                                    4: <?php echo htmlspecialchars($decoderText['unsignedInteger'], ENT_QUOTES, 'UTF-8'); ?> U8<br>    5: <?php echo htmlspecialchars($decoderText['signedInteger'], ENT_QUOTES, 'UTF-8'); ?> I8<br>
                                    6: <?php echo htmlspecialchars($decoderText['unsignedInteger'], ENT_QUOTES, 'UTF-8'); ?> U16<br>   7: <?php echo htmlspecialchars($decoderText['signedInteger'], ENT_QUOTES, 'UTF-8'); ?> I16<br>
                                    8: <?php echo htmlspecialchars($decoderText['unsignedInteger'], ENT_QUOTES, 'UTF-8'); ?> U24<br>   9: <?php echo htmlspecialchars($decoderText['signedInteger'], ENT_QUOTES, 'UTF-8'); ?> I24<br>
                                    10: <?php echo htmlspecialchars($decoderText['unsignedInteger'], ENT_QUOTES, 'UTF-8'); ?> U32<br>  11: <?php echo htmlspecialchars($decoderText['signedInteger'], ENT_QUOTES, 'UTF-8'); ?> I32<br>
                                    12: <?php echo htmlspecialchars($decoderText['float'], ENT_QUOTES, 'UTF-8'); ?><br>
                                </span>
                            </a>
                            <label>
                                base 64
                                <input type="checkbox" name="checkbasebatch" id ="checkbasebatch" value="checkox_value">
                            </label>
                            <button class="wtc-button wtc-button--primary" type="submit" value="Submit2" id="Submit2" name=" submit"><?php echo htmlspecialchars($decoderText['decode'], ENT_QUOTES, 'UTF-8'); ?></button>
                        </div>
                    </div>
            
                <?php
                    if (isset($_GET['trameBatch']))
                    {
                    $user_statement_trame = strip_tags($_GET['trameBatch']) ;
                    $submited_trameBatch = htmlspecialchars($user_statement_trame, ENT_QUOTES);
                    $submited_productSelectIndex = htmlspecialchars((string)($_GET['productSelectIndex'] ?? ''), ENT_QUOTES);
                    $user_statement_trame=str_replace(' ','',$user_statement_trame);
                    $user_statement_attributes = strip_tags($_GET['BatchAttributes']) ;
                    $submited_BatchAttributes=htmlspecialchars($user_statement_attributes, ENT_QUOTES);
                    $user_statement_timestamp = strip_tags($_GET['timestamp']) ;
                    $submited_timestamp=htmlspecialchars($user_statement_timestamp, ENT_QUOTES);
                    echo( "<hr size=2 align=center width='100%'>");
                    echo('<div class="decoder-result-actions"><span class="shareStatus" role="status" aria-live="polite"></span><button class="wtc-button wtc-button--secondary shareDecoderButton" type="button" onclick="shareDecoderState(this)">' . htmlspecialchars($decoderText['share'], ENT_QUOTES, 'UTF-8') . '</button></div>');

                    $pattern = "/[g-z=]/i";

                    if(isset($_GET['checkbasebatch']) || preg_match($pattern, $user_statement_trame)){
                        $user_statement_trame = bin2hex(base64_decode($user_statement_trame));
                    }

                    // SECURITE : $user_statement_attributes est une liste d'arguments separes par
                    // des espaces. On la decoupe puis on echappe chaque argument individuellement
                    // pour conserver le format attendu tout en neutralisant l'injection shell.
                    $escapedBatchAttributes = array_map('escapeshellarg', preg_split('/\s+/', trim((string)$user_statement_attributes), -1, PREG_SPLIT_NO_EMPTY));
                    $batchAttributesArg = implode(' ', $escapedBatchAttributes);
                    if(empty($_GET['timestamp']))
                    {
                    $s = escapeshellarg($Python).' '.escapeshellarg($pathPythonBatchDecoder).' -if '.escapeshellarg($user_statement_trame).' -a '.$batchAttributesArg.' 2>&1';
                    }
                    else
                    {
                    $s = escapeshellarg($Python).' '.escapeshellarg($pathPythonBatchDecoder).' -if '.escapeshellarg($user_statement_trame).' -t '.escapeshellarg($user_statement_timestamp).' -a '.$batchAttributesArg.' 2>&1';
                    }
                    echo( htmlspecialchars($decoderText['frameToDecode'], ENT_QUOTES, 'UTF-8') . ": " . htmlspecialchars($user_statement_trame, ENT_QUOTES) );
                    echo( "<br>");
                    echo( htmlspecialchars($decoderText['timestamp'], ENT_QUOTES, 'UTF-8') . ": " . htmlspecialchars($user_statement_timestamp, ENT_QUOTES) );
                    echo( "<br>");
                    echo( htmlspecialchars($decoderText['batchAttributes'], ENT_QUOTES, 'UTF-8') . ": " . htmlspecialchars($user_statement_attributes, ENT_QUOTES) );

                    echo( "<br>" );
                    //echo( "<br>");
                    $output2 = shell_exec( $s );
                    echo( "<br>");
                    // SECURITE : la sortie est echappee avant d'etre placee dans le <textarea>
                    // (un contenu contenant "</textarea><script>..." permettait une XSS)
                    echo( "<textarea rows=25 cols=150 readonly wrap='hard'>" . htmlspecialchars($output2, ENT_QUOTES) . "</textarea>" );
                    echo( "<br>");
                    }
                    ?>
                </div>
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

            function getDecoderShareParams()
            {
                var hash = window.location.hash;

                try {
                    if (window.parent !== window) {
                        hash = window.parent.location.hash;
                    }
                } catch (error) {
                    // Direct iframe use or a cross-origin parent: use the local hash.
                }

                return new URLSearchParams(hash.replace(/^#/, ''));
            }

            function setDecoderMode(mode, persist = true, updateAddress = true)
            {
                var normalizedMode = mode === 'batch' ? 'batch' : 'standard';
                var isStandard = normalizedMode === 'standard';
                var standardButton = document.getElementById('standardModeButton');
                var batchButton = document.getElementById('batchModeButton');

                document.getElementById('standardDecoderPanel').hidden = !isStandard;
                document.getElementById('batchDecoderPanel').hidden = isStandard;

                standardButton.classList.toggle('active', isStandard);
                batchButton.classList.toggle('active', !isStandard);
                standardButton.setAttribute('aria-selected', isStandard ? 'true' : 'false');
                batchButton.setAttribute('aria-selected', isStandard ? 'false' : 'true');
                standardButton.tabIndex = isStandard ? 0 : -1;
                batchButton.tabIndex = isStandard ? -1 : 0;

                document.querySelectorAll('.shareStatus').forEach(function(shareStatus) {
                    shareStatus.textContent = '';
                });

                if (persist) {
                    localStorage.setItem('decoderMode', normalizedMode);
                }

                if (updateAddress) {
                    try {
                        if (window.parent !== window && typeof window.parent.UpdateDecoderMode === 'function') {
                            window.parent.UpdateDecoderMode(normalizedMode);
                        }
                    } catch (error) {
                        // The decoder also remains usable outside the parent page.
                    }
                }
            }

            function getInitialDecoderMode(sharedParams)
            {
                var requestParams = new URLSearchParams(window.location.search);

                if (requestParams.has('trameBatch')) return 'batch';
                if (requestParams.has('trame')) return 'standard';
                if (requestParams.get('mode') === 'batch') return 'batch';
                if (requestParams.get('mode') === 'standard') return 'standard';

                if (sharedParams.get('tool') === 'decoders') {
                    var sharedMode = sharedParams.get('mode');
                    if (sharedMode === 'standard' || sharedMode === 'batch') return sharedMode;
                }

                return localStorage.getItem('decoderMode') === 'batch' ? 'batch' : 'standard';
            }

            function hasDecoderSubmission()
            {
                var requestParams = new URLSearchParams(window.location.search);
                return requestParams.has('trame') || requestParams.has('trameBatch');
            }

            function applySharedDecoderState(sharedParams)
            {
                if (sharedParams.get('tool') !== 'decoders') return;

                var mode = sharedParams.get('mode');
                var frame = sharedParams.get('frame');

                if (mode === 'batch') {
                    if (frame !== null) document.getElementById('trameBatch').value = frame;
                    if (sharedParams.has('timestamp')) document.getElementById('timestamp').value = sharedParams.get('timestamp');
                    if (sharedParams.has('attributes')) {
                        document.getElementById('BatchAttributes').value = sharedParams.get('attributes');
                        restoreSharedBatchProduct(sharedParams.get('attributes'));
                    }
                    document.getElementById('checkbasebatch').checked = sharedParams.get('base64') === '1';
                } else if (mode === 'standard') {
                    if (frame !== null) document.getElementById('trame').value = frame;
                    if (sharedParams.has('format')) document.getElementById('MySelectMenu').value = sharedParams.get('format');
                    document.getElementById('checkbase').checked = sharedParams.get('base64') === '1';
                }
            }

            function restoreSharedBatchProduct(attributes)
            {
                var productSelect = document.getElementById('productSelect');

                function selectMatchingProduct() {
                    if (productSelect.options.length === 0) return false;

                    var matchingIndex = Array.from(productSelect.options).findIndex(function(option) {
                        return option.value === attributes;
                    });
                    productSelect.selectedIndex = matchingIndex >= 0 ? matchingIndex : 0;
                    return true;
                }

                if (selectMatchingProduct()) return;

                var observer = new MutationObserver(function() {
                    if (selectMatchingProduct()) observer.disconnect();
                });
                observer.observe(productSelect, { childList: true, subtree: true });
            }

            function initializeDecoderPage(sharedParams)
            {
                setDecoderMode(getInitialDecoderMode(sharedParams), true, false);
                if (!hasDecoderSubmission()) {
                    applySharedDecoderState(sharedParams);
                } else if (new URLSearchParams(window.location.search).has('trameBatch')) {
                    restoreSharedBatchProduct(document.getElementById('BatchAttributes').value);
                }
                document.querySelectorAll('.shareDecoderButton').forEach(function(shareButton) {
                    shareButton.textContent = lang === 1 ? 'Partager' : 'Share';
                });

                document.querySelector('.decoder-mode-switch').addEventListener('keydown', function(event) {
                    if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;

                    event.preventDefault();
                    var nextMode = document.getElementById('standardDecoderPanel').hidden ? 'standard' : 'batch';
                    setDecoderMode(nextMode);
                    document.getElementById(nextMode + 'ModeButton').focus();
                });

                synchronizeParentDecoderUrl();
            }

            function synchronizeParentDecoderUrl()
            {
                var requestParams = new URLSearchParams(window.location.search);
                if (!requestParams.has('trame') && !requestParams.has('trameBatch')) return;

                try {
                    var parentUrl = new URL('/Lora/', window.location.origin);
                    parentUrl.searchParams.set('tool', 'decoders');
                    parentUrl.searchParams.set('mode', requestParams.has('trameBatch') ? 'batch' : 'standard');
                    requestParams.forEach(function(value, name) {
                        if (name !== 'tool' && name !== 'mode') parentUrl.searchParams.append(name, value);
                    });

                    if (window.parent === window) {
                        window.location.replace(parentUrl.href);
                        return;
                    }
                    if (window.parent.location.origin !== window.location.origin) return;

                    window.parent.history.replaceState(null, '', parentUrl.href);
                } catch (error) {
                    // Direct iframe use or a cross-origin parent: no address to synchronize.
                }
            }

            function addSharedValue(params, name, value)
            {
                if (value !== null && value !== '') params.set(name, value);
            }

            function copyTextFallback(text)
            {
                var textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);

                try {
                    textArea.select();
                    return document.execCommand('copy');
                } catch (error) {
                    return false;
                } finally {
                    textArea.remove();
                }
            }

            async function shareDecoderState(button)
            {
                var mode = document.getElementById('batchDecoderPanel').hidden ? 'standard' : 'batch';
                var params = new URLSearchParams();
                var shareUrl = new URL('/Lora/', window.location.origin);
                var status = button.parentElement.querySelector('.shareStatus');

                params.set('tool', 'decoders');
                params.set('mode', mode);

                if (mode === 'batch') {
                    addSharedValue(params, 'trameBatch', document.getElementById('trameBatch').value.trim());
                    addSharedValue(params, 'timestamp', document.getElementById('timestamp').value.trim());
                    params.set('BatchAttributes', document.getElementById('BatchAttributes').value.trim());
                    params.set(' submit', 'Submit2');
                    if (document.getElementById('checkbasebatch').checked) params.set('checkbasebatch', 'checkox_value');
                } else {
                    addSharedValue(params, 'trame', document.getElementById('trame').value.trim());
                    params.set('MySelectMenu', document.getElementById('MySelectMenu').value);
                    params.set(' submit', 'Submit');
                    if (document.getElementById('checkbase').checked) params.set('checkbase', 'checkox_value');
                }

                shareUrl.search = params.toString();

                try {
                    if (window.parent !== window && window.parent.location.origin === window.location.origin) {
                        window.parent.history.replaceState(null, '', shareUrl.href);
                    }
                } catch (error) {
                    // Copying the canonical URL still works when the parent is unavailable.
                }

                try {
                    await navigator.clipboard.writeText(shareUrl.href);
                    status.textContent = lang === 1 ? 'Lien copié' : 'Link copied';
                } catch (error) {
                    if (copyTextFallback(shareUrl.href)) {
                        status.textContent = lang === 1 ? 'Lien copié' : 'Link copied';
                    } else {
                        window.prompt(lang === 1 ? 'Copiez ce lien :' : 'Copy this link:', shareUrl.href);
                    }
                }
            }

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
			var sharedDecoderParams = getDecoderShareParams();

            <?php
                // SECURITE : les valeurs sont desormais serialisees avec json_encode()
                // plutot que simplement echappees pour du HTML, puis inserees dans le JS.
                // L'ancien code utilisait htmlspecialchars() sans ENT_QUOTES et sans
                // echappement JS dedie : une valeur contenant certains caracteres pouvait
                // casser hors de la chaine de caracteres JavaScript (XSS).
                $jsopt = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
            ?>
            // For standard decoder form
            submited_MySelectMenu=<?php echo json_encode((string)$submited_MySelectMenu, $jsopt); ?>;
            document.getElementById('MySelectMenu').selectedIndex = ((!(submited_MySelectMenu?.trim())) ? localStorage.getItem("MySelectMenuIndex") :submited_MySelectMenu);
            submited_checkbase=<?php echo json_encode((string)$submited_checkbase, $jsopt); ?>;
            document.getElementById('checkbase').value = ((!(submited_checkbase?.trim())) ? localStorage.getItem("checkbaseValue") :submited_checkbase);
            submited_trame=<?php echo json_encode((string)$submited_trame, $jsopt); ?>;
            document.getElementById('trame').value = ((!(submited_trame?.trim())) ? localStorage.getItem("trameValue") :submited_trame);

            // For batch decoder form
            submited_productSelectIndex=<?php echo json_encode((string)$submited_productSelectIndex, $jsopt); ?>;
			var initialProductIndex = ((!(submited_productSelectIndex?.trim())) ? localStorage.getItem("productSelectIndex") : submited_productSelectIndex);
			getAllAvailableProducts(initialProductIndex);

            submited_BatchAttributes=<?php echo json_encode((string)$submited_BatchAttributes, $jsopt); ?>;
            document.getElementById('BatchAttributes').value = ((!(submited_BatchAttributes?.trim())) ? localStorage.getItem("BatchAttributesValue") :submited_BatchAttributes);
            
            submited_trameBatch=<?php echo json_encode((string)$submited_trameBatch, $jsopt); ?>;
            document.getElementById('trameBatch').value = ((!(submited_trameBatch?.trim())) ? localStorage.getItem("trameBatchValue") :submited_trameBatch);

            submited_timestamp=<?php echo json_encode((string)$submited_timestamp, $jsopt); ?>;
            document.getElementById('timestamp').value = ((!(submited_timestamp?.trim())) ? localStorage.getItem("timestampValue") :submited_timestamp);
            document.getElementById('timestamp').value = localStorage.getItem("timestampValue");

            SetInputMaskMngt();
			initializeDecoderPage(sharedDecoderParams);
		</script>
    </BODY>
</HTML>
