<?php
/**
 * File Path: edit-decision.php
 * Description: React interface for editing existing strategic decisions.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$decisionId = $_GET['id'] ?? null;
$pdo = getDbConnection();
$orgId = $_SESSION['current_org_id'];

// Fetch existing data
$stmt = $pdo->prepare("SELECT * FROM decisions WHERE id = ? AND organization_id = ?");
$stmt->execute([$decisionId, $orgId]);
$decision = $stmt->fetch();

if (!$decision) {
    header('Location: /dashboard.php');
    exit;
}

// Fetch existing options
$stmt = $pdo->prepare("SELECT id, name, description, is_ai_suggested FROM decision_options WHERE decision_id = ?");
$stmt->execute([$decisionId]);
$options = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Strategy | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/includes/header.php'; ?>
    <div id="root"></div>

    <script type="text/babel">
        const { useState } = React;

        function App() {
            const [title, setTitle] = useState(<?= json_encode($decision['title']) ?>);
            const [problem, setProblem] = useState(<?= json_encode($decision['problem_statement']) ?>);
            const [options, setOptions] = useState(<?= json_encode($options) ?>);
            const [isSubmitting, setIsSubmitting] = useState(false);

            const handleUpdate = async () => {
                setIsSubmitting(true);
                try {
                    const res = await fetch('/api/create-decision.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            id: <?= $decisionId ?>,
                            title,
                            problem,
                            options,
                            mode: 'edit'
                        })
                    });
                    const data = await res.json();
                    if (data.success) window.location.href = `/decision.php?id=<?= $decisionId ?>`;
                    else alert(data.error);
                } catch (e) { alert("Update failed."); }
                finally { setIsSubmitting(false); }
            };

            return (
                <main className="max-w-4xl mx-auto py-20 px-6">
                    <header className="mb-12 flex justify-between items-end">
                        <div>
                            <h1 className="text-4xl font-black text-gray-900">Edit Strategic Log</h1>
                            <p className="text-gray-500 font-medium">Refining the logic for #{<?= $decisionId ?>}</p>
                        </div>
                        <a href="/decision.php?id=<?= $decisionId ?>" className="text-xs font-black text-gray-400 uppercase tracking-widest hover:text-indigo-600 transition">Discard Changes</a>
                    </header>

                    <div className="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm space-y-8">
                        <div className="space-y-4">
                            <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest">Title</label>
                            <input className="w-full p-5 border-2 border-gray-50 rounded-2xl text-xl font-bold focus:border-indigo-600 outline-none transition" value={title} onChange={e => setTitle(e.target.value)} />
                        </div>

                        <div className="space-y-4">
                            <label className="text-[10px] font-black text-gray-400 uppercase tracking-widest">Problem Statement</label>
                            <textarea className="w-full p-5 border-2 border-gray-50 rounded-2xl h-32 focus:border-indigo-600 outline-none transition font-medium" value={problem} onChange={e => setProblem(e.target.value)} />
                        </div>

                        <div className="pt-8 border-t border-gray-50">
                            <h3 className="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6">Modify Paths</h3>
                            <div className="space-y-4">
                                {options.map((opt, i) => (
                                    <div key={i} className="p-6 bg-gray-50 rounded-3xl border border-gray-100 space-y-3">
                                        <input className="w-full p-3 bg-white border rounded-xl font-bold" value={opt.name} onChange={e => {
                                            const newOpts = [...options];
                                            newOpts[i].name = e.target.value;
                                            setOptions(newOpts);
                                        }} />
                                        <textarea className="w-full p-3 bg-white border rounded-xl text-sm" value={opt.description} onChange={e => {
                                            const newOpts = [...options];
                                            newOpts[i].description = e.target.value;
                                            setOptions(newOpts);
                                        }} />
                                    </div>
                                ))}
                            </div>
                        </div>

                        <button
                            onClick={handleUpdate}
                            disabled={isSubmitting}
                            className="w-full bg-indigo-600 text-white py-5 rounded-2xl font-black text-lg shadow-xl hover:bg-indigo-700 transition-all disabled:opacity-50"
                        >
                            {isSubmitting ? 'Updating Intelligence...' : 'Save Changes'}
                        </button>
                    </div>
                </main>
            );
        }

        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<App />);
    </script>
</body>
</html>
