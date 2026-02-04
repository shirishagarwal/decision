<?php
/**
 * File Path: create-decision.php
 * Description: Hybrid manual + AI decision flow. Allows manual entry, AI suggestions,
 * and identifies similar past decisions from the organization's history.
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strategic Creator | <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; }
        .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body>
    <div id="root"></div>

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
            const [similarDecisions, setSimilarDecisions] = useState([]);
            const [isAiLoading, setIsAiLoading] = useState(false);
            const [isSubmitting, setIsSubmitting] = useState(false);

            // Auto-analysis logic for similar decisions and AI options
            const runStrategicAnalysis = useCallback(async () => {
                if (title.length < 5) return;
                
                setIsAiLoading(true);
                try {
                    // This endpoint combines similarity search and LLM generation
                    const response = await fetch('/api/ai-strategy.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            title,
                            problem_statement: problem
                        })
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        setAiSuggestions(data.external?.suggested_options || []);
                        setSimilarDecisions(data.internal?.similar_decisions || []);
                    }
                } catch (error) {
                    console.error("Analysis failed:", error);
                } finally {
                    setIsAiLoading(false);
                }
            }, [title, problem]);

            // Debounced analysis trigger
            useEffect(() => {
                const handler = setTimeout(() => {
                    if (step === 2 || title.length > 10) {
                        runStrategicAnalysis();
                    }
                }, 1000);
                return () => clearTimeout(handler);
            }, [title, problem, step, runStrategicAnalysis]);

            const addOption = (data = {}) => {
                setOptions(prev => [...prev, {
                    id: Date.now() + Math.random(),
                    name: data.name || '',
                    description: data.description || '',
                    isAiGenerated: !!data.isAiGenerated
                }]);
            };

            const updateOption = (id, field, value) => {
                setOptions(prev => prev.map(opt => opt.id === id ? { ...opt, [field]: value } : opt));
            };

            const removeOption = (id) => {
                if (options.length > 1) {
                    setOptions(prev => prev.filter(opt => opt.id !== id));
                }
            };

            const handleSubmit = async () => {
                if (!title) return alert("Please provide a decision title.");
                const validOptions = options.filter(o => o.name.trim() !== '');
                if (validOptions.length === 0) return alert("Please add at least one option.");

                setIsSubmitting(true);
                try {
                    const res = await fetch('/api/create-decision.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ title, problem, options: validOptions })
                    });
                    const data = await res.json();
                    if (data.success) {
                        window.location.href = `/decision.php?id=${data.decision_id}`;
                    } else {
                        alert(data.error || "Submission failed");
                    }
                } catch (e) {
                    alert("Submission failed. Connection error.");
                } finally {
                    setIsSubmitting(false);
                }
            };

            return (
                <div className="min-h-screen">
                    {/* Header */}
                    <nav className="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-50">
                        <div className="max-w-7xl mx-auto flex justify-between items-center">
                            <div className="flex items-center gap-4">
                                <span className="text-xl font-black tracking-tighter text-gray-900">DECISION<span className="text-indigo-600">VAULT</span></span>
                            </div>
                            <div className="flex items-center gap-6">
                                <div className="hidden md:flex items-center gap-2">
                                    <div className={`h-2 w-12 rounded-full ${step >= 1 ? 'bg-indigo-600' : 'bg-gray-200'}`}></div>
                                    <div className={`h-2 w-12 rounded-full ${step >= 2 ? 'bg-indigo-600' : 'bg-gray-200'}`}></div>
                                </div>
                                <a href="/dashboard.php" className="text-sm font-bold text-gray-400 hover:text-gray-600 uppercase tracking-widest">Cancel</a>
                            </div>
                        </div>
                    </nav>

                    <main className="max-w-7xl mx-auto py-12 px-6">
                        <div className="grid lg:grid-cols-3 gap-12">
                            {/* Main Content Area */}
                            <div className="lg:col-span-2 space-y-8">
                                
                                {step === 1 ? (
                                    <div className="bg-white p-10 rounded-[40px] border border-gray-100 card-shadow animate-in fade-in slide-in-from-bottom-4 duration-500">
                                        <h2 className="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] mb-8">01 &bull; Context & Problem</h2>
                                        <div className="space-y-8">
                                            <div>
                                                <label className="block text-sm font-black text-gray-700 mb-2">What is the decision to be made?</label>
                                                <input
                                                    className="w-full p-5 border-2 border-gray-100 rounded-2xl outline-none focus:border-indigo-600 bg-gray-50/50 focus:bg-white transition-all text-xl font-bold placeholder:font-normal"
                                                    placeholder="e.g. Hire a VP of Engineering vs. Fractional CTO"
                                                    value={title} onChange={e => setTitle(e.target.value)}
                                                />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-black text-gray-700 mb-2">Why is this decision being made now?</label>
                                                <textarea
                                                    className="w-full p-5 border-2 border-gray-100 rounded-2xl outline-none focus:border-indigo-600 bg-gray-50/50 focus:bg-white transition-all h-40 font-medium"
                                                    placeholder="Describe the current pain points, goals, and constraints..."
                                                    value={problem} onChange={e => setProblem(e.target.value)}
                                                ></textarea>
                                            </div>
                                            <button
                                                onClick={() => setStep(2)}
                                                disabled={title.length < 5}
                                                className="bg-indigo-600 text-white px-10 py-5 rounded-2xl font-black shadow-xl shadow-indigo-100 hover:bg-indigo-700 disabled:opacity-50 transition-all flex items-center gap-2"
                                            >
                                                Next: Evaluate Options
                                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="bg-white p-10 rounded-[40px] border border-gray-100 card-shadow animate-in fade-in slide-in-from-bottom-4 duration-500">
                                        <div className="flex justify-between items-center mb-10">
                                            <h2 className="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em]">02 &bull; Define Strategic Options</h2>
                                            <button
                                                onClick={() => addOption()}
                                                className="text-[10px] font-black bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl uppercase tracking-widest hover:bg-indigo-100 transition"
                                            >
                                                + Add Manual Option
                                            </button>
                                        </div>
                                        
                                        <div className="space-y-6 mb-12">
                                            {options.map((opt, i) => (
                                                <div key={opt.id} className={`p-8 border rounded-3xl space-y-4 group relative transition-all ${opt.isAiGenerated ? 'bg-indigo-50/30 border-indigo-100' : 'bg-gray-50/50 border-gray-100'}`}>
                                                    <div className="flex justify-between items-center">
                                                        <span className="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                                            {opt.isAiGenerated ? 'AI Suggested Option' : `Manual Option ${i+1}`}
                                                        </span>
                                                        <button onClick={() => removeOption(opt.id)} className="text-gray-300 hover:text-red-500 transition-colors">
                                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        </button>
                                                    </div>
                                                    <input
                                                        className="w-full p-4 bg-white border border-gray-100 rounded-2xl font-bold outline-none focus:border-indigo-600 shadow-sm"
                                                        placeholder="Name this path..."
                                                        value={opt.name} onChange={e => updateOption(opt.id, 'name', e.target.value)}
                                                    />
                                                    <textarea
                                                        className="w-full p-4 bg-white border border-gray-100 rounded-2xl text-sm outline-none focus:border-indigo-600 font-medium h-24 shadow-sm"
                                                        placeholder="Rationale, pros, and expected impact..."
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
                                                className="flex-1 bg-indigo-600 text-white py-5 rounded-2xl font-black text-xl shadow-2xl shadow-indigo-100 hover:bg-indigo-700 disabled:opacity-50"
                                            >
                                                {isSubmitting ? 'Recording Strategic Logic...' : 'Finalize & Review'}
                                            </button>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Sidebar - Intelligence Moat */}
                            <aside className="space-y-6">
                                {/* AI Suggested Options */}
                                <div className="bg-indigo-600 p-8 rounded-[40px] text-white shadow-2xl shadow-indigo-200 sticky top-32">
                                    <h3 className="font-black text-[10px] uppercase tracking-[0.2em] mb-8 flex items-center gap-3">
                                        <div className="w-6 h-6 bg-white/20 rounded flex items-center justify-center text-xs">🤖</div>
                                        AI Strategy Engine
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
                                                    className="w-full p-5 bg-white/5 hover:bg-white/10 border border-white/10 rounded-3xl text-left transition-all group"
                                                >
                                                    <div className="font-bold text-sm mb-1 group-hover:text-white transition">+ {s.name}</div>
                                                    <div className="text-[10px] text-indigo-200 font-medium">Confidence: {s.confidence || 85}%</div>
                                                </button>
                                            ))}
                                        </div>
                                    ) : (
                                        <div className="text-sm text-indigo-100/60 font-medium leading-relaxed italic">
                                            Type at least 5 characters in the decision title to activate strategic intelligence from 2,000+ failures.
                                        </div>
                                    )}
                                </div>

                                {/* Internal History Analysis */}
                                {similarDecisions.length > 0 && (
                                    <div className="bg-white p-8 rounded-[40px] border border-gray-100 card-shadow">
                                        <h3 className="font-black text-[10px] text-gray-400 uppercase tracking-[0.2em] mb-6">Internal Similar Decisions</h3>
                                        <div className="space-y-4">
                                            {similarDecisions.map((sd, i) => (
                                                <div key={i} className="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                                    <div className="text-sm font-bold text-gray-900 mb-1">{sd.title}</div>
                                                    <div className="flex items-center gap-2">
                                                        <span className={`text-[10px] font-black uppercase px-2 py-0.5 rounded ${
                                                            sd.status === 'Implemented' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'
                                                        }`}>
                                                            {sd.status}
                                                        </span>
                                                        <span className="text-[10px] text-gray-400">{new Date(sd.created_at).toLocaleDateString()}</span>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                <div className="p-8 bg-amber-50 border border-amber-200 rounded-[40px] text-amber-900">
                                    <h4 className="font-black text-[10px] uppercase tracking-widest mb-4">Strategic Moat Tip</h4>
                                    <p className="text-xs leading-relaxed font-medium">
                                        By documenting manual options alongside AI suggestions, you prevent "Hindsight Bias" and build a higher Decision IQ for the whole team.
                                    </p>
                                </div>
                            </aside>
                        </div>
                    </main>
                </div>
            );
        }

        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<App />);
    </script>
</body>
</html>
