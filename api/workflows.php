<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/WorkflowEngine.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$user = getCurrentUser();
$pdo = getDbConnection();
$orgId = $_SESSION['current_org_id'] ?? null;

if (!$orgId) {
    jsonResponse(['error' => 'Organization ID required'], 400);
}

// CREATE WORKFLOW
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['execute'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Check permissions
    $stmt = $pdo->prepare("SELECT role FROM organization_members WHERE organization_id = ? AND user_id = ?");
    $stmt->execute([$orgId, $user['id']]);
    $member = $stmt->fetch();
    
    if (!$member || !in_array($member['role'], ['owner', 'admin'])) {
        jsonResponse(['error' => 'Admin access required'], 403);
    }
    
    try {
        $pdo->beginTransaction();
        
        // Create workflow template
        $stmt = $pdo->prepare("
            INSERT INTO workflow_templates 
            (organization_id, name, description, trigger_type, trigger_conditions, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $orgId,
            $data['name'],
            $data['description'] ?? '',
            $data['trigger_type'],
            json_encode($data['trigger_conditions'] ?? []),
            $user['id']
        ]);
        
        $workflowId = $pdo->lastInsertId();
        
        // Add actions
        if (!empty($data['actions'])) {
            $stmt = $pdo->prepare("
                INSERT INTO workflow_actions 
                (workflow_id, action_type, action_config, execution_order)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($data['actions'] as $index => $action) {
                $stmt->execute([
                    $workflowId,
                    $action['action_type'],
                    json_encode($action['config']),
                    $index
                ]);
            }
        }
        
        $pdo->commit();
        
        jsonResponse([
            'success' => true,
            'workflow_id' => $workflowId,
            'message' => 'Workflow created successfully'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Workflow creation error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to create workflow'], 500);
    }
}

// EXECUTE WORKFLOW (trigger manually or after decision creation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['execute'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    $decisionId = $data['decision_id'] ?? null;
    
    if (!$decisionId) {
        jsonResponse(['error' => 'Decision ID required'], 400);
    }
    
    try {
        $engine = new WorkflowEngine($orgId);
        $results = $engine->executeWorkflowsForDecision($decisionId);
        
        jsonResponse([
            'success' => true,
            'results' => $results
        ]);
        
    } catch (Exception $e) {
        error_log("Workflow execution error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to execute workflows'], 500);
    }
}

// GET WORKFLOWS
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $workflowId = $_GET['id'] ?? null;
    
    if ($workflowId) {
        // Get specific workflow with actions
        $stmt = $pdo->prepare("
            SELECT * FROM workflow_templates WHERE id = ? AND organization_id = ?
        ");
        $stmt->execute([$workflowId, $orgId]);
        $workflow = $stmt->fetch();
        
        if (!$workflow) {
            jsonResponse(['error' => 'Workflow not found'], 404);
        }
        
        // Get actions
        $stmt = $pdo->prepare("
            SELECT * FROM workflow_actions WHERE workflow_id = ? ORDER BY execution_order ASC
        ");
        $stmt->execute([$workflowId]);
        $workflow['actions'] = $stmt->fetchAll();
        
        jsonResponse(['success' => true, 'workflow' => $workflow]);
        
    } else {
        // Get all workflows for organization
        $stmt = $pdo->prepare("
            SELECT * FROM v_active_workflows WHERE organization_id = ? ORDER BY created_at DESC
        ");
        $stmt->execute([$orgId]);
        $workflows = $stmt->fetchAll();
        
        jsonResponse(['success' => true, 'workflows' => $workflows]);
    }
}

// UPDATE WORKFLOW
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $workflowId = $data['id'] ?? null;
    
    if (!$workflowId) {
        jsonResponse(['error' => 'Workflow ID required'], 400);
    }
    
    // Check permissions
    $stmt = $pdo->prepare("SELECT role FROM organization_members WHERE organization_id = ? AND user_id = ?");
    $stmt->execute([$orgId, $user['id']]);
    $member = $stmt->fetch();
    
    if (!$member || !in_array($member['role'], ['owner', 'admin'])) {
        jsonResponse(['error' => 'Admin access required'], 403);
    }
    
    try {
        // Update workflow
        $stmt = $pdo->prepare("
            UPDATE workflow_templates 
            SET name = ?, description = ?, trigger_conditions = ?, is_active = ?
            WHERE id = ? AND organization_id = ?
        ");
        
        $stmt->execute([
            $data['name'] ?? '',
            $data['description'] ?? '',
            json_encode($data['trigger_conditions'] ?? []),
            $data['is_active'] ?? true,
            $workflowId,
            $orgId
        ]);
        
        jsonResponse(['success' => true, 'message' => 'Workflow updated']);
        
    } catch (Exception $e) {
        error_log("Workflow update error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to update workflow'], 500);
    }
}

// DELETE WORKFLOW
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $workflowId = $_GET['id'] ?? null;
    
    if (!$workflowId) {
        jsonResponse(['error' => 'Workflow ID required'], 400);
    }
    
    // Check permissions
    $stmt = $pdo->prepare("SELECT role FROM organization_members WHERE organization_id = ? AND user_id = ?");
    $stmt->execute([$orgId, $user['id']]);
    $member = $stmt->fetch();
    
    if (!$member || !in_array($member['role'], ['owner', 'admin'])) {
        jsonResponse(['error' => 'Admin access required'], 403);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM workflow_templates WHERE id = ? AND organization_id = ?");
        $stmt->execute([$workflowId, $orgId]);
        
        jsonResponse(['success' => true, 'message' => 'Workflow deleted']);
        
    } catch (Exception $e) {
        error_log("Workflow deletion error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to delete workflow'], 500);
    }
}