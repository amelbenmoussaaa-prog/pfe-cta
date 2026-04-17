# CTA Supervision - API & MQTT Integration

This project now includes:

- `api/cta.php`: CRUD endpoint for the `cta` table
- `api/sensor_data.php`: backend endpoint for sensor measurements in `donnees_capteurs`
- `mqtt_listener.js`: HiveMQ MQTT listener service that forwards IoT telemetry to the PHP backend
- `package.json`: npm metadata for the MQTT listener

## How to run

1. Start Apache in XAMPP so the PHP app is reachable in the browser.
2. Open `http://localhost/ctaaaa/machine.php` once the web server is running.
3. From the project folder, run the MQTT listener:

```powershell
cd C:\xampp\htdocs\ctaaaa
npm start
```

## HiveMQ setup

The listener subscribes to the topic pattern:

- `ctaaaa/+/telemetry`

Payload should be JSON with `capteur_id` and `valeur`, for example:

```json
{
  "capteur_id": 3,
  "valeur": 22.5
}
```

You can override the broker or API URL by setting environment variables:

- `MQTT_BROKER`
- `MQTT_TOPICS`
- `API_URL`

Example:

```powershell
$env:MQTT_BROKER = 'mqtt://broker.hivemq.com:1883'
$env:API_URL = 'http://localhost/ctaaaa/api/sensor_data.php'
npm start
```
