<?php
require_once __DIR__ . '/lib/auth.php';
requireOrgAccess();
?>
<!DOCTYPE html>
<html>
<head>
    <title>New Strategic Decision | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
</head>
<body class="bg-gray-50 p-8">
    <div id="decision-creator-root"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;

        function DecisionCreator() {
            const [title, setTitle] = useState('');
            const [problem, setProblem] = useState('');
            const [options, setOptions] = useState([{ name: '', pros: [], cons: [] }]);
            const [aiInsights, setAiInsights] = useState(null);

            // AUTO-ANALYSIS: Runs as the user types
            useEffect(() => {
                const timer = setTimeout(async () => {
                    if (title.length > 5) {
                        const res = await fetch('/api/ai-strategy.php', {
                            method: 'POST',
                            body: JSON.stringify({ title, problem_statement: problem })
                        });
                        const data = await res.json();
                        setAiInsights(data);
                    }
                }, 1000);
                return () => clearTimeout(timer);
            }, [title, problem]);

            const addAiOption = (opt) => {
                setOptions([...options, { name: opt.name, description: opt.description, pros: opt.pros, cons: opt.cons }]);
            };

            return (
                <div className="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div className="lg:col-span-2 space-y-6">
                        <h1 className="text-3xl font-black">Strategic Decision</h1>
                        <input className="w-full p-4 text-xl border-2 rounded-2xl" placeholder="Decision Title..." value={title} onChange={e => setTitle(e.target.value)} />
                        <textarea className="w-full p-4 border-2 rounded-2xl" placeholder="Problem Statement..." value={problem} onChange={e => setProblem(e.target.value)} />
                        
                        <div className="space-y-4">
                            <h2 className="text-xl font-bold">Options</h2>
                            {options.map((opt, i) => (
                                <div key={i} className="p-4 bg-white border rounded-xl shadow-sm">
                                    <input className="font-bold w-full outline-none" value={opt.name} onChange={e => {
                                        const newOpts = [...options];
                                        newOpts[i].name = e.target.value;
                                        setOptions(newOpts);
                                    }} placeholder="Option name..." />
                                </div>
                            ))}
                            <button onClick={() => setOptions([...options, {name: ''}])} className="text-indigo-600 font-bold">+ Add Manual Option</button>
                        </div>
                    </div>

                    {/* SIDEBAR: AI Recommendations & Similar Decisions */}
                    <div className="space-y-6">
                        {aiInsights && (
                            <div className="bg-indigo-600 text-white p-6 rounded-3xl shadow-xl">
                                <h3 className="font-bold flex items-center gap-2">🤖 AI Suggestions</h3>
                                <p className="text-sm text-indigo-100 mb-4">Click to inject into your decision:</p>
                                {aiInsights.external.suggested_options.map((opt, i) => (
                                    <button key={i} onClick={() => addAiOption(opt.option)} className="w-full mb-2 p-3 bg-white/10 hover:bg-white/20 rounded-xl text-left text-sm transition">
                                        + {opt.option.name} ({Math.round(opt.base_success_rate * 100)}% Success)
                                    </button>
                                ))}
                            </div>
                        )}
                        
                        {aiInsights?.internal?.has_recommendations && (
                            <div className="bg-white p-6 rounded-3xl border shadow-sm">
                                <h3 className="font-bold text-gray-400 text-xs uppercase mb-4 tracking-widest">Internal History</h3>
                                <p className="text-sm mb-4">We found {aiInsights.internal.similar_count} similar decisions in your vault.</p>
                                {aiInsights.internal.similar_decisions.map((sd, i) => (
                                    <div key={i} className="text-xs p-3 bg-gray-50 rounded-lg mb-2">
                                        <div className="font-bold">"{sd.title}"</div>
                                        <div className="text-indigo-600">Rating: {sd.outcome_rating}/5</div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            );
        }

        const root = ReactDOM.createRoot(document.getElementById('decision-creator-root'));
        root.render(<DecisionCreator />);
    </script>

<div id="ai-risk-panel" class="hidden">
    <div class="bg-red-50 border-2 border-red-500 rounded-3xl p-6 shadow-xl">
        <div class="flex items-center gap-3 mb-4">
            <span class="text-3xl">⚠️</span>
            <h3 class="text-xl font-black text-red-700 uppercase tracking-tighter">Brutal AI Warning</h3>
        </div>
        
        <p id="risk-summary" class="font-bold text-red-600 mb-6 text-lg leading-tight">
            </p>

        <div class="space-y-4">
            <h4 class="text-xs font-black text-red-400 uppercase">Historical Autopsy</h4>
            <ul id="risk-list" class="text-sm text-red-800 space-y-2">
                </ul>
        </div>
        
        <div class="mt-6 pt-6 border-t border-red-200">
            <p class="text-xs text-red-400 italic">"Strategy is about what NOT to do. This AI thinks you are making a mistake."</p>
        </div>
    </div>
</div>

<script>
window.updateAIWarnings = async function(data) {
    const res = await fetch(`/api/proactive-intel.php?q=${encodeURIComponent(data.title)}&p=${encodeURIComponent(data.problem_statement)}`);
    const intel = await res.json();
    
    const panel = document.getElementById('ai-risk-panel');
    const summary = document.getElementById('risk-summary');
    const list = document.getElementById('risk-list');
    
    if (intel.external_risks.length > 0 || intel.internal_mistakes.length > 0) {
        panel.classList.remove('hidden');
        summary.innerText = intel.aggressive_warning;
        
        list.innerHTML = intel.external_risks.map(r =>
            `<li><strong>${r.company_name}:</strong> failed because ${r.failure_reason}</li>`
        ).join('');
    } else {
        panel.classList.add('hidden');
    }
};
</script>
</body>
</html>
