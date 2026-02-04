<?php
require_once __DIR__ . '/config.php';
if (!isLoggedIn()) redirect(APP_URL . '/index.php');

$user = getCurrentUser();
$pdo = getDbConnection();

$decisionId = $_GET['id'] ?? null;
if (!$decisionId) redirect(APP_URL . '/dashboard.php');

// Get decision
$stmt = $pdo->prepare("
    SELECT d.*, w.id as workspace_id
    FROM decisions d
    INNER JOIN workspaces w ON d.workspace_id = w.id
    INNER JOIN workspace_members wm ON w.id = wm.workspace_id
    WHERE d.id = ? AND wm.user_id = ?
");
$stmt->execute([$decisionId, $user['id']]);
$decision = $stmt->fetch();

if (!$decision) {
    die('Decision not found or access denied');
}

// Can only edit if not decided yet
$canEdit = ($decision['status'] !== 'Decided' && $decision['status'] !== 'Implemented');

if (!$canEdit) {
    header('Location: ' . APP_URL . '/decision.php?id=' . $decisionId . '&error=cannot_edit');
    exit;
}

// Get options with pros/cons
$stmt = $pdo->prepare("
    SELECT o.*,
           (SELECT GROUP_CONCAT(content SEPARATOR '|||') FROM option_pros_cons WHERE option_id = o.id AND type = 'pro') as pros,
           (SELECT GROUP_CONCAT(content SEPARATOR '|||') FROM option_pros_cons WHERE option_id = o.id AND type = 'con') as cons
    FROM options o
    WHERE o.decision_id = ?
    ORDER BY o.sort_order, o.id
");
$stmt->execute([$decisionId]);
$options = $stmt->fetchAll();

// Parse pros/cons into arrays
foreach ($options as &$option) {
    $option['pros_array'] = $option['pros'] ? explode('|||', $option['pros']) : [];
    $option['cons_array'] = $option['cons'] ? explode('|||', $option['cons']) : [];
}

// Get workspace members
$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.email, u.avatar_url
    FROM users u
    INNER JOIN workspace_members wm ON u.id = wm.user_id
    WHERE wm.workspace_id = ?
");
$stmt->execute([$decision['workspace_id']]);
$members = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Decision - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-purple-50 to-pink-50 min-h-screen">
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="decision.php?id=<?php echo $decisionId; ?>" class="flex items-center gap-2 text-gray-600 hover:text-gray-900">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span class="font-medium">Cancel</span>
            </a>
            <h1 class="text-lg font-bold text-gray-900">Edit Decision</h1>
            <div class="w-16"></div>
        </div>
    </nav>

    <div id="app-root"></div>

    <script type="text/babel">
        const { useState } = React;

        const EXISTING_DECISION = <?php echo json_encode($decision); ?>;
        const EXISTING_OPTIONS = <?php echo json_encode($options); ?>;
        const WORKSPACE_MEMBERS = <?php echo json_encode($members); ?>;
        
        console.log('=== EDIT DECISION DEBUG ===');
        console.log('Decision:', EXISTING_DECISION);
        console.log('Options:', EXISTING_OPTIONS);
        console.log('Members:', WORKSPACE_MEMBERS);
        
        const APP_CONFIG = {
            apiUrl: '<?php echo APP_URL; ?>',
            userId: <?php echo $user['id']; ?>,
            decisionId: <?php echo $decisionId; ?>
        };

        function EditDecision() {
            const [saving, setSaving] = useState(false);
            
            // More defensive option loading
            let initialOptions = [];
            try {
                initialOptions = EXISTING_OPTIONS.map(opt => {
                    const prosArray = Array.isArray(opt.pros_array) ? opt.pros_array : [];
                    const consArray = Array.isArray(opt.cons_array) ? opt.cons_array : [];
                    
                    return {
                        id: opt.id,
                        name: opt.name || '',
                        description: opt.description || '',
                        pros: prosArray.length > 0 ? prosArray : [''],
                        cons: consArray.length > 0 ? consArray : [''],
                        estimatedCost: opt.estimated_cost ? String(opt.estimated_cost) : '',
                        estimatedTime: opt.estimated_effort ? String(opt.estimated_effort) : ''
                    };
                });
                
                console.log('Processed initial options:', initialOptions);
            } catch (error) {
                console.error('Error processing options:', error);
                initialOptions = [{
                    id: 'new_' + Date.now(),
                    name: '',
                    description: '',
                    pros: [''],
                    cons: [''],
                    estimatedCost: '',
                    estimatedTime: ''
                }];
            }
            
            const [decisionData, setDecisionData] = useState({
                problem: EXISTING_DECISION.problem_statement || EXISTING_DECISION.title || '',
                context: EXISTING_DECISION.description || '',
                deadline: EXISTING_DECISION.deadline || '',
                category: EXISTING_DECISION.category || 'Strategic',
                reviewDate: EXISTING_DECISION.review_date || '',
                expectedOutcome: EXISTING_DECISION.expected_outcome || '',
                options: initialOptions
            });

            console.log('Initial decision data:', decisionData);

            const addOption = () => {
                setDecisionData({
                    ...decisionData,
                    options: [...decisionData.options, {
                        id: 'new_' + Date.now(),
                        name: '',
                        description: '',
                        pros: [''],
                        cons: [''],
                        estimatedCost: '',
                        estimatedTime: ''
                    }]
                });
            };

            const removeOption = (index) => {
                if (!confirm('Remove this option?')) return;
                const newOptions = decisionData.options.filter((_, i) => i !== index);
                setDecisionData({...decisionData, options: newOptions});
            };

            const updateOption = (index, field, value) => {
                const newOptions = [...decisionData.options];
                newOptions[index][field] = value;
                setDecisionData({...decisionData, options: newOptions});
            };

            const addProCon = (optionIndex, type) => {
                const newOptions = [...decisionData.options];
                newOptions[optionIndex][type].push('');
                setDecisionData({...decisionData, options: newOptions});
            };

            const updateProCon = (optionIndex, type, itemIndex, value) => {
                const newOptions = [...decisionData.options];
                newOptions[optionIndex][type][itemIndex] = value;
                setDecisionData({...decisionData, options: newOptions});
            };

            const removeProCon = (optionIndex, type, itemIndex) => {
                const newOptions = [...decisionData.options];
                newOptions[optionIndex][type] = newOptions[optionIndex][type].filter((_, i) => i !== itemIndex);
                setDecisionData({...decisionData, options: newOptions});
            };

            const saveChanges = async () => {
                if (!decisionData.problem.trim()) {
                    alert('Please enter a problem statement');
                    return;
                }

                if (decisionData.options.length < 2) {
                    alert('Please add at least 2 options');
                    return;
                }

                const invalidOptions = decisionData.options.filter(opt => !opt.name.trim());
                if (invalidOptions.length > 0) {
                    alert('All options must have a name');
                    return;
                }

                setSaving(true);

                // Clean and prepare options data
                const cleanedOptions = decisionData.options.map((opt, index) => ({
                    id: (typeof opt.id === 'string' && opt.id.startsWith('new_')) ? null : opt.id,
                    name: opt.name.trim(),
                    description: opt.description.trim(),
                    estimated_cost: opt.estimatedCost.trim(),
                    estimated_effort: opt.estimatedTime.trim(),
                    pros: opt.pros.filter(p => p && p.trim()).map(p => p.trim()),
                    cons: opt.cons.filter(c => c && c.trim()).map(c => c.trim()),
                    sort_order: index
                }));

                console.log('Saving options:', cleanedOptions);

                try {
                    const response = await fetch(`${APP_CONFIG.apiUrl}/api/decisions.php/${APP_CONFIG.decisionId}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            title: decisionData.problem.trim(),
                            description: decisionData.context.trim(),
                            problem_statement: decisionData.problem.trim(),
                            category: decisionData.category,
                            deadline: decisionData.deadline || null,
                            review_date: decisionData.reviewDate,
                            expected_outcome: decisionData.expectedOutcome.trim(),
                            options: cleanedOptions
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('✅ Changes saved!');
                        window.location.href = `decision.php?id=${APP_CONFIG.decisionId}`;
                    } else {
                        console.error('Save error:', data);
                        alert('Error: ' + (data.error || 'Failed to save'));
                        setSaving(false);
                    }
                } catch (error) {
                    console.error('Save error:', error);
                    alert('Failed to save changes. Please try again.');
                    setSaving(false);
                }
            };

            return (
                <div className="max-w-4xl mx-auto px-4 py-8">
                    <div className="bg-amber-50 border-2 border-amber-200 rounded-xl p-4 mb-6">
                        <div className="flex items-start gap-3">
                            <span className="text-2xl">✏️</span>
                            <div>
                                <div className="font-bold text-amber-900">Editing Mode</div>
                                <div className="text-sm text-amber-700">You can edit all details until the decision is marked as "Decided"</div>
                            </div>
                        </div>
                    </div>

                    {/* Problem & Context */}
                    <div className="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
                        <h2 className="text-xl font-bold mb-4">Decision Details</h2>
                        
                        <div className="space-y-4">
                            <div>
                                <label className="block text-sm font-semibold text-gray-900 mb-2">
                                    Problem Statement *
                                </label>
                                <textarea
                                    value={decisionData.problem}
                                    onChange={(e) => setDecisionData({...decisionData, problem: e.target.value})}
                                    rows="2"
                                    className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                                    placeholder="What decision needs to be made?"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-semibold text-gray-900 mb-2">
                                    Context & Background
                                </label>
                                <textarea
                                    value={decisionData.context}
                                    onChange={(e) => setDecisionData({...decisionData, context: e.target.value})}
                                    rows="3"
                                    className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                                    placeholder="Additional context..."
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-semibold text-gray-900 mb-2">Category</label>
                                    <select
                                        value={decisionData.category}
                                        onChange={(e) => setDecisionData({...decisionData, category: e.target.value})}
                                        className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                        <option>Strategic</option>
                                        <option>Financial</option>
                                        <option>Product</option>
                                        <option>Personnel</option>
                                        <option>Family</option>
                                        <option>Health</option>
                                        <option>Education</option>
                                        <option>Lifestyle</option>
                                        <option>Other</option>
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-gray-900 mb-2">Deadline</label>
                                    <input
                                        type="date"
                                        value={decisionData.deadline}
                                        onChange={(e) => setDecisionData({...decisionData, deadline: e.target.value})}
                                        className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-semibold text-gray-900 mb-2">Review Date</label>
                                    <input
                                        type="date"
                                        value={decisionData.reviewDate}
                                        onChange={(e) => setDecisionData({...decisionData, reviewDate: e.target.value})}
                                        className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                    />
                                </div>

                                <div className="flex items-end">
                                    <div className="text-xs text-gray-500">
                                        When to review if this decision worked out
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label className="block text-sm font-semibold text-gray-900 mb-2">
                                    Expected Outcome
                                </label>
                                <textarea
                                    value={decisionData.expectedOutcome}
                                    onChange={(e) => setDecisionData({...decisionData, expectedOutcome: e.target.value})}
                                    rows="2"
                                    className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none"
                                    placeholder="What do you expect will happen?"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Options */}
                    <div className="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="text-xl font-bold">Options ({decisionData.options.length})</h2>
                            <button
                                onClick={addOption}
                                className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium"
                            >
                                + Add Option
                            </button>
                        </div>

                        <div className="space-y-6">
                            {decisionData.options.map((option, optIdx) => (
                                <div key={optIdx} className="border-2 border-gray-200 rounded-xl p-4">
                                    <div className="flex items-start justify-between mb-3">
                                        <input
                                            type="text"
                                            value={option.name}
                                            onChange={(e) => updateOption(optIdx, 'name', e.target.value)}
                                            placeholder="Option name *"
                                            className="flex-1 px-3 py-2 text-lg font-bold border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        />
                                        <button
                                            onClick={() => removeOption(optIdx)}
                                            className="ml-2 p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                        >
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <textarea
                                        value={option.description}
                                        onChange={(e) => updateOption(optIdx, 'description', e.target.value)}
                                        rows="2"
                                        placeholder="Description..."
                                        className="w-full px-3 py-2 mb-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none text-sm"
                                    />

                                    <div className="grid grid-cols-2 gap-3 mb-3">
                                        <input
                                            type="text"
                                            value={option.estimatedCost}
                                            onChange={(e) => updateOption(optIdx, 'estimatedCost', e.target.value)}
                                            placeholder="Estimated cost..."
                                            className="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                                        />
                                        <input
                                            type="text"
                                            value={option.estimatedTime}
                                            onChange={(e) => updateOption(optIdx, 'estimatedTime', e.target.value)}
                                            placeholder="Time needed..."
                                            className="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                                        />
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        {/* Pros */}
                                        <div>
                                            <div className="flex items-center justify-between mb-2">
                                                <span className="text-sm font-bold text-emerald-700">Pros</span>
                                                <button
                                                    onClick={() => addProCon(optIdx, 'pros')}
                                                    className="text-xs text-emerald-600 hover:text-emerald-700"
                                                >
                                                    + Add
                                                </button>
                                            </div>
                                            <div className="space-y-2">
                                                {option.pros.map((pro, proIdx) => (
                                                    <div key={proIdx} className="flex gap-2">
                                                        <input
                                                            type="text"
                                                            value={pro}
                                                            onChange={(e) => updateProCon(optIdx, 'pros', proIdx, e.target.value)}
                                                            placeholder="Benefit..."
                                                            className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm"
                                                        />
                                                        <button
                                                            onClick={() => removeProCon(optIdx, 'pros', proIdx)}
                                                            className="p-2 text-gray-400 hover:text-red-600"
                                                        >
                                                            ×
                                                        </button>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>

                                        {/* Cons */}
                                        <div>
                                            <div className="flex items-center justify-between mb-2">
                                                <span className="text-sm font-bold text-red-700">Cons</span>
                                                <button
                                                    onClick={() => addProCon(optIdx, 'cons')}
                                                    className="text-xs text-red-600 hover:text-red-700"
                                                >
                                                    + Add
                                                </button>
                                            </div>
                                            <div className="space-y-2">
                                                {option.cons.map((con, conIdx) => (
                                                    <div key={conIdx} className="flex gap-2">
                                                        <input
                                                            type="text"
                                                            value={con}
                                                            onChange={(e) => updateProCon(optIdx, 'cons', conIdx, e.target.value)}
                                                            placeholder="Drawback..."
                                                            className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-sm"
                                                        />
                                                        <button
                                                            onClick={() => removeProCon(optIdx, 'cons', conIdx)}
                                                            className="p-2 text-gray-400 hover:text-red-600"
                                                        >
                                                            ×
                                                        </button>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Actions */}
                    <div className="flex gap-3">
                        <button
                            onClick={saveChanges}
                            disabled={saving}
                            className="flex-1 px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 font-bold text-lg disabled:opacity-50"
                        >
                            {saving ? 'Saving...' : '💾 Save Changes'}
                        </button>
                        <a
                            href={`decision.php?id=${APP_CONFIG.decisionId}`}
                            className="px-6 py-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-bold text-lg text-center"
                        >
                            Cancel
                        </a>
                    </div>
                </div>
            );
        }

        ReactDOM.render(<EditDecision />, document.getElementById('app-root'));
    </script>
</body>
</html>