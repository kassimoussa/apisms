# 📤 Guide Complet - Bulk SMS (Envoi en Masse)

## 🎯 Vue d'Ensemble

Le système de Bulk SMS permet d'envoyer des milliers de SMS de manière efficace, avec suivi en temps réel, gestion des erreurs, et contrôle de débit.

### **🔥 Fonctionnalités Principales**

| Fonctionnalité | Description | Status |
|---|---|---|
| **📤 Envoi Bulk** | Jusqu'à 10,000 SMS par campagne | ✅ |
| **⏰ Programmation** | Envoi différé/programmé | ✅ |
| **📊 Suivi Temps Réel** | Progression en % | ✅ |
| **⏸️ Pause/Resume** | Contrôle des campagnes | ✅ |
| **🚀 Queue Processing** | Traitement asynchrone | ✅ |
| **🎯 Rate Limiting** | Contrôle du débit | ✅ |
| **📈 Statistiques** | Rapports détaillés | ✅ |

## 🚀 API Endpoints

### **1. Créer une Campagne Bulk**

```bash
POST /api/v1/sms/bulk
Content-Type: application/json
Authorization: Bearer YOUR_API_KEY
```

#### Exemple de Requête Simple :
```json
{
  "name": "Campagne Marketing Q4",
  "recipients": ["77123456", "77987654", "77555333"],
  "content": "🔥 Offre spéciale ! 50% de réduction aujourd'hui seulement !",
  "from": "PROMO"
}
```

#### Exemple de Requête Avancée :
```json
{
  "name": "Newsletter Décembre 2025",
  "recipients": ["77123456", "77987654", "77555333"],
  "content": "Découvrez nos nouveautés de fin d'année ! Visitez notre site web pour plus d'infos.",
  "from": "INFO",
  "scheduled_at": "2025-12-01T09:00:00Z",
  "settings": {
    "rate_limit": 30,
    "batch_size": 100
  }
}
```

#### Réponse :
```json
{
  "success": true,
  "message": "Bulk SMS job created successfully",
  "data": {
    "job_id": 123,
    "name": "Campagne Marketing Q4",
    "status": "pending",
    "total_count": 3,
    "valid_recipients": 3,
    "scheduled_at": null
  }
}
```

### **2. Suivi du Statut**

```bash
GET /api/v1/sms/bulk/123
Authorization: Bearer YOUR_API_KEY
```

#### Réponse :
```json
{
  "success": true,
  "data": {
    "job_id": 123,
    "name": "Campagne Marketing Q4",
    "status": "processing",
    "progress_percentage": 65.5,
    "total_count": 1000,
    "sent_count": 655,
    "failed_count": 12,
    "pending_count": 333,
    "success_rate": 98.2,
    "estimated_duration": 300,
    "started_at": "2025-09-15T14:30:00Z",
    "created_at": "2025-09-15T14:25:00Z"
  }
}
```

### **3. Liste des Campagnes**

```bash
GET /api/v1/sms/bulk?status=processing&per_page=20
Authorization: Bearer YOUR_API_KEY
```

### **4. Contrôle des Campagnes**

#### Pause :
```bash
POST /api/v1/sms/bulk/123/pause
Authorization: Bearer YOUR_API_KEY
```

#### Reprise :
```bash
POST /api/v1/sms/bulk/123/resume
Authorization: Bearer YOUR_API_KEY
```

## 🎛️ Paramètres de Configuration

### **Settings Disponibles**

| Paramètre | Type | Défaut | Description |
|---|---|---|---|
| `rate_limit` | integer | 60 | SMS par minute |
| `batch_size` | integer | 50 | Taille des lots |
| `retry_failed` | boolean | true | Réessayer les échecs |
| `max_retries` | integer | 3 | Nombre de tentatives |

### **Limites Système**

| Limite | Valeur | Description |
|---|---|---|
| **Max Recipients** | 10,000 | Par campagne |
| **Rate Limit Max** | 1,000/min | Débit maximum |
| **Daily Limit** | 50,000 | Par client/jour |
| **Message Length** | 1,600 chars | SMS long |

## 📊 États des Campagnes

| Status | Description | Actions Possibles |
|---|---|---|
| **pending** | En attente | Start, Cancel |
| **processing** | En cours | Pause, Monitor |
| **paused** | En pause | Resume, Cancel |
| **completed** | Terminé | View Report |
| **failed** | Échec | Retry, View Error |

## 🔄 Workflow Complet

### **1. Étape de Création**
```bash
# 1. Créer la campagne
curl -X POST "https://your-domain.com/api/v1/sms/bulk" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Campaign",
    "recipients": ["77123456", "77987654"],
    "content": "Test message"
  }'
```

### **2. Étape de Monitoring**
```bash
# 2. Surveiller le progrès
curl "https://your-domain.com/api/v1/sms/bulk/123" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

### **3. Contrôle si Nécessaire**
```bash
# 3a. Pause si nécessaire
curl -X POST "https://your-domain.com/api/v1/sms/bulk/123/pause" \
  -H "Authorization: Bearer YOUR_API_KEY"

