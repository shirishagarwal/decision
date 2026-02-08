<?php
/**
 * File Path: api/export-report.php
 * Description: Generates a high-fidelity Markdown/PDF-ready report of a decision.
 * Targeted at: Board members and Stakeholders.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/Security.php';
requireLogin();

$decisionId = $_GET['id'] ?? null;
$pdo = getDbConnection();
$orgId = $_SESSION['current_org_id'];

// Fetch decision and simulation data
$stmt = $pdo->prepare("
    SELECT d.*, s.day30, s.day90, s.day365, s.mitigation_plan
    FROM decisions d
    LEFT JOIN decision_simulations s ON d.id = s.decision_id
    WHERE d.id = ? AND d.organization_id = ?
");
$stmt->execute([$decisionId, $orgId]);
$data = $stmt->fetch();

if (!$data) die("Unauthorized or not found.");

// Log the export action for audit purposes
Security::logAction($pdo, $orgId, $_SESSION['user_id'], 'Generated Executive Briefing', $decisionId);

header('Content-Type: text/markdown');
header('Content-Disposition: attachment; filename="Strategic_Briefing_'.$decisionId.'.md"');

echo "# EXECUTIVE STRATEGIC BRIEFING\n";
echo "## PROJECT: " . strtoupper($data['title']) . "\n";
echo "Date: " . date('Y-m-d') . " | Status: " . $data['status'] . "\n\n";

echo "--- \n\n";

echo "### 1. RATIONALE SUMMARY\n";
echo $data['problem_statement'] . "\n\n";

echo "### 2. RISK SIMULATION (PRE-MORTEM)\n";
echo "* **30-Day Warning Signs:** " . $data['day30'] . "\n";
echo "* **Annual Autopsy:** " . $data['day365'] . "\n\n";

echo "### 3. REQUIRED MITIGATION\n";
echo "> " . $data['mitigation_plan'] . "\n\n";

echo "--- \n";
echo "Generated via DecisionVault Intelligence OS. Confidential Institutional Memory.";
