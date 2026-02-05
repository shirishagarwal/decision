<?php
/**
 * File Path: ingest-failures.php
 * Description: Tools for sourcing real-world failure data into the Intelligence Moat.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$user = getCurrentUser();
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawContent = $_POST['content'] ?? '';
    $sourceUrl = $_POST['source_url'] ?? '';

    if (!empty($rawContent)) {
        // Use Gemini to extract structured logic from the raw text
        $prompt = "Act as a Strategic Analyst. I will provide a post-mortem or article about a startup failure. 
        Extract the following data in RAW JSON format:
        1. 'company_name': Name of the startup.
        2. 'industry': Broad industry (e.g., Fintech, SaaS, E-commerce).
        3. 'decision_type': The primary strategic decision that failed (e.g., International Expansion, Pricing Change).
        4. 'logic_used': The original rationale/logic the founders had.
        5. 'failure_reason': The actual reason the path failed.
        6. 'red_flags': 2-3 subtle warning signs mentioned in the text.

        Article Content:
        {$rawContent}

        Return ONLY raw JSON.";

        $apiKey = GEMINI_API_KEY;
        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . $apiKey;

        $payload = [
            "contents" => [["parts" => [["text" => $prompt]]]],
            "generationConfig" => ["responseMimeType" => "application/json"]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        $cleanJson = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        $data = json_decode($cleanJson, true);

        if (isset($data['company_name'])) {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("
                INSERT INTO external_startup_failures (source_url, company_name, industry, decision_type, logic_used, failure_reason, red_flags)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $sourceUrl,
                $data['company_name'],
                $data['industry'],
                $data['decision_type'],
                $data['logic_used'],
                $data['failure_reason'],
                json_encode($data['red_flags'])
            ]);
            $message = "Intelligence Moat Updated: Successfully ingested {$data['company_name']}.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ingestion Engine | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="max-w-4xl mx-auto py-20 px-6">
        <header class="mb-12">
            <h1 class="text-4xl font-black text-gray-900 tracking-tight">Intelligence Ingestion</h1>
            <p class="text-gray-500 font-medium">Sourcing real-world failure patterns into the Intelligence Moat.</p>
        </header>

        <?php if ($message): ?>
            <div class="mb-8 p-6 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-3xl font-bold flex items-center gap-3">
                <span class="text-2xl">🛡️</span> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm">
            <form method="POST" class="space-y-8">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Source URL (Optional)</label>
                    <input type="url" name="source_url" placeholder="https://medium.com/post-mortem-startup..."
                           class="w-full p-4 border rounded-2xl bg-gray-50 outline-none focus:border-indigo-600 transition">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Paste Article Content</label>
                    <textarea name="content" required placeholder="Paste the text of the failure analysis or post-mortem here..."
                              class="w-full p-6 border rounded-3xl bg-gray-50 h-64 outline-none focus:border-indigo-600 transition"></textarea>
                </div>

                <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-indigo-600 transition-all shadow-xl">
                    Extract Strategic Pattern
                </button>
            </form>
        </div>

        <section class="mt-20">
            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-8">Recent Moat Ingestions</h2>
            <div class="grid gap-4">
                <?php
                $pdo = getDbConnection();
                $recent = $pdo->query("SELECT * FROM external_startup_failures ORDER BY created_at DESC LIMIT 5")->fetchAll();
                foreach($recent as $r): ?>
                    <div class="p-6 bg-white border border-gray-100 rounded-3xl flex justify-between items-center">
                        <div>
                            <div class="font-black text-gray-900"><?php echo htmlspecialchars($r['company_name']); ?></div>
                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($r['decision_type']); ?> &bull; <?php echo htmlspecialchars($r['industry']); ?></div>
                        </div>
                        <div class="text-[10px] font-black text-indigo-600 uppercase">Pattern Secured</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
