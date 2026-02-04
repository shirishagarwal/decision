<?php
/**
 * Decision Intelligence Math
 * Calculates the Decision IQ score.
 */
class IntelligenceMetrics {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection(); // cite: 144
    }

    /**
     * Decision IQ Formula:
     * Base Score (Accuracy %) + Volume Bonus + Consistency Multiplier.
     */
    public function calculateDecisionIQ($userId) {
        // 1. Accuracy: How often were outcomes 'As Expected' or better?
        $stmt = $this->pdo->prepare("
            SELECT review_rating FROM decisions 
            WHERE created_by = ? AND review_completed_at IS NOT NULL
        ");
        $stmt->execute([$userId]);
        $reviews = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($reviews)) return 0;

        $successCount = count(array_filter($reviews, function($r) {
            return in_array($r, ['as_expected', 'better', 'much_better']);
        }));
        
        $accuracy = ($successCount / count($reviews)) * 100;

        // 2. Volume Bonus: Rewarding the 'Black Box' habit
        $volumeBonus = min(50, count($reviews) * 5);

        // 3. Consistency (Streak): Reward regular reviews
        $stmt = $this->pdo->prepare("SELECT current_streak FROM user_streaks WHERE user_id = ?");
        $stmt->execute([$userId]);
        $streak = $stmt->fetchColumn() ?: 0;
        $streakMultiplier = 1 + ($streak * 0.05); // 5% boost per day of streak

        $finalIQ = ($accuracy + $volumeBonus) * $streakMultiplier;

        return round(min(200, $finalIQ)); // Cap at 200
    }
}
