# ApiSMS Gateway - Laravel 11 SMS API Gateway

Modern SMS API Gateway built with Laravel 11 for Kannel SMS server integration. Designed for the DPCR Fleet Management System in Djibouti.

## 🌟 Features Implémentées

### ✅ Configuration Environnement
- **Variables sécurisées** - Configuration multi-environnements (dev/staging/prod)
- **Chiffrement API keys** - AES-256 avec expiration automatique
- **Backup automatique** - Base de données avec rétention configurable

### ✅ Sécurité Production
- **HTTPS obligatoire** - Redirection automatique et headers sécurisés
- **CORS configuré** - Origines et méthodes contrôlées
- **IP Whitelisting** - Protection webhooks avec support CIDR
- **Logs audit** - Traçabilité complète des requêtes API
- **Protection CSRF** - Interface admin sécurisée

### ✅ Performance
- **Cache Redis** - Optimisation requêtes fréquentes
- **Eager loading** - Optimisation requêtes DB
- **Compression réponses** - Réduction bande passante
- **CDN ready** - Assets statiques optimisés

### ✅ Monitoring Production
- **Logs structurés JSON** - Elasticsearch/Logstash compatible
- **Métriques détaillées** - Prometheus/Grafana ready
- **Alerting** - Email/Slack notifications
- **Health checks** - Surveillance système complète

### ✅ Commandes Artisan
- `sms:test-kannel` - Test connectivité Kannel
- `sms:stats` - Statistiques globales détaillées
- `backup:database` - Sauvegarde avec compression
- `client:create` - Création clients sécurisée

## 🚀 Installation Production

### 1. Prérequis
```bash
# Système requis
PHP 8.2+ avec extensions: redis, gd, curl, mbstring, zip
MySQL 8.0+ ou MariaDB 10.6+
Redis 6.0+
Nginx 1.20+ ou Apache 2.4+
```

