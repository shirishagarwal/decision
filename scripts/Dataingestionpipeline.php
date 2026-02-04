<?php
/**
 * DecisionVault - Data Ingestion Pipeline
 * Pulls real-world failure patterns and success benchmarks into the intelligence engine.
 */

require_once __DIR__ . '/../config.php';

class DataIngestionPipeline {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection(); //
    }

    /**
     * Seeds the database with failure patterns from curated research (e.g., Failory, CB Insights).
     * This data feeds the AI's "Predictive Strategy" warnings.
     *
     */
    public function ingestFailurePatterns() {
        echo "🚀 Starting Intelligence Data Ingestion...\n";
        
        // This seeds the data points referenced in your "Stop Guessing" vision
        $patterns = [
            ['name' => 'Quibi', 'industry' => 'Entertainment', 'reason' => 'No market need - short-form content mismatch', 'type' => 'Product'],
            ['name' => 'Theranos', 'industry' => 'Healthcare', 'reason' => 'Lack of technical validation/transparency', 'type' => 'Strategic'],
            ['name' => 'Fast.co', 'industry' => 'Fintech', 'reason' => 'Unsustainable burn rate/high acquisition cost', 'type' => 'Financial'],
            ['name' => 'Scale-up Example', 'industry' => 'SaaS', 'reason' => 'Hiring senior executives before Product-Market Fit', 'type' => 'Personnel']
        ];

        $stmt = $this->pdo->prepare("
            INSERT INTO external_startup_failures (company_name, industry, failure_reason, decision_type, source)
            VALUES (?, ?, ?, ?, 'DecisionVault Research')
            ON DUPLICATE KEY UPDATE ingested_at = NOW()
        ");

        foreach ($patterns as $p) {
            $stmt->execute([$p['name'], $p['industry'], $p['reason'], $p['type']]);
            echo "✓ Ingested Pattern: {$p['name']} ({$p['type']})\n";
        }
        
        echo "✅ Pipeline Complete. AI now has fresh failure data.\n";
    }
}

// Run if called via Command Line Interface (CLI)
if (php_sapi_name() === 'cli') {
    $pipeline = new DataIngestionPipeline();
    $pipeline->ingestFailurePatterns();
}
