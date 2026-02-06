import React, { useState, useEffect } from 'react';
import {
  Shield,
  ChevronRight,
  Plus,
  Trash2,
  Loader2,
  Database,
  Zap,
  AlertCircle,
  Link as LinkIcon
} from 'lucide-react';

export default function App() {
  const [step, setStep] = useState(1);
  const [title, setTitle] = useState('');
  const [problem, setProblem] = useState('');
  const [gaps, setGaps] = useState([]);
  const [contextData, setContextData] = useState({});
  const [options, setOptions] = useState([]);
  const [isAnalyzing, setIsAnalyzing] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Analyze Context Gaps
  const analyzeContext = async () => {
    if (title.length < 5) return;
    setIsAnalyzing(true);
    try {
      const res = await fetch('/api/ai-strategy.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title, problem_statement: problem, context_data: contextData })
      });
      const data = await res.json();
      setGaps(data.gaps || []);
      setOptions(data.options || []);
    } catch (e) {
      console.error("Analysis failed");
    } finally {
      setIsAnalyzing(false);
    }
  };

  const handleNext = () => {
    if (step === 1) {
      analyzeContext();
      setStep(2);
    } else if (step === 2) {
      setStep(3);
    }
  };

  const saveDecision = async () => {
    setIsSubmitting(true);
    // Standard save logic to api/create-decision.php
    // ...
    setIsSubmitting(false);
  };

  return (
    <div className="min-h-screen bg-[#fcfcfd] p-6 lg:p-12 font-['Inter']">
      <div className="max-w-6xl mx-auto">
        <header className="mb-12">
          <div className="flex items-center gap-2 mb-4">
            <Shield className="text-indigo-600 w-6 h-6" />
            <span className="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-600">Decision Intelligence OS</span>
          </div>
          <h1 className="text-5xl font-black text-slate-900 tracking-tighter">Record Strategic Logic</h1>
        </header>

        <div className="grid lg:grid-cols-3 gap-12">
          <div className="lg:col-span-2 space-y-8">
            
            {/* STEP 1: TITLE & PROBLEM */}
            {step === 1 && (
              <div className="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/50 animate-in fade-in slide-in-from-bottom-4">
                <h2 className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8">01 • Narrative Context</h2>
                <div className="space-y-8">
                  <div>
                    <label className="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">The Decision Title</label>
                    <input
                      className="w-full p-6 bg-slate-50 border-2 border-transparent rounded-3xl text-2xl font-black outline-none focus:border-indigo-600 focus:bg-white transition-all"
                      placeholder="e.g. Hire VP of Sales"
                      value={title}
                      onChange={(e) => setTitle(e.target.value)}
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Problem Statement</label>
                    <textarea
                      className="w-full p-6 bg-slate-50 border-2 border-transparent rounded-3xl h-40 font-medium outline-none focus:border-indigo-600 focus:bg-white transition-all"
                      placeholder="What is the ground truth driving this decision?"
                      value={problem}
                      onChange={(e) => setProblem(e.target.value)}
                    />
                  </div>
                  <button
                    onClick={handleNext}
                    disabled={!title}
                    className="w-full bg-slate-900 text-white py-6 rounded-2xl font-black text-lg shadow-2xl hover:bg-indigo-600 transition-all disabled:opacity-50"
                  >
                    Analyze Intelligence Gaps
                  </button>
                </div>
              </div>
            )}

            {/* STEP 2: CONTEXT INTERVIEW (The Solution to your query) */}
            {step === 2 && (
              <div className="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/50 animate-in fade-in slide-in-from-bottom-4">
                <div className="flex justify-between items-center mb-10">
                  <h2 className="text-[10px] font-black text-indigo-600 uppercase tracking-widest">02 • The Intelligence Interview</h2>
                  {isAnalyzing && <Loader2 className="animate-spin text-indigo-600 w-5 h-5" />}
                </div>

                {gaps.length > 0 ? (
                  <div className="space-y-8">
                    <p className="text-xl font-bold text-slate-900 leading-tight">
                      To provide a high-confidence recommendation, I need the following variables:
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
                              <button className="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-600 text-[10px] font-black rounded-full uppercase tracking-tighter hover:bg-indigo-100 transition">
                                <LinkIcon size={12} /> Connect {gap.suggested_connector}
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
                    <button
                      onClick={handleNext}
                      className="w-full bg-indigo-600 text-white py-6 rounded-2xl font-black text-lg shadow-xl hover:bg-indigo-700 transition-all"
                    >
                      Update Recommendations
                    </button>
                  </div>
                ) : (
                  <div className="py-20 text-center">
                    <Zap className="w-12 h-12 text-indigo-600 mx-auto mb-6 animate-pulse" />
                    <h3 className="text-2xl font-black text-slate-900">Intelligence Baseline Met</h3>
                    <p className="text-slate-500 font-medium mt-2">I have enough context to generate high-fidelity paths.</p>
                    <button onClick={() => setStep(3)} className="mt-8 bg-slate-900 text-white px-10 py-4 rounded-xl font-black uppercase tracking-widest text-xs">View Options</button>
                  </div>
                )}
              </div>
            )}

            {/* STEP 3: OPTIONS PREVIEW */}
            {step === 3 && (
              <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4">
                <h2 className="text-[10px] font-black text-slate-400 uppercase tracking-widest">03 • High-Fidelity Strategic Paths</h2>
                {options.map((opt, i) => (
                  <div key={i} className="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm hover:border-indigo-300 transition-all group">
                    <div className="flex justify-between items-start mb-6">
                      <h3 className="text-3xl font-black text-slate-900 group-hover:text-indigo-600 transition tracking-tighter">{opt.name}</h3>
                      <div className="bg-emerald-50 text-emerald-600 text-[10px] font-black px-3 py-1.5 rounded-full uppercase">
                        {opt.confidence}% Confidence
                      </div>
                    </div>
                    <p className="text-slate-500 font-medium leading-relaxed mb-8">{opt.description}</p>
                    <button className="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:text-indigo-800">Select This Path</button>
                  </div>
                ))}
                <div className="flex gap-4 pt-10">
                   <button onClick={() => setStep(2)} className="px-10 py-5 bg-white border border-slate-100 rounded-2xl font-black text-slate-400 text-sm">Back</button>
                   <button onClick={saveDecision} className="flex-1 bg-indigo-600 text-white py-5 rounded-2xl font-black text-xl shadow-2xl">Finalize Logic Vault</button>
                </div>
              </div>
            )}
          </div>

          {/* SIDEBAR: ACTIVE CONNECTORS */}
          <aside className="space-y-8">
            <div className="p-8 bg-slate-900 text-white rounded-[2.5rem] shadow-2xl">
              <h3 className="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-8">Active Context Sources</h3>
              <div className="space-y-4">
                <div className="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10">
                  <div className="flex items-center gap-3">
                    <div className="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center font-bold">S</div>
                    <div className="text-xs font-bold">Stripe</div>
                  </div>
                  <div className="w-2 h-2 rounded-full bg-emerald-500"></div>
                </div>
                <div className="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10 opacity-50 grayscale">
                  <div className="flex items-center gap-3">
                    <div className="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center font-bold text-white">H</div>
                    <div className="text-xs font-bold">HubSpot</div>
                  </div>
                  <div className="text-[8px] font-black uppercase">Disconnected</div>
                </div>
              </div>
              <p className="text-[10px] text-slate-500 mt-8 leading-relaxed font-medium">
                Connect your business stack to automatically resolve Intelligence Gaps.
              </p>
            </div>
          </aside>
        </div>
      </div>
    </div>
  );
}
