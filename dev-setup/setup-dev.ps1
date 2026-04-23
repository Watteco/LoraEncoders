#Requires -Version 5.1
<#
.SYNOPSIS
    Configure l'environnement de développement local pour LoRaEncoders.
    
.DESCRIPTION
    Ce script:
      - Installe Python si absent (best effort via winget)
      - Clone les repos frères manquants (Codec-Report-Standard-Python, Codec-Report-Batch-Python)
      - Normalise le nom du dossier courant en "Lora" si nécessaire (via relance externe)
      - Crée un venv Python dans .venv/
      - Installe dicttoxml et construct depuis les sources locales vendoriées
      - Génère install-local.json
      - Valide l'environnement

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File .\dev-setup\setup-dev.ps1

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File .\dev-setup\setup-dev.ps1 -SkipXamppSetup
#>

param(
    [switch]$SkipXamppSetup
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

# ─── Helpers ────────────────────────────────────────────────────────────────

function Write-Step   { param($msg) Write-Host "`n>>  $msg" -ForegroundColor Cyan }
function Write-OK     { param($msg) Write-Host "   OK  $msg" -ForegroundColor Green }
function Write-Warn   { param($msg) Write-Host "   !!  $msg" -ForegroundColor Yellow }
function Write-Fail   { param($msg) Write-Host "`nERR  $msg" -ForegroundColor Red; exit 1 }

# ─── 0. Gestion renommage vers "Lora" ────────────────────────────────────────

$scriptDir   = Split-Path -Parent $MyInvocation.MyCommand.Path
$repoDir     = (Resolve-Path (Join-Path $scriptDir "..")).Path
$repoName    = Split-Path -Leaf $repoDir
$parentDir   = Split-Path -Parent $repoDir
$loraDir     = Join-Path $parentDir "Lora"

if ($repoName -ne "Lora") {
    Write-Step "Renommage du dossier '$repoName' -> 'Lora'"

    if (Test-Path $loraDir) {
        Write-Fail "Un dossier 'Lora' existe déjà dans '$parentDir'. Supprimez-le ou renommez-le avant de relancer."
    }

    # Relance depuis le parent (on ne peut pas renommer le cwd)
    $helperBlock = @"
Rename-Item -Path '$repoDir' -NewName 'Lora'
Write-Host "Renommage effectué. Relance du setup..."
& powershell -ExecutionPolicy Bypass -File '$loraDir\dev-setup\setup-dev.ps1'
"@
    $helperFile = Join-Path $parentDir "_lora_rename_helper.ps1"
    $helperBlock | Set-Content -Path $helperFile -Encoding UTF8

    Start-Process powershell -ArgumentList "-ExecutionPolicy Bypass -File `"$helperFile`"" -WorkingDirectory $parentDir
    Write-Host "`nUn nouveau terminal a été lancé pour effectuer le renommage et poursuivre le setup.`nCe terminal peut être fermé." -ForegroundColor Yellow
    exit 0
}

Write-OK "Dossier repo : $repoDir"

# ─── 1. Vérification / installation de Git ───────────────────────────────────

Write-Step "Vérification de Git"
if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
    Write-Warn "Git non trouvé. Tentative d'installation via winget..."
    if (Get-Command winget -ErrorAction SilentlyContinue) {
        winget install --id Git.Git -e --source winget --silent
        $env:PATH = [System.Environment]::GetEnvironmentVariable("PATH","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("PATH","User")
        if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
            Write-Fail "Git installé mais non détecté dans le PATH. Relancez ce script dans un nouveau terminal."
        }
        Write-OK "Git installé"
    } else {
        Write-Fail "winget indisponible. Installez Git manuellement : https://git-scm.com/download/win`nRelancez ce script ensuite."
    }
} else {
    Write-OK "Git $(git --version)"
}

# ─── 2. Clonage des repos frères ─────────────────────────────────────────────

Write-Step "Vérification des repos frères"

