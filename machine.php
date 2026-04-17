<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vue Machine CTA</title>
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --bg: #ffffff;
            --surface: #f8f9fa;
            --surface2: #eff0f3;
            --accent: #2563eb;
            --text: #1a1a1a;
            --text-muted: #6b7280;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --radius: 8px;
            --font: 'Segoe UI', system-ui, sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
        }

        .main-machine {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: var(--accent);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb a:hover {
            color: var(--text);
        }

        .breadcrumb-separator {
            color: var(--text-muted);
        }

        .machine-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .machine-title::before {
            content: '⚙️';
            font-size: 1.75rem;
        }

        /* Edit mode UI removed */

        .title-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .machine-container {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 9;
            background-image: url('cta.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            background-color: transparent !important;
            border-radius: var(--radius);
            border: 1px solid #e5e7eb;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* edit-mode class removed */

        .component {
            position: absolute;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border: 2px solid var(--accent);
            border-radius: var(--radius);
            padding: 0.9rem 1.1rem;
            font-family: var(--font);
            font-size: 0.85rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
            min-width: 130px;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
            text-align: left;
            outline: none;
            appearance: none;
            -webkit-appearance: none;
        }

        .component:hover {
            transform: translateY(-4px) scale(1.05);
            border-color: var(--accent);
            box-shadow: 0 12px 32px rgba(37, 99, 235, 0.3);
            background: rgba(237, 244, 255, 1);
        }

        .component:active {
            transform: translateY(-1px) scale(0.98);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        .component:focus-visible {
            outline: 3px solid var(--accent);
            outline-offset: 2px;
        }

        .component.selected {
            border: 2px solid var(--success);
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.5);
        }

        .label {
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .value {
            font-size: 1.3rem;
            color: var(--accent);
            font-weight: 700;
        }

        .component.warning .value { color: var(--warning); }
        .component.danger .value { color: var(--danger); }
        .component.success .value { color: var(--success); }

        /* État toggle */
        .component.etat-inactive {
            border-color: var(--danger);
            background: rgba(255, 240, 240, 0.97);
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3);
            transition: all 0.4s ease;
        }
        .component.etat-inactive .value {
            color: var(--danger) !important;
        }
        .component.etat-active {
            border-color: var(--success);
            background: rgba(236, 253, 245, 0.97);
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
            transition: all 0.4s ease;
        }
        .component.etat-active .value {
            color: var(--success) !important;
        }

        @keyframes etatFadeOut {
            from { opacity: 1; transform: translateY(0) scale(1); }
            to   { opacity: 0; transform: translateY(-6px) scale(0.85); }
        }
        @keyframes etatFadeIn {
            from { opacity: 0; transform: translateY(6px) scale(0.85); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .etat-fade-out {
            animation: etatFadeOut 0.25s ease forwards;
        }
        .etat-fade-in {
            animation: etatFadeIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        /* Mode toggle */
        .component.mode-chauffage {
            border-color: #f59e0b;
            background: rgba(255, 251, 235, 0.97);
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.3);
            transition: all 0.4s ease;
        }
        .component.mode-chauffage .value { color: #f59e0b !important; }

        .component.mode-climatisation {
            border-color: #2563eb;
            background: rgba(239, 246, 255, 0.97);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.3);
            transition: all 0.4s ease;
        }
        .component.mode-climatisation .value { color: #2563eb !important; }

        .component.mode-ventilation {
            border-color: #8b5cf6;
            background: rgba(245, 243, 255, 0.97);
            box-shadow: 0 8px 24px rgba(139, 92, 246, 0.3);
            transition: all 0.4s ease;
        }
        .component.mode-ventilation .value { color: #8b5cf6 !important; }

        /* Slider components */
        .slider-component {
            cursor: default;
            min-width: 160px;
        }

        .slider {
            width: 100%;
            margin-top: 0.6rem;
            -webkit-appearance: none;
            appearance: none;
            height: 5px;
            border-radius: 3px;
            background: #e5e7eb;
            outline: none;
            cursor: pointer;
        }

        .slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--accent);
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.4);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .slider::-webkit-slider-thumb:hover {
            transform: scale(1.25);
            box-shadow: 0 3px 10px rgba(37, 99, 235, 0.5);
        }

        .slider::-moz-range-thumb {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--accent);
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.4);
        }

        #mode { top: 5%; left: 5%; }
        #etat { top: 5%; right: 5%; }
        #consigne { top: 18%; left: 40%; transform: translateX(-40%); }
        #temp-ambiante { top: 18%; right: 8%; }
        #extracteur { top: 46%; left: 5%; }
        #temp-reprise { bottom: 22%; left: 5%; }
        #vanne { bottom: 22%; right: 5%; }
        #humidite { bottom: 5%; left: 50%; transform: translateX(-50%); }

        /* edit-panel styles removed */

        .info-section {
            margin-top: 1.5rem;
            padding: 1rem;
            background: var(--surface);
            border-left: 3px solid var(--accent);
            border-radius: var(--radius);
            border: 1px solid #e5e7eb;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .info-section strong {
            color: var(--text);
            display: block;
            margin-bottom: 0.3rem;
        }

        /* Schéma du flux d'air retiré — styles supprimés */

        .cta-gallery {
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            background: var(--surface);
            border: 1px solid #e5e7eb;
            border-radius: var(--radius);
        }

        .gallery-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
        }

        .cta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .cta-card {
            padding: 1rem;
            background: rgba(243, 244, 246, 0.8);
            border: 1px solid rgba(37, 99, 235, 0.2);
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
        }

        .cta-card:hover {
            background: rgba(229, 231, 235, 0.8);
            border-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        .cta-card.active {
            background: rgba(37, 99, 235, 0.1);
            border-color: var(--accent);
            box-shadow: 0 0 16px rgba(37, 99, 235, 0.2);
        }

        .cta-card-title {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--accent);
            margin-bottom: 0.75rem;
        }

        .cta-card-status {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            font-size: 0.8rem;
        }

        .status-item {
            display: flex;
            justify-content: space-between;
            padding: 0.4rem;
            background: rgba(0, 0, 0, 0.05);
            border-radius: 4px;
        }

        .status-label {
            color: var(--text-muted);
        }

        .status-value {
            font-weight: 600;
            color: var(--accent);
        }

        .cta-card.warning .status-value {
            color: var(--warning);
        }

        .cta-card.danger .status-value {
            color: var(--danger);
        }

        .cta-card.success .status-value {
            color: var(--success);
        }

        /* Logo Royal Garden Palace */
        .rg-logo {
            height: 52px;
            width: auto;
            border-radius: 6px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.18);
            object-fit: contain;
            background-color: transparent;
        }

        /* styles rg-watermark retirés */
    </style>
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
</head>
<body>
    <header class="header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <img src="rg.jpg" alt="Royal Garden Palace" class="rg-logo">
                <div>
                    <h1>Vue Machine</h1>
                    <p class="subtitle">Supervision détaillée du système CTA</p>
                </div>
            </div>
            <a href="index.php" style="padding: 0.5rem 1rem; background: rgba(59, 130, 246, 0.2); color: var(--accent); text-decoration: none; border-radius: var(--radius); font-weight: 500; border: 1px solid var(--accent); transition: all 0.3s;" onmouseover="this.style.background='rgba(59, 130, 246, 0.3)'" onmouseout="this.style.background='rgba(59, 130, 246, 0.2)'">← Retour</a>
        </div>
    </header>

    <main class="main-machine">
        <div class="breadcrumb">
            <a href="index.php">Tableaux de bord</a>
            <span class="breadcrumb-separator">›</span>
            <span>Vue Machine</span>
        </div>

        <div class="title-row">
            <div class="machine-title">Vue détaillée - Machine CTA</div>
        </div>

        <!-- Galerie des CTAs disponibles -->
        <div class="cta-gallery">
            <div class="gallery-title">🏨 Sélectionnez une Machine CTA</div>
            <div class="cta-grid">
                <div class="cta-card active" id="cta-galerie-gauche" onclick="selectCTA(this, 'galerie-gauche')">
                    <div class="cta-card-title">CTA Galerie Gauche</div>
                    <div class="cta-card-status">
                        <div class="status-item">
                            <span class="status-label">Mode</span>
                            <span class="status-value" data-cta="galerie-gauche-mode">Chauffage</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">État</span>
                            <span class="status-value" data-cta="galerie-gauche-etat">Actif</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">T° Ambiance</span>
                            <span class="status-value" data-cta="galerie-gauche-temp">21.5°C</span>
                        </div>
                    </div>
                </div>

                <div class="cta-card" id="cta-galerie-droite" onclick="selectCTA(this, 'galerie-droite')">
                    <div class="cta-card-title">CTA Galerie Droite</div>
                    <div class="cta-card-status">
                        <div class="status-item">
                            <span class="status-label">Mode</span>
                            <span class="status-value" data-cta="galerie-droite-mode">Climatisation</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">État</span>
                            <span class="status-value" data-cta="galerie-droite-etat">Inactif</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">T° Ambiance</span>
                            <span class="status-value" data-cta="galerie-droite-temp">22.0°C</span>
                        </div>
                    </div>
                </div>

                <div class="cta-card" id="cta-salle-polyvalente" onclick="selectCTA(this, 'salle-polyvalente')">
                    <div class="cta-card-title">CTA Salle Polyvalente</div>
                    <div class="cta-card-status">
                        <div class="status-item">
                            <span class="status-label">Mode</span>
                            <span class="status-value" data-cta="salle-polyvalente-mode">Chauffage</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">État</span>
                            <span class="status-value" data-cta="salle-polyvalente-etat">Actif</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">T° Ambiance</span>
                            <span class="status-value" data-cta="salle-polyvalente-temp">20.8°C</span>
                        </div>
                    </div>
                </div>

                <div class="cta-card" id="cta-hall-reception" onclick="selectCTA(this, 'hall-reception')">
                    <div class="cta-card-title">CTA Hall Réception</div>
                    <div class="cta-card-status">
                        <div class="status-item">
                            <span class="status-label">Mode</span>
                            <span class="status-value" data-cta="hall-reception-mode">Ventilation</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">État</span>
                            <span class="status-value" data-cta="hall-reception-etat">Actif</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">T° Ambiance</span>
                            <span class="status-value" data-cta="hall-reception-temp">21.2°C</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="machine-container" id="machine-container">
            <button class="component" id="mode" onclick="onComponentClick('mode')" title="Cliquer pour voir le détail du mode">
                <div class="label">Mode</div>
                <div class="value" id="mode-value">Chauffage</div>
            </button>

            <button class="component" id="etat" onclick="onComponentClick('etat')" title="Cliquer pour voir l'état">
                <div class="label">État</div>
                <div class="value" id="etat-value">Actif</div>
            </button>

            <button class="component" id="consigne" onclick="onComponentClick('consigne')" title="Cliquer pour modifier la consigne">
                <div class="label">Consigne</div>
                <div class="value" id="consigne-value">22°C</div>
            </button>

            <button class="component" id="temp-ambiante" onclick="onComponentClick('temp-ambiante')" title="Cliquer pour voir la température ambiante">
                <div class="label">T° ambiante</div>
                <div class="value" id="temp-ambiante-value">21.5°C</div>
            </button>

            <button class="component" id="temp-reprise" onclick="onComponentClick('temp-reprise')" title="Cliquer pour voir la température de reprise">
                <div class="label">T° reprise</div>
                <div class="value" id="temp-reprise-value">45.2°C</div>
            </button>

            <button class="component" id="extracteur" onclick="toggleExtracteur()" title="Activer ou désactiver l'extracteur">
                <div class="label">Extracteur</div>
                <div class="value" id="extracteur-value">OFF</div>
            </button>

            <div class="component slider-component" id="vanne">
                <div class="label">Vanne ouverture</div>
                <div class="value" id="vanne-value">85%</div>
                <input type="range" min="0" max="100" value="85" class="slider" id="vanne-slider"
                    oninput="onSliderChange('vanne', this.value)"
                    onclick="event.stopPropagation()">
            </div>

          
        </div>

        <!-- Schéma du flux d'air retiré -->
        
        <div class="bottom-bar-grid" style="margin-top: 1.5rem; display: grid; gap: 1rem; grid-template-columns: 1fr;">
            <div class="commands-panel" style="padding: 1.5rem; background: var(--surface); border-radius: var(--radius); border: 1px solid #e5e7eb;">
                <h3 style="margin-bottom: 1rem;">Commandes Manuelles</h3>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn-cmd" id="btn-chauffage" data-topic="cta/chauffage" style="flex: 1; padding: 1rem; border-radius: 8px; border: 1px solid #d1d5db; background: white; cursor: pointer;">Chauffage OFF</button>
                    <button class="btn-cmd" id="btn-ventilateur" data-topic="cta/ventilateur" style="flex: 1; padding: 1rem; border-radius: 8px; border: 1px solid #d1d5db; background: white; cursor: pointer;">Ventilateur OFF</button>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Gestion du clic sur un composant
        function onComponentClick(id) {
            if (id === 'etat') { toggleEtat(); return; }
            if (id === 'mode') { toggleMode(); return; }
            if (id === 'consigne') { changeConsigne(); return; }
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.add('selected');
            setTimeout(() => el.classList.remove('selected'), 600);
        }

        let vanneManuallyChanged = false;
        let humiditeManuallyChanged = false;
        let consigneManuallyChanged = false;
        let extracteurManuallyToggled = false;

        function onSliderChange(field, val) {
            const valueEl = document.getElementById(`${field}-value`);
            if (!valueEl) return;
            valueEl.textContent = val + '%';
            if (field === 'vanne') vanneManuallyChanged = true;
            if (field === 'humidite') humiditeManuallyChanged = true;

            // Couleur dynamique selon valeur
            const slider = document.getElementById(`${field}-slider`);
            const pct = parseInt(val);
            const color = pct < 30 ? '#10b981' : pct < 70 ? '#2563eb' : '#f59e0b';
            if (slider) slider.style.background = `linear-gradient(to right, ${color} ${pct}%, #e5e7eb ${pct}%)`;
            valueEl.style.color = color;
        }

        function changeConsigne() {
            const valueEl = document.getElementById('consigne-value');
            if (!valueEl) return;
            const currentText = valueEl.textContent.trim();
            const currentNumber = parseFloat(currentText.replace('°C', '').trim());
            const newValue = window.prompt('Nouvelle consigne (°C)', currentNumber || 22);
            if (newValue === null) return;
            const parsed = parseFloat(newValue.replace(',', '.'));
            if (Number.isNaN(parsed)) {
                alert('Veuillez saisir une valeur numérique valide.');
                return;
            }
            const clamped = Math.max(5, Math.min(30, parsed));
            const formatted = clamped.toFixed(1) + '°C';
            valueEl.textContent = formatted;
            consigneManuallyChanged = true;
            const el = document.getElementById('consigne');
            if (el) {
                el.classList.add('selected');
                setTimeout(() => el.classList.remove('selected'), 600);
            }
        }

        // Cycle Chauffage → Climatisation → Ventilation → Chauffage…
        const MODES = ['Chauffage', 'Climatisation', 'Ventilation'];
        const MODE_CLASS = {
            'Chauffage':     'mode-chauffage',
            'Climatisation': 'mode-climatisation',
            'Ventilation':   'mode-ventilation'
        };
        const MODE_CARD_COLOR = {
            'Chauffage':     '#f59e0b',
            'Climatisation': '#2563eb',
            'Ventilation':   '#8b5cf6'
        };
        let modeManuallyToggled = false;

        function toggleMode() {
            const el = document.getElementById('mode');
            const valueEl = document.getElementById('mode-value');
            const current = valueEl.textContent.trim();
            const idx = MODES.indexOf(current);
            const newMode = MODES[(idx + 1) % MODES.length];

            modeManuallyToggled = true;

            valueEl.classList.add('etat-fade-out');
            setTimeout(() => {
                valueEl.textContent = newMode;
                valueEl.classList.remove('etat-fade-out');
                applyModeStyle(newMode);
                valueEl.classList.add('etat-fade-in');
                setTimeout(() => valueEl.classList.remove('etat-fade-in'), 400);

                // Sync carte CTA
                const cardModeSpan = document.querySelector(`[data-cta="${currentCTA}-mode"]`);
                if (cardModeSpan) {
                    cardModeSpan.textContent = newMode;
                    cardModeSpan.style.color = MODE_CARD_COLOR[newMode];
                    cardModeSpan.style.transition = 'color 0.4s ease';
                }
            }, 250);
        }

        function applyModeStyle(mode) {
            const el = document.getElementById('mode');
            // Retirer toutes les classes de mode
            Object.values(MODE_CLASS).forEach(cls => el.classList.remove(cls));
            el.classList.add(MODE_CLASS[mode]);

            // Sync couleur carte
            const cardModeSpan = document.querySelector(`[data-cta="${currentCTA}-mode"]`);
            if (cardModeSpan) {
                cardModeSpan.style.color = MODE_CARD_COLOR[mode];
                cardModeSpan.style.transition = 'color 0.4s ease';
            }
        }

        // Toggle Actif / Inactif avec animation
        function toggleEtat() {
            const el = document.getElementById('etat');
            const valueEl = document.getElementById('etat-value');
            const isActif = valueEl.textContent.trim() === 'Actif';
            const newValue = isActif ? 'Inactif' : 'Actif';

            etatManuallyToggled = true; // verrouille le refresh auto

            valueEl.classList.add('etat-fade-out');

            setTimeout(() => {
                valueEl.textContent = newValue;
                valueEl.classList.remove('etat-fade-out');
                applyEtatStyle(newValue);
                valueEl.classList.add('etat-fade-in');
                setTimeout(() => valueEl.classList.remove('etat-fade-in'), 400);

                // Sync la carte CTA correspondante
                const cardEtatSpan = document.querySelector(`[data-cta="${currentCTA}-etat"]`);
                if (cardEtatSpan) {
                    cardEtatSpan.textContent = newValue;
                    cardEtatSpan.style.color = newValue === 'Actif' ? 'var(--success)' : 'var(--danger)';
                    cardEtatSpan.style.transition = 'color 0.4s ease';
                }

                // ⚡ NEW: Sync status directly to the MySQL database via Node.js
                console.log("[DEBUG] Sending PUT request via fetch to update DB:", { ctaSlug: currentCTA, status: newValue.toLowerCase() });
                
                fetch('http://localhost:3001/api/equipments/status', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ ctaSlug: currentCTA, status: newValue.toLowerCase() })
                })
                .then(res => res.json())
                .then(data => console.log("[DEBUG] API Response from Node.js:", data))
                .catch(e => console.error('[ERROR] Failed to sync equipment status to DB:', e));

            }, 250);
        }

        // État de l'édition — REMOVED
        // let editMode = false;
        // let selectedElement = null;
        let currentCTA = 'galerie-gauche';
        let etatManuallyToggled = false; // empêche le refresh auto d'écraser l'état manuel
        const positions = {};

        // Jeu de données démo par CTA (au lieu de valeurs identiques)
        const CTA_DATA = {
            'galerie-gauche': {
                mode: 'Chauffage',
                etat: 'Actif',
                consigne: '22°C',
                tempAmbiante: '21.5°C',
                tempReprise: '19.8°C',
                tempSoufflage: '23.0°C',
                tempNeuf: '6.0°C',
                vanne: '65%',
                humidite: '40%',
                extracteur: 'OFF'
            },
            'galerie-droite': {
                mode: 'Climatisation',
                etat: 'Inactif',
                consigne: '20°C',
                tempAmbiante: '24.0°C',
                tempReprise: '23.0°C',
                tempSoufflage: '18.5°C',
                tempNeuf: '10.0°C',
                vanne: '30%',
                humidite: '55%',
                extracteur: 'OFF'
            },
            'salle-polyvalente': {
                mode: 'Chauffage',
                etat: 'Actif',
                consigne: '21°C',
                tempAmbiante: '20.8°C',
                tempReprise: '20.0°C',
                tempSoufflage: '22.0°C',
                tempNeuf: '7.5°C',
                vanne: '72%',
                humidite: '48%',
                extracteur: 'ON'
            },
            'hall-reception': {
                mode: 'Ventilation',
                etat: 'Actif',
                consigne: '21°C',
                tempAmbiante: '21.2°C',
                tempReprise: '21.0°C',
                tempSoufflage: '21.3°C',
                tempNeuf: '9.0°C',
                vanne: '10%',
                humidite: '50%',
                extracteur: 'ON'
            }
        };

        const APIClient = {
            baseUrl: 'http://localhost:3001/api/',

            async getDonneesCourant() {
                const response = await fetch(this.baseUrl + 'history?limit=1');
                if (!response.ok) {
                    throw new Error('API request failed: ' + response.status);
                }
                const history = await response.json();
                if (history && history.length > 0) {
                    const latest = history[history.length - 1]; // Node server returns oldest to newest, though maybe history returns descending? Let's just grab index 0 or length-1
                    return [
                        { capteur_id: 3, valeur: latest.temperature }, // Ambiante
                        { capteur_id: 2, valeur: latest.temperature - 1.5 }, // Fake Reprise
                    ];
                }
                return [];
            }
        };

        function mapSensorMessage(capteurId, valeur) {
            switch (parseInt(capteurId, 10)) {
                case 2:
                    return { id: 'temp-reprise-value', value: `${parseFloat(valeur).toFixed(1)}°C` };
                case 3:
                    return { id: 'temp-ambiante-value', value: `${parseFloat(valeur).toFixed(1)}°C` };
                case 4:
                    return { id: 'temp-depart-chaudiere-value', value: `${parseFloat(valeur).toFixed(1)}°C` };
                case 5:
                    return { id: 'temp-retour-chaudiere-value', value: `${parseFloat(valeur).toFixed(1)}°C` };
                case 6:
                    return { id: 'temp-depart-eau-glacee-value', value: `${parseFloat(valeur).toFixed(1)}°C` };
                case 7:
                    return { id: 'temp-retour-eau-glacee-value', value: `${parseFloat(valeur).toFixed(1)}°C` };
                case 9:
                    return { id: 'qualite-air-value', value: `${parseInt(valeur, 10)} ppm` };
                default:
                    return null;
            }
        }

        function applySensorPayload(capteurId, valeur) {
            const entry = mapSensorMessage(capteurId, valeur);
            if (!entry) {
                return;
            }
            const element = document.getElementById(entry.id);
            if (element && element.textContent !== entry.value) {
                element.textContent = entry.value;
                element.parentElement.style.animation = 'valuePulse 0.5s ease';
                setTimeout(() => { element.parentElement.style.animation = ''; }, 500);
            }
        }

        function createMqttClient() {
            if (typeof mqtt === 'undefined') {
                console.warn('mqtt.js library is not loaded. Real-time MQTT updates are unavailable.');
                return null;
            }

            const brokerUrl = 'wss://broker.hivemq.com:8884/mqtt';
            const topicFilter = 'ctaaaa/+/telemetry';
            const clientId = 'ctaaaa-frontend-' + Math.random().toString(16).slice(2);
            const client = mqtt.connect(brokerUrl, {
                clientId,
                clean: true,
                connectTimeout: 5000,
            });

            client.on('connect', () => {
                console.log('MQTT connected to HiveMQ broker');
                client.subscribe(topicFilter, { qos: 0 }, (err) => {
                    if (err) {
                        console.error('MQTT subscribe failed', err);
                    } else {
                        console.log('Subscribed to', topicFilter);
                    }
                });
            });

            client.on('message', (topic, payload) => {
                try {
                    const data = JSON.parse(payload.toString());
                    const capteurId = data.capteur_id || data.capteurId || data.sensorId;
                    const valeur = data.valeur ?? data.value ?? data.v;
                    if (capteurId && typeof valeur !== 'undefined') {
                        applySensorPayload(capteurId, valeur);
                    }
                } catch (err) {
                    console.warn('Invalid MQTT payload', err);
                }
            });

            client.on('error', (err) => console.error('MQTT error', err));
            return client;
        }

        const mqttClient = createMqttClient();

        // Sélectionner un CTA
        function selectCTA(card, ctaId) {
            currentCTA = ctaId;
            etatManuallyToggled = false;
            modeManuallyToggled = false;
            vanneManuallyChanged = false;
            humiditeManuallyChanged = false;
            consigneManuallyChanged = false;
            extracteurManuallyToggled = false;
            
            // Retirer active des autres cartes
            document.querySelectorAll('.cta-card').forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            
            // Mettre à jour le titre
            document.querySelector('.machine-title').textContent = card.querySelector('.cta-card-title').textContent;
            
            // Charger les données pour ce CTA
            loadPositions();
            updateValuesFromStorage();
        }

        // Charger les positions sauvegardées
        function loadPositions() {
            const saved = localStorage.getItem('component_positions_' + currentCTA);
            if (saved) {
                Object.assign(positions, JSON.parse(saved));
                applyPositions();
            }
        }

        // Appliquer les positions aux éléments
        function applyPositions() {
            ['mode', 'etat', 'consigne', 'temp-ambiante', 'temp-reprise', 'vanne', 'humidite'].forEach(id => {
                const el = document.getElementById(id);
                if (positions[id]) {
                    const pos = positions[id];
                    if (pos.top !== undefined) el.style.top = pos.top;
                    if (pos.left !== undefined) el.style.left = pos.left;
                    if (pos.right !== undefined) el.style.right = pos.right;
                    if (pos.bottom !== undefined) el.style.bottom = pos.bottom;
                }
            });
        }

        // Edit mode removed

        // Component selection and position editing removed

        // Position save/reset/copy buttons removed

        // Récupère les données et met à jour les composants
        async function updateValuesFromStorage() {
            try {
                // Try fetching from API first
                const donnees = await APIClient.getDonneesCourant();
                
                if (donnees && Array.isArray(donnees)) {
                    // Map API data to UI
                    const dataMap = {};
                    donnees.forEach(item => {
                        switch(item.capteur_id) {
                            case 2: dataMap.tempReprise = `${parseFloat(item.valeur).toFixed(1)}°C`; break;
                            case 3: dataMap.tempAmbiante = `${parseFloat(item.valeur).toFixed(1)}°C`; break;
                            case 4: dataMap.tempDepartChaudiere = `${parseFloat(item.valeur).toFixed(1)}°C`; break;
                            case 5: dataMap.tempRetourChaudiere = `${parseFloat(item.valeur).toFixed(1)}°C`; break;
                            case 6: dataMap.tempDepartEauGlacee = `${parseFloat(item.valeur).toFixed(1)}°C`; break;
                            case 7: dataMap.tempRetourEauGlacee = `${parseFloat(item.valeur).toFixed(1)}°C`; break;
                            case 9: dataMap.qualiteAir = `${parseInt(item.valeur)} ppm`; break;
                        }
                    });

                    // Update UI elements if they exist
                    if (dataMap.tempReprise && document.getElementById('temp-reprise-value')) {
                        updateElement('temp-reprise-value', dataMap.tempReprise);
                    }
                    if (dataMap.tempAmbiante && document.getElementById('temp-ambiante-value')) {
                        updateElement('temp-ambiante-value', dataMap.tempAmbiante);
                    }
                    
                    return; // Successfully fetched from API, exit
                }
            } catch (error) {
                console.warn('API unavailable, falling back to demo data:', error.message);
            }

            // Fallback to demo data from CTA_DATA
            const def = CTA_DATA[currentCTA] || CTA_DATA['galerie-gauche'];
            const data = {
                mode: def.mode,
                etat: def.etat,
                consigne: def.consigne,
                tempAmbiante: def.tempAmbiante,
                tempReprise: def.tempReprise,
                tempSoufflage: def.tempSoufflage,
                tempNeuf: def.tempNeuf,
                vanne: def.vanne,
                humidite: def.humidite,
                extracteur: def.extracteur || 'OFF'
            };

            if (!modeManuallyToggled) {
                updateElement('mode-value', data.mode);
                applyModeStyle(data.mode);
            }
            // Try fetching LIVE equipment status from DB!
            try {
                const eqResponse = await fetch('http://localhost:3001/api/equipments');
                const equipments = await eqResponse.json();
                
                const slugMap = {
                    'galerie-gauche': 'CTA Galerie Gauche',
                    'galerie-droite': 'CTA Galerie Droite',
                    'salle-polyvalente': 'CTA Salle Polyvalente',
                    'hall-reception': 'CTA Hall Réception'
                };
                const matchedEq = equipments.find(e => e.name === slugMap[currentCTA]);
                if (matchedEq && !etatManuallyToggled) {
                    const dynamicEtat = matchedEq.status === 'actif' ? 'Actif' : 'Inactif';
                    updateElement('etat-value', dynamicEtat);
                    applyEtatStyle(dynamicEtat);
                    // Update sidecard
                    const cardEtatSpan = document.querySelector(`[data-cta="${currentCTA}-etat"]`);
                    if (cardEtatSpan) {
                         cardEtatSpan.textContent = dynamicEtat;
                         cardEtatSpan.style.color = dynamicEtat === 'Actif' ? 'var(--success)' : 'var(--danger)';
                    }
                }
            } catch(e) {
                console.error("Failed fetching dynamic DB status", e);
            }

            if (!consigneManuallyChanged) {
                updateElement('consigne-value', data.consigne);
            }
            updateElement('temp-ambiante-value', data.tempAmbiante);
            updateElement('temp-reprise-value', data.tempReprise);
            if (!vanneManuallyChanged) {
                updateElement('vanne-value', data.vanne);
                const v = parseInt(data.vanne);
                const vs = document.getElementById('vanne-slider');
                if (vs) { vs.value = v; onSliderChange('vanne', v); vanneManuallyChanged = false; }
            }
            if (!humiditeManuallyChanged) {
                updateElement('humidite-value', data.humidite);
                const h = parseInt(data.humidite);
                const hs = document.getElementById('humidite-slider');
                if (hs) { hs.value = h; onSliderChange('humidite', h); humiditeManuallyChanged = false; }
            }
            if (!extracteurManuallyToggled) {
                updateElement('extracteur-value', data.extracteur);
                applyExtracteurStyle(data.extracteur);
            }
        }

        // Applique la couleur du bouton État selon la valeur
        function applyEtatStyle(value) {
            const el = document.getElementById('etat');
            if (value === 'Actif') {
                el.classList.add('etat-active');
                el.classList.remove('etat-inactive');
            } else {
                el.classList.add('etat-inactive');
                el.classList.remove('etat-active');
            }
            // Sync couleur dans la carte aussi
            const cardEtatSpan = document.querySelector(`[data-cta="${currentCTA}-etat"]`);
            if (cardEtatSpan) {
                cardEtatSpan.style.color = value === 'Actif' ? 'var(--success)' : 'var(--danger)';
                cardEtatSpan.style.transition = 'color 0.4s ease';
            }
        }

        function toggleExtracteur() {
            const valueEl = document.getElementById('extracteur-value');
            const isOn = valueEl.textContent.trim() === 'ON';
            const newValue = isOn ? 'OFF' : 'ON';

            extracteurManuallyToggled = true;
            valueEl.classList.add('etat-fade-out');
            setTimeout(() => {
                valueEl.textContent = newValue;
                valueEl.classList.remove('etat-fade-out');
                applyExtracteurStyle(newValue);
                valueEl.classList.add('etat-fade-in');
                setTimeout(() => valueEl.classList.remove('etat-fade-in'), 400);
            }, 250);
        }

        function applyExtracteurStyle(value) {
            const el = document.getElementById('extracteur');
            if (!el) return;
            if (value === 'ON') {
                el.classList.add('etat-active');
                el.classList.remove('etat-inactive');
            } else {
                el.classList.add('etat-inactive');
                el.classList.remove('etat-active');
            }
        }

        function updateElement(id, value) {
            const element = document.getElementById(id);
            if (element && element.textContent !== value) {
                element.textContent = value;
                element.parentElement.style.animation = 'none';
                setTimeout(() => {
                    element.parentElement.style.animation = 'valuePulse 0.5s ease';
                }, 10);
            }
        }

        // Animation pour les mises à jour
        const style = document.createElement('style');
        style.textContent = `
            @keyframes valuePulse {
                0% { transform: scale(1.1); color: var(--warning); }
                100% { transform: scale(1); color: var(--accent); }
            }
            @keyframes fanRotate {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);

        // Schéma et animations associés supprimés

        // Initialisation
        (function initMachinePage() {
            // Choisir le CTA initial via l'URL ?cta=...
            const params = new URLSearchParams(window.location.search);
            const fromQuery = params.get('cta');
            if (fromQuery) currentCTA = fromQuery;

            const cardId = 'cta-' + currentCTA;
            const initialCard = document.getElementById(cardId) || document.querySelector('.cta-card');
            if (initialCard) {
                selectCTA(initialCard, currentCTA);
            } else {
                loadPositions();
                updateValuesFromStorage();
            }

            // Initialiser les couleurs des sliders
            setTimeout(() => {
                const vSlider = document.getElementById('vanne-slider');
                const hSlider = document.getElementById('humidite-slider');
                if (vSlider) onSliderChange('vanne', vSlider.value);
                if (hSlider) onSliderChange('humidite', hSlider.value);
            }, 100);

            // Rafraîchissement périodique avec gestion des promesses
            setInterval(() => {
                updateValuesFromStorage().catch(err => {
                    console.error('Update error:', err);
                });
            }, 4000);
            
            // Initial update
            updateValuesFromStorage().catch(err => {
                console.error('Initial update error:', err);
            });
        })();
    </script>
    <script>
    // Auth guard pour machine.php
    (function authGuard() {
        if (localStorage.getItem('rg_logged') !== '1') {
            window.location.replace('login.php');
        }
    })();
    </script>
</body>
</html>