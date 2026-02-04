<?php
require_once __DIR__ . '/config.php';

if (!isLoggedIn()) {
    redirect(APP_URL . '/index.php');
}

$user = getCurrentUser();

// Get user's first workspace
$pdo = getDbConnection();
$stmt = $pdo->prepare("
    SELECT w.* FROM workspaces w
    INNER JOIN workspace_members wm ON w.id = wm.workspace_id
    WHERE wm.user_id = ?
    ORDER BY w.created_at DESC
    LIMIT 1
");
$stmt->execute([$user['id']]);
$workspace = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(147, 51, 234, 0.3); }
            50% { box-shadow: 0 0 30px rgba(147, 51, 234, 0.5); }
        }
        .ai-glow { animation: pulse-glow 2s ease-in-out infinite; }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .slide-in { animation: slideIn 0.3s ease-out; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-purple-50 to-pink-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="dashboard.php" class="text-gray-600 hover:text-gray-900">← Back to Dashboard</a>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center text-white">
                        📋
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900"><?php echo APP_NAME; ?></h1>
                        <p class="text-xs text-gray-500">AI Assistant</p>
                    </div>
                </div>
                <img 
                    src="<?php echo e($user['avatar_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['name'])); ?>" 
                    alt="<?php echo e($user['name']); ?>"
                    class="w-10 h-10 rounded-full border-2 border-gray-200"
                >
            </div>
        </div>
    </nav>

    <div id="app-root"></div>

    <script type="text/babel">
        const { useState, useRef, useEffect } = React;

        const APP_CONFIG = {
            apiUrl: '<?php echo APP_URL; ?>',
            userId: <?php echo $user['id']; ?>,
            workspaceId: <?php echo $workspace['id']; ?>
        };

        // Icons (simplified versions)
        const Sparkles = () => <span className="text-2xl">✨</span>;
        const Brain = () => <span className="text-2xl">🧠</span>;
        const Lightbulb = () => <span className="text-2xl">💡</span>;

        function AIAssistant() {
            const [step, setStep] = useState('situation');
            const [userInput, setUserInput] = useState('');
            const [conversationHistory, setConversationHistory] = useState([]);
            const [isAiThinking, setIsAiThinking] = useState(false);
            const [discoveredProblem, setDiscoveredProblem] = useState(null);
            const [generatedOptions, setGeneratedOptions] = useState([]);
            const [selectedOption, setSelectedOption] = useState(null);
            const chatEndRef = useRef(null);

            useEffect(() => {
                chatEndRef.current?.scrollIntoView({ behavior: 'smooth' });
            }, [conversationHistory, isAiThinking]);

            const sendMessage = async () => {
                if (!userInput.trim() || isAiThinking) return;

                const message = userInput;
                setUserInput('');
                setIsAiThinking(true);

                // Add user message to history
                const newHistory = [
                    ...conversationHistory,
                    { role: 'user', content: message }
                ];
                setConversationHistory(newHistory);

                try {
                    const response = await fetch(`${APP_CONFIG.apiUrl}/api/ai-chat.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            message: message,
                            history: newHistory,
                            step: step
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        setConversationHistory([
                            ...newHistory,
                            { role: 'ai', content: data.message }
                        ]);

                        // Check if AI discovered the problem
                        if (step === 'situation' && data.message.toLowerCase().includes('decision') && 
                            data.message.toLowerCase().includes('constraint')) {
                            // Parse problem from AI message
                            setTimeout(() => {
                                if (confirm('Has the AI helped you identify the core problem? Click OK to move to generating options.')) {
                                    const problemText = prompt('Enter the refined problem statement:', '');
                                    if (problemText) {
                                        setDiscoveredProblem({
                                            refined: problemText,
                                            context: [],
                                            constraints: []
                                        });
                                        setStep('options');
                                    }
                                }
                            }, 1000);
                        }

                        // Parse generated options if available
                        if (data.parsed_options && Array.isArray(data.parsed_options)) {
                            setGeneratedOptions(data.parsed_options);
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    setConversationHistory([
                        ...newHistory,
                        { role: 'ai', content: 'Sorry, I encountered an error. Please try again.' }
                    ]);
                }

                setIsAiThinking(false);
            };

            const generateOptions = async () => {
                setIsAiThinking(true);
                setStep('options');

                const optionsPrompt = `Based on this problem: "${discoveredProblem.refined}", generate 4-5 comprehensive decision options. For each option, provide: name, summary, estimated cost, 5-7 pros, 5-7 cons, feasibility (Very High/High/Medium/Low), and your AI recommendation. Format as a JSON array.`;

                try {
                    const response = await fetch(`${APP_CONFIG.apiUrl}/api/ai-chat.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            message: optionsPrompt,
                            history: conversationHistory,
                            step: 'options'
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        setConversationHistory([
                            ...conversationHistory,
                            { role: 'user', content: 'Generate options' },
                            { role: 'ai', content: data.message }
                        ]);

                        if (data.parsed_options) {
                            setGeneratedOptions(data.parsed_options);
                        } else {
                            // Manual parsing fallback
                            alert('Options generated! Please review the AI response above.');
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                }

                setIsAiThinking(false);
            };

            const saveDecision = async () => {
                if (!selectedOption) {
                    alert('Please select an option first');
                    return;
                }

                try {
                    const response = await fetch(`${APP_CONFIG.apiUrl}/api/decisions.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            workspace_id: APP_CONFIG.workspaceId,
                            title: discoveredProblem.refined,
                            problem_statement: discoveredProblem.refined,
                            final_decision: selectedOption.name,
                            rationale: selectedOption.aiInsight || selectedOption.summary,
                            category: 'Other',
                            status: 'Decided',
                            confidence_level: 4,
                            ai_generated: true,
                            decided_at: new Date().toISOString().split('T')[0],
                            options: generatedOptions.map(opt => ({
                                name: opt.name,
                                summary: opt.summary,
                                estimated_cost: opt.estimatedCost,
                                pros: opt.pros || [],
                                cons: opt.cons || [],
                                chosen: opt.name === selectedOption.name
                            }))
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('Decision saved successfully!');
                        window.location.href = `${APP_CONFIG.apiUrl}/dashboard.php`;
                    } else {
                        alert('Error saving decision: ' + (data.error || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Failed to save decision');
                }
            };

            return (
                <div className="max-w-4xl mx-auto px-6 py-8">
                    {/* Header */}
                    <div className="text-center mb-8">
                        <div className="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl ai-glow mb-4">
                            <Brain />
                        </div>
                        <h1 className="text-4xl font-bold text-gray-900 mb-2">AI Decision Assistant</h1>
                        <p className="text-lg text-gray-600">Let's discover what you really need to decide</p>
                    </div>

                    {/* Progress */}
                    <div className="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                        <div className="flex items-center justify-between">
                            <div className={`flex items-center gap-3 ${step === 'situation' ? 'opacity-100' : 'opacity-50'}`}>
                                <div className={`w-10 h-10 rounded-full flex items-center justify-center ${
                                    step === 'situation' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-600'
                                }`}>1</div>
                                <div className="text-sm font-semibold">Define Problem</div>
                            </div>
                            <div className="flex-1 h-0.5 bg-gray-200 mx-4" />
                            <div className={`flex items-center gap-3 ${step === 'options' ? 'opacity-100' : 'opacity-50'}`}>
                                <div className={`w-10 h-10 rounded-full flex items-center justify-center ${
                                    step === 'options' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-600'
                                }`}>2</div>
                                <div className="text-sm font-semibold">Generate Options</div>
                            </div>
                            <div className="flex-1 h-0.5 bg-gray-200 mx-4" />
                            <div className={`flex items-center gap-3 ${step === 'decide' ? 'opacity-100' : 'opacity-50'}`}>
                                <div className={`w-10 h-10 rounded-full flex items-center justify-center ${
                                    step === 'decide' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-600'
                                }`}>3</div>
                                <div className="text-sm font-semibold">Decide</div>
                            </div>
                        </div>
                    </div>

                    {/* Chat Area */}
                    <div className="bg-white rounded-xl border border-gray-200 min-h-[500px] max-h-[600px] overflow-y-auto p-6 mb-6">
                        {conversationHistory.length === 0 ? (
                            <div className="text-center py-12">
                                <Lightbulb />
                                <h3 className="text-xl font-bold text-gray-900 mt-4 mb-2">What's on your mind?</h3>
                                <p className="text-gray-600 mb-6">Describe your situation and I'll help you identify the core decision.</p>
                                <div className="flex flex-wrap gap-2 justify-center">
                                    <button onClick={() => setUserInput("We want to plan a vacation with 4 friends but can't decide where to go")}
                                        className="px-4 py-2 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 text-sm">
                                        🏖️ Plan vacation
                                    </button>
                                    <button onClick={() => setUserInput("Need to choose CRM software for our sales team")}
                                        className="px-4 py-2 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 text-sm">
                                        💼 Choose software
                                    </button>
                                    <button onClick={() => setUserInput("Should we hire a senior or junior developer?")}
                                        className="px-4 py-2 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 text-sm">
                                        👥 Hiring decision
                                    </button>
                                </div>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {conversationHistory.map((msg, idx) => (
                                    <div key={idx} className={`flex ${msg.role === 'user' ? 'justify-end' : 'justify-start'}`}>
                                        <div className={`max-w-[80%] p-4 rounded-2xl ${
                                            msg.role === 'user' 
                                                ? 'bg-indigo-600 text-white' 
                                                : 'bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-100'
                                        }`}>
                                            {msg.role === 'ai' && <div className="flex items-center gap-2 mb-2 text-purple-700 font-semibold text-sm">
                                                <Sparkles /> AI Assistant
                                            </div>}
                                            <div className="text-sm whitespace-pre-wrap">{msg.content}</div>
                                        </div>
                                    </div>
                                ))}
                                {isAiThinking && (
                                    <div className="flex justify-start">
                                        <div className="bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-100 p-4 rounded-2xl">
                                            <div className="flex gap-2 items-center text-purple-700">
                                                <div className="flex gap-1">
                                                    <div className="w-2 h-2 bg-purple-600 rounded-full animate-bounce"></div>
                                                    <div className="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style={{animationDelay: '0.1s'}}></div>
                                                    <div className="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style={{animationDelay: '0.2s'}}></div>
                                                </div>
                                                <span className="text-sm font-medium">AI is thinking...</span>
                                            </div>
                                        </div>
                                    </div>
                                )}
                                <div ref={chatEndRef} />
                            </div>
                        )}
                    </div>

                    {/* Problem Defined */}
                    {discoveredProblem && (
                        <div className="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl border-2 border-emerald-200 p-6 mb-6">
                            <h3 className="font-bold text-emerald-900 mb-2 flex items-center gap-2">
                                <span className="text-2xl">✓</span> Problem Defined!
                            </h3>
                            <p className="text-gray-900 font-medium mb-4">{discoveredProblem.refined}</p>
                            <button 
                                onClick={generateOptions}
                                disabled={isAiThinking}
                                className="w-full px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-semibold disabled:opacity-50"
                            >
                                <Sparkles /> Generate Options with AI
                            </button>
                        </div>
                    )}

                    {/* Generated Options */}
                    {generatedOptions.length > 0 && (
                        <div className="space-y-4 mb-6">
                            <h3 className="text-xl font-bold text-gray-900">AI Generated Options</h3>
                            {generatedOptions.map((opt, idx) => (
                                <div key={idx} 
                                    onClick={() => setSelectedOption(opt)}
                                    className={`bg-white border-2 rounded-xl p-6 cursor-pointer transition-all ${
                                        selectedOption?.name === opt.name ? 'border-purple-500 shadow-lg' : 'border-gray-200 hover:border-purple-300'
                                    }`}>
                                    <h4 className="text-lg font-bold text-gray-900 mb-2">{opt.name}</h4>
                                    <p className="text-sm text-gray-600 mb-4">{opt.summary || opt.description}</p>
                                    {opt.estimatedCost && <div className="text-sm font-semibold text-indigo-600 mb-4">{opt.estimatedCost}</div>}
                                    {selectedOption?.name === opt.name && (
                                        <span className="inline-block px-3 py-1 bg-purple-600 text-white rounded-full text-xs font-medium">
                                            ✓ Selected
                                        </span>
                                    )}
                                </div>
                            ))}
                            {selectedOption && (
                                <button 
                                    onClick={saveDecision}
                                    className="w-full px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 font-bold text-lg"
                                >
                                    Save Decision
                                </button>
                            )}
                        </div>
                    )}

                    {/* Input */}
                    {!discoveredProblem && (
                        <div className="bg-white rounded-xl border border-gray-200 p-4">
                            <div className="flex gap-3">
                                <input
                                    type="text"
                                    value={userInput}
                                    onChange={(e) => setUserInput(e.target.value)}
                                    onKeyPress={(e) => e.key === 'Enter' && sendMessage()}
                                    placeholder="Describe your situation..."
                                    className="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                    disabled={isAiThinking}
                                />
                                <button
                                    onClick={sendMessage}
                                    disabled={!userInput.trim() || isAiThinking}
                                    className="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium disabled:opacity-50"
                                >
                                    Send
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            );
        }

        ReactDOM.render(<AIAssistant />, document.getElementById('app-root'));
    </script>
</body>
</html>
