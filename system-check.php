<?php
require_once __DIR__ . '/config.php';
if (!isLoggedIn()) die('Please login first');

$pdo = getDbConnection();
$issues = [];
$successes = [];

// Check 1: Review columns in decisions table
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM decisions LIKE 'review_date'");
    if ($stmt->rowCount() > 0) {
        $successes[] = "✅ review_date column exists";
    } else {
        $issues[] = "❌ review_date column MISSING - Run database-unicorn.sql!";
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM decisions LIKE 'expected_outcome'");
    if ($stmt->rowCount() > 0) {
        $successes[] = "✅ expected_outcome column exists";
    } else {
        $issues[] = "❌ expected_outcome column MISSING - Run database-unicorn.sql!";
    }
} catch (Exception $e) {
    $issues[] = "❌ Error checking decisions table: " . $e->getMessage();
}

// Check 2: decision_reviews table
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'decision_reviews'");
    if ($stmt->rowCount() > 0) {
        $successes[] = "✅ decision_reviews table exists";
    } else {
        $issues[] = "❌ decision_reviews table MISSING - Run database-unicorn.sql!";
    }
} catch (Exception $e) {
    $issues[] = "❌ Error checking decision_reviews table";
}

// Check 3: decision_votes table
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'decision_votes'");
    if ($stmt->rowCount() > 0) {
        $successes[] = "✅ decision_votes table exists";
    } else {
        $issues[] = "❌ decision_votes table MISSING - Run database-unicorn.sql!";
    }
} catch (Exception $e) {
    $issues[] = "❌ Error checking decision_votes table";
}

// Check 4: decision_templates table
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM decision_templates");
    $result = $stmt->fetch();
    if ($result['count'] >= 8) {
        $successes[] = "✅ decision_templates has {$result['count']} templates";
    } else {
        $issues[] = "❌ decision_templates only has {$result['count']} templates (should be 8) - Run database-unicorn.sql!";
    }
} catch (Exception $e) {
    $issues[] = "❌ decision_templates table MISSING - Run database-unicorn.sql!";
}

// Check 5: Decisions with review dates
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM decisions WHERE review_date IS NOT NULL");
    $result = $stmt->fetch();
    if ($result['count'] > 0) {
        $successes[] = "✅ {$result['count']} decision(s) have review dates";
    } else {
        $issues[] = "⚠️ No decisions have review dates yet (need to create new ones or update existing)";
    }
} catch (Exception $e) {
    $issues[] = "❌ Can't check review dates - column might not exist";
}

// Check 6: Files exist
$files = [
    'review-decision.php' => 'Review completion page',
    'insights.php' => 'Decision Intelligence dashboard',
    'templates.php' => 'Template selection page',
    'api/review-decision.php' => 'Review API',
    'api/vote.php' => 'Voting API'
];

foreach ($files as $file => $name) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $successes[] = "✅ $name exists ($file)";
    } else {
        $issues[] = "❌ $name MISSING - Upload $file!";
    }
}

// Check 7: Get a sample decision
$sampleDecision = null;
try {
    $stmt = $pdo->query("SELECT id, title, review_date, expected_outcome FROM decisions ORDER BY created_at DESC LIMIT 1");
    $sampleDecision = $stmt->fetch();
} catch (Exception $e) {
    // Ignore
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Check - DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-2">🔍 DecisionVault System Check</h1>
        <p class="text-gray-600 mb-8">Checking if all unicorn features are properly installed...</p>

        <?php if (!empty($issues)): ?>
        <div class="bg-red-50 border-2 border-red-200 rounded-xl p-6 mb-6">
            <h2 class="text-xl font-bold text-red-900 mb-4">❌ Issues Found (<?php echo count($issues); ?>)</h2>
            <ul class="space-y-2">
                <?php foreach ($issues as $issue): ?>
                <li class="text-red-800"><?php echo $issue; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($successes)): ?>
        <div class="bg-green-50 border-2 border-green-200 rounded-xl p-6 mb-6">
            <h2 class="text-xl font-bold text-green-900 mb-4">✅ Working (<?php echo count($successes); ?>)</h2>
            <ul class="space-y-2">
                <?php foreach ($successes as $success): ?>
                <li class="text-green-800"><?php echo $success; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Fix Instructions -->
        <?php if (!empty($issues)): ?>
        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-6 mb-6">
            <h2 class="text-xl font-bold text-blue-900 mb-4">🔧 How to Fix</h2>
            
            <?php if (in_array("❌ review_date column MISSING - Run database-unicorn.sql!", $issues)): ?>
            <div class="mb-4">
                <h3 class="font-bold text-blue-900 mb-2">Step 1: Run Database Update</h3>
                <ol class="list-decimal ml-5 space-y-1 text-blue-800 text-sm">
                    <li>Download <code>database-unicorn.sql</code></li>
                    <li>Open phpMyAdmin</li>
                    <li>Select your database</li>
                    <li>Click "SQL" tab</li>
                    <li>Paste ENTIRE file contents</li>
                    <li>Click "Go"</li>
                    <li>Should see: "8 rows inserted"</li>
                    <li>Refresh this page</li>
                </ol>
            </div>
            <?php endif; ?>

            <?php 
            $missingFiles = array_filter($issues, function($i) { return strpos($i, 'MISSING - Upload') !== false; });
            if (!empty($missingFiles)): 
            ?>
            <div class="mb-4">
                <h3 class="font-bold text-blue-900 mb-2">Step 2: Upload Missing Files</h3>
                <ul class="list-disc ml-5 space-y-1 text-blue-800 text-sm">
                    <?php foreach ($missingFiles as $file): ?>
                    <li><?php echo str_replace('❌ ', '', $file); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Sample Decision Test -->
        <?php if ($sampleDecision): ?>
        <div class="bg-white border-2 border-gray-200 rounded-xl p-6">
            <h2 class="text-xl font-bold mb-4">🧪 Test Decision</h2>
            <div class="mb-4">
                <div class="text-sm text-gray-500 mb-1">Latest Decision:</div>
                <div class="font-bold"><?php echo htmlspecialchars($sampleDecision['title']); ?></div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                <div>
                    <div class="text-gray-500">Review Date:</div>
                    <div class="font-medium"><?php echo $sampleDecision['review_date'] ?: '❌ Not set'; ?></div>
                </div>
                <div>
                    <div class="text-gray-500">Expected Outcome:</div>
                    <div class="font-medium"><?php echo $sampleDecision['expected_outcome'] ? '✅ Set' : '❌ Not set'; ?></div>
                </div>
            </div>

            <?php if (!$sampleDecision['review_date']): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                <div class="font-semibold text-amber-900 mb-2">⚠️ This decision has no review date</div>
                <p class="text-sm text-amber-800 mb-3">Set one to test the review button:</p>
                <code class="block bg-white p-3 rounded text-xs text-gray-800 mb-2">
                    UPDATE decisions SET review_date = CURDATE(), expected_outcome = 'Test expectation' WHERE id = <?php echo $sampleDecision['id']; ?>;
                </code>
                <p class="text-xs text-amber-700">Run this in phpMyAdmin, then view the decision</p>
            </div>
            <?php endif; ?>

            <a href="decision.php?id=<?php echo $sampleDecision['id']; ?>" 
               class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                View This Decision →
            </a>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="mt-8 flex gap-4">
            <a href="dashboard.php" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Back to Dashboard
            </a>
            <button onclick="location.reload()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                🔄 Refresh Check
            </button>
        </div>
    </div>
</body>
</html>