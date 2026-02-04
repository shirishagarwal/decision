<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$user = getCurrentUser();
$pdo = getDbConnection();

// Handle PUT/PATCH for updates
if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    // Extract decision ID from path like /api/decisions.php/123
    $requestUri = $_SERVER['REQUEST_URI'];
    $matches = [];
    if (preg_match('/\/decisions\.php\/(\d+)/', $requestUri, $matches)) {
        $decisionId = $matches[1];
    } else {
        jsonResponse(['error' => 'Decision ID required in URL'], 400);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Verify user has access and decision is editable
    $stmt = $pdo->prepare("
        SELECT d.*, wm.user_id 
        FROM decisions d
        INNER JOIN workspace_members wm ON d.workspace_id = wm.workspace_id
        WHERE d.id = ? AND wm.user_id = ?
    ");
    $stmt->execute([$decisionId, $user['id']]);
    $decision = $stmt->fetch();
    
    if (!$decision) {
        jsonResponse(['error' => 'Decision not found or access denied'], 403);
    }
    
    // Can only edit if not decided
    if ($decision['status'] === 'Decided' || $decision['status'] === 'Implemented') {
        jsonResponse(['error' => 'Cannot edit decided decisions'], 400);
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update decision
        $stmt = $pdo->prepare("
            UPDATE decisions 
            SET title = ?,
                description = ?,
                problem_statement = ?,
                category = ?,
                deadline = ?,
                review_date = ?,
                expected_outcome = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['title'] ?? $decision['title'],
            $data['description'] ?? $decision['description'],
            $data['problem_statement'] ?? $decision['problem_statement'],
            $data['category'] ?? $decision['category'],
            $data['deadline'] ?? null,
            $data['review_date'] ?? $decision['review_date'],
            $data['expected_outcome'] ?? $decision['expected_outcome'],
            $decisionId
        ]);
        
        // Update options if provided
        if (isset($data['options']) && is_array($data['options'])) {
            // Get existing option IDs
            $stmt = $pdo->prepare("SELECT id FROM options WHERE decision_id = ?");
            $stmt->execute([$decisionId]);
            $existingIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $processedIds = [];
            
            error_log("Updating options for decision $decisionId");
            error_log("Existing option IDs: " . json_encode($existingIds));
            error_log("Incoming options: " . json_encode($data['options']));
            
            foreach ($data['options'] as $index => $option) {
                // Skip if we've already processed this ID (prevent duplicates)
                if (!empty($option['id']) && in_array($option['id'], $processedIds)) {
                    error_log("Skipping duplicate option ID: {$option['id']}");
                    continue;
                }
                
                if (!empty($option['id']) && in_array($option['id'], $existingIds)) {
                    // Update existing option
                    $optionId = $option['id'];
                    $processedIds[] = $optionId;
                    
                    error_log("Updating existing option $optionId: {$option['name']}");
                    
                    $stmt = $pdo->prepare("
                        UPDATE options 
                        SET name = ?,
                            description = ?,
                            estimated_cost = ?,
                            estimated_effort = ?,
                            sort_order = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $option['name'],
                        $option['description'] ?? '',
                        $option['estimated_cost'] ?? null,
                        $option['estimated_effort'] ?? null,
                        $index,
                        $optionId
                    ]);
                    
                    // Delete old pros/cons
                    $pdo->prepare("DELETE FROM option_pros_cons WHERE option_id = ?")->execute([$optionId]);
                    
                } else {
                    // Insert new option
                    error_log("Inserting new option: {$option['name']}");
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO options (decision_id, name, description, estimated_cost, estimated_effort, sort_order)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $decisionId,
                        $option['name'],
                        $option['description'] ?? '',
                        $option['estimated_cost'] ?? null,
                        $option['estimated_effort'] ?? null,
                        $index
                    ]);
                    $optionId = $pdo->lastInsertId();
                    $processedIds[] = $optionId;
                }
                
                // Insert pros
                if (!empty($option['pros']) && is_array($option['pros'])) {
                    foreach ($option['pros'] as $pro) {
                        if (trim($pro)) {
                            $pdo->prepare("INSERT INTO option_pros_cons (option_id, type, content) VALUES (?, 'pro', ?)")
                                ->execute([$optionId, trim($pro)]);
                        }
                    }
                }
                
                // Insert cons
                if (!empty($option['cons']) && is_array($option['cons'])) {
                    foreach ($option['cons'] as $con) {
                        if (trim($con)) {
                            $pdo->prepare("INSERT INTO option_pros_cons (option_id, type, content) VALUES (?, 'con', ?)")
                                ->execute([$optionId, trim($con)]);
                        }
                    }
                }
            }
            
            // Delete options that were removed
            $toDelete = array_diff($existingIds, $processedIds);
            if (!empty($toDelete)) {
                error_log("Deleting removed options: " . json_encode($toDelete));
                foreach ($toDelete as $deleteId) {
                    $pdo->prepare("DELETE FROM option_pros_cons WHERE option_id = ?")->execute([$deleteId]);
                    $pdo->prepare("DELETE FROM options WHERE id = ?")->execute([$deleteId]);
                }
            }
        }
        
        $pdo->commit();
        
        jsonResponse([
            'success' => true,
            'decision_id' => $decisionId
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Decision update error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to update decision'], 500);
    }
}

