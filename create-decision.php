<?php
/**
 * File Path: create-decision.php
 * Description: Restored high-fidelity interface with integrated killer features.
 * Features: 3-step flow, Manual Path Editing, Risk Quantification, and Data Connectors.
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
        body { font-family: 'Inter', sans-serif; background-color: #fcfcfd; color: #0f172a; }
        .animate-in { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .loader-dots:after { content: '.'; animation: dots 1.5s steps(5, end) infinite; }
        @keyframes dots { 0%, 20% { content: '.'; } 40% { content: '..'; } 60% { content: '...'; } 80%, 100% { content: ''; } }
        .premium-card { background: white; border: 1px solid #f1f3f5; box-shadow: 0 10px 30px -5px rgba(0,0,0,0.03); border-radius: 3rem; }
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
            const [gaps, setGaps] = useState([]);
            const [contextData, setContextData] = useState({});
            const [options, setOptions] = useState([]);
            const [aiPayload, setAiPayload] = useState({ counterfactual: null, benchmark: '' });
            const [isAnalyzing, setIsAnalyzing] = useState(false);
            const [isSubmitting, setIsSubmitting] = useState(false);
            
            const [connectedServices, setConnectedServices] = useState(['stripe']);
            const [isConnecting, setIsConnecting] = useState(null);

            const connectorRegistry = [
                { id: 'stripe', name: 'Stripe', color: '#635BFF', description: 'Revenue & Burn', icon: 'S' },
                { id: 'hubspot', name: 'HubSpot', color: '#FF7A59', description: 'Pipeline & CRM', icon: 'H' },
                { id: 'salesforce', name: 'Salesforce', color: '#00A1E0', description: 'Enterprise Sales', icon: 'SF' },
                { id: 'linkedin', name: 'LinkedIn Ads', color: '#0A66C2', description: 'CAC & Funnel', icon: 'in' }
            ];

            useEffect(() => {
                const fetchConnectors = async () => {
                    try {
                        const res = await fetch('/api/get-connectors.php');
                        const data = await res.json();
                        if (data.success) setConnectedServices(data.connectors.map(c => c.provider.toLowerCase()));
                    } catch (e) { console.warn("Syncing offline."); }
                };
                fetchConnectors();
            }, []);

            const analyzeContext = async (forceOptions = false) => {
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
                            force_options: forceOptions
                        })
                    });
                    const data = await res.json();
                    
                    if (data.success) {
                        setGaps(data.gaps || []);
                        setAiPayload({
                            counterfactual: data.counterfactual,
                            benchmark: data.benchmark
                        });
                        
                        // Map and unique-ID new options
                        const fetchedOptions = (data.options || []).map((o, idx) => ({
                            ...o,
                            id: 'ai-' + idx + '-' + Date.now(),
                            isAiGenerated: true
                        }));
                        
                        if (fetchedOptions.length > 0) setOptions(fetchedOptions);

                        if (forceOptions || (step === 2 && data.gaps.length === 0)) {
                            setStep(3);
                        } else if (step === 1) {
                            setStep(2);
                        }
                    }
                } catch (e) {
                    console.error("AI Analysis failed", e);
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
                    setConnectedServices(isConnected ? connectedServices.filter(s => s !== service) : [...connectedServices, service]);
                } finally {
                    setIsConnecting(null);
                }
            };

            const addManualOption = () => {
                setOptions([...options, { id: 'manual-' + Date.now(), name: '', description: '', confidence_interval: 'N/A', risk_score: 5, expected_value: '$0', isAiGenerated: false }]);
            };

            const updateOption = (id, field, value) => {
                setOptions(options.map(opt => opt.id === id ? { ...opt, [field]: value } : opt));
            };

            const removeOption = (id) => {
                setOptions(options.filter(opt => opt.id !== id));
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
                } catch (e) { alert("Secure Save failed."); }
                finally { setIsSubmitting(false); }
            };

            return (
                <main className="max-w-7xl mx-auto py-12 px-6">
                    <header className="mb-12 flex justify-between items-end">
                        <div>
                            <div className="flex items-center gap-2 mb-4">
                                <Icon name="shield" className="text-indigo-600" />
                                <span className="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-600">Decision Intelligence OS</span>
                            </div>
                            <h1 className="text-5xl font-black text-slate-900 tracking-tighter">Strategic Architecture</h1>
                        </div>
                        {aiPayload.benchmark && (
                            <div className="hidden lg:block bg-indigo-50 border border-indigo-100 p-4 rounded-2xl animate-in">
                                <div className="text-[8px] font-black text-indigo-400 uppercase tracking-widest mb-1">Industry Benchmark</div>
                                <div className="text-xs font-bold text-indigo-900">{aiPayload.benchmark}</div>
                            </div>
                        )}
                    </header>

                    <div className="grid lg:grid-cols-3 gap-12">
                        <div className="lg:col-span-2 space-y-8">
                            
                            {/* STEP 1: NARRATIVE */}
                            {step === 1 && (
                                <div className="premium-card p-10 animate-in">
                                    <h2 className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8">01 • Problem Analysis</h2>
                                    <div className="space-y-8">
                                        <div>
                                            <label className="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">The Decision Title</label>
                                            <input className="w-full p-6 bg-slate-50 border-2 border-transparent rounded-3xl text-2xl font-black outline-none focus:border-indigo-600 focus:bg-white transition-all" placeholder="e.g. Hire VP of Sales" value={title} onChange={(e) => setTitle(e.target.value)} />
                                        </div>
                                        <div>
                                            <label className="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Strategic Problem Statement</label>
                                            <textarea className="w-full p-6 bg-slate-50 border-2 border-transparent rounded-3xl h-40 font-medium outline-none focus:border-indigo-600 focus:bg-white transition-all" placeholder="What core friction is driving this choice?" value={problem} onChange={(e) => setProblem(e.target.value)} />
                                        </div>
                                        <button
                                            onClick={() => analyzeContext(false)}
                                            disabled={!title || isAnalyzing}
                                            className="w-full bg-slate-900 text-white py-6 rounded-2xl font-black text-lg shadow-2xl hover:bg-indigo-600 transition-all flex items-center justify-center gap-3 disabled:opacity-50"
                                        >
                                            {isAnalyzing ? <Icon name="loader-2" className="animate-spin" /> : <Icon name="cpu" />}
                                            {isAnalyzing ? 'Scanning External Failures...' : 'Weaponize Logic'}
                                        </button>
                                    </div>
                                </div>
                            )}

                            {/* STEP 2: INTERVIEW */}
                            {step === 2 && (
                                <div className="premium-card p-10 animate-in">
                                    <div className="flex justify-between items-center mb-10">
                                        <h2 className="text-[10px] font-black text-indigo-600 uppercase tracking-widest">02 • Intelligence Interview</h2>
                                        {isAnalyzing && <Icon name="loader-2" className="animate-spin text-indigo-600" />}
                                    </div>

                                    {gaps.length > 0 ? (
                                        <div className="space-y-8">
                                            <p className="text-xl font-bold text-slate-900 leading-tight">Identify missing variables for a high-confidence recommendation:</p>
                                            <div className="space-y-4">
                                                {gaps.map((gap) => {
                                                    const isResolved = gap.suggested_connector && connectedServices.includes(gap.suggested_connector.toLowerCase());
                                                    return (
                                                        <div key={gap.key} className={`p-6 border rounded-3xl transition-all ${isResolved ? 'bg-emerald-50/50 border-emerald-100' : 'bg-slate-50 border-slate-100 group hover:border-indigo-200'}`}>
                                                            <div className="flex justify-between items-start mb-4">
                                                                <div>
                                                                    <div className="font-black text-slate-900">{gap.label} {isResolved && '✓'}</div>
                                                                    <div className="text-xs text-slate-500 font-medium">{gap.reason}</div>
                                                                </div>
                                                                {gap.suggested_connector && (
                                                                    <button onClick={() => toggleService(gap.suggested_connector.toLowerCase())} className={`px-3 py-1.5 rounded-full text-[10px] font-black uppercase transition ${isResolved ? 'bg-emerald-100 text-emerald-600' : 'bg-indigo-50 text-indigo-600 hover:bg-indigo-100'}`}>
                                                                        {isResolved ? 'Verified' : `Link ${gap.suggested_connector}`}
                                                                    </button>
                                                                )}
                                                            </div>
                                                            <input className="w-full p-4 bg-white border border-slate-200 rounded-2xl font-bold outline-none focus:border-indigo-600 disabled:opacity-50" value={contextData[gap.key] || ''} disabled={isResolved} onChange={(e) => setContextData({...contextData, [gap.key]: e.target.value})} placeholder={isResolved ? 'Automatically synced via API' : 'Enter value...'} />
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                            <div className="flex gap-4">
                                                <button onClick={() => setStep(1)} className="px-8 py-5 border border-slate-100 rounded-2xl font-black text-slate-400">Back</button>
                                                <button onClick={() => analyzeContext(false)} disabled={isAnalyzing} className="flex-1 bg-indigo-600 text-white py-5 rounded-2xl font-black flex items-center justify-center gap-2">
                                                    {isAnalyzing && <Icon name="loader-2" className="animate-spin" />}
                                                    Update Modeling
                                                </button>
                                            </div>
                                            <div className="text-center">
                                                <button onClick={() => analyzeContext(true)} disabled={isAnalyzing} className="text-xs font-black text-slate-300 uppercase tracking-widest hover:text-indigo-600 transition">Skip & View Speculative Paths</button>
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="py-20 text-center">
                                            <Icon name="zap" size={48} className="text-indigo-600 mx-auto mb-6" />
                                            <h3 className="text-2xl font-black text-slate-900">Intelligence Baseline Met</h3>
                                            <button onClick={() => setStep(3)} className="mt-8 bg-slate-900 text-white px-10 py-4 rounded-xl font-black uppercase tracking-widest text-xs">View Options</button>
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* STEP 3: PATHS */}
                            {step === 3 && (
                                <div className="space-y-8 animate-in">
                                    <div className="flex justify-between items-center">
                                        <h2 className="text-[10px] font-black text-slate-400 uppercase tracking-widest">03 • Strategic Architecture</h2>
                                        <div className="flex gap-4">
                                            <button onClick={() => analyzeContext(true)} disabled={isAnalyzing} className="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2 hover:text-indigo-600 transition">
                                                <Icon name="refresh-cw" size={12} className={isAnalyzing ? 'animate-spin' : ''} /> Regenerate
                                            </button>
                                            <button onClick={addManualOption} className="text-[10px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2">
                                                <Icon name="plus" size={12} /> Manual Path
                                            </button>
                                        </div>
                                    </div>

                                    {aiPayload.counterfactual && (
                                        <div className="p-8 bg-red-50 border border-red-100 rounded-[3rem] relative overflow-hidden">
                                            <div className="text-[10px] font-black text-red-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                                                <Icon name="alert-circle" size={12} /> The Cost of Inaction
                                            </div>
                                            <p className="text-red-900 font-bold leading-relaxed">{aiPayload.counterfactual}</p>
                                        </div>
                                    )}

                                    <div className="space-y-6">
                                        {options.map((opt) => (
                                            <div key={opt.id} className="premium-card p-10 hover:border-indigo-200 transition-all group relative">
                                                <button onClick={() => removeOption(opt.id)} className="absolute top-8 right-8 text-slate-200 hover:text-red-500 transition"><Icon name="trash-2" size={18} /></button>
                                                <div className="flex justify-between items-start mb-6 mr-10">
                                                    <input className="text-3xl font-black text-slate-900 bg-transparent border-b-2 border-transparent focus:border-indigo-600 outline-none w-full tracking-tighter" value={opt.name} placeholder="Path Title..." onChange={(e) => updateOption(opt.id, 'name', e.target.value)} />
                                                    {opt.isAiGenerated && (
                                                        <div className="text-right shrink-0 ml-4">
                                                            <div className="text-[8px] font-black text-emerald-500 uppercase">Confidence</div>
                                                            <div className="text-lg font-black text-slate-900">{opt.confidence_interval || '80%'}</div>
                                                        </div>
                                                    )}
                                                </div>
                                                <textarea className="w-full bg-slate-50 p-6 rounded-2xl text-slate-600 font-medium h-32 resize-none outline-none focus:bg-white transition" value={opt.description} placeholder="Logic rationale..." onChange={(e) => updateOption(opt.id, 'description', e.target.value)} />
                                                
                                                {opt.isAiGenerated && (
                                                    <div className="grid grid-cols-3 gap-4 mt-8 pt-8 border-t border-slate-50">
                                                        <div className="text-center p-3 bg-slate-50 rounded-2xl">
                                                            <div className="text-[8px] font-black text-slate-400 uppercase mb-1">Expected Value</div>
                                                            <div className="text-xs font-black text-indigo-600">{opt.expected_value}</div>
                                                        </div>
                                                        <div className="text-center p-3 bg-slate-50 rounded-2xl">
                                                            <div className="text-[8px] font-black text-slate-400 uppercase mb-1">Risk Score</div>
                                                            <div className="text-xs font-black text-red-500">{opt.risk_score}/10</div>
                                                        </div>
                                                        <div className="text-center p-3 bg-slate-50 rounded-2xl">
                                                            <div className="text-[8px] font-black text-slate-400 uppercase mb-1">Pattern Match</div>
                                                            <div className="text-[10px] font-black text-slate-900 truncate">{opt.pattern_match || 'Strategic Fit'}</div>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        ))}
                                    </div>

                                    <div className="flex gap-4 pt-10">
                                       <button onClick={() => setStep(2)} className="px-10 py-6 bg-white border border-slate-100 rounded-3xl font-black text-slate-400">Back</button>
                                       <button onClick={saveDecision} disabled={isSubmitting || options.length === 0} className="flex-1 bg-indigo-600 text-white py-6 rounded-3xl font-black text-xl shadow-2xl flex items-center justify-center gap-3">
                                            {isSubmitting && <Icon name="loader-2" className="animate-spin" />}
                                            {isSubmitting ? 'Securing Intelligence...' : 'Secure in Logic Vault'}
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* CONNECTORS SIDEBAR */}
                        <aside className="space-y-8">
                            <div className="p-8 bg-slate-900 text-white rounded-[3rem] shadow-2xl sticky top-24">
                                <h3 className="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-8">Data Connectors</h3>
                                <div className="space-y-4">
                                    {connectorRegistry.map(conn => (
                                        <div key={conn.id} className={`flex items-center justify-between p-4 bg-white/5 rounded-2xl border transition ${connectedServices.includes(conn.id) ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-white/10'}`}>
                                            <div className="flex items-center gap-3">
                                                <div className="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white shadow-sm" style={{backgroundColor: conn.color}}>{conn.icon}</div>
                                                <div>
                                                    <div className="text-[10px] font-black tracking-tight">{conn.name}</div>
                                                    <div className="text-[8px] font-black uppercase text-slate-500">{connectedServices.includes(conn.id) ? 'Synced' : 'Inactive'}</div>
                                                </div>
                                            </div>
                                            <button onClick={() => toggleService(conn.id)} disabled={isConnecting === conn.id} className={`w-8 h-8 rounded-full flex items-center justify-center transition ${connectedServices.includes(conn.id) ? 'bg-emerald-500 shadow-md' : 'bg-white/10 hover:bg-white/20'}`}>
                                                {isConnecting === conn.id ? <Icon name="loader-2" size={12} className="animate-spin" /> : <Icon name={connectedServices.includes(conn.id) ? "check" : "plus"} size={14} />}
                                            </button>
                                        </div>
                                    ))}
                                </div>
                                <div className="mt-8 p-4 bg-indigo-500/10 rounded-2xl border border-indigo-500/20">
                                    <p className="text-[9px] text-indigo-200 leading-relaxed font-bold">Connecting services automatically verifies intelligence variables during strategic modeling.</p>
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
