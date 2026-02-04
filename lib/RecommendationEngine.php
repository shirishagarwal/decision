<?php
/**
 * AI Recommendation Engine
 * 
 * Analyzes past decisions to recommend the best option for current decisions
 * This is the KILLER FEATURE - tells you what to do based on what worked before
 */

require_once __DIR__ . '/../config.php';

class RecommendationEngine {
    private $pdo;
    private $organizationId;
    private $userId;
    
    public function __construct($organizationId, $userId) {
        $this->pdo = getDbConnection();
        $this->organizationId = $organizationId;
        $this->userId = $userId;
    }
    
    /**
     * Generate recommendations for a decision
     * 
     * @param array $decisionData - Current decision being created
     * @return array - Recommendations with confidence scores
     */
    public function generateRecommendations($decisionData) {
        // Step 1: Find similar past decisions
        $similarDecisions = $this->findSimilarDecisions($decisionData);
        
        if (empty($similarDecisions)) {
            return [
                'has_recommendations' => false,
                'reason' => 'Not enough historical data to make recommendations',
                'similar_count' => 0
            ];
        }
        
        // Step 2: Analyze what worked vs what didn't
        $patterns = $this->analyzeOutcomePatterns($similarDecisions);
        
        // Step 3: Score current options against patterns
        $optionScores = $this->scoreOptions($decisionData['options'] ?? [], $patterns, $similarDecisions);
        
        // Step 4: Generate recommendation
        $recommendation = $this->generateRecommendation($optionScores, $patterns, $similarDecisions);
        
        return $recommendation;
    }
    
    /**
     * Find similar decisions from history
     */
    private function findSimilarDecisions($decisionData) {
        $category = $decisionData['category'] ?? '';
        $title = $decisionData['title'] ?? '';
        $problem = $decisionData['problem_statement'] ?? '';
        
        // Extract keywords
        $keywords = $this->extractKeywords($title . ' ' . $problem);
        
        if (empty($keywords) && empty($category)) {
            return [];
        }
        
        // Build query to find similar decisions
        $sql = "
            SELECT 
                d.*,
                d.review_rating as outcome_rating,
                d.actual_outcome,
                d.review_rating_reason,
                u.name as creator_name
            FROM decisions d
            INNER JOIN workspaces w ON d.workspace_id = w.id
            LEFT JOIN users u ON d.created_by = u.id
            WHERE w.organization_id = ?
            AND d.review_completed_at IS NOT NULL
            AND d.review_rating IS NOT NULL
        ";
        
        $params = [$this->organizationId];
        
        // Filter by category if available
        if ($category) {
            $sql .= " AND d.category = ?";
            $params[] = $category;
        }
        
        // Filter by keywords
        if (!empty($keywords)) {
            $keywordConditions = [];
            foreach ($keywords as $keyword) {
                $keywordConditions[] = "(d.title LIKE ? OR d.problem_statement LIKE ?)";
                $params[] = "%{$keyword}%";
                $params[] = "%{$keyword}%";
            }
            $sql .= " AND (" . implode(' OR ', $keywordConditions) . ")";
        }
        
        $sql .= " ORDER BY d.review_completed_at DESC LIMIT 20";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $decisions = $stmt->fetchAll();
        
        // Calculate similarity scores
        foreach ($decisions as &$decision) {
            $decision['similarity_score'] = $this->calculateSimilarity(
                $decisionData,
                $decision
            );
        }
        
        // Sort by similarity and take top 10
        usort($decisions, function($a, $b) {
            return $b['similarity_score'] <=> $a['similarity_score'];
        });
        
        return array_slice($decisions, 0, 10);
    }
    
