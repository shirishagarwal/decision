<?php
/**
 * File Path: create-decision.php
 * Description: The definitive Enterprise Strategy Creator.
 * Integrates: React UI, RAG Knowledge Hub links, Stakeholder tracking, and AI synthesis.
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
    
    <!-- Core Dependencies -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #fcfcfd; color: #0f172a; }
        
        .premium-card { background: white; border: 1px solid #f1f3f5; box-shadow: 0 10px 30px -5px rgba(0,0,0,0.03); border-radius: 2.5rem; }
        .input-formal { width: 100%; padding: 1.25rem; border: 1px solid #e2e8f0; border-radius: 1rem; background: #fdfdfd; font-size: 0.95rem; outline: none; transition: all 0.2s; }
        .input-formal:focus { border-color: #6366f1; background: white; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.05); }
        
        .step-pill { padding: 6px 16px; border-radius: 999px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; transition: all 0.3s; }
        .step-active { background: #4f46e5; color: white; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
        .step-inactive { background: #f1f5f9; color: #94a3b8; }

        .animate-in { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div id="root" class="flex-grow"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;

        /**
         * Lucide Icon Wrapper for React
         */
        const Icon = ({ name, size = 18, className = "" }) => {
            useEffect(() => {
                if (window.lucide) window.lucide.createIcons();
            }, [name]);
            return <i data-lucide={name} style={{ width: size, height: size }} className={className}></i>;
        };

        function App() {
            const [step, setStep] = useState(1);
            const [title, setTitle] = useState('');
            const [problem, setProblem] = useState('');
            const [stakeholders, setStakeholders] = useState('');
            const [gaps, setGaps] = useState([]);
            const [contextData, setContextData] = useState({});
            const [options, setOptions] = useState([]);
            const [aiPayload, setAiPayload] = useState({ counterfactual: null, benchmark: '' });
            
            const [isAnalyzing, setIsAnalyzing] = useState(false);
            const [isSubmitting, setIsSubmitting] = useState(false);
            const [connectedServices, setConnectedServices] = useState([]);

            // Hardcoded registry for UI display
            const connectorRegistry = [
                { id: 'stripe', name: 'Stripe', color: '#635BFF', icon: 'S' },
                { id: 'hubspot', name: 'HubSpot', color: '#FF7A59', icon: 'H' },
                { id: 'salesforce', name: 'Salesforce', color: '#00A1E0', icon: 'SF' }
            ];

            const fetchIntelligence = async (forceOptions = false) => {
                if (title.length < 5) return;
                setIsAnalyzing(true);
                try {
                    const res = await fetch('/api/ai-strategy.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            title,
                            problem_statement: problem,
                            stakeholders: stakeholders.split(',').map(s => s.trim()).filter(s => s !== ''),
                            context_data: contextData,
                            active_connectors: connectedServices,
                            force_options: forceOptions
                        })
                    });
                    const data = await res.json();
                    
                    if (data.success || data.strategic_options) {
                        setGaps(data.context_gaps || data.gaps || []);
                        setAiPayload({
                            counterfactual: data.counterfactual_analysis || data.counterfactual,
                            benchmark: data.industry_benchmark || data.benchmark
                        });
                        
                        const fetchedOptions = (data.strategic_options || data.options || []).map((o, idx) => ({
                            ...o,
                            id: 'ai-' + idx + '-' + Date.now(),
                            isAiGenerated: true
                        }));
                        
                        if (fetchedOptions.length > 0) setOptions(fetchedOptions);

                        // Decision Flow Logic
                        if (forceOptions || (step === 2 && (!data.gaps || data.gaps.length === 0))) {
                            setStep(3);
                        } else if (step === 1) {
                            setStep(2);
                        }
                    }
                } catch (e) {
                    console.error("Strategic Synthesis Failed", e);
                } finally {
                    setIsAnalyzing(false);
                }
            };

            const saveDecision = async () => {
                setIsSubmitting(true);
                try {
                    const res = await fetch('/api/create-decision.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            title,
                            problem,
                            options: options.map(o => ({ name: o.name, description: o.description, isAiGenerated: o.isAiGenerated })),
                            mode: 'create'
                        })
                    });
                    const data = await res.json();
                    if (data.success) window.location.href = `/decision.php?id=${data.decision_id}`;
                } catch (e) { alert("Vault Security Failure."); }
                finally { setIsSubmitting(false); }
            };

            const activeStakeholders = stakeholders.split(',').map(s => s.trim()).filter(s => s !== '');

            return (
                <main className="max-w-7xl mx-auto py-12 px-6">
                    <header className="mb-12 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
                        <div>
                            <div className="flex items-center gap-2 mb-2">
                                <Icon name="shield" className="text-indigo-600" />
                                <span className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Governance Architecture OS</span>
                            </div>
                            <h1 className="text-4xl font-black text-slate-900 tracking-tighter">Record Strategic Logic</h1>
                        </div>
                        <div className="flex items-center gap-3">
                            <span className={`step-pill ${step === 1 ? 'step-active' : 'step-inactive'}`}>01 Architecture</span>
                            <Icon name="chevron-right" size={14} className="text-slate-300" />
                            <span className={`step-pill ${step === 2 ? 'step-active' : 'step-inactive'}`}>02 Intelligence</span>
                            <Icon name="chevron-right" size={14} className="text-slate-300" />
                            <span className={`step-pill ${step === 3 ? 'step-active' : 'step-inactive'}`}>03 Synthesis</span>
                        </div>
                    </header>

                    <div className="grid lg:grid-cols-4 gap-12">
                        <div className="lg:col-span-3 space-y-8">
                            
                            {/* STEP 1: CONTEXT & RATIONALE */}
                            {step === 1 && (
                                <div className="premium-card p-10 animate-in">
                                    <h2 className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8 flex items-center gap-2">
                                        <Icon name="file-text" size={14} className="text-indigo-600" /> 01 • Rationale and Governance
                                    </h2>
                                    <div className="space-y-8">
                                        <div>
                                            <label className="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Decision Title</label>
                                            <input
                                                className="input-formal text-xl font-bold"
                                                placeholder="e.g. FY26 International Expansion Strategy"
                                                value={title}
                                                onChange={(e) => setTitle(e.target.value)}
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Strategic Problem Statement</label>
                                            <textarea
                                                className="input-formal h-40 font-medium"
                                                placeholder="What core friction or market signal is driving this decision? Document the rationale for institutional memory."
                                                value={problem}
                                                onChange={(e) => setProblem(e.target.value)}
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Key Stakeholders (Emails, comma separated)</label>
                                            <input
                                                className="input-formal text-sm font-bold"
                                                placeholder="ceo@company.com, cfo@company.com, head_of_product@company.com"
                                                value={stakeholders}
                                                onChange={(e) => setStakeholders(e.target.value)}
                                            />
                                        </div>
                                        <button
                                            onClick={() => fetchIntelligence()}
                                            disabled={!title || isAnalyzing}
                                            className="w-full bg-slate-900 text-white py-5 rounded-2xl font-black text-sm uppercase tracking-widest shadow-2xl hover:bg-indigo-600 transition-all flex items-center justify-center gap-3 disabled:opacity-50"
                                        >
                                            {isAnalyzing ? <Icon name="loader-2" className="animate-spin" /> : <Icon name="zap" size={16} />}
                                            {isAnalyzing ? 'Synthesizing Signals...' : 'Analyze Intelligence Gaps'}
                                        </button>
                                    </div>
                                </div>
                            )}

                            {/* STEP 2: INTELLIGENCE INTERVIEW */}
                            {step === 2 && (
                                <div className="premium-card p-10 animate-in">
                                    <div className="flex justify-between items-center mb-10">
                                        <h2 className="text-[10px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2">
                                            <Icon name="search" size={14} /> 02 • Information Requirements
                                        </h2>
                                        {isAnalyzing && <Icon name="loader-2" className="animate-spin text-indigo-600" />}
                                    </div>

                                    {gaps.length > 0 ? (
                                        <div className="space-y-8">
                                            <p className="text-xl font-bold text-slate-900 leading-tight">Critical variables required for high-confidence synthesis:</p>
                                            <div className="grid md:grid-cols-2 gap-4">
                                                {gaps.map((gap) => (
                                                    <div key={gap.key} className="p-6 bg-slate-50 border border-slate-100 rounded-3xl group hover:border-indigo-200 transition-all">
                                                        <div className="font-black text-slate-900 mb-1">{gap.label}</div>
                                                        <div className="text-xs text-slate-500 font-medium mb-4">{gap.reason}</div>
                                                        <input
                                                            className="w-full p-4 bg-white border border-slate-200 rounded-2xl font-bold outline-none focus:border-indigo-600"
                                                            value={contextData[gap.key] || ''}
                                                            onChange={(e) => setContextData({...contextData, [gap.key]: e.target.value})}
                                                            placeholder="Enter manual value..."
                                                        />
                                                    </div>
                                                ))}
                                            </div>
                                            <div className="flex gap-4 pt-6 border-t border-slate-100">
                                                <button onClick={() => setStep(1)} className="px-8 py-4 border border-slate-200 rounded-xl font-black text-slate-400 text-xs uppercase tracking-widest">Back</button>
                                                <button onClick={() => fetchIntelligence()} disabled={isAnalyzing} className="flex-1 bg-indigo-600 text-white py-4 rounded-xl font-black text-xs uppercase tracking-widest shadow-xl flex items-center justify-center gap-2">
                                                    Update Modeling
                                                </button>
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="py-20 text-center">
                                            <Icon name="check-circle" size={48} className="text-emerald-500 mx-auto mb-6" />
                                            <h3 className="text-2xl font-black text-slate-900">Intelligence Baseline Met</h3>
                                            <button onClick={() => setStep(3)} className="mt-8 bg-slate-900 text-white px-10 py-4 rounded-xl font-black uppercase tracking-widest text-xs">View Synthesis</button>
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* STEP 3: PATHS & SYNTHESIS */}
                            {step === 3 && (
                                <div className="space-y-8 animate-in">
                                    <div className="flex justify-between items-center">
                                        <h2 className="text-xs font-black text-slate-400 uppercase tracking-widest">03 • Strategic Paths</h2>
                                        <button onClick={() => fetchIntelligence(true)} className="text-[10px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2">
                                            <Icon name="refresh-cw" size={12} /> Regenerate
                                        </button>
                                    </div>

                                    {aiPayload.counterfactual && (
                                        <div className="p-8 bg-red-50 border border-red-100 rounded-[2.5rem]">
                                            <div className="text-[10px] font-black text-red-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                                                <Icon name="alert-circle" size={12} /> The Impact of Inaction (Counterfactual)
                                            </div>
                                            <p className="text-red-900 font-bold leading-relaxed italic text-sm">{aiPayload.counterfactual}</p>
                                        </div>
                                    )}

                                    <div className="space-y-6">
                                        {options.map((opt) => (
                                            <div key={opt.id} className="premium-card p-10 hover:border-indigo-200 transition-all group relative">
                                                <div className="flex justify-between items-start mb-6">
                                                    <h3 className="text-2xl font-black text-slate-900 group-hover:text-indigo-600 transition tracking-tight">{opt.name}</h3>
                                                    <div className="text-right">
                                                        <div className="text-[8px] font-black text-emerald-500 uppercase">Confidence</div>
                                                        <div className="text-sm font-black text-slate-900">{opt.confidence_interval || '85-90%'}</div>
                                                    </div>
                                                </div>
                                                <p className="text-slate-500 font-medium text-sm leading-relaxed mb-8">{opt.description}</p>
                                                <div className="grid grid-cols-3 gap-4 pt-6 border-t border-slate-50">
                                                    <div className="p-3 bg-slate-50 rounded-2xl">
                                                        <div className="text-[8px] font-black text-slate-400 uppercase mb-1">Expected ROI</div>
                                                        <div className="text-[10px] font-black text-indigo-600">{opt.expected_value || 'TBD'}</div>
                                                    </div>
                                                    <div className="p-3 bg-slate-50 rounded-2xl">
                                                        <div className="text-[8px] font-black text-slate-400 uppercase mb-1">Risk Score</div>
                                                        <div className="text-[10px] font-black text-red-500">{opt.risk_score || '3'}/10</div>
                                                    </div>
                                                    <div className="p-3 bg-slate-50 rounded-2xl">
                                                        <div className="text-[8px] font-black text-slate-400 uppercase mb-1">Benchmark</div>
                                                        <div className="text-[10px] font-black text-slate-900 truncate">{opt.pattern_match || 'Strategic Fit'}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    <div className="flex gap-4 pt-10">
                                       <button onClick={() => setStep(2)} className="px-10 py-5 bg-white border border-slate-100 rounded-2xl font-black text-slate-400 text-xs uppercase tracking-widest">Back</button>
                                       <button onClick={saveDecision} disabled={isSubmitting || options.length === 0} className="flex-1 bg-indigo-600 text-white py-5 rounded-2xl font-black text-lg shadow-2xl flex items-center justify-center gap-3">
                                            {isSubmitting ? <Icon name="loader-2" className="animate-spin" /> : <Icon name="shield" size={20} />}
                                            {isSubmitting ? 'Securing Logic...' : 'Finalize Strategic Artifact'}
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* SIDEBAR: GOVERNANCE & KNOWLEDGE HUB */}
                        <aside className="space-y-6">
                            <div className="p-8 bg-slate-900 text-white rounded-[2.5rem] shadow-2xl sticky top-24 overflow-hidden relative">
                                <h3 className="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-8 relative z-10">Decision Governance</h3>
                                
                                <div className="space-y-8 relative z-10">
                                    {/* Stakeholder Tracking */}
                                    <div>
                                        <div className="text-[8px] font-black text-slate-500 uppercase tracking-widest mb-4">Active Stakeholders</div>
                                        <div className="space-y-3">
                                            {activeStakeholders.length > 0 ? activeStakeholders.map((s, i) => (
                                                <div key={i} className="flex items-center gap-3 bg-white/5 p-2.5 rounded-xl border border-white/10">
                                                    <div className="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center text-[9px] font-bold uppercase">{s.charAt(0)}</div>
                                                    <div className="flex-1 min-w-0">
                                                        <div className="text-[10px] font-bold truncate">{s}</div>
                                                        <div className="text-[7px] font-black text-slate-500 uppercase">Awaiting Review</div>
                                                    </div>
                                                </div>
                                            )) : (
                                                <div className="text-[9px] text-slate-500 italic">No stakeholders identified in Step 1.</div>
                                            )}
                                        </div>
                                    </div>

                                    {/* Knowledge Hub / RAG Prompt */}
                                    <div className="pt-6 border-t border-white/10">
                                        <a href="/organization-knowledge.php" className="flex items-center justify-between group">
                                            <div className="flex items-center gap-2">
                                                <Icon name="book-open" size={14} className="text-slate-500 group-hover:text-indigo-400 transition" />
                                                <span className="text-[10px] font-black uppercase tracking-widest text-slate-500 group-hover:text-white transition">Knowledge Hub</span>
                                            </div>
                                            <Icon name="chevron-right" size={14} className="text-slate-700 group-hover:text-white transition" />
                                        </a>
                                        <p className="text-[9px] text-slate-500 mt-4 leading-relaxed font-medium">
                                            Link internal datasets (PDFs, Wikis) to resolve intelligence gaps via Privacy Shield RAG.
                                        </p>
                                    </div>

                                    {/* Data Connectors */}
                                    <div className="space-y-3">
                                        <div className="text-[8px] font-black text-slate-500 uppercase tracking-widest">Connectors</div>
                                        {connectorRegistry.map(conn => (
                                            <div key={conn.id} className="flex items-center justify-between p-3 rounded-xl border border-white/5 bg-white/5">
                                                <div className="flex items-center gap-3">
                                                    <div className="w-6 h-6 rounded flex items-center justify-center text-[9px] font-black text-white" style={{backgroundColor: conn.color}}>{conn.icon}</div>
                                                    <div className="text-[10px] font-bold">{conn.name}</div>
                                                </div>
                                                <Icon name="link" size={12} className="text-slate-600" />
                                            </div>
                                        ))}
                                    </div>
                                </div>
                                <div className="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 blur-3xl rounded-full"></div>
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
