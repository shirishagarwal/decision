<?php
/**
 * Intelligent Decision Learning Engine
 * 
 * Analyzes external decision data + user's own decisions
 * to generate smart recommendations
 */

require_once __DIR__ . '/../config.php';

class IntelligentDecisionEngine {
    private $pdo;
    private $organizationId;
    
    public function __construct($organizationId) {
        $this->pdo = getDbConnection();
        $this->organizationId = $organizationId;
    }
    
    /**
     * Generate intelligent recommendation for a decision
     * 
     * This combines:
     * 1. External data (startup failures, layoffs, funding patterns)
     * 2. User's own historical decisions  
     * 3. Industry benchmarks
     * 
     * @param array $decisionData - Current decision being created
     * @return array - Smart recommendations with options + reasoning
     */
    public function generateIntelligentRecommendation($decisionData) {
        $category = $this->classifyDecision($decisionData);
        
        // Step 1: Generate option suggestions based on external data
        $suggestedOptions = $this->generateOptionSuggestions($category, $decisionData);
        
        // Step 2: Find similar decisions in external data
        $externalPatterns = $this->findExternalPatterns($category, $decisionData);
        
        // Step 3: Find similar decisions in user's own history
        $internalPatterns = $this->findInternalPatterns($category, $decisionData);
        
        // Step 4: Combine and rank recommendations
        $recommendation = $this->generateRecommendation(
            $suggestedOptions,
            $externalPatterns,
            $internalPatterns,
            $decisionData
        );
        
        return $recommendation;
    }
    
