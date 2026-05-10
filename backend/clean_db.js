require('dotenv').config();
const mysql = require('mysql2/promise');

async function cleanDatabase() {
    let connection;
    try {
        connection = await mysql.createConnection({
            host: process.env.DB_HOST || 'localhost',
            user: process.env.DB_USER || 'root',
            password: process.env.DB_PASSWORD || 'admin',
            database: process.env.DB_NAME || 'cta'
        });

        console.log('--- Force Cleanup Database ---');
        
        // Disable foreign key checks to allow dropping tables in any order
        await connection.query('SET FOREIGN_KEY_CHECKS = 0');
        
        const [tables] = await connection.query('SHOW TABLES');
        const dbName = process.env.DB_NAME || 'cta';
        const tableKey = `Tables_in_${dbName}`;

        for (const row of tables) {
            const tableName = row[tableKey];
            if (tableName === 'users') {
                console.log(`Skipping ${tableName}`);
                continue;
            }
            console.log(`Dropping table: ${tableName}`);
            await connection.query(`DROP TABLE IF EXISTS \`${tableName}\``);
        }

        await connection.query('SET FOREIGN_KEY_CHECKS = 1');
        console.log('Cleanup complete!');
        
    } catch (error) {
        console.error('Cleanup failed:', error);
    } finally {
        if (connection) await connection.end();
    }
}

cleanDatabase();