$stdRepo   = "https://github.com/Watteco/Codec-Report-Standard-Python.git"
$batchRepo = "https://github.com/Watteco/Codec-Report-Batch-Python.git"
$stdDir    = Join-Path $parentDir "Codec-Report-Standard-Python"
$batchDir  = Join-Path $parentDir "Codec-Report-Batch-Python"

foreach ($entry in @(
    @{ Dir = $stdDir;   Url = $stdRepo;   Name = "Codec-Report-Standard-Python" },
    @{ Dir = $batchDir; Url = $batchRepo; Name = "Codec-Report-Batch-Python"    }
)) {
    if (Test-Path (Join-Path $entry.Dir ".git")) {
        Write-OK "$($entry.Name) déjà présent"
    } else {
        if (Test-Path $entry.Dir) {
            Write-Warn "$($entry.Name) : dossier présent mais n'est pas un repo git valide. Vérifiez manuellement : $($entry.Dir)"
        } else {
            Write-Host "   Clonage de $($entry.Name)..." -ForegroundColor Gray
            git clone $entry.Url $entry.Dir
            if ($LASTEXITCODE -ne 0) { Write-Fail "Échec du clone de $($entry.Name)" }
            Write-OK "$($entry.Name) cloné"
        }
    }
}

# ─── 3. Vérification / installation de Python ────────────────────────────────

Write-Step "Vérification de Python"

function Find-Python {
    foreach ($cmd in @("py", "python", "python3")) {
        $p = Get-Command $cmd -ErrorAction SilentlyContinue
        if ($p) {
            $ver = & $cmd --version 2>&1
            if ($ver -match "Python (\d+)\.(\d+)") {
                $maj = [int]$Matches[1]; $min = [int]$Matches[2]
                if ($maj -gt 3 -or ($maj -eq 3 -and $min -ge 7)) {
                    return $p.Source
                }
            }
        }
    }
    return $null
}

$pythonExe = Find-Python

