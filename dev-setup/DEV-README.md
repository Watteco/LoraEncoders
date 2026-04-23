# Installation de l'environnement de développement local

## Prérequis minimaux

- Windows 10/11
- Accès internet (pour les clones git et installations)
- PowerShell 5.1 ou supérieur (inclus par défaut sous Windows)

> Les scripts installent automatiquement Git, Python et configurent XAMPP.
> Voir ci-dessous selon votre point de départ.

---

## Option A — Départ zéro (rien d'installé)

Téléchargez ou copiez `dev-setup/bootstrap-all.ps1` sur votre machine, puis lancez :

```powershell
powershell -ExecutionPolicy Bypass -File bootstrap-all.ps1
```

Ce script :
1. Installe **Git** si absent (via `winget`)
2. Clone le repo `LoraEncoders`
3. Enchaîne automatiquement sur le setup interne (Option B)

Si `winget` est indisponible (entreprise, machine restreinte) :
- Installez Git manuellement : https://git-scm.com/download/win
- Puis clonez : `git clone https://github.com/Watteco/LoraEncoders.git`
- Et continuez avec Option B

---

## Option B — Après clone de LoraEncoders

Depuis le dossier cloné (peu importe son nom) :

```powershell
powershell -ExecutionPolicy Bypass -File .\dev-setup\setup-dev.ps1
```

Ce script :
1. Renomme automatiquement le dossier en `Lora` si nécessaire
2. Clone les 2 repos Python frères dans le dossier parent :
   - `Codec-Report-Standard-Python`
   - `Codec-Report-Batch-Python`
3. Installe **Python 3.11** si absent (via `winget`)
4. Crée un environnement virtuel Python dans `.venv/`
5. Installe `dicttoxml` et `construct` depuis les sources locales vendoriées
6. Génère `install-local.json` avec les chemins locaux corrects
7. Valide l'environnement (imports + test des 2 codecs)

> **Structure attendue après le script :**
> ```
> <dossier_parent>/
>   Lora/                          ← repo LoraEncoders (renommé)
>   Codec-Report-Standard-Python/
>   Codec-Report-Batch-Python/
> ```

---

## Option C — Configuration XAMPP/Apache

Si XAMPP n'est pas encore configuré pour servir le site :

```powershell
powershell -ExecutionPolicy Bypass -File .\dev-setup\setup-xampp.ps1
```

Options disponibles :
```powershell
# Chemin XAMPP par défaut (C:\xampp)
.\dev-setup\setup-xampp.ps1

# Chemin XAMPP personnalisé
.\dev-setup\setup-xampp.ps1 -XamppPath "D:\xampp"

# Installation automatique de XAMPP si absent
.\dev-setup\setup-xampp.ps1 -InstallIfMissing
```

Ce script :
1. Détecte l'installation XAMPP (ou la tente via `winget` avec `-InstallIfMissing`)
2. Sauvegarde `httpd.conf` avant toute modification
3. Ajoute de manière **idempotente** dans `httpd.conf` :
   - Le bloc `<Directory>` autorisant le dossier `Lora`
   - L'alias case-insensitive `AliasMatch (?i)^/Lora/(.*)$`
4. Valide la configuration Apache (`httpd.exe -t`)

Si XAMPP est absent et que `winget` est indisponible, installez-le manuellement :
- https://www.apachefriends.org/
- Puis relancez `setup-xampp.ps1`

### Configuration manuelle équivalente

Si vous préférez modifier `httpd.conf` manuellement, ajoutez :

```apache
# --- Watteco LoRa : bloc Directory ---
<Directory "C:/chemin/vers/Lora">
    Options Indexes FollowSymLinks Includes ExecCGI
    AllowOverride All
    Require all granted
</Directory>
```

Et dans `<IfModule alias_module>` :

```apache
# --- Watteco LoRa : AliasMatch (case insensitive) ---
AliasMatch (?i)^/Lora/(.*)$ "C:/chemin/vers/Lora/$1"
```

---

## Après l'installation

1. Démarrez Apache depuis le **XAMPP Control Panel**
2. Ouvrez : http://localhost/Lora/

---

## Dev vs Prod

| Contexte | Fichier de config lu |
|----------|----------------------|
| Dev local | `install-local.json` (généré par le script, ignoré par git) |
| Serveur/prod | `install.json` (commité, chemins serveur) |

Le fallback est automatique : si `install-local.json` est absent, `install.json` est utilisé.

---

## Dépannage

| Symptôme | Solution |
|----------|----------|
| `winget` absent | Mettre à jour Windows ou installer App Installer depuis le Microsoft Store |
| Erreur de politique PowerShell | Lancer avec `-ExecutionPolicy Bypass` |
| Dossier `Lora` déjà existant (conflit) | Renommer ou supprimer le dossier conflictuel avant de relancer |
| Apache refuse de démarrer après config | Restaurer le backup `httpd.conf.bak_*` dans `C:\xampp\apache\conf\` |
| Erreur d'import Python (`construct`, `dicttoxml`) | Relancer `setup-dev.ps1` ; vérifier que `Codec-Report-Standard-Python` est bien cloné |