# 3b. Reprendre
curl -X POST "https://your-domain.com/api/v1/sms/bulk/123/resume" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

## 🛠️ Gestion Avancée

### **Programmation de Campagnes**

```bash
# Programmer pour demain 9h
curl -X POST "https://your-domain.com/api/v1/sms/bulk" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Campaign Demain 9h",
    "recipients": ["77123456"],
    "content": "Message programmé",
    "scheduled_at": "2025-09-16T09:00:00Z"
  }'
```

### **Optimisation Rate Limiting**

```json
{
  "name": "High Volume Campaign",
  "recipients": ["..."],
  "content": "Message",
  "settings": {
    "rate_limit": 100,    // 100 SMS/min (plus rapide)
    "batch_size": 200     // Lots de 200 (plus efficace)
  }
}
```

## 📈 Suivi et Rapports

### **Métriques Disponibles**

- **Progress** : Pourcentage d'avancement
- **Success Rate** : Taux de réussite
- **Estimated Duration** : Temps restant estimé
- **Throughput** : SMS par minute réels
- **Error Analysis** : Détail des échecs

### **Exemple de Monitoring Loop**

```bash
#!/bin/bash
JOB_ID=123

while true; do
  STATUS=$(curl -s "https://your-domain.com/api/v1/sms/bulk/$JOB_ID" \
    -H "Authorization: Bearer YOUR_API_KEY" | jq -r '.data.status')
  
  if [ "$STATUS" = "completed" ] || [ "$STATUS" = "failed" ]; then
    echo "Campaign finished with status: $STATUS"
    break
  fi
  
  PROGRESS=$(curl -s "https://your-domain.com/api/v1/sms/bulk/$JOB_ID" \
    -H "Authorization: Bearer YOUR_API_KEY" | jq -r '.data.progress_percentage')
  
  echo "Progress: $PROGRESS%"
  sleep 30
done
```

## ⚡ Performance et Optimisation

### **Recommandations**

1. **Batch Size** : 
   - Petites campagnes (< 1000) : 50
   - Moyennes campagnes (1000-5000) : 100
   - Grandes campagnes (> 5000) : 200

2. **Rate Limiting** :
   - Test/Dev : 10-30/min
   - Production normale : 60-100/min
   - High volume : 200-500/min

3. **Scheduling** :
   - Éviter les heures de pointe (12h-14h)
   - Programmer la nuit pour gros volumes
   - Étaler sur plusieurs heures si > 10,000 SMS

## 🚨 Gestion d'Erreurs

### **Codes d'Erreur Courants**

| Code | Erreur | Solution |
|---|---|---|
| 422 | Invalid recipients | Vérifier format numéros |
| 429 | Rate limit exceeded | Réduire rate_limit |
| 500 | Server error | Contacter support |

### **Retry Logic**

Les échecs sont automatiquement relancés :
- **Échec temporaire** : 3 tentatives avec délai croissant
- **Échec permanent** : Marqué comme failed définitivement
- **Rate limit** : Pause automatique puis reprise

## 🏗️ Architecture Technique

### **Queue Processing**

```
[API Request] → [BulkSmsJob] → [Queue] → [ProcessBulkSmsJob] → [KannelService] → [SMS Sent]
      ↓              ↓                          ↓                      ↓
  [Database]  [Progress Track]           [Individual SMS]       [Status Update]
```

### **Commandes Système**

```bash
# Traiter les jobs programmés (cron)
php artisan bulk-sms:process-scheduled

# Worker pour queue bulk-sms
php artisan queue:work --queue=bulk-sms

# Monitorer les jobs
php artisan queue:monitor bulk-sms
```

## 🎯 Cas d'Usage

### **1. Marketing Campaigns**
```json
{
  "name": "Black Friday 2025",
  "recipients": ["..."], // 5000 contacts
  "content": "🔥 FLASH SALE! 70% OFF everything! Today only: blackfriday.com",
  "from": "SALES",
  "settings": {"rate_limit": 200}
}
```

### **2. Notifications Système**
```json
{
  "name": "System Maintenance Alert",
  "recipients": ["..."], // Admins only
  "content": "⚠️ Maintenance programmée ce soir 22h-24h. Système indisponible.",
  "from": "SYSTEM",
  "scheduled_at": "2025-09-15T20:00:00Z"
}
```

### **3. Newsletter**
```json
{
  "name": "Newsletter Hebdomadaire",
  "recipients": ["..."], // 10000 abonnés
  "content": "📰 Newsletter #42: Actualités de la semaine. Lire: news.com/42",
  "from": "NEWS",
  "settings": {
    "rate_limit": 50,    // Respecter les limites opérateur
    "batch_size": 100
  }
}
```

---

## 🚀 Pour Commencer

1. **Testez avec un petit lot** (5-10 SMS)
2. **Surveillez les métriques** de performance
3. **Ajustez les paramètres** selon vos besoins
4. **Programmez vos campagnes** aux heures optimales
5. **Monitorer les résultats** et optimisez

**Le système Bulk SMS est maintenant prêt pour vos campagnes à grande échelle !** 📤🚀