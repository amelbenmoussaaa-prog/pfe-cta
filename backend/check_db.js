require('dotenv').config();
const mysql = require('mysql2/promise');

async function checkDb() {
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: process.env.DB_NAME || 'cta'
    });

    console.log("--- DEVICES ---");
    const [devices] = await connection.query("SELECT * FROM devices");
    console.table(devices);

    console.log("\n--- SENSORS ---");
    const [sensors] = await connection.query("SELECT * FROM sensors");
    console.table(sensors);

    console.log("\n--- THRESHOLDS ---");
    const [thresholds] = await connection.query("SELECT * FROM thresholds");
    console.table(thresholds);

    console.log("\n--- ALARMS ---");
    const [alarms] = await connection.query("SELECT * FROM alarms");
    console.table(alarms);

    await connection.end();
}

checkDb().catch(console.error);
