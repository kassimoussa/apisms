# 📮 Collection Postman - ApiSMS Gateway DPCR

Collection complète de tests Postman pour l'API SMS Gateway DPCR avec tests automatisés, gestion d'environnements et documentation intégrée.

## 📁 Fichiers Inclus

### 📋 Collection
- **`ApiSMS_Gateway_DPCR.postman_collection.json`** - Collection principale avec tous les tests

### 🌍 Environnements
- **`ApiSMS_Gateway_DPCR.postman_environment.json`** - Environnement Production
- **`ApiSMS_Gateway_DPCR_Staging.postman_environment.json`** - Environnement Staging/Test

## 🚀 Installation Rapide

### 1. Importer dans Postman

#### Via l'interface Postman :
1. Ouvrir Postman
2. Cliquer sur **"Import"** (bouton en haut à gauche)
3. Glisser-déposer les 3 fichiers JSON ou cliquer **"Upload Files"**
4. Cliquer **"Import"** pour confirmer

#### Via URL (si hébergé) :
```
Collection: https://your-domain.com/postman/ApiSMS_Gateway_DPCR.postman_collection.json
Environnement Prod: https://your-domain.com/postman/ApiSMS_Gateway_DPCR.postman_environment.json
```

### 2. Configuration des Variables

#### Variables d'Environnement à Configurer :

| Variable | Description | Exemple Production | Exemple Staging |
|----------|-------------|-------------------|-----------------|
| `base_url` | URL de base API | `https://sms-gateway.dj/api/v1` | `https://staging.sms-gateway.dj/api/v1` |
| `api_key` | Clé API client | `sk_prod_12345...` | `sk_test_67890...` |
| `test_phone` | Numéro de test | `77166677` | `77166677` |
| `from_number` | Expéditeur défaut | `11123` | `11123` |
| `health_token` | Token health check | `prod_health_token` | `staging_health_token` |

#### Comment configurer :
1. Sélectionner l'environnement (Production ou Staging)
2. Cliquer sur l'œil 👁️ à côté du nom d'environnement
3. Cliquer **"Edit"** 
4. Remplir les valeurs **"Current Value"** pour chaque variable
5. **Sauvegarder**

## 📊 Structure de la Collection

### 🏥 **Health & Connectivity**
Tests de connectivité et vérification système
- **System Health Check** - État général du système
- **API Documentation** - Accès documentation Swagger

### 📤 **SMS Sending** 
Tests d'envoi de SMS complets
- **Send SMS - Synchronous** - Envoi immédiat avec réponse
- **Send SMS - Asynchronous** - Envoi via queue
- **Send SMS - With Custom Sender** - Expéditeur personnalisé

### 📊 **SMS Status & Tracking**
Suivi et récupération statuts
- **Get SMS Status** - Statut SMS spécifique
- **Get SMS Status - Async** - Suivi SMS asynchrone  
- **Get SMS Status - Not Found** - Gestion erreur 404

### 📋 **SMS Listing**
Tests de listing et pagination
- **List SMS - All** - Liste complète paginée
- **List SMS - Delivered Only** - Filtre par statut
- **List SMS - Outbound Only** - Filtre par direction

### 📈 **Statistics**
Tests des statistiques et analytics
- **Get Monthly Stats** - Statistiques mensuelles
- **Get Weekly Stats** - Statistiques hebdomadaires
- **Get Realtime Stats** - Stats temps réel (24h)

### 🚨 **Error Handling**
Tests de gestion d'erreurs
- **Invalid API Key** - Clé API incorrecte
- **Missing API Key** - Authentification manquante
- **Invalid Phone Number** - Numéro invalide
- **Missing Required Fields** - Champs obligatoires
- **Message Too Long** - Message > 160 caractères

### ⚡ **Performance Tests**
Tests de performance et temps réponse
- **Response Time - SMS Send** - Performance envoi
- **Response Time - SMS List** - Performance listing
- **Response Time - Stats** - Performance statistiques

## 🧪 Tests Automatiques Intégrés

### ✅ **Tests Automatisés Inclus**

Chaque requête inclut des tests JavaScript automatiques :

