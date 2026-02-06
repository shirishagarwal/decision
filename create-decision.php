<?php
/**
 * File Path: create-decision.php
 * Description: Upgraded Strategy Interface with Killer AI features.
 * Added: Risk Quantification, Counterfactuals, and Stakeholder Workflows.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$user = getCurrentUser();
$orgId = $_SESSION['current_org_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strategic Architecture | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #fcfcfd; }
        .glass-card { background: white; border: 1px solid #f1f3f5; box-shadow: 0 10px 30px -5px rgba(0,0,0,0.03); border-radius: 2.5rem; }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/header.php'; ?>
    <div id="root" class="flex-grow"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;

        const Icon = ({ name, size = 20, className = "" }) => {
            useEffect(() => { if (window.lucide) window.lucide.createIcons(); }, [name]);
            return <i data-lucide={name} style={{ width: size, height: size }} className={className}></i>;
        };

        function App() {
            const [step, setStep] = useState(1);
            const [title, setTitle] = useState('');
            const [problem, setProblem] = useState('');
            const [isAnalyzing, setIsAnalyzing] = useState(false);
            const [aiPayload, setAiPayload] = useState({ gaps: [], options: [], counterfactual: null, benchmark: '' });
            const [contextData, setContextData] = useState({});
            const [connectedServices, setConnectedServices] = useState(['stripe']);

            const fetchIntelligence = async (forceOptions = false) => {
                if (title.length < 5) return;
                setIsAnalyzing(true);
                try {
                    const res = await fetch('/api/ai-strategy.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ title, problem_statement: problem, context_data: contextData, active_connectors: connectedServices, force_options: forceOptions })
                    });
                    const data = await res.json();
                    setAiPayload(data);
                    if (forceOptions || (step === 2 && data.gaps.length === 0)) setStep(3);
                    else if (step === 1) setStep(2);
                } catch (e) { console.error(e); }
                finally { setIsAnalyzing(false); }
            };

            return (
                <main className="max-w-7xl mx-auto py-12 px-6">
                    <div className="flex justify-between items-end mb-12">
                        <div>
                            <div className="flex items-center gap-2 mb-4">
                                <Icon name="zap" className="text-indigo-600" />
                                <span className="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-600">Active Intelligence Active</span>
                            </div>
                            <h1 className="text-5xl font-black text-slate-900 tracking-tighter">Strategic Architecture</h1>
                        </div>
                        {aiPayload.benchmark && (
                            <div className="bg-indigo-50 border border-indigo-100 p-4 rounded-2xl">
                                <div className="text-[8px] font-black text-indigo-400 uppercase mb-1">Industry Benchmark</div>
                                <div className="text-xs font-bold text-indigo-900">{aiPayload.benchmark}</div>
                            </div>
                        )}
                    </div>

                    <div className="grid lg:grid-cols-3 gap-12">
                        <div className="lg:col-span-2 space-y-8">
                            {/* STEP 1: CONTEXT */}
                            {step === 1 && (
                                <div className="glass-card p-10 animate-in">
                                    <h2 className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8">01 • Problem Context</h2>
                                    <div className="space-y-8">
                                        <input className="w-full p-6 bg-slate-50 rounded-3xl text-2xl font-black outline-none focus:bg-white border-2 border-transparent focus:border-indigo-600 transition-all" placeholder="Strategic Decision Title..." value={title} onChange={e => setTitle(e.target.value)} />
                                        <textarea className="w-full p-6 bg-slate-50 rounded-3xl h-40 font-medium outline-none focus:bg-white border-2 border-transparent focus:border-indigo-600 transition-all" placeholder="Problem Statement..." value={problem} onChange={e => setProblem(e.target.value)} />
                                        <button onClick={() => fetchIntelligence()} disabled={!title || isAnalyzing} className="w-full bg-slate-900 text-white py-6 rounded-2xl font-black text-lg shadow-2xl flex items-center justify-center gap-3">
                                            {isAnalyzing ? <Icon name="loader-2" className="animate-spin" /> : <Icon name="cpu" />}
                                            Weaponize Logic
                                        </button>
                                    </div>
                                </div>
                            )}

                            {/* STEP 2: INTERVIEW & GAPS */}
                            {step === 2 && (
                                <div className="glass-card p-10 animate-in">
                                    <div className="flex justify-between items-center mb-8">
                                        <h2 className="text-[10px] font-black text-indigo-600 uppercase tracking-widest">02 • Intelligence Gaps</h2>
                                        <button onClick={() => fetchIntelligence(true)} className="text-[10px] font-black text-slate-300 hover:text-indigo-600">Skip to Speculative Analysis →</button>
                                    </div>
                                    <div className="space-y-4">
                                        {aiPayload.gaps.map(gap => (
                                            <div key={gap.key} className="p-6 bg-slate-50 rounded-3xl border border-slate-100 group hover:border-indigo-200 transition-all">
                                                <div className="flex justify-between mb-4">
                                                    <div className="font-black text-slate-900">{gap.label}</div>
                                                    <div className="text-[8px] font-black text-indigo-500 uppercase tracking-widest bg-indigo-50 px-2 py-1 rounded-full">Missing Vector</div>
                                                </div>
                                                <input className="w-full p-4 bg-white rounded-2xl border border-slate-200 outline-none focus:border-indigo-600 font-bold" placeholder={gap.reason} onChange={e => setContextData({...contextData, [gap.key]: e.target.value})} />
                                            </div>
                                        ))}
                                        <button onClick={() => fetchIntelligence()} className="w-full bg-indigo-600 text-white py-6 rounded-2xl font-black text-lg shadow-xl mt-6">Refresh Strategic Modeling</button>
                                    </div>
                                </div>
                            )}

                            {/* STEP 3: THE KILLER FEATURES (Strategy) */}
                            {step === 3 && (
                                <div className="space-y-8 animate-in">
                                    <h2 className="text-[10px] font-black text-slate-400 uppercase tracking-widest">03 • High-Fidelity Strategic Paths</h2>
                                    
                                    {/* Counterfactual Alert */}
                                    {aiPayload.counterfactual && (
                                        <div className="p-8 bg-red-50 border border-red-100 rounded-[2.5rem] relative overflow-hidden">
                                            <div className="relative z-10">
                                                <div className="text-[10px] font-black text-red-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                                                    <Icon name="alert-triangle" size={12} /> Counterfactual Analysis: The Cost of Inaction
                                                </div>
                                                <p className="text-red-900 font-bold leading-relaxed">{aiPayload.counterfactual}</p>
                                            </div>
                                            <div className="absolute top-0 right-0 w-24 h-24 bg-red-500/5 blur-2xl rounded-full"></div>
                                        </div>
                                    )}

                                    <div className="space-y-6">
                                        {aiPayload.options.map((opt, i) => (
                                            <div key={i} className="glass-card p-10 hover:border-indigo-300 transition-all group cursor-pointer">
                                                <div className="flex justify-between items-start mb-6">
                                                    <h3 className="text-3xl font-black text-slate-900 tracking-tighter group-hover:text-indigo-600 transition">{opt.name}</h3>
                                                    <div className="text-right">
                                                        <div className="text-[8px] font-black text-emerald-500 uppercase">Accuracy Probability</div>
                                                        <div className="text-xl font-black text-slate-900">{opt.confidence_interval}</div>
                                                    </div>
                                                </div>
                                                <p className="text-slate-500 font-medium mb-8 leading-relaxed">{opt.description}</p>
                                                
                                                <div className="grid grid-cols-3 gap-4 border-t border-slate-50 pt-8">
                                                    <div className="p-4 bg-slate-50 rounded-2xl text-center">
                                                        <div className="text-[8px] font-black text-slate-400 uppercase mb-1">Expected Value</div>
                                                        <div className="text-sm font-black text-indigo-600">{opt.expected_value}</div>
                                                    </div>
                                                    <div className="p-4 bg-slate-50 rounded-2xl text-center">
                                                        <div className="text-[8px] font-black text-slate-400 uppercase mb-1">Risk Factor</div>
                                                        <div className="text-sm font-black text-red-500">{opt.risk_score}/10</div>
                                                    </div>
                                                    <div className="p-4 bg-slate-50 rounded-2xl text-center">
                                                        <div className="text-[8px] font-black text-slate-400 uppercase mb-1">Pattern Match</div>
                                                        <div className="text-[10px] font-black text-slate-900 uppercase tracking-tighter truncate">{opt.pattern_match}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    <div className="flex gap-4">
                                        <button onClick={() => setStep(2)} className="px-10 py-5 bg-white border border-slate-100 rounded-2xl font-black text-slate-400">Back</button>
                                        <button className="flex-1 bg-indigo-600 text-white py-5 rounded-2xl font-black text-xl shadow-2xl hover:bg-indigo-700 transition">Secure in Vault</button>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* SIDEBAR: NETWORK & DEPENDENCIES */}
                        <aside className="space-y-8">
                            <div className="glass-card p-8 bg-slate-900 text-white shadow-2xl relative overflow-hidden">
                                <h3 className="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-8">Collaborative Context</h3>
                                <div className="space-y-6">
                                    <div>
                                        <label className="text-[8px] font-black text-slate-500 uppercase tracking-widest block mb-3">Decision Dependencies</label>
                                        <button className="w-full p-4 bg-white/5 border border-white/10 rounded-2xl text-xs font-bold text-slate-400 flex items-center gap-3 hover:bg-white/10 transition">
                                            <Icon name="link" size={14} /> Link to Parent Decision
                                        </button>
                                    </div>
                                    <div>
                                        <label className="text-[8px] font-black text-slate-500 uppercase tracking-widest block mb-3">Stakeholder Consensus</label>
                                        <div className="flex -space-x-2">
                                            {[1,2,3].map(i => <div key={i} className="w-8 h-8 rounded-full bg-slate-800 border-2 border-slate-900 flex items-center justify-center text-[10px] font-bold">U{i}</div>)}
                                            <button className="w-8 h-8 rounded-full bg-indigo-600 border-2 border-slate-900 flex items-center justify-center"><Icon name="plus" size={12}/></button>
                                        </div>
                                    </div>
                                </div>
                                <div className="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 blur-3xl"></div>
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
