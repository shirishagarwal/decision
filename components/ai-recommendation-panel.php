<!-- AI Recommendation Panel Component -->
<!-- Include this in create-decision.php or edit-decision.php -->

<div id="ai-recommendations-container" class="mb-6"></div>

<script type="text/babel">
const { useState, useEffect } = React;

function AIRecommendationPanel({ decisionData }) {
    const [recommendations, setRecommendations] = useState(null);
    const [loading, setLoading] = useState(false);
    const [expanded, setExpanded] = useState(true);

    useEffect(() => {
        // Only fetch if we have options to analyze
        if (!decisionData.options || decisionData.options.length < 2) {
            setRecommendations(null);
            return;
        }

        const timer = setTimeout(() => {
            fetchRecommendations();
        }, 1500); // Debounce

        return () => clearTimeout(timer);
    }, [JSON.stringify(decisionData)]);

    const fetchRecommendations = async () => {
        setLoading(true);
        try {
            const response = await fetch('<?php echo APP_URL; ?>/api/recommendations.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(decisionData)
            });

            const data = await response.json();

            if (data.success && data.recommendations.has_recommendations) {
                setRecommendations(data.recommendations);
            } else {
                setRecommendations(null);
            }
        } catch (error) {
            console.error('Recommendation error:', error);
            setRecommendations(null);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="flex items-center gap-3 p-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-2 border-purple-200 rounded-xl">
                <svg className="animate-spin h-6 w-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span className="text-purple-900 font-medium">🤖 AI analyzing {recommendations?.similar_count || 'historical'} decisions to recommend best option...</span>
            </div>
        );
    }

    if (!recommendations || !recommendations.has_recommendations) {
        return null;
    }

    const rec = recommendations;
    const confidenceColor = rec.confidence >= 70 ? 'emerald' : rec.confidence >= 50 ? 'amber' : 'gray';

    return (
        <div className="relative">
            {/* Main Recommendation Card */}
            <div className={`bg-gradient-to-br from-${confidenceColor}-50 to-purple-50 border-2 border-${confidenceColor}-300 rounded-2xl overflow-hidden shadow-lg`}>
                {/* Header */}
                <div className="bg-gradient-to-r from-indigo-600 to-purple-600 p-4 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="text-3xl">🤖</div>
                        <div>
                            <div className="text-white font-bold text-lg">AI Recommendation</div>
                            <div className="text-purple-100 text-sm">Based on {rec.similar_count} similar decisions</div>
                        </div>
                    </div>
                    <div className="text-right">
                        <div className="text-white font-black text-2xl">{rec.confidence}%</div>
                        <div className="text-purple-100 text-xs">Confidence</div>
                    </div>
                </div>

                {/* Recommended Option */}
                <div className="p-6 space-y-4">
                    <div className="flex items-start gap-4 p-4 bg-white rounded-xl border-2 border-emerald-300 shadow-md">
                        <div className="text-4xl">✅</div>
                        <div className="flex-1">
                            <div className="flex items-center gap-2 mb-2">
                                <span className="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold uppercase">
                                    Recommended
                                </span>
                                {rec.success_rate && (
                                    <span className="text-sm text-gray-500">
                                        {rec.success_rate}% success rate
                                    </span>
                                )}
                            </div>
                            <div className="text-xl font-bold text-gray-900 mb-2">
                                {rec.recommended_option.name}
                            </div>
                            {rec.recommended_option.description && (
                                <div className="text-gray-600 text-sm mb-3">
                                    {rec.recommended_option.description}
                                </div>
                            )}
                            
                            {/* Reasoning */}
                            {rec.reasoning && rec.reasoning.length > 0 && (
                                <div className="space-y-1">
                                    <div className="text-xs font-semibold text-gray-700 uppercase">Why this works:</div>
                                    {rec.reasoning.map((reason, idx) => (
                                        <div key={idx} className="text-sm text-gray-700 flex items-start gap-2">
                                            <span className="text-emerald-500 mt-0.5">•</span>
                                            <span>{reason}</span>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Not Recommended Option */}
                    {rec.not_recommended_option && (
                        <div className="flex items-start gap-4 p-4 bg-white rounded-xl border-2 border-red-300 shadow-md opacity-75">
                            <div className="text-4xl">⚠️</div>
                            <div className="flex-1">
                                <div className="flex items-center gap-2 mb-2">
                                    <span className="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold uppercase">
                                        Not Recommended
                                    </span>
                                </div>
                                <div className="text-xl font-bold text-gray-900 mb-2">
                                    {rec.not_recommended_option.name}
                                </div>
                                <div className="text-sm text-red-700">
                                    Based on similar past decisions, this option has a lower success rate
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Stats */}
                    <div className="grid grid-cols-3 gap-3 p-4 bg-white rounded-xl border border-gray-200">
                        <div className="text-center">
                            <div className="text-2xl font-bold text-emerald-600">
                                {rec.patterns?.successful || 0}
                            </div>
                            <div className="text-xs text-gray-500">Succeeded</div>
                        </div>
                        <div className="text-center">
                            <div className="text-2xl font-bold text-red-600">
                                {rec.patterns?.failed || 0}
                            </div>
                            <div className="text-xs text-gray-500">Failed</div>
                        </div>
                        <div className="text-center">
                            <div className="text-2xl font-bold text-indigo-600">
                                {rec.similar_count}
                            </div>
                            <div className="text-xs text-gray-500">Analyzed</div>
                        </div>
                    </div>

                    {/* Expandable: Similar Decisions */}
                    {rec.similar_decisions && rec.similar_decisions.length > 0 && (
                        <div>
                            <button
                                onClick={() => setExpanded(!expanded)}
                                className="flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-700"
                            >
                                <svg className={`w-4 h-4 transition-transform ${expanded ? 'rotate-90' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                                </svg>
                                View {rec.similar_decisions.length} similar past decisions
                            </button>

                            {expanded && (
                                <div className="mt-3 space-y-2">
                                    {rec.similar_decisions.map((sd, idx) => {
                                        const rating = sd.outcome_rating || 0;
                                        const emoji = rating >= 4 ? '✅' : rating <= 2 ? '❌' : '⚠️';
                                        const bgColor = rating >= 4 ? 'bg-emerald-50' : rating <= 2 ? 'bg-red-50' : 'bg-amber-50';
                                        
                                        return (
                                            <div key={idx} className={`p-3 ${bgColor} rounded-lg border border-gray-200`}>
                                                <div className="flex items-start gap-3">
                                                    <span className="text-2xl">{emoji}</span>
                                                    <div className="flex-1 min-w-0">
                                                        <div className="font-semibold text-gray-900 truncate">
                                                            {sd.title}
                                                        </div>
                                                        <div className="text-xs text-gray-500 mt-1">
                                                            {new Date(sd.created_at).toLocaleDateString()} • Rating: {rating}/5
                                                        </div>
                                                        {sd.actual_outcome && (
                                                            <div className="text-sm text-gray-600 mt-1 line-clamp-2">
                                                                {sd.actual_outcome}
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            )}
                        </div>
                    )}

                    {/* Explanation */}
                    {rec.explanation && (
                        <div className="p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                            <div className="text-xs font-semibold text-indigo-900 uppercase mb-2">
                                💡 AI Analysis
                            </div>
                            <div className="text-sm text-indigo-800 whitespace-pre-line">
                                {rec.explanation}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

// Render the component
const container = document.getElementById('ai-recommendations-container');
if (container) {
    const root = ReactDOM.createRoot(container);
    
    // This will be called whenever decision data changes
    window.updateAIRecommendations = (decisionData) => {
        root.render(<AIRecommendationPanel decisionData={decisionData} />);
    };

    // Initial render
    root.render(<AIRecommendationPanel decisionData={{}} />);
}
</script>

<style>
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#ai-recommendations-container > div {
    animation: slideDown 0.4s ease-out;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>