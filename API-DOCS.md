# 📡 Documentation API DT SMS Gateway

API REST pour l'envoi de SMS individuels et en masse via DT SMS Gateway.

## 🚀 Démarrage Rapide

### Authentification
Toutes les requêtes nécessitent une clé API dans l'en-tête :
```bash
Authorization: Bearer YOUR_API_KEY
```

### URLs de base
- **Production** : `https://mysms.djiboutitelecom.dj/api` 
- **Local** : `http://localhost:8000/api`

## 📱 Envoi de SMS Individuels

### Envoyer un SMS
```bash
POST /v1/sms/send
```

**Exemple simple :**
```bash
curl -X POST "https://mysms.djiboutitelecom.dj/api/v1/sms/send" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "77111213",
    "message": "Bonjour, ceci est un test!"
  }'
```

**Exemple avec expéditeur :**
```bash
curl -X POST "https://mysms.djiboutitelecom.dj/api/v1/sms/send" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+25377111213",
    "message": "Votre code de vérification: 123456"
  }'
```

**Réponse :**
```json
{
  "success": true,
  "message": "SMS sent successfully",
  "data": {
    "sms_id": 12345,
    "to": "+25377111213",
    "from": "DT SMS",
    "status": "sent",
    "created_at": "2025-09-24T10:30:00Z"
  }
}
```

### Lister les SMS envoyés
```bash
GET /v1/sms?status=delivered&page=1&per_page=20
```

### Vérifier le statut d'un SMS
```bash
GET /v1/sms/{sms_id}/status
```

## 📧 Campagnes SMS (Bulk)

### Créer une campagne
```bash
POST /v1/sms/bulk
```

**Exemple simple :**
```bash
curl -X POST "https://mysms.djiboutitelecom.dj/api/v1/sms/bulk" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Campagne Marketing",
    "recipients": ["77123456", "77987654", "77555333"],
    "content": "Offre spéciale! 50% de réduction ce weekend!"
  }'
```

**Exemple avec programmation :**
```bash
curl -X POST "https://mysms.djiboutitelecom.dj/api/v1/sms/bulk" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Newsletter Hebdomadaire",
    "recipients": ["77111111", "77222222"],
    "content": "Découvrez nos nouveautés cette semaine!",
    "scheduled_at": "2025-09-28T09:00:00Z",
    "settings": {
      "rate_limit": 60,
      "batch_size": 50
    }
  }'
```

**Réponse :**
```json
{
  "success": true,
  "message": "Bulk SMS job created successfully",
  "data": {
    "job_id": 123,
    "name": "Campagne Marketing",
    "status": "pending",
    "total_count": 3,
    "valid_recipients": 3,
    "scheduled_at": null
  }
}
```

### Suivre une campagne
```bash
GET /v1/sms/bulk/{job_id}
```

**Réponse :**
```json
{
  "success": true,
  "data": {
    "job_id": 123,
    "name": "Campagne Marketing",
    "status": "processing",
    "progress_percentage": 65.5,
    "total_count": 1000,
    "sent_count": 655,
    "failed_count": 12,
    "pending_count": 333,
    "success_rate": 98.2
  }
}
```

### Contrôler une campagne
```bash
# Mettre en pause
POST /v1/sms/bulk/{job_id}/pause

# Reprendre
POST /v1/sms/bulk/{job_id}/resume
```

### Lister les campagnes
```bash
GET /v1/sms/bulk?status=processing&page=1&per_page=10
```

## 📊 Statistiques

### Statistiques générales
```bash
GET /v1/stats?period=month
```

**Réponse :**
```json
{
  "success": true,
  "data": {
    "period": "month",
    "total_sms": 1250,
    "sent_sms": 1200,
    "delivered_sms": 1150,
    "failed_sms": 50,
    "success_rate": 96.0,
    "total_campaigns": 15,
    "daily_usage": 85,
    "daily_limit": 1000,
    "monthly_usage": 1250,
    "monthly_limit": 25000
  }
}
```

### Statistiques temps réel
```bash
GET /v1/stats/realtime
```

## 🔧 Exemples de Code

### PHP (avec cURL)
```php
<?php
function sendSMS($apiKey, $to, $message, $from = null) {
    $url = 'https://mysms.djiboutitelecom.dj/api/v1/sms/send';
    
    $data = [
        'to' => $to,
        'message' => $message
    ];
    
    if ($from) {
        $data['from'] = $from;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

// Utilisation
$result = sendSMS('YOUR_API_KEY', '77111213', 'Test message', 'DT SMS');
if ($result['http_code'] === 200 && $result['response']['success']) {
    echo "SMS envoyé! ID: " . $result['response']['data']['sms_id'];
} else {
    echo "Erreur: " . $result['response']['message'];
}
?>
```

