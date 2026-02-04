<?php
/**
 * Workflow Engine
 * 
 * Automatically executes workflow actions when decisions are created/updated
 * Handles: Auto-invites, required fields, approvals, notifications, etc.
 */

require_once __DIR__ . '/../config.php';

class WorkflowEngine {
    private $pdo;
    private $organizationId;
    
    public function __construct($organizationId) {
        $this->pdo = getDbConnection();
        $this->organizationId = $organizationId;
    }
    
    /**
     * Find and execute applicable workflows for a decision
     */
    public function executeWorkflowsForDecision($decisionId, $triggerType = 'decision_created') {
        // Get decision details
        $decision = $this->getDecision($decisionId);
        if (!$decision) {
            return ['error' => 'Decision not found'];
        }
        
        // Find matching workflows
        $workflows = $this->findMatchingWorkflows($decision, $triggerType);
        
        $results = [];
        foreach ($workflows as $workflow) {
            $result = $this->executeWorkflow($workflow, $decision);
            $results[] = $result;
        }
        
        return $results;
    }
    
    /**
     * Get decision details
     */
    private function getDecision($decisionId) {
        $stmt = $this->pdo->prepare("
            SELECT d.*, w.organization_id
            FROM decisions d
            INNER JOIN workspaces w ON d.workspace_id = w.id
            WHERE d.id = ?
        ");
        $stmt->execute([$decisionId]);
        return $stmt->fetch();
    }
    
    /**
     * Find workflows that match this decision
     */
    private function findMatchingWorkflows($decision, $triggerType) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM workflow_templates
            WHERE organization_id = ?
            AND is_active = TRUE
            AND trigger_type = ?
        ");
        $stmt->execute([$this->organizationId, $triggerType]);
        $workflows = $stmt->fetchAll();
        
        // Filter by conditions
        $matching = [];
        foreach ($workflows as $workflow) {
            if ($this->matchesConditions($decision, $workflow)) {
                $matching[] = $workflow;
            }
        }
        
        return $matching;
    }
    
