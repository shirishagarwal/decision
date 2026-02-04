<?php
// lib/DecisionAssistant.php

class DecisionAssistant {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection(); // cite: 144
    }

    /**
     * Converts an AI-approved option into a permanent record.
     *
     */
    public function saveGeneratedDecision($userId, $workspaceId, $title, $problem, $options) {
        try {
            $this->pdo->beginTransaction();

            // 1. Create the Decision
            $stmt = $this->pdo->prepare("
                INSERT INTO decisions (workspace_id, title, problem_statement, created_by, status) 
                VALUES (?, ?, ?, ?, 'Proposed')
            ");
            $stmt->execute([$workspaceId, $title, $problem, $userId]);
            $decisionId = $this->pdo->lastInsertId();

            // 2. Save Options
            $optStmt = $this->pdo->prepare("
                INSERT INTO options (decision_id, name, description) 
                VALUES (?, ?, ?)
            ");

            foreach ($options as $opt) {
                $optStmt->execute([
                    $decisionId,
                    $opt['name'],
                    $opt['rationale'] ?? $opt['description']
                ]);
            }

            $this->pdo->commit();
            return $decisionId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