### JavaScript (Node.js)
```javascript
const axios = require('axios');

async function sendSMS(apiKey, to, message, from = null) {
    try {
        const data = { to, message };
        if (from) data.from = from;
        
        const response = await axios.post(
            'https://mysms.djiboutitelecom.dj/api/v1/sms/send',
            data,
            {
                headers: {
                    'Authorization': `Bearer ${apiKey}`,
                    'Content-Type': 'application/json'
                }
            }
        );
        
        return response.data;
    } catch (error) {
        if (error.response) {
            return error.response.data;
        }
        throw error;
    }
}

// Utilisation
(async () => {
    const result = await sendSMS('YOUR_API_KEY', '77111213', 'Test message', 'DT SMS');
    if (result.success) {
        console.log(`SMS envoyé! ID: ${result.data.sms_id}`);
    } else {
        console.error(`Erreur: ${result.message}`);
    }
})();
```

### Python
```python
import requests
import json

def send_sms(api_key, to, message, from_sender=None):
    url = 'https://mysms.djiboutitelecom.dj/api/v1/sms/send'
    
    headers = {
        'Authorization': f'Bearer {api_key}',
        'Content-Type': 'application/json'
    }
    
    data = {
        'to': to,
        'message': message
    }
    
    if from_sender:
        data['from'] = from_sender
    
    response = requests.post(url, headers=headers, json=data)
    return response.json()

# Utilisation
result = send_sms('YOUR_API_KEY', '77111213', 'Test message', 'DT SMS')
if result.get('success'):
    print(f"SMS envoyé! ID: {result['data']['sms_id']}")
else:
    print(f"Erreur: {result.get('message')}")
```

## 📋 Formats et Limites

### Numéros de téléphone
- **Format Djibouti** : `77XXXXXX` (recommandé)
- **Format international** : `+25377XXXXXX`
- **Autres pays** : `+[code_pays][numéro]`

### Messages SMS
- **Caractères maximum** : 160 par SMS standard
- **Messages longs** : Automatiquement divisés en plusieurs SMS
- **Caractères spéciaux** : UTF-8 supporté

### Expéditeur (FROM)
- **🔒 Sécurisé** : L'ID expéditeur est fixé par client lors de la création du compte
- **Automatique** : Pas besoin de spécifier le champ `from` dans les requêtes
- **Format** : Maximum 11 caractères alphanumériques (défini par l'admin)
- **Fallback** : Utilise la configuration par défaut si aucun ID expéditeur client

### Limites API
- **Rate limiting** : Selon configuration client
- **Quotas journaliers/mensuels** : Définis par client
- **Campagnes** : Maximum 10,000 destinataires par campagne

## ⚠️ Codes d'Erreur

| Code | Description | Solution |
|------|-------------|----------|
| 400 | Requête invalide | Vérifiez le format JSON et les paramètres |
| 401 | Non autorisé | Vérifiez votre clé API |
| 422 | Erreur de validation | Vérifiez les champs requis |
| 429 | Quota dépassé | Attendez ou contactez le support |
| 500 | Erreur serveur | Réessayez plus tard |

### Exemples de réponses d'erreur

**Quota dépassé :**
```json
{
  "success": false,
  "message": "Daily SMS quota exceeded",
  "limit": 1000,
  "used_today": 1000,
  "remaining": 0
}
```

**Validation échouée :**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "to": ["Le champ to est requis."],
    "message": ["Le message ne peut pas dépasser 160 caractères."]
  }
}
```

## 🔍 Health Check

Vérifiez l'état de l'API :
```bash
GET /health
```

**Réponse :**
```json
{
  "status": "ok",
  "service": "DT SMS Gateway",
  "version": "1.0.0",
  "timestamp": "2025-09-24T10:30:00Z"
}
```

## 🛠️ Outils de Test

### Swagger UI
Accédez à l'interface interactive : `https://mysms.djiboutitelecom.dj/docs`

### Postman Collection
Importez le fichier `openapi.yaml` dans Postman pour générer automatiquement une collection.

### cURL Examples
Testez rapidement avec les exemples cURL fournis ci-dessus.

## 📞 Support

- **Email** : support@dt-sms-gateway.com
- **Documentation** : https://docs.dt-sms-gateway.com
- **Status Page** : https://status.dt-sms-gateway.com

---

## 🔄 Changelog

### Version 1.0.0
- API REST complète pour SMS individuels
- Gestion des campagnes SMS en masse
- Statistiques et monitoring
- Authentification par clé API
- Support des quotas et rate limiting