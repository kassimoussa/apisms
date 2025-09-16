# 📚 Guide d'Utilisation API - ApiSMS Gateway

Guide complet pour l'intégration et l'utilisation de l'API SMS Gateway DPCR.

## 🚀 Démarrage Rapide

### 1. Obtenir votre Clé API

Contactez l'équipe technique DPCR pour obtenir votre clé API :
- **Email :** tech@dpcr.dj
- **Format :** `sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`
- **Durée de validité :** 365 jours (renouvelable)

### 2. Configuration de Base

```bash
# URL de base production
BASE_URL=https://sms-gateway.dj/api/v1

# Headers requis
X-API-Key: sk_votre_cle_api_32_caracteres
Content-Type: application/json
Accept: application/json
```

### 3. Premier Envoi SMS

```http
POST /api/v1/sms/send
X-API-Key: sk_votre_cle_api
Content-Type: application/json

{
    "to": "77166677",
    "message": "Test SMS depuis mon application",
    "from": "11123"
}
```

## 📋 Référence API Complète

### 🔐 Authentification

Toutes les requêtes API nécessitent une authentification via clé API.

#### Méthodes d'authentification :

**Méthode 1 : Header X-API-Key (recommandée)**
```http
X-API-Key: sk_votre_cle_api_32_caracteres
```

**Méthode 2 : Authorization Bearer**
```http
Authorization: Bearer sk_votre_cle_api_32_caracteres
```

#### Codes d'erreur authentification :
- `401` - Clé API manquante ou invalide
- `403` - IP non autorisée
- `429` - Limite de taux dépassée

---

## 📤 Envoi de SMS

### `POST /api/v1/sms/send`

Envoi d'un SMS via le gateway Kannel.

#### Paramètres de requête

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `to` | string | ✅ | Numéro destinataire (format: `77XXXXXX` ou `+25377XXXXXX`) |
| `message` | string | ✅ | Contenu SMS (max 160 caractères) |
| `from` | string | ❌ | Expéditeur (défaut: `11123`) |
| `async` | boolean | ❌ | Traitement asynchrone (défaut: `false`) |

#### Formats de numéros acceptés

```json
{
    "valides": [
        "77166677",           // Format national Djibouti
        "+25377166677",       // Format international
        "78123456",           // Autres opérateurs nationaux
        "+25378123456"        // Autres opérateurs internationaux
    ]
}
```

#### Exemples de requêtes

**Envoi SMS Synchrone :**
```bash
curl -X POST https://sms-gateway.dj/api/v1/sms/send \
  -H "X-API-Key: sk_votre_cle_api" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "77166677",
    "message": "Alerte: Véhicule #12345 nécessite une maintenance",
    "from": "DPCR"
  }'
```

**Envoi SMS Asynchrone :**
```bash
curl -X POST https://sms-gateway.dj/api/v1/sms/send \
  -H "X-API-Key: sk_votre_cle_api" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "77166677",
    "message": "Rapport quotidien généré avec succès",
    "from": "11123",
    "async": true
  }'
```

#### Réponses

**✅ Succès Synchrone (200 OK) :**
```json
{
    "data": {
        "id": 123,
        "direction": "outbound",
        "from": "+25311123",
        "to": "+25377166677",
        "message": "Alerte: Véhicule #12345 nécessite une maintenance",
        "status": "sent",
        "kannel_id": "uuid-1234-5678-9abc",
        "sent_at": "2025-09-11T14:30:15.000000Z",
        "created_at": "2025-09-11T14:30:15.000000Z",
        "updated_at": "2025-09-11T14:30:15.000000Z"
    }
}
```

**✅ Succès Asynchrone (202 Accepted) :**
```json
{
    "message": "SMS queued for delivery",
    "async": true,
    "sms": {
        "id": 124,
        "direction": "outbound",
        "from": "+25311123",
        "to": "+25377166677",
        "message": "Rapport quotidien généré avec succès",
        "status": "pending",
        "created_at": "2025-09-11T14:31:00.000000Z"
    }
}
```

**❌ Erreur Validation (422 Unprocessable Entity) :**
```json
{
    "error": "SMS sending failed",
    "code": "KANNEL_5",
    "message": "Invalid destination number",
    "sms": {
        "id": 125,
        "status": "failed",
        "error_code": "KANNEL_5",
        "error_message": "Invalid destination number"
    }
}
```

---

## 📊 Suivi et Statut SMS

### `GET /api/v1/sms/{id}/status`

Récupération du statut d'un SMS spécifique.

#### Paramètres URL