### 2. Déploiement
```bash
# Cloner le projet
git clone <repository-url> /var/www/apisms
cd /var/www/apisms

# Configuration environnement production
cp .env.production.example .env
# Éditer .env avec vos paramètres

# Installation dépendances
composer install --no-dev --optimize-autoloader
npm ci --only=production

# Génération clés et cache
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migration base de données
php artisan migrate --force

# Build assets
npm run build

# Permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 3. Configuration Nginx Production
```nginx
server {
    listen 443 ssl http2;
    server_name sms-gateway.dj;
    root /var/www/apisms/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/sms-gateway.crt;
    ssl_certificate_key /etc/ssl/private/sms-gateway.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;

    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Frame-Options DENY always;
    add_header X-Content-Type-Options nosniff always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Rate Limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=60r/m;
    limit_req_zone $binary_remote_addr zone=webhooks:10m rate=100r/m;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location /api/ {
        limit_req zone=api burst=10 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }

    location /webhooks/ {
        limit_req zone=webhooks burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Block access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /(storage|vendor|tests|database)/ {
        deny all;
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name sms-gateway.dj;
    return 301 https://$server_name$request_uri;
}
```

## 📖 API Documentation Complète

### Authentification
```http
X-API-Key: sk_your_32_character_api_key
# OU
Authorization: Bearer sk_your_32_character_api_key
```

### Endpoints Disponibles

#### 📤 Envoi SMS
```http
POST /api/v1/sms/send
Content-Type: application/json

{
    "to": "77166677",
    "message": "Test SMS depuis ApiSMS Gateway",
    "from": "11123",
    "async": false
}
```

**Réponse Succès:**
```json
{
    "data": {
        "id": 123,
        "direction": "outbound",
        "from": "+25311123",
        "to": "+25377166677",
        "message": "Test SMS depuis ApiSMS Gateway",
        "status": "sent",
        "kannel_id": "uuid-string",
        "sent_at": "2025-09-11T14:30:00.000000Z"
    }
}
```

#### 📊 Statut SMS
```http
GET /api/v1/sms/123/status
```

#### 📋 Liste SMS
```http
GET /api/v1/sms?page=1&per_page=20&status=delivered&direction=outbound
```

#### 📈 Statistiques
```http
GET /api/v1/stats?period=month
GET /api/v1/stats/realtime
```

### Documentation Interactive
🌐 **Swagger UI:** `https://your-domain.com/api/documentation`

## 🛠 Commandes Artisan Production

### 🔧 Opérations SMS
```bash
# Test connectivité Kannel
php artisan sms:test-kannel --verbose --timeout=30

# Statistiques globales
php artisan sms:stats --period=week --format=table --export=/tmp/stats.csv

# Génération données test
php artisan sms:generate-test-data --count=1000

# Nettoyage données test
php artisan sms:clear-test-data --confirm
```

### 👥 Gestion Clients
```bash
# Créer nouveau client
php artisan client:create "DPCR Fleet Management" \
    --rate-limit=200 \
    --description="Système de gestion de flotte DPCR" \
    --allowed-ips="192.168.1.100,10.0.0.0/24"

# Lister clients
php artisan client:list --active-only

# Régénérer clé API
php artisan client:regenerate-key 1 --expire-days=365
```

### 💾 Backup & Maintenance
```bash
# Backup base de données
php artisan backup:database --compress --exclude=sessions,cache

# Lister backups
php artisan backup:list --recent=10

# Vérifier intégrité backup
php artisan backup:verify backup_2025_09_11_14_30_00.sql.gz

# Nettoyage automatique
php artisan backup:cleanup --older-than=90
```

### 🧹 Maintenance
```bash
# Nettoyage logs anciens
php artisan sms:cleanup-old-logs --days=30 --confirm

# Vérification santé système
php artisan system:health-check --full

# Surveillance queue
php artisan queue:monitor --queue=sms,default
```

## 🔒 Sécurité Implémentée

### 🔐 Chiffrement API Keys
- **Algorithme:** AES-256-CBC
- **Stockage:** Hash SHA-256 + version chiffrée
- **Expiration:** Configurable (défaut: 365 jours)
- **Rotation:** Commande de régénération sécurisée

### 🌐 Sécurité Réseau
- **IP Whitelisting:** Support CIDR (192.168.1.0/24)
- **Rate Limiting:** Redis-backed par client
- **CORS:** Origines/méthodes contrôlées
- **Headers Sécurité:** HSTS, CSP, X-Frame-Options

### 📋 Audit & Logs
- **Format JSON** structuré pour parsing automatisé
- **Données sensibles** automatiquement masquées
- **Traçabilité complète** requêtes/réponses
- **Rétention** configurable par type de log

## 📊 Monitoring & Alertes

### 🚨 Alertes Configurées
```env
# Slack
LOG_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK
BACKUP_SLACK_NOTIFICATIONS=true

# Email
BACKUP_MAIL_NOTIFICATIONS=true
BACKUP_MAIL_TO="admin@dpcr.dj,tech@dpcr.dj"
```

### 📈 Métriques Surveillées
- **Performance API** - Temps réponse, taux erreur
- **Kannel Gateway** - Connectivité, temps réponse
- **Queue Jobs** - Taux traitement, jobs échoués
- **Base de données** - Requêtes lentes, connexions
- **Système** - CPU, mémoire, espace disque

### 🏥 Health Checks
```http
GET /health?token=YOUR_HEALTH_TOKEN

{
    "status": "healthy",
    "checks": {
        "database": {"status": "ok", "response_time": "5ms"},
        "redis": {"status": "ok", "memory_usage": "45%"},
        "kannel": {"status": "ok", "last_check": "2025-09-11T14:30:00Z"},
        "queue": {"status": "ok", "pending_jobs": 12},
        "disk_space": {"status": "ok", "available": "75%"}
    }
}
```

## 🧪 Tests Intégrés

### Exécution Tests
```bash
# Tests complets
php artisan test --coverage

# Tests par catégorie
php artisan test --testsuite=Feature --filter=SmsApiTest
php artisan test --testsuite=Unit --filter=KannelServiceTest

# Tests performance
php artisan test --group=performance
```

### Couverture Tests
- ✅ **Services critiques** - KannelService, ApiKeyEncryptionService
- ✅ **API Endpoints** - Envoi SMS, statuts, listes
- ✅ **Webhooks Kannel** - DLR, MO SMS
- ✅ **Sécurité** - Authentication, rate limiting, IP whitelisting
- ✅ **Performance** - Tests charge, temps réponse

## 🚀 Déploiement Production

### Variables Environnement Critiques
```env
# Production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sms-gateway.dj

# Sécurité
FORCE_HTTPS=true
API_KEY_ENCRYPTION_KEY=base64:VOTRE_CLE_32_CARACTERES
WEBHOOK_IP_WHITELIST="IP_KANNEL_SERVER,IP_BACKUP_SERVER"

# Performance
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Monitoring
LOG_JSON_ENABLED=true
PROMETHEUS_METRICS_ENABLED=true
HEALTH_CHECK_TOKEN=VOTRE_TOKEN_SECRET
```

### Systemd Services
```bash
# Queue Worker
sudo systemctl enable apisms-queue
sudo systemctl start apisms-queue

# Scheduler
sudo systemctl enable apisms-scheduler
sudo systemctl start apisms-scheduler

# Monitoring
sudo systemctl status apisms-*
```

## 🆘 Troubleshooting Production

### Problèmes Fréquents

#### 🔴 Kannel Non Accessible
```bash
# Test connectivité
php artisan sms:test-kannel --verbose

# Vérification logs
tail -f storage/logs/sms.log | jq '.level,.message,.context.error'

# Test réseau
curl -v "http://kannel-server:13013/cgi-bin/sendsms?user=test&pass=test"
```

#### 🔴 Clés API Expirées
```bash
# Vérifier expiration
php artisan client:list --show-expiry

# Régénérer clé
php artisan client:regenerate-key CLIENT_ID --expire-days=365
```

#### 🔴 Queue Bloquée
```bash
# Redémarrer workers
php artisan queue:restart

# Vérifier jobs échoués
php artisan queue:failed

# Relancer jobs échoués
php artisan queue:retry all
```

### Logs Analyse
```bash
# Erreurs API récentes
tail -f storage/logs/audit.log | jq 'select(.level=="error")'

# Performance lente
grep "slow_request" storage/logs/audit.log | jq '.duration_ms,.path'

# Tentatives d'intrusion
grep "Invalid API key" storage/logs/security.log | jq '.ip,.timestamp'
```

---

## 📞 Support DPCR

### Contacts Techniques
- **Email:** tech@dpcr.dj
- **Urgences:** +253 XX XX XX XX
- **Documentation:** https://docs.dpcr.dj/apisms

### Maintenance Planifiée
- **Backups:** Tous les jours à 02h00
- **Nettoyage logs:** Hebdomadaire dimanche 03h00  
- **Mise à jour sécurité:** Premier mardi du mois
- **Redémarrage services:** Maintenance mensuelle

---

**ApiSMS Gateway v1.0 - Production Ready**  
*© 2025 Direction de la Planification et de Contrôle Routier, Djibouti*
