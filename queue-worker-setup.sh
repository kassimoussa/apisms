#!/bin/bash

# Script d'installation automatique du queue worker Laravel
# Compatible macOS, Linux et Windows (via WSL/Git Bash)

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_NAME="apisms-queue-worker"

echo "🚀 Configuration du queue worker Laravel..."
echo "📁 Répertoire du projet: $PROJECT_DIR"

# Détecter le système d'exploitation
detect_os() {
    case "$OSTYPE" in
        darwin*)  echo "macos" ;;
        linux*)   echo "linux" ;;
        msys*|cygwin*|mingw*) echo "windows" ;;
        *)        echo "unknown" ;;
    esac
}

OS=$(detect_os)
echo "💻 Système détecté: $OS"

# Créer le script de worker principal
create_worker_script() {
    local script_path="$PROJECT_DIR/start-queue-worker"
    
    # Script pour Unix (macOS/Linux)
    cat > "${script_path}.sh" << 'EOF'
#!/bin/bash

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

echo "$(date): 🚀 Démarrage du queue worker Laravel..."

# Boucle infinie avec redémarrage automatique
while true; do
    echo "$(date): ▶️  Lancement du worker..."
    php artisan queue:work --queue=bulk-sms --timeout=600 --tries=3 --sleep=3 --memory=512 --verbose
    
    EXIT_CODE=$?
    echo "$(date): ⚠️  Worker arrêté (code: $EXIT_CODE), redémarrage dans 5 secondes..."
    sleep 5
done
EOF

    # Script pour Windows
    cat > "${script_path}.bat" << 'EOF'
@echo off
cd /d "%~dp0"

echo %date% %time%: 🚀 Démarrage du queue worker Laravel...

:loop
echo %date% %time%: ▶️  Lancement du worker...
php artisan queue:work --queue=bulk-sms --timeout=600 --tries=3 --sleep=3 --memory=512 --verbose

echo %date% %time%: ⚠️  Worker arrêté, redémarrage dans 5 secondes...
timeout /t 5 /nobreak >nul
goto loop
EOF

    chmod +x "${script_path}.sh" 2>/dev/null || true
    echo "✅ Scripts créés: ${script_path}.sh et ${script_path}.bat"
}

# Configuration pour macOS (launchd)
setup_macos() {
    local plist_file="$HOME/Library/LaunchAgents/com.apisms.queueworker.plist"
    
    cat > "$plist_file" << EOF
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>Label</key>
    <string>com.apisms.queueworker</string>
    
    <key>ProgramArguments</key>
    <array>
        <string>/bin/bash</string>
        <string>$PROJECT_DIR/start-queue-worker.sh</string>
    </array>
    
    <key>WorkingDirectory</key>
    <string>$PROJECT_DIR</string>
    
    <key>RunAtLoad</key>
    <true/>
    
    <key>KeepAlive</key>
    <true/>
    
    <key>StandardOutPath</key>
    <string>$PROJECT_DIR/storage/logs/queue-worker.log</string>
    
    <key>StandardErrorPath</key>
    <string>$PROJECT_DIR/storage/logs/queue-worker-error.log</string>
</dict>
</plist>
EOF

    # Charger le service
    launchctl unload "$plist_file" 2>/dev/null || true
    launchctl load "$plist_file"
    
    echo "✅ Service macOS configuré et démarré"
    echo "📝 Logs: $PROJECT_DIR/storage/logs/queue-worker.log"
}

# Configuration pour Linux (systemd)
setup_linux() {
    local service_file="/tmp/apisms-queue-worker.service"
    
    cat > "$service_file" << EOF
[Unit]
Description=ApiSMS Queue Worker
After=network.target

[Service]
Type=simple
User=$USER
WorkingDirectory=$PROJECT_DIR
ExecStart=/bin/bash $PROJECT_DIR/start-queue-worker.sh
Restart=always
RestartSec=5

# Logs
StandardOutput=append:$PROJECT_DIR/storage/logs/queue-worker.log
StandardError=append:$PROJECT_DIR/storage/logs/queue-worker-error.log

[Install]
WantedBy=multi-user.target
EOF

    echo "📄 Fichier service créé: $service_file"
    echo ""
    echo "🔧 Pour installer le service Linux, exécutez (en tant qu'admin):"
    echo "   sudo cp $service_file /etc/systemd/system/"
    echo "   sudo systemctl daemon-reload"
    echo "   sudo systemctl enable apisms-queue-worker.service"
    echo "   sudo systemctl start apisms-queue-worker.service"
    echo ""
    echo "📊 Pour vérifier le statut:"
    echo "   sudo systemctl status apisms-queue-worker.service"
}

# Configuration pour Windows (Task Scheduler via PowerShell)
setup_windows() {
    local ps_script="$PROJECT_DIR/setup-windows-service.ps1"
    
    cat > "$ps_script" << EOF
# Script PowerShell pour configurer le service Windows
\$taskName = "ApiSMS-Queue-Worker"
\$projectDir = "$PROJECT_DIR"

# Supprimer la tâche existante si elle existe
try {
    Unregister-ScheduledTask -TaskName \$taskName -Confirm:\$false -ErrorAction SilentlyContinue
    Write-Host "Ancienne tâche supprimée"
} catch {}

# Créer une nouvelle tâche planifiée
\$action = New-ScheduledTaskAction -Execute "cmd.exe" -Argument "/c `"\$projectDir\start-queue-worker.bat`""
\$trigger = New-ScheduledTaskTrigger -AtStartup
\$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -RestartCount 999 -RestartInterval (New-TimeSpan -Minutes 1)

# Enregistrer la tâche
Register-ScheduledTask -TaskName \$taskName -Action \$action -Trigger \$trigger -Settings \$settings -Description "ApiSMS Laravel Queue Worker"

Write-Host "✅ Tâche Windows configurée: \$taskName"
Write-Host "🚀 Démarrage de la tâche..."

# Démarrer la tâche
Start-ScheduledTask -TaskName \$taskName

Write-Host "📊 Pour vérifier le statut, utilisez: Get-ScheduledTask -TaskName '$taskName'"
EOF

    echo "📄 Script PowerShell créé: $ps_script"
    echo ""
    echo "🔧 Pour installer le service Windows, exécutez (en tant qu'administrateur):"
    echo "   PowerShell -ExecutionPolicy Bypass -File \"$ps_script\""
    echo ""
    echo "📊 Pour vérifier: Get-ScheduledTask -TaskName 'ApiSMS-Queue-Worker'"
}

# Créer les répertoires de logs si nécessaire
mkdir -p "$PROJECT_DIR/storage/logs"

# Créer les scripts worker
create_worker_script

# Configuration spécifique au système
case "$OS" in
    "macos")
        setup_macos
        ;;
    "linux")
        setup_linux
        ;;
    "windows")
        setup_windows
        ;;
    *)
        echo "❌ Système non supporté: $OS"
        echo "ℹ️  Vous pouvez lancer manuellement: ./start-queue-worker.sh"
        exit 1
        ;;
esac

echo ""
echo "🎉 Configuration terminée!"
echo "🔄 Le queue worker devrait démarrer automatiquement."
echo "📝 Logs disponibles dans: $PROJECT_DIR/storage/logs/"
echo ""
echo "🛠️  Commandes utiles:"
echo "   Lancement manuel: ./start-queue-worker.sh (Unix) ou start-queue-worker.bat (Windows)"
echo "   Arrêt manuel: Ctrl+C dans le terminal du worker"