<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/SimulationEngine.php';
requireLogin();

header('Content-Type: application/json');

$decisionId = $_GET['id'] ?? null;

if (!$decisionId) {
    echo json_encode(['error' => 'Decision ID required']);
    exit;
}

try {
    $engine = new SimulationEngine();
    $result = $engine->runStressTest($decisionId);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
