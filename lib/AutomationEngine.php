<?php
/**
 * DecisionVault Automation Engine
 * Executes workflow rules based on triggers.
 */
class AutomationEngine {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    /**
     * Scans for matching workflow templates and executes actions.
     *
     */
    public function trigger($decisionId) {
        // Fetch decision details
        $stmt = $this->pdo->prepare("SELECT * FROM decisions WHERE id = ?");
        $stmt->execute([$decisionId]);
        $decision = $stmt->fetch();

        // Find active workflows for this category or organization
        $stmt = $this->pdo->prepare("
            SELECT wa.* FROM workflow_actions wa
            JOIN workflow_templates wt ON wa.workflow_id = wt.id
            WHERE wt.organization_id = (SELECT organization_id FROM workspaces WHERE id = ?)
            AND wt.is_active = 1
            AND (wt.trigger_type = 'decision_created' OR (wt.trigger_type = 'decision_category' AND wt.trigger_conditions LIKE ?))
        ");
        $stmt->execute([$decision['workspace_id'], '%' . $decision['category'] . '%']);
        $actions = $stmt->fetchAll();

        foreach ($actions as $action) {
            $config = json_decode($action['action_config'], true);
            $this->executeAction($decisionId, $action['action_type'], $config);
        }
    }

    private function executeAction($decisionId, $type, $config) {
        switch ($type) {
            case 'auto_invite_roles':
                // Auto-invite admins or stakeholders
                break;
            case 'require_field':
                // Lock the decision until 'ROI' or 'Budget' is filled
                $stmt = $this->pdo->prepare("INSERT INTO decision_required_fields (decision_id, field_name, field_label) VALUES (?, ?, ?)");
                $stmt->execute([$decisionId, $config['field_name'], $config['label']]);
                break;
            case 'set_review_date':
                $date = date('Y-m-d', strtotime("+{$config['days_from_now']} days"));
                $this->pdo->prepare("UPDATE decisions SET review_date = ? WHERE id = ?")->execute([$date, $decisionId]);
                break;
        }
    }
}
