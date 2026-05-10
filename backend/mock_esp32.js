require('dotenv').config();
const mqtt = require('mqtt');

const MQTT_BROKER = process.env.MQTT_BROKER || 'mqtts://03c494c6704246e79abf5b75add3b57d.s1.eu.hivemq.cloud:8883';
const MQTT_USER = process.env.MQTT_USER || 'saber';
const MQTT_PASSWORD = process.env.MQTT_PASSWORD || 'Hamdi2002';

console.log("🤖 Simulated ESP32 (CTA Supervision) starting up...");

const client = mqtt.connect(MQTT_BROKER, {
    clientId: 'esp32-simulator-' + Math.random().toString(16).substr(2, 8),
    username: MQTT_USER,
    password: MQTT_PASSWORD
});

// ============================================================
// État initial des 7 capteurs DS18B20
// Correspond aux capteurs du programme Arduino :
//   reprise, soufflage, salle,
//   chaud_aller, chaud_retour,
//   froid_aller, froid_retour
// ============================================================
let sensors = {
    reprise: 22.0,   // Température de reprise d'air
    soufflage: 18.0,   // Température de soufflage
    salle: 21.0,   // Température de la salle
    chaud_aller: 55.0,   // Eau chaudière aller
    chaud_retour: 45.0,   // Eau chaudière retour
    froid_aller: 7.0,   // Eau glacée aller
    froid_retour: 12.0,   // Eau glacée retour
};

// États des actionneurs (contrôlables depuis le dashboard)
let isHeating = false;
let isFanOn = false;
let isVanne = false;

// Energy state
let energy = {
    tension: 230.0,     // V
    courant: 8.0,       // A
    puissance: 0,       // W (calculated)
    energie_kwh: 0.0,   // kWh cumulative
};

// Comfort & ventilation sensors
let comfort = {
    humidite: 45.0,           // % relative humidity
    pression_filtre: 50.0,    // Pa — differential pressure across filter
    vitesse_ventilateur: 800, // RPM
    aqi: 50,
    tvoc: 120,
    eco2: 450,
};

// ============================================================
// Simulation physique des températures
// ============================================================
function simulateSensors() {
    const noise = () => (Math.random() - 0.5) * 0.3;

    if (isHeating) {
        // Chaudière active → eau chaudière monte, soufflage monte
        sensors.chaud_aller = Math.min(80, sensors.chaud_aller + 0.5 + noise());
        sensors.chaud_retour = Math.min(70, sensors.chaud_retour + 0.3 + noise());
        sensors.soufflage = Math.min(40, sensors.soufflage + 0.4 + noise());
        sensors.salle += 0.1 + noise() * 0.2;
    } else {
        // Refroidissement naturel
        sensors.chaud_aller = Math.max(20, sensors.chaud_aller - 0.3 + noise());
        sensors.chaud_retour = Math.max(20, sensors.chaud_retour - 0.2 + noise());
        sensors.soufflage += noise() * 0.2;
    }

    if (isFanOn) {
        // Ventilation → rapproche reprise/soufflage de la température salle
        sensors.reprise += (sensors.salle - sensors.reprise) * 0.1 + noise() * 0.1;
        sensors.soufflage += noise() * 0.2;
    }

    if (isVanne) {
        // Vanne ouverte → eau glacée circule, froid_retour monte légèrement
        sensors.froid_retour = Math.min(20, sensors.froid_retour + 0.2 + noise());
        sensors.salle = Math.max(18, sensors.salle - 0.1 + noise() * 0.1);
    } else {
        sensors.froid_retour = Math.max(7, sensors.froid_retour - 0.1 + noise());
    }

    // Fluctuations naturelles sur les autres capteurs
    sensors.salle = clamp(sensors.salle + noise() * 0.1, 16, 30);
    sensors.reprise = clamp(sensors.reprise + noise() * 0.1, 16, 30);
    sensors.froid_aller = clamp(sensors.froid_aller + noise() * 0.1, 5, 15);

    // Arrondi à 1 décimale (comme le vrai capteur DS18B20)
    for (const key in sensors) {
        sensors[key] = parseFloat(sensors[key].toFixed(1));
    }

    // Simulate energy: tension ~230V, courant varies with heating/cooling load
    energy.tension = parseFloat((230 + (Math.random() - 0.5) * 4).toFixed(1));
    energy.courant = parseFloat((isHeating ? 12 + (Math.random() - 0.5) * 2 : 6 + (Math.random() - 0.5) * 1.5).toFixed(2));
    energy.puissance = parseFloat((energy.tension * energy.courant).toFixed(1));
    // Increment cumulative kWh: P(W) * interval(3s) / 3,600,000 ms → kWh
    energy.energie_kwh = parseFloat((energy.energie_kwh + energy.puissance * (3 / 3600000)).toFixed(6));

    // Simulate humidity: rises when fan off, drops when fan on, natural drift
    const humiTarget = isFanOn ? 40 : 55;
    comfort.humidite = clamp(
        comfort.humidite + (humiTarget - comfort.humidite) * 0.02 + (Math.random() - 0.5) * 0.5,
        30, 75
    );
    comfort.humidite = parseFloat(comfort.humidite.toFixed(1));

    // Simulate filter differential pressure: slowly rises over time (clogging), resets on maintenance
    comfort.pression_filtre = clamp(
        comfort.pression_filtre + 0.02 + (Math.random() - 0.5) * 0.3,
        10, 200
    );
    comfort.pression_filtre = parseFloat(comfort.pression_filtre.toFixed(1));

    // Simulate fan RPM: higher when heating, varies with load
    const rpmTarget = isFanOn ? 1200 : (isHeating ? 900 : 600);
    comfort.vitesse_ventilateur = clamp(
        comfort.vitesse_ventilateur + (rpmTarget - comfort.vitesse_ventilateur) * 0.05 + (Math.random() - 0.5) * 10,
        300, 1500
    );
    comfort.vitesse_ventilateur = Math.round(comfort.vitesse_ventilateur);

    // Simulate air quality: CO2 drifts based on ventilation
    const eco2Target = isFanOn ? 420 : 700;
    comfort.eco2 = Math.round(clamp(
        comfort.eco2 + (eco2Target - comfort.eco2) * 0.03 + (Math.random() - 0.5) * 10,
        400, 1500
    ));
    comfort.tvoc = Math.round(clamp(comfort.tvoc + (Math.random() - 0.5) * 5, 50, 500));
    comfort.aqi = Math.round(clamp(comfort.eco2 / 15, 10, 100));
}