// GET - List decisions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $workspaceId = $_GET['workspace_id'] ?? null;
    
    if (!$workspaceId) {
        jsonResponse(['error' => 'Workspace ID required'], 400);
    }
    
    // Verify user has access to workspace
    $stmt = $pdo->prepare("
        SELECT 1 FROM workspace_members 
        WHERE workspace_id = ? AND user_id = ?
    ");
    $stmt->execute([$workspaceId, $user['id']]);
    
    if (!$stmt->fetch()) {
        jsonResponse(['error' => 'Access denied'], 403);
    }
    
    // Get decisions with tags
    $stmt = $pdo->prepare("
        SELECT d.*, 
               u.name as creator_name,
               GROUP_CONCAT(t.name SEPARATOR ', ') as tags
        FROM decisions d
        LEFT JOIN users u ON d.created_by = u.id
        LEFT JOIN decision_tags dt ON d.id = dt.decision_id
        LEFT JOIN tags t ON dt.tag_id = t.id
        WHERE d.workspace_id = ?
        GROUP BY d.id
        ORDER BY d.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$workspaceId]);
    $decisions = $stmt->fetchAll();
    
    jsonResponse(['decisions' => $decisions]);
}

// POST - Create decision
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        jsonResponse(['error' => 'Invalid JSON'], 400);
    }
    
    $workspaceId = $data['workspace_id'] ?? null;
    $title = $data['title'] ?? null;
    $description = $data['description'] ?? '';
    $problemStatement = $data['problem_statement'] ?? '';
    $finalDecision = $data['final_decision'] ?? '';
    $rationale = $data['rationale'] ?? '';
    $category = $data['category'] ?? 'Other';
    $status = $data['status'] ?? 'Proposed';
    $confidenceLevel = $data['confidence_level'] ?? 3;
    $decidedAt = $data['decided_at'] ?? null;
    $aiGenerated = $data['ai_generated'] ?? false;
    
    if (!$workspaceId || !$title) {
        jsonResponse(['error' => 'Workspace ID and title required'], 400);
    }
    
    // Verify user has access to workspace
    $stmt = $pdo->prepare("
        SELECT 1 FROM workspace_members 
        WHERE workspace_id = ? AND user_id = ?
    ");
    $stmt->execute([$workspaceId, $user['id']]);
    
    if (!$stmt->fetch()) {
        jsonResponse(['error' => 'Access denied'], 403);
    }
    
    try {
        $pdo->beginTransaction();
        
        // Insert decision
        $stmt = $pdo->prepare("
            INSERT INTO decisions 
            (workspace_id, title, description, problem_statement, final_decision, rationale, 
             category, status, confidence_level, decided_at, created_by, ai_generated, deadline, review_date, expected_outcome)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $workspaceId, $title, $description, $problemStatement, $finalDecision, $rationale,
            $category, $status, $confidenceLevel, $decidedAt, $user['id'], $aiGenerated ? 1 : 0,
            $data['deadline'] ?? null,
            $data['review_date'] ?? null,
            $data['expected_outcome'] ?? null
        ]);
        
        $decisionId = $pdo->lastInsertId();
        
        // Insert options if provided
        if (isset($data['options']) && is_array($data['options'])) {
            foreach ($data['options'] as $index => $option) {
                $stmt = $pdo->prepare("
                    INSERT INTO options (decision_id, name, description, estimated_cost, was_chosen, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $decisionId,
                    $option['name'] ?? '',
                    $option['summary'] ?? '',
                    $option['estimated_cost'] ?? null,
                    $option['chosen'] ?? false,
                    $index
                ]);
                
                $optionId = $pdo->lastInsertId();
                
                // Insert pros
                if (isset($option['pros']) && is_array($option['pros'])) {
                    foreach ($option['pros'] as $proIndex => $pro) {
                        $stmt = $pdo->prepare("
                            INSERT INTO option_pros_cons (option_id, type, content, sort_order)
                            VALUES (?, 'pro', ?, ?)
                        ");
                        $stmt->execute([$optionId, $pro, $proIndex]);
                    }
                }
                
                // Insert cons
                if (isset($option['cons']) && is_array($option['cons'])) {
                    foreach ($option['cons'] as $conIndex => $con) {
                        $stmt = $pdo->prepare("
                            INSERT INTO option_pros_cons (option_id, type, content, sort_order)
                            VALUES (?, 'con', ?, ?)
                        ");
                        $stmt->execute([$optionId, $con, $conIndex]);
                    }
                }
            }
        }
        
        // Insert tags if provided
        if (isset($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tagName) {
                // Get or create tag
                $stmt = $pdo->prepare("
                    INSERT INTO tags (workspace_id, name) 
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE usage_count = usage_count + 1
                ");
                $stmt->execute([$workspaceId, $tagName]);
                
                $stmt = $pdo->prepare("SELECT id FROM tags WHERE workspace_id = ? AND name = ?");
                $stmt->execute([$workspaceId, $tagName]);
                $tag = $stmt->fetch();
                
                // Link tag to decision
                $stmt = $pdo->prepare("
                    INSERT INTO decision_tags (decision_id, tag_id) VALUES (?, ?)
                ");
                $stmt->execute([$decisionId, $tag['id']]);
            }
        }
        
        // Log activity
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (workspace_id, user_id, decision_id, action_type, details)
            VALUES (?, ?, ?, 'created', ?)
        ");
        $stmt->execute([
            $workspaceId,
            $user['id'],
            $decisionId,
            json_encode(['title' => $title])
        ]);
        
        $pdo->commit();
        
        jsonResponse([
            'success' => true,
            'decision_id' => $decisionId,
            'message' => 'Decision created successfully'
        ], 201);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => 'Failed to create decision: ' . $e->getMessage()], 500);
    }
}

jsonResponse(['error' => 'Method not allowed'], 405);