    /**
     * Extract keywords from text
     */
    private function extractKeywords($text) {
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 
                      'of', 'with', 'by', 'from', 'we', 'should', 'will', 'can', 'would',
                      'could', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had'];
        
        $text = strtolower($text);
        $words = preg_split('/\s+/', $text);
        
        $keywords = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
        });
        
        return array_unique(array_slice($keywords, 0, 10));
    }
    
    /**
     * Calculate similarity between current decision and past decision
     */
    private function calculateSimilarity($current, $past) {
        $score = 0;
        
        // Category match (40% weight)
        if (isset($current['category']) && $current['category'] === $past['category']) {
            $score += 0.4;
        }
        
        // Keyword overlap (40% weight)
        $currentKeywords = $this->extractKeywords(
            ($current['title'] ?? '') . ' ' . ($current['problem_statement'] ?? '')
        );
        $pastKeywords = $this->extractKeywords(
            $past['title'] . ' ' . $past['problem_statement']
        );
        
        $overlap = count(array_intersect($currentKeywords, $pastKeywords));
        $total = count(array_unique(array_merge($currentKeywords, $pastKeywords)));
        
        if ($total > 0) {
            $score += 0.4 * ($overlap / $total);
        }
        
        // Recency bonus (20% weight)
        $daysAgo = (time() - strtotime($past['created_at'])) / 86400;
        $recencyScore = max(0, 1 - ($daysAgo / 365)); // Decay over 1 year
        $score += 0.2 * $recencyScore;
        
        return $score;
    }
    
    /**
     * Analyze patterns in outcomes
     */
    private function analyzeOutcomePatterns($similarDecisions) {
        $successful = [];
        $failed = [];
        
        foreach ($similarDecisions as $decision) {
            // Get the chosen option
            $chosenOption = $this->getChosenOption($decision['id']);
            
            if (!$chosenOption) continue;
            
            $decisionData = [
                'decision' => $decision,
                'chosen_option' => $chosenOption,
                'rating' => $decision['outcome_rating']
            ];
            
            if ($decision['outcome_rating'] >= 4) {
                $successful[] = $decisionData;
            } else if ($decision['outcome_rating'] <= 2) {
                $failed[] = $decisionData;
            }
        }
        
        // Extract success patterns
        $successPatterns = $this->extractPatterns($successful);
        $failurePatterns = $this->extractPatterns($failed);
        
        return [
            'successful' => $successful,
            'failed' => $failed,
            'success_patterns' => $successPatterns,
            'failure_patterns' => $failurePatterns,
            'total_analyzed' => count($similarDecisions),
            'success_rate' => count($similarDecisions) > 0 
                ? round((count($successful) / count($similarDecisions)) * 100) 
                : 0
        ];
    }
    
    /**
     * Get the chosen option for a decision
     */
    private function getChosenOption($decisionId) {
        $stmt = $this->pdo->prepare("
            SELECT o.* FROM options o
            INNER JOIN decisions d ON o.decision_id = d.id
            WHERE d.id = ?
            AND d.chosen_option_id = o.id
            LIMIT 1
        ");
        $stmt->execute([$decisionId]);
        return $stmt->fetch();
    }
    
    /**
     * Extract common patterns from decisions
     */
    private function extractPatterns($decisions) {
        if (empty($decisions)) return [];
        
        $patterns = [];
        
        // Analyze option characteristics
        $optionNames = [];
        $keywords = [];
        
        foreach ($decisions as $data) {
            $option = $data['chosen_option'];
            
            // Collect option name keywords
            $nameWords = $this->extractKeywords($option['name'] ?? '');
            foreach ($nameWords as $word) {
                $keywords[$word] = ($keywords[$word] ?? 0) + 1;
            }
            
            // Collect description keywords
            $descWords = $this->extractKeywords($option['description'] ?? '');
            foreach ($descWords as $word) {
                $keywords[$word] = ($keywords[$word] ?? 0) + 1;
            }
        }
        
        // Find most common keywords (appear in >30% of decisions)
        $threshold = count($decisions) * 0.3;
        $commonKeywords = array_filter($keywords, function($count) use ($threshold) {
            return $count >= $threshold;
        });
        
        arsort($commonKeywords);
        
        return [
            'common_keywords' => array_keys(array_slice($commonKeywords, 0, 5)),
            'sample_size' => count($decisions),
            'avg_rating' => array_sum(array_column($decisions, 'rating')) / count($decisions)
        ];
    }
    
    /**
     * Score current options against patterns
     */
    private function scoreOptions($options, $patterns, $similarDecisions) {
        if (empty($options)) return [];
        
        $scored = [];
        
        foreach ($options as $option) {
            $score = [
                'option' => $option,
                'success_score' => 0,
                'failure_score' => 0,
                'confidence' => 0,
                'reasoning' => []
            ];
            
            // Check against success patterns
            if (!empty($patterns['success_patterns']['common_keywords'])) {
                $optionText = strtolower(($option['name'] ?? '') . ' ' . ($option['description'] ?? ''));
                $matches = 0;
                
                foreach ($patterns['success_patterns']['common_keywords'] as $keyword) {
                    if (strpos($optionText, $keyword) !== false) {
                        $matches++;
                        $score['reasoning'][] = "Contains success keyword: '{$keyword}'";
                    }
                }
                
                $score['success_score'] = $matches / max(1, count($patterns['success_patterns']['common_keywords']));
            }
            
            // Check against failure patterns
            if (!empty($patterns['failure_patterns']['common_keywords'])) {
                $optionText = strtolower(($option['name'] ?? '') . ' ' . ($option['description'] ?? ''));
                $matches = 0;
                
                foreach ($patterns['failure_patterns']['common_keywords'] as $keyword) {
                    if (strpos($optionText, $keyword) !== false) {
                        $matches++;
                        $score['reasoning'][] = "⚠️ Contains failure keyword: '{$keyword}'";
                    }
                }
                
                $score['failure_score'] = $matches / max(1, count($patterns['failure_patterns']['common_keywords']));
            }
            
            // Calculate overall confidence
            $score['confidence'] = max(0, $score['success_score'] - $score['failure_score']);
            $score['confidence'] = min(1, $score['confidence']); // Cap at 1.0
            
            // Adjust based on sample size
            $sampleSizeConfidence = min(1, count($similarDecisions) / 10);
            $score['confidence'] = $score['confidence'] * $sampleSizeConfidence;
            
            $scored[] = $score;
        }
        
        // Sort by confidence
        usort($scored, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });
        
        return $scored;
    }
    
    /**
     * Generate final recommendation
     */
    private function generateRecommendation($optionScores, $patterns, $similarDecisions) {
        if (empty($optionScores)) {
            return [
                'has_recommendations' => false,
                'reason' => 'No options provided to analyze'
            ];
        }
        
        $topOption = $optionScores[0];
        $worstOption = end($optionScores);
        
        // Only recommend if we have reasonable confidence
        if ($topOption['confidence'] < 0.3) {
            return [
                'has_recommendations' => false,
                'reason' => 'Not enough confidence to make a recommendation (need more historical data)',
                'similar_count' => count($similarDecisions)
            ];
        }
        
        // Build recommendation
        $recommendation = [
            'has_recommendations' => true,
            'recommended_option' => $topOption['option'],
            'not_recommended_option' => $worstOption['confidence'] < 0.3 ? $worstOption['option'] : null,
            'confidence' => round($topOption['confidence'] * 100),
            'similar_count' => count($similarDecisions),
            'success_rate' => $patterns['success_rate'],
            'reasoning' => $topOption['reasoning'],
            'similar_decisions' => array_slice($similarDecisions, 0, 3), // Top 3 most similar
            'patterns' => [
                'successful' => count($patterns['successful']),
                'failed' => count($patterns['failed'])
            ]
        ];
        
        // Generate human-readable explanation
        $recommendation['explanation'] = $this->generateExplanation($recommendation, $patterns);
        
        return $recommendation;
    }
    
    /**
     * Generate human-readable explanation
     */
    private function generateExplanation($recommendation, $patterns) {
        $explanation = [];
        
        $successCount = $patterns['successful'] ? count($patterns['successful']) : 0;
        $failCount = $patterns['failed'] ? count($patterns['failed']) : 0;
        
        $explanation[] = "Based on {$recommendation['similar_count']} similar decisions:";
        
        if ($successCount > 0) {
            $explanation[] = "✅ {$successCount} succeeded (rated 4-5 stars)";
        }
        
        if ($failCount > 0) {
            $explanation[] = "❌ {$failCount} failed (rated 1-2 stars)";
        }
        
        if (!empty($recommendation['similar_decisions'])) {
            $explanation[] = "\nMost similar past decisions:";
            foreach (array_slice($recommendation['similar_decisions'], 0, 3) as $sd) {
                $rating = $sd['outcome_rating'];
                $emoji = $rating >= 4 ? '✅' : ($rating <= 2 ? '❌' : '⚠️');
                $explanation[] = "{$emoji} \"{$sd['title']}\" ({$sd['created_at']}) - Rating: {$rating}/5";
            }
        }
        
        return implode("\n", $explanation);
    }
}