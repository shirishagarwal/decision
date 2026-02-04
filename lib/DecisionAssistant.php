<?php
/**
 * AI Decision Assistant - Pattern Analysis Engine
 * 
 * This analyzes decision history to:
 * 1. Detect patterns (timeline accuracy, budget accuracy, success predictors)
 * 2. Generate warnings for new decisions
 * 3. Calculate decision maker accuracy
 * 4. Find similar past decisions
 */

require_once __DIR__ . '/../config.php';

class DecisionAssistant {
    private $pdo;
    private $userId;
    private $orgId;
    
    public function __construct($userId, $orgId = null) {
        $this->pdo = getDbConnection();
        $this->userId = $userId;
        $this->orgId = $orgId;
    }
    
    /**
     * Analyze timeline estimation accuracy
     * Returns: avg % difference between estimated and actual timelines
     */
    public function analyzeTimelineAccuracy($userId = null) {
        $userId = $userId ?? $this->userId;
        
        $sql = "
            SELECT 
                d.id,
                d.title,
                d.deadline,
                d.decided_at,
                d.review_completed_at,
                DATEDIFF(d.deadline, d.decided_at) as estimated_days,
                DATEDIFF(d.review_completed_at, d.decided_at) as actual_days,
                ((DATEDIFF(d.review_completed_at, d.decided_at) - DATEDIFF(d.deadline, d.decided_at)) / DATEDIFF(d.deadline, d.decided_at) * 100) as variance_percent
            FROM decisions d
            WHERE d.created_by = ?
            AND d.deadline IS NOT NULL
            AND d.review_completed_at IS NOT NULL
            AND DATEDIFF(d.deadline, d.decided_at) > 0
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        $decisions = $stmt->fetchAll();
        
        if (empty($decisions)) {
            return [
                'has_data' => false,
                'avg_variance' => 0,
                'pattern' => 'Not enough data',
                'sample_size' => 0
            ];
        }
        
        $totalVariance = 0;
        foreach ($decisions as $d) {
            $totalVariance += $d['variance_percent'];
        }
        
        $avgVariance = $totalVariance / count($decisions);
        
        $pattern = '';
        if ($avgVariance > 20) {
            $pattern = "You consistently underestimate timelines by " . round(abs($avgVariance)) . "%. Add buffer to your estimates.";
        } elseif ($avgVariance < -20) {
            $pattern = "You tend to overestimate timelines by " . round(abs($avgVariance)) . "%. You're more efficient than you think.";
        } else {
            $pattern = "Your timeline estimates are quite accurate (±" . round(abs($avgVariance)) . "%).";
        }
        
        return [
            'has_data' => true,
            'avg_variance' => $avgVariance,
            'pattern' => $pattern,
            'sample_size' => count($decisions),
            'decisions' => $decisions
        ];
    }
    
    /**
     * Analyze decision accuracy by category
     * Returns: success rate per category
     */
    public function analyzeAccuracyByCategory($userId = null) {
        $userId = $userId ?? $this->userId;
        
        $sql = "
            SELECT 
                d.category,
                COUNT(*) as total_decisions,
                COUNT(CASE WHEN d.review_rating >= 4 THEN 1 END) as successful,
                COUNT(CASE WHEN d.review_rating >= 4 THEN 1 END) / COUNT(*) * 100 as success_rate
            FROM decisions d
            WHERE d.created_by = ?
            AND d.review_completed_at IS NOT NULL
            GROUP BY d.category
            HAVING total_decisions >= 2
            ORDER BY success_rate DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        $categories = $stmt->fetchAll();
        
        $insights = [];
        foreach ($categories as $cat) {
            if ($cat['success_rate'] >= 80) {
                $insights[] = [
                    'type' => 'strength',
                    'category' => $cat['category'],
                    'message' => "You're excellent at {$cat['category']} decisions ({$cat['success_rate']}% success rate)",
                    'confidence' => 'high'
                ];
            } elseif ($cat['success_rate'] <= 50) {
                $insights[] = [
                    'type' => 'warning',
                    'category' => $cat['category'],
                    'message' => "Your {$cat['category']} decisions need improvement ({$cat['success_rate']}% success rate)",
                    'confidence' => 'high'
                ];
            }
        }
        
        return [
            'categories' => $categories,
            'insights' => $insights
        ];
    }
    
