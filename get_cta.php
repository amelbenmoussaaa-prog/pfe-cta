<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=UTF-8');

$result = $conn->query("SELECT * FROM cta");
if ($result === false) {
    http_response_code(500);
    die(json_encode(["error" => "Erreur SQL : " . $conn->error]));
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>