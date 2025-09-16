# 📡 Configuration Kannel pour SMS Gateway

Ce guide explique comment configurer Kannel pour envoyer les réponses SMS vers votre ApiSMS Gateway.

## 🔗 URLs de Webhook

### Webhook pour Messages Entrants (MO - Mobile Originated)
```
URL: https://votre-domaine.com/webhooks/kannel/mo
Paramètres: ?from={from}&to={to}&text={text}&timestamp={timestamp}
```

### Webhook pour Rapports de Livraison (DLR - Delivery Reports)
```
URL: https://votre-domaine.com/webhooks/kannel/dlr
Paramètres: ?id={kannel_id}&status={status}&error_code={error_code}
```

## ⚙️ Configuration Kannel

### 1. Configuration SMS-Service pour Messages Entrants

Ajoutez cette section dans votre fichier `kannel.conf` :

```ini
# SMS Service pour traiter les messages entrants
group = sms-service
keyword = default
url = "https://votre-domaine.com/webhooks/kannel/mo?from=%p&to=%P&text=%a&timestamp=%t"
max-messages = 0
get-url = true
catch-all = true
text = ""
```

### 2. Configuration SMSC avec DLR

```ini
# Configuration SMSC avec DLR activé
group = smsc
smsc = [type_de_votre_smsc]
# ... autres paramètres SMSC ...

# URL de callback pour les DLR
dlr-url = "https://votre-domaine.com/webhooks/kannel/dlr?id=%i&status=%d&error_code=%e"
```

### 3. Variables Kannel Importantes

| Variable | Description | Utilisation |
|----------|-------------|-------------|
| `%p` | Numéro expéditeur (from) | Messages entrants |
| `%P` | Numéro destinataire (to) | Messages entrants |
| `%a` | Contenu du message | Messages entrants |
| `%t` | Timestamp | Messages entrants |
| `%i` | ID du message Kannel | DLR |
| `%d` | Statut de livraison | DLR |
| `%e` | Code d'erreur | DLR |

## 🔒 Sécurité

### IP Whitelisting
Configurez le middleware IP dans votre `.env` :

```env
# Ajoutez les IPs de vos serveurs Kannel
ALLOWED_IPS=192.168.1.100,10.0.0.50
IP_WHITELIST_ENABLED=true
```

### Headers de Sécurité
Les webhooks incluent automatiquement :
- Rate limiting
- Audit logging
- Validation des paramètres

## 📊 Monitoring

### Logs des Webhooks
Les webhooks sont automatiquement loggés dans :
- `storage/logs/laravel.log`
- Channel spécifique : `webhook`

### Métriques Disponibles
- Total messages entrants
- Messages par jour/heure
- Taux d'erreur des webhooks
- Performance des DLR

## 🧪 Test des Webhooks

### Test Manuel avec curl

```bash
# Test message entrant
curl "https://votre-domaine.com/webhooks/kannel/mo?from=77123456&to=11123&text=Test%20message&timestamp=2025-09-15T10:00:00Z"

# Test DLR
curl "https://votre-domaine.com/webhooks/kannel/dlr?id=12345&status=1"
```

### Test avec Postman
Utilisez la collection Postman fournie dans le dossier `/postman/`.

## 🔧 Configuration Avancée

### Gestion des Erreurs Kannel

```ini
# Retry configuration
group = sms-service
keyword = default
url = "https://votre-domaine.com/webhooks/kannel/mo?from=%p&to=%P&text=%a"

# Timeout et retry
connection-timeout = 30
max-pending-requests = 100
retry-after = 60
```

### Load Balancing

```ini
# Multiple URLs pour high availability
group = sms-service
keyword = default
url = "https://gateway1.votre-domaine.com/webhooks/kannel/mo?from=%p&to=%P&text=%a"
url = "https://gateway2.votre-domaine.com/webhooks/kannel/mo?from=%p&to=%P&text=%a"
```

## 📱 Types de Messages Supportés

### Messages Entrants (MO)
- ✅ SMS texte standard
- ✅ Messages Unicode
- ✅ Messages longs (concaténés)
- ✅ Metadata complète

### Rapports de Livraison (DLR)
- ✅ Délivré (status=1)
- ✅ Échec (status=2) 
- ✅ En attente (status=4)
- ✅ Rejeté SMSC (status=8)
- ✅ Inconnu SMSC (status=16)

## 🎯 Cas d'Usage

### 1. Service Client Automatique
```ini
group = sms-service
keyword = "INFO"
url = "https://votre-domaine.com/webhooks/kannel/mo?from=%p&to=%P&text=%a&service=info"
text = "Merci pour votre demande d'information"
```

### 2. Confirmation de Commande
```ini
group = sms-service
keyword = "CONFIRM"  
url = "https://votre-domaine.com/webhooks/kannel/mo?from=%p&to=%P&text=%a&service=confirm"
text = "Votre commande est confirmée"
```

## 🛠️ Dépannage

### Problèmes Courants

1. **Webhook ne répond pas**
   - Vérifier la connectivité réseau
   - Contrôler les logs Laravel
   - Tester l'URL manuellement

2. **Messages non reçus**
   - Vérifier la configuration `catch-all`
   - Contrôler les keywords
   - Vérifier l'URL de callback

3. **DLR manqués**
   - Vérifier l'URL DLR dans la config SMSC
   - Contrôler le format des paramètres
   - Tester avec des messages simples

### Debug Mode

```bash
# Activer les logs détaillés
php artisan log:clear
tail -f storage/logs/laravel.log | grep webhook
```

## 🚀 Performance

### Optimisations Recommandées

1. **Queue Processing**
   - Les DLR sont traités en arrière-plan
   - Configuration Redis recommandée

2. **Database Indexing**
   - Index sur `direction` dans `sms_messages`
   - Index sur `from` et `to` pour recherches

3. **Caching**
   - Cache des statistiques
   - Cache des configurations client

---

## 📞 Support

Pour toute assistance :
1. Consultez les logs dans l'interface admin
2. Utilisez l'outil de test intégré
3. Vérifiez la documentation API

**Configuration testée avec Kannel 1.4.5+ et PHP 8.2+**