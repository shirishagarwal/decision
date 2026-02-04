<?php
/**
 * DecisionVault - Template Submission API
 * Promotes a private framework to the Public Marketplace.
 */
require_once __DIR__ . '/../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDbConnection();
    $data = json_decode(file_get_contents('php://input'), true);

    //
    $stmt = $pdo->prepare("
        INSERT INTO decision_templates (
            name, category, description, problem_statement_template, 
            context_template, is_public, author_id
        ) VALUES (?, ?, ?, ?, ?, 1, ?)
    ");

    try {
        $stmt->execute([
            $data['name'],
            $data['category'],
            $data['description'],
            $data['problem_template'],
            $data['context_template'],
            $_SESSION['user_id']
        ]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database failure']);
    }
}
