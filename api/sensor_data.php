<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=UTF-8');

function jsonResponse($data, int $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "SELECT d.capteur_id, d.valeur, d.date_mesure
            FROM donnees_capteurs d
            JOIN (
                SELECT capteur_id, MAX(date_mesure) AS max_date
                FROM donnees_capteurs
                GROUP BY capteur_id
            ) latest ON latest.capteur_id = d.capteur_id AND latest.max_date = d.date_mesure
            ORDER BY d.capteur_id ASC";

    $result = $conn->query($sql);
    if ($result === false) {
        jsonResponse(['error' => $conn->error], 500);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    jsonResponse($data);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        jsonResponse(['error' => 'Invalid JSON payload'], 400);
    }

    $capteurId = isset($input['capteur_id']) ? intval($input['capteur_id']) : null;
    $valeur = isset($input['valeur']) ? floatval($input['valeur']) : null;
    $dateMesure = isset($input['date_mesure']) ? $conn->real_escape_string($input['date_mesure']) : date('Y-m-d H:i:s');

    if ($capteurId === null || $valeur === null) {
        jsonResponse(['error' => 'capteur_id and valeur are required'], 400);
    }

    $sql = sprintf(
        "INSERT INTO donnees_capteurs (capteur_id, valeur, date_mesure) VALUES (%d, %f, '%s')",
        $capteurId,
        $valeur,
        $dateMesure
    );

    if (!$conn->query($sql)) {
        jsonResponse(['error' => $conn->error], 500);
    }

    jsonResponse(['success' => true, 'insert_id' => $conn->insert_id], 201);
}

jsonResponse(['error' => 'Unsupported method'], 405);
