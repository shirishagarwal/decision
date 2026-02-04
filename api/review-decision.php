<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$user = getCurrentUser();
$pdo = getDbConnection();
$data = json_decode(file_get_contents('php://input'), true);

$decisionId = $data['decision_id'] ?? null;
$rating = $data['rating'] ?? null;
$actualOutcome = $data['actual_outcome'] ?? '';
$lessonsLearned = $data['lessons_learned'] ?? '';
$wouldDecideSame = $data['would_decide_same'] ?? false;

if (!$decisionId || !$rating || !$actualOutcome) {
    jsonResponse(['error' => 'Missing required fields'], 400);
}

// Verify access to decision
$stmt = $pdo->prepare("
    SELECT d.* FROM decisions d
    INNER JOIN workspace_members wm ON d.workspace_id = wm.workspace_id
    WHERE d.id = ? AND wm.user_id = ?
");
$stmt->execute([$decisionId, $user['id']]);
$decision = $stmt->fetch();

if (!$decision) {
    jsonResponse(['error' => 'Decision not found or access denied'], 403);
}

try {
    $pdo->beginTransaction();
    
    // Update decision with review data
    $stmt = $pdo->prepare("
        UPDATE decisions 
        SET actual_outcome = ?,
            review_rating = ?,
            review_completed_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$actualOutcome, $rating, $decisionId]);
    
    // Insert into decision_reviews table for history
    $stmt = $pdo->prepare("
        INSERT INTO decision_reviews 
        (decision_id, user_id, review_date, expected_outcome, actual_outcome, rating, lessons_learned, would_decide_same)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $decisionId,
        $user['id'],
        date('Y-m-d'),
        $decision['expected_outcome'],
        $actualOutcome,
        $rating,
        $lessonsLearned,
        $wouldDecideSame ? 1 : 0
    ]);
    
    // Update user streak
    updateUserStreak($pdo, $user['id'], 'review');
    
    // Generate insights based on this review
    generateInsightsForUser($pdo, $user['id']);
    
    $pdo->commit();
    
    jsonResponse([
        'success' => true,
        'message' => 'Review saved successfully'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Review save error: " . $e->getMessage());
    jsonResponse(['error' => 'Failed to save review'], 500);
}

// Helper function to update user streaks
function updateUserStreak($pdo, $userId, $type = 'review') {
    // Get or create streak record
    $stmt = $pdo->prepare("SELECT * FROM user_streaks WHERE user_id = ?");
    $stmt->execute([$userId]);
    $streak = $stmt->fetch();
    
    $today = date('Y-m-d');
    
    if (!$streak) {
        // Create new streak
        $stmt = $pdo->prepare("
            INSERT INTO user_streaks (user_id, current_streak, longest_streak, last_review_date, total_reviews)
            VALUES (?, 1, 1, ?, 1)
        ");
        $stmt->execute([$userId, $today]);
    } else {
        $lastDate = $streak['last_review_date'];
        $currentStreak = $streak['current_streak'];
        
        if ($lastDate === $today) {
            // Already reviewed today, just increment total
            $stmt = $pdo->prepare("UPDATE user_streaks SET total_reviews = total_reviews + 1 WHERE user_id = ?");
            $stmt->execute([$userId]);
        } else {
            $daysSince = (strtotime($today) - strtotime($lastDate)) / 86400;
            
            if ($daysSince == 1) {
                // Consecutive day! Increment streak
                $newStreak = $currentStreak + 1;
                $longestStreak = max($newStreak, $streak['longest_streak']);
                
                $stmt = $pdo->prepare("
                    UPDATE user_streaks 
                    SET current_streak = ?, longest_streak = ?, last_review_date = ?, total_reviews = total_reviews + 1
                    WHERE user_id = ?
                ");
                $stmt->execute([$newStreak, $longestStreak, $today, $userId]);
            } else {
                // Streak broken, reset to 1
                $stmt = $pdo->prepare("
                    UPDATE user_streaks 
                    SET current_streak = 1, last_review_date = ?, total_reviews = total_reviews + 1
                    WHERE user_id = ?
                ");
                $stmt->execute([$today, $userId]);
            }
        }
    }
}

// Helper function to generate insights
function generateInsightsForUser($pdo, $userId) {
    // Get all reviewed decisions for this user
    $stmt = $pdo->prepare("
        SELECT d.*, dr.rating 
        FROM decisions d
        LEFT JOIN decision_reviews dr ON d.id = dr.decision_id
        WHERE d.created_by = ? AND d.review_completed_at IS NOT NULL
    ");
    $stmt->execute([$userId]);
    $decisions = $stmt->fetchAll();
    
    if (count($decisions) < 3) {
        return; // Need at least 3 reviews to generate insights
    }
    
    // Calculate accuracy by category
    $byCategory = [];
    foreach ($decisions as $decision) {
        $cat = $decision['category'];
        if (!isset($byCategory[$cat])) {
            $byCategory[$cat] = ['total' => 0, 'accurate' => 0];
        }
        $byCategory[$cat]['total']++;
        if (in_array($decision['rating'], ['as_expected', 'better', 'much_better'])) {
            $byCategory[$cat]['accurate']++;
        }
    }
    
    // Find best and worst categories
    $bestCategory = null;
    $bestAccuracy = 0;
    $worstCategory = null;
    $worstAccuracy = 100;
    
    foreach ($byCategory as $category => $stats) {
        if ($stats['total'] >= 2) { // Need at least 2 decisions in category
            $accuracy = ($stats['accurate'] / $stats['total']) * 100;
            if ($accuracy > $bestAccuracy) {
                $bestAccuracy = $accuracy;
                $bestCategory = $category;
            }
            if ($accuracy < $worstAccuracy) {
                $worstAccuracy = $accuracy;
                $worstCategory = $category;
            }
        }
    }
    
    // Generate insights
    if ($bestCategory && $bestAccuracy >= 70) {
        $stmt = $pdo->prepare("
            INSERT INTO decision_insights (user_id, insight_type, title, description, action_suggested)
            VALUES (?, 'success_factor', ?, ?, ?)
            ON DUPLICATE KEY UPDATE created_at = NOW()
        ");
        $stmt->execute([
            $userId,
            'pattern',
            "You're great at {$bestCategory} decisions!",
            "Your {$bestCategory} decisions are {$bestAccuracy}% accurate. You have strong intuition in this area.",
            "Consider mentoring others on {$bestCategory} decision-making."
        ]);
    }
    
    if ($worstCategory && $worstAccuracy < 50 && $worstCategory !== $bestCategory) {
        $stmt = $pdo->prepare("
            INSERT INTO decision_insights (user_id, insight_type, title, description, action_suggested)
            VALUES (?, 'warning', ?, ?, ?)
            ON DUPLICATE KEY UPDATE created_at = NOW()
        ");
        $stmt->execute([
            $userId,
            'warning',
            "Room for improvement in {$worstCategory}",
            "Your {$worstCategory} decisions are only {$worstAccuracy}% accurate. This might indicate a blind spot.",
            "Seek input from others before making {$worstCategory} decisions."
        ]);
    }
}
