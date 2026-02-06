<?php
/**
 * File Path: lib/Intelligence.php
 * Description: Robust logic for calculating organizational strategic metrics.
 */

class Intelligence {
    
    /**
     * Calculates the Decision Maturity Index (DMI).
     * Formula based on data density, connectivity, and feedback loops.
     */
    public static function calculateDMI($pdo, $orgId) {
        if (!$orgId) return 0;
        
        $score = 20; // Baseline entry score

        try {
            // 1. Data Density: +10 pts per unique decision artifact (Max 80)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM decisions WHERE organization_id = ?");
            $stmt->execute([$orgId]);
            $count = (int)$stmt->fetchColumn();
            $score += min($count * 10, 80);

            // 2. Connectivity: +15 pts per active intelligence connector (Max 60)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM data_connectors WHERE organization_id = ? AND status = 'active'");
            $stmt->execute([$orgId]);
            $connCount = (int)$stmt->fetchColumn();
            $score += min($connCount * 15, 60);

            // 3. Feedback Velocity: +20 pts per 'Implemented/Closed' outcome (Max 40)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM decisions WHERE organization_id = ? AND status = 'Implemented'");
            $stmt->execute([$orgId]);
            $closedCount = (int)$stmt->fetchColumn();
            $score += min($closedCount * 20, 40);

        } catch (Exception $e) {
            // Log error internally, return baseline to avoid 500
            error_log("DMI Calculation Error: " . $e->getMessage());
        }

        return $score;
    }

    /**
     * Returns total count of strategic patterns ingested via the Scout engine.
     */
    public static function getScoutedPatternCount($pdo) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM external_startup_failures");
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Categorizes organizational performance relative to the sector.
     */
    public static function getSectorRanking($score) {
        if ($score >= 170) return "Top 1% Global";
        if ($score >= 140) return "Top 12% Sector";
        if ($score >= 100) return "Upper Decile";
        if ($score >= 60)  return "Standard Compliance";
        return "Initial Architecture";
    }
}
