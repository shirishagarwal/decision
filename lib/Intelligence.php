<?php
/**
 * File Path: lib/Intelligence.php
 * Description: The math behind the "Moat IQ." Calculates strategic accuracy and velocity.
 */

class Intelligence {
    /**
     * Calculates the Moat IQ score (0-200).
     * Logic:
     * - Base Score: 20
     * - Volume: +5 per decision (Max 50)
     * - Rigor: +10 per AI Stress Test (Max 50)
     * - Learning: +15 per Outcome Review (Max 80)
     */
    public static function calculateIQ($orgId) {
        $pdo = getDbConnection();
        $score = 20;

        // 1. Volume Points
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM decisions WHERE organization_id = ?");
        $stmt->execute([$orgId]);
        $decisionCount = $stmt->fetchColumn();
        $score += min(($decisionCount * 5), 50);

        // 2. Rigor (Stress Tests)
        $stmt = $pdo->prepare("
            SELECT COUNT(ds.id) FROM decision_simulations ds
            JOIN decisions d ON ds.decision_id = d.id
            WHERE d.organization_id = ?
        ");
        $stmt->execute([$orgId]);
        $simCount = $stmt->fetchColumn();
        $score += min(($simCount * 10), 50);

        // 3. Learning Loop (Closed Outcomes)
        $stmt = $pdo->prepare("SELECT review_rating FROM decisions WHERE organization_id = ? AND status = 'Implemented'");
        $stmt->execute([$orgId]);
        $reviews = $stmt->fetchAll();
        
        foreach ($reviews as $r) {
            $score += 15;
            // Bonus for high accuracy (as expected) or radical learning (much worse)
            if ($r['review_rating'] === 'as_expected' || $r['review_rating'] === 'much_better') $score += 5;
        }

        return min($score, 200);
    }

    /**
     * Returns a human-readable percentile based on the score.
     */
    public static function getPercentile($iq) {
        if ($iq >= 180) return "Top 1%";
        if ($iq >= 150) return "Top 5%";
        if ($iq >= 120) return "Top 12%";
        if ($iq >= 80) return "Top 25%";
        return "Baseline Established";
    }
}
