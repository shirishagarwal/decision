<?php
require_once __DIR__ . '/config.php';
if (!isLoggedIn()) die('Login first');

$decisionId = $_GET['id'] ?? null;
if (!$decisionId) die('Provide decision ID: ?id=X');

$pdo = getDbConnection();

// Get decision
$stmt = $pdo->prepare("SELECT * FROM decisions WHERE id = ?");
$stmt->execute([$decisionId]);
$decision = $stmt->fetch();

// Get options
$stmt = $pdo->prepare("SELECT * FROM options WHERE decision_id = ? ORDER BY sort_order, id");
$stmt->execute([$decisionId]);
$options = $stmt->fetchAll();

// Get pros/cons
$proscons = [];
foreach ($options as $opt) {
    $stmt = $pdo->prepare("SELECT * FROM option_pros_cons WHERE option_id = ? ORDER BY type, id");
    $stmt->execute([$opt['id']]);
    $proscons[$opt['id']] = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Diagnostic</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        table { border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        .duplicate { background: #ffcccc; }
    </style>
</head>
<body>
    <h1>Database Diagnostic for Decision #<?php echo $decisionId; ?></h1>
    
    <h2>Decision Info</h2>
    <table>
        <tr><th>Field</th><th>Value</th></tr>
        <tr><td>ID</td><td><?php echo $decision['id']; ?></td></tr>
        <tr><td>Title</td><td><?php echo htmlspecialchars($decision['title']); ?></td></tr>
        <tr><td>Status</td><td><?php echo $decision['status']; ?></td></tr>
        <tr><td>Created</td><td><?php echo $decision['created_at']; ?></td></tr>
    </table>

    <h2>Options (<?php echo count($options); ?> total)</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Sort Order</th>
                <th>Name</th>
                <th>Cost</th>
                <th>Effort</th>
                <th>Pros/Cons Count</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $names = [];
            foreach ($options as $opt): 
                $isDupe = in_array($opt['name'], $names);
                $names[] = $opt['name'];
                $proCount = count(array_filter($proscons[$opt['id']], fn($pc) => $pc['type'] === 'pro'));
                $conCount = count(array_filter($proscons[$opt['id']], fn($pc) => $pc['type'] === 'con'));
            ?>
            <tr class="<?php echo $isDupe ? 'duplicate' : ''; ?>">
                <td><?php echo $opt['id']; ?></td>
                <td><?php echo $opt['sort_order']; ?></td>
                <td><?php echo htmlspecialchars($opt['name']); ?></td>
                <td><?php echo htmlspecialchars($opt['estimated_cost']); ?></td>
                <td><?php echo htmlspecialchars($opt['estimated_effort']); ?></td>
                <td><?php echo $proCount; ?> pros, <?php echo $conCount; ?> cons</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (count($options) !== count(array_unique($names))): ?>
    <p style="color: red; font-weight: bold;">⚠️ DUPLICATES DETECTED! Same name appears multiple times.</p>
    <p>To fix, delete duplicate option IDs manually in phpMyAdmin:</p>
    <pre>DELETE FROM option_pros_cons WHERE option_id IN (<?php 
        $dupes = [];
        $seen = [];
        foreach ($options as $opt) {
            if (in_array($opt['name'], $seen)) {
                $dupes[] = $opt['id'];
            }
            $seen[] = $opt['name'];
        }
        echo implode(', ', $dupes);
    ?>);
DELETE FROM options WHERE id IN (<?php echo implode(', ', $dupes); ?>);</pre>
    <?php endif; ?>

    <h2>Detailed Pros/Cons</h2>
    <?php foreach ($options as $opt): ?>
    <h3>Option #<?php echo $opt['id']; ?>: <?php echo htmlspecialchars($opt['name']); ?></h3>
    <table>
        <thead>
            <tr><th>ID</th><th>Type</th><th>Content</th></tr>
        </thead>
        <tbody>
            <?php foreach ($proscons[$opt['id']] as $pc): ?>
            <tr>
                <td><?php echo $pc['id']; ?></td>
                <td><?php echo $pc['type']; ?></td>
                <td><?php echo htmlspecialchars($pc['content']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endforeach; ?>

    <hr>
    <p><a href="decision.php?id=<?php echo $decisionId; ?>">← Back to Decision</a></p>
    <p><a href="edit-decision.php?id=<?php echo $decisionId; ?>">✏️ Edit Decision</a></p>
</body>
</html>