if (-not $pythonExe) {
    Write-Warn "Python >= 3.7 non trouvé. Tentative d'installation via winget..."
    if (Get-Command winget -ErrorAction SilentlyContinue) {
        winget install --id Python.Python.3.11 -e --source winget --silent
        $env:PATH = [System.Environment]::GetEnvironmentVariable("PATH","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("PATH","User")
        $pythonExe = Find-Python
        if (-not $pythonExe) {
            Write-Fail "Python installé mais non détecté. Relancez ce script dans un nouveau terminal."
        }
        Write-OK "Python installé : $pythonExe"
    } else {
        Write-Fail "winget indisponible. Installez Python >= 3.7 manuellement : https://www.python.org/downloads/`nRelancez ce script ensuite."
    }
} else {
    $ver = & $pythonExe --version 2>&1
    Write-OK "Python : $ver ($pythonExe)"
}

# ─── 4. Venv ─────────────────────────────────────────────────────────────────

Write-Step "Création/vérification du venv (.venv)"
$venvDir  = Join-Path $repoDir ".venv"
$venvPy   = Join-Path $venvDir "Scripts\python.exe"

if (-not (Test-Path $venvPy)) {
    & $pythonExe -m venv $venvDir
    if ($LASTEXITCODE -ne 0) { Write-Fail "Échec création du venv" }
    Write-OK "venv créé"
} else {
    Write-OK "venv existant"
}

# Upgrade pip/setuptools/wheel
Write-Host "   Mise à jour pip, setuptools, wheel..." -ForegroundColor Gray
& $venvPy -m pip install --upgrade pip setuptools wheel --quiet
if ($LASTEXITCODE -ne 0) { Write-Fail "Échec mise à jour pip" }
Write-OK "pip à jour"

# ─── 5. Installation dicttoxml et construct ───────────────────────────────────

Write-Step "Installation des dépendances Python locales"

$dicttoxmlDir = Join-Path $stdDir "dicttoxml-1.7.4"
$constructDir = Join-Path $stdDir "construct-2.8.12"

foreach ($entry in @(
    @{ Dir = $dicttoxmlDir; Name = "dicttoxml" },
    @{ Dir = $constructDir; Name = "construct"  }
)) {
    if (-not (Test-Path (Join-Path $entry.Dir "setup.py"))) {
        Write-Fail "Source $($entry.Name) introuvable dans : $($entry.Dir)"
    }
    Write-Host "   Installation de $($entry.Name)..." -ForegroundColor Gray
    & $venvPy -m pip install $entry.Dir --quiet
    if ($LASTEXITCODE -ne 0) { Write-Fail "Échec installation $($entry.Name)" }
    Write-OK "$($entry.Name) installé"
}

# ─── 6. Génération install-local.json ────────────────────────────────────────

Write-Step "Génération de install-local.json"

$installLocal = Join-Path $repoDir "install-local.json"
$config = [ordered]@{
    pathPython           = $venvPy -replace "\\", "/"
    pathPythonStdCodec   = "../Codec-Report-Standard-Python/srcWatteco/Main.py"
    pathPythonBatchDecoder = "../Codec-Report-Batch-Python/br_uncompress.py"
}
$config | ConvertTo-Json | Set-Content -Path $installLocal -Encoding UTF8
Write-OK "install-local.json écrit : $installLocal"

# ─── 7. Validation ───────────────────────────────────────────────────────────

Write-Step "Validation de l'environnement"

# Import Python libs
& $venvPy -c "import construct; import dicttoxml" 2>&1 | Out-Null
if ($LASTEXITCODE -ne 0) { Write-Fail "Import construct/dicttoxml échoué" }
Write-OK "Imports Python OK (construct, dicttoxml)"

# Main.py -h
$mainPy = Join-Path $stdDir "srcWatteco\Main.py"
$out = & $venvPy $mainPy -h 2>&1
if ($LASTEXITCODE -ne 0) { Write-Warn "Main.py -h a retourné une erreur (non bloquant)" }
else { Write-OK "Standard codec (Main.py) : OK" }

# br_uncompress.py -h
$brPy = Join-Path $batchDir "br_uncompress.py"
$out = & $venvPy $brPy -h 2>&1
if ($LASTEXITCODE -ne 0) { Write-Warn "br_uncompress.py -h a retourné une erreur (non bloquant)" }
else { Write-OK "Batch codec (br_uncompress.py) : OK" }

# ─── 8. Configuration XAMPP (auto par défaut) ───────────────────────────────

if ($SkipXamppSetup) {
    Write-Warn "Configuration XAMPP ignorée (option -SkipXamppSetup)."
} else {
    Write-Step "Configuration XAMPP/Apache (automatique)"
    $xamppSetup = Join-Path $repoDir "dev-setup\setup-xampp.ps1"
    if (Test-Path $xamppSetup) {
        try {
            & powershell -ExecutionPolicy Bypass -File $xamppSetup
            if ($LASTEXITCODE -eq 0) {
                Write-OK "Configuration XAMPP terminée"
            } else {
                Write-Warn "setup-xampp.ps1 a retourné un code $LASTEXITCODE. Vous pouvez le relancer manuellement."
            }
        }
        catch {
            Write-Warn "Échec de la configuration XAMPP automatique : $($_.Exception.Message)"
            Write-Warn "Relance manuelle possible : powershell -ExecutionPolicy Bypass -File .\dev-setup\setup-xampp.ps1"
        }
    } else {
        Write-Warn "dev-setup\setup-xampp.ps1 introuvable."
    }
}

# ─── 9. Résumé final ─────────────────────────────────────────────────────────

Write-Host ""
Write-Host "=========================================================" -ForegroundColor Green
  Write-Host "  OK  Environnement de dev pret !" -ForegroundColor Green
  Write-Host "=========================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  URL locale (apres demarrage Apache) :" -ForegroundColor White
Write-Host "    -> http://localhost/Lora/" -ForegroundColor Yellow
Write-Host ""
