<?php
require_once 'config.php';

if (!isset($conn)) {
    echo 'ERROR: Connection object not found. Check config.php.';
    exit;
}

if ($conn->connect_error) {
    echo 'ERROR: ' . $conn->connect_error;
    exit;
}

echo 'SUCCESS: Connected to database ' . htmlspecialchars($db, ENT_QUOTES, 'UTF-8') . ' using mysqli.';
