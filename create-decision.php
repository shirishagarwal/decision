<?php
/**
 * File Path: create-decision.php
 * Description: Decision Creator that allows for BOTH AI-generated and Manual option entry.
 */
require_once __DIR__ . '/config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>New Strategic Decision | DecisionVault</title>
    <script src="[https://cdn.tailwindcss.com](https://cdn.tailwindcss.com)"></script>
    <script src="[https://unpkg.com/react@18/umd/react.production.min.js](https://unpkg.com/react@18/umd/react.production.min.js)"></script>
    <script src="[https://unpkg.com/react-dom@18/umd/react-dom.production.min.js](https://unpkg.com/react-dom@18/umd/react-dom.production.min.js)"></script>
    <script src="[https://unpkg.com/@babel/standalone/babel.min.js](https://unpkg.com/@babel/standalone/babel.min.js)"></script>
</head>
<body class="bg-gray-50 p-8">
    <div id="root"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;

        function App() {
            const [title, setTitle] = useState('');
            const [problem, setProblem] = useState('');
            const [manualOptions, setManualOptions] = useState(['']);
            const [aiSuggestions, setAiSuggestions] = useState([]);
            const [isLoading, setIsLoading] = useState(false);

            // Fetch AI Suggestions as the user types [PROACTIVE INTELLIGENCE]
            useEffect(() => {
                const timer = setTimeout(async () => {
                    if (title.length > 5) {
                        setIsLoading(true);
                        try {
                            // Call your proactive intel API
                            const res = await fetch(`/api/ai-strategy.php`, {
                                method: 'POST',
                                body: JSON.stringify({ title, problem_statement: problem })
                            });
                            const data = await res.json();
                            setAiSuggestions(data.external?.suggested_options || []);
                        } catch (e) {
                            console.error("AI Fetch Failed");
                        }
                        setIsLoading(false);
                    }
                }, 1000);
                return () => clearTimeout(timer);
            }, [title, problem]);

            const addManualOption = () => setManualOptions([...manualOptions, '']);
            
            const updateManualOption = (index, val) => {
                const newOpts = [...manualOptions];
                newOpts[index] = val;
                setManualOptions(newOpts);
            };

            const adoptAiOption = (optName) => {
                setManualOptions([...manualOptions, optName]);
            };

            return (
                <div className="max-w-6xl mx-auto grid lg:grid-cols-3 gap-12">
                    <div className="lg:col-span-2">
                        <header class="mb-10">
                            <h1 className="text-4xl font-black text-gray-900">New Strategic Decision</h1>
                            <p class="text-gray-500">Document the logic. Avoid the failure patterns.</p>
                        </header>

                        <div className="space-y-8 bg-white p-10 rounded-3xl border shadow-sm">
                            <section>
                                <label className="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Decision Context</label>
                                <input
                                    className="w-full p-4 text-xl border-2 rounded-2xl outline-indigo-600 mb-4"
                                    placeholder="e.g. Hiring VP of Sales"
                                    value={title}
                                    onChange={e => setTitle(e.target.value)}
                                />
                                <textarea
                                    className="w-full p-4 border-2 rounded-2xl outline-indigo-600 h-32"
                                    placeholder="Describe the core problem this decision solves..."
                                    value={problem}
                                    onChange={e => setProblem(e.target.value)}
                                ></textarea>
                            </section>

                            <section>
                                <div className="flex justify-between items-end mb-4">
                                    <label className="text-xs font-black text-gray-400 uppercase tracking-widest">Options Under Consideration</label>
                                    <button onClick={addManualOption} className="text-indigo-600 font-bold text-sm">+ Add Custom Option</button>
                                </div>
                                <div className="space-y-3">
                                    {manualOptions.map((opt, i) => (
                                        <input
                                            key={i}
                                            className="w-full p-4 bg-gray-50 border-2 border-transparent focus:border-indigo-600 rounded-2xl transition-all"
                                            placeholder={`Option ${i+1}`}
                                            value={opt}
                                            onChange={e => updateManualOption(i, e.target.value)}
                                        />
                                    ))}
                                </div>
                            </section>

                            <button className="w-full bg-indigo-600 text-white py-5 rounded-2xl font-black text-xl shadow-xl shadow-indigo-100">
                                Document Strategic Logic
                            </button>
                        </div>
                    </div>

                    <aside className="space-y-6">
                        <h3 className="font-black text-gray-400 uppercase text-xs tracking-widest">AI Intelligence Moat</h3>
                        
                        {isLoading && (
                            <div className="p-6 bg-white border rounded-3xl animate-pulse">
                                <div className="h-4 bg-gray-100 rounded w-3/4 mb-4"></div>
                                <div className="h-4 bg-gray-100 rounded w-1/2"></div>
                            </div>
                        )}

                        {!isLoading && aiSuggestions.length > 0 && (
                            <div className="bg-indigo-600 p-6 rounded-3xl text-white shadow-xl">
                                <h4 className="font-bold mb-4 flex items-center gap-2">🧠 AI Suggested Options</h4>
                                <div className="space-y-3">
                                    {aiSuggestions.map((s, i) => (
                                        <button
                                            key={i}
                                            onClick={() => adoptAiOption(s.option.name)}
                                            className="w-full p-3 bg-white/10 hover:bg-white/20 rounded-xl text-left text-sm transition"
                                        >
                                            + {s.option.name}
                                        </button>
                                    ))}
                                </div>
                                <p className="text-[10px] mt-4 opacity-60">Suggestions based on 2,000+ historical failures.</p>
                            </div>
                        )}

                        <div className="p-6 bg-amber-50 border border-amber-200 rounded-3xl text-amber-800">
                            <h4 className="font-bold text-sm mb-2">💡 Pro Tip</h4>
                            <p className="text-xs leading-relaxed">Most Series A startups fail because they hire for scale before Product-Market Fit. If you're hiring, ensure PMF is validated first.</p>
                        </div>
                    </aside>
                </div>
            );
        }

        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<App />);
    </script>
</body>
</html>
