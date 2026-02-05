<?php
/**
 * File Path: lib/Intelligence.php
 * Description: Logic to calculate Strategic Moat IQ and Percentiles.
 */

class Intelligence {
    public static function calculateIQ($orgId) {
        $pdo = getDbConnection();
        
        // 1. Base Score (The starting point)
        $iq = 20;

        // 2. Volume Score: +5 points per decision recorded (Max 50)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM decisions WHERE organization_id = ?");
        $stmt->execute([$orgId]);
        $decisionCount = $stmt->fetchColumn();
        $iq += min(($decisionCount * 5), 50);

        // 3. Proactivity Score: +10 points per Stress Test run (Max 50)
        $stmt = $pdo->prepare("
            SELECT COUNT(ds.id) FROM decision_simulations ds
            JOIN decisions d ON ds.decision_id = d.id
            WHERE d.organization_id = ?
        ");
        $stmt->execute([$orgId]);
        $simCount = $stmt->fetchColumn();
        $iq += min(($simCount * 10), 50);

        // 4. Learning Loop Score: +15 points per outcome review (Max 80)
        $stmt = $pdo->prepare("SELECT review_rating FROM decisions WHERE organization_id = ? AND status = 'Implemented'");
        $stmt->execute([$orgId]);
        $reviews = $stmt->fetchAll();
        
        foreach ($reviews as $r) {
            $iq += 15;
            // Bonus for high-quality outcomes
            if ($r['review_rating'] == 'much_better') $iq += 5;
            if ($r['review_rating'] == 'better') $iq += 2;
            if ($r['review_rating'] == 'worse') $iq -= 2;
        }

        return min($iq, 200); // Cap at 200
    }

    public static function getPercentile($iq) {
        // Simple curve: 200 IQ = Top 1%, 20 IQ = Bottom 90%
        if ($iq >= 180) return "Top 1%";
        if ($iq >= 150) return "Top 5%";
        if ($iq >= 120) return "Top 12%";
        if ($iq >= 80) return "Top 25%";
        if ($iq >= 50) return "Top 45%";
        return "Establishing Baseline";
    }
}
