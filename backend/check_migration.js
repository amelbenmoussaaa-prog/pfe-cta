require('dotenv').config();
const mysql = require('mysql2/promise');

async function checkMigration() {
    try {
        const dbPool = await mysql.createPool({
            host: process.env.DB_HOST || 'localhost',
            user: process.env.DB_USER || 'root',
            password: process.env.DB_PASSWORD || '',
            database: process.env.DB_NAME || 'cta'
        });

        console.log('--- Database Migration Check ---');
        
        const tables = ['cta', 'cta_temperature', 'energie', 'air_quality', 'alertes', 'seuils', 'users'];
        
        for (const table of tables) {
            try {
                const [rows] = await dbPool.query(`SELECT COUNT(*) as count FROM \`${table}\``);
                console.log(`Table ${table.padEnd(20)}: ${rows[0].count} rows`);
            } catch (err) {
                console.log(`Table ${table.padEnd(20)}: ERROR (${err.message})`);
            }
        }

        console.log('\n--- Latest Telemetry ---');
        const [temps] = await dbPool.query('SELECT * FROM cta_temperature ORDER BY timestamp DESC LIMIT 1');
        console.log('Latest Temperature:', temps[0]);

        const [energy] = await dbPool.query('SELECT * FROM energie ORDER BY timestamp DESC LIMIT 1');
        console.log('Latest Energy:', energy[0]);

        const [air] = await dbPool.query('SELECT * FROM air_quality ORDER BY timestamp DESC LIMIT 1');
        console.log('Latest Air Quality:', air[0]);

        await dbPool.end();
    } catch (error) {
        console.error('Migration check failed:', error);
    }
}

checkMigration();