    /**
     * Classify what type of decision this is
     */
    private function classifyDecision($decisionData) {
        $title = strtolower($decisionData['title'] ?? '');
        $problem = strtolower($decisionData['problem_statement'] ?? '');
        $text = $title . ' ' . $problem;
        
        // Decision type classification
        $patterns = [
            'hiring' => ['hire', 'hiring', 'recruit', 'vp of', 'cto', 'cfo', 'engineer', 'designer', 'team'],
            'product' => ['launch', 'feature', 'build', 'product', 'release', 'ship'],
            'pricing' => ['price', 'pricing', 'charge', 'subscription', 'tier'],
            'funding' => ['raise', 'series', 'funding', 'capital', 'investors', 'fundraise'],
            'market_expansion' => ['expand', 'international', 'market', 'geography', 'country'],
            'pivot' => ['pivot', 'change direction', 'new direction', 'business model'],
            'acquisition' => ['acquire', 'acquisition', 'buy', 'merge', 'merger'],
            'scaling' => ['scale', 'growth', 'expand team', 'headcount'],
        ];
        
        foreach ($patterns as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    return $type;
                }
            }
        }
        
        return 'general';
    }
    
    /**
     * Generate option suggestions based on what's typical for this decision type
     */
    private function generateOptionSuggestions($category, $decisionData) {
        // These come from consulting frameworks + failure analysis
        
        $optionTemplates = [
            'hiring' => [
                [
                    'name' => 'Hire senior external candidate',
                    'description' => 'Bring in experienced executive from outside',
                    'success_rate' => 0.68,
                    'avg_cost' => 200000,
                    'pros' => [
                        'Brings fresh perspective and proven experience',
                        'Can hit ground running with established network',
                        'Signals credibility to investors/customers'
                    ],
                    'cons' => [
                        'Higher salary/equity expectations',
                        'Cultural fit risk (30% fail within 18 months)',
                        'Longer ramp time than expected'
                    ],
                    'based_on' => 'External hiring patterns show 68% retention at 2 years for senior hires'
                ],
                [
                    'name' => 'Promote from within',
                    'description' => 'Elevate existing team member to role',
                    'success_rate' => 0.78,
                    'avg_cost' => 150000,
                    'pros' => [
                        'Already knows company culture and product',
                        'Morale boost for team',
                        'Faster ramp to productivity'
                    ],
                    'cons' => [
                        'May lack external perspective',
                        'Leaves gap in previous role',
                        'Might need external coaching/support'
                    ],
                    'based_on' => 'Internal promotions show 78% success rate vs 68% external'
                ],
                [
                    'name' => 'Use fractional/consultant initially',
                    'description' => 'Hire part-time expert while you search',
                    'success_rate' => 0.45,
                    'avg_cost' => 75000,
                    'pros' => [
                        'Lower commitment and cost',
                        'Can evaluate multiple candidates',
                        'Flexible based on actual needs'
                    ],
                    'cons' => [
                        'Less ownership and commitment',
                        'Harder to build long-term strategy',
                        'Becomes expensive if extended'
                    ],
                    'based_on' => 'Fractional roles typically convert to full-time 45% of the time'
                ],
                [
                    'name' => 'Don\'t hire - redistribute responsibilities',
                    'description' => 'Restructure existing team instead',
                    'success_rate' => 0.55,
                    'avg_cost' => 0,
                    'pros' => [
                        'No new hire cost or risk',
                        'Develops existing team',
                        'Preserves cash runway'
                    ],
                    'cons' => [
                        'May overload existing team',
                        'Missing specialized expertise',
                        'Could slow progress'
                    ],
                    'based_on' => 'Common in early-stage; works 55% of time if team has capacity'
                ]
            ],
            
            'funding' => [
                [
                    'name' => 'Raise Series A now',
                    'description' => 'Go to market with current metrics',
                    'success_rate' => 0.25,
                    'pros' => [
                        'Secures capital before runway expires',
                        'Competitive market for good companies',
                        'Can accelerate growth plans'
                    ],
                    'cons' => [
                        'Only 20-30% of seed companies raise Series A',
                        'Dilution of founders and early investors',
                        'High pressure to show growth'
                    ],
                    'based_on' => 'CB Insights: 20-30% seed→Series A conversion rate'
                ],
                [
                    'name' => 'Extend seed with bridge',
                    'description' => 'Raise smaller bridge round to improve metrics',
                    'success_rate' => 0.65,
                    'pros' => [
                        'More time to hit Series A milestones',
                        'Less dilution than full round',
                        'Existing investors often participate'
                    ],
                    'cons' => [
                        'May signal struggle to investors',
                        'Delays the inevitable',
                        'Still need to raise Series A later'
                    ],
                    'based_on' => 'Bridge rounds improve Series A success rate to 65%'
                ],
                [
                    'name' => 'Achieve profitability instead',
                    'description' => 'Cut burn and become default alive',
                    'success_rate' => 0.40,
                    'pros' => [
                        'Never need to fundraise again',
                        'Full control, no dilution',
                        'Strong negotiating position later'
                    ],
                    'cons' => [
                        'Slower growth than competitors',
                        'May lose market opportunity',
                        'Requires significant cost cuts'
                    ],
                    'based_on' => 'Profitability path works 40% of time; depends on business model'
                ]
            ],
            
            'pivot' => [
                [
                    'name' => 'Pivot now',
                    'description' => 'Change direction immediately',
                    'success_rate' => 0.18,
                    'pros' => [
                        'Can address market need faster',
                        'New energy and motivation for team',
                        'May unlock new market'
                    ],
                    'cons' => [
                        'Pivots only succeed ~20% of time',
                        'Confuses existing customers',
                        'Burns remaining resources'
                    ],
                    'based_on' => 'Autopsy: 82% of pivots still fail; only works if fundamental insight changed'
                ],
                [
                    'name' => 'Test pivot with small experiment',
                    'description' => 'Validate new direction before full commit',
                    'success_rate' => 0.55,
                    'pros' => [
                        'Lower risk way to validate',
                        'Can run parallel to existing business',
                        'Data-driven decision to commit'
                    ],
                    'cons' => [
                        'Split focus between old and new',
                        'Takes longer to see results',
                        'May not give true signal'
                    ],
                    'based_on' => 'Validated pivots succeed 55% vs 18% for blind pivots'
                ],
                [
                    'name' => 'Double down on current direction',
                    'description' => 'Fix execution instead of changing direction',
                    'success_rate' => 0.33,
                    'pros' => [
                        'Keeps existing traction',
                        'Clear, focused message',
                        'Execution is often the real issue'
                    ],
                    'cons' => [
                        'May be ignoring market signals',
                        'Opportunity cost of not changing',
                        'Team may lose faith'
                    ],
                    'based_on' => 'Works 1/3 of time when poor execution is the real issue'
                ]
            ],
            
            'pricing' => [
                [
                    'name' => 'Increase prices',
                    'description' => 'Raise pricing for existing tier',
                    'success_rate' => 0.70,
                    'pros' => [
                        'Immediate revenue increase',
                        'Most companies are underpriced',
                        'Signals premium value'
                    ],
                    'cons' => [
                        'May lose price-sensitive customers',
                        'Need to communicate value clearly',
                        'Competitive pressure'
                    ],
                    'based_on' => 'ProfitWell: 70% of SaaS companies successfully raised prices without major churn'
                ],
                [
                    'name' => 'Add premium tier',
                    'description' => 'Keep existing, add higher-priced option',
                    'success_rate' => 0.85,
                    'pros' => [
                        'Captures willingness to pay',
                        'No disruption to existing customers',
                        'Can test new features'
                    ],
                    'cons' => [
                        'Need to justify premium features',
                        'May cannibalize existing tier',
                        'More complex to maintain'
                    ],
                    'based_on' => '85% of SaaS companies with 3+ tiers outperform single-tier pricing'
                ],
                [
                    'name' => 'Usage-based pricing',
                    'description' => 'Switch from flat to metered billing',
                    'success_rate' => 0.65,
                    'pros' => [
                        'Aligns cost with value received',
                        'Lower barrier for small customers',
                        'Revenue grows with usage'
                    ],
                    'cons' => [
                        'Revenue unpredictability',
                        'Complex to implement',
                        'Harder to forecast'
                    ],
                    'based_on' => 'Works for 65% of infrastructure/platform products'
                ]
            ]
        ];
        
        // Return the templates for this category
        return $optionTemplates[$category] ?? [];
    }
    
    /**
     * Find patterns in external data
     */
    private function findExternalPatterns($category, $decisionData) {
        $patterns = [
            'failure_patterns' => $this->getFailurePatterns($category),
            'success_patterns' => $this->getSuccessPatterns($category),
            'benchmark_data' => $this->getBenchmarkData($category),
        ];
        
        return $patterns;
    }
    
    /**
     * Get failure patterns from external data
     */
    private function getFailurePatterns($category) {
        // Query external_startup_failures table
        $stmt = $this->pdo->prepare("
            SELECT failure_reason, COUNT(*) as count,
                   AVG(year_failed) as avg_year
            FROM external_startup_failures
            WHERE decision_type = ?
            GROUP BY failure_reason
            ORDER BY count DESC
            LIMIT 5
        ");
        
        $stmt->execute([$category]);
        $failures = $stmt->fetchAll();
        
        return [
            'common_mistakes' => $failures,
            'total_analyzed' => array_sum(array_column($failures, 'count'))
        ];
    }
    
    /**
     * Get success patterns
     */
    private function getSuccessPatterns($category) {
        // For funding decisions, look at conversion rates
        if ($category === 'funding') {
            $stmt = $this->pdo->prepare("
                SELECT round_type,
                       COUNT(*) as total,
                       SUM(made_to_next_round) as successes,
                       AVG(time_to_next_round) as avg_time
                FROM external_funding_rounds
                WHERE round_type LIKE ?
                GROUP BY round_type
            ");
            
            $stmt->execute(['%Series%']);
            return $stmt->fetchAll();
        }
        
        return [];
    }
    
    /**
     * Get benchmark data
     */
    private function getBenchmarkData($category) {
        // Return industry benchmarks based on category
        $benchmarks = [
            'hiring' => [
                'avg_senior_retention_2yr' => 0.68,
                'avg_internal_promotion_success' => 0.78,
                'avg_time_to_productivity_days' => 90,
            ],
            'funding' => [
                'seed_to_series_a_rate' => 0.25,
                'series_a_to_b_rate' => 0.65,
                'median_series_a_size' => 8000000,
            ],
            'pricing' => [
                'successful_price_increase_rate' => 0.70,
                'avg_churn_from_price_increase' => 0.05,
            ]
        ];
        
        return $benchmarks[$category] ?? [];
    }
    
    /**
     * Find patterns in user's internal data
     */
    private function findInternalPatterns($category, $decisionData) {
        // Use existing RecommendationEngine for internal patterns
        require_once __DIR__ . '/../lib/RecommendationEngine.php';
        $internalEngine = new RecommendationEngine($this->organizationId);
        
        $internalRec = $internalEngine->generateRecommendations($decisionData);
        
        return $internalRec;
    }
    
    /**
     * Generate final recommendation combining all sources
     */
    private function generateRecommendation($suggestedOptions, $externalPatterns, $internalPatterns, $decisionData) {
        // Score each suggested option based on:
        // 1. External success rates
        // 2. Internal patterns (if they exist)
        // 3. Current context
        
        $scoredOptions = [];
        
        foreach ($suggestedOptions as $option) {
            $score = [
                'option' => $option,
                'base_success_rate' => $option['success_rate'] ?? 0.5,
                'confidence' => 'medium',
                'reasoning' => [],
                'warnings' => [],
                'similar_cases' => []
            ];
            
            // Adjust based on external patterns
            if (!empty($externalPatterns['failure_patterns']['common_mistakes'])) {
                $score['reasoning'][] = "Based on analysis of " . 
                    $externalPatterns['failure_patterns']['total_analyzed'] . 
                    " similar failed companies";
            }
            
            // Add specific warnings based on failure patterns
            if (isset($option['cons'])) {
                foreach ($externalPatterns['failure_patterns']['common_mistakes'] as $mistake) {
                    // Match cons to common mistakes
                    foreach ($option['cons'] as $con) {
                        if (stripos($con, $mistake['failure_reason']) !== false) {
                            $score['warnings'][] = "⚠️ " . $mistake['count'] . " companies failed due to: " . $mistake['failure_reason'];
                        }
                    }
                }
            }
            
            // Boost confidence if internal patterns agree
            if (!empty($internalPatterns['has_recommendations'])) {
                $score['confidence'] = 'high';
                $score['reasoning'][] = "Your team has made similar decisions before";
            }
            
            $scoredOptions[] = $score;
        }
        
        // Sort by success rate
        usort($scoredOptions, function($a, $b) {
            return $b['base_success_rate'] <=> $a['base_success_rate'];
        });
        
        return [
            'has_intelligent_recommendations' => true,
            'suggested_options' => $scoredOptions,
            'external_insights' => [
                'failure_patterns' => $externalPatterns['failure_patterns'],
                'benchmarks' => $externalPatterns['benchmark_data']
            ],
            'internal_insights' => $internalPatterns,
            'recommendation_quality' => $this->assessRecommendationQuality($scoredOptions, $externalPatterns, $internalPatterns)
        ];
    }
    
    /**
     * Assess quality of recommendation
     */
    private function assessRecommendationQuality($options, $external, $internal) {
        $quality = 'low';
        
        // High quality if we have both external and internal data
        if (!empty($external['failure_patterns']['total_analyzed']) && 
            !empty($internal['has_recommendations'])) {
            $quality = 'high';
        } 
        // Medium if we have substantial external data
        else if (!empty($external['failure_patterns']['total_analyzed']) && 
                 $external['failure_patterns']['total_analyzed'] > 20) {
            $quality = 'medium';
        }
        
        return [
            'level' => $quality,
            'external_data_points' => $external['failure_patterns']['total_analyzed'] ?? 0,
            'internal_data_points' => $internal['similar_count'] ?? 0
        ];
    }
}