<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion – Supervision CTA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #c9a84c;
            --gold-light: #e8c97a;
            --gold-dark: #9b7b2e;
            --deep: #0c1118;
            --deep2: #141c28;
            --surface: #1a2438;
            --surface2: #223050;
            --text: #e8e2d4;
            --text-muted: #8a9bb5;
            --accent: #3b82f6;
            --danger: #ef4444;
            --radius: 10px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background: var(--deep);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated background */
        .bg-canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 30%, rgba(201,168,76,0.06) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 80% 70%, rgba(59,130,246,0.05) 0%, transparent 60%),
                linear-gradient(160deg, #0c1118 0%, #141c28 50%, #0d1520 100%);
        }

        .bg-grid {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-image:
                linear-gradient(rgba(201,168,76,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(201,168,76,0.04) 1px, transparent 1px);
            background-size: 48px 48px;
            mask-image: radial-gradient(ellipse 70% 70% at 50% 50%, black, transparent);
        }

        .particles {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            border-radius: 50%;
            background: var(--gold-light);
            opacity: 0;
            animation: float var(--dur, 8s) var(--delay, 0s) ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 0.6; }
            90% { opacity: 0.3; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }

        /* Login card */
        .login-wrap {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            padding: 1rem;
            animation: cardIn 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(32px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .login-card {
            background: rgba(26, 36, 56, 0.85);
            border: 1px solid rgba(201,168,76,0.18);
            border-radius: 18px;
            padding: 2.5rem 2rem;
            backdrop-filter: blur(24px);
            box-shadow:
                0 0 0 1px rgba(201,168,76,0.06),
                0 32px 64px rgba(0,0,0,0.5),
                inset 0 1px 0 rgba(201,168,76,0.1);
        }

        /* Logo */
        .logo-area {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-svg {
            width: 80px;
            height: auto;
            margin-bottom: 0.75rem;
            filter: drop-shadow(0 4px 16px rgba(201,168,76,0.25));
        }

        .brand-title {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gold-light);
            letter-spacing: 0.04em;
            line-height: 1.2;
        }

        .brand-sub {
            font-size: 0.72rem;
            color: var(--text-muted);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-top: 0.25rem;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.5rem 0 1.75rem;
            color: rgba(201,168,76,0.3);
            font-size: 0.7rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(201,168,76,0.25), transparent);
        }

        /* Form fields */
        .field {
            margin-bottom: 1.1rem;
        }

        .field label {
            display: block;
            font-size: 0.72rem;
            color: var(--text-muted);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 0.45rem;
        }

        .field-wrap {
            position: relative;
        }

        .field-icon {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.4;
            font-size: 1rem;
            pointer-events: none;
        }

        .field input,
        .field select {
            width: 100%;
            padding: 0.75rem 0.9rem 0.75rem 2.5rem;
            background: rgba(12,17,24,0.6);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            -webkit-appearance: none;
        }

        .field input::placeholder { color: rgba(138,155,181,0.5); }

        .field input:focus,
        .field select:focus {
            border-color: rgba(201,168,76,0.5);
            background: rgba(12,17,24,0.8);
            box-shadow: 0 0 0 3px rgba(201,168,76,0.08), 0 0 16px rgba(201,168,76,0.06);
        }

        .field select {
            cursor: pointer;
            color: var(--text);
        }

        .field select option {
            background: var(--surface);
            color: var(--text);
        }

        /* Select arrow custom */
        .select-wrap::after {
            content: '▾';
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gold);
            opacity: 0.6;
            pointer-events: none;
            font-size: 0.85rem;
        }

        /* Role badges */
        .role-hint {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .role-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            font-size: 0.68rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-weight: 500;
        }

        .badge-admin {
            background: rgba(201,168,76,0.12);
            border: 1px solid rgba(201,168,76,0.3);
            color: var(--gold-light);
        }

        .badge-visiteur {
            background: rgba(59,130,246,0.1);
            border: 1px solid rgba(59,130,246,0.25);
            color: #93c5fd;
        }

        /* Submit button */
        .btn-login {
            width: 100%;
            margin-top: 1.5rem;
            padding: 0.9rem;
            background: linear-gradient(135deg, var(--gold-dark), var(--gold), var(--gold-light));
            background-size: 200% 100%;
            background-position: 100% 0;
            border: none;
            border-radius: var(--radius);
            color: #1a1000;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background-position 0.4s ease, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 20px rgba(201,168,76,0.2);
            position: relative;
            overflow: hidden;
        }

        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.15) 50%, transparent 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .btn-login:hover {
            background-position: 0% 0;
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(201,168,76,0.35);
        }

        .btn-login:hover::after { opacity: 1; }
        .btn-login:active { transform: translateY(0); }

        /* Error state */
        .error-msg {
            display: none;
            padding: 0.65rem 0.9rem;
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: var(--radius);
            font-size: 0.82rem;
            color: #fca5a5;
            margin-top: 1rem;
            text-align: center;
            animation: shake 0.3s ease;
        }

        .error-msg.visible { display: block; }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }

        /* Demo credentials note */
        .demo-note {
            margin-top: 1.5rem;
            padding: 0.75rem;
            background: rgba(59,130,246,0.06);
            border: 1px dashed rgba(59,130,246,0.2);
            border-radius: var(--radius);
            font-size: 0.75rem;
            color: var(--text-muted);
            text-align: center;
            line-height: 1.6;
        }

        .demo-note strong { color: #93c5fd; }

        /* Loading state */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-login.loading::before {
            content: '';
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(26,16,0,0.3);
            border-top-color: #1a1000;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-right: 0.5rem;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.7rem;
            color: rgba(138,155,181,0.45);
            letter-spacing: 0.04em;
        }
    </style>
</head>
<body>

<div class="bg-canvas"></div>
<div class="bg-grid"></div>
<div class="particles" id="particles"></div>

<div class="login-wrap">
    <div class="login-card">

        <div class="logo-area">
            <svg class="logo-svg" viewBox="0 0 200 160" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="goldGrad" x1="0" x2="1" y1="0" y2="1">
                        <stop offset="0%" stop-color="#c9a84c"/>
                        <stop offset="50%" stop-color="#e8c97a"/>
                        <stop offset="100%" stop-color="#9b7b2e"/>
                    </linearGradient>
                </defs>
                <text x="10" y="110" font-family="Georgia, serif" font-weight="700" font-size="110" fill="url(#goldGrad)" opacity="0.95">RG</text>
            </svg>
            <div class="brand-title">Royal Garden Palace</div>
            <div class="brand-sub">Supervision CTA · Système de gestion</div>
        </div>

        <div class="divider">Connexion sécurisée</div>

        <div class="field">
            <label>Nom d'utilisateur</label>
            <div class="field-wrap">
                <span class="field-icon">👤</span>
                <input type="text" id="username" placeholder="Entrez votre identifiant" autocomplete="username">
            </div>
        </div>

        <div class="field">
            <label>Mot de passe</label>
            <div class="field-wrap">
                <span class="field-icon">🔒</span>
                <input type="password" id="password" placeholder="••••••••" autocomplete="current-password">
            </div>
        </div>

        <div class="field">
            <label>Rôle</label>
            <div class="field-wrap select-wrap">
                <span class="field-icon">🎫</span>
                <select id="role">
                    <option value="">— Sélectionner un rôle —</option>
                    <option value="admin">Administrateur</option>
                    <option value="visiteur">Visiteur</option>
                </select>
            </div>
            <div class="role-hint">
                <span class="role-badge badge-admin">Admin — Accès complet</span>
                <span class="role-badge badge-visiteur">Visiteur — Lecture seule</span>
            </div>
        </div>

        <button class="btn-login" id="btn-login" onclick="doLogin()">
            Se connecter
        </button>

        <div class="error-msg" id="error-msg">
            ⚠️ Identifiants incorrects. Veuillez réessayer.
        </div>

        <div class="demo-note">
            <strong>Démo :</strong> admin / <strong>admin123</strong> &nbsp;·&nbsp; visiteur / <strong>visit123</strong><br>
            Sélectionnez le rôle correspondant avant de vous connecter.
        </div>
    </div>

    <div class="login-footer">© Royal Garden Palace — Supervision CTA</div>
</div>

<script>
    // Particules flottantes
    (function spawnParticles() {
        const container = document.getElementById('particles');
        for (let i = 0; i < 28; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            p.style.left = Math.random() * 100 + 'vw';
            p.style.setProperty('--dur', (6 + Math.random() * 10) + 's');
            p.style.setProperty('--delay', (Math.random() * 10) + 's');
            p.style.width = p.style.height = (1 + Math.random() * 2.5) + 'px';
            p.style.opacity = (0.2 + Math.random() * 0.5).toString();
            container.appendChild(p);
        }
    })();

    async function doLogin() {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        const role = document.getElementById('role').value;
        const errorEl = document.getElementById('error-msg');
        const btn = document.getElementById('btn-login');

        // Validation basique
        if (!username || !password || !role) {
            showError('Veuillez remplir tous les champs.');
            return;
        }

        btn.classList.add('loading');
        btn.textContent = 'Connexion en cours…';
        errorEl.classList.remove('visible');

        try {
            const response = await fetch('http://localhost:3001/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                btn.classList.remove('loading');
                btn.textContent = 'Se connecter';
                showError(result.error || 'Identifiants incorrects.');
                return;
            }

            // Vérification stricte du rôle choisi vs le rôle de la DB
            if (result.user.role !== role) {
                btn.classList.remove('loading');
                btn.textContent = 'Se connecter';
                showError('Vous n\'avez pas le rôle ' + role + '.');
                return;
            }

            // Succès
            localStorage.setItem('rg_user', result.user.username);
            localStorage.setItem('rg_role', result.user.role);
            localStorage.setItem('rg_logged', '1');
            localStorage.setItem('rg_user_id', result.user.id);

            // Rediriger vers la page principale
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 500);

        } catch (error) {
            console.error("Login Server Error:", error);
            btn.classList.remove('loading');
            btn.textContent = 'Se connecter';
            showError('Serveur injoignable (Node.js). Vérifiez votre connexion.');
        }
    }

    function showError(msg) {
        const el = document.getElementById('error-msg');
        el.textContent = '⚠️ ' + msg;
        el.classList.remove('visible');
        void el.offsetWidth; // force reflow pour relancer l'animation
        el.classList.add('visible');
    }

    // Touche Entrée pour soumettre
    document.addEventListener('keydown', e => {
        if (e.key === 'Enter') doLogin();
    });

    // Si déjà connecté, rediriger directement
    if (localStorage.getItem('rg_logged') === '1') {
        window.location.href = 'index.php';
    }
</script>
</body>
</html>
