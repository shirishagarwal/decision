<?php
require_once __DIR__ . '/config.php';
if (!isLoggedIn()) redirect(APP_URL . '/index.php');

$user = getCurrentUser();
$pdo = getDbConnection();

// Get decision to review
$decisionId = $_GET['id'] ?? null;
if (!$decisionId) {
    redirect(APP_URL . '/dashboard.php');
}

// Fetch decision with options
$stmt = $pdo->prepare("
    SELECT d.*, u.name as creator_name, u.avatar_url as creator_avatar
    FROM decisions d
    LEFT JOIN users u ON d.created_by = u.id
    WHERE d.id = ? AND d.workspace_id IN (
        SELECT workspace_id FROM workspace_members WHERE user_id = ?
    )
");
$stmt->execute([$decisionId, $user['id']]);
$decision = $stmt->fetch();

if (!$decision) {
    die('Decision not found or access denied');
}

// Get chosen option
$stmt = $pdo->prepare("SELECT * FROM options WHERE decision_id = ? AND was_chosen = 1");
$stmt->execute([$decisionId]);
$chosenOption = $stmt->fetch();

// Check if already reviewed
$alreadyReviewed = !empty($decision['review_completed_at']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Decision - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .rating-btn { transition: all 0.2s ease; }
        .rating-btn:hover { transform: scale(1.05); }
        .rating-btn.selected { transform: scale(1.1); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.3); }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-purple-50 to-pink-50 min-h-screen">
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="decision.php?id=<?php echo $decisionId; ?>" class="flex items-center gap-2 text-gray-600 hover:text-gray-900">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span class="font-medium">Back to Decision</span>
            </a>
            <h1 class="text-lg font-bold text-gray-900">Review Decision</h1>
            <div class="w-32"></div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <?php if ($alreadyReviewed): ?>
        <!-- Already Reviewed -->
        <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
            <div class="text-6xl mb-4">✅</div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Already Reviewed!</h2>
            <p class="text-gray-600 mb-6">You reviewed this decision on <?php echo date('F j, Y', strtotime($decision['review_completed_at'])); ?></p>
            
            <?php if ($decision['actual_outcome']): ?>
            <div class="bg-gray-50 rounded-xl p-6 text-left mb-6">
                <h3 class="font-bold text-gray-900 mb-2">Your Review:</h3>
                <p class="text-gray-700 mb-4"><?php echo nl2br(htmlspecialchars($decision['actual_outcome'])); ?></p>
                
                <?php if ($decision['review_rating']): ?>
                <div class="flex items-center gap-2">
                    <span class="font-semibold">Rating:</span>
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm">
                        <?php echo ucfirst(str_replace('_', ' ', $decision['review_rating'])); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <a href="decision.php?id=<?php echo $decisionId; ?>" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                View Full Decision
            </a>
        </div>
        <?php else: ?>
        
        <!-- Review Form -->
        <div class="space-y-6">
            <!-- Time Badge -->
            <div class="text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Review Date: <?php echo date('F j, Y', strtotime($decision['review_date'])); ?>
                </div>
            </div>

            <!-- Decision Summary -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">What You Decided</h2>
                
                <div class="mb-6">
                    <div class="text-sm font-semibold text-gray-500 uppercase mb-2">Problem</div>
                    <p class="text-lg text-gray-900"><?php echo htmlspecialchars($decision['title']); ?></p>
                </div>

                <?php if ($chosenOption): ?>
                <div class="mb-6 p-4 bg-emerald-50 border-2 border-emerald-200 rounded-xl">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-2xl">✓</span>
                        <span class="text-sm font-semibold text-emerald-800 uppercase">You Chose</span>
                    </div>
                    <p class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($chosenOption['name']); ?></p>
                    <?php if ($chosenOption['description']): ?>
                    <p class="text-sm text-gray-600 mt-2"><?php echo htmlspecialchars($chosenOption['description']); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="mb-6">
                    <div class="text-sm font-semibold text-gray-500 uppercase mb-2">What You Expected</div>
                    <p class="text-gray-900 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <?php echo nl2br(htmlspecialchars($decision['expected_outcome'] ?: 'No expectation recorded')); ?>
                    </p>
                </div>

                <div class="text-sm text-gray-500">
                    Decided: <?php echo date('F j, Y', strtotime($decision['decided_at'] ?: $decision['created_at'])); ?>
                    (<?php 
                        $days = floor((time() - strtotime($decision['decided_at'] ?: $decision['created_at'])) / 86400);
                        echo $days . ' days ago';
                    ?>)
                </div>
            </div>

            <!-- Review Form -->
            <form id="reviewForm" class="space-y-6">
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">How Did It Turn Out?</h2>

                    <!-- Rating -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-900 mb-4">Overall, this decision worked out:</label>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                            <button type="button" onclick="selectRating('much_worse')" 
                                    class="rating-btn px-4 py-3 border-2 rounded-lg text-center hover:border-red-500 focus:outline-none"
                                    data-rating="much_worse">
                                <div class="text-2xl mb-1">😞</div>
                                <div class="text-xs font-medium">Much Worse</div>
                            </button>
                            <button type="button" onclick="selectRating('worse')" 
                                    class="rating-btn px-4 py-3 border-2 rounded-lg text-center hover:border-orange-500 focus:outline-none"
                                    data-rating="worse">
                                <div class="text-2xl mb-1">😕</div>
                                <div class="text-xs font-medium">Worse</div>
                            </button>
                            <button type="button" onclick="selectRating('as_expected')" 
                                    class="rating-btn px-4 py-3 border-2 rounded-lg text-center hover:border-blue-500 focus:outline-none"
                                    data-rating="as_expected">
                                <div class="text-2xl mb-1">😐</div>
                                <div class="text-xs font-medium">As Expected</div>
                            </button>
                            <button type="button" onclick="selectRating('better')" 
                                    class="rating-btn px-4 py-3 border-2 rounded-lg text-center hover:border-green-500 focus:outline-none"
                                    data-rating="better">
                                <div class="text-2xl mb-1">🙂</div>
                                <div class="text-xs font-medium">Better</div>
                            </button>
                            <button type="button" onclick="selectRating('much_better')" 
                                    class="rating-btn px-4 py-3 border-2 rounded-lg text-center hover:border-emerald-500 focus:outline-none"
                                    data-rating="much_better">
                                <div class="text-2xl mb-1">😄</div>
                                <div class="text-xs font-medium">Much Better</div>
                            </button>
                        </div>
                        <input type="hidden" id="rating" name="rating" required>
                    </div>

                    <!-- Actual Outcome -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            What actually happened? *
                        </label>
                        <textarea 
                            id="actualOutcome"
                            name="actual_outcome"
                            rows="4"
                            required
                            placeholder="Describe the real outcome, results, and any surprises..."
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                        ></textarea>
                    </div>

                    <!-- Lessons Learned -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            What did you learn? (Optional)
                        </label>
                        <textarea 
                            id="lessonsLearned"
                            name="lessons_learned"
                            rows="3"
                            placeholder="What would you do differently next time? Any insights?"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                        ></textarea>
                    </div>

                    <!-- Would Decide Same -->
                    <div class="mb-6">
                        <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition">
                            <input 
                                type="checkbox" 
                                id="wouldDecideSame"
                                name="would_decide_same"
                                class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500"
                            >
                            <span class="text-sm font-medium text-gray-900">
                                Knowing what I know now, I would make the same decision again
                            </span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <button 
                        type="submit"
                        class="w-full px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 font-bold text-lg"
                    >
                        Complete Review & Get Smarter! 🧠
                    </button>
                </div>
            </form>

            <!-- Why This Matters -->
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 border-2 border-purple-200 rounded-xl p-6">
                <div class="flex items-start gap-3">
                    <span class="text-3xl">💡</span>
                    <div>
                        <h3 class="font-bold text-purple-900 mb-2">Why Reviewing Matters</h3>
                        <p class="text-sm text-purple-800">
                            By reviewing your decisions, you're building your Decision Intelligence. 
                            We'll track your accuracy over time and help you spot patterns in your decision-making. 
                            This is how you get smarter! 🚀
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        let selectedRating = null;

        function selectRating(rating) {
            selectedRating = rating;
            document.getElementById('rating').value = rating;
            
            // Visual feedback
            document.querySelectorAll('.rating-btn').forEach(btn => {
                btn.classList.remove('selected', 'border-red-500', 'border-orange-500', 'border-blue-500', 'border-green-500', 'border-emerald-500');
                btn.classList.add('border-gray-300');
            });
            
            const selectedBtn = document.querySelector(`[data-rating="${rating}"]`);
            selectedBtn.classList.add('selected');
            selectedBtn.classList.remove('border-gray-300');
            
            // Color based on rating
            const colors = {
                'much_worse': 'border-red-500',
                'worse': 'border-orange-500',
                'as_expected': 'border-blue-500',
                'better': 'border-green-500',
                'much_better': 'border-emerald-500'
            };
            selectedBtn.classList.add(colors[rating]);
        }

        document.getElementById('reviewForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!selectedRating) {
                alert('Please select a rating for how the decision worked out');
                return;
            }
            
            const formData = {
                decision_id: <?php echo $decisionId; ?>,
                rating: selectedRating,
                actual_outcome: document.getElementById('actualOutcome').value,
                lessons_learned: document.getElementById('lessonsLearned').value,
                would_decide_same: document.getElementById('wouldDecideSame').checked
            };
            
            try {
                const response = await fetch('<?php echo APP_URL; ?>/api/review-decision.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Success! Show celebration and redirect
                    alert('🎉 Review completed! You\'re getting smarter!');
                    window.location.href = 'decision.php?id=<?php echo $decisionId; ?>';
                } else {
                    alert('Error: ' + (data.error || 'Failed to save review'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to submit review. Please try again.');
            }
        });
    </script>
</body>
</html>
