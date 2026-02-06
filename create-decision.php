<?php
/**
 * File Path: create-decision.php
 * Description: High-fidelity React interface for creating strategic decisions.
 * Restores manual options, skip logic, and data connector persistence.
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
    <title>New Strategic Logic | DecisionVault</title>
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><path d=%22M50 5 L15 20 L15 45 C15 70 50 95 50 95 C50 95 85 70 85 45 L85 20 L50 5 Z%22 fill=%22%234f46e5%22 /><path d=%22M50 15 L25 25 L25 45 C25 62 50 82 50 82 C50 82 75 62 75 45 L75 25 L50 15 Z%22 fill=%22white%22 opacity=%220.2%22 /></svg>">
    
    <!-- CDNs -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #fcfcfd; }
        .animate-in { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .loader-dots:after { content: '.'; animation: dots 1.5s steps(5, end) infinite; }
        @keyframes dots { 0%, 20% { content: '.'; } 40% { content: '..'; } 60% { content: '...'; } 80%, 100% { content: ''; } }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div id="root" class="flex-grow"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;

        const Icon = ({ name, size = 20, className = "" }) => {
            useEffect(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            }, [name]);
            return <i data-lucide={name} style={{ width: size, height: size }} className={className}></i>;
        };

        function App() {
            const [step, setStep] = useState(1);
            const [title, setTitle] = useState('');
            const [problem, setProblem] = useState('');
            const [gaps, setGaps] = useState([]);
            const [contextData, setContextData] = useState({});
            const [options, setOptions] = useState([]);
            const [isAnalyzing, setIsAnalyzing] = useState(false);
            const [isSubmitting, setIsSubmitting] = useState(false);
            const [connectedServices, setConnectedServices] = useState([]);
            const [isConnecting, setIsConnecting] = useState(null);

            const connectorRegistry = [
                { id: 'stripe', name: 'Stripe', color: '#635BFF', description: 'Revenue & Burn', icon: 'S' },
                { id: 'hubspot', name: 'HubSpot', color: '#FF7A59', description: 'Pipeline & CRM', icon: 'H' },
                { id: 'salesforce', name: 'Salesforce', color: '#00A1E0', description: 'Enterprise Sales', icon: 'SF' },
                { id: 'linkedin', name: 'LinkedIn Ads', color: '#0A66C2', description: 'CAC & Funnel', icon: 'in' },
                { id: 'quickbooks', name: 'QuickBooks', color: '#2CA01C', description: 'OpEx & Profit', icon: 'Q' }
            ];

            useEffect(() => {
                const fetchConnectors = async () => {
                    try {
                        const res = await fetch('/api/get-connectors.php');
                        const data = await res.json();
                        if (data.success) {
                            setConnectedServices(data.connectors.map(c => c.provider.toLowerCase()));
                        }
                    } catch (e) {
                        console.warn("Connectors API not responsive, defaulting to local state.");
                    }
                };
                fetchConnectors();
            }, []);

            /**
             * Analyzes context and fetches options.
             * @param {boolean} forceTransition - If true, force move to Step 3 regardless of gaps.
             */
            const analyzeContext = async (forceTransition = false) => {
                if (title.length < 5) return;
                setIsAnalyzing(true);
                try {
                    const res = await fetch('/api/ai-strategy.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            title,
                            problem_statement: problem,
                            context_data: contextData,
                            active_connectors: connectedServices,
                            force_options: forceTransition // Tell the backend we definitely want options now
                        })
                    });
                    const data = await res.json();
                    
                    const newGaps = data.gaps || [];
                    setGaps(newGaps);

                    const initialOptions = (data.options || []).map((o, idx) => ({
                        ...o,
                        id: 'ai-' + idx + '-' + Date.now(),
                        isAiGenerated: true
                    }));
                    
                    // Only update options if we actually got new ones from the AI
                    if (initialOptions.length > 0) {
                        setOptions(initialOptions);
                    }

                    if (forceTransition) {
                        setStep(3);
                    } else if (step === 1) {
                        setStep(2);
                    } else if (step === 2 && newGaps.length === 0) {
                        // If we are in the interview and gaps are cleared, go to strategy
                        setStep(3);
                    }
                } catch (e) {
                    console.error("Analysis failed", e);
                } finally {
                    setIsAnalyzing(false);
                }
            };

            const toggleService = async (service) => {
                const isConnected = connectedServices.includes(service);
                setIsConnecting(service);
                try {
                    const res = await fetch('/api/toggle-connector.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ provider: service, action: isConnected ? 'disconnect' : 'connect' })
                    });
                    const data = await res.json();
                    if (data.success) {
                        setConnectedServices(isConnected ? connectedServices.filter(s => s !== service) : [...connectedServices, service]);
                    }
                } catch (e) {
                    console.warn("Backend link failed, updating local UI only.");
                    setConnectedServices(isConnected ? connectedServices.filter(s => s !== service) : [...connectedServices, service]);
                } finally {
                    setIsConnecting(null);
                }
            };

            const addManualOption = () => {
                setOptions([...options, { id: 'manual-' + Date.now(), name: '', description: '', confidence: 0, isAiGenerated: false }]);
            };

            const updateOption = (id, field, value) => {
                setOptions(options.map(opt => opt.id === id ? { ...opt, [field]: value } : opt));
            };

            const removeOption = (id) => {
                setOptions(options.filter(opt => opt.id !== id));
            };

            const saveDecision = async () => {
                if (options.length === 0) {
                    alert("Please provide at least one strategic path.");
                    return;
                }
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
                } catch (e) { alert("Save failed."); }
                finally { setIsSubmitting(false); }
            };

            return (
                <main className="max-w-7xl mx-auto py-12 px-6">
                    <header className="mb-12">
                        <div className="flex items-center gap-2 mb-4">
                            <Icon name="shield" className="text-indigo-600" />
                            <span className="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-600">Strategic Logic Engine</span>
                        </div>
                        <h1 className="text-5xl font-black text-slate-900 tracking-tighter">Record Strategic Logic</h1>
                    </header>

                    <div className="grid lg:grid-cols-3 gap-12">
                        <div className="lg:col-span-2 space-y-8">
                            
                            {/* STEP 1: NARRATIVE */}
                            {step === 1 && (
                                <div className="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/50 animate-in">
                                    <h2 className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8">01 • Problem Analysis</h2>
                                    <div className="space-y-8">
                                        <div>
                                            <label className="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Decision Title</label>
                                            <input className="w-full p-6 bg-slate-50 border-2 border-transparent rounded-3xl text-2xl font-black outline-none focus:border-indigo-600 focus:bg-white transition-all" placeholder="e.g. Expand into UK Market" value={title} onChange={(e) => setTitle(e.target.value)} />
                                        </div>
                                        <div>
                                            <label className="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Problem Statement</label>
                                            <textarea className="w-full p-6 bg-slate-50 border-2 border-transparent rounded-3xl h-40 font-medium outline-none focus:border-indigo-600 focus:bg-white transition-all" placeholder="What core problem are we solving?" value={problem} onChange={(e) => setProblem(e.target.value)} />
                                        </div>
                                        <button onClick={() => analyzeContext(false)} disabled={!title || isAnalyzing} className="w-full bg-slate-900 text-white py-6 rounded-2xl font-black text-lg shadow-2xl hover:bg-indigo-600 transition-all flex items-center justify-center gap-3">
                                            {isAnalyzing && <Icon name="loader-2" className="animate-spin" />}
                                            {isAnalyzing ? 'Scanning Knowledge Base...' : 'Analyze Strategic Gaps'}
                                        </button>
                                    </div>
                                </div>
                            )}

                            {/* STEP 2: INTERVIEW */}
                            {step === 2 && (
                                <div className="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/50 animate-in">
                                    <div className="flex justify-between items-center mb-10">
                                        <h2 className="text-[10px] font-black text-indigo-600 uppercase tracking-widest">02 • Intelligence Gaps</h2>
                                        {isAnalyzing && <Icon name="loader-2" className="animate-spin text-indigo-600" />}
                                    </div>
                                    {gaps.length > 0 ? (
                                        <div className="space-y-8">
                                            <p className="text-xl font-bold text-slate-900 leading-tight">Missing variables identified for high-confidence strategy:</p>
                                            <div className="space-y-4">
                                                {gaps.map((gap) => {
                                                    const isResolved = gap.suggested_connector && connectedServices.includes(gap.suggested_connector.toLowerCase());
                                                    return (
                                                        <div key={gap.key} className={`p-6 border rounded-3xl transition-all ${isResolved ? 'bg-emerald-50/50 border-emerald-100' : 'bg-slate-50 border-slate-100'}`}>
                                                            <div className="flex justify-between items-start mb-4">
                                                                <div>
                                                                    <div className="font-black text-slate-900">{gap.label} {isResolved && '✓'}</div>
                                                                    <div className="text-xs text-slate-500">{gap.reason}</div>
                                                                </div>
                                                                {gap.suggested_connector && (
                                                                    <button onClick={() => toggleService(gap.suggested_connector.toLowerCase())} className={`px-3 py-1.5 rounded-full text-[10px] font-black uppercase transition ${isResolved ? 'bg-emerald-100 text-emerald-600' : 'bg-indigo-50 text-indigo-600'}`}>
                                                                        {isResolved ? 'Linked' : `Link ${gap.suggested_connector}`}
                                                                    </button>
                                                                )}
                                                            </div>
                                                            <input className="w-full p-4 bg-white border border-slate-200 rounded-2xl font-bold outline-none focus:border-indigo-600 disabled:opacity-50" value={contextData[gap.key] || ''} disabled={isResolved} onChange={(e) => setContextData({...contextData, [gap.key]: e.target.value})} placeholder={isResolved ? 'Verified via API' : 'Enter value...'} />
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                            <div className="flex gap-4">
                                                <button onClick={() => setStep(1)} className="px-8 py-5 border border-slate-100 rounded-2xl font-black text-slate-400">Back</button>
                                                <button onClick={() => analyzeContext(false)} disabled={isAnalyzing} className="flex-1 bg-indigo-600 text-white py-5 rounded-2xl font-black flex items-center justify-center gap-2">
                                                    {isAnalyzing && <Icon name="loader-2" className="animate-spin" />}
                                                    Refresh Recommendations
                                                </button>
                                            </div>
                                            <div className="text-center">
                                                <button onClick={() => analyzeContext(true)} disabled={isAnalyzing} className="text-xs font-black text-slate-300 uppercase tracking-widest hover:text-indigo-600 transition">
                                                    Skip & Generate Speculative Paths
                                                </button>
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="py-20 text-center">
                                            <Icon name="zap" size={40} className="text-indigo-600 mx-auto mb-6" />
                                            <h3 className="text-2xl font-black text-slate-900">Logic Verified</h3>
                                            <p className="text-slate-500 mt-2">I have enough context to generate high-fidelity paths.</p>
                                            <button onClick={() => setStep(3)} className="mt-8 bg-slate-900 text-white px-10 py-4 rounded-xl font-black uppercase tracking-widest text-xs">View Options</button>
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* STEP 3: OPTIONS & MANUAL */}
                            {step === 3 && (
                                <div className="space-y-8 animate-in">
                                    <div className="flex justify-between items-center">
                                        <h2 className="text-[10px] font-black text-slate-400 uppercase tracking-widest">03 • Strategic Paths</h2>
                                        <div className="flex gap-4">
                                            <button onClick={analyzeContext} disabled={isAnalyzing} className="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2 hover:text-indigo-600 transition">
                                                {isAnalyzing ? <Icon name="loader-2" className="animate-spin" size={12} /> : <Icon name="refresh-cw" size={12} />}
                                                Regenerate AI Options
                                            </button>
                                            <button onClick={addManualOption} className="text-[10px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2">
                                                <Icon name="plus" size={12} /> Add Manual Path
                                            </button>
                                        </div>
                                    </div>

                                    {isAnalyzing && options.length === 0 ? (
                                        <div className="p-20 bg-white border border-slate-100 rounded-[3rem] text-center">
                                            <div className="animate-pulse space-y-4">
                                                <div className="h-8 bg-slate-100 rounded-full w-2/3 mx-auto"></div>
                                                <div className="h-4 bg-slate-50 rounded-full w-1/2 mx-auto"></div>
                                                <div className="h-4 bg-slate-50 rounded-full w-1/3 mx-auto"></div>
                                            </div>
                                            <p className="text-[10px] font-black text-indigo-600 uppercase tracking-widest mt-8 loader-dots">Simulating Logical Outcomes</p>
                                        </div>
                                    ) : (
                                        <div className="space-y-6">
                                            {options.map((opt) => (
                                                <div key={opt.id} className="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm relative group">
                                                    <button onClick={() => removeOption(opt.id)} className="absolute top-8 right-8 text-slate-200 hover:text-red-500 transition"><Icon name="trash-2" size={18} /></button>
                                                    <div className="flex justify-between items-start mb-6 mr-10">
                                                        <input className="text-3xl font-black text-slate-900 bg-transparent border-b-2 border-transparent focus:border-indigo-600 outline-none w-full tracking-tighter" value={opt.name} placeholder="Title of Strategic Path" onChange={(e) => updateOption(opt.id, 'name', e.target.value)} />
                                                        {opt.isAiGenerated && <span className="bg-emerald-50 text-emerald-600 text-[8px] px-2 py-1 rounded-full font-black uppercase tracking-widest shrink-0 ml-4">AI SUGGESTED</span>}
                                                    </div>
                                                    <textarea className="w-full bg-slate-50 p-6 rounded-2xl text-slate-600 font-medium h-32 resize-none outline-none focus:bg-white focus:ring-2 focus:ring-indigo-100 transition" value={opt.description} placeholder="Describe the rationale and expected outcome..." onChange={(e) => updateOption(opt.id, 'description', e.target.value)} />
                                                </div>
                                            ))}
                                            
                                            {options.length === 0 && !isAnalyzing && (
                                                <div className="p-16 border-2 border-dashed border-slate-100 rounded-[3rem] text-center">
                                                    <p className="text-slate-400 font-medium mb-6">No automated paths generated yet.</p>
                                                    <button onClick={() => analyzeContext(true)} className="bg-slate-900 text-white px-8 py-3 rounded-xl font-black uppercase text-[10px] tracking-widest shadow-xl">Generate AI Options</button>
                                                </div>
                                            )}
                                        </div>
                                    )}

                                    <div className="flex gap-4">
                                        <button onClick={() => setStep(2)} className="px-8 py-6 bg-white border border-slate-100 rounded-3xl font-black text-slate-400">Back</button>
                                        <button onClick={saveDecision} disabled={isSubmitting || options.length === 0} className="flex-1 bg-indigo-600 text-white py-6 rounded-3xl font-black text-xl shadow-2xl flex items-center justify-center gap-3">
                                            {isSubmitting && <Icon name="loader-2" className="animate-spin" />}
                                            {isSubmitting ? 'Securing...' : 'Secure in Logic Vault'}
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* CONNECTORS SIDEBAR */}
                        <aside className="space-y-8">
                            <div className="p-8 bg-slate-900 text-white rounded-[2.5rem] shadow-2xl sticky top-24">
                                <h3 className="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-8">Intelligence Connectors</h3>
                                <div className="space-y-4">
                                    {connectorRegistry.map(conn => (
                                        <div key={conn.id} className={`flex items-center justify-between p-4 bg-white/5 rounded-2xl border transition ${connectedServices.includes(conn.id) ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-white/10'}`}>
                                            <div className="flex items-center gap-3">
                                                <div className="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white shadow-sm" style={{backgroundColor: conn.color}}>{conn.icon}</div>
                                                <div>
                                                    <div className="text-[10px] font-black tracking-tight">{conn.name}</div>
                                                    <div className="text-[8px] font-black uppercase text-slate-500">{connectedServices.includes(conn.id) ? 'Synced' : conn.description}</div>
                                                </div>
                                            </div>
                                            <button onClick={() => toggleService(conn.id)} disabled={isConnecting === conn.id} className={`w-8 h-8 rounded-full flex items-center justify-center transition ${connectedServices.includes(conn.id) ? 'bg-emerald-500 shadow-md' : 'bg-white/10 hover:bg-white/20'}`}>
                                                {isConnecting === conn.id ? <Icon name="loader-2" size={12} className="animate-spin" /> : <Icon name={connectedServices.includes(conn.id) ? "check" : "plus"} size={14} />}
                                            </button>
                                        </div>
                                    ))}
                                </div>
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
