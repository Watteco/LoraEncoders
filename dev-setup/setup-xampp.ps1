#Requires -Version 5.1
<#
.SYNOPSIS
    Configure Apache/XAMPP pour servir le site LoRaEncoders en local.

.DESCRIPTION
    Ce script:
      - Détecte l'installation XAMPP (ou utilise le chemin fourni en paramètre)
      - Installe XAMPP si absent et si le flag -InstallIfMissing est spécifié
      - Ajoute de manière idempotente dans httpd.conf :
          * le bloc <Directory> pour le dossier Lora
          * l'AliasMatch case-insensitive vers /Lora/
      - Valide la configuration Apache (httpd.exe -t)
      - Affiche les instructions de relance Apache

.PARAMETER XamppPath
    Chemin racine de l'installation XAMPP. Défaut : C:\xampp

.PARAMETER InstallIfMissing
    Si spécifié, tente d'installer XAMPP via winget si introuvable.

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File .\dev-setup\setup-xampp.ps1

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File .\dev-setup\setup-xampp.ps1 -XamppPath "D:\xampp"

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File .\dev-setup\setup-xampp.ps1 -InstallIfMissing
#>

param(
    [string]$XamppPath = "C:\xampp",
    [switch]$InstallIfMissing
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

# ─── Helpers ─────────────────────────────────────────────────────────────────

function Write-Step { param($msg) Write-Host "`n>>  $msg" -ForegroundColor Cyan }
function Write-OK   { param($msg) Write-Host "   OK  $msg" -ForegroundColor Green }
function Write-Warn { param($msg) Write-Host "   !!  $msg" -ForegroundColor Yellow }
function Write-Fail { param($msg) Write-Host "`nERR  $msg" -ForegroundColor Red; exit 1 }

# ─── Résolution du dossier Lora ──────────────────────────────────────────────

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$loraDir   = (Resolve-Path (Join-Path $scriptDir "..")).Path

if ((Split-Path -Leaf $loraDir) -ne "Lora") {
    Write-Fail "Ce script doit être exécuté depuis un dossier nommé 'Lora'.`nLancez d'abord dev-setup\setup-dev.ps1."
}

# ─── 1. Détection / installation XAMPP ───────────────────────────────────────

Write-Step "Détection de XAMPP"

$httpdConf = Join-Path $XamppPath "apache\conf\httpd.conf"
$httpdExe  = Join-Path $XamppPath "apache\bin\httpd.exe"

if (-not (Test-Path $httpdConf)) {
    if ($InstallIfMissing) {
        Write-Warn "XAMPP non trouvé dans '$XamppPath'. Tentative d'installation via winget..."
        if (-not (Get-Command winget -ErrorAction SilentlyContinue)) {
            Write-Fail "winget indisponible. Installez XAMPP manuellement : https://www.apachefriends.org/`nRelancez ce script ensuite."
        }
        winget install --id ApacheFriends.Xampp.8.2 -e --source winget
        if (-not (Test-Path $httpdConf)) {
            Write-Fail "XAMPP installé mais httpd.conf non trouvé dans '$XamppPath'.`nVérifiez le chemin avec -XamppPath."
        }
        Write-OK "XAMPP installé"
    } else {
        Write-Fail "httpd.conf introuvable : $httpdConf`n`nOptions :`n  - Spécifiez le bon chemin : -XamppPath `"D:\xampp`"`n  - Lancez avec -InstallIfMissing pour installer XAMPP automatiquement`n  - Téléchargez XAMPP : https://www.apachefriends.org/"
    }
} else {
    Write-OK "XAMPP trouvé : $XamppPath"
}

# ─── 2. Backup httpd.conf ─────────────────────────────────────────────────────

Write-Step "Backup de httpd.conf"
$backupPath = "$httpdConf.bak_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
Copy-Item $httpdConf $backupPath
Write-OK "Backup : $backupPath"

# ─── 3. Injection idempotente dans httpd.conf ────────────────────────────────

Write-Step "Configuration Apache pour /Lora/"

$confContent = Get-Content $httpdConf -Raw

# Marqueurs pour détecter si déjà configuré
$markerDir   = "# [Watteco-LoRa] Directory block"
$markerAlias = "# [Watteco-LoRa] AliasMatch block"

$loraPath = $loraDir -replace "\\", "/"   # Apache préfère les slashes

# ── Bloc <Directory> ──────────────────────────────────────

if ($confContent -match [regex]::Escape($markerDir)) {
    Write-OK "<Directory> déjà configuré (skip)"
} else {
    $dirBlock = @"

$markerDir
<Directory "$loraDir">
    Options Indexes FollowSymLinks Includes ExecCGI
    AllowOverride All
    Require all granted
</Directory>
"@
    # On injecte après le bloc Directory DocumentRoot existant, en fin de fichier sinon
    if ($confContent -match '(?s)(<Directory "[^"]*">\s*Options Indexes[^<]*</Directory>)') {
        $confContent = $confContent -replace '(?s)(<Directory "[^"]*">\s*Options Indexes[^<]*</Directory>)', "`$1$dirBlock"
    } else {
        $confContent += $dirBlock
    }
    Write-OK "Bloc <Directory> ajouté"
}

# ── AliasMatch ────────────────────────────────────────────

if ($confContent -match [regex]::Escape($markerAlias)) {
    Write-OK "AliasMatch déjà configuré (skip)"
} else {
    $aliasBlock = @"

$markerAlias
AliasMatch (?i)^/Lora/(.*)$ "$loraDir/`$1"
"@
    # On injecte dans le bloc IfModule alias_module s'il existe, sinon à la fin
    if ($confContent -match '<IfModule alias_module>') {
        $confContent = $confContent -replace '(<IfModule alias_module>)', "`$1$aliasBlock"
    } else {
        $confContent += "`n<IfModule alias_module>$aliasBlock`n</IfModule>`n"
    }
    Write-OK "AliasMatch ajouté"
}

Set-Content $httpdConf -Value $confContent -Encoding UTF8 -NoNewline

# ─── 4. Validation Apache ────────────────────────────────────────────────────

Write-Step "Validation de la configuration Apache"

if (-not (Test-Path $httpdExe)) {
    Write-Warn "httpd.exe introuvable : $httpdExe - validation manuelle requise"
} else {
    $result = & $httpdExe -t 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-OK "Configuration Apache valide (Syntax OK)"
    } else {
        Write-Host $result -ForegroundColor Red
        Write-Warn "Erreur de configuration Apache. Le backup est disponible : $backupPath"
        Write-Fail "Correction requise dans : $httpdConf"
    }
}

# ─── 5. Résumé ───────────────────────────────────────────────────────────────

Write-Host ""
Write-Host "=========================================================" -ForegroundColor Green
  Write-Host "  OK  Configuration Apache terminee !" -ForegroundColor Green
  Write-Host "=========================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Redémarrez Apache pour appliquer les changements :" -ForegroundColor White
  Write-Host "    -> Via le panneau XAMPP Control Panel (recommande)" -ForegroundColor Yellow
  Write-Host "    -> Ou : net stop Apache2.4 && net start Apache2.4" -ForegroundColor Yellow
  Write-Host ""
  Write-Host "  URL du site :" -ForegroundColor White
  Write-Host "    -> http://localhost/Lora/" -ForegroundColor Yellow
Write-Host ""
