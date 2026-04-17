<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervision CTA – Schéma dynamique</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .page-layout {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            justify-content: center;
        }
        .cta-sidebar {
            width: min(100%, 860px);
            max-width: 860px;
            flex: 0 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
            position: relative;
            top: 0;
            align-self: stretch;
        }
        .cta-item {
            display: block;
            width: 100%;
            min-height: 160px;
            background: var(--surface);
            border: 1px solid #e5e7eb;
            padding: 1.2rem 1.25rem;
            border-radius: 18px;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 12px 32px rgba(16, 24, 40, 0.05);
        }
        .cta-item .title { font-weight: 600; color: var(--accent); margin-bottom: 0.5rem; }
        .cta-item .meta { display: flex; justify-content: space-between; font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.25rem; }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .status-card {
            background: var(--surface);
            border: 1px solid #e5e7eb;
            border-radius: var(--radius);
            padding: 1rem;
        }

        .status-card-title {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }

        .status-card-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--accent);
        }

        .status-card-unit {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-left: 0.25rem;
        }

        .status-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        .schema-wrapper {
            background: var(--surface);
            border: 1px solid #e5e7eb;
            border-radius: var(--radius);
            padding: 1rem;
        }

        .bottom-bar-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .commands-panel {
            background: var(--surface);
            border: 1px solid #e5e7eb;
            border-radius: var(--radius);
            padding: 1rem;
        }

        .commands-panel h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.05em;
        }

        .btn-cmd {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .btn-cmd:last-child {
            margin-bottom: 0;
        }

        .energy-panel {
            background: var(--surface);
            border: 1px solid #e5e7eb;
            border-radius: var(--radius);
            padding: 1rem;
        }

        .energy-panel h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.05em;
        }

        @media (max-width: 900px) {
            .bottom-bar-grid {
                grid-template-columns: 1fr;
            }
        }
        /* UI polish and responsive layout tweaks */
        .header {
            background: linear-gradient(90deg, rgba(245,247,250,1) 0%, rgba(239,243,246,1) 100%);
            border-bottom: 1px solid #e6eef9;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 8px rgba(16,24,40,0.04);
        }
        .header h1 { margin: 0; font-size: 1.45rem; color: var(--text); }
        .subtitle { margin: 0.25rem 0 0; font-size: 0.85rem; color: var(--text-muted); }

        .status.mode-demo { background: rgba(37,99,235,0.08); color: var(--accent); padding: 0.35rem 0.65rem; border-radius: 999px; border: 1px solid rgba(37,99,235,0.12); }
        .live-dot { display:inline-block; width:8px; height:8px; background: #10b981; border-radius:50%; margin-right:0.45rem; box-shadow:0 0 6px rgba(16,185,129,0.25); vertical-align:middle; }

        .page-layout { gap:1.25rem; }
        .content-area { flex: 1; background: linear-gradient(180deg,#fff,#f8fafc); border:1px solid #eef2f6; padding: 1.25rem; border-radius: 10px; box-shadow: 0 6px 18px rgba(16,24,40,0.03); min-height: 360px; }

        .cta-sidebar { padding: 0.5rem 0; }
        .cta-item { transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease; display:block; }
        .cta-item:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(37,99,235,0.08); border-color: rgba(37,99,235,0.28); }
        .cta-item.active { border-left: 4px solid var(--accent); box-shadow: 0 12px 30px rgba(37,99,235,0.12); background: linear-gradient(180deg,#ffffff,#fbfdff); }
        .cta-item .title { display:flex; align-items:center; gap:0.75rem; font-size: 1.05rem; }
        .cta-item .title::before { content: '🏷️'; font-size:1rem; }

        /* compact meta rows */
        .cta-item .meta { font-size:0.92rem; color:var(--text-muted); }

        /* Fancy scrollbar for sidebar */
        .cta-sidebar::-webkit-scrollbar { width: 10px; }
        .cta-sidebar::-webkit-scrollbar-thumb { background: rgba(16,24,40,0.06); border-radius: 8px; }

        .footer { text-align:center; padding: 1rem; color: var(--text-muted); background: transparent; border-top: 1px solid #f1f5f9; }

        @media (max-width: 900px) {
            .page-layout { flex-direction: column; }
            .cta-sidebar { width: 100%; grid-template-columns: 1fr; overflow-x: visible; padding: 0.5rem 0; }
            .cta-item { min-width: auto; }
            .content-area { min-height: 240px; }
        }
        /* Brand logo in header */
        #brand-logo { width: 96px; max-height: 84px; height: auto; margin-right: 0.75rem; vertical-align: middle; background: transparent; display: inline-block; opacity: 0.98; filter: drop-shadow(0 6px 18px rgba(0,0,0,0.08)); border-radius: 6px; }

        @media (max-width: 800px) {
            #brand-logo { width: 72px; max-height: 64px; margin-right: 0.5rem; }
        }
    </style>
</head>
<body>
<header class="header">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div style="display: flex; align-items: center;">
            <!-- Inline SVG logo (transparent background) -->
            <svg id="brand-logo" viewBox="0 0 320 160" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Royal Garden Logo">
                <defs>
                    <linearGradient id="g" x1="0" x2="1">
                        <stop offset="0" stop-color="#d4af37"/>
                        <stop offset="1" stop-color="#f3d86b"/>
                    </linearGradient>
                </defs>
                <rect width="100%" height="100%" fill="none"/>
                <text x="22" y="90" font-family="Georgia, 'Times New Roman', serif" font-weight="700" font-size="96" fill="url(#g)">RG</text>
                <text x="22" y="125" font-family="Segoe UI, system-ui, sans-serif" font-size="14" fill="#6b7280">Royal Garden Palace</text>
            </svg>
            <div style="display:flex; flex-direction:column;">
                <h1 style="margin:0;">Supervision CTA</h1>
                <p class="subtitle" style="margin:0.15rem 0 0;">Centrale de Traitement d'Air</p>
            </div>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div id="user-badge" style="
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.35rem 0.75rem;
                border-radius: 999px;
                font-size: 0.75rem;
                background: rgba(201,168,76,0.1);
                border: 1px solid rgba(201,168,76,0.25);
                color: #c9a84c;
                font-weight: 500;
            ">
                <span id="user-avatar" style="font-size:1rem;">👤</span>
                <span id="user-name-display">—</span>
                <span id="user-role-badge" style="
                    padding: 0.1rem 0.45rem;
                    border-radius: 999px;
                    font-size: 0.65rem;
                    letter-spacing: 0.07em;
                    text-transform: uppercase;
                    background: rgba(201,168,76,0.15);
                    border: 1px solid rgba(201,168,76,0.3);
                ">—</span>
            </div>
            <button onclick="doLogout()" style="
                padding: 0.35rem 0.8rem;
                border-radius: 999px;
                font-size: 0.75rem;
                background: rgba(239,68,68,0.1);
                border: 1px solid rgba(239,68,68,0.25);
                color: #fca5a5;
                cursor: pointer;
                font-family: inherit;
                transition: background 0.2s;
            " onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'">
                ⬡ Déconnexion
            </button>
        </div>
    </div>
</header>

    <main class="main">
        <div class="page-layout">
            <aside class="cta-sidebar" id="cta-sidebar">
                <a class="cta-item" href="machine.php?cta=galerie-gauche" data-cta="galerie-gauche">
                    <div class="title">CTA Galerie Gauche</div>
                    <div class="meta"><span>Mode</span><span data-idx="galerie-gauche-mode">Chauffage</span></div>
                    <div class="meta"><span>État</span><span data-idx="galerie-gauche-etat">Actif</span></div>
                    <div class="meta"><span>T°</span><span data-idx="galerie-gauche-temp">21.5°C</span></div>
                </a>
                <a class="cta-item" href="machine.php?cta=galerie-droite" data-cta="galerie-droite">
                    <div class="title">CTA Galerie Droite</div>
                    <div class="meta"><span>Mode</span><span data-idx="galerie-droite-mode">Climatisation</span></div>
                    <div class="meta"><span>État</span><span data-idx="galerie-droite-etat">Inactif</span></div>
                    <div class="meta"><span>T°</span><span data-idx="galerie-droite-temp">22.0°C</span></div>
                </a>
                <a class="cta-item" href="machine.php?cta=salle-polyvalente" data-cta="salle-polyvalente">
                    <div class="title">CTA Salle Polyvalente</div>
                    <div class="meta"><span>Mode</span><span data-idx="salle-polyvalente-mode">Chauffage</span></div>
                    <div class="meta"><span>État</span><span data-idx="salle-polyvalente-etat">Actif</span></div>
                    <div class="meta"><span>T°</span><span data-idx="salle-polyvalente-temp">20.8°C</span></div>
                </a>
                <a class="cta-item" href="machine.php?cta=hall-reception" data-cta="hall-reception">
                    <div class="title">CTA Hall Réception</div>
                    <div class="meta"><span>Mode</span><span data-idx="hall-reception-mode">Ventilation</span></div>
                    <div class="meta"><span>État</span><span data-idx="hall-reception-etat">Actif</span></div>
                    <div class="meta"><span>T°</span><span data-idx="hall-reception-temp">21.2°C</span></div>
                </a>
            </aside>

        </div>
    </main>

    <footer class="footer">
        © Royal Garden Palace — Supervision CTA
    </footer>

<script>
// ── Garde d'authentification ──────────────────────────────────────
(function authGuard() {
    if (localStorage.getItem('rg_logged') !== '1') {
        window.location.replace('login.php');
        return;
    }
    const username = localStorage.getItem('rg_user') || '—';
    const role     = localStorage.getItem('rg_role') || '—';
    document.addEventListener('DOMContentLoaded', function() {
        const nameEl = document.getElementById('user-name-display');
        const roleEl = document.getElementById('user-role-badge');
        const avatarEl = document.getElementById('user-avatar');
        if (nameEl) nameEl.textContent = username;
        if (roleEl) {
            roleEl.textContent = role === 'admin' ? 'Admin' : 'Visiteur';
            if (role === 'admin') {
                roleEl.style.background = 'rgba(201,168,76,0.2)';
                roleEl.style.borderColor = 'rgba(201,168,76,0.4)';
                roleEl.style.color = '#e8c97a';
            } else {
                roleEl.style.background = 'rgba(59,130,246,0.12)';
                roleEl.style.borderColor = 'rgba(59,130,246,0.3)';
                roleEl.style.color = '#93c5fd';
            }
        }
        if (avatarEl) avatarEl.textContent = role === 'admin' ? '🛡️' : '👤';
    });
})();

function doLogout() {
    localStorage.removeItem('rg_logged');
    localStorage.removeItem('rg_user');
    localStorage.removeItem('rg_role');
    window.location.replace('login.php');
}
// ─────────────────────────────────────────────────────────────────
</script>

<script>
// Remplir rapidement les statuts des CTA depuis localStorage
(function(){
    document.querySelectorAll('[data-idx]').forEach(el => {
        const parts = el.getAttribute('data-idx').split('-');
        const prop = parts.pop();
        const ctaId = parts.join('-');
        const key = 'cta_' + ctaId + '_' + prop;
        const v = localStorage.getItem(key);
        if (v) el.textContent = v;
    });

    // Mise à jour dynamique de TOUS les statuts depuis la base de données
    fetch('http://localhost:3001/api/equipments')
        .then(res => res.json())
        .then(equipments => {
            const slugMap = {
                'CTA Galerie Gauche': 'galerie-gauche',
                'CTA Galerie Droite': 'galerie-droite',
                'CTA Salle Polyvalente': 'salle-polyvalente',
                'CTA Hall Réception': 'hall-reception'
            };
            equipments.forEach(eq => {
                const slug = slugMap[eq.name];
                if (slug) {
                    const etatSpan = document.querySelector(`[data-idx="${slug}-etat"]`);
                    if (etatSpan) {
                        etatSpan.textContent = eq.status === 'actif' ? 'Actif' : 'Inactif';
                    }
                }
            });
        })
        .catch(e => console.error("Could not fetch equipments table on dashboard load", e));
})();
</script>
<script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
<script src="script.js"></script>
</body>
</html>
