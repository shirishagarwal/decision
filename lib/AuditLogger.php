<?php
/**
 * DecisionVault Audit Logger
 * Records every strategic change for Enterprise compliance.
 */
class AuditLogger {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection(); // cite: lib/db.php
    }

    /**
     * Logs an action with old and new values for a complete audit trail.
     *
     */
    public function log($orgId, $userId, $action, $type, $id, $old = null, $new = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO audit_logs (
                organization_id, user_id, action, entity_type, entity_id, 
                old_values, new_values, ip_address, user_agent
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $orgId,
            $userId,
            $action,
            $type,
            $id,
            $old ? json_encode($old) : null,
            $new ? json_encode($new) : null,
            $_SERVER['REMOTE_ADDR'] ?? 'system',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
}
