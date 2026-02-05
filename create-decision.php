<?php
/**
 * File Path: create-decision.php
 * Description: Hybrid manual + AI decision flow.
 * Updated: Added explicit loading states to prevent "stuck" confusion.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$user = getCurrentUser();
$orgId = $_SESSION['current_org_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Strategic Decision | <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; }
        .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); }
        .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .loader {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #4f46e5;
            border-radius: 50%;
            width: 14px;
            height: 14px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 8px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="flex flex-col min-h-screen">
    
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div id="root" class="flex-grow"></div>

    <script type="text/babel">
        const { useState } = React;

        function App() {
            const [step, setStep] = useState(1);
            const [title, setTitle] = useState('');
            const [problem, setProblem] = useState('');
            const [options, setOptions] = useState([
                { id: Date.now(), name: '', description: '', isAiGenerated: false }
            ]);
            
            const [aiSuggestions, setAiSuggestions] = useState([]);
            const [isAiLoading, setIsAiLoading] = useState(false);
            const [isSubmitting, setIsSubmitting] = useState(false);

            const fetchSuggestions = async () => {
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
                } catch (e) {
                    console.error("AI Fetch Error:", e);
                } finally {
                    setIsAiLoading(false);
                }
            };

            const addOption = (initialData = {}) => {
                setOptions(prev => [...prev, {
                    id: Date.now() + Math.random(),
                    name: initialData.name || '',
                    description: initialData.description || (initialData.reason || ''),
                    isAiGenerated: !!initialData.isAiGenerated
                }]);
            };

            const handleSubmit = async () => {
                if (!title) return alert("Title required.");
                const validOptions = options.filter(o => o.name.trim() !== '');
                if (validOptions.length === 0) return alert("Please provide at least one option.");

                setIsSubmitting(true);
                try {
                    const res = await fetch('/api/create-decision.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ title, problem, options: validOptions })
                    });
                    
                    const data = await res.json();
                    if (data.success) {
                        window.location.replace(`/decision.php?id=${data.decision_id}`);
                    } else {
                        alert(data.error || "Unknown error occurred.");
                        setIsSubmitting(false);
                    }
                } catch (e) {
                    alert("Submission failed. The system may be busy. Please try again.");
                    setIsSubmitting(false);
                }
            };

            return (
                <main className="max-w-7xl mx-auto py-12 px-6">
                    <header className="mb-12">
                        <h1 className="text-4xl font-black text-gray-900 tracking-tight leading-none mb-2">New Strategic Recording</h1>
                        <p className="text-gray-500 font-medium tracking-tight">Access failure patterns and define your defensive logic.</p>
                    </header>

                    <div className="grid lg:grid-cols-3 gap-12">
                        <div className="lg:col-span-2 space-y-8">
                            
                            {/* Step 1: CONTEXT */}
                            <div className={`bg-white p-10 rounded-[40px] border border-gray-100 card-shadow transition-all duration-300 ${step !== 1 ? 'opacity-40 grayscale pointer-events-none scale-95 origin-top' : ''}`}>
                                <h2 className="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] mb-8">01 &bull; Strategic Context</h2>
                                <div className="space-y-6">
                                    <input
                                        className="w-full p-5 border-2 border-gray-100 rounded-2xl outline-none focus:border-indigo-600 bg-gray-50/50 focus:bg-white transition-all text-xl font-bold"
                                        placeholder="Decision Title"
                                        value={title} onChange={e => setTitle(e.target.value)}
                                    />
                                    <textarea
                                        className="w-full p-5 border-2 border-gray-100 rounded-2xl outline-none focus:border-indigo-600 bg-gray-50/50 focus:bg-white transition-all h-32 font-medium"
                                        placeholder="Core problem..."
                                        value={problem} onChange={e => setProblem(e.target.value)}
                                    ></textarea>
                                    <button
                                        onClick={() => { setStep(2); fetchSuggestions(); }}
                                        disabled={!title}
                                        className="bg-indigo-600 text-white px-10 py-5 rounded-2xl font-black shadow-xl hover:bg-indigo-700 disabled:opacity-50 transition-all flex items-center justify-center"
                                    >
                                        Next: Define Options →
                                    </button>
                                </div>
                            </div>

                            {/* Step 2: OPTIONS */}
                            {step === 2 && (
                                <div className="bg-white p-10 rounded-[40px] border border-gray-100 card-shadow animate-fade-in">
                                    <div className="flex justify-between items-center mb-10">
                                        <h2 className="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em]">02 &bull; Strategic Options</h2>
                                        <button onClick={() => addOption()} className="text-[10px] font-black bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl uppercase hover:bg-indigo-100 transition">+ Add Manual</button>
                                    </div>
                                    
                                    <div className="space-y-6 mb-12">
                                        {options.map((opt, i) => (
                                            <div key={opt.id} className="p-8 rounded-3xl space-y-4 border border-gray-100 bg-gray-50/50">
                                                <input className="w-full p-4 bg-white border border-gray-100 rounded-2xl font-bold outline-none focus:border-indigo-600" placeholder="Option Name" value={opt.name} onChange={e => {
                                                    const newOpts = [...options];
                                                    newOpts[i].name = e.target.value;
                                                    setOptions(newOpts);
                                                }} />
                                                <textarea className="w-full p-4 bg-white border border-gray-100 rounded-2xl text-sm outline-none focus:border-indigo-600 h-24" placeholder="Rationale..." value={opt.description} onChange={e => {
                                                    const newOpts = [...options];
                                                    newOpts[i].description = e.target.value;
                                                    setOptions(newOpts);
                                                }}></textarea>
                                            </div>
                                        ))}
                                    </div>

                                    <div className="flex gap-4">
                                        <button onClick={() => setStep(1)} className="px-10 py-5 border-2 border-gray-100 rounded-2xl font-black text-gray-400 hover:bg-gray-50 transition">Back</button>
                                        <button
                                            onClick={handleSubmit}
                                            disabled={isSubmitting}
                                            className="flex-1 bg-indigo-600 text-white py-5 rounded-2xl font-black text-xl shadow-2xl disabled:opacity-50 flex items-center justify-center"
                                        >
                                            {isSubmitting ? <><span className="loader"></span>Recording Strategy...</> : 'Finalize Logic'}
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>

                        <aside className="space-y-6">
                            <div className="bg-indigo-600 p-10 rounded-[40px] text-white shadow-2xl shadow-indigo-100 sticky top-32">
                                <h3 className="font-black text-[10px] uppercase tracking-[0.2em] mb-10">Strategy Engine</h3>
                                {isAiLoading ? <div className="animate-pulse space-y-4"><div className="h-20 bg-white/10 rounded-2xl"></div><div className="h-20 bg-white/10 rounded-2xl"></div></div> : (
                                    <div className="space-y-4">
                                        {aiSuggestions.map((s, i) => (
                                            <button key={i} onClick={() => addOption({ name: s.name, description: s.description || s.reason, isAiGenerated: true })} className="w-full p-6 bg-white/5 hover:bg-white/10 border border-white/5 rounded-3xl text-left transition group">
                                                <div className="font-bold text-sm mb-1">+ {s.name}</div>
                                                <div className="text-[10px] text-indigo-200 font-medium">Confidence: {s.confidence || 85}%</div>
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