    /**
     * Check if decision matches workflow conditions
     */
    private function matchesConditions($decision, $workflow) {
        $conditions = json_decode($workflow['trigger_conditions'], true);
        if (empty($conditions)) {
            return true; // No conditions = always match
        }
        
        // Check category
        if (isset($conditions['category'])) {
            if ($decision['category'] !== $conditions['category']) {
                return false;
            }
        }
        
        // Check minimum cost
        if (isset($conditions['min_cost'])) {
            // Extract cost from decision (simplified - you'd parse expected_outcome or custom field)
            $cost = 0; // TODO: Extract from decision data
            if ($cost < $conditions['min_cost']) {
                return false;
            }
        }
        
        // Check template used
        if (isset($conditions['template_id'])) {
            if ($decision['template_id'] != $conditions['template_id']) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Execute a workflow
     */
    private function executeWorkflow($workflow, $decision) {
        // Create execution record
        $stmt = $this->pdo->prepare("
            INSERT INTO workflow_executions (workflow_id, decision_id, status, started_at)
            VALUES (?, ?, 'running', NOW())
        ");
        $stmt->execute([$workflow['id'], $decision['id']]);
        $executionId = $this->pdo->lastInsertId();
        
        // Get workflow actions
        $stmt = $this->pdo->prepare("
            SELECT * FROM workflow_actions
            WHERE workflow_id = ? AND is_active = TRUE
            ORDER BY execution_order ASC
        ");
        $stmt->execute([$workflow['id']]);
        $actions = $stmt->fetchAll();
        
        $actionResults = [];
        $allSuccess = true;
        
        // Execute each action
        foreach ($actions as $action) {
            try {
                $result = $this->executeAction($action, $decision);
                $actionResults[] = [
                    'action_id' => $action['id'],
                    'action_type' => $action['action_type'],
                    'status' => 'success',
                    'result' => $result
                ];
            } catch (Exception $e) {
                $actionResults[] = [
                    'action_id' => $action['id'],
                    'action_type' => $action['action_type'],
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
                $allSuccess = false;
            }
        }
        
        // Update execution record
        $stmt = $this->pdo->prepare("
            UPDATE workflow_executions
            SET status = ?, actions_executed = ?, completed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $allSuccess ? 'completed' : 'failed',
            json_encode($actionResults),
            $executionId
        ]);
        
        return [
            'workflow_id' => $workflow['id'],
            'workflow_name' => $workflow['name'],
            'execution_id' => $executionId,
            'status' => $allSuccess ? 'completed' : 'failed',
            'actions' => $actionResults
        ];
    }
    
    /**
     * Execute a single action
     */
    private function executeAction($action, $decision) {
        $config = json_decode($action['action_config'], true);
        
        switch ($action['action_type']) {
            case 'auto_invite_users':
                return $this->autoInviteUsers($decision['id'], $config);
                
            case 'auto_invite_roles':
                return $this->autoInviteRoles($decision['id'], $config);
                
            case 'require_field':
                return $this->requireField($decision['id'], $config);
                
            case 'require_attachment':
                return $this->requireAttachment($decision['id'], $config);
                
            case 'require_approval':
                return $this->requireApproval($decision['id'], $config);
                
            case 'set_deadline':
                return $this->setDeadline($decision['id'], $config);
                
            case 'set_review_date':
                return $this->setReviewDate($decision['id'], $config);
                
            case 'post_to_slack':
                return $this->postToSlack($decision, $config);
                
            case 'send_email':
                return $this->sendEmail($decision, $config);
                
            case 'add_tag':
                return $this->addTag($decision['id'], $config);
                
            default:
                throw new Exception("Unknown action type: {$action['action_type']}");
        }
    }
    
    /**
     * Auto-invite specific users
     */
    private function autoInviteUsers($decisionId, $config) {
        $userIds = $config['user_ids'] ?? [];
        $invited = 0;
        
        foreach ($userIds as $userId) {
            // Check if already invited
            $stmt = $this->pdo->prepare("
                SELECT id FROM decision_votes WHERE decision_id = ? AND user_id = ?
            ");
            $stmt->execute([$decisionId, $userId]);
            
            if (!$stmt->fetch()) {
                // Invite user (create vote placeholder)
                $stmt = $this->pdo->prepare("
                    INSERT IGNORE INTO decision_votes (decision_id, user_id, created_at)
                    VALUES (?, ?, NOW())
                ");
                $stmt->execute([$decisionId, $userId]);
                $invited++;
            }
        }
        
        return "Invited $invited users";
    }
    
    /**
     * Auto-invite users by role
     */
    private function autoInviteRoles($decisionId, $config) {
        $roles = $config['roles'] ?? [];
        
        // Get users with these roles in the organization
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT u.id
            FROM users u
            INNER JOIN organization_members om ON u.id = om.user_id
            WHERE om.organization_id = ?
            AND om.role IN (" . implode(',', array_fill(0, count($roles), '?')) . ")
            AND om.status = 'active'
        ");
        $stmt->execute(array_merge([$this->organizationId], $roles));
        $users = $stmt->fetchAll();
        
        $invited = 0;
        foreach ($users as $user) {
            // Invite each user
            $stmt = $this->pdo->prepare("
                INSERT IGNORE INTO decision_votes (decision_id, user_id, created_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$decisionId, $user['id']]);
            $invited++;
        }
        
        return "Invited $invited users with roles: " . implode(', ', $roles);
    }
    
    /**
     * Require a field
     */
    private function requireField($decisionId, $config) {
        $stmt = $this->pdo->prepare("
            INSERT INTO decision_required_fields 
            (decision_id, field_name, field_label, field_type, validation_rules, required_by)
            VALUES (?, ?, ?, ?, ?, 'workflow')
            ON DUPLICATE KEY UPDATE field_label = VALUES(field_label)
        ");
        
        $stmt->execute([
            $decisionId,
            $config['field_name'],
            $config['label'] ?? $config['field_name'],
            $config['type'] ?? 'text',
            json_encode($config['validation'] ?? [])
        ]);
        
        return "Required field added: {$config['field_name']}";
    }
    
    /**
     * Require attachment
     */
    private function requireAttachment($decisionId, $config) {
        $stmt = $this->pdo->prepare("
            INSERT INTO decision_required_fields 
            (decision_id, field_name, field_label, field_type, validation_rules, required_by)
            VALUES (?, ?, ?, 'file', ?, 'workflow')
            ON DUPLICATE KEY UPDATE field_label = VALUES(field_label)
        ");
        
        $validation = [
            'file_types' => $config['file_types'] ?? ['pdf', 'docx', 'xlsx']
        ];
        
        $stmt->execute([
            $decisionId,
            $config['field_name'] ?? 'attachment',
            $config['label'] ?? 'Required Attachment',
            json_encode($validation)
        ]);
        
        return "Required attachment: {$config['label']}";
    }
    
    /**
     * Require approval
     */
    private function requireApproval($decisionId, $config) {
        $stmt = $this->pdo->prepare("
            INSERT INTO decision_approvals 
            (decision_id, approver_user_id, approver_role, approval_type, required_by)
            VALUES (?, ?, ?, ?, 'workflow')
        ");
        
        $stmt->execute([
            $decisionId,
            $config['approver_user_id'] ?? null,
            $config['approver_role'] ?? null,
            $config['type'] ?? 'required'
        ]);
        
        $approver = $config['approver_role'] ?? "User #{$config['approver_user_id']}";
        return "Approval required from: $approver";
    }
    
    /**
     * Set deadline automatically
     */
    private function setDeadline($decisionId, $config) {
        $daysFromNow = $config['days_from_now'] ?? 30;
        $deadline = date('Y-m-d', strtotime("+{$daysFromNow} days"));
        
        $stmt = $this->pdo->prepare("UPDATE decisions SET deadline = ? WHERE id = ?");
        $stmt->execute([$deadline, $decisionId]);
        
        return "Deadline set to $deadline ($daysFromNow days from now)";
    }
    
    /**
     * Set review date automatically
     */
    private function setReviewDate($decisionId, $config) {
        $daysFromNow = $config['days_from_now'] ?? 90;
        $reviewDate = date('Y-m-d', strtotime("+{$daysFromNow} days"));
        
        $stmt = $this->pdo->prepare("UPDATE decisions SET review_date = ? WHERE id = ?");
        $stmt->execute([$reviewDate, $decisionId]);
        
        return "Review date set to $reviewDate ($daysFromNow days from now)";
    }
    
    /**
     * Post to Slack (basic version - will enhance with real Slack API)
     */
    private function postToSlack($decision, $config) {
        // TODO: Integrate with real Slack API
        // For now, just log it
        $channel = $config['channel'] ?? '#decisions';
        $message = str_replace('{title}', $decision['title'], $config['message'] ?? 'New decision: {title}');
        
        error_log("SLACK: Would post to $channel: $message");
        
        return "Posted to Slack: $channel";
    }
    
    /**
     * Send email notification
     */
    private function sendEmail($decision, $config) {
        $to = $config['to'] ?? '';
        $subject = str_replace('{title}', $decision['title'], $config['subject'] ?? 'Decision notification');
        $body = str_replace('{title}', $decision['title'], $config['message'] ?? 'A new decision needs your attention: {title}');
        
        // Send email
        $headers = "From: " . APP_NAME . " <noreply@" . parse_url(APP_URL, PHP_URL_HOST) . ">";
        mail($to, $subject, $body, $headers);
        
        return "Email sent to: $to";
    }
    
    /**
     * Add tag to decision
     */
    private function addTag($decisionId, $config) {
        $tag = $config['tag'] ?? 'workflow';
        
        // Get existing tags
        $stmt = $this->pdo->prepare("SELECT tags FROM decisions WHERE id = ?");
        $stmt->execute([$decisionId]);
        $decision = $stmt->fetch();
        
        $tags = $decision['tags'] ? explode(',', $decision['tags']) : [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            
            $stmt = $this->pdo->prepare("UPDATE decisions SET tags = ? WHERE id = ?");
            $stmt->execute([implode(',', $tags), $decisionId]);
        }
        
        return "Tag added: $tag";
    }
}