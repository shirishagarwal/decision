<?php
/**
 * DecisionVault Dependency Manager
 * Handles the logic for strategic decision mapping.
 */
class DependencyManager {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection(); // cite: 144
    }

    /**
     * Get the strategic graph for a specific decision.
     */
    public function getStrategicGraph($decisionId) {
        // Fetch direct dependencies (blockers)
        $stmt = $this->pdo->prepare("
            SELECT d.id, d.title, d.status, dep.dependency_type
            FROM decision_dependencies dep
            INNER JOIN decisions d ON dep.depends_on_id = d.id
            WHERE dep.decision_id = ?
        ");
        $stmt->execute([$decisionId]);
        $blockers = $stmt->fetchAll();

        // Fetch dependent decisions (blocked by this one)
        $stmt = $this->pdo->prepare("
            SELECT d.id, d.title, d.status, dep.dependency_type
            FROM decision_dependencies dep
            INNER JOIN decisions d ON dep.decision_id = d.id
            WHERE dep.depends_on_id = ?
        ");
        $stmt->execute([$decisionId]);
        $blocked = $stmt->fetchAll();

        return [
            'blocks_this' => $blockers,
            'blocked_by_this' => $blocked
        ];
    }
}
