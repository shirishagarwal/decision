<?php
/**
 * File Path: api/simulate.php
 * Description: API endpoint to trigger AI simulations for a specific decision.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/SimulationEngine.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    exit(json_encode(['error' => 'Authentication required']));
}

$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    exit(json_encode(['error' => 'No decision ID provided']));
}

try {
    $engine = new SimulationEngine();
    $result = $engine->runStressTest($id);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Simulation failed: ' . $e->getMessage()]);
}
