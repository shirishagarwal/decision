<?php
require_once __DIR__ . '/config.php';

if (!isLoggedIn()) {
    redirect(APP_URL . '/index.php');
}

$user = getCurrentUser();
$decisionId = $_GET['id'] ?? null;

if (!$decisionId) {
    redirect(APP_URL . '/dashboard.php');
}

$pdo = getDbConnection();

// Get decision with workspace check
$stmt = $pdo->prepare("
    SELECT d.*, u.name as creator_name, u.avatar_url as creator_avatar,
           w.name as workspace_name
    FROM decisions d
    INNER JOIN users u ON d.created_by = u.id
    INNER JOIN workspaces w ON d.workspace_id = w.id
    INNER JOIN workspace_members wm ON w.id = wm.workspace_id
    WHERE d.id = ? AND wm.user_id = ?
");
$stmt->execute([$decisionId, $user['id']]);
$decision = $stmt->fetch();

if (!$decision) {
    redirect(APP_URL . '/dashboard.php');
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

// Parse pros/cons
foreach ($options as &$option) {
    $option['pros_array'] = $option['pros'] ? explode('|||', $option['pros']) : [];
    $option['cons_array'] = $option['cons'] ? explode('|||', $option['cons']) : [];
}
unset($option); // CRITICAL: Break the reference to avoid bugs later

// Get comments
$stmt = $pdo->prepare("
    SELECT c.*, u.name as user_name, u.avatar_url
    FROM comments c
    INNER JOIN users u ON c.user_id = u.id
    WHERE c.decision_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$decisionId]);
$comments = $stmt->fetchAll();

// Get tags
$stmt = $pdo->prepare("
    SELECT t.name, t.color
    FROM tags t
    INNER JOIN decision_tags dt ON t.id = dt.tag_id
    WHERE dt.decision_id = ?
");
$stmt->execute([$decisionId]);
$tags = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($decision['title']); ?> - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
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
            <h1 class="text-lg font-bold text-gray-900 truncate max-w-[200px]">Decision</h1>
            <button class="p-2 hover:bg-gray-100 rounded-lg">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="1"></circle>
                    <circle cx="12" cy="5" r="1"></circle>
                    <circle cx="12" cy="19" r="1"></circle>
                </svg>
            </button>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-6 pb-24">
        <?php 
        // Check if decision needs review
        $needsReview = false;
        $reviewPending = false;
        if ($decision['review_date'] && !$decision['review_completed_at']) {
            $reviewDate = strtotime($decision['review_date']);
            $today = strtotime('today');
            $daysUntil = floor(($reviewDate - $today) / 86400);
            
            if ($daysUntil <= 0) {
                $needsReview = true; // Past due
            } elseif ($daysUntil <= 7) {
                $reviewPending = true; // Coming up soon
            }
        }
        ?>
        
        <!-- Review Reminder (if needed) -->
        <?php if ($needsReview): ?>
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-2xl p-6 mb-6 shadow-xl">
            <div class="flex items-start gap-4">
                <div class="text-4xl">⏰</div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold mb-2">Time to Review This Decision!</h3>
                    <p class="mb-4 text-purple-100">
                        You set a review date for <?php echo date('F j, Y', strtotime($decision['review_date'])); ?>. 
                        Let's see how it turned out!
                    </p>
                    <?php if ($decision['expected_outcome']): ?>
                    <div class="bg-white/20 rounded-lg p-3 mb-4 text-sm">
                        <div class="font-semibold mb-1">You expected:</div>
                        <div class="text-purple-50"><?php echo nl2br(htmlspecialchars($decision['expected_outcome'])); ?></div>
                    </div>
                    <?php endif; ?>
                    <a href="review-decision.php?id=<?php echo $decision['id']; ?>" 
                       class="inline-flex items-center gap-2 px-6 py-3 bg-white text-purple-600 rounded-lg hover:bg-purple-50 font-bold transition-colors">
                        <span>Review This Decision</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        <?php elseif ($reviewPending): ?>
        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4 mb-6">
            <div class="flex items-center gap-3">
                <span class="text-2xl">📅</span>
                <div class="flex-1">
                    <div class="font-semibold text-blue-900">Review coming up in <?php echo $daysUntil; ?> day<?php echo $daysUntil != 1 ? 's' : ''; ?></div>
                    <div class="text-sm text-blue-700">Review date: <?php echo date('F j, Y', strtotime($decision['review_date'])); ?></div>
                </div>
            </div>
        </div>
        <?php elseif ($decision['review_completed_at']): ?>
        <div class="bg-emerald-50 border-2 border-emerald-200 rounded-xl p-4 mb-6">
            <div class="flex items-center gap-3">
                <span class="text-2xl">✅</span>
                <div class="flex-1">
                    <div class="font-semibold text-emerald-900">Reviewed on <?php echo date('F j, Y', strtotime($decision['review_completed_at'])); ?></div>
                    <?php if ($decision['review_rating']): ?>
                    <div class="text-sm text-emerald-700">
                        Rating: 
                        <?php
                        $emoji = match($decision['review_rating']) {
                            'much_better' => '😄 Much Better',
                            'better' => '🙂 Better',
                            'as_expected' => '😐 As Expected',
                            'worse' => '😕 Worse',
                            'much_worse' => '😞 Much Worse',
                            default => '❓'
                        };
                        echo $emoji;
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
                <a href="review-decision.php?id=<?php echo $decision['id']; ?>" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                    View Review →
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Decision Header -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900"><?php echo e($decision['title']); ?></h1>
                        <?php if ($decision['status'] !== 'Decided' && $decision['status'] !== 'Implemented'): ?>
                        <a href="edit-decision.php?id=<?php echo $decision['id']; ?>" 
                           class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium flex items-center gap-1">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Edit
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="px-3 py-1 rounded-full text-sm font-medium <?php 
                            echo $decision['status'] === 'Implemented' ? 'bg-green-100 text-green-800' :
                                ($decision['status'] === 'Decided' ? 'bg-blue-100 text-blue-800' :
                                'bg-amber-100 text-amber-800');
                        ?>">
                            <?php echo e($decision['status']); ?>
                        </span>
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            <?php echo e($decision['category']); ?>
                        </span>
                        <?php foreach ($tags as $tag): ?>
                        <span class="px-3 py-1 rounded-full text-sm font-medium" style="background-color: <?php echo e($tag['color']); ?>20; color: <?php echo e($tag['color']); ?>">
                            <?php echo e($tag['name']); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($decision['problem_statement']): ?>
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Problem</h3>
                        <p class="text-gray-700"><?php echo e($decision['problem_statement']); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($decision['description']): ?>
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Context</h3>
                        <p class="text-gray-700"><?php echo e($decision['description']); ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                        <span class="flex items-center gap-1">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            Created <?php echo date('M j, Y', strtotime($decision['created_at'])); ?>
                        </span>
                        <?php if ($decision['decided_at']): ?>
                        <span class="flex items-center gap-1">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Decided <?php echo date('M j, Y', strtotime($decision['decided_at'])); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                <img src="<?php echo e($decision['creator_avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($decision['creator_name'])); ?>" 
                     alt="<?php echo e($decision['creator_name']); ?>"
                     class="w-10 h-10 rounded-full">
                <div>
                    <p class="text-sm font-semibold text-gray-900"><?php echo e($decision['creator_name']); ?></p>
                    <p class="text-xs text-gray-500">Decision Maker</p>
                </div>
            </div>
        </div>

        <!-- Options -->
        <?php if (count($options) > 0): ?>
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Options Considered (<?php echo count($options); ?>)</h2>
            <div class="space-y-4">
                <?php foreach ($options as $idx => $option): ?>
                <div class="bg-white rounded-xl border-2 <?php echo $option['was_chosen'] ? 'border-green-500' : 'border-gray-200'; ?> p-6">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900"><?php echo e($option['name']); ?></h3>
                            <?php if ($option['description']): ?>
                            <p class="text-gray-600 text-sm mt-1"><?php echo e($option['description']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if ($option['was_chosen']): ?>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">✓ CHOSEN</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($option['estimated_cost'] || $option['estimated_effort']): ?>
                    <div class="flex gap-4 mb-4 text-sm">
                        <?php if ($option['estimated_cost']): ?>
                        <span class="text-gray-600">💰 <?php echo e($option['estimated_cost']); ?></span>
                        <?php endif; ?>
                        <?php if ($option['estimated_effort']): ?>
                        <span class="text-gray-600">⏱️ <?php echo e($option['estimated_effort']); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php if (count($option['pros_array']) > 0): ?>
                        <div>
                            <h4 class="text-sm font-bold text-emerald-700 mb-2">✓ Pros (<?php echo count($option['pros_array']); ?>)</h4>
                            <ul class="space-y-1">
                                <?php foreach ($option['pros_array'] as $pro): ?>
                                <li class="text-sm text-gray-700 flex gap-2">
                                    <span class="text-emerald-600">•</span>
                                    <span><?php echo e($pro); ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <?php if (count($option['cons_array']) > 0): ?>
                        <div>
                            <h4 class="text-sm font-bold text-red-700 mb-2">✗ Cons (<?php echo count($option['cons_array']); ?>)</h4>
                            <ul class="space-y-1">
                                <?php foreach ($option['cons_array'] as $con): ?>
                                <li class="text-sm text-gray-700 flex gap-2">
                                    <span class="text-red-600">•</span>
                                    <span><?php echo e($con); ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Voting Section -->
                    <div class="mt-4 pt-4 border-t border-gray-200" id="voting-section-<?php echo $option['id']; ?>">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-semibold text-gray-700">Your vote:</span>
                                <button 
                                    onclick="voteForOption(<?php echo $decision['id']; ?>, <?php echo $option['id']; ?>, '<?php echo e($option['name']); ?>')"
                                    class="vote-button px-4 py-2 border-2 border-indigo-200 text-indigo-600 rounded-lg hover:bg-indigo-50 hover:border-indigo-400 transition-all text-sm font-medium"
                                    data-option-id="<?php echo $option['id']; ?>"
                                >
                                    <span class="vote-icon">👍</span>
                                    <span class="vote-text ml-1">Vote</span>
                                </button>
                            </div>
                            <div class="text-sm">
                                <span class="vote-count font-bold text-indigo-600" data-option-id="<?php echo $option['id']; ?>">0</span>
                                <span class="text-gray-500"> votes</span>
                            </div>
                        </div>
                        
                        <!-- Voters list -->
                        <div class="voters-list hidden mt-3 p-3 bg-gray-50 rounded-lg" id="voters-<?php echo $option['id']; ?>">
                            <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Who voted:</div>
                            <div class="flex flex-wrap gap-2" id="voters-list-<?php echo $option['id']; ?>">
                                <!-- Populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Final Decision & Rationale -->
        <?php if ($decision['final_decision'] || $decision['rationale']): ?>
        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl border-2 border-indigo-200 p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3">Final Decision</h2>
            <?php if ($decision['final_decision']): ?>
            <p class="text-gray-900 font-semibold mb-3"><?php echo e($decision['final_decision']); ?></p>
            <?php endif; ?>
            <?php if ($decision['rationale']): ?>
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Rationale</h3>
                <p class="text-gray-700"><?php echo e($decision['rationale']); ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Comments Section -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Discussion (<?php echo count($comments); ?>)</h2>
            
            <!-- Add comment form -->
            <div class="mb-6">
                <textarea 
                    placeholder="Add your thoughts..."
                    rows="3"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                ></textarea>
                <button class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                    Post Comment
                </button>
            </div>

            <!-- Comments list -->
            <?php if (count($comments) > 0): ?>
            <div class="space-y-4">
                <?php foreach ($comments as $comment): ?>
                <div class="flex gap-3">
                    <img src="<?php echo e($comment['avatar_url'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($comment['user_name'])); ?>" 
                         alt="<?php echo e($comment['user_name']); ?>"
                         class="w-10 h-10 rounded-full">
                    <div class="flex-1">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="font-semibold text-gray-900 text-sm"><?php echo e($comment['user_name']); ?></p>
                            <p class="text-gray-700 text-sm mt-1"><?php echo e($comment['content']); ?></p>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 ml-3">
                            <?php echo date('M j, Y \a\t g:i A', strtotime($comment['created_at'])); ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-gray-500 text-center py-8">No comments yet. Be the first to share your thoughts!</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Voting System
        const decisionId = <?php echo $decision['id']; ?>;
        const currentUserId = <?php echo $user['id']; ?>;
        let userVote = null;

        // Load votes on page load
        document.addEventListener('DOMContentLoaded', loadVotes);

        async function loadVotes() {
            try {
                const response = await fetch(`<?php echo APP_URL; ?>/api/vote.php?decision_id=${decisionId}`);
                const data = await response.json();
                
                if (data.success) {
                    // Update vote counts
                    Object.entries(data.vote_counts || {}).forEach(([optionId, count]) => {
                        const countEl = document.querySelector(`.vote-count[data-option-id="${optionId}"]`);
                        if (countEl) countEl.textContent = count;
                    });
                    
                    // Show who voted
                    (data.votes || []).forEach(vote => {
                        addVoterToList(vote.option_id, vote.voter_name, vote.avatar_url);
                    });
                    
                    // Mark user's vote
                    if (data.user_vote) {
                        userVote = data.user_vote.option_id;
                        updateVoteButton(data.user_vote.option_id, true);
                    }
                }
            } catch (error) {
                console.error('Failed to load votes:', error);
            }
        }

        async function voteForOption(decisionId, optionId, optionName) {
            // If already voted for this option, show message
            if (userVote === optionId) {
                alert('You already voted for this option!');
                return;
            }
            
            // Confirm if changing vote
            if (userVote && userVote !== optionId) {
                if (!confirm(`Change your vote to "${optionName}"?`)) {
                    return;
                }
            }
            
            try {
                const response = await fetch('<?php echo APP_URL; ?>/api/vote.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        decision_id: decisionId,
                        option_id: optionId
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Remove old vote styling
                    if (userVote) {
                        updateVoteButton(userVote, false);
                    }
                    
                    // Add new vote styling
                    userVote = optionId;
                    updateVoteButton(optionId, true);
                    
                    // Reload votes to update counts
                    loadVotes();
                    
                    // Show success message
                    showToast(`✅ Voted for "${optionName}"!`);
                } else {
                    alert('Error: ' + (data.error || 'Failed to vote'));
                }
            } catch (error) {
                console.error('Vote error:', error);
                alert('Failed to submit vote. Please try again.');
            }
        }

        function updateVoteButton(optionId, voted) {
            const button = document.querySelector(`.vote-button[data-option-id="${optionId}"]`);
            if (!button) return;
            
            if (voted) {
                button.classList.remove('border-indigo-200', 'text-indigo-600', 'hover:bg-indigo-50');
                button.classList.add('bg-indigo-600', 'text-white', 'border-indigo-600');
                button.querySelector('.vote-icon').textContent = '✓';
                button.querySelector('.vote-text').textContent = 'Your Vote';
            } else {
                button.classList.add('border-indigo-200', 'text-indigo-600', 'hover:bg-indigo-50');
                button.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-600');
                button.querySelector('.vote-icon').textContent = '👍';
                button.querySelector('.vote-text').textContent = 'Vote';
            }
        }

        function addVoterToList(optionId, voterName, avatarUrl) {
            const votersList = document.getElementById(`voters-list-${optionId}`);
            const votersContainer = document.getElementById(`voters-${optionId}`);
            
            if (!votersList) return;
            
            // Show the voters list
            votersContainer.classList.remove('hidden');
            
            // Check if already added
            if (votersList.querySelector(`[data-voter="${voterName}"]`)) return;
            
            const voterEl = document.createElement('div');
            voterEl.className = 'flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-gray-200';
            voterEl.setAttribute('data-voter', voterName);
            voterEl.innerHTML = `
                <img src="${avatarUrl || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(voterName)}" 
                     alt="${voterName}"
                     class="w-6 h-6 rounded-full">
                <span class="text-sm text-gray-700">${voterName}</span>
            `;
            votersList.appendChild(voterEl);
        }

        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-xl z-50 animate-fade-in';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>