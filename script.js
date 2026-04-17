// Dashboard schéma CTA dynamique – Mode démo sans serveur
// Données simulées + animations (ventilateur, flamme, flèches)

const equipmentState = { ventilateur: false, chauffage: false };
const energyHistory = [];
const HISTORY_MAX = 30;
let energieJourWh = 0;
let energieTotalKWh = 0;
let lastPowerUpdate = 0;
let chartEnergy = null;
const CONSIGNE_DEFAULT = 22;

let t_soufflage = 21.5, t_reprise = 20, t_neuf = 6, t_ext = 5;
let t_eau_aller = 45, t_eau_retour = 38;
let filtrePct = 18;
let vannePct = 0;

function initChart() {
    const ctx = document.getElementById("chart-energy").getContext("2d");
    chartEnergy = new Chart(ctx, {
        type: "line",
        data: {
            labels: [],
            datasets: [{
                label: "Puissance (W)",
                data: [],
                borderColor: "#3b82f6",
                backgroundColor: "rgba(59, 130, 246, 0.1)",
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 400 },
            scales: {
                y: { beginAtZero: true, grid: { color: "rgba(255,255,255,0.06)" }, ticks: { color: "#94a3b8" } },
                x: { grid: { color: "rgba(255,255,255,0.06)" }, ticks: { color: "#94a3b8", maxTicksLimit: 8 } }
            },
            plugins: { legend: { display: false } }
        }
    });
}

function getPuissanceW() {
    let p = 0;
    if (equipmentState.ventilateur) p += 150;
    if (equipmentState.chauffage) p += 2000;
    return p;
}

function setValueWithAnimation(id, value, isNumber) {
    const el = document.getElementById(id);
    if (!el) return;
    const str = isNumber ? (typeof value === "number" ? value.toFixed(1) : String(value)) : String(value);
    if (el.textContent !== str) {
        el.textContent = str;
        el.classList.add("updated");
        setTimeout(function () { el.classList.remove("updated"); }, 500);
    }
}

function setElValue(id, value, suffix) {
    const el = document.getElementById(id);
    if (!el) return;
    const str = (typeof value === "number" ? value.toFixed(1) : value) + (suffix || "");
    if (el.textContent !== str) {
        el.textContent = str;
        el.classList.add("updated");
        setTimeout(function () { el.classList.remove("updated"); }, 500);
    }
}

function pushEnergy(powerW) {
    const t = new Date();
    const label = t.getHours() + ":" + String(t.getMinutes()).padStart(2, "0") + ":" + String(t.getSeconds()).padStart(2, "0");
    energyHistory.push({ label, value: powerW });
    if (energyHistory.length > HISTORY_MAX) energyHistory.shift();
    if (chartEnergy) {
        chartEnergy.data.labels = energyHistory.map((e) => e.label);
        chartEnergy.data.datasets[0].data = energyHistory.map((e) => e.value);
        chartEnergy.update("active");
    }
    const now = Date.now();
    if (lastPowerUpdate > 0) {
        const dt = (now - lastPowerUpdate) / 3600000;
        energieJourWh += powerW * dt;
        energieTotalKWh += (powerW * dt) / 1000;
    }
    lastPowerUpdate = now;
    const jourEl = document.getElementById("energie_jour");
    const totalEl = document.getElementById("energie_total");
    if (jourEl) {
        jourEl.textContent = Math.round(energieJourWh);
        jourEl.classList.add("updated");
        setTimeout(function () { jourEl.classList.remove("updated"); }, 500);
    }
    if (totalEl) {
        totalEl.textContent = energieTotalKWh.toFixed(2);
        totalEl.classList.add("updated");
        setTimeout(function () { totalEl.classList.remove("updated"); }, 500);
    }
}

function updateDynamicElements() {
    const running = equipmentState.ventilateur;
    const heating = equipmentState.chauffage;

    const fanBox = document.getElementById("fan-box");
    const fanBlades = document.getElementById("fan-blades");
    if (fanBox) fanBox.classList.toggle("running", running);
    if (fanBlades) fanBlades.classList.toggle("running", running);

    const heaterBox = document.getElementById("heater-box");
    if (heaterBox) heaterBox.classList.toggle("active", heating);

    ["arrow-neuf", "arrow-reprise", "arrow-soufflage"].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.classList.toggle("flowing", running);
    });

    const modeEl = document.getElementById("mode");
    const etatEl = document.getElementById("etat");
    if (modeEl) modeEl.textContent = running ? "Marche" : "Arrêt";
    if (etatEl) etatEl.textContent = running ? "Marche" : "Arrêt";
    if (modeEl) modeEl.style.color = running ? "var(--success)" : "var(--text-muted)";
    if (etatEl) etatEl.style.color = running ? "var(--success)" : "var(--text-muted)";
}

