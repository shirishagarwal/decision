<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$user = getCurrentUser();
$pdo = getDbConnection();

// CREATE Organization
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = trim($data['name'] ?? '');
    $slug = trim($data['slug'] ?? '');
    $industry = $data['industry'] ?? null;
    $companySize = $data['company_size'] ?? null;
    $website = trim($data['website'] ?? '');
    $planType = $data['plan_type'] ?? 'team';
    
    // Validation
    if (empty($name)) {
        jsonResponse(['error' => 'Organization name is required'], 400);
    }
    
    if (empty($slug) || !preg_match('/^[a-z0-9-]+$/', $slug)) {
        jsonResponse(['error' => 'Invalid slug. Use lowercase letters, numbers, and hyphens only'], 400);
    }
    
    // Check if slug is already taken
    $stmt = $pdo->prepare("SELECT id FROM organizations WHERE slug = ? AND deleted_at IS NULL");
    $stmt->execute([$slug]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'This URL is already taken. Please choose another.'], 400);
    }
    
    try {
        $pdo->beginTransaction();
        
        // Set limits based on plan
        $limits = match($planType) {
            'team' => ['max_users' => 25, 'max_workspaces' => 5, 'max_decisions' => 500],
            'business' => ['max_users' => 100, 'max_workspaces' => 20, 'max_decisions' => 1000],
            'enterprise' => ['max_users' => 999999, 'max_workspaces' => 999, 'max_decisions' => 999999],
            default => ['max_users' => 5, 'max_workspaces' => 3, 'max_decisions' => 100]
        };
        
        // Create organization
        $stmt = $pdo->prepare("
            INSERT INTO organizations (
                name, slug, type, industry, company_size, website,
                plan_type, plan_status, trial_ends_at,
                max_users, max_workspaces, max_decisions_per_month
            ) VALUES (?, ?, 'business', ?, ?, ?, ?, 'trialing', DATE_ADD(NOW(), INTERVAL 14 DAY), ?, ?, ?)
        ");
        
        $stmt->execute([
            $name,
            $slug,
            $industry,
            $companySize,
            $website,
            $planType,
            $limits['max_users'],
            $limits['max_workspaces'],
            $limits['max_decisions']
        ]);
        
        $orgId = $pdo->lastInsertId();
        
        // Add creator as owner
        $stmt = $pdo->prepare("
            INSERT INTO organization_members (organization_id, user_id, role, status, joined_at)
            VALUES (?, ?, 'owner', 'active', NOW())
        ");
        $stmt->execute([$orgId, $user['id']]);
        
        // Create default workspace
        $stmt = $pdo->prepare("
            INSERT INTO workspaces (organization_id, name, type, is_default, created_by)
            VALUES (?, ?, 'team', TRUE, ?)
        ");
        $stmt->execute([$orgId, $name . ' Workspace', $user['id']]);
        $workspaceId = $pdo->lastInsertId();
        
        // Add creator to default workspace
        $stmt = $pdo->prepare("
            INSERT INTO workspace_members (workspace_id, user_id, role)
            VALUES (?, ?, 'admin')
        ");
        $stmt->execute([$workspaceId, $user['id']]);
        
        // Log audit event
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (organization_id, user_id, action, entity_type, entity_id, new_values)
            VALUES (?, ?, 'created_organization', 'organization', ?, ?)
        ");
        $stmt->execute([
            $orgId,
            $user['id'],
            $orgId,
            json_encode(['name' => $name, 'plan_type' => $planType])
        ]);
        
        $pdo->commit();
        
        jsonResponse([
            'success' => true,
            'organization_id' => $orgId,
            'workspace_id' => $workspaceId,
            'message' => 'Organization created successfully'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Organization creation error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to create organization'], 500);
    }
}

// GET Organization(s)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $orgId = $_GET['id'] ?? null;
    
    if ($orgId) {
        // Get specific organization
        $stmt = $pdo->prepare("
            SELECT o.*, om.role, om.status as member_status
            FROM organizations o
            INNER JOIN organization_members om ON o.id = om.organization_id
            WHERE o.id = ? AND om.user_id = ? AND o.deleted_at IS NULL
        ");
        $stmt->execute([$orgId, $user['id']]);
        $org = $stmt->fetch();
        
        if (!$org) {
            jsonResponse(['error' => 'Organization not found or access denied'], 404);
        }
        
        // Get stats
        $stmt = $pdo->prepare("SELECT * FROM v_organization_stats WHERE organization_id = ?");
        $stmt->execute([$orgId]);
        $stats = $stmt->fetch();
        
        jsonResponse([
            'success' => true,
            'organization' => $org,
            'stats' => $stats
        ]);
        
    } else {
        // Get all user's organizations
        $stmt = $pdo->prepare("
            SELECT o.*, om.role, om.status as member_status,
                   (SELECT COUNT(*) FROM organization_members WHERE organization_id = o.id AND status = 'active') as member_count
            FROM organizations o
            INNER JOIN organization_members om ON o.id = om.organization_id
            WHERE om.user_id = ? AND o.deleted_at IS NULL
            ORDER BY om.role = 'owner' DESC, o.created_at DESC
        ");
        $stmt->execute([$user['id']]);
        $orgs = $stmt->fetchAll();
        
        jsonResponse([
            'success' => true,
            'organizations' => $orgs
        ]);
    }
}

// UPDATE Organization
if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $data = json_decode(file_get_contents('php://input'), true);
    $orgId = $data['id'] ?? null;
    
    if (!$orgId) {
        jsonResponse(['error' => 'Organization ID required'], 400);
    }
    
    // Check if user is owner or admin
    $stmt = $pdo->prepare("
        SELECT role FROM organization_members 
        WHERE organization_id = ? AND user_id = ? AND status = 'active'
    ");
    $stmt->execute([$orgId, $user['id']]);
    $membership = $stmt->fetch();
    
    if (!$membership || !in_array($membership['role'], ['owner', 'admin'])) {
        jsonResponse(['error' => 'Insufficient permissions'], 403);
    }
    
    try {
        $updates = [];
        $params = [];
        
        if (isset($data['name'])) {
            $updates[] = "name = ?";
            $params[] = trim($data['name']);
        }
        
        if (isset($data['logo_url'])) {
            $updates[] = "logo_url = ?";
            $params[] = trim($data['logo_url']);
        }
        
        if (isset($data['website'])) {
            $updates[] = "website = ?";
            $params[] = trim($data['website']);
        }
        
        if (isset($data['color_primary'])) {
            $updates[] = "color_primary = ?";
            $params[] = $data['color_primary'];
        }
        
        if (isset($data['settings'])) {
            $updates[] = "settings = ?";
            $params[] = json_encode($data['settings']);
        }
        
        if (empty($updates)) {
            jsonResponse(['error' => 'No fields to update'], 400);
        }
        
        $params[] = $orgId;
        
        $sql = "UPDATE organizations SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Log audit event
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (organization_id, user_id, action, entity_type, entity_id, new_values)
            VALUES (?, ?, 'updated_organization', 'organization', ?, ?)
        ");
        $stmt->execute([$orgId, $user['id'], $orgId, json_encode($data)]);
        
        jsonResponse(['success' => true, 'message' => 'Organization updated']);
        
    } catch (Exception $e) {
        error_log("Organization update error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to update organization'], 500);
    }
}

// DELETE Organization (soft delete)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $orgId = $_GET['id'] ?? null;
    
    if (!$orgId) {
        jsonResponse(['error' => 'Organization ID required'], 400);
    }
    
    // Only owner can delete
    $stmt = $pdo->prepare("
        SELECT role FROM organization_members 
        WHERE organization_id = ? AND user_id = ? AND status = 'active'
    ");
    $stmt->execute([$orgId, $user['id']]);
    $membership = $stmt->fetch();
    
    if (!$membership || $membership['role'] !== 'owner') {
        jsonResponse(['error' => 'Only the owner can delete the organization'], 403);
    }
    
    try {
        // Soft delete
        $stmt = $pdo->prepare("UPDATE organizations SET deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$orgId]);
        
        // Log audit event
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (organization_id, user_id, action, entity_type, entity_id)
            VALUES (?, ?, 'deleted_organization', 'organization', ?)
        ");
        $stmt->execute([$orgId, $user['id'], $orgId]);
        
        jsonResponse(['success' => true, 'message' => 'Organization deleted']);
        
    } catch (Exception $e) {
        error_log("Organization deletion error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to delete organization'], 500);
    }
}