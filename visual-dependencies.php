<?php
require_once __DIR__ . '/lib/auth.php';
requireOrgAccess();

$decisionId = $_GET['id'] ?? null;
$pdo = getDbConnection();

// Fetch the main decision info
$stmt = $pdo->prepare("SELECT title FROM decisions WHERE id = ?");
$stmt->execute([$decisionId]);
$decision = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Strategy Map | <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script type="module">
        import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
        mermaid.initialize({ startOnLoad: true, theme: 'neutral' });
    </script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-6xl mx-auto">
        <header class="mb-12 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-black">Strategic Dependency Map</h1>
                <p class="text-gray-500">Visualizing the path for: <strong><?php echo htmlspecialchars($decision['title']); ?></strong></p>
            </div>
            <a href="/decision.php?id=<?php echo $decisionId; ?>" class="bg-white border px-6 py-2 rounded-xl font-bold">Back to Detail</a>
        </header>

        <div class="bg-white p-12 rounded-3xl border shadow-sm flex justify-center">
            <pre class="mermaid">
                graph TD
                <?php
                // Fetch all dependencies in the workspace to build the full tree
                $stmt = $pdo->prepare("
                    SELECT d1.title as child, d2.title as parent, d1.status as child_status
                    FROM decision_dependencies dep
                    JOIN decisions d1 ON dep.decision_id = d1.id
                    JOIN decisions d2 ON dep.depends_on_id = d2.id
                    WHERE d1.workspace_id = (SELECT workspace_id FROM decisions WHERE id = ?)
                ");
                $stmt->execute([$decisionId]);
                $deps = $stmt->fetchAll();

                foreach($deps as $row) {
                    $p = str_replace('"', '', $row['parent']);
                    $c = str_replace('"', '', $row['child']);
                    echo "    \"$p\" --> \"$c\"\n";
                    if($row['child_status'] === 'Implemented') {
                        echo "    style \"$c\" fill:#dcfce7,stroke:#22c55e\n";
                    }
                }
                ?>
            </pre>
        </div>

        <div class="mt-8 grid grid-cols-3 gap-6">
            <div class="p-4 bg-white border rounded-xl flex items-center gap-3">
                <div class="w-4 h-4 bg-green-100 border border-green-500 rounded"></div>
                <span class="text-xs font-bold text-gray-400 uppercase">Implemented</span>
            </div>
            <div class="p-4 bg-white border rounded-xl flex items-center gap-3">
                <div class="w-4 h-4 bg-gray-100 border border-gray-300 rounded"></div>
                <span class="text-xs font-bold text-gray-400 uppercase">Proposed / Pending</span>
            </div>
            <div class="p-4 bg-indigo-600 border rounded-xl flex items-center gap-3">
                <div class="w-4 h-4 bg-indigo-400 rounded"></div>
                <span class="text-xs font-bold text-white uppercase">Critical Path</span>
            </div>
        </div>
    </div>
</body>
</html>
