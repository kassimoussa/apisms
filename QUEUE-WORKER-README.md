# 🚀 Configuration automatique du Queue Worker

Ce système configure automatiquement le queue worker Laravel pour traiter les SMS bulk en arrière-plan sur **macOS**, **Linux** et **Windows**.

## 📋 Installation rapide

### Étape 1: Lancement du script d'installation

```bash
# Rendre le script exécutable
chmod +x queue-worker-setup.sh

# Lancer l'installation
./queue-worker-setup.sh
```

### Étape 2: Configuration spécifique par OS

#### 🍎 macOS
Le script configure automatiquement un service **launchd** qui démarre au boot.

**Commandes utiles:**
```bash
# Vérifier le statut
launchctl list | grep apisms

# Arrêter le service
launchctl unload ~/Library/LaunchAgents/com.apisms.queueworker.plist

# Redémarrer le service
launchctl load ~/Library/LaunchAgents/com.apisms.queueworker.plist
```

#### 🐧 Linux
Le script génère un fichier service **systemd**. Installation manuelle requise :

```bash
# Copier le service (en tant qu'admin)
sudo cp /tmp/apisms-queue-worker.service /etc/systemd/system/

# Activer et démarrer
sudo systemctl daemon-reload
sudo systemctl enable apisms-queue-worker.service
sudo systemctl start apisms-queue-worker.service

# Vérifier le statut
sudo systemctl status apisms-queue-worker.service
```

#### 🪟 Windows
Le script génère un script **PowerShell** pour configurer une tâche planifiée.

```powershell
# Exécuter en tant qu'administrateur
PowerShell -ExecutionPolicy Bypass -File "setup-windows-service.ps1"

# Vérifier le statut
Get-ScheduledTask -TaskName "ApiSMS-Queue-Worker"
```

## 📊 Monitoring

### Logs
Les logs sont automatiquement créés dans :
- `storage/logs/queue-worker.log` - Logs normaux
- `storage/logs/queue-worker-error.log` - Logs d'erreur

### Vérification manuelle
```bash
# Test manuel du worker
php artisan queue:work --queue=bulk-sms --once

# Voir les jobs en attente  
php artisan queue:monitor

# Vider la queue (si nécessaire)
php artisan queue:clear
```

## 🛠️ Dépannage

### Le worker ne démarre pas
1. Vérifier les permissions du script
2. Vérifier les logs d'erreur
3. Tester le lancement manuel

### SMS restent en pending
1. Vérifier que le service tourne
2. Vérifier les logs pour les erreurs Kannel
3. Tester une connexion à Kannel

### Redémarrer le worker
```bash
# macOS
launchctl unload ~/Library/LaunchAgents/com.apisms.queueworker.plist
launchctl load ~/Library/LaunchAgents/com.apisms.queueworker.plist

# Linux
sudo systemctl restart apisms-queue-worker.service

# Windows
Stop-ScheduledTask -TaskName "ApiSMS-Queue-Worker"
Start-ScheduledTask -TaskName "ApiSMS-Queue-Worker"
```

## 🔧 Configuration avancée

### Modifier les paramètres du worker
Éditez le fichier `start-queue-worker.sh` ou `start-queue-worker.bat` :

```bash
# Paramètres actuels
--queue=bulk-sms        # Queue à traiter
--timeout=600           # Timeout de 10 minutes
--tries=3              # 3 tentatives maximum
--sleep=3              # Pause de 3s entre les vérifications
--memory=512           # Limite mémoire 512MB
```

### Ajouter d'autres queues
```bash
# Traiter plusieurs queues
--queue=bulk-sms,emails,notifications
```

## 🎯 Utilisation

Une fois configuré, le system fonctionne automatiquement :

1. **Bulk SMS envoyé** → Job créé en queue
2. **Worker traite automatiquement** → SMS envoyés via Kannel  
3. **Redémarrage automatique** en cas d'arrêt

Plus besoin d'intervention manuelle ! 🎉