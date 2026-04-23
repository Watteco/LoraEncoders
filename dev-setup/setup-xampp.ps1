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

# Nettoyage des blocs managés existants (pour mise à jour idempotente)
$confContent = [regex]::Replace(
    $confContent,
    '(?ms)\r?\n?[ \t]*# \[Watteco-LoRa\] Directory block\r?\n[ \t]*<Directory "[^"]+">.*?</Directory>\r?\n?',
    "`n"
)
$confContent = [regex]::Replace(
    $confContent,
    '(?ms)\r?\n?[ \t]*# \[Watteco-LoRa\] AliasMatch block\r?\n[ \t]*AliasMatch .*?\r?\n',
    "`n"
)

# Nettoyage des anciens blocs legacy non managés (historique setup manuel)
$confContent = [regex]::Replace(
    $confContent,
    '(?ms)\r?\n?[ \t]*# Added directory below that contains the Lora webserver files\r?\n[ \t]*<Directory "[^"]+">.*?</Directory>\r?\n?',
    "`n"
)
$confContent = [regex]::Replace(
    $confContent,
    '(?ms)\r?\n?[ \t]*# Set Alias for Lora server \(case-insensitive\)\r?\n[ \t]*AliasMatch \(\?i\)\^/Lora/\(\.\*\)\$ "[^"]*"\r?\n?',
    "`n"
)

# Nettoyage de toute autre ligne AliasMatch /Lora potentiellement dupliquée
$confContent = [regex]::Replace(
    $confContent,
    '(?m)^[ \t]*AliasMatch \(\?i\)\^/Lora/\(\.\*\)\$ "[^"]*"\r?\n',
    ""
)

# ── Bloc <Directory> ──────────────────────────────────────

$dirBlock = "`r`n$markerDir`r`n<Directory `"$loraPath`">`r`n    Options Indexes FollowSymLinks Includes ExecCGI`r`n    AllowOverride All`r`n    Require all granted`r`n</Directory>`r`n"

# On injecte après le bloc <Directory> associé à DocumentRoot, en fin de fichier sinon
$docRootDirPattern = '(?s)(DocumentRoot\s+"[^"]+"\r?\n\s*<Directory\s+"[^"]+">.*?</Directory>)'
if ($confContent -match $docRootDirPattern) {
    $confContent = [regex]::Replace($confContent, $docRootDirPattern, "`$1$dirBlock", 1)
} else {
    $confContent += $dirBlock
}
Write-OK "Bloc <Directory> ajouté/mis à jour"

# ── AliasMatch ────────────────────────────────────────────

$aliasBlock = "`r`n$markerAlias`r`nAliasMatch (?i)^/Lora/(.*)$ `"$loraPath/`$1`"`r`n"

# On injecte dans le bloc IfModule alias_module s'il existe, sinon à la fin
if ($confContent -match '<IfModule alias_module>\r?\n') {
    $aliasModuleMatch = [regex]::Match($confContent, '<IfModule alias_module>\r?\n')
    $insertPos = $aliasModuleMatch.Index + $aliasModuleMatch.Length
    $confContent = $confContent.Insert($insertPos, $aliasBlock)
} else {
    $confContent += "`n<IfModule alias_module>`n$aliasBlock`n</IfModule>`n"
}
Write-OK "AliasMatch ajouté/mis à jour"

Set-Content $httpdConf -Value $confContent -Encoding UTF8 -NoNewline

# ─── 4. Validation Apache ────────────────────────────────────────────────────

Write-Step "Validation de la configuration Apache"

if (-not (Test-Path $httpdExe)) {
    Write-Warn "httpd.exe introuvable : $httpdExe - validation manuelle requise"
} else {
    $tmpOut = Join-Path $env:TEMP ("lora_httpd_out_{0}.log" -f [guid]::NewGuid().ToString("N"))
    $tmpErr = Join-Path $env:TEMP ("lora_httpd_err_{0}.log" -f [guid]::NewGuid().ToString("N"))
    try {
        $proc = Start-Process -FilePath $httpdExe -ArgumentList "-t" -NoNewWindow -Wait -PassThru -RedirectStandardOutput $tmpOut -RedirectStandardError $tmpErr
        $stdout = ""
        $stderr = ""
        if (Test-Path $tmpOut) { $stdout = [string](Get-Content $tmpOut -Raw -ErrorAction SilentlyContinue) }
        if (Test-Path $tmpErr) { $stderr = [string](Get-Content $tmpErr -Raw -ErrorAction SilentlyContinue) }
        if ($null -eq $stdout) { $stdout = "" }
        if ($null -eq $stderr) { $stderr = "" }

        if ($proc.ExitCode -eq 0) {
            if ($stdout.Trim().Length -gt 0) { Write-Host $stdout.Trim() -ForegroundColor DarkGray }
            if ($stderr.Trim().Length -gt 0) { Write-Host $stderr.Trim() -ForegroundColor DarkGray }
            Write-OK "Configuration Apache valide (Syntax OK)"
        } else {
            if ($stdout.Trim().Length -gt 0) { Write-Host $stdout.Trim() -ForegroundColor Red }
            if ($stderr.Trim().Length -gt 0) { Write-Host $stderr.Trim() -ForegroundColor Red }
            Write-Warn "Erreur de configuration Apache. Le backup est disponible : $backupPath"
            Write-Fail "Correction requise dans : $httpdConf"
        }
    }
    finally {
        Remove-Item $tmpOut, $tmpErr -ErrorAction SilentlyContinue
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
