<!-- AI Warning Panel Component -->
<!-- Include this in create-decision.php where you want warnings to appear -->

<div id="ai-warnings-container" class="mb-6"></div>

<script type="text/babel">
const { useState, useEffect } = React;

function AIWarningPanel({ decisionData }) {
    const [warnings, setWarnings] = useState([]);
    const [loading, setLoading] = useState(false);
    const [dismissed, setDismissed] = useState(new Set());

    useEffect(() => {
        // Debounce: only fetch warnings when user stops typing
        const timer = setTimeout(() => {
            if (decisionData.title || decisionData.problem_statement || decisionData.category) {
                fetchWarnings();
            }
        }, 1000);

        return () => clearTimeout(timer);
    }, [decisionData.title, decisionData.problem_statement, decisionData.category, decisionData.deadline]);

    const fetchWarnings = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams({
                action: 'warnings',
                title: decisionData.title || '',
                problem_statement: decisionData.problem_statement || '',
                category: decisionData.category || '',
                deadline: decisionData.deadline || '',
                options: JSON.stringify(decisionData.options || [])
            });

            const response = await fetch(`<?php echo APP_URL; ?>/api/ai-assistant.php?${params}`);
            const data = await response.json();

            if (data.success) {
                setWarnings(data.warnings);
            }
        } catch (error) {
            console.error('AI warnings error:', error);
        } finally {
            setLoading(false);
        }
    };

    const dismissWarning = (index) => {
        setDismissed(prev => new Set([...prev, index]));
    };

    const activeWarnings = warnings.filter((_, index) => !dismissed.has(index));

    if (activeWarnings.length === 0 && !loading) {
        return null;
    }

    return (
        <div className="space-y-3">
            {loading && (
                <div className="flex items-center gap-2 text-gray-500 text-sm">
                    <svg className="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>🤖 AI analyzing your decision...</span>
                </div>
            )}

            {activeWarnings.map((warning, index) => {
                const severityColors = {
                    danger: 'bg-red-50 border-red-200 text-red-900',
                    warning: 'bg-amber-50 border-amber-200 text-amber-900',
                    caution: 'bg-yellow-50 border-yellow-200 text-yellow-900',
                    info: 'bg-blue-50 border-blue-200 text-blue-900'
                };

                const severityIcons = {
                    danger: '🚨',
                    warning: '⚠️',
                    caution: '💡',
                    info: '💭'
                };

                const colorClass = severityColors[warning.severity] || severityColors.info;
                const icon = severityIcons[warning.severity] || '💡';

                return (
                    <div key={index} className={`rounded-lg border-2 p-4 ${colorClass} relative`}>
                        <button
                            onClick={() => dismissWarning(index)}
                            className="absolute top-2 right-2 text-gray-400 hover:text-gray-600"
                        >
                            ×
                        </button>

                        <div className="flex gap-3">
                            <div className="text-2xl flex-shrink-0">{icon}</div>
                            <div className="flex-1">
                                <div className="font-bold mb-1">{warning.title}</div>
                                <div className="text-sm mb-2">{warning.message}</div>

                                {warning.data && warning.type === 'similarity' && (
                                    <div className="mt-2 p-2 bg-white rounded border border-gray-200">
                                        <div className="text-xs font-semibold mb-1">Similar Decision:</div>
                                        <div className="text-xs">{warning.data.title}</div>
                                        <div className="text-xs text-gray-500">
                                            Rating: {warning.data.review_rating}/5 | {warning.data.actual_outcome}
                                        </div>
                                    </div>
                                )}

                                {warning.data && warning.type === 'timeline' && (
                                    <div className="mt-2 text-xs">
                                        <strong>Your history:</strong> {warning.data.sample_size} decisions analyzed
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                );
            })}
        </div>
    );
}

// Render the component
const container = document.getElementById('ai-warnings-container');
if (container) {
    const root = ReactDOM.createRoot(container);
    
    // This will be called whenever decision data changes
    window.updateAIWarnings = (decisionData) => {
        root.render(<AIWarningPanel decisionData={decisionData} />);
    };

    // Initial render
    root.render(<AIWarningPanel decisionData={{}} />);
}
</script>

<style>
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#ai-warnings-container > div {
    animation: slideIn 0.3s ease-out;
}
</style>