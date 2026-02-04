<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$user = getCurrentUser();
$pdo = getDbConnection();

// SEND INVITATION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $orgId = $data['organization_id'] ?? null;
    $email = trim($data['email'] ?? '');
    $role = $data['role'] ?? 'member';
    
    if (!$orgId || !$email) {
        jsonResponse(['error' => 'Organization ID and email required'], 400);
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['error' => 'Invalid email address'], 400);
    }
    
    // Check if user can invite (owner or admin)
    $stmt = $pdo->prepare("
        SELECT role FROM organization_members 
        WHERE organization_id = ? AND user_id = ? AND status = 'active'
    ");
    $stmt->execute([$orgId, $user['id']]);
    $membership = $stmt->fetch();
    
    if (!$membership || !in_array($membership['role'], ['owner', 'admin'])) {
        jsonResponse(['error' => 'Insufficient permissions'], 403);
    }
    
    // Check if email is already a member
    $stmt = $pdo->prepare("
        SELECT om.id FROM organization_members om
        INNER JOIN users u ON om.user_id = u.id
        WHERE om.organization_id = ? AND u.email = ?
    ");
    $stmt->execute([$orgId, $email]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'This user is already a member'], 400);
    }
    
    // Check if already invited
    $stmt = $pdo->prepare("
        SELECT id FROM organization_invitations
        WHERE organization_id = ? AND email = ? AND accepted_at IS NULL AND expires_at > NOW()
    ");
    $stmt->execute([$orgId, $email]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'Already invited. Invitation pending.'], 400);
    }
    
    try {
        // Generate unique token
        $token = bin2hex(random_bytes(32));
        
        // Create invitation
        $stmt = $pdo->prepare("
            INSERT INTO organization_invitations (organization_id, email, role, token, invited_by, expires_at)
            VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))
        ");
        $stmt->execute([$orgId, $email, $role, $token, $user['id']]);
        
        // Get org details for email
        $stmt = $pdo->prepare("SELECT name FROM organizations WHERE id = ?");
        $stmt->execute([$orgId]);
        $orgName = $stmt->fetchColumn();
        
        // Send invitation email
        $inviteUrl = APP_URL . '/accept-invite.php?token=' . $token;
        $subject = "You're invited to join {$orgName} on " . APP_NAME;
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .button { display: inline-block; padding: 12px 24px; background: #4F46E5; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>{$user['name']} invited you to join {$orgName}</h2>
                <p>You've been invited to collaborate on decisions in {$orgName}.</p>
                <p>Click the button below to accept the invitation:</p>
                <p><a href='{$inviteUrl}' class='button'>Accept Invitation</a></p>
                <p>Or copy this link: {$inviteUrl}</p>
                <p>This invitation will expire in 7 days.</p>
                <hr>
                <p style='font-size: 12px; color: #666;'>If you weren't expecting this invitation, you can safely ignore this email.</p>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . APP_NAME . " <noreply@" . parse_url(APP_URL, PHP_URL_HOST) . ">\r\n";
        
        mail($email, $subject, $message, $headers);
        
        jsonResponse([
            'success' => true,
            'message' => 'Invitation sent successfully'
        ]);
        
    } catch (Exception $e) {
        error_log("Invitation error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to send invitation'], 500);
    }
}

// GET INVITATIONS for an organization
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $orgId = $_GET['organization_id'] ?? null;
    
    if (!$orgId) {
        jsonResponse(['error' => 'Organization ID required'], 400);
    }
    
    // Check access
    $stmt = $pdo->prepare("
        SELECT role FROM organization_members 
        WHERE organization_id = ? AND user_id = ? AND status = 'active'
    ");
    $stmt->execute([$orgId, $user['id']]);
    $membership = $stmt->fetch();
    
    if (!$membership) {
        jsonResponse(['error' => 'Access denied'], 403);
    }
    
    // Get invitations
    $stmt = $pdo->prepare("
        SELECT i.*, u.name as invited_by_name
        FROM organization_invitations i
        LEFT JOIN users u ON i.invited_by = u.id
        WHERE i.organization_id = ?
        ORDER BY i.created_at DESC
    ");
    $stmt->execute([$orgId]);
    $invitations = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'invitations' => $invitations
    ]);
}