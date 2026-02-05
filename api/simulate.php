<?php
/**
 * File Path: api/simulate.php
 * Description: API endpoint that runs the AI stress test and saves results.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/SimulationEngine.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    exit(json_encode(['error' => 'Auth required']));
}

$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    exit(json_encode(['error' => 'Decision ID missing']));
}

try {
    $engine = new SimulationEngine();
    $result = $engine->runStressTest($id);
    
    if (isset($result['error'])) {
        throw new Exception($result['error']);
    }

    // Save to Database so it persists on refresh
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        INSERT INTO decision_simulations (decision_id, day30, day90, day365, mitigation_plan) 
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE day30=VALUES(day30), day90=VALUES(day90), day365=VALUES(day365), mitigation_plan=VALUES(mitigation_plan)
    ");
    
    $stmt->execute([
        $id,
        $result['day30'],
        $result['day90'],
        $result['day365'],
        $result['mitigation'] ?? ''
    ]);

    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