function clamp(val, min, max) {
    return Math.max(min, Math.min(max, val));
}

// ============================================================
// MQTT
// ============================================================
client.on('connect', () => {
    console.log("🟢 Simulator connected to HiveMQ Broker!");

    client.subscribe('cta/commands/1', (err) => {
        if (!err) console.log("📻 Listening for commands on 'cta/commands/1'...");
    });

    // Publier les données toutes les 3 secondes (identique au delay(3000) Arduino)
    setInterval(() => {
        simulateSensors();

        const payload = JSON.stringify({
            mac: 'AA:BB:CC:DD:EE:01',
            // Temperatures
            reprise: sensors.reprise,
            soufflage: sensors.soufflage,
            salle: sensors.salle,
            chaud_aller: sensors.chaud_aller,
            chaud_retour: sensors.chaud_retour,
            froid_aller: sensors.froid_aller,
            froid_retour: sensors.froid_retour,
            // Energy
            tension: energy.tension,
            courant: energy.courant,
            puissance: energy.puissance,
            energie_kwh: energy.energie_kwh,
            // Comfort & ventilation
            humidite: comfort.humidite,
            pression_filtre: comfort.pression_filtre,
            vitesse_ventilateur: comfort.vitesse_ventilateur,
            // Air quality
            aqi: comfort.aqi,
            tvoc: comfort.tvoc,
            eco2: comfort.eco2,
        });

        console.log("========= CTA SUPERVISION =========");
        console.log(`Reprise        : ${sensors.reprise} °C`);
        console.log(`Soufflage      : ${sensors.soufflage} °C`);
        console.log(`Salle          : ${sensors.salle} °C`);
        console.log(`Tension        : ${energy.tension} V  |  Courant : ${energy.courant} A`);
        console.log(`Puissance      : ${energy.puissance} W  |  Énergie : ${energy.energie_kwh} kWh`);
        console.log(`Humidité       : ${comfort.humidite} %  |  Filtre ΔP : ${comfort.pression_filtre} Pa`);
        console.log(`Ventilateur    : ${comfort.vitesse_ventilateur} RPM  |  eCO2 : ${comfort.eco2} ppm`);
        console.log("==================================\n");

        client.publish('cta/telemetry', payload);
    }, 3000);
});

// ============================================================
// Réaction aux commandes du dashboard
// ============================================================
client.on('message', (topic, message) => {
    if (topic === 'cta/commands/1') {
        try {
            const cmd = JSON.parse(message.toString());
            console.log(`\n===========================================`);
            console.log(`🚨 COMMAND RECEIVED FROM DASHBOARD:`, cmd);
            console.log(`===========================================\n`);

            if (cmd.heating !== undefined) { isHeating = cmd.heating; console.log(`🔥 Heating  → ${isHeating}`); }
            if (cmd.fan !== undefined) { isFanOn = cmd.fan; console.log(`💨 Fan      → ${isFanOn}`); }
            if (cmd.vanne !== undefined) { isVanne = cmd.vanne; console.log(`🔧 Vanne    → ${isVanne}`); }
            if (cmd.extractor !== undefined) { console.log(`🌬️ Extractor → ${cmd.extractor}`); }
            if (cmd.etat !== undefined) {
                isFanOn = (cmd.etat === 'actif');
                console.log(`⚡ Machine state → ${cmd.etat}`);
            }

        } catch (e) {
            console.error("Could not parse command:", e);
        }
    }
});

client.on('error', (err) => {
    console.error("Broker connection error:", err);
});
