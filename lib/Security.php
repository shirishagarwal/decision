<?php
/**
 * File Path: lib/Security.php
 * Description: Handles data anonymization and audit logging for enterprise compliance.
 */

class Security {
    
    /**
     * Anonymizes sensitive strings (PII) before sending to the AI.
     * Corporate users require that names/specific IDs are masked.
     */
    public static function anonymizeContext($text) {
        // Simple regex patterns for common PII
        $patterns = [
            '/\b[A-Z][a-z]+ [A-Z][a-z]+\b/' => '[PERSON_NAME]', // Simple Name mask
            '/\b\d{3}-\d{2}-\d{4}\b/' => '[SSN_HIDDEN]',
            '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/' => '[EMAIL_HIDDEN]'
        ];
        return preg_replace(array_keys($patterns), array_values($patterns), $text);
    }

    /**
     * Creates an immutable log entry for governance audits.
     */
    public static function logAction($pdo, $orgId, $userId, $action, $targetId = null) {
        $stmt = $pdo->prepare("
            INSERT INTO governance_audit_logs (organization_id, user_id, action, target_id, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $orgId,
            $userId,
            $action,
            $targetId,
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }
}
