import React, { useState, useEffect } from 'react';

/**
 * Intelligent Recommendation Panel
 * 
 * Shows AI-generated option suggestions based on:
 * - External startup failure patterns
 * - Industry benchmarks
 * - User's historical decisions
 */
const IntelligentRecommendationPanel = ({ decisionData, onOptionsGenerated }) => {
    const [loading, setLoading] = useState(false);
    const [recommendation, setRecommendation] = useState(null);
    const [error, setError] = useState(null);
    const [showDetails, setShowDetails] = useState({});

    useEffect(() => {
        // Auto-fetch when decision data changes
        if (decisionData?.title && decisionData?.problem_statement) {
            fetchIntelligentRecommendations();
        }
    }, [decisionData?.title, decisionData?.problem_statement]);

    const fetchIntelligentRecommendations = async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await fetch('/api/intelligent-recommendations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    decision: decisionData
                })
            });

            const data = await response.json();

            if (data.success) {
                setRecommendation(data.recommendation);
            } else {
                setError(data.error || 'Failed to generate recommendations');
            }
        } catch (err) {
            setError('Network error. Please try again.');
        } finally {
            setLoading(false);
        }
    };

    const addOptionToDecision = (option) => {
        if (onOptionsGenerated) {
            onOptionsGenerated([{
                name: option.option.name,
                description: option.option.description,
                pros: option.option.pros || [],
                cons: option.option.cons || [],
                estimatedCost: option.option.avg_cost || null,
            }]);
        }
    };

    const addAllOptions = () => {
        if (onOptionsGenerated && recommendation?.suggested_options) {
            const formattedOptions = recommendation.suggested_options.map(opt => ({
                name: opt.option.name,
                description: opt.option.description,
                pros: opt.option.pros || [],
                cons: opt.option.cons || [],
                estimatedCost: opt.option.avg_cost || null,
            }));
            onOptionsGenerated(formattedOptions);
        }
    };

    if (!decisionData?.title) {
        return (
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
                💡 Enter a decision title and problem statement to see AI-powered recommendations
            </div>
        );
    }

    if (loading) {
        return (
            <div className="bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-lg p-6">
                <div className="flex items-center space-x-3">
                    <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600"></div>
                    <div>
                        <div className="font-semibold text-purple-900">🤖 Analyzing decision...</div>
                        <div className="text-sm text-purple-600">Checking 2,000+ startup failures and industry benchmarks</div>
                    </div>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                <div className="font-semibold text-red-900">⚠️ Error</div>
                <div className="text-sm text-red-700">{error}</div>
                <button 
                    onClick={fetchIntelligentRecommendations}
                    className="mt-2 text-sm text-red-600 underline hover:text-red-800"
                >
                    Try again
                </button>
            </div>
        );
    }

    if (!recommendation) {
        return null;
    }

    const { suggested_options, external_insights, recommendation_quality } = recommendation;

    return (
        <div className="space-y-4">
            {/* Header */}
            <div className="bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg p-6">
                <div className="flex items-start justify-between">
                    <div>
                        <h3 className="text-xl font-bold flex items-center gap-2">
                            🤖 AI-Powered Recommendations
                        </h3>
                        <p className="mt-1 text-purple-100 text-sm">
                            Based on {external_insights?.failure_patterns?.total_analyzed || 0} similar company decisions
                        </p>
                    </div>
                    <div className="text-right">
                        <div className="text-xs text-purple-200">Confidence</div>
                        <div className="text-2xl font-bold">
                            {recommendation_quality.level === 'high' ? '🟢 High' :
                             recommendation_quality.level === 'medium' ? '🟡 Medium' : '🔴 Low'}
                        </div>
                    </div>
                </div>
            </div>

            {/* Quick Add All */}
            {suggested_options && suggested_options.length > 0 && (
                <button
                    onClick={addAllOptions}
                    className="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors"
                >
                    ✨ Add All {suggested_options.length} Options to Decision
                </button>
            )}

            {/* Suggested Options */}
            <div className="space-y-3">
                {suggested_options?.map((scoredOption, index) => (
                    <div 
                        key={index}
                        className="bg-white border-2 border-gray-200 rounded-lg overflow-hidden hover:border-purple-300 transition-colors"
                    >
                        {/* Option Header */}
                        <div className="p-4 bg-gradient-to-r from-gray-50 to-white">
                            <div className="flex items-start justify-between">
                                <div className="flex-1">
                                    <div className="flex items-center gap-2">
                                        <span className="text-2xl font-bold text-gray-400">#{index + 1}</span>
                                        <h4 className="text-lg font-bold text-gray-900">
                                            {scoredOption.option.name}
                                        </h4>
                                    </div>
                                    <p className="mt-1 text-sm text-gray-600">
                                        {scoredOption.option.description}
                                    </p>
                                </div>
                                <div className="ml-4 text-right">
                                    <div className="text-3xl font-bold text-purple-600">
                                        {Math.round(scoredOption.base_success_rate * 100)}%
                                    </div>
                                    <div className="text-xs text-gray-500">Success Rate</div>
                                </div>
                            </div>

                            {/* Quick Stats */}
                            <div className="mt-3 flex gap-4 text-sm">
                                {scoredOption.option.avg_cost && (
                                    <div className="flex items-center gap-1 text-gray-600">
                                        <span>💰</span>
                                        <span>${(scoredOption.option.avg_cost / 1000).toFixed(0)}K avg cost</span>
                                    </div>
                                )}
                                <div className="flex items-center gap-1 text-gray-600">
                                    <span>📊</span>
                                    <span>{scoredOption.option.based_on}</span>
                                </div>
                            </div>
                        </div>

                        {/* Expandable Details */}
                        <div className="border-t border-gray-200">
                            <button
                                onClick={() => setShowDetails({
                                    ...showDetails,
                                    [index]: !showDetails[index]
                                })}
                                className="w-full p-3 text-left text-sm font-semibold text-purple-600 hover:bg-purple-50 transition-colors flex items-center justify-between"
                            >
                                <span>{showDetails[index] ? '▼' : '▶'} Show Details (Pros, Cons, Warnings)</span>
                            </button>

                            {showDetails[index] && (
                                <div className="p-4 bg-gray-50 space-y-4">
                                    {/* Pros */}
                                    {scoredOption.option.pros && scoredOption.option.pros.length > 0 && (
                                        <div>
                                            <h5 className="font-semibold text-green-700 text-sm mb-2">✅ Pros</h5>
                                            <ul className="space-y-1">
                                                {scoredOption.option.pros.map((pro, i) => (
                                                    <li key={i} className="text-sm text-gray-700 flex items-start gap-2">
                                                        <span className="text-green-500 mt-0.5">•</span>
                                                        <span>{pro}</span>
                                                    </li>
                                                ))}
                                            </ul>
                                        </div>
                                    )}

                                    {/* Cons */}
                                    {scoredOption.option.cons && scoredOption.option.cons.length > 0 && (
                                        <div>
                                            <h5 className="font-semibold text-red-700 text-sm mb-2">⚠️ Cons</h5>
                                            <ul className="space-y-1">
                                                {scoredOption.option.cons.map((con, i) => (
                                                    <li key={i} className="text-sm text-gray-700 flex items-start gap-2">
                                                        <span className="text-red-500 mt-0.5">•</span>
                                                        <span>{con}</span>
                                                    </li>
                                                ))}
                                            </ul>
                                        </div>
                                    )}

                                    {/* Warnings from real failures */}
                                    {scoredOption.warnings && scoredOption.warnings.length > 0 && (
                                        <div className="bg-yellow-50 border border-yellow-200 rounded p-3">
                                            <h5 className="font-semibold text-yellow-800 text-sm mb-2">🚨 Warnings from Real Failures</h5>
                                            <ul className="space-y-1">
                                                {scoredOption.warnings.map((warning, i) => (
                                                    <li key={i} className="text-sm text-yellow-700">
                                                        {warning}
                                                    </li>
                                                ))}
                                            </ul>
                                        </div>
                                    )}

                                    {/* Add This Option Button */}
                                    <button
                                        onClick={() => addOptionToDecision(scoredOption)}
                                        className="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded transition-colors text-sm"
                                    >
                                        + Add This Option to Decision
                                    </button>
                                </div>
                            )}
                        </div>
                    </div>
                ))}
            </div>

            {/* External Insights */}
            {external_insights?.failure_patterns?.common_mistakes && (
                <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 className="font-bold text-red-900 mb-3">🚨 Common Mistakes to Avoid</h4>
                    <div className="space-y-2">
                        {external_insights.failure_patterns.common_mistakes.slice(0, 3).map((mistake, i) => (
                            <div key={i} className="text-sm text-red-800 flex items-start gap-2">
                                <span className="font-bold text-red-600">{mistake.count}</span>
                                <span>companies failed due to: <strong>{mistake.failure_reason}</strong></span>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Data Quality */}
            <div className="bg-gray-50 border border-gray-200 rounded-lg p-3 text-xs text-gray-600">
                <div className="flex items-center justify-between">
                    <div>
                        📊 Analysis based on:
                    </div>
                    <div className="font-semibold">
                        {recommendation_quality.external_data_points} external + {recommendation_quality.internal_data_points} your own decisions
                    </div>
                </div>
            </div>
        </div>
    );
};

export default IntelligentRecommendationPanel;