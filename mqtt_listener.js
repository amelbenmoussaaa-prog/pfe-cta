const mqtt = require('mqtt');

const MQTT_BROKER = process.env.MQTT_BROKER || 'mqtt://broker.hivemq.com:1883';
const MQTT_TOPICS = (process.env.MQTT_TOPICS || 'ctaaaa/+/telemetry').split(',').map(t => t.trim());
const API_URL = process.env.API_URL || 'http://localhost/ctaaaa/api/sensor_data.php';

const clientId = 'ctaaaa-listener-' + Math.random().toString(16).slice(2);
console.log(`Starting MQTT listener with clientId=${clientId}`);
console.log(`Broker=${MQTT_BROKER}`);
console.log(`Topics=${MQTT_TOPICS.join(', ')}`);

const client = mqtt.connect(MQTT_BROKER, {
  clientId,
  clean: true,
  reconnectPeriod: 5000,
  connectTimeout: 30_000,
});

client.on('connect', () => {
  console.log('Connected to MQTT broker');
  MQTT_TOPICS.forEach(topic => {
    client.subscribe(topic, { qos: 0 }, (err) => {
      if (err) {
        console.error(`Unable to subscribe to topic ${topic}`, err);
      } else {
        console.log(`Subscribed to ${topic}`);
      }
    });
  });
});

client.on('message', async (topic, payload) => {
  const message = payload.toString();
  console.log(`MQTT message received: ${topic} -> ${message}`);

  let data;
  try {
    data = JSON.parse(message);
  } catch (err) {
    return console.warn('Invalid JSON payload, ignoring message');
  }

  const capteurId = parseInt(data.capteur_id || data.capteurId || data.sensorId || data.id, 10);
  const valeur = data.valeur ?? data.value ?? data.v;
  const dateMesure = data.date_mesure || data.dateMesure || new Date().toISOString().slice(0, 19).replace('T', ' ');

  if (!capteurId || typeof valeur === 'undefined') {
    return console.warn('Payload does not contain capteur_id and valeur');
  }

  try {
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ capteur_id: capteurId, valeur, date_mesure: dateMesure }),
    });
    if (!response.ok) {
      const text = await response.text();
      console.error('Backend API error:', response.status, text);
    } else {
      console.log('Sensor data forwarded to backend successfully');
    }
  } catch (err) {
    console.error('Failed to send sensor data to backend', err);
  }
});

client.on('error', (err) => {
  console.error('MQTT client error:', err);
});

client.on('reconnect', () => {
  console.log('Reconnecting to MQTT broker...');
});

client.on('close', () => {
  console.log('MQTT connection closed');
});
