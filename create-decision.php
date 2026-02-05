<?php
/**
 * File Path: create-decision.php
 * Description: Hybrid manual + AI decision flow.
 * Updated: Included global header and refined multiple options support.
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
    </style>
</head>
<body class="flex flex-col min-h-screen">
    
    <!-- Global Header -->
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div id="root" class="flex-grow"></div>

    <script type="text/babel">
        const { useState, useEffect, useCallback } = React;

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

            // Fetch AI Suggestions based on Title
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

            const updateOption = (id, field, value) => {
                setOptions(prev => prev.map(o => o.id === id ? { ...o, [field]: value } : o));
            };

            const removeOption = (id) => {
                if (options.length > 1) {
                    setOptions(prev => prev.filter(o => o.id !== id));
                }
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
                    alert("Submission failed. Check network.");
                    setIsSubmitting(false);
                }
            };

            return (
                <main className="max-w-7xl mx-auto py-12 px-6">
                    <header className="mb-12 flex justify-between items-center">
                        <div>
                            <h1 className="text-4xl font-black text-gray-900 tracking-tight leading-none mb-2">New Strategic Recording</h1>
                            <p className="text-gray-500 font-medium">Document your logic. Avoid historical failure patterns.</p>
                        </div>
                    </header>

                    <div className="grid lg:grid-cols-3 gap-12">
                        <div className="lg:col-span-2 space-y-8">
                            
                            {/* Step 1: CONTEXT */}
                            <div className={`bg-white p-10 rounded-[40px] border border-gray-100 card-shadow transition-all duration-300 ${step !== 1 ? 'opacity-40 grayscale pointer-events-none scale-95 origin-top' : ''}`}>
                                <h2 className="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] mb-8">01 &bull; Strategic Context</h2>
                                <div className="space-y-6">
                                    <div>
                                        <label className="block text-sm font-black text-gray-700 mb-2">Decision Title</label>
                                        <input
                                            className="w-full p-5 border-2 border-gray-100 rounded-2xl outline-none focus:border-indigo-600 bg-gray-50/50 focus:bg-white transition-all text-xl font-bold"
                                            placeholder="e.g. Hire Head of Growth vs Agency"
                                            value={title} onChange={e => setTitle(e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-black text-gray-700 mb-2">The Problem Statement</label>
                                        <textarea
                                            className="w-full p-5 border-2 border-gray-100 rounded-2xl outline-none focus:border-indigo-600 bg-gray-50/50 focus:bg-white transition-all h-32 font-medium"
                                            placeholder="What core problem does this solve?"
                                            value={problem} onChange={e => setProblem(e.target.value)}
                                        ></textarea>
                                    </div>
                                    <button
                                        onClick={() => { setStep(2); fetchSuggestions(); }}
                                        disabled={!title}
                                        className="bg-indigo-600 text-white px-10 py-5 rounded-2xl font-black shadow-xl hover:bg-indigo-700 disabled:opacity-50 transition-all"
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
                                        <button
                                            onClick={() => addOption()}
                                            className="text-[10px] font-black bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl uppercase tracking-widest hover:bg-indigo-100 transition"
                                        >
                                            + Add Manual Option
                                        </button>
                                    </div>
                                    
                                    <div className="space-y-6 mb-12">
                                        {options.map((opt, i) => (
                                            <div key={opt.id} className={`p-8 rounded-3xl space-y-4 group relative border ${opt.isAiGenerated ? 'bg-indigo-50/30 border-indigo-100' : 'bg-gray-50/50 border-gray-100'}`}>
                                                <div className="flex justify-between items-center">
                                                    <span className="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                                        {opt.isAiGenerated ? 'AI Suggested Path' : `Manual Option ${i+1}`}
                                                    </span>
                                                    {options.length > 1 && (
                                                        <button onClick={() => removeOption(opt.id)} className="text-red-400 hover:text-red-600">
                                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        </button>
                                                    )}
                                                </div>
                                                <input
                                                    className="w-full p-4 bg-white border border-gray-100 rounded-2xl font-bold outline-none focus:border-indigo-600"
                                                    placeholder="Option Name"
                                                    value={opt.name} onChange={e => updateOption(opt.id, 'name', e.target.value)}
                                                />
                                                <textarea
                                                    className="w-full p-4 bg-white border border-gray-100 rounded-2xl text-sm outline-none focus:border-indigo-600 h-24"
                                                    placeholder="Rationale..."
                                                    value={opt.description} onChange={e => updateOption(opt.id, 'description', e.target.value)}
                                                ></textarea>
                                            </div>
                                        ))}
                                    </div>

                                    <div className="flex gap-4">
                                        <button onClick={() => setStep(1)} className="px-10 py-5 border-2 border-gray-100 rounded-2xl font-black text-gray-400 hover:bg-gray-50 transition">Back</button>
                                        <button
                                            onClick={handleSubmit}
                                            disabled={isSubmitting}
                                            className="flex-1 bg-indigo-600 text-white py-5 rounded-2xl font-black text-xl shadow-2xl disabled:opacity-50"
                                        >
                                            {isSubmitting ? 'Recording...' : 'Finalize Strategic Logic'}
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* AI SIDEBAR */}
                        <aside className="space-y-6">
                            <div className="bg-indigo-600 p-10 rounded-[40px] text-white shadow-2xl shadow-indigo-100 sticky top-32">
                                <h3 className="font-black text-[10px] uppercase tracking-[0.2em] mb-10 flex items-center gap-3">
                                    <div className="w-6 h-6 bg-white/10 rounded flex items-center justify-center text-xs">🧠</div>
                                    Strategy Engine
                                </h3>
                                
                                {isAiLoading ? (
                                    <div className="space-y-6 animate-pulse">
                                        <div className="h-20 bg-white/10 rounded-2xl"></div>
                                        <div className="h-20 bg-white/10 rounded-2xl"></div>
                                    </div>
                                ) : aiSuggestions.length > 0 ? (
                                    <div className="space-y-4">
                                        <p className="text-[10px] font-black text-indigo-200 uppercase tracking-widest mb-4">Click to adopt suggestion:</p>
                                        {aiSuggestions.map((s, i) => (
                                            <button
                                                key={i}
                                                onClick={() => addOption({ name: s.name, description: s.description || s.reason, isAiGenerated: true })}
                                                className="w-full p-6 bg-white/5 hover:bg-white/10 border border-white/5 rounded-3xl text-left transition-all group"
                                            >
                                                <div className="font-bold text-sm mb-1 group-hover:text-white transition">+ {s.name}</div>
                                                <div className="text-[10px] text-indigo-200 font-medium">Confidence: {s.confidence || 85}%</div>
                                            </button>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-sm text-indigo-100/60 font-medium leading-relaxed italic">
                                        Define your context to activate AI strategic intelligence.
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
