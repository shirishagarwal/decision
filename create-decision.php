<?php
/**
 * File Path: create-decision.php
 * Description: Executive interface for strategic decision architecture.
 * Professionalized typography, formal language, and integrated governance.
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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .executive-card { background: white; border: 1px solid #e2e8f0; border-radius: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .step-indicator { border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; }
        .input-formal { width: 100%; padding: 1rem; border: 1px solid #e2e8f0; border-radius: 0.75rem; background: #fdfdfd; font-size: 0.875rem; outline: none; transition: all 0.2s; }
        .input-formal:focus { border-color: #6366f1; background: white; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.05); }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div id="root" class="flex-grow"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;

        const Icon = ({ name, size = 18, className = "" }) => {
            useEffect(() => { if (window.lucide) window.lucide.createIcons(); }, [name]);
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
            const [connectedServices, setConnectedServices] = useState(['stripe']);
            const [isConnecting, setIsConnecting] = useState(null);

            const connectorRegistry = [
                { id: 'stripe', name: 'Stripe', color: '#635BFF', description: 'Financial Data', icon: 'S' },
                { id: 'hubspot', name: 'HubSpot', color: '#FF7A59', description: 'CRM Pipeline', icon: 'H' },
                { id: 'salesforce', name: 'Salesforce', color: '#00A1E0', description: 'Sales Intel', icon: 'SF' },
                { id: 'linkedin', name: 'LinkedIn', color: '#0A66C2', description: 'Marketing Funnel', icon: 'in' }
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
                            stakeholders: stakeholders.split(',').map(s => s.trim()),
                            context_data: contextData,
                            active_connectors: connectedServices,
                            force_options: forceOptions
                        })
                    });
                    const data = await res.json();
                    
                    if (data.success) {
                        setGaps(data.gaps || []);
                        setAiPayload({ counterfactual: data.counterfactual, benchmark: data.benchmark });
                        const fetchedOptions = (data.options || []).map((o, idx) => ({ ...o, id: 'ai-' + idx + '-' + Date.now(), isAiGenerated: true }));
                        if (fetchedOptions.length > 0) setOptions(fetchedOptions);
                        if (forceOptions || (step === 2 && data.gaps.length === 0)) setStep(3);
                        else if (step === 1) setStep(2);
                    }
                } catch (e) { console.error(e); } finally { setIsAnalyzing(false); }
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
                    if (data.success) setConnectedServices(isConnected ? connectedServices.filter(s => s !== service) : [...connectedServices, service]);
                } catch (e) { setConnectedServices(isConnected ? connectedServices.filter(s => s !== service) : [...connectedServices, service]); }
                finally { setIsConnecting(null); }
            };

            const addManualOption = () => {
                setOptions([...options, { id: 'manual-' + Date.now(), name: '', description: '', confidence_interval: 'N/A', risk_score: 5, expected_value: 'TBD', isAiGenerated: false }]);
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
                        body: JSON.stringify({ title, problem, options: options.map(o => ({ name: o.name, description: o.description, isAiGenerated: o.isAiGenerated })), mode: 'create' })
                    });
                    const data = await res.json();
                    if (data.success) window.location.href = `/decision.php?id=${data.decision_id}`;
                } catch (e) { alert("Save process failed."); }
                finally { setIsSubmitting(false); }
            };

            return (
                <main className="max-w-7xl mx-auto py-12 px-6">
                    <header className="mb-10 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
                        <div>
                            <div className="flex items-center gap-2 mb-2">
                                <Icon name="shield" className="text-indigo-600" />
                                <span className="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Governance & Strategy Architecture</span>
                            </div>
                            <h1 className="text-4xl font-extrabold text-slate-900 tracking-tight">New Strategic Artifact</h1>
                        </div>
                        <div className="flex items-center gap-2">
                            {[1, 2, 3].map(s => (
                                <div key={s} className={`step-indicator ${step >= s ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-500'}`}>{s}</div>
                            ))}
                        </div>
                    </header>

                    <div className="grid lg:grid-cols-4 gap-8">
                        <div className="lg:col-span-3 space-y-8">
                            
                            {/* STEP 1: CONTEXT */}
                            {step === 1 && (
                                <div className="executive-card p-10 animate-in">
                                    <h2 className="text-xs font-black text-slate-900 uppercase tracking-widest mb-8 flex items-center gap-2">
                                        <Icon name="file-text" size={14} className="text-indigo-600" /> 01 • Rationale and Objectives
                                    </h2>
                                    <div className="space-y-6">
                                        <div>
                                            <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Decision Title</label>
                                            <input className="input-formal text-lg font-bold" placeholder="e.g. FY26 International Expansion Strategy" value={title} onChange={(e) => setTitle(e.target.value)} />
                                        </div>
                                        <div>
                                            <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Executive Problem Statement</label>
                                            <textarea className="input-formal h-32 font-medium" placeholder="Synthesize the core business problem and intended outcome..." value={problem} onChange={(e) => setProblem(e.target.value)} />
                                        </div>
                                        <div>
                                            <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Governance Stakeholders (Emails)</label>
                                            <input className="input-formal text-sm font-bold" placeholder="approver@corp.com, review@corp.com..." value={stakeholders} onChange={(e) => setStakeholders(e.target.value)} />
                                        </div>
                                        <button
                                            onClick={() => fetchIntelligence()}
                                            disabled={!title || isAnalyzing}
                                            className="w-full bg-slate-900 text-white py-4 rounded-xl font-bold text-sm uppercase tracking-widest shadow-lg hover:bg-indigo-600 transition-all flex items-center justify-center gap-3 disabled:opacity-50"
                                        >
                                            {isAnalyzing ? <Icon name="loader-2" className="animate-spin" /> : <Icon name="zap" size={14} />}
                                            {isAnalyzing ? 'Simulating Analysis...' : 'Synthesize Strategy'}
                                        </button>
                                    </div>
                                </div>
                            )}

                            {/* STEP 2: INTERVIEW */}
                            {step === 2 && (
                                <div className="executive-card p-10 animate-in">
                                    <div className="flex justify-between items-center mb-8">
                                        <h2 className="text-xs font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                            <Icon name="search" size={14} className="text-indigo-600" /> 02 • Risk Gaps & Information Requirements
                                        </h2>
                                        {isAnalyzing && <Icon name="loader-2" className="animate-spin text-indigo-600" />}
                                    </div>

                                    {gaps.length > 0 ? (
                                        <div className="space-y-8">
                                            <p className="text-lg font-semibold text-slate-900 leading-tight">Critical information gaps identified for high-confidence synthesis:</p>
                                            <div className="grid md:grid-cols-2 gap-4">
                                                {gaps.map((gap) => {
                                                    const isResolved = gap.suggested_connector && connectedServices.includes(gap.suggested_connector.toLowerCase());
                                                    return (
                                                        <div key={gap.key} className={`p-5 border rounded-xl transition-all ${isResolved ? 'bg-emerald-50/50 border-emerald-100' : 'bg-slate-50 border-slate-200'}`}>
                                                            <div className="flex justify-between items-start mb-3">
                                                                <div>
                                                                    <div className="font-bold text-slate-900 text-sm">{gap.label} {isResolved && '✓'}</div>
                                                                    <div className="text-[10px] text-slate-500 font-medium leading-tight mt-1">{gap.reason}</div>
                                                                </div>
                                                            </div>
                                                            <input className="w-full p-2.5 bg-white border border-slate-200 rounded-lg text-xs font-bold outline-none focus:border-indigo-600 disabled:opacity-50" value={contextData[gap.key] || ''} disabled={isResolved} onChange={(e) => setContextData({...contextData, [gap.key]: e.target.value})} placeholder={isResolved ? 'Verified via API' : 'Enter value...'} />
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                            <div className="flex gap-4 pt-4 border-t border-slate-100">
                                                <button onClick={() => setStep(1)} className="px-6 py-3 border border-slate-200 rounded-lg font-bold text-xs uppercase tracking-widest text-slate-400">Back</button>
                                                <button onClick={() => fetchIntelligence()} disabled={isAnalyzing} className="flex-1 bg-indigo-600 text-white py-3 rounded-lg font-bold text-xs uppercase tracking-widest shadow-md flex items-center justify-center gap-2">
                                                    Update Modeling
                                                </button>
                                            </div>
                                            <div className="text-center">
                                                <button onClick={() => fetchIntelligence(true)} disabled={isAnalyzing} className="text-[10px] font-black text-slate-300 uppercase tracking-widest hover:text-indigo-600 transition">Proceed with Speculative Modeling</button>
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="py-20 text-center">
                                            <Icon name="check-circle" size={40} className="text-emerald-500 mx-auto mb-6" />
                                            <h3 className="text-xl font-bold text-slate-900">Information Requirements Satisfied</h3>
                                            <button onClick={() => setStep(3)} className="mt-8 bg-slate-900 text-white px-10 py-3 rounded-lg font-bold uppercase tracking-widest text-xs">View Strategic Paths</button>
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* STEP 3: PATHS */}
                            {step === 3 && (
                                <div className="space-y-8 animate-in">
                                    <div className="flex justify-between items-center">
                                        <h2 className="text-xs font-black text-slate-900 uppercase tracking-widest">03 • Strategic Architecture</h2>
                                        <div className="flex gap-4">
                                            <button onClick={() => fetchIntelligence(true)} disabled={isAnalyzing} className="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2 hover:text-indigo-600">
                                                <Icon name="refresh-cw" size={12} className={isAnalyzing ? 'animate-spin' : ''} /> Refresh AI
                                            </button>
                                            <button onClick={addManualOption} className="text-[10px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2">
                                                <Icon name="plus" size={12} /> Add Path
                                            </button>
                                        </div>
                                    </div>

                                    {aiPayload.counterfactual && (
                                        <div className="p-6 bg-amber-50 border border-amber-200 rounded-xl">
                                            <div className="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-2 flex items-center gap-2">
                                                <Icon name="alert-triangle" size={12} /> Impact of Inaction (Status Quo Analysis)
                                            </div>
                                            <p className="text-xs text-amber-900 font-medium leading-relaxed italic">{aiPayload.counterfactual}</p>
                                        </div>
                                    )}

                                    <div className="space-y-6">
                                        {options.map((opt) => (
                                            <div key={opt.id} className="executive-card p-8 hover:border-indigo-200 transition-all group relative">
                                                <button onClick={() => removeOption(opt.id)} className="absolute top-6 right-6 text-slate-200 hover:text-red-500 transition"><Icon name="trash-2" size={16} /></button>
                                                <div className="flex justify-between items-start mb-6 mr-10">
                                                    <input className="text-2xl font-bold text-slate-900 bg-transparent border-b border-transparent focus:border-indigo-600 outline-none w-full tracking-tight" value={opt.name} placeholder="Strategic Path Title..." onChange={(e) => updateOption(opt.id, 'name', e.target.value)} />
                                                    {opt.isAiGenerated && (
                                                        <div className="text-right shrink-0 ml-4">
                                                            <div className="text-[8px] font-black text-emerald-500 uppercase">Probability</div>
                                                            <div className="text-sm font-black text-slate-900">{opt.confidence_interval || '80%'}</div>
                                                        </div>
                                                    )}
                                                </div>
                                                <textarea className="w-full bg-slate-50 p-4 rounded-xl text-slate-600 text-sm font-medium h-24 resize-none outline-none focus:bg-white border border-slate-100 transition" value={opt.description} placeholder="Describe the logical rationale..." onChange={(e) => updateOption(opt.id, 'description', e.target.value)} />
                                                
                                                {opt.isAiGenerated && (
                                                    <div className="grid grid-cols-3 gap-3 mt-6 pt-6 border-t border-slate-100">
                                                        <div className="p-3 bg-slate-50 rounded-lg">
                                                            <div className="text-[8px] font-bold text-slate-400 uppercase mb-1">Expected ROI</div>
                                                            <div className="text-[10px] font-black text-indigo-600">{opt.expected_value}</div>
                                                        </div>
                                                        <div className="p-3 bg-slate-50 rounded-lg">
                                                            <div className="text-[8px] font-bold text-slate-400 uppercase mb-1">Risk Quotient</div>
                                                            <div className="text-[10px] font-black text-red-500">{opt.risk_score}/10</div>
                                                        </div>
                                                        <div className="p-3 bg-slate-50 rounded-lg">
                                                            <div className="text-[8px] font-bold text-slate-400 uppercase mb-1">Sector Benchmark</div>
                                                            <div className="text-[10px] font-black text-slate-900 truncate">{opt.pattern_match || 'Strategic Fit'}</div>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        ))}
                                    </div>

                                    <div className="flex gap-4 pt-10">
                                       <button onClick={() => setStep(2)} className="px-8 py-4 border border-slate-200 rounded-lg font-bold text-xs uppercase tracking-widest text-slate-400">Back</button>
                                       <button onClick={saveDecision} disabled={isSubmitting || options.length === 0} className="flex-1 bg-indigo-600 text-white py-4 rounded-lg font-bold text-sm uppercase tracking-widest shadow-lg flex items-center justify-center gap-3">
                                            {isSubmitting && <Icon name="loader-2" className="animate-spin" />}
                                            {isSubmitting ? 'Architecting...' : 'Finalize Strategic Artifact'}
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* SIDEBAR */}
                        <aside className="space-y-6">
                            {aiPayload.benchmark && (
                                <div className="p-5 bg-indigo-600 rounded-xl text-white shadow-xl">
                                    <div className="text-[9px] font-black text-indigo-200 uppercase tracking-widest mb-2">Industry Insight</div>
                                    <p className="text-xs font-bold leading-snug">{aiPayload.benchmark}</p>
                                </div>
                            )}

                            <div className="p-6 bg-slate-900 text-white rounded-xl shadow-xl sticky top-24">
                                <h3 className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-6">Decision Governance</h3>
                                
                                <div className="space-y-6">
                                    <div>
                                        <div className="text-[8px] font-black text-slate-500 uppercase tracking-widest mb-3">Stakeholders</div>
                                        <div className="flex -space-x-1.5">
                                            {stakeholders.split(',').filter(s => s.trim()).map((s, i) => (
                                                <div key={i} className="w-7 h-7 rounded-full bg-indigo-500 border border-slate-900 flex items-center justify-center text-[9px] font-bold" title={s.trim()}>
                                                    {s.trim().charAt(0).toUpperCase()}
                                                </div>
                                            ))}
                                            <button onClick={() => setStep(1)} className="w-7 h-7 rounded-full bg-slate-800 border border-slate-900 flex items-center justify-center text-slate-400 hover:text-white transition">
                                                <Icon name="plus" size={10} />
                                            </button>
                                        </div>
                                    </div>

                                    <div className="space-y-3">
                                        <div className="text-[8px] font-black text-slate-500 uppercase tracking-widest">Active Connectors</div>
                                        {connectorRegistry.map(conn => (
                                            <div key={conn.id} className={`flex items-center justify-between p-3 rounded-lg border transition ${connectedServices.includes(conn.id) ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-white/5'}`}>
                                                <div className="flex items-center gap-2">
                                                    <div className="w-6 h-6 rounded flex items-center justify-center text-[9px] font-black text-white" style={{backgroundColor: conn.color}}>{conn.icon}</div>
                                                    <div className="text-[10px] font-bold">{conn.name}</div>
                                                </div>
                                                <button onClick={() => toggleService(conn.id)} disabled={isConnecting === conn.id} className="text-slate-400 hover:text-white">
                                                    <Icon name={connectedServices.includes(conn.id) ? "check" : "link"} size={12} className={connectedServices.includes(conn.id) ? 'text-emerald-500' : ''} />
                                                </button>
                                            </div>
                                        ))}
                                    </div>
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
