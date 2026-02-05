<?php
/**
 * File Path: lib/Intelligence.php
 * Description: Logic to calculate Strategic Moat IQ based on real metrics.
 */

class Intelligence {
    /**
     * Calculates the Moat IQ score (0-200).
     */
    public static function calculateIQ($orgId) {
        $pdo = getDbConnection();
        $iq = 20; // Starting baseline

        // 1. Volume: +5 points per decision (Max 50)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM decisions WHERE organization_id = ?");
        $stmt->execute([$orgId]);
        $iq += min(($stmt->fetchColumn() * 5), 50);

        // 2. Proactivity: +10 points per AI Stress Test (Max 50)
        $stmt = $pdo->prepare("
            SELECT COUNT(ds.id) FROM decision_simulations ds
            JOIN decisions d ON ds.decision_id = d.id
            WHERE d.organization_id = ?
        ");
        $stmt->execute([$orgId]);
        $iq += min(($stmt->fetchColumn() * 10), 50);

        // 3. Learning Loop: +15 points per outcome reviewed (Max 80)
        $stmt = $pdo->prepare("SELECT review_rating FROM decisions WHERE organization_id = ? AND status = 'Implemented'");
        $stmt->execute([$orgId]);
        $reviews = $stmt->fetchAll();
        
        foreach ($reviews as $r) {
            $iq += 15;
            // Bonus for success or high-fidelity learning
            if ($r['review_rating'] == 'much_better') $iq += 5;
            if ($r['review_rating'] == 'much_worse') $iq += 2; // Learning from failure is valuable
        }

        return min($iq, 200);
    }

    public static function getPercentile($iq) {
        if ($iq >= 180) return "Top 1%";
        if ($iq >= 150) return "Top 5%";
        if ($iq >= 120) return "Top 12%";
        if ($iq >= 80) return "Top 25%";
        return "Establishing Baseline";
    }
}
