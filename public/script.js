// Connexion Socket.io pour les mises à jour en temps réel (index.html)
(function () {
    if (typeof io === 'undefined') return;
    const socket = io();

    socket.on('telemetry', function (msg) {
        if (!msg || !msg.data) return;
        const d = msg.data;

        // Mettre à jour les cartes CTA sur la page d'accueil
        const slugMap = {
            'CTA Salle Polyvalente': 'salle-polyvalente',
        };

        // Récupérer le nom CTA depuis l'API et mettre à jour les valeurs affichées
        if (d.salle != null) {
            document.querySelectorAll('[data-idx$="-temp"]').forEach(function (el) {
                el.textContent = parseFloat(d.salle).toFixed(1) + '°C';
            });
        }

        if (d.puissance != null) {
            document.querySelectorAll('[data-idx$="-puissance"]').forEach(function (el) {
                el.textContent = d.puissance + ' W';
            });
        }
    });
})();
