<?php
/**
 * File Path: create-decision.php
 * Description: Hybrid manual + AI decision flow as requested.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$user = getCurrentUser();
$orgId = $_SESSION['current_org_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>New Strategic Decision | <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div id="root"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;

        function App() {
            const [step, setStep] = useState(1); // 1: Context, 2: Options
            const [title, setTitle] = useState('');
            const [problem, setProblem] = useState('');
            const [options, setOptions] = useState([
                { id: 1, name: '', description: '', pros: '', cons: '' }
            ]);
            const [aiSuggestions, setAiSuggestions] = useState([]);
            const [isAiLoading, setIsAiLoading] = useState(false);

            // Fetch AI Suggestions based on Title/Problem
            const fetchSuggestions = async () => {
                if (!title) return;
                setIsAiLoading(true);
                try {
                    const res = await fetch('/api/ai-chat.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            action: 'generate_options',
                            title: title,
                            problem: problem
                        })
                    });
                    const data = await res.json();
                    setAiSuggestions(data.suggestions || []);
                } catch (e) { console.error(e); }
                setIsAiLoading(false);
            };

            const addOption = (initialData = {}) => {
                setOptions([...options, {
                    id: Date.now(),
                    name: initialData.name || '',
                    description: initialData.description || '',
                    pros: initialData.pros || '',
                    cons: initialData.cons || ''
                }]);
            };

            const updateOption = (id, field, value) => {
                setOptions(options.map(o => o.id === id ? { ...o, [field]: value } : o));
            };

            return (
                <div className="max-w-6xl mx-auto py-12 px-6">
                    <header class="mb-12">
                        <h1 class="text-4xl font-black text-gray-900">New Strategic Decision</h1>
                        <p class="text-gray-500">Document the path. Use AI to avoid common failure modes.</p>
                    </header>

                    <div className="grid lg:grid-cols-3 gap-12">
                        <div className="lg:col-span-2 space-y-8">
                            {/* Step 1: CONTEXT */}
                            <div className={`bg-white p-10 rounded-3xl border shadow-sm transition-opacity ${step !== 1 ? 'opacity-40 pointer-events-none' : ''}`}>
                                <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">1. Strategic Context</h2>
                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Decision Title</label>
                                        <input
                                            class="w-full p-4 border-2 rounded-2xl outline-indigo-600 bg-gray-50 focus:bg-white transition-all"
                                            placeholder="e.g. Hire Head of Growth"
                                            value={title} onChange={e => setTitle(e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">The Core Problem</label>
                                        <textarea
                                            class="w-full p-4 border-2 rounded-2xl outline-indigo-600 bg-gray-50 focus:bg-white transition-all h-32"
                                            placeholder="What is the root cause of this decision?"
                                            value={problem} onChange={e => setProblem(e.target.value)}
                                        ></textarea>
                                    </div>
                                    <button onClick={() => { setStep(2); fetchSuggestions(); }} class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black shadow-xl shadow-indigo-100">
                                        Next: Define Options →
                                    </button>
                                </div>
                            </div>

                            {/* Step 2: OPTIONS */}
                            <div className={`bg-white p-10 rounded-3xl border shadow-sm transition-opacity ${step !== 2 ? 'opacity-40 pointer-events-none' : ''}`}>
                                <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">2. Define Options</h2>
                                <div class="space-y-6 mb-8">
                                    {options.map((opt, i) => (
                                        <div key={opt.id} class="p-6 bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl space-y-4">
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs font-black text-gray-400 uppercase">Manual Option {i+1}</span>
                                                {options.length > 1 && (
                                                    <button onClick={() => setOptions(options.filter(o => o.id !== opt.id))} class="text-red-500 text-xs font-bold">Remove</button>
                                                )}
                                            </div>
                                            <input
                                                class="w-full p-3 bg-white border rounded-xl font-bold outline-none"
                                                placeholder="Option Name"
                                                value={opt.name} onChange={e => updateOption(opt.id, 'name', e.target.value)}
                                            />
                                            <textarea
                                                class="w-full p-3 bg-white border rounded-xl text-sm outline-none"
                                                placeholder="Specific Details / Rationale"
                                                value={opt.description} onChange={e => updateOption(opt.id, 'description', e.target.value)}
                                            ></textarea>
                                        </div>
                                    ))}
                                    <button onClick={() => addOption()} class="w-full border-2 border-dashed border-indigo-200 p-4 rounded-2xl text-indigo-600 font-bold hover:bg-indigo-50 transition-colors">
                                        + Add Your Own Option
                                    </button>
                                </div>

                                <div class="flex gap-4">
                                    <button onClick={() => setStep(1)} class="px-8 py-4 border-2 rounded-2xl font-black text-gray-400">Back</button>
                                    <button class="flex-1 bg-indigo-600 text-white py-4 rounded-2xl font-black text-xl shadow-xl shadow-indigo-100">Document Strategy</button>
                                </div>
                            </div>
                        </div>

                        {/* AI SIDEBAR */}
                        <aside className="space-y-6">
                            <div className="bg-indigo-600 p-8 rounded-3xl text-white shadow-xl shadow-indigo-200">
                                <h3 className="font-black text-xs uppercase tracking-widest mb-6 flex items-center gap-2">
                                    <span class="text-xl">🧠</span> AI Strategy Engine
                                </h3>
                                
                                {isAiLoading ? (
                                    <div class="space-y-4 animate-pulse">
                                        <div class="h-12 bg-white/10 rounded-xl"></div>
                                        <div class="h-12 bg-white/10 rounded-xl"></div>
                                    </div>
                                ) : aiSuggestions.length > 0 ? (
                                    <div class="space-y-3">
                                        <p class="text-xs text-indigo-100 mb-4 font-medium italic">Based on 2,000+ failures, consider these paths:</p>
                                        {aiSuggestions.map((s, i) => (
                                            <button
                                                key={i}
                                                onClick={() => addOption({ name: s.name, description: s.reason })}
                                                class="w-full p-4 bg-white/10 hover:bg-white/20 border border-white/10 rounded-2xl text-left text-sm transition-all"
                                            >
                                                <div class="font-bold">+ {s.name}</div>
                                                <div class="text-[10px] opacity-60 mt-1">Confidence Score: {s.confidence}%</div>
                                            </button>
                                        ))}
                                    </div>
                                ) : (
                                    <div class="text-xs text-indigo-100 opacity-60 italic">Define your context to activate AI Intelligence.</div>
                                )}
                            </div>

                            <div class="p-8 bg-white border border-gray-100 rounded-3xl">
                                <h4 class="font-black text-gray-400 uppercase text-[10px] tracking-widest mb-4">Why Document?</h4>
                                <p class="text-xs text-gray-500 leading-relaxed">By documenting your manual options alongside AI suggestions, you create a permanent audit trail of your team's logic before the outcome is known.</p>
                            </div>
                        </aside>
                    </div>
                </div>
            );
        }

        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<App />);
    </script>
</body>
</html>
