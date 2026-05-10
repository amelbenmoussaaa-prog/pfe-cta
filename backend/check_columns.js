require('dotenv').config();
const mysql = require('mysql2/promise');

async function checkColumns() {
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: process.env.DB_NAME || 'cta'
    });

    const [columns] = await connection.query("SHOW COLUMNS FROM alarms");
    console.table(columns);

    await connection.end();
}

checkColumns().catch(console.error);
