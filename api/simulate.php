<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/SimulationEngine.php';

header('Content-Type: application/json');
if (!isLoggedIn()) exit(json_encode(['error' => 'Auth required']));

$id = $_GET['id'] ?? null;
if (!$id) exit(json_encode(['error' => 'No ID']));

$engine = new SimulationEngine();
echo json_encode($engine->runStressTest($id));
