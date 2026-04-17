<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=UTF-8');

function jsonResponse($data, int $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getRequestData(): array {
    $input = json_decode(file_get_contents('php://input'), true);
    if (is_array($input)) {
        return $input;
    }
    return $_POST;
}

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($method === 'GET') {
    if ($id !== null) {
        $stmt = $conn->prepare('SELECT * FROM cta WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if (!$row) {
            jsonResponse(['error' => 'CTA not found'], 404);
        }
        jsonResponse($row);
    }

    $result = $conn->query('SELECT * FROM cta ORDER BY id ASC');
    if ($result === false) {
        jsonResponse(['error' => $conn->error], 500);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    jsonResponse($data);
}

$input = getRequestData();
$allowedFields = ['nom', 'zone', 'mode', 'etat', 'temperature'];
$fields = [];
foreach ($allowedFields as $field) {
    if (array_key_exists($field, $input) && $input[$field] !== null) {
        $fields[$field] = $input[$field];
    }
}

if ($method === 'POST') {
    if (empty($fields)) {
        jsonResponse(['error' => 'No fields provided'], 400);
    }

    $columns = [];
    $values = [];
    foreach ($fields as $name => $value) {
        $columns[] = $name;
        if ($name === 'temperature') {
            $values[] = floatval($value);
        } else {
            $values[] = "'" . $conn->real_escape_string($value) . "'";
        }
    }

    $sql = 'INSERT INTO cta (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ')';
    if (!$conn->query($sql)) {
        jsonResponse(['error' => $conn->error], 500);
    }

    jsonResponse(['success' => true, 'id' => $conn->insert_id], 201);
}

if ($method === 'PUT') {
    if ($id === null) {
        jsonResponse(['error' => 'Missing CTA id'], 400);
    }
    if (empty($fields)) {
        jsonResponse(['error' => 'No fields provided'], 400);
    }

    $pairs = [];
    foreach ($fields as $name => $value) {
        if ($name === 'temperature') {
            $pairs[] = "$name = " . floatval($value);
        } else {
            $pairs[] = "$name = '" . $conn->real_escape_string($value) . "'";
        }
    }

    $sql = 'UPDATE cta SET ' . implode(', ', $pairs) . ' WHERE id = ' . intval($id);
    if (!$conn->query($sql)) {
        jsonResponse(['error' => $conn->error], 500);
    }

    jsonResponse(['success' => true]);
}

if ($method === 'DELETE') {
    if ($id === null) {
        jsonResponse(['error' => 'Missing CTA id'], 400);
    }
    $sql = 'DELETE FROM cta WHERE id = ' . intval($id);
    if (!$conn->query($sql)) {
        jsonResponse(['error' => $conn->error], 500);
    }
    jsonResponse(['success' => true]);
}

jsonResponse(['error' => 'Unsupported method'], 405);
