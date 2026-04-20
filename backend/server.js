require('dotenv').config();
const express = require('express');
const path = require('path');
const cors = require('cors');
const mysql = require('mysql2/promise');
const mqtt = require('mqtt');

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware
app.use(cors());
app.use(express.json());

// Serve frontend static files from ../public
app.use(express.static(path.join(__dirname, '..', 'public')));

// MySQL connection setup (creates DB and tables automatically)
let dbPool;
async function initializeDatabase() {
    try {
        const connection = await mysql.createConnection({
            host: process.env.DB_HOST || 'localhost',
            user: process.env.DB_USER || 'root',
            password: process.env.DB_PASSWORD || '',
        });

        const dbName = process.env.DB_NAME || 'cta';
        await connection.query(`CREATE DATABASE IF NOT EXISTS \`${dbName}\``);
        await connection.end();

        dbPool = mysql.createPool({
            host: process.env.DB_HOST || 'localhost',
            user: process.env.DB_USER || 'root',
            password: process.env.DB_PASSWORD || '',
            database: dbName,
            waitForConnections: true,
            connectionLimit: 10,
            queueLimit: 0
        });

        // 1. Users Table
        await dbPool.query(`
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(150) NOT NULL,
                role ENUM('admin', 'visiteur') NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);

        // Seed default users if none exist
        const [users] = await dbPool.query("SELECT * FROM users");
        if (users.length === 0) {
            await dbPool.query(`INSERT INTO users (username, password, role) VALUES ('admin', 'admin123', 'admin')`);
            await dbPool.query(`INSERT INTO users (username, password, role) VALUES ('visiteur', 'visit123', 'visiteur')`);
            console.log('Seeded default users.');
        }

        // 2. Equipments Table
        await dbPool.query(`
            CREATE TABLE IF NOT EXISTS equipments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                location VARCHAR(100),
                status ENUM('actif', 'inactif', 'maintenance') DEFAULT 'actif',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);

        // Seed standard equipment 
        const [equipments] = await dbPool.query("SELECT * FROM equipments");
        if (equipments.length === 0) {
             await dbPool.query(`INSERT INTO equipments (name, location) VALUES ('CTA Galerie Gauche', 'Galerie Gauche')`);
             await dbPool.query(`INSERT INTO equipments (name, location, status) VALUES ('CTA Galerie Droite', 'Galerie Droite', 'inactif')`);
             await dbPool.query(`INSERT INTO equipments (name, location) VALUES ('CTA Salle Polyvalente', 'Salle Polyvalente')`);
             await dbPool.query(`INSERT INTO equipments (name, location) VALUES ('CTA Hall Réception', 'Hall Réception')`);
        }

        // 3. CTA Table (ported from api/cta.php)
        await dbPool.query(`
            CREATE TABLE IF NOT EXISTS cta (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(100),
                zone VARCHAR(100),
                mode VARCHAR(50),
                etat VARCHAR(50),
                temperature FLOAT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);

        // 4. Telemetry Table (Expanded with equipment_id)
        await dbPool.query(`
            CREATE TABLE IF NOT EXISTS telemetry (
                id INT AUTO_INCREMENT PRIMARY KEY,
                equipment_id INT NULL,
                temperature FLOAT,
                humidity FLOAT,
                current FLOAT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (equipment_id) REFERENCES equipments(id) ON DELETE CASCADE
            )
        `);

        // Migration step: Add equipment_id to legacy telemetry tables from v1
        try {
            const [cols] = await dbPool.query("SHOW COLUMNS FROM telemetry LIKE 'equipment_id'");
            if (cols.length === 0) {
                 await dbPool.query("ALTER TABLE telemetry ADD COLUMN equipment_id INT NULL AFTER id");
                 console.log("Migrated telemetry table with equipment_id.");
            }
        } catch(e) {}

        // 5. Commands Log Table
        await dbPool.query(`
            CREATE TABLE IF NOT EXISTS commands_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                equipment_id INT NULL,
                command_type VARCHAR(50),
                new_state BOOLEAN,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (equipment_id) REFERENCES equipments(id) ON DELETE CASCADE
            )
        `);

        console.log('Database schema fully initialized (Users, Equipments, CTA, Telemetry, Logs).');
    } catch (error) {
        console.error('Error initializing database:', error);
    }
}

// MQTT Client Setup
const MQTT_BROKER = process.env.MQTT_BROKER || 'mqtt://broker.hivemq.com:1883';
const MQTT_TOPIC_TELEMETRY = 'cta/telemetry';
const MQTT_TOPIC_COMMANDS = 'cta/commands';

const mqttClient = mqtt.connect(MQTT_BROKER, {
    clientId: 'cta-backend-' + Math.random().toString(16).substr(2, 8),
    username: process.env.MQTT_USER, 
    password: process.env.MQTT_PASSWORD
});

mqttClient.on('connect', () => {
    console.log(`Connected to MQTT broker: ${MQTT_BROKER}`);
    mqttClient.subscribe(MQTT_TOPIC_TELEMETRY, (err) => {
        if (!err) {
            console.log(`Subscribed to topic: ${MQTT_TOPIC_TELEMETRY}`);
        }
    });
});

mqttClient.on('message', async (topic, payload) => {
    if (topic === MQTT_TOPIC_TELEMETRY) {
        try {
            const data = JSON.parse(payload.toString());
            console.log('Received telemetry:', data);
            
            if (dbPool) {
                // By default linking mock data to CTA Galerie Gauche (ID 1)
                await dbPool.query(
                    'INSERT INTO telemetry (equipment_id, temperature, humidity, current) VALUES (?, ?, ?, ?)',
                    [1, data.temperature || 0, data.humidity || 0, data.current || 0]
                );
            }
        } catch (error) {
            console.error('Failed to parse or save MQTT message:', error);
        }
    }
});

// ═══════════════════════════════════════════════════════════
// REST API Routes
// ═══════════════════════════════════════════════════════════

// Health check (replaces connect_test.php)
app.get('/api/health', async (req, res) => {
    try {
        if (!dbPool) return res.status(500).json({ status: 'error', message: 'Database not initialized' });
        await dbPool.query('SELECT 1');
        res.json({ status: 'ok', message: 'Connected to database', database: process.env.DB_NAME || 'cta' });
    } catch (error) {
        res.status(500).json({ status: 'error', message: error.message });
    }
});

// Login verification
app.post('/api/login', async (req, res) => {
    const { username, password } = req.body;
    
    if (!username || !password) {
        return res.status(400).json({ error: 'Missing credentials' });
    }

    try {
        if (!dbPool) return res.status(500).json({ error: 'Database not initialized' });
        
        const [rows] = await dbPool.query('SELECT * FROM users WHERE username = ? LIMIT 1', [username]);
        if (rows.length === 0) {
            return res.status(401).json({ error: 'Utilisateur introuvable' });
        }

        const user = rows[0];
        
        // Comparing plaintext password for ease of migration (in production: use bcrypt.compare)
        if (user.password !== password) {
            return res.status(401).json({ error: 'Mot de passe incorrect' });
        }

        // Authentication success
        res.json({
            success: true,
            user: {
                id: user.id,
                username: user.username,
                role: user.role
            }
        });

    } catch (error) {
        console.error('Login query error:', error);
        res.status(500).json({ error: 'Erreur interne du serveur' });
    }
});

// ─── CTA CRUD Routes (ported from api/cta.php) ───────────

// GET all CTAs or single CTA by id
app.get('/api/cta', async (req, res) => {
    try {
        if (!dbPool) return res.status(500).json({ error: 'Database not initialized' });
        const [rows] = await dbPool.query('SELECT * FROM cta ORDER BY id ASC');
        res.json(rows);
    } catch (error) {
        console.error('CTA query error:', error);
        res.status(500).json({ error: error.message });
    }
});

app.get('/api/cta/:id', async (req, res) => {
    try {
        if (!dbPool) return res.status(500).json({ error: 'Database not initialized' });
        const [rows] = await dbPool.query('SELECT * FROM cta WHERE id = ?', [req.params.id]);
        if (rows.length === 0) {
            return res.status(404).json({ error: 'CTA not found' });
        }
        res.json(rows[0]);
    } catch (error) {
        console.error('CTA query error:', error);
        res.status(500).json({ error: error.message });
    }
});

// POST create a new CTA
app.post('/api/cta', async (req, res) => {
    try {
        if (!dbPool) return res.status(500).json({ error: 'Database not initialized' });
        const allowedFields = ['nom', 'zone', 'mode', 'etat', 'temperature'];
        const fields = {};
        for (const field of allowedFields) {
            if (req.body[field] !== undefined && req.body[field] !== null) {
                fields[field] = req.body[field];
            }
        }
        if (Object.keys(fields).length === 0) {
            return res.status(400).json({ error: 'No fields provided' });
        }
        const columns = Object.keys(fields);
        const placeholders = columns.map(() => '?').join(', ');
        const values = columns.map(c => fields[c]);
        const [result] = await dbPool.query(
            `INSERT INTO cta (${columns.join(', ')}) VALUES (${placeholders})`,
            values
        );
        res.status(201).json({ success: true, id: result.insertId });
    } catch (error) {
        console.error('CTA create error:', error);
        res.status(500).json({ error: error.message });
    }
});

// PUT update a CTA
app.put('/api/cta/:id', async (req, res) => {
    try {
        if (!dbPool) return res.status(500).json({ error: 'Database not initialized' });
        const allowedFields = ['nom', 'zone', 'mode', 'etat', 'temperature'];
        const fields = {};
        for (const field of allowedFields) {
            if (req.body[field] !== undefined && req.body[field] !== null) {
                fields[field] = req.body[field];
            }
        }
        if (Object.keys(fields).length === 0) {
            return res.status(400).json({ error: 'No fields provided' });
        }
        const setParts = Object.keys(fields).map(k => `${k} = ?`).join(', ');
        const values = [...Object.values(fields), req.params.id];
        await dbPool.query(`UPDATE cta SET ${setParts} WHERE id = ?`, values);
        res.json({ success: true });
    } catch (error) {
        console.error('CTA update error:', error);
        res.status(500).json({ error: error.message });
    }
});

// DELETE a CTA
app.delete('/api/cta/:id', async (req, res) => {
    try {
        if (!dbPool) return res.status(500).json({ error: 'Database not initialized' });
        await dbPool.query('DELETE FROM cta WHERE id = ?', [req.params.id]);
        res.json({ success: true });
    } catch (error) {
        console.error('CTA delete error:', error);
        res.status(500).json({ error: error.message });
    }
});

// ─── Sensor Data Routes (ported from api/sensor_data.php) ──

// GET latest sensor data per equipment
app.get('/api/sensor-data', async (req, res) => {
    try {
        if (!dbPool) return res.status(500).json({ error: 'Database not initialized' });
        const [rows] = await dbPool.query(`
            SELECT t.equipment_id as capteur_id, t.temperature as valeur, t.created_at as date_mesure
            FROM telemetry t
            WHERE t.id IN (
                SELECT MAX(id) FROM telemetry GROUP BY equipment_id
            )
            ORDER BY t.equipment_id ASC
        `);
        res.json(rows);
    } catch (error) {
        console.error('Sensor data query error:', error);
        res.status(500).json({ error: error.message });
    }
});

// POST insert sensor data
app.post('/api/sensor-data', async (req, res) => {
    try {
        if (!dbPool) return res.status(500).json({ error: 'Database not initialized' });
        const capteurId = req.body.capteur_id != null ? parseInt(req.body.capteur_id) : null;
        const valeur = req.body.valeur != null ? parseFloat(req.body.valeur) : null;
        const dateMesure = req.body.date_mesure || new Date().toISOString().slice(0, 19).replace('T', ' ');

        if (capteurId === null || valeur === null) {
            return res.status(400).json({ error: 'capteur_id and valeur are required' });
        }

        const [result] = await dbPool.query(
            'INSERT INTO telemetry (equipment_id, temperature, created_at) VALUES (?, ?, ?)',
            [capteurId, valeur, dateMesure]
        );
        res.status(201).json({ success: true, insert_id: result.insertId });
    } catch (error) {
        console.error('Sensor data insert error:', error);
        res.status(500).json({ error: error.message });
    }
});

// ─── Telemetry & History Routes ──────────────────────────

// Get telemetry data
app.get('/api/telemetry', async (req, res) => {
    try {
        if (!dbPool) return res.status(500).json({ error: 'Database not initialized' });
        
        const [rows] = await dbPool.query(`
            SELECT t.*, e.name as equipment_name 
            FROM telemetry t 
            LEFT JOIN equipments e ON t.equipment_id = e.id 
            ORDER BY t.created_at DESC LIMIT 100
        `);
        
        res.json(rows);
    } catch (error) {
        console.error('Telemetry query error:', error);
        res.status(500).json({ error: 'Erreur interne du serveur' });
    }
});

// Get historical data
app.get('/api/history', async (req, res) => {
    try {
        if (!dbPool) return res.status(500).json({ error: 'Database not initialized' });
        const [rows] = await dbPool.query('SELECT * FROM telemetry ORDER BY id DESC LIMIT 50');
        res.json(rows.reverse());
    } catch (error) {
        console.error('Database query error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// ─── Commands & Equipment Routes ─────────────────────────

// Publish command route
app.post('/api/commands', async (req, res) => {
    const { command, device, state, userId, equipmentId } = req.body;
    
    // Log the command to the database securely
    if (dbPool && userId && equipmentId) {
        try {
            await dbPool.query(
                `INSERT INTO commands_log (user_id, equipment_id, command_type, new_state) VALUES (?, ?, ?, ?)`,
                [userId, equipmentId, device, state ? 1 : 0]
            );
        } catch(e) {
            console.error("Failed to log command", e);
        }
    }

    // Publish directly via MQTT
    mqttClient.publish(MQTT_TOPIC_COMMANDS, JSON.stringify({ [device]: state }), (err) => {
        if (err) {
            return res.status(500).json({ error: 'Failed to send command' });
        }
        res.json({ success: true, message: 'Command published and logged' });
    });
});

// Get all equipment status
app.get('/api/equipments', async (req, res) => {
    try {
        if (!dbPool) return res.status(500).json({ error: 'DB not initialized' });
        const [rows] = await dbPool.query('SELECT name, location, status FROM equipments');
        res.json(rows);
    } catch(err) {
        console.error('Get equipments error:', err);
        res.status(500).json({ error: 'Server error' });
    }
});

// Sync equipment status
app.put('/api/equipments/status', async (req, res) => {
    const { ctaSlug, status } = req.body;
    
    const slugMap = {
        'galerie-gauche': 'CTA Galerie Gauche',
        'galerie-droite': 'CTA Galerie Droite',
        'salle-polyvalente': 'CTA Salle Polyvalente',
        'hall-reception': 'CTA Hall Réception'
    };
    const name = slugMap[ctaSlug];
    if (!name || !status) return res.status(400).json({ error: 'Bad request' });

    try {
        if (!dbPool) return res.status(500).json({ error: 'DB not initialized' });
        const realStatus = status.toLowerCase() === 'actif' ? 'actif' : 'inactif';
        await dbPool.query('UPDATE equipments SET status = ? WHERE name = ?', [realStatus, name]);
        res.json({ success: true, message: 'Status updated' });
    } catch(err) {
        console.error('Update status error:', err);
        res.status(500).json({ error: 'Server error' });
    }
});

// Get latest telemetry per equipment (for dashboard & machine page sync)
app.get('/api/telemetry/latest', async (req, res) => {
    try {
        if (!dbPool) return res.status(500).json({ error: 'DB not initialized' });
        const [rows] = await dbPool.query(`
            SELECT e.id as equipment_id, e.name, e.location, e.status,
                   t.temperature, t.humidity, t.current, t.created_at
            FROM equipments e
            LEFT JOIN telemetry t ON t.id = (
                SELECT MAX(t2.id) FROM telemetry t2 WHERE t2.equipment_id = e.id
            )
            ORDER BY e.id ASC
        `);
        res.json(rows);
    } catch(err) {
        console.error('Latest telemetry error:', err);
        res.status(500).json({ error: 'Server error' });
    }
});

// ─── SPA Fallback: serve index.html for unmatched routes ──
// Express 5 requires named wildcard parameters
app.get('/{*path}', (req, res) => {
    // Only serve index.html for non-API, non-file requests
    if (!req.path.startsWith('/api/')) {
        res.sendFile(path.join(__dirname, '..', 'public', 'index.html'));
    }
});

// Start Express Server
app.listen(PORT, async () => {
    console.log(`CTA Node.js Backend is running on port ${PORT}`);
    console.log(`Frontend served at: http://localhost:${PORT}`);
    await initializeDatabase();
});
