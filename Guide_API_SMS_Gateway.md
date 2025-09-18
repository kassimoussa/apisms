# 📱 Guide d'Utilisation - API SMS Gateway

## 🚀 Installation et Configuration

### 1. Importer dans Postman

1. **Ouvrez Postman**
2. **Cliquez sur "Import"**
3. **Glissez-déposez les fichiers** :
   - `SMS_Gateway_API_Collection.postman_collection.json`
   - `SMS_Gateway_Environment.postman_environment.json`

### 2. Configuration de l'Environnement

1. **Sélectionnez l'environnement** "SMS Gateway - Local Development"
2. **Modifiez les variables** :
   - `base_url` : URL de votre API (ex: `http://localhost:8000` ou `https://votre-domaine.com`)
   - `api_key` : Votre clé API (format: `sk_...`)
   - `test_phone` : Votre numéro de test (ex: `77166677`)

### 3. Obtenir une Clé API

1. **Créez un compte client** via l'interface web
2. **Générez une clé API** dans la section "Clés API"
3. **Copiez la clé** et collez-la dans la variable `api_key`

## 📋 Structure de la Collection

### 🏥 Health Check
- **API Health Status** : Vérification de l'état de l'API

### 📱 SMS Simple
- **Envoyer SMS (Synchrone)** : Envoi immédiat avec réponse en temps réel
- **Envoyer SMS (Asynchrone)** : Envoi via queue Laravel
- **Liste des SMS Envoyés** : Historique de vos SMS
- **Statut d'un SMS** : Détails d'un SMS spécifique

### 📤 SMS en Masse
- **Créer Campagne SMS Basique** : Campagne simple avec envoi immédiat
- **Créer Campagne SMS Programmée** : Campagne avec date/heure programmée
- **Créer Campagne SMS Massive** : Test avec un grand nombre de destinataires
- **Liste des Campagnes** : Toutes vos campagnes
- **Statut d'une Campagne** : Détails et progression
- **Mettre en Pause une Campagne** : Contrôle en temps réel
- **Reprendre une Campagne** : Redémarrage d'une campagne

### 📊 Statistiques
- **Statistiques Générales** : Vue d'ensemble de votre utilisation
- **Statistiques Temps Réel** : Données de monitoring

### 🧪 Tests de Validation
- **Tests d'erreurs** : Validation des paramètres requis
- **Test sans authentification** : Vérification de la sécurité

### 🌍 Tests Internationaux
- **Test SMS avec Emojis** : Support Unicode complet
- **Test SMS avec Accents Français** : Caractères spéciaux
- **Test SMS avec Caractères Arabes** : Support multilingue

## 🔑 Authentification

Toutes les requêtes API utilisent l'authentification par clé API :

```
Header: X-API-Key
Value: sk_your_api_key_here
```

## 📞 Formats de Numéros Supportés

### Djibouti (Format Local)
- `77166677` (format local 8 chiffres)
- `77123456`, `77987654`, etc.

### Format International
- `+25377166677` (avec indicatif pays)

## 💬 Types de Messages Supportés

### Caractères Standards
- Texte ASCII : `Hello World`
- Longueur max : 160 caractères (SMS standard)

### UTF-8 / Unicode
- **Emojis** : `🚀🎉😊📱💻`
- **Français** : `àéèùç, êâôî, ëïüÿ`
- **Arabe** : `مرحبا، كيف حالك؟`
- Longueur max : 70 caractères (mode Unicode)

## 🎯 Exemples d'Utilisation

### SMS Simple Synchrone
```json
POST /api/v1/sms/send
{
    "to": "77166677",
    "message": "🚀 Test SMS depuis l'API!",
    "from": "TEST",
    "async": false
}
```

### SMS Simple Asynchrone
```json
POST /api/v1/sms/send
{
    "to": "77166677", 
    "message": "SMS traité en arrière-plan",
    "async": true
}
```