| Paramètre | Type | Description |
|-----------|------|-------------|
| `id` | integer | ID du message SMS |

#### Exemple de requête

```bash
curl -X GET https://sms-gateway.dj/api/v1/sms/123/status \
  -H "X-API-Key: sk_votre_cle_api"
```

#### Statuts possibles

| Statut | Description |
|--------|-------------|
| `pending` | En attente de traitement |
| `sent` | Envoyé au gateway Kannel |
| `delivered` | Livré au destinataire |
| `failed` | Échec d'envoi |

#### Réponse

```json
{
    "data": {
        "id": 123,
        "direction": "outbound",
        "from": "+25311123",
        "to": "+25377166677",
        "message": "Alerte: Véhicule #12345 nécessite une maintenance",
        "status": "delivered",
        "kannel_id": "uuid-1234-5678-9abc",
        "sent_at": "2025-09-11T14:30:15.000000Z",
        "delivered_at": "2025-09-11T14:30:45.000000Z",
        "error_code": null,
        "error_message": null,
        "created_at": "2025-09-11T14:30:15.000000Z",
        "updated_at": "2025-09-11T14:30:45.000000Z"
    }
}
```

---

## 📋 Liste des SMS

### `GET /api/v1/sms`

Récupération de la liste paginée des SMS.

#### Paramètres de requête

| Paramètre | Type | Défaut | Description |
|-----------|------|--------|-------------|
| `page` | integer | `1` | Numéro de page |
| `per_page` | integer | `20` | Messages par page (max 100) |
| `status` | string | - | Filtrer par statut (`pending`, `sent`, `delivered`, `failed`) |
| `direction` | string | - | Filtrer par direction (`outbound`, `inbound`) |

#### Exemple de requête

```bash
curl -X GET "https://sms-gateway.dj/api/v1/sms?page=1&per_page=10&status=delivered" \
  -H "X-API-Key: sk_votre_cle_api"
```

#### Réponse

```json
{
    "data": [
        {
            "id": 123,
            "direction": "outbound",
            "from": "+25311123",
            "to": "+25377166677",
            "message": "Alerte: Véhicule #12345 nécessite une maintenance",
            "status": "delivered",
            "created_at": "2025-09-11T14:30:15.000000Z"
        },
        {
            "id": 122,
            "direction": "outbound",
            "from": "+25311123",
            "to": "+25377166677",
            "message": "Inspection programmée pour demain 10h00",
            "status": "delivered",
            "created_at": "2025-09-11T13:15:30.000000Z"
        }
    ],
    "links": {
        "first": "https://sms-gateway.dj/api/v1/sms?page=1",
        "last": "https://sms-gateway.dj/api/v1/sms?page=5",
        "prev": null,
        "next": "https://sms-gateway.dj/api/v1/sms?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "per_page": 10,
        "to": 10,
        "total": 47
    }
}
```

---

## 📈 Statistiques

### `GET /api/v1/stats`

Récupération des statistiques détaillées.

#### Paramètres de requête

| Paramètre | Type | Défaut | Description |
|-----------|------|--------|-------------|
| `period` | string | `month` | Période (`today`, `week`, `month`, `year`) |

#### Exemple de requête

```bash
curl -X GET "https://sms-gateway.dj/api/v1/stats?period=week" \
  -H "X-API-Key: sk_votre_cle_api"
```

#### Réponse

```json
{
    "client": {
        "id": 1,
        "name": "DPCR Fleet Management",
        "rate_limit": 200,
        "active": true
    },
    "period": {
        "name": "week",
        "start_date": "2025-09-08T00:00:00.000000Z",
        "end_date": "2025-09-11T14:30:00.000000Z"
    },
    "totals": {
        "sent": 245,
        "delivered": 213,
        "failed": 12,
        "pending": 5,
        "total": 475
    },
    "directions": {
        "outbound": 450,
        "inbound": 25
    },
    "success_rate": 95.5,
    "daily": [
        {
            "date": "2025-09-11",
            "sent": 45,
            "failed": 2
        },
        {
            "date": "2025-09-10",
            "sent": 38,
            "failed": 1
        }
    ],
    "recent_messages": [
        {
            "id": 123,
            "direction": "outbound",
            "to": "+25377166677",
            "status": "delivered",
            "created_at": "2025-09-11T14:30:15.000000Z"
        }
    ],
    "kannel": {
        "status": "connected",
        "response_time": 150,
        "last_check": "2025-09-11T14:30:00.000000Z"
    }
}
```

### `GET /api/v1/stats/realtime`

Statistiques temps réel (dernières 24h).

#### Exemple de requête