function updateAffichage() {
    setValueWithAnimation("t_ambiante", t_ext, true);
    setValueWithAnimation("t_reprise", t_reprise, true);
    setElValue("t_reprise_schema", t_reprise, " °C");
    setElValue("t_neuf_tag", t_neuf, " °C");
    setElValue("t_soufflage_tag", t_soufflage, " °C");

    const consigneEl = document.getElementById("consigne");
    if (consigneEl) consigneEl.textContent = CONSIGNE_DEFAULT.toFixed(1) + " °C";

    const filtreEl = document.getElementById("filtre_pct");
    const vanneEl = document.getElementById("vanne_pct");
    if (filtreEl) {
        filtreEl.textContent = filtrePct.toFixed(1) + " %";
        filtreEl.classList.add("updated");
        setTimeout(function () { filtreEl.classList.remove("updated"); }, 500);
    }
    if (vanneEl) {
        vanneEl.textContent = vannePct.toFixed(1) + " %";
        vanneEl.classList.add("updated");
        setTimeout(function () { vanneEl.classList.remove("updated"); }, 500);
    }

    const p = getPuissanceW();
    const puissanceEl = document.getElementById("puissance-energy");
    const puissanceCard = document.getElementById("puissance");
    if (puissanceEl) {
        const str = p.toString();
        if (puissanceEl.textContent !== str) {
            puissanceEl.textContent = str;
            puissanceEl.classList.add("updated");
            setTimeout(function () { puissanceEl.classList.remove("updated"); }, 500);
        }
    }
    if (puissanceCard) {
        let str = p.toString();
        if (puissanceCard.textContent !== str) {
            puissanceCard.textContent = str;
            puissanceCard.classList.add("updated");
            setTimeout(function () { puissanceCard.classList.remove("updated"); }, 500);
        }
    }
    pushEnergy(p);
    updateDynamicElements();
}

function simulateTemperatures() {
    t_ext += (Math.random() - 0.5) * 0.2;
    t_ext = Math.max(0, Math.min(15, t_ext));
    t_neuf = t_ext + (Math.random() - 0.5) * 0.6;
    if (equipmentState.chauffage) {
        t_soufflage += (t_eau_aller * 0.06 - t_soufflage * 0.04);
        t_eau_retour = t_eau_aller - (t_soufflage - t_reprise) * 0.12;
    } else {
        t_soufflage += (t_neuf - t_soufflage) * 0.015;
    }
    t_reprise = t_reprise * 0.98 + t_soufflage * 0.02;
    t_soufflage = Math.max(10, Math.min(50, t_soufflage));
    t_reprise = Math.max(10, Math.min(35, t_reprise));
    t_eau_retour = Math.max(25, Math.min(45, t_eau_retour));

    filtrePct += (Math.random() - 0.5) * 0.5;
    filtrePct = Math.max(0, Math.min(100, filtrePct));
    vannePct = equipmentState.chauffage ? Math.min(100, vannePct + 2) : Math.max(0, vannePct - 3);
}

function updateButton(btnId, on) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    const label = btnId === "btn-ventilateur" ? "Ventilateur" : "Chauffage";
    btn.textContent = label + (on ? " ON" : " OFF");
    btn.classList.toggle("on", on);
}

document.querySelectorAll(".btn-cmd").forEach(function (btn) {
    btn.addEventListener("click", function () {
        const key = this.getAttribute("data-topic").split("/").pop();
        equipmentState[key] = !equipmentState[key];
        updateButton(this.id, equipmentState[key]);
        
        if (client && client.connected) {
            const cmd = {};
            cmd[key === "ventilateur" ? "fan" : "heating"] = equipmentState[key];
            client.publish('cta/commands', JSON.stringify(cmd));
        }
    });
});

initChart();
updateAffichage();

// === MQTT Real-Time Integration ===
const MQTT_BROKER = 'wss://d5125818a1ee4c1a950ddf610a8f07d6.s1.eu.hivemq.cloud:8884/mqtt';

// ⚠️ REPLACE 'VOTRE_UTILISATEUR' and 'VOTRE_MOT_DE_PASSE' with your HiveMQ Access Management credentials!
const client = typeof mqtt !== 'undefined' ? mqtt.connect(MQTT_BROKER, {
  clientId: 'cta-legacy-ui-' + Math.random().toString(16).substr(2, 8),
  username: 'adibos', // <--- Changez ceci
  password: 'Trontocasino1' // <--- Changez ceci
}) : null;

if (client) {
    client.on('connect', () => {
        console.log('Connecté au broker MQTT !');
        client.subscribe('cta/sensors');
        
        // Initialize interface to indicate it's online
        const statEl = document.getElementById("etat");
        if (statEl) statEl.textContent = "Connecté MQTT";
    });

    client.on('message', (topic, payload) => {
        if (topic === 'cta/sensors') {
            try {
                const data = JSON.parse(payload.toString());
                
                if (data.temperature !== undefined) {
                    t_ext = data.temperature;
                    // Deriving realistic values for the schematic based on the single sensor
                    t_reprise = data.temperature - 1.5;
                    t_neuf = data.temperature - 3;
                    t_soufflage = equipmentState.chauffage ? t_reprise + 15 : t_reprise;
                }
                
                // Update filtration / vanne to look dynamic
                filtrePct += (Math.random() - 0.5) * 0.5;
                filtrePct = Math.max(0, Math.min(100, filtrePct));
                vannePct = equipmentState.chauffage ? Math.min(100, vannePct + 2) : Math.max(0, vannePct - 3);

                updateAffichage();
            } catch(e) {
                console.error("Payload MQTT invalide", e);
            }
        }
    });
}