### Campagne SMS Basique
```json
POST /api/v1/sms/bulk
{
    "name": "Ma Première Campagne",
    "recipients": ["77166677", "77123456", "77987654"],
    "content": "🎉 Message pour tous!",
    "from": "PROMO"
}
```

### Campagne SMS Programmée
```json
POST /api/v1/sms/bulk
{
    "name": "Campagne du Nouvel An",
    "recipients": ["77166677", "77123456"],
    "content": "🎊 Bonne Année 2025!",
    "from": "VOEUX",
    "scheduled_at": "2025-01-01T00:00:00Z",
    "settings": {
        "rate_limit": 30,
        "batch_size": 50
    }
}
```

## 📊 Codes de Réponse

### Succès
- **200** : SMS envoyé avec succès (synchrone)
- **201** : Campagne créée avec succès
- **202** : SMS mis en queue (asynchrone)

### Erreurs Client
- **400** : Requête malformée
- **401** : Clé API manquante ou invalide
- **422** : Erreur de validation (paramètres invalides)
- **429** : Limite de débit dépassée

### Erreurs Serveur
- **500** : Erreur interne du serveur
- **503** : Service temporairement indisponible

## 🔧 Paramètres Avancés

### Contrôle de Performance
```json
"settings": {
    "rate_limit": 60,      // SMS par minute (1-1000)
    "batch_size": 100      // SMS traités ensemble (10-500)
}
```

### Programmation
```json
"scheduled_at": "2025-01-01T10:30:00Z"  // Format ISO 8601
```

## 🚨 Gestion d'Erreurs

### Erreur de Validation
```json
{
    "error": "Validation failed",
    "details": {
        "to": ["The to field is required."],
        "message": ["The message field is required."]
    }
}
```

### Erreur d'Authentification
```json
{
    "error": "Unauthenticated",
    "message": "Invalid API key"
}
```

### Erreur de Limite
```json
{
    "error": "Rate limit exceeded",
    "retry_after": 60
}
```

## 📈 Monitoring et Statistiques

### Statistiques Générales
```
GET /api/v1/stats
```

Retourne :
- Total SMS envoyés
- Total SMS échoués  
- Taux de succès
- Campagnes actives
- Utilisation mensuelle

### Temps Réel
```
GET /api/v1/stats/realtime
```

Retourne :
- SMS en cours d'envoi
- Queue status
- Performance réseau

## 🛠️ Workflow Recommandé

### 1. Test Initial
1. **Health Check** - Vérifier que l'API fonctionne
2. **SMS Simple** - Test avec votre numéro
3. **Vérification Statut** - Confirmer la livraison

### 2. Test de Validation
1. **Tests d'erreurs** - S'assurer que la validation fonctionne
2. **Test Unicode** - Vérifier le support des caractères spéciaux

### 3. Test de Campagne
1. **Petite campagne** - 2-3 destinataires
2. **Campagne programmée** - Test de scheduling
3. **Contrôle campagne** - Pause/Reprise

### 4. Production
1. **Surveillance** - Utiliser les endpoints de stats
2. **Optimisation** - Ajuster rate_limit et batch_size
3. **Monitoring** - Vérifier régulièrement la santé de l'API

## 🔍 Débogage

### Vérifications Communes
1. **Clé API** : Format correct (`sk_...`)
2. **Headers** : `Content-Type: application/json`
3. **Numéros** : Format valide (8 chiffres pour Djibouti)
4. **Encoding** : UTF-8 pour les caractères spéciaux

### Logs Utiles
- Vérifier les logs Laravel pour les erreurs détaillées
- Utiliser les endpoints de statut pour le suivi
- Monitorer les queues Laravel pour l'asynchrone

## 📞 Support

Pour toute question ou problème :
1. Vérifiez d'abord ce guide
2. Testez avec les exemples fournis
3. Consultez les logs d'erreur
4. Contactez le support technique

---

*Collection créée pour tester l'API SMS Gateway de manière complète et professionnelle.*