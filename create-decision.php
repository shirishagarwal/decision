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

// Get workspace members for collaboration
$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.email, u.avatar_url, wm.role
    FROM users u
    INNER JOIN workspace_members wm ON u.id = wm.user_id
    WHERE wm.workspace_id = ?
");
$stmt->execute([$workspace['id']]);
$members = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Decision - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .step-indicator { transition: all 0.3s ease; }
        .step-active { background: #4F46E5; color: white; }
        .step-completed { background: #10B981; color: white; }
        .step-inactive { background: #E5E7EB; color: #6B7280; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-purple-50 to-pink-50 min-h-screen">
    <!-- Mobile Header -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="dashboard.php" class="flex items-center gap-2 text-gray-600 hover:text-gray-900">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span class="font-medium">Back</span>
            </a>
            <h1 class="text-lg font-bold text-gray-900">New Decision</h1>
            <div class="w-16"></div>
        </div>
    </nav>

    <div id="app-root"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;

        const APP_CONFIG = {
            apiUrl: '<?php echo APP_URL; ?>',
            userId: <?php echo $user['id']; ?>,
            workspaceId: <?php echo $workspace['id']; ?>,
            members: <?php echo json_encode($members); ?>
        };

        // ========================================
        // INTELLIGENT RECOMMENDATION COMPONENT
        // ========================================
        function IntelligentRecommendationPanel({ decisionData, onOptionsGenerated }) {
            const [loading, setLoading] = useState(false);
            const [recommendation, setRecommendation] = useState(null);
            const [error, setError] = useState(null);
            const [showDetails, setShowDetails] = useState({});

            useEffect(() => {
                // Auto-fetch when decision data changes
                if (decisionData?.problem && decisionData?.context) {
                    fetchIntelligentRecommendations();
                }
            }, [decisionData?.problem, decisionData?.context]);

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
                            decision: {
                                title: decisionData.problem,
                                problem_statement: decisionData.context,
                                category: decisionData.category
                            }
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
                        id: Date.now(),
                        name: option.option.name,
                        description: option.option.description,
                        pros: option.option.pros || [],
                        cons: option.option.cons || [],
                        estimatedCost: option.option.avg_cost || '',
                        estimatedTime: ''
                    }]);
                }
            };

            const addAllOptions = () => {
                if (onOptionsGenerated && recommendation?.suggested_options) {
                    const formattedOptions = recommendation.suggested_options.map(opt => ({
                        id: Date.now() + Math.random(),
                        name: opt.option.name,
                        description: opt.option.description,
                        pros: opt.option.pros || [],
                        cons: opt.option.cons || [],
                        estimatedCost: opt.option.avg_cost || '',
                        estimatedTime: ''
                    }));
                    onOptionsGenerated(formattedOptions);
                }
            };

            if (!decisionData?.problem) {
                return (
                    <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800 mb-6">
                        💡 Enter a decision problem and context to see AI-powered recommendations based on 2,000+ real company decisions
                    </div>
                );
            }

            if (loading) {
                return (
                    <div className="bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-lg p-6 mb-6">
                        <div className="flex items-center space-x-3">
                            <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600"></div>
                            <div>
                                <div className="font-semibold text-purple-900">🤖 Analyzing decision...</div>
                                <div className="text-sm text-purple-600">Checking startup failures, layoffs, and industry benchmarks</div>
                            </div>
                        </div>
                    </div>
                );
            }

            if (error) {
                return (
                    <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
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
                <div className="space-y-4 mb-6">
                    {/* Header */}
                    <div className="bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg p-6">
                        <div className="flex items-start justify-between">
                            <div>
                                <h3 className="text-xl font-bold flex items-center gap-2">
                                    🤖 AI-Powered Recommendations
                                </h3>
                                <p className="mt-1 text-purple-100 text-sm">
                                    Based on {external_insights?.failure_patterns?.total_analyzed || '2,000+'} similar company decisions
                                </p>
                            </div>
                            <div className="text-right">
                                <div className="text-xs text-purple-200">Confidence</div>
                                <div className="text-2xl font-bold">
                                    {recommendation_quality?.level === 'high' ? '🟢 High' :
                                     recommendation_quality?.level === 'medium' ? '🟡 Med' : '🔴 Low'}
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
                                            <div className="text-xs text-gray-500">Success</div>
                                        </div>
                                    </div>

                                    {/* Quick Stats */}
                                    <div className="mt-3 flex gap-4 text-sm flex-wrap">
                                        {scoredOption.option.avg_cost && (
                                            <div className="flex items-center gap-1 text-gray-600">
                                                <span>💰</span>
                                                <span>${(scoredOption.option.avg_cost / 1000).toFixed(0)}K avg</span>
                                            </div>
                                        )}
                                        <div className="flex items-center gap-1 text-gray-600">
                                            <span>📊</span>
                                            <span className="text-xs">{scoredOption.option.based_on}</span>
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
                                        <span>{showDetails[index] ? '▼' : '▶'} Show Details</span>
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
                                                + Add This Option
                                            </button>
                                        </div>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>

                    {/* External Insights */}
                    {external_insights?.failure_patterns?.common_mistakes && external_insights.failure_patterns.common_mistakes.length > 0 && (
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
                </div>
            );
        }

        // ========================================
        // MAIN DECISION CREATION COMPONENT
        // ========================================
        function NewDecision() {
            const [currentStep, setCurrentStep] = useState(1);
            const [isGeneratingOptions, setIsGeneratingOptions] = useState(false);
            const [showContextModal, setShowContextModal] = useState(false);
            const [decisionData, setDecisionData] = useState({
                problem: '',
                context: '',
                deadline: '',
                category: 'Strategic',
                options: [],
                invitedMembers: [],
                requiresVoting: false,
                permanentContext: '',
                temporaryContext: '',
                reviewDate: new Date(Date.now() + 90*24*60*60*1000).toISOString().split('T')[0], // Default 90 days
                expectedOutcome: ''
            });

            const steps = [
                { number: 1, title: 'Problem', icon: '🎯' },
                { number: 2, title: 'Options', icon: '💡' },
                { number: 3, title: 'Team', icon: '👥' },
                { number: 4, title: 'Review', icon: '✅' }
            ];

            const categories = [
                'Strategic', 'Financial', 'Product', 'Team', 'Marketing', 
                'Operations', 'Technology', 'Legal', 'Other'
            ];

            const canProceed = (step) => {
                switch(step) {
                    case 1:
                        return decisionData.problem.trim() !== '' && 
                               decisionData.reviewDate && 
                               decisionData.expectedOutcome.trim() !== '';
                    case 2:
                        return decisionData.options.length >= 2 && 
                               decisionData.options.every(opt => opt.name.trim() !== '');
                    case 3:
                        return true; // Team is optional
                    case 4:
                        return true;
                    default:
                        return false;
                }
            };

            const nextStep = () => {
                if (canProceed(currentStep)) {
                    setCurrentStep(prev => Math.min(prev + 1, 4));
                }
            };

            const prevStep = () => {
                setCurrentStep(prev => Math.max(prev - 1, 1));
            };

            const addOption = () => {
                setDecisionData(prev => ({
                    ...prev,
                    options: [...prev.options, {
                        id: Date.now(),
                        name: '',
                        description: '',
                        pros: [],
                        cons: [],
                        estimatedCost: '',
                        estimatedTime: ''
                    }]
                }));
            };

            const updateOption = (id, field, value) => {
                setDecisionData(prev => ({
                    ...prev,
                    options: prev.options.map(opt => 
                        opt.id === id ? { ...opt, [field]: value } : opt
                    )
                }));
            };

            const removeOption = (id) => {
                setDecisionData(prev => ({
                    ...prev,
                    options: prev.options.filter(opt => opt.id !== id)
                }));
            };

            const handleAIOptionsGenerated = (newOptions) => {
                setDecisionData(prev => ({
                    ...prev,
                    options: [...prev.options, ...newOptions]
                }));
            };

            const toggleMember = (memberId) => {
                setDecisionData(prev => ({
                    ...prev,
                    invitedMembers: prev.invitedMembers.includes(memberId)
                        ? prev.invitedMembers.filter(id => id !== memberId)
                        : [...prev.invitedMembers, memberId]
                }));
            };

            const setReviewQuickDate = (months) => {
                const date = new Date();
                date.setMonth(date.getMonth() + months);
                setDecisionData(prev => ({
                    ...prev,
                    reviewDate: date.toISOString().split('T')[0]
                }));
            };

            const saveDecision = async () => {
                try {
                    const response = await fetch(`${APP_CONFIG.apiUrl}/api/decisions.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            workspace_id: APP_CONFIG.workspaceId,
                            title: decisionData.problem,
                            description: decisionData.context,
                            problem_statement: decisionData.problem,
                            category: decisionData.category,
                            status: 'pending',
                            deadline: decisionData.deadline || null,
                            review_date: decisionData.reviewDate,
                            expected_outcome: decisionData.expectedOutcome,
                            options: decisionData.options.map(opt => ({
                                name: opt.name,
                                description: opt.description,
                                pros: opt.pros,
                                cons: opt.cons,
                                estimated_cost: opt.estimatedCost,
                                estimated_time: opt.estimatedTime
                            })),
                            invited_members: decisionData.invitedMembers,
                            requires_voting: decisionData.requiresVoting
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        window.location.href = `${APP_CONFIG.apiUrl}/decision.php?id=${data.decision_id}`;
                    } else {
                        alert('Error creating decision: ' + (data.error || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Network error. Please try again.');
                }
            };

            return (
                <div className="max-w-4xl mx-auto px-4 py-8">
                    {/* Progress Steps */}
                    <div className="mb-8">
                        <div className="flex items-center justify-between">
                            {steps.map((step, index) => (
                                <React.Fragment key={step.number}>
                                    <div className="flex flex-col items-center flex-1">
                                        <div className={`
                                            step-indicator w-12 h-12 rounded-full flex items-center justify-center 
                                            font-bold text-lg
                                            ${currentStep > step.number ? 'step-completed' : 
                                              currentStep === step.number ? 'step-active' : 'step-inactive'}
                                        `}>
                                            {currentStep > step.number ? '✓' : step.icon}
                                        </div>
                                        <div className={`mt-2 text-sm font-medium ${
                                            currentStep >= step.number ? 'text-gray-900' : 'text-gray-500'
                                        }`}>
                                            {step.title}
                                        </div>
                                    </div>
                                    {index < steps.length - 1 && (
                                        <div className={`flex-1 h-1 mx-2 rounded ${
                                            currentStep > step.number ? 'bg-green-500' : 'bg-gray-200'
                                        }`}></div>
                                    )}
                                </React.Fragment>
                            ))}
                        </div>
                    </div>

                    {/* Step Content */}
                    <div className="bg-white rounded-2xl shadow-xl p-8">
                        {/* STEP 1: PROBLEM */}
                        {currentStep === 1 && (
                            <div className="space-y-6">
                                <div>
                                    <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                        🎯 What decision do you need to make?
                                    </h2>
                                    <p className="text-gray-600">Describe the problem or opportunity you're facing</p>
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-gray-700 mb-2">
                                        Decision Title *
                                    </label>
                                    <input
                                        type="text"
                                        value={decisionData.problem}
                                        onChange={(e) => setDecisionData({...decisionData, problem: e.target.value})}
                                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        placeholder="e.g., Should we hire a VP of Sales?"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-gray-700 mb-2">
                                        Context & Background
                                    </label>
                                    <textarea
                                        value={decisionData.context}
                                        onChange={(e) => setDecisionData({...decisionData, context: e.target.value})}
                                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        rows="4"
                                        placeholder="Provide more details about the situation..."
                                    />
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-sm font-semibold text-gray-700 mb-2">
                                            Category
                                        </label>
                                        <select
                                            value={decisionData.category}
                                            onChange={(e) => setDecisionData({...decisionData, category: e.target.value})}
                                            className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                        >
                                            {categories.map(cat => (
                                                <option key={cat} value={cat}>{cat}</option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-sm font-semibold text-gray-700 mb-2">
                                            Deadline (Optional)
                                        </label>
                                        <input
                                            type="date"
                                            value={decisionData.deadline}
                                            onChange={(e) => setDecisionData({...decisionData, deadline: e.target.value})}
                                            className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                        />
                                    </div>
                                </div>

                                {/* Learning Loop (Unicorn Feature) */}
                                <div className="bg-gradient-to-r from-purple-50 to-pink-50 border-2 border-purple-200 rounded-lg p-6">
                                    <h3 className="text-lg font-bold text-purple-900 mb-4 flex items-center gap-2">
                                        🔮 Learning Loop (Future Review)
                                    </h3>
                                    
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block text-sm font-semibold text-gray-700 mb-2">
                                                When should we review this decision? *
                                            </label>
                                            <input
                                                type="date"
                                                value={decisionData.reviewDate}
                                                onChange={(e) => setDecisionData({...decisionData, reviewDate: e.target.value})}
                                                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                            />
                                            <div className="mt-2 flex gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() => setReviewQuickDate(1)}
                                                    className="px-3 py-1 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50"
                                                >
                                                    1 month
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => setReviewQuickDate(3)}
                                                    className="px-3 py-1 text-sm bg-purple-100 border border-purple-300 rounded hover:bg-purple-200 font-semibold"
                                                >
                                                    3 months ⭐
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => setReviewQuickDate(6)}
                                                    className="px-3 py-1 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50"
                                                >
                                                    6 months
                                                </button>
                                            </div>
                                        </div>

                                        <div>
                                            <label className="block text-sm font-semibold text-gray-700 mb-2">
                                                What outcome do you expect from this decision? *
                                            </label>
                                            <textarea
                                                value={decisionData.expectedOutcome}
                                                onChange={(e) => setDecisionData({...decisionData, expectedOutcome: e.target.value})}
                                                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                                rows="3"
                                                placeholder="e.g., Increase sales by 50%, reduce churn to under 5%, ship feature in Q2..."
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* STEP 2: OPTIONS */}
                        {currentStep === 2 && (
                            <div className="space-y-6">
                                <div>
                                    <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                        💡 What are your options?
                                    </h2>
                                    <p className="text-gray-600">Add at least 2 options you're considering</p>
                                </div>

                                {/* INTELLIGENT RECOMMENDATIONS */}
                                <IntelligentRecommendationPanel
                                    decisionData={decisionData}
                                    onOptionsGenerated={handleAIOptionsGenerated}
                                />

                                {/* Manual Options */}
                                <div className="space-y-4">
                                    {decisionData.options.map((option, index) => (
                                        <div key={option.id} className="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                            <div className="flex items-start justify-between mb-3">
                                                <h4 className="font-semibold text-gray-900">Option {index + 1}</h4>
                                                {decisionData.options.length > 2 && (
                                                    <button
                                                        onClick={() => removeOption(option.id)}
                                                        className="text-red-600 hover:text-red-800 text-sm"
                                                    >
                                                        Remove
                                                    </button>
                                                )}
                                            </div>

                                            <div className="space-y-3">
                                                <input
                                                    type="text"
                                                    value={option.name}
                                                    onChange={(e) => updateOption(option.id, 'name', e.target.value)}
                                                    className="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-purple-500"
                                                    placeholder="Option name *"
                                                />

                                                <textarea
                                                    value={option.description}
                                                    onChange={(e) => updateOption(option.id, 'description', e.target.value)}
                                                    className="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-purple-500"
                                                    rows="2"
                                                    placeholder="Description"
                                                />

                                                <div className="grid grid-cols-2 gap-3">
                                                    <input
                                                        type="text"
                                                        value={option.estimatedCost}
                                                        onChange={(e) => updateOption(option.id, 'estimatedCost', e.target.value)}
                                                        className="px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-purple-500"
                                                        placeholder="Est. cost"
                                                    />
                                                    <input
                                                        type="text"
                                                        value={option.estimatedTime}
                                                        onChange={(e) => updateOption(option.id, 'estimatedTime', e.target.value)}
                                                        className="px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-purple-500"
                                                        placeholder="Est. time"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    ))}

                                    <button
                                        onClick={addOption}
                                        className="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-purple-500 hover:text-purple-600 font-medium transition-colors"
                                    >
                                        + Add Another Option
                                    </button>
                                </div>
                            </div>
                        )}

                        {/* STEP 3: TEAM */}
                        {currentStep === 3 && (
                            <div className="space-y-6">
                                <div>
                                    <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                        👥 Who should be involved?
                                    </h2>
                                    <p className="text-gray-600">Invite team members to collaborate (optional)</p>
                                </div>

                                <div className="space-y-3">
                                    {APP_CONFIG.members.map(member => (
                                        <div
                                            key={member.id}
                                            onClick={() => toggleMember(member.id)}
                                            className={`p-4 border-2 rounded-lg cursor-pointer transition-all ${
                                                decisionData.invitedMembers.includes(member.id)
                                                    ? 'border-purple-500 bg-purple-50'
                                                    : 'border-gray-200 hover:border-gray-300'
                                            }`}
                                        >
                                            <div className="flex items-center gap-3">
                                                <div className="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                                    {member.name.charAt(0).toUpperCase()}
                                                </div>
                                                <div className="flex-1">
                                                    <div className="font-semibold text-gray-900">{member.name}</div>
                                                    <div className="text-sm text-gray-500">{member.email}</div>
                                                </div>
                                                {decisionData.invitedMembers.includes(member.id) && (
                                                    <div className="text-purple-600 font-bold">✓</div>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                <div className="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <input
                                        type="checkbox"
                                        checked={decisionData.requiresVoting}
                                        onChange={(e) => setDecisionData({...decisionData, requiresVoting: e.target.checked})}
                                        className="w-5 h-5 text-purple-600"
                                    />
                                    <label className="text-sm font-medium text-gray-900">
                                        Require team voting before final decision
                                    </label>
                                </div>
                            </div>
                        )}

                        {/* STEP 4: REVIEW */}
                        {currentStep === 4 && (
                            <div className="space-y-6">
                                <div>
                                    <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                        ✅ Review Your Decision
                                    </h2>
                                    <p className="text-gray-600">Double-check everything before creating</p>
                                </div>

                                <div className="space-y-4">
                                    <div className="bg-gray-50 rounded-lg p-4">
                                        <h3 className="font-bold text-gray-900 mb-2">Problem</h3>
                                        <p className="text-gray-700">{decisionData.problem}</p>
                                        {decisionData.context && (
                                            <p className="text-sm text-gray-600 mt-2">{decisionData.context}</p>
                                        )}
                                    </div>

                                    <div className="bg-gray-50 rounded-lg p-4">
                                        <h3 className="font-bold text-gray-900 mb-2">
                                            Options ({decisionData.options.length})
                                        </h3>
                                        <ul className="space-y-2">
                                            {decisionData.options.map((opt, idx) => (
                                                <li key={opt.id} className="flex items-start gap-2">
                                                    <span className="font-semibold text-purple-600">{idx + 1}.</span>
                                                    <span className="text-gray-700">{opt.name}</span>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>

                                    <div className="bg-gray-50 rounded-lg p-4">
                                        <h3 className="font-bold text-gray-900 mb-2">Learning Loop</h3>
                                        <div className="text-sm text-gray-700">
                                            <div className="mb-1">
                                                <span className="font-semibold">Review Date:</span> {decisionData.reviewDate}
                                            </div>
                                            <div>
                                                <span className="font-semibold">Expected Outcome:</span> {decisionData.expectedOutcome}
                                            </div>
                                        </div>
                                    </div>

                                    {decisionData.invitedMembers.length > 0 && (
                                        <div className="bg-gray-50 rounded-lg p-4">
                                            <h3 className="font-bold text-gray-900 mb-2">
                                                Team ({decisionData.invitedMembers.length} members)
                                            </h3>
                                            <div className="text-sm text-gray-700">
                                                {decisionData.requiresVoting && (
                                                    <div className="mb-2 text-purple-600 font-semibold">
                                                        ✓ Voting required
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        )}

                        {/* Navigation Buttons */}
                        <div className="mt-8 flex items-center justify-between">
                            <button
                                onClick={prevStep}
                                disabled={currentStep === 1}
                                className={`px-6 py-3 rounded-lg font-semibold transition-colors ${
                                    currentStep === 1
                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                }`}
                            >
                                ← Previous
                            </button>

                            {currentStep < 4 ? (
                                <button
                                    onClick={nextStep}
                                    disabled={!canProceed(currentStep)}
                                    className={`px-6 py-3 rounded-lg font-semibold transition-colors ${
                                        canProceed(currentStep)
                                            ? 'bg-purple-600 text-white hover:bg-purple-700'
                                            : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                    }`}
                                >
                                    Next →
                                </button>
                            ) : (
                                <button
                                    onClick={saveDecision}
                                    className="px-8 py-3 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700 transition-colors"
                                >
                                    Create Decision ✓
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            );
        }

        // Render the app
        const root = ReactDOM.createRoot(document.getElementById('app-root'));
        root.render(<NewDecision />);
    </script>
</body>
</html>