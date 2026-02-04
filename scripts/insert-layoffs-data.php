<?php
/**
 * Quick fix to manually insert known layoffs data
 * Run this once to populate your database with real layoff data
 */

require_once __DIR__ . '/../config.php';

$pdo = getDbConnection();

echo "📊 Inserting known layoff data...\n\n";

// Real layoffs from research
$layoffs = [
    ['company' => 'Lattice', 'layoff_count' => 105, 'percentage' => 15, 'date' => '2023-01-19', 'industry' => 'HR Tech', 'source' => 'layoffs_fyi'],
    ['company' => 'Beamery', 'layoff_count' => 50, 'percentage' => 12, 'date' => '2023-01-11', 'industry' => 'HR Tech', 'source' => 'layoffs_fyi'],
    ['company' => 'MessageBird', 'layoff_count' => 248, 'percentage' => 31, 'date' => '2022-11-15', 'industry' => 'Communications', 'source' => 'research'],
    ['company' => 'Zepz', 'layoff_count' => 420, 'percentage' => 26, 'date' => '2023-05-16', 'industry' => 'Fintech', 'source' => 'research'],
    ['company' => 'Hopin', 'layoff_count' => 242, 'percentage' => 29, 'date' => '2022-07-11', 'industry' => 'Events', 'source' => 'research'],
    ['company' => 'Stash', 'layoff_count' => 200, 'percentage' => 40, 'date' => '2024-12-04', 'industry' => 'Fintech', 'source' => 'research'],
    ['company' => 'Cameo', 'layoff_count' => 87, 'percentage' => 25, 'date' => '2022-05-11', 'industry' => 'Entertainment', 'source' => 'research'],
    ['company' => 'Pipe', 'layoff_count' => 65, 'percentage' => 50, 'date' => '2024-11-20', 'industry' => 'Fintech', 'source' => 'research'],
    ['company' => 'Oyster HR', 'layoff_count' => 75, 'percentage' => 20, 'date' => '2023-02-15', 'industry' => 'HR Tech', 'source' => 'research'],
    ['company' => 'Limeade', 'layoff_count' => 30, 'percentage' => 15, 'date' => '2023-01-20', 'industry' => 'HR Tech', 'source' => 'research'],
    ['company' => 'Stripe', 'layoff_count' => 1000, 'percentage' => 14, 'date' => '2022-11-03', 'industry' => 'Fintech', 'source' => 'research'],
    ['company' => 'Coinbase', 'layoff_count' => 1100, 'percentage' => 18, 'date' => '2022-06-14', 'industry' => 'Crypto', 'source' => 'research'],
    ['company' => 'Netflix', 'layoff_count' => 450, 'percentage' => 4, 'date' => '2022-06-23', 'industry' => 'Entertainment', 'source' => 'research'],
    ['company' => 'Robinhood', 'layoff_count' => 1072, 'percentage' => 23, 'date' => '2022-08-02', 'industry' => 'Fintech', 'source' => 'research'],
    ['company' => 'Shopify', 'layoff_count' => 1000, 'percentage' => 10, 'date' => '2022-07-26', 'industry' => 'E-commerce', 'source' => 'research'],
];

// Create table if not exists
$pdo->exec("
    CREATE TABLE IF NOT EXISTS external_layoffs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_name VARCHAR(255),
        layoff_count INT,
        percentage FLOAT,
        layoff_date DATE,
        industry VARCHAR(100),
        source VARCHAR(50),
        raw_data JSON,
        ingested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_company (company_name),
        INDEX idx_date (layoff_date),
        INDEX idx_industry (industry),
        UNIQUE KEY unique_layoff (company_name, layoff_date)
    )
");

$stmt = $pdo->prepare("
    INSERT INTO external_layoffs 
    (company_name, layoff_count, percentage, layoff_date, industry, source, raw_data)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        layoff_count = VALUES(layoff_count),
        percentage = VALUES(percentage),
        industry = VALUES(industry),
        source = VALUES(source)
");

$count = 0;
foreach ($layoffs as $layoff) {
    $stmt->execute([
        $layoff['company'],
        $layoff['layoff_count'],
        $layoff['percentage'],
        $layoff['date'],
        $layoff['industry'],
        $layoff['source'],
        json_encode($layoff)
    ]);
    $count++;
    echo "✓ Added: {$layoff['company']} ({$layoff['layoff_count']} employees, {$layoff['percentage']}%)\n";
}

echo "\n✅ Inserted $count layoff records!\n\n";

// Verify
$result = $pdo->query("SELECT COUNT(*) as total FROM external_layoffs")->fetch();
echo "📊 Total layoffs in database: {$result['total']}\n";

// Show sample
echo "\n📋 Sample layoffs:\n";
$samples = $pdo->query("
    SELECT company_name, layoff_count, percentage, industry, layoff_date 
    FROM external_layoffs 
    ORDER BY layoff_count DESC 
    LIMIT 5
")->fetchAll();

foreach ($samples as $sample) {
    echo "   • {$sample['company_name']}: {$sample['layoff_count']} employees ({$sample['percentage']}%) - {$sample['industry']}\n";
}