<?php
/**
 * File Path: create-decision.php
 * Description: Fixes the "stuck at recording" issue by handling the redirect more explicitly.
 */
require_once __DIR__ . '/config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Decision | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50">
    <div id="root"></div>
    <script type="text/babel">
        const { useState, useEffect } = React;

        function App() {
            const [step, setStep] = useState(1);
            const [title, setTitle] = useState('');
            const [problem, setProblem] = useState('');
            const [options, setOptions] = useState([{ id: 1, name: '', description: '' }]);
            const [isSubmitting, setIsSubmitting] = useState(false);

            const handleSubmit = async () => {
                if (!title) return alert("Title required.");
                setIsSubmitting(true);
                
                try {
                    const res = await fetch('/api/create-decision.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ title, problem, options })
                    });
                    
                    const text = await res.text();
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error("Malformed JSON response:", text);
                        throw new Error("Server returned an invalid response.");
                    }

                    if (data.success) {
                        // FORCE REDIRECT
                        window.location.replace(`/decision.php?id=${data.decision_id}`);
                    } else {
                        alert(data.error || "Unknown error occurred.");
                        setIsSubmitting(false);
                    }
                } catch (e) {
                    alert("Submission failed: " + e.message);
                    setIsSubmitting(false);
                }
            };

            return (
                <div className="max-w-4xl mx-auto py-20 px-6">
                    <h1 className="text-4xl font-black mb-8">New Strategic Recording</h1>
                    <div className="bg-white p-10 rounded-[40px] border shadow-sm space-y-8">
                        {step === 1 ? (
                            <div className="space-y-6">
                                <input className="w-full p-5 border-2 rounded-2xl text-xl font-bold" placeholder="Decision Title" value={title} onChange={e => setTitle(e.target.value)} />
                                <textarea className="w-full p-5 border-2 rounded-2xl h-32" placeholder="Context/Problem" value={problem} onChange={e => setProblem(e.target.value)} />
                                <button onClick={() => setStep(2)} className="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black">Next Step →</button>
                            </div>
                        ) : (
                            <div className="space-y-6">
                                {options.map((opt, i) => (
                                    <div key={opt.id} className="p-6 bg-gray-50 rounded-2xl space-y-4">
                                        <input className="w-full p-3 border rounded-xl font-bold" placeholder="Option Name" value={opt.name} onChange={e => {
                                            const newOpts = [...options];
                                            newOpts[i].name = e.target.value;
                                            setOptions(newOpts);
                                        }} />
                                    </div>
                                ))}
                                <div className="flex gap-4">
                                    <button onClick={() => setStep(1)} className="px-8 py-4 border-2 rounded-2xl font-bold text-gray-400">Back</button>
                                    <button onClick={handleSubmit} disabled={isSubmitting} className="flex-1 bg-indigo-600 text-white py-4 rounded-2xl font-black">
                                        {isSubmitting ? 'Finalizing Strategy...' : 'Document Strategy'}
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            );
        }

        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<App />);
    </script>
</body>
</html>