    /**
     * Find similar past decisions using text similarity
     */
    public function findSimilarDecisions($title, $problemStatement = '', $limit = 5) {
        // Simple keyword-based similarity for now
        // TODO: Use embeddings for better semantic similarity
        
        $keywords = $this->extractKeywords($title . ' ' . $problemStatement);
        
        if (empty($keywords)) {
            return [];
        }
        
        // Build LIKE clauses for each keyword
        $whereClauses = [];
        $params = [];
        foreach ($keywords as $keyword) {
            $whereClauses[] = "(d.title LIKE ? OR d.problem_statement LIKE ?)";
            $params[] = "%{$keyword}%";
            $params[] = "%{$keyword}%";
        }
        
        $whereSQL = implode(' OR ', $whereClauses);
        
        $sql = "
            SELECT 
                d.id,
                d.title,
                d.category,
                d.status,
                d.review_rating,
                d.review_completed_at,
                d.actual_outcome,
                u.name as creator_name
            FROM decisions d
            LEFT JOIN users u ON d.created_by = u.id
            WHERE ({$whereSQL})
            AND d.review_completed_at IS NOT NULL
        ";
        
        if ($this->orgId) {
            $sql .= " AND d.workspace_id IN (SELECT id FROM workspaces WHERE organization_id = ?)";
            $params[] = $this->orgId;
        }
        
        $sql .= " ORDER BY d.review_completed_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Extract keywords from text
     */
    private function extractKeywords($text) {
        // Remove common words
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'we', 'should', 'will'];
        
        // Convert to lowercase and split
        $words = preg_split('/\s+/', strtolower($text));
        
        // Filter out stop words and short words
        $keywords = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
        });
        
        // Return top 5 most relevant
        return array_slice(array_unique($keywords), 0, 5);
    }
    
    /**
     * Generate real-time warnings for new decision
     */
    public function generateWarnings($decisionData) {
        $warnings = [];
        
        // Check timeline estimation
        if (!empty($decisionData['deadline'])) {
            $timelineAnalysis = $this->analyzeTimelineAccuracy($this->userId);
            
            if ($timelineAnalysis['has_data'] && abs($timelineAnalysis['avg_variance']) > 20) {
                $deadlineDays = (strtotime($decisionData['deadline']) - time()) / 86400;
                
                if ($timelineAnalysis['avg_variance'] > 0) {
                    // User underestimates
                    $suggestedDays = round($deadlineDays * (1 + $timelineAnalysis['avg_variance'] / 100));
                    $warnings[] = [
                        'type' => 'timeline',
                        'severity' => 'warning',
                        'title' => '⚠️ Timeline Warning',
                        'message' => "You typically underestimate timelines by " . round($timelineAnalysis['avg_variance']) . "%. Consider extending deadline from " . round($deadlineDays) . " to " . $suggestedDays . " days.",
                        'data' => $timelineAnalysis
                    ];
                }
            }
        }
        
        // Check category-specific patterns
        if (!empty($decisionData['category'])) {
            $categoryAnalysis = $this->analyzeAccuracyByCategory($this->userId);
            
            foreach ($categoryAnalysis['categories'] as $cat) {
                if ($cat['category'] == $decisionData['category'] && $cat['success_rate'] < 60) {
                    $warnings[] = [
                        'type' => 'category',
                        'severity' => 'caution',
                        'title' => '💡 Category Insight',
                        'message' => "Your {$cat['category']} decisions have a {$cat['success_rate']}% success rate. Consider getting extra input from your team.",
                        'data' => $cat
                    ];
                }
            }
        }
        
        // Find similar decisions
        $similarDecisions = $this->findSimilarDecisions(
            $decisionData['title'] ?? '',
            $decisionData['problem_statement'] ?? ''
        );
        
        if (!empty($similarDecisions)) {
            $failedSimilar = array_filter($similarDecisions, function($d) {
                return $d['review_rating'] <= 2;
            });
            
            if (!empty($failedSimilar)) {
                $example = $failedSimilar[0];
                $warnings[] = [
                    'type' => 'similarity',
                    'severity' => 'danger',
                    'title' => '🚨 Similar Decision Failed',
                    'message' => "This looks similar to '{$example['title']}' which didn't work out well. Review that decision before proceeding.",
                    'data' => $example
                ];
            }
        }
        
        // Check if decision is being made too quickly
        if (!empty($decisionData['options']) && count($decisionData['options']) < 2) {
            $warnings[] = [
                'type' => 'process',
                'severity' => 'info',
                'title' => '💭 Missing Alternatives',
                'message' => "Great decisions consider multiple options. Add at least one more alternative to compare.",
                'data' => null
            ];
        }
        
        return $warnings;
    }
    
    /**
     * Get decision maker leaderboard for organization
     */
    public function getDecisionMakerLeaderboard() {
        if (!$this->orgId) {
            return [];
        }
        
        $sql = "
            SELECT 
                u.id,
                u.name,
                u.avatar_url,
                COUNT(d.id) as total_decisions,
                COUNT(CASE WHEN d.review_completed_at IS NOT NULL THEN 1 END) as reviewed_decisions,
                AVG(CASE WHEN d.review_rating IS NOT NULL THEN d.review_rating END) as avg_rating,
                COUNT(CASE WHEN d.review_rating >= 4 THEN 1 END) / COUNT(CASE WHEN d.review_rating IS NOT NULL THEN 1 END) * 100 as success_rate
            FROM users u
            INNER JOIN decisions d ON u.id = d.created_by
            INNER JOIN workspaces w ON d.workspace_id = w.id
            WHERE w.organization_id = ?
            GROUP BY u.id
            HAVING reviewed_decisions >= 3
            ORDER BY success_rate DESC, total_decisions DESC
            LIMIT 10
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->orgId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Detect decision-making patterns across organization
     */
    public function detectOrganizationPatterns() {
        if (!$this->orgId) {
            return [];
        }
        
        $patterns = [];
        
        // Pattern: Best day of week for decisions
        $sql = "
            SELECT 
                DAYNAME(d.created_at) as day_of_week,
                COUNT(*) as total,
                AVG(d.review_rating) as avg_rating
            FROM decisions d
            INNER JOIN workspaces w ON d.workspace_id = w.id
            WHERE w.organization_id = ?
            AND d.review_rating IS NOT NULL
            GROUP BY DAYNAME(d.created_at)
            ORDER BY avg_rating DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->orgId]);
        $dayData = $stmt->fetchAll();
        
        if (!empty($dayData)) {
            $best = $dayData[0];
            $worst = $dayData[count($dayData) - 1];
            
            $patterns[] = [
                'type' => 'temporal',
                'pattern' => "Decisions made on {$best['day_of_week']} tend to be most successful (avg rating: {$best['avg_rating']})",
                'recommendation' => "Schedule important decisions for {$best['day_of_week']}. Avoid {$worst['day_of_week']} if possible."
            ];
        }
        
        // Pattern: Team size vs success
        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM decision_votes WHERE decision_id = d.id) as voter_count,
                AVG(d.review_rating) as avg_rating,
                COUNT(*) as decision_count
            FROM decisions d
            INNER JOIN workspaces w ON d.workspace_id = w.id
            WHERE w.organization_id = ?
            AND d.review_rating IS NOT NULL
            GROUP BY voter_count
            HAVING decision_count >= 3
            ORDER BY avg_rating DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->orgId]);
        $teamSizeData = $stmt->fetchAll();
        
        if (!empty($teamSizeData)) {
            $best = $teamSizeData[0];
            $patterns[] = [
                'type' => 'collaboration',
                'pattern' => "Decisions with {$best['voter_count']} voters have the highest success rate",
                'recommendation' => "Aim for {$best['voter_count']} people voting on important decisions."
            ];
        }
        
        return $patterns;
    }
}