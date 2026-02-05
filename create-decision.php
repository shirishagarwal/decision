<?php
/**
 * File Path: create-decision.php
 * Description: Hybrid manual + AI decision flow with Marketplace Integration.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$pdo = getDbConnection();
$templateId = $_GET['template_id'] ?? null;
$initialData = ['title' => '', 'problem' => ''];

if ($templateId) {
    $stmt = $pdo->prepare("SELECT * FROM decision_templates WHERE id = ?");
    $stmt->execute([$templateId]);
    $template = $stmt->fetch();
    if ($template) {
        $initialData['title'] = $template['name'];
        $initialData['problem'] = $template['problem_statement_template'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Strategy | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div id="root" class="flex-grow"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;

        function App() {
            const [step, setStep] = useState(1);
            const [title, setTitle] = useState(<?php echo json_encode($initialData['title']); ?>);
            const [problem, setProblem] = useState(<?php echo json_encode($initialData['problem']); ?>);
            const [options, setOptions] = useState([{ id: Date.now(), name: '', description: '', isAiGenerated: false }]);
            const [aiSuggestions, setAiSuggestions] = useState([]);
            const [isAiLoading, setIsAiLoading] = useState(false);
            const [isSubmitting, setIsSubmitting] = useState(false);

            const fetchAiSuggestions = async () => {
                if (title.length < 5) return;
                setIsAiLoading(true);
                try {
                    const res = await fetch('/api/ai-strategy.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ title, problem_statement: problem })
                    });
                    const data = await res.json();
                    if (data.success && data.external?.suggested_options) {
                        setAiSuggestions(data.external.suggested_options);
                    }
                } catch (e) { console.error(e); } finally { setIsAiLoading(false); }
            };

            const handleSubmit = async () => {
                setIsSubmitting(true);
                try {
                    const res = await fetch('/api/create-decision.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ title, problem, options: options.filter(o => o.name) })
                    });
                    const data = await res.json();
                    if (data.success) window.location.replace(`/decision.php?id=${data.decision_id}`);
                    else alert(data.error);
                } catch (e) { alert("Save failed."); } finally { setIsSubmitting(false); }
            };

            return (
                <main className="max-w-6xl mx-auto py-16 px-6">
                    <div className="grid lg:grid-cols-3 gap-12">
                        <div className="lg:col-span-2 space-y-8">
                            <div className={`bg-white p-10 rounded-[40px] border shadow-sm ${step !== 1 ? 'opacity-40 grayscale pointer-events-none scale-95 origin-top' : ''}`}>
                                <h2 className="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-6">01 &bull; Strategic Context</h2>
                                <input className="w-full p-5 border-2 rounded-2xl text-xl font-bold mb-4" placeholder="Decision Title" value={title} onChange={e => setTitle(e.target.value)} />
                                <textarea className="w-full p-5 border-2 rounded-2xl h-32 mb-6" placeholder="What is the core problem?" value={problem} onChange={e => setProblem(e.target.value)} />
                                <button onClick={() => { setStep(2); fetchAiSuggestions(); }} className="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-black">Next: Options →</button>
                            </div>

                            {step === 2 && (
                                <div className="bg-white p-10 rounded-[40px] border shadow-sm">
                                    <h2 className="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-6">02 &bull; Define Paths</h2>
                                    <div className="space-y-4 mb-8">
                                        {options.map((opt, i) => (
                                            <div key={opt.id} className="p-6 bg-gray-50 rounded-3xl border border-gray-100">
                                                <input className="w-full p-3 bg-white border rounded-xl font-bold mb-2" placeholder="Option Name" value={opt.name} onChange={e => {
                                                    const newOpts = [...options];
                                                    newOpts[i].name = e.target.value;
                                                    setOptions(newOpts);
                                                }} />
                                                <textarea className="w-full p-3 bg-white border rounded-xl text-sm" placeholder="Details..." value={opt.description} onChange={e => {
                                                    const newOpts = [...options];
                                                    newOpts[i].description = e.target.value;
                                                    setOptions(newOpts);
                                                }} />
                                            </div>
                                        ))}
                                        <button onClick={() => setOptions([...options, { id: Date.now(), name: '', description: '' }])} className="text-indigo-600 font-bold text-sm">+ Add Option</button>
                                    </div>
                                    <div className="flex gap-4">
                                        <button onClick={() => setStep(1)} className="px-8 py-4 border-2 rounded-2xl font-bold text-gray-400">Back</button>
                                        <button onClick={handleSubmit} disabled={isSubmitting} className="flex-1 bg-indigo-600 text-white py-4 rounded-2xl font-black text-lg">
                                            {isSubmitting ? 'Recording...' : 'Finalize Strategy'}
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>

                        <aside>
                            <div className="bg-slate-900 p-8 rounded-[40px] text-white sticky top-32">
                                <h3 className="font-black text-[10px] uppercase tracking-widest text-indigo-400 mb-6">Strategy Engine</h3>
                                {isAiLoading ? <div className="animate-pulse space-y-4"><div className="h-12 bg-white/5 rounded-xl"></div><div className="h-12 bg-white/5 rounded-xl"></div></div> : (
                                    <div className="space-y-4">
                                        {aiSuggestions.map((s, i) => (
                                            <button key={i} onClick={() => setOptions([...options, { id: Date.now(), name: s.name, description: s.description, isAiGenerated: true }])} className="w-full p-4 bg-white/5 hover:bg-white/10 rounded-2xl text-left transition">
                                                <div className="font-bold text-sm">+ {s.name}</div>
                                                <div className="text-[10px] text-indigo-300 uppercase font-black tracking-tighter mt-1">Adopt AI Path</div>
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>
                        </aside>
                    </div>
                </main>
            );
        }

        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<App />);
    </script>
</body>
</html>
