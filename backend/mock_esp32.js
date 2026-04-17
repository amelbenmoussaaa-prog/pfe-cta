require('dotenv').config();
const mqtt = require('mqtt');

// Connect identical to the Node.js backend using the HiveMQ URL
const MQTT_BROKER = process.env.MQTT_BROKER || 'mqtts://d5125818a1ee4c1a950ddf610a8f07d6.s1.eu.hivemq.cloud:8883';
const MQTT_USER = process.env.MQTT_USER || 'adibos';
const MQTT_PASSWORD = process.env.MQTT_PASSWORD || 'Trontocasino1';

console.log("🤖 Simulated ESP32 starting up...");

const client = mqtt.connect(MQTT_BROKER, {
    clientId: 'esp32-simulator-' + Math.random().toString(16).substr(2, 8),
    username: MQTT_USER,
    password: MQTT_PASSWORD
});

// Current theoretical room state
let temperature = 22.0;
let humidity = 60.0;
let current = 2.4;
let isHeating = false;
let isFanOn = false;

client.on('connect', () => {
    console.log("🟢 Simulator connected to HiveMQ Broker!");
    
    // Subscribe to commands coming from the web dashboard
    client.subscribe('cta/commands', (err) => {
        if (!err) console.log("📻 Listening for Web Dashboard Commands...");
    });

    // Start publishing fake data every 3 seconds
    setInterval(() => {
        // Natural fluctuations
        const ambientShift = (Math.random() - 0.5) * 0.4;
        
        // If heating is ON, temperature goes up fast. Otherwise it drops slowly back to ~20
        if (isHeating) {
            temperature += 0.8;
            current = 9.5; // Heater takes lots of current!
            humidity -= 0.5; // Gets dry
        } else if (isFanOn) {
            temperature += ambientShift;
            current = 1.2; // Just the fan running
        } else {
            temperature -= 0.1; // Room cools down slowly
            if (temperature < 20.0) temperature = 20.0; // Stop cooling at 20C
            current = 0.1; // Standby
        }
        
        // Constrain values to look realistic
        temperature = Math.max(15, Math.min(35, temperature));
        humidity = Math.max(40, Math.min(80, humidity));

        const payload = JSON.stringify({
            temperature: parseFloat(temperature.toFixed(1)),
            humidity: parseFloat(humidity.toFixed(1)),
            current: parseFloat(current.toFixed(1))
        });

        console.log(`📤 Publishing Sensor Data -> ${payload}`);
        client.publish('cta/sensors', payload);
    }, 3000);
});

// Reacting to dashboard commands!
client.on('message', (topic, payload) => {
    if (topic === 'cta/commands') {
        try {
            const cmd = JSON.parse(payload.toString());
            console.log(`\n===========================================`);
            console.log(`🚨 COMMAND RECEIVED FROM DASHBOARD:`, cmd);
            console.log(`===========================================\n`);
            
            if (cmd.heating !== undefined) isHeating = cmd.heating;
            if (cmd.fan !== undefined) isFanOn = cmd.fan;
            
        } catch(e) {
            console.log("Could not parse command", e);
        }
    }
});

client.on('error', (err) => {
    console.log("Broker Connection Error: ", err);
});
