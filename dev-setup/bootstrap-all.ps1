#Requires -Version 5.1
<#
.SYNOPSIS
    Bootstrap complet : installe Git si nécessaire, clone LoraEncoders, puis lance le setup dev.

.DESCRIPTION
    Ce script est destiné à être lancé depuis n'importe quel dossier, sans prérequis.
    Il effectue :
      1. Vérification / installation de Git (best effort via winget)
      2. Clone du repo LoraEncoders dans le dossier cible
      3. Lancement automatique de dev-setup/setup-dev.ps1

    Usage typique : copier ce script et le lancer sur une machine vierge.

.PARAMETER TargetDir
    Dossier dans lequel cloner les repos. Défaut : dossier courant.

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File bootstrap-all.ps1

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File bootstrap-all.ps1 -TargetDir "C:\dev\watteco"
#>

param(
    [string]$TargetDir = (Get-Location).Path
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

# ─── Helpers ─────────────────────────────────────────────────────────────────

function Write-Step { param($msg) Write-Host "`n>>  $msg" -ForegroundColor Cyan }
function Write-OK   { param($msg) Write-Host "   OK  $msg" -ForegroundColor Green }
function Write-Warn { param($msg) Write-Host "   !!  $msg" -ForegroundColor Yellow }
function Write-Fail { param($msg) Write-Host "`nERR  $msg" -ForegroundColor Red; exit 1 }

$loraEncodersUrl = "https://github.com/Watteco/LoraEncoders.git"

Write-Host ""
Write-Host "=========================================================" -ForegroundColor Cyan
  Write-Host "  LoRaEncoders - Bootstrap complet"                          -ForegroundColor Cyan
  Write-Host "=========================================================" -ForegroundColor Cyan

# ─── 1. Vérification / installation de Git ───────────────────────────────────

Write-Step "Vérification de Git"
if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
    Write-Warn "Git non trouvé. Tentative d'installation via winget..."
    if (-not (Get-Command winget -ErrorAction SilentlyContinue)) {
        Write-Fail "winget indisponible et Git absent.`nInstallez Git manuellement : https://git-scm.com/download/win`nRelancez ce script ensuite."
    }
    winget install --id Git.Git -e --source winget --silent
    # Rafraîchir le PATH
    $env:PATH = [System.Environment]::GetEnvironmentVariable("PATH","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("PATH","User")
    if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
        Write-Fail "Git installé mais non détecté dans le PATH.`nOuvrez un nouveau terminal et relancez ce script."
    }
    Write-OK "Git installé"
} else {
    Write-OK "Git $(git --version)"
}

# ─── 2. Préparation du dossier cible ─────────────────────────────────────────

Write-Step "Préparation du dossier cible : $TargetDir"

if (-not (Test-Path $TargetDir)) {
    New-Item -ItemType Directory -Path $TargetDir | Out-Null
    Write-OK "Dossier créé : $TargetDir"
} else {
    Write-OK "Dossier existant : $TargetDir"
}

# ─── 3. Clone de LoraEncoders ────────────────────────────────────────────────

Write-Step "Clonage de LoraEncoders"

# On accepte "Lora" ou "LoraEncoders" comme nom cible existant
$loraDir   = Join-Path $TargetDir "Lora"
$encodersDir = Join-Path $TargetDir "LoraEncoders"

if (Test-Path (Join-Path $loraDir ".git")) {
    Write-OK "Repo déjà présent sous 'Lora' : $loraDir"
    $cloneDir = $loraDir
} elseif (Test-Path (Join-Path $encodersDir ".git")) {
    Write-OK "Repo déjà présent sous 'LoraEncoders' : $encodersDir"
    $cloneDir = $encodersDir
} else {
    $cloneDir = $encodersDir
    Write-Host "   Clonage dans $cloneDir ..." -ForegroundColor Gray
    git clone $loraEncodersUrl $cloneDir
    if ($LASTEXITCODE -ne 0) { Write-Fail "Échec du clone de LoraEncoders" }
    Write-OK "LoraEncoders cloné"
}

# ─── 4. Lancement du setup interne ───────────────────────────────────────────

Write-Step "Lancement de setup-dev.ps1"

$setupScript = Join-Path $cloneDir "dev-setup\setup-dev.ps1"
if (-not (Test-Path $setupScript)) {
    Write-Fail "dev-setup\setup-dev.ps1 introuvable dans $cloneDir.`nLe clone est peut-être incomplet."
}

& powershell -ExecutionPolicy Bypass -File $setupScript
