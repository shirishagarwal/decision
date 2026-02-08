<?php
/**
 * File Path: api/upload-document.php
 * Description: Backend for receiving and initializing the RAG indexing process.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/RAGService.php';
require_once __DIR__ . '/../lib/Security.php';
requireLogin();

$user = getCurrentUser();
$orgId = $_SESSION['current_org_id'];
$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['doc'])) {
    $file = $_FILES['doc'];
    $fileName = basename($file['name']);
    $fileType = strtoupper(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileType, ['PDF', 'DOCX', 'TXT', 'CSV'])) {
        die("Unsupported file type for institutional indexing.");
    }

    // In a real system, move_uploaded_file and trigger an async python worker for embedding.
    // Here we register it and log the action.
    try {
        $docId = RAGService::processDocument($pdo, $orgId, $user['id'], $fileName, $fileType);
        
        // Log for Governance Audit
        Security::logAction($pdo, $orgId, $user['id'], 'Uploaded Knowledge Dataset', $docId);
        
        header("Location: /organization-knowledge.php?status=indexed");
        exit;

    } catch (Exception $e) {
        header("Location: /organization-knowledge.php?error=failed");
        exit;
    }
}