```bash
curl -X GET https://sms-gateway.dj/api/v1/stats/realtime \
  -H "X-API-Key: sk_votre_cle_api"
```

#### Réponse

```json
{
    "last_24_hours": {
        "sent": 48,
        "failed": 3,
        "pending": 2
    },
    "kannel_status": {
        "status": "connected",
        "response_time": 120,
        "last_check": "2025-09-11T14:30:45.000000Z"
    },
    "last_activity": "2025-09-11T14:25:30.000000Z",
    "current_time": "2025-09-11T14:30:45.000000Z"
}
```

---

## 🚨 Gestion des Erreurs

### Codes d'Erreur HTTP

| Code | Description | Action recommandée |
|------|-------------|-------------------|
| `400` | Requête malformée | Vérifier format JSON et paramètres |
| `401` | Non authentifié | Vérifier clé API |
| `403` | Accès refusé | Vérifier IP autorisée |
| `404` | Ressource non trouvée | Vérifier ID SMS |
| `422` | Erreur validation | Vérifier paramètres requis |
| `429` | Limite de taux | Réduire fréquence requêtes |
| `500` | Erreur serveur | Réessayer plus tard |

### Codes d'Erreur Kannel

| Code | Description | Cause probable |
|------|-------------|----------------|
| `KANNEL_1` | Invalid username/password | Identifiants Kannel incorrects |
| `KANNEL_3` | Authorization failed | Problème autorisation |
| `KANNEL_5` | Invalid destination number | Numéro destinataire invalide |
| `KANNEL_7` | Message too long | Message > 160 caractères |
| `KANNEL_13` | Cannot route message | Problème routage réseau |

### Exemples d'Erreurs

**Clé API invalide :**
```json
{
    "error": "Invalid API key",
    "message": "The provided API key is invalid, expired, or inactive."
}
```

**Rate limit dépassé :**
```json
{
    "error": "Rate limit exceeded",
    "message": "Too many requests. Try again in 45 seconds.",
    "retry_after": 45,
    "rate_limit": 200
}
```

**Validation échouée :**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "to": ["The to field is required."],
        "message": ["The message field is required."]
    }
}
```

---

## 💻 Exemples d'Intégration

### PHP avec cURL

```php
<?php

class SmsGatewayClient
{
    private $baseUrl = 'https://sms-gateway.dj/api/v1';
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function sendSms($to, $message, $from = '11123', $async = false)
    {
        $data = [
            'to' => $to,
            'message' => $message,
            'from' => $from,
            'async' => $async
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/sms/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'data' => json_decode($response, true),
            'http_code' => $httpCode
        ];
    }

    public function getSmsStatus($smsId)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . "/sms/{$smsId}/status",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $this->apiKey,
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success' => $httpCode === 200,
            'data' => json_decode($response, true),
            'http_code' => $httpCode
        ];
    }
}

// Utilisation
$client = new SmsGatewayClient('sk_votre_cle_api');

// Envoi SMS
$result = $client->sendSms(
    '77166677',
    'Alerte: Véhicule nécessite maintenance',
    'DPCR'
);

if ($result['success']) {
    $smsId = $result['data']['data']['id'];
    echo "SMS envoyé avec succès. ID: {$smsId}\n";
    
    // Vérifier statut après 30 secondes
    sleep(30);
    $status = $client->getSmsStatus($smsId);
    echo "Statut: " . $status['data']['data']['status'] . "\n";
} else {
    echo "Erreur: " . $result['data']['error'] . "\n";
}
?>
```

### Python avec requests

```python
import requests
import json
import time

class SmsGatewayClient:
    def __init__(self, api_key, base_url='https://sms-gateway.dj/api/v1'):
        self.api_key = api_key
        self.base_url = base_url
        self.headers = {
            'X-API-Key': api_key,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }

    def send_sms(self, to, message, from_='11123', async_=False):
        """Envoyer un SMS"""
        data = {
            'to': to,
            'message': message,
            'from': from_,
            'async': async_
        }
        
        response = requests.post(
            f'{self.base_url}/sms/send',
            headers=self.headers,
            data=json.dumps(data)
        )
        
        return {
            'success': response.status_code in [200, 202],
            'data': response.json(),
            'status_code': response.status_code
        }

    def get_sms_status(self, sms_id):
        """Récupérer le statut d'un SMS"""
        response = requests.get(
            f'{self.base_url}/sms/{sms_id}/status',
            headers=self.headers
        )
        
        return {
            'success': response.status_code == 200,
            'data': response.json(),
            'status_code': response.status_code
        }

    def get_sms_list(self, page=1, per_page=20, status=None):
        """Récupérer la liste des SMS"""
        params = {'page': page, 'per_page': per_page}
        if status:
            params['status'] = status
            
        response = requests.get(
            f'{self.base_url}/sms',
            headers=self.headers,
            params=params
        )
        
        return {
            'success': response.status_code == 200,
            'data': response.json(),
            'status_code': response.status_code
        }

