<?php
/**
 * File Path: lib/RAGService.php
 * Description: Logic for Retrieval-Augmented Generation (RAG).
 * Manages "Context Injection" from internal organization documents.
 */

class RAGService {
    
    /**
     * Retrieves relevant snippets from internal datasets based on a search query.
     * In a production environment, this would call a Vector DB (Pinecone/Milvus).
     * For this implementation, we simulate the retrieval from organization_documents.
     */
    public static function getRelevantContext($pdo, $orgId, $query) {
        if (!$orgId) return "";

        try {
            // Fetch metadata of indexed documents
            $stmt = $pdo->prepare("SELECT id, file_name FROM organization_documents WHERE organization_id = ? AND status = 'ready'");
            $stmt->execute([$orgId]);
            $docs = $stmt->fetchAll();

            if (empty($docs)) return "No internal organizational datasets linked.";

            // MOCK RAG LOGIC:
            // In a real system, we would:
            // 1. Convert $query into a vector embedding.
            // 2. Perform a similarity search in a vector DB.
            // 3. Return the top 3 most relevant text chunks.
            
            // For now, we return a summary of the available institutional knowledge
            // to demonstrate the context injection in the AI prompt.
            $context = "INTERNAL INSTITUTIONAL KNOWLEDGE (Siloed):\n";
            $context .= "The following proprietary datasets are linked to this organization and available for reasoning: \n";
            foreach($docs as $doc) {
                $context .= "- " . $doc['file_name'] . " (Indexed for Strategic Match)\n";
            }
            $context .= "\nSIMULATED RETRIEVAL: Using internal benchmarks from these documents to weight the recommendation.";

            return $context;

        } catch (Exception $e) {
            return "Internal knowledge retrieval unavailable.";
        }
    }

    /**
     * Processes a new file for the knowledge base.
     */
    public static function processDocument($pdo, $orgId, $userId, $fileName, $fileType) {
        $stmt = $pdo->prepare("
            INSERT INTO organization_documents (organization_id, file_name, file_type, uploaded_by, doc_hash, status)
            VALUES (?, ?, ?, ?, ?, 'ready')
        ");
        $hash = hash('sha256', $fileName . time());
        $stmt->execute([$orgId, $fileName, $fileType, $userId, $hash]);
        return $pdo->lastInsertId();
    }
}
