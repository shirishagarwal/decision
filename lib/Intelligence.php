<?php
/**
 * File Path: lib/Intelligence.php
 * Description: Real calculation of Decision Maturity and Intelligence stats.
 */

class Intelligence {
    
    /**
     * Calculates the Decision Maturity Index (DMI) out of 200.
     * Logic:
     * - Base: 20 pts
     * - Per Decision Logged: +10 pts (Max 80)
     * - Per Connector Active: +15 pts (Max 60)
     * - Per Outcome Analysis: +20 pts (Max 40)
     */
    public static function calculateDMI($orgId) {
        $pdo = getDbConnection();
        $score = 20;

        // Count Decisions
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM decisions WHERE organization_id = ?");
        $stmt->execute([$orgId]);
        $decisionsCount = (int)$stmt->fetchColumn();
        $score += min($decisionsCount * 10, 80);

        // Count Active Connectors
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM data_connectors WHERE organization_id = ? AND status = 'active'");
        $stmt->execute([$orgId]);
        $connectorsCount = (int)$stmt->fetchColumn();
        $score += min($connectorsCount * 15, 60);

        // Count Decisions with Outcomes (Learning loops)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM decisions WHERE organization_id = ? AND status = 'Closed'");
        $stmt->execute([$orgId]);
        $outcomesCount = (int)$stmt->fetchColumn();
        $score += min($outcomesCount * 20, 40);

        return $score;
    }

    /**
     * Gets the total count of 'Scouted' intelligence patterns.
     */
    public static function getScoutedPatternCount() {
        $pdo = getDbConnection();
        return (int)$pdo->query("SELECT COUNT(*) FROM external_startup_failures")->fetchColumn();
    }

    public static function getSectorRanking($score) {
        if ($score > 160) return "Top 2%";
        if ($score > 120) return "Top 12%";
        if ($score > 80) return "Average";
        return "Initial";
    }
}