#### Tests de Base
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response time is acceptable", function () {
    pm.expect(pm.response.responseTime).to.be.below(2000);
});
```

#### Tests de Structure
```javascript
pm.test("SMS data structure is correct", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('data');
    pm.expect(jsonData.data).to.have.property('id');
    pm.expect(jsonData.data).to.have.property('status');
});
```

#### Tests de Logique Métier
```javascript
pm.test("SMS status is valid", function () {
    var jsonData = pm.response.json();
    pm.expect(['pending', 'sent', 'delivered', 'failed']).to.include(jsonData.data.status);
});
```

#### Variables Dynamiques
```javascript
// Sauvegarde automatique ID SMS pour tests suivants
pm.test("Save SMS ID for later tests", function () {
    var jsonData = pm.response.json();
    pm.collectionVariables.set("last_sms_id", jsonData.data.id);
});
```

## 🏃‍♂️ Utilisation

### 1. **Tests Individuels**
Sélectionner une requête → Cliquer **"Send"** → Voir résultats onglet **"Test Results"**

### 2. **Exécution Collection Complète**
```bash
# Via interface Postman
1. Clic droit sur "ApiSMS Gateway DPCR"
2. Sélectionner "Run collection"
3. Choisir environnement
4. Cliquer "Run ApiSMS Gateway DPCR"

# Via Newman CLI
npm install -g newman
newman run ApiSMS_Gateway_DPCR.postman_collection.json \
    -e ApiSMS_Gateway_DPCR.postman_environment.json \
    --reporters html,cli
```

### 3. **Tests de Performance**
```bash
# Avec délai entre requêtes
newman run collection.json -e environment.json --delay-request 1000

# Tests de charge
newman run collection.json -e environment.json --iteration-count 10
```

## 📈 **Monitoring et Rapports**

### Rapports HTML Newman
```bash
newman run collection.json -e environment.json \
    --reporters html \
    --reporter-html-export report.html
```

### Intégration CI/CD
```yaml
# GitHub Actions example
- name: Run API Tests
  run: |
    newman run postman/collection.json \
      -e postman/environment.json \
      --reporters cli,json \
      --reporter-json-export results.json
```

### Monitoring Continu avec Postman Monitor
1. Dans Postman : **Monitors** → **Create Monitor**
2. Sélectionner collection et environnement
3. Configurer fréquence (ex: toutes les 5 minutes)
4. Ajouter alertes email/Slack sur échecs

## 🔧 **Customisation Avancée**

### Ajouter Nouveaux Tests
```javascript
// Template test personnalisé
pm.test("Mon test personnalisé", function () {
    var jsonData = pm.response.json();
    // Vos assertions ici
    pm.expect(jsonData.custom_field).to.exist;
});
```

### Variables Dynamiques
```javascript
// Générer données uniques
pm.collectionVariables.set("unique_id", pm.globals.replaceIn("{{$randomUUID}}"));
pm.collectionVariables.set("timestamp", new Date().toISOString());
```

### Scripts Pre-Request
```javascript
// Script avant requête
pm.request.headers.add({
    key: "X-Request-ID", 
    value: pm.globals.replaceIn("{{$randomUUID}}")
});
```

## 📞 **Dépannage**

### Problèmes Fréquents

#### ❌ "Variable not found"
**Solution :** Vérifier que l'environnement est sélectionné et variables configurées

#### ❌ "SSL Error"
**Solution :** Dans Postman Settings → désactiver "SSL certificate verification"

#### ❌ "Request timeout"
**Solution :** Augmenter timeout dans Postman Settings → Request timeout

#### ❌ Tests échouent
**Solution :** 
1. Vérifier clé API valide
2. Confirmer URL base correcte  
3. Tester requête individuelle d'abord

### Logs de Debug
```javascript
// Ajouter dans tests pour debugging
console.log("Response body:", pm.response.text());
console.log("Response headers:", pm.response.headers);
console.log("Variables:", pm.collectionVariables.toObject());
```

## 🔄 **Workflow Recommandé**

### Pour Développement
1. **Health Check** - Vérifier connectivité
2. **Send SMS Sync** - Test envoi de base
3. **Get SMS Status** - Vérifier statut  
4. **Error Handling** - Tests robustesse

### Pour Tests d'Intégration
1. Exécuter **collection complète** avec Newman
2. Vérifier tous tests passent
3. Analyser rapport HTML généré
4. Documenter échecs éventuels

### Pour Monitoring Production
1. Configurer **Postman Monitor**
2. Tests critiques uniquement (Health + Send SMS)
3. Alertes sur échecs consécutifs
4. Fréquence adaptée (5-15 minutes)

---

## 🎯 **Métriques de Test**

La collection mesure automatiquement :
- ✅ **Temps de réponse** par endpoint
- ✅ **Codes de statut HTTP** conformité
- ✅ **Structure des réponses** validation
- ✅ **Logique métier** SMS workflow
- ✅ **Gestion d'erreurs** robustesse
- ✅ **Performance** sous charge

### Objectifs de Performance
- **Health Check** : < 1000ms
- **Send SMS** : < 5000ms  
- **List SMS** : < 2000ms
- **Statistics** : < 3000ms

---

**Collection Postman v1.0 - ApiSMS Gateway DPCR**  
*Tests automatisés complets pour API SMS Gateway*  
*© 2025 Direction de la Planification et de Contrôle Routier, Djibouti*