# Utilisation
client = SmsGatewayClient('sk_votre_cle_api')

# Envoi SMS
result = client.send_sms(
    '77166677',
    'Alerte: Véhicule #12345 nécessite maintenance',
    'DPCR'
)

if result['success']:
    sms_id = result['data']['data']['id']
    print(f"SMS envoyé avec succès. ID: {sms_id}")
    
    # Vérifier statut
    time.sleep(30)
    status_result = client.get_sms_status(sms_id)
    if status_result['success']:
        status = status_result['data']['data']['status']
        print(f"Statut: {status}")
else:
    print(f"Erreur: {result['data'].get('error', 'Erreur inconnue')}")
```

### JavaScript/Node.js avec axios

```javascript
const axios = require('axios');

class SmsGatewayClient {
    constructor(apiKey, baseUrl = 'https://sms-gateway.dj/api/v1') {
        this.apiKey = apiKey;
        this.baseUrl = baseUrl;
        this.headers = {
            'X-API-Key': apiKey,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
    }

    async sendSms(to, message, from = '11123', async = false) {
        try {
            const response = await axios.post(`${this.baseUrl}/sms/send`, {
                to,
                message,
                from,
                async
            }, { headers: this.headers });

            return {
                success: true,
                data: response.data,
                statusCode: response.status
            };
        } catch (error) {
            return {
                success: false,
                data: error.response?.data || { error: error.message },
                statusCode: error.response?.status || 500
            };
        }
    }

    async getSmsStatus(smsId) {
        try {
            const response = await axios.get(`${this.baseUrl}/sms/${smsId}/status`, {
                headers: this.headers
            });

            return {
                success: true,
                data: response.data,
                statusCode: response.status
            };
        } catch (error) {
            return {
                success: false,
                data: error.response?.data || { error: error.message },
                statusCode: error.response?.status || 500
            };
        }
    }

    async getSmsList(page = 1, perPage = 20, status = null) {
        try {
            const params = { page, per_page: perPage };
            if (status) params.status = status;

            const response = await axios.get(`${this.baseUrl}/sms`, {
                headers: this.headers,
                params
            });

            return {
                success: true,
                data: response.data,
                statusCode: response.status
            };
        } catch (error) {
            return {
                success: false,
                data: error.response?.data || { error: error.message },
                statusCode: error.response?.status || 500
            };
        }
    }

    async getStats(period = 'month') {
        try {
            const response = await axios.get(`${this.baseUrl}/stats`, {
                headers: this.headers,
                params: { period }
            });

            return {
                success: true,
                data: response.data,
                statusCode: response.status
            };
        } catch (error) {
            return {
                success: false,
                data: error.response?.data || { error: error.message },
                statusCode: error.response?.status || 500
            };
        }
    }
}

// Utilisation
async function exempleUtilisation() {
    const client = new SmsGatewayClient('sk_votre_cle_api');

    try {
        // Envoi SMS
        const result = await client.sendSms(
            '77166677',
            'Alerte: Véhicule #12345 nécessite maintenance',
            'DPCR'
        );

        if (result.success) {
            const smsId = result.data.data.id;
            console.log(`SMS envoyé avec succès. ID: ${smsId}`);

            // Attendre et vérifier statut
            setTimeout(async () => {
                const statusResult = await client.getSmsStatus(smsId);
                if (statusResult.success) {
                    console.log(`Statut: ${statusResult.data.data.status}`);
                }
            }, 30000);

        } else {
            console.error(`Erreur: ${result.data.error}`);
        }

        // Récupérer statistiques
        const stats = await client.getStats('week');
        if (stats.success) {
            console.log(`SMS envoyés cette semaine: ${stats.data.totals.sent}`);
            console.log(`Taux de succès: ${stats.data.success_rate}%`);
        }

    } catch (error) {
        console.error('Erreur:', error.message);
    }
}

// exempleUtilisation();
module.exports = SmsGatewayClient;
```

---

## 🔧 Bonnes Pratiques

### 1. Gestion des Erreurs

```javascript
// Toujours vérifier le code de réponse
if (response.status === 429) {
    // Rate limit - attendre avant de réessayer
    const retryAfter = response.data.retry_after || 60;
    setTimeout(() => {
        // Réessayer la requête
    }, retryAfter * 1000);
}

// Gérer les erreurs réseau
try {
    const result = await sendSms(...);
} catch (error) {
    if (error.code === 'ECONNREFUSED') {
        // Problème de connectivité
        console.error('Impossible de se connecter au serveur SMS');
    }
}
```

### 2. Optimisation Performance

```python
# Utiliser le mode asynchrone pour les envois en lot
async_results = []
for recipient in recipients:
    result = client.send_sms(
        recipient['phone'],
        recipient['message'],
        async_=True  # Mode asynchrone
    )
    async_results.append(result)

# Vérifier les statuts en lot après délai
time.sleep(60)
for result in async_results:
    if result['success']:
        sms_id = result['data']['sms']['id']
        status = client.get_sms_status(sms_id)
```

### 3. Sécurité

```php
// Ne jamais exposer la clé API côté client
// Utiliser un proxy backend pour les appels API

// Valider les données avant envoi
function validatePhone($phone) {
    return preg_match('/^(\+253)?[7-8]\d{7}$/', $phone);
}

function sanitizeMessage($message) {
    return substr(strip_tags($message), 0, 160);
}

// Logger les erreurs sans exposer la clé
error_log("SMS API Error: " . $response['error'] . " for message to " . substr($phone, 0, 3) . "***");
```

### 4. Surveillance et Monitoring

```javascript
// Implémenter retry avec backoff exponentiel
async function sendSmsWithRetry(client, to, message, maxRetries = 3) {
    for (let attempt = 1; attempt <= maxRetries; attempt++) {
        try {
            const result = await client.sendSms(to, message);
            
            if (result.success) {
                return result;
            }
            
            // Ne pas retry sur certaines erreurs
            if (result.statusCode === 422) {
                throw new Error('Invalid request parameters');
            }
            
        } catch (error) {
            if (attempt === maxRetries) {
                throw error;
            }
            
            // Backoff exponentiel
            const delay = Math.pow(2, attempt) * 1000;
            await new Promise(resolve => setTimeout(resolve, delay));
        }
    }
}

// Monitoring des métriques
class SmsMetrics {
    constructor() {
        this.successCount = 0;
        this.errorCount = 0;
        this.responseTimes = [];
    }
    
