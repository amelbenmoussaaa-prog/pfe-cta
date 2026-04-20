# CTA Supervision – Node.js Full-Stack

This project is a **CTA (Centrale de Traitement d'Air)** IoT supervision dashboard for **Royal Garden Palace**.

## Architecture

- **Frontend**: Static HTML/CSS/JS served by Express from `public/`
- **Backend**: Node.js + Express (`backend/server.js`) — REST API + MQTT + MySQL
- **IoT**: ESP32 sensors communicating via HiveMQ MQTT broker
- **Database**: MySQL (auto-created on first run)

## Project Structure

```
pfe-cta/
├── backend/
│   ├── server.js          # Express server (API + MQTT + static serving)
│   ├── mock_esp32.js       # ESP32 simulator for testing
│   ├── .env                # Environment configuration
│   └── package.json        # Node.js dependencies
├── public/
│   ├── index.html          # Dashboard page
│   ├── login.html          # Login page
│   ├── machine.html        # Machine detail view
│   ├── script.js           # Frontend JavaScript
│   ├── style.css           # Frontend styles
│   ├── cta.png             # CTA schematic image
│   └── rg.jpg              # Royal Garden logo
├── esp32/
│   └── esp32_example.cpp   # Real ESP32 firmware code
├── README.md
└── START_INSTRUCTIONS.txt
```

## How to Run

### 1. Start MySQL (XAMPP)
Open XAMPP and start **MySQL only** (Apache is no longer needed).

### 2. Start the Node.js Server
```powershell
cd backend
npm install        # First time only
node server.js
```

### 3. Open the Dashboard
Open your browser to: **http://localhost:3001**

### 4. Login Credentials
- **Admin**: `admin` / `admin123` (select Administrateur)
- **Visitor**: `visiteur` / `visit123` (select Visiteur)

### 5. (Optional) Start ESP32 Simulator
```powershell
cd backend
node mock_esp32.js
```

## API Endpoints

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/api/health` | Database connection test |
| POST | `/api/login` | User authentication |
| GET | `/api/cta` | List all CTA records |
| GET | `/api/cta/:id` | Get single CTA |
| POST | `/api/cta` | Create CTA record |
| PUT | `/api/cta/:id` | Update CTA record |
| DELETE | `/api/cta/:id` | Delete CTA record |
| GET | `/api/sensor-data` | Latest sensor readings |
| POST | `/api/sensor-data` | Insert sensor reading |
| GET | `/api/telemetry` | Telemetry data (last 100) |
| GET | `/api/history` | Historical data (last 50) |
| POST | `/api/commands` | Send MQTT command |
| GET | `/api/equipments` | Equipment status list |
| PUT | `/api/equipments/status` | Update equipment status |

## MQTT Topics

- `cta/telemetry` — Sensor data from ESP32
- `cta/commands` — Commands to ESP32
