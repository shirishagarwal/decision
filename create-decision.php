<?php
/**
 * File Path: create-decision.php
 * Description: High-fidelity React interface for creating strategic decisions.
 * Wraps the JSX logic in a PHP shell with React/Babel/Lucide CDNs.
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

        // Simple Lucide Icon Component for Browser Environment
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
            
            // Mock Connector State
            const [connectedServices, setConnectedServices] = useState(['stripe']); // Stripe is active by default
            const [isConnecting, setIsConnecting] = useState(null); // 'stripe' or 'hubspot'

            const analyzeContext = async () => {
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
                            active_connectors: connectedServices
                        })
                    });
                    const data = await res.json();
                    setGaps(data.gaps || []);
                    setOptions(data.options || []);
                    setStep(2); // Only move to step 2 after we have results
                } catch (e) {
                    console.error("Analysis failed", e);
                    alert("The AI Intelligence engine is currently unavailable. Please try again in a moment.");
                } finally {
                    setIsAnalyzing(false);
                }
            };

            const toggleService = (service) => {
                if (connectedServices.includes(service)) {
                    setConnectedServices(connectedServices.filter(s => s !== service));
                } else {
                    setIsConnecting(service);
                    // Simulate OAuth delay
                    setTimeout(() => {
                        setConnectedServices([...connectedServices, service]);
                        setIsConnecting(null);
                    }, 1500);
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
                            options: options.map(o => ({
                                name: o.name,
                                description: o.description,
                                isAiGenerated: true
                            })),
                            mode: 'create'
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        window.location.href = `/decision.php?id=${data.decision_id}`;
                    } else {
                        alert(data.error || "Save failed");
                    }
                } catch (e) {
                    alert("Submission error. Please check your connection.");
                } finally {
                    setIsSubmitting(false);
                }
            };

            return (
                <main className="max-w-7xl mx-auto py-12 px-6">
                    <header className="mb-12">
                        <div className="flex items-center gap-2 mb-4">
                            <Icon name="shield" className="text-indigo-600" />
                            <span className="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-600">Decision Intelligence OS</span>
                        </div>
                        <h1 className="text-5xl font-black text-slate-900 tracking-tighter">Record Strategic Logic</h1>
                    </header>

                    <div className="grid lg:grid-cols-3 gap-12">
                        <div className="lg:col-span-2 space-y-8">
                            
                            {/* STEP 1: TITLE & PROBLEM */}
                            {step === 1 && (
                                <div className="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/50 animate-in">
                                    <div className="flex justify-between items-center mb-8">
                                        <h2 className="text-[10px] font-black text-slate-400 uppercase tracking-widest">01 • Narrative Context</h2>
                                        {isAnalyzing && <span className="text-[10px] font-black text-indigo-600 uppercase tracking-widest loader-dots">Analyzing Failure Vectors</span>}
                                    </div>
                                    <div className="space-y-8">
                                        <div>
                                            <label className="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">The Decision Title</label>
                                            <input
                                                className="w-full p-6 bg-slate-50 border-2 border-transparent rounded-3xl text-2xl font-black outline-none focus:border-indigo-600 focus:bg-white transition-all"
                                                placeholder="e.g. Hire VP of Sales"
                                                value={title}
                                                disabled={isAnalyzing}
                                                onChange={(e) => setTitle(e.target.value)}
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Problem Statement</label>
                                            <textarea
                                                className="w-full p-6 bg-slate-50 border-2 border-transparent rounded-3xl h-40 font-medium outline-none focus:border-indigo-600 focus:bg-white transition-all"
                                                placeholder="What is the ground truth driving this decision?"
                                                value={problem}
                                                disabled={isAnalyzing}
                                                onChange={(e) => setProblem(e.target.value)}
                                            />
                                        </div>
                                        <button
                                            onClick={analyzeContext}
                                            disabled={!title || title.length < 5 || isAnalyzing}
                                            className="w-full bg-slate-900 text-white py-6 rounded-2xl font-black text-lg shadow-2xl hover:bg-indigo-600 transition-all disabled:opacity-50 flex items-center justify-center gap-3"
                                        >
                                            {isAnalyzing ? <Icon name="loader-2" className="animate-spin" /> : null}
                                            {isAnalyzing ? 'Scanning External Failures...' : 'Analyze Intelligence Gaps'}
                                        </button>
                                    </div>
                                </div>
                            )}

                            {/* STEP 2: CONTEXT INTERVIEW */}
                            {step === 2 && (
                                <div className="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/50 animate-in">
                                    <div className="flex justify-between items-center mb-10">
                                        <h2 className="text-[10px] font-black text-indigo-600 uppercase tracking-widest">02 • The Intelligence Interview</h2>
                                        {isAnalyzing && <Icon name="loader-2" className="animate-spin text-indigo-600" />}
                                    </div>

                                    {!isAnalyzing && gaps.length > 0 ? (
                                        <div className="space-y-8">
                                            <p className="text-xl font-bold text-slate-900 leading-tight">
                                                To provide a high-confidence recommendation, the AI requires the following variables:
                                            </p>
                                            <div className="space-y-4">
                                                {gaps.map((gap) => (
                                                    <div key={gap.key} className="p-6 bg-slate-50 border border-slate-100 rounded-3xl group hover:border-indigo-200 transition-all">
                                                        <div className="flex justify-between items-start mb-4">
                                                            <div>
                                                                <div className="font-black text-slate-900 mb-1">{gap.label}</div>
                                                                <div className="text-xs text-slate-500 font-medium">{gap.reason}</div>
                                                            </div>
                                                            {gap.suggested_connector && (
                                                                <button
                                                                    onClick={() => toggleService(gap.suggested_connector.toLowerCase())}
                                                                    className={`flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-tighter transition ${connectedServices.includes(gap.suggested_connector.toLowerCase()) ? 'bg-emerald-50 text-emerald-600' : 'bg-indigo-50 text-indigo-600 hover:bg-indigo-100'}`}
                                                                >
                                                                    <Icon name={connectedServices.includes(gap.suggested_connector.toLowerCase()) ? "check" : "link"} size={12} />
                                                                    {connectedServices.includes(gap.suggested_connector.toLowerCase()) ? "Linked" : `Connect ${gap.suggested_connector}`}
                                                                </button>
                                                            )}
                                                        </div>
                                                        <input
                                                            className="w-full p-4 bg-white border border-slate-200 rounded-2xl outline-none focus:border-indigo-600 font-bold"
                                                            placeholder={`Enter ${gap.label}...`}
                                                            value={contextData[gap.key] || ''}
                                                            onChange={(e) => setContextData({...contextData, [gap.key]: e.target.value})}
                                                        />
                                                    </div>
                                                ))}
                                            </div>
                                            <div className="flex gap-4">
                                                <button onClick={() => setStep(1)} className="px-10 py-5 bg-white border border-slate-100 rounded-2xl font-black text-slate-400 text-sm hover:bg-slate-50 transition">Back</button>
                                                <button
                                                    onClick={analyzeContext}
                                                    className="flex-1 bg-indigo-600 text-white py-6 rounded-2xl font-black text-lg shadow-xl hover:bg-indigo-700 transition-all flex items-center justify-center gap-3"
                                                >
                                                    {isAnalyzing ? <Icon name="loader-2" className="animate-spin" /> : <Icon name="zap" />}
                                                    Update Recommendations
                                                </button>
                                            </div>
                                            <div className="text-center">
                                                <button onClick={() => setStep(3)} className="text-xs font-bold text-slate-400 hover:text-indigo-600 uppercase tracking-widest transition">Skip and view speculative options</button>
                                            </div>
                                        </div>
                                    ) : !isAnalyzing && (
                                        <div className="py-20 text-center">
                                            <Icon name="zap" size={48} className="text-indigo-600 mx-auto mb-6 animate-pulse" />
                                            <h3 className="text-2xl font-black text-slate-900">Intelligence Baseline Met</h3>
                                            <p className="text-slate-500 font-medium mt-2">The AI has verified your current data sources and context as sufficient.</p>
                                            <button onClick={() => setStep(3)} className="mt-8 bg-slate-900 text-white px-10 py-4 rounded-xl font-black uppercase tracking-widest text-xs">View Options</button>
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* STEP 3: OPTIONS PREVIEW */}
                            {step === 3 && (
                                <div className="space-y-6 animate-in">
                                    <h2 className="text-[10px] font-black text-slate-400 uppercase tracking-widest">03 • High-Fidelity Strategic Paths</h2>
                                    {options.length > 0 ? options.map((opt, i) => (
                                        <div key={i} className="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm hover:border-indigo-300 transition-all group">
                                            <div className="flex justify-between items-start mb-6">
                                                <h3 className="text-3xl font-black text-slate-900 group-hover:text-indigo-600 transition tracking-tighter">{opt.name}</h3>
                                                <div className="bg-emerald-50 text-emerald-600 text-[10px] font-black px-3 py-1.5 rounded-full uppercase">
                                                    {opt.confidence}% Confidence
                                                </div>
                                            </div>
                                            <p className="text-slate-500 font-medium leading-relaxed mb-8">{opt.description}</p>
                                            <button onClick={() => saveDecision()} className="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:text-indigo-800">Select This Path</button>
                                        </div>
                                    )) : (
                                        <div className="p-20 text-center bg-white rounded-[3rem] border border-dashed border-slate-200">
                                            <p className="text-slate-400 font-bold uppercase tracking-widest text-xs">Generating speculative paths based on limited data...</p>
                                        </div>
                                    )}
                                    <div className="flex gap-4 pt-10">
                                       <button onClick={() => setStep(2)} className="px-10 py-5 bg-white border border-slate-100 rounded-2xl font-black text-slate-400 text-sm hover:bg-slate-50 transition">Back</button>
                                       <button
                                            onClick={() => saveDecision()}
                                            disabled={isSubmitting}
                                            className="flex-1 bg-indigo-600 text-white py-5 rounded-2xl font-black text-xl shadow-2xl hover:bg-indigo-700 transition disabled:opacity-50 flex items-center justify-center gap-3"
                                        >
                                            {isSubmitting && <Icon name="loader-2" className="animate-spin" />}
                                            {isSubmitting ? 'Securing Logic...' : 'Finalize Logic Vault'}
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* SIDEBAR: ACTIVE CONNECTORS */}
                        <aside className="space-y-8">
                            <div className="p-8 bg-slate-900 text-white rounded-[2.5rem] shadow-2xl sticky top-24">
                                <h3 className="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-8">Intelligence Data Connectors</h3>
                                <div className="space-y-4">
                                    {/* Stripe Connector */}
                                    <div className={`flex items-center justify-between p-4 bg-white/5 rounded-2xl border transition-all ${connectedServices.includes('stripe') ? 'border-emerald-500/30' : 'border-white/10'}`}>
                                        <div className="flex items-center gap-3">
                                            <div className="w-8 h-8 bg-[#635BFF] rounded-lg flex items-center justify-center font-bold text-white">S</div>
                                            <div>
                                                <div className="text-xs font-bold">Stripe</div>
                                                <div className="text-[8px] font-black uppercase text-slate-500">Revenue & Burn</div>
                                            </div>
                                        </div>
                                        <button
                                            onClick={() => toggleService('stripe')}
                                            className={`w-6 h-6 rounded-full flex items-center justify-center transition ${connectedServices.includes('stripe') ? 'bg-emerald-500' : 'bg-white/10 hover:bg-white/20'}`}
                                        >
                                            {isConnecting === 'stripe' ? <Icon name="loader-2" size={12} className="animate-spin" /> : <Icon name={connectedServices.includes('stripe') ? "check" : "plus"} size={12} />}
                                        </button>
                                    </div>

                                    {/* HubSpot Connector */}
                                    <div className={`flex items-center justify-between p-4 bg-white/5 rounded-2xl border transition-all ${connectedServices.includes('hubspot') ? 'border-emerald-500/30' : 'border-white/10'}`}>
                                        <div className="flex items-center gap-3">
                                            <div className="w-8 h-8 bg-[#FF7A59] rounded-lg flex items-center justify-center font-bold text-white">H</div>
                                            <div>
                                                <div className="text-xs font-bold">HubSpot</div>
                                                <div className="text-[8px] font-black uppercase text-slate-500">Pipeline & CRM</div>
                                            </div>
                                        </div>
                                        <button
                                            onClick={() => toggleService('hubspot')}
                                            className={`w-6 h-6 rounded-full flex items-center justify-center transition ${connectedServices.includes('hubspot') ? 'bg-emerald-500' : 'bg-white/10 hover:bg-white/20'}`}
                                        >
                                            {isConnecting === 'hubspot' ? <Icon name="loader-2" size={12} className="animate-spin" /> : <Icon name={connectedServices.includes('hubspot') ? "check" : "plus"} size={12} />}
                                        </button>
                                    </div>
                                </div>
                                <div className="mt-8 p-4 bg-indigo-500/10 rounded-2xl">
                                    <p className="text-[10px] text-indigo-200 leading-relaxed font-bold">
                                        <Icon name="info" size={12} className="inline mr-1 mb-0.5" />
                                        Linked services automatically populate strategic variables during the Intelligence Interview.
                                    </p>
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
