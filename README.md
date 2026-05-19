# CTA - Système de Supervision et de Contrôle

Application web de supervision et de contrôle d'une Centrale de Traitement d'Air (CTA) en temps réel.

## Technologies utilisées

- **Backend** : Node.js, Express.js, Socket.io
- **Base de données** : MySQL
- **Communication IoT** : MQTT (HiveMQ Cloud)
- **Frontend** : HTML, CSS, JavaScript
- **Matériel** : Cartes ESP32/ESP8266

## Fonctionnalités

- Supervision en temps réel des températures (reprise, soufflage, salle, chaud/froid)
- Surveillance de la qualité d'air (CO₂, AQI, TVOC)
- Suivi de la consommation énergétique (tension, courant, puissance, kWh)
- Système d'alertes automatiques avec seuils configurables
- Planification automatique (mode chauffage/climatisation avec horaires)
- Gestion de la configuration réseau des cartes ESP (IP fixe, WiFi)
- Authentification avec rôles (admin / visiteur)

## Structure du projet

```
pfe-cta/
├── backend/
│   ├── server.js        # Serveur principal (API REST + MQTT + WebSocket)
│   ├── package.json     # Dépendances Node.js
│   └── .env             # Variables d'environnement (ne pas publier)
├── public/
│   ├── index.html       # Dashboard principal
│   ├── machine.html     # Page détail machine
│   ├── login.html       # Page de connexion
│   ├── script.js        # Logique frontend
│   └── style.css        # Styles
└── README.md
```

## Installation

### Prérequis

- Node.js v18+
- MySQL 8.0+
- Un broker MQTT (HiveMQ Cloud ou local)

### Étapes

**1. Cloner le projet**
```bash
git clone <url_du_repo>
cd pfe-cta
```

**2. Installer les dépendances**
```bash
cd backend
npm install
```

**3. Importer la base de données**

Dans MySQL Workbench ou via la commande :
```bash
mysql -u root -p < cta.sql
```

**4. Configurer les variables d'environnement**

Créer le fichier `backend/.env` :
```
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=votre_mot_de_passe
DB_NAME=cta
PORT=3001
MQTT_BROKER=mqtts://votre_broker:8883
MQTT_USER=votre_user
MQTT_PASSWORD=votre_password
```

**5. Lancer le serveur**
```bash
cd backend
node server.js
```

**6. Accéder à l'application**

Ouvrir dans le navigateur : `http://localhost:3001`

## Connexion par défaut

| Utilisateur | Mot de passe | Rôle  |
|-------------|-------------|-------|
| admin       | admin123    | Admin |

## Tables de la base de données

| Table | Description |
|-------|-------------|
| `cta` | Machines CTA |
| `cta_temperature` | Historique des températures |
| `energie` | Données énergétiques |
| `air_quality` | Qualité d'air |
| `alertes` | Alertes et alarmes |
| `schedules` | Planifications horaires |
| `seuils` | Seuils d'alarme configurables |
| `users` | Utilisateurs |
| `device_config` | Configuration réseau des ESP |

## Configuration réseau des ESP

Les cartes ESP32/ESP8266 utilisent des adresses IP fixes configurées directement dans le firmware. Les informations réseau (IP, gateway, subnet, WiFi) sont gérables depuis la section **Configuration Réseau** du dashboard.