    recordSuccess(responseTime) {
        this.successCount++;
        this.responseTimes.push(responseTime);
    }
    
    recordError() {
        this.errorCount++;
    }
    
    getSuccessRate() {
        const total = this.successCount + this.errorCount;
        return total > 0 ? (this.successCount / total) * 100 : 0;
    }
    
    getAverageResponseTime() {
        return this.responseTimes.length > 0 
            ? this.responseTimes.reduce((a, b) => a + b) / this.responseTimes.length 
            : 0;
    }
}
```

---

## 📞 Support et Contact

### 🆘 Support Technique

- **Email :** tech@dpcr.dj
- **Téléphone :** +253 XX XX XX XX
- **Heures :** Lundi-Vendredi 8h00-17h00

### 📚 Documentation Additionnelle

- **API Interactive :** https://sms-gateway.dj/api/documentation
- **Guide Admin :** https://docs.dpcr.dj/apisms/admin
- **Status Page :** https://status.sms-gateway.dj

### 🔄 Mises à Jour API

Les changements d'API sont annoncés via :
- Email aux développeurs enregistrés
- Notifications dans l'interface admin
- Changelog : https://docs.dpcr.dj/apisms/changelog

---

## 📋 Checklist Intégration

### Avant Déploiement Production

- [ ] **Clé API obtenue** et testée en environnement staging
- [ ] **IP autorisée** ajoutée à la whitelist
- [ ] **Gestion d'erreurs** implémentée avec retry logic
- [ ] **Validation données** côté client avant envoi
- [ ] **Logs** configurés pour debugging
- [ ] **Monitoring** métriques d'usage implémenté
- [ ] **Rate limiting** côté client respecté
- [ ] **Tests** envoi SMS réussis sur numéros test

### Post-Déploiement

- [ ] **Monitoring** actif des erreurs et performance
- [ ] **Alertes** configurées pour échecs critiques
- [ ] **Sauvegarde** clé API dans gestionnaire secrets
- [ ] **Documentation** interne mise à jour
- [ ] **Formation** équipe sur utilisation API

---

**Guide API v1.0 - ApiSMS Gateway DPCR**  
*Dernière mise à jour : 11 septembre 2025*  
*© 2025 Direction de la Planification et de Contrôle Routier, Djibouti*