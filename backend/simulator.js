require('dotenv').config();
const mqtt = require('mqtt');

const MQTT_BROKER = process.env.MQTT_BROKER || 'mqtts://03c494c6704246e79abf5b75add3b57d.s1.eu.hivemq.cloud:8883';
const MQTT_USER = process.env.MQTT_USER || 'saber';
const MQTT_PASSWORD = process.env.MQTT_PASSWORD || 'Hamdi2002';

console.log('🤖 Simulateur ESP32 - Envoie données toutes les 5 secondes');
console.log('Connecting to MQTT...\n');

const client = mqtt.connect(MQTT_BROKER, {
    clientId: 'esp32-simulator-' + Math.random().toString(16).substr(2, 8),
    username: MQTT_USER,
    password: MQTT_PASSWORD
});

// État initial des capteurs
let sensors = {
    reprise: 22.0,
    soufflage: 18.0,
    salle: 21.0,
    chaud_aller: 55.0,
    chaud_retour: 45.0,
    froid_aller: 7.0,
    froid_retour: 12.0
};

function randomChange(value, range = 0.5) {
    return value + (Math.random() - 0.5) * range;
}

function publishSensorData() {
    // Mise à jour réaliste des capteurs
    sensors.reprise = Math.max(15, Math.min(30, randomChange(sensors.reprise, 0.3)));
    sensors.soufflage = Math.max(15, Math.min(25, randomChange(sensors.soufflage, 0.2)));
    sensors.salle = Math.max(18, Math.min(24, randomChange(sensors.salle, 0.1)));
    sensors.chaud_aller = Math.max(50, Math.min(60, randomChange(sensors.chaud_aller, 0.5)));
    sensors.chaud_retour = Math.max(40, Math.min(50, randomChange(sensors.chaud_retour, 0.5)));
    sensors.froid_aller = Math.max(5, Math.min(10, randomChange(sensors.froid_aller, 0.3)));
    sensors.froid_retour = Math.max(10, Math.min(15, randomChange(sensors.froid_retour, 0.3)));

    const data = {
        ...sensors,
        timestamp: new Date().toISOString()
    };

    const payload = JSON.stringify(data);
    client.publish('cta/sensors/data', payload, (err) => {
        if (!err) {
            console.log('📤 [' + new Date().toLocaleTimeString() + '] Données envoyées:');
            console.log('   Reprise:', data.reprise.toFixed(1), '°C');
            console.log('   Soufflage:', data.soufflage.toFixed(1), '°C');
            console.log('   Salle:', data.salle.toFixed(1), '°C');
            console.log('   Chaud aller:', data.chaud_aller.toFixed(1), '°C');
            console.log('   Chaud retour:', data.chaud_retour.toFixed(1), '°C');
            console.log('   Froid aller:', data.froid_aller.toFixed(1), '°C');
            console.log('   Froid retour:', data.froid_retour.toFixed(1), '°C\n');
        }
    });
}

client.on('connect', () => {
    console.log('✅ Connecté à HiveMQ\n');
    console.log('🔄 Envoi de données toutes les 5 secondes...\n');
    
    // Publier immédiatement
    publishSensorData();
    
    // Puis toutes les 5 secondes
    setInterval(publishSensorData, 5000);
});

client.on('error', (err) => {
    console.error('❌ Erreur MQTT:', err.message);
    process.exit(1);
});

process.on('SIGINT', () => {
    console.log('\n\n⏹️  Simulateur arrêté');
    client.end();
    process.exit(0);
});
