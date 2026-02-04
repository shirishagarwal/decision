<?php
/**
 * DecisionVault Simulation Engine
 * Runs "Pre-Mortem" stress tests using Gemini 2.5 Flash.
 */
class SimulationEngine {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function runStressTest($decisionId) {
        // Fetch decision context
        $stmt = $this->pdo->prepare("
            SELECT d.*, o.name as org_name 
            FROM decisions d 
            JOIN workspaces w ON d.workspace_id = w.id 
            JOIN organizations o ON w.organization_id = o.id 
            WHERE d.id = ?
        ");
        $stmt->execute([$decisionId]);
        $decision = $stmt->fetch();

        if (!$decision) throw new Exception("Decision not found.");

        // The Aggressive Pre-Mortem Prompt
        $prompt = "You are a 'Chief Disaster Officer'. Perform a brutal pre-mortem on this decision: '{$decision['title']}'. 
        Assume it is 1 year from now and this decision has FAILED CATEGORICALLY. 
        Describe the timeline of collapse:
        1. Day 30: The first subtle red flags everyone ignores.
        2. Day 90: The critical point where you should have pivoted but didn't.
        3. Day 365: The final autopsy of why the organization is now in crisis.
        
        Provide your response ONLY in raw JSON format with keys: 'day30', 'day90', 'day365', and 'mitigation'.";

        // Gemini API Call
        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . GEMINI_API_KEY;
        $payload = [
            "contents" => [["parts" => [["text" => $prompt]]]]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $result = json_decode($response, true);
        curl_close($ch);

        $rawText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        
        // Sanitize JSON for storage
        $cleanJson = preg_replace('/^```json\s*|\s*```$/', '', trim($rawText));
        $data = json_decode($cleanJson, true);

        // Save simulation to database
        $stmt = $this->pdo->prepare("
            INSERT INTO decision_simulations (decision_id, scenario_name, failure_timeline, mitigation_plan)
            VALUES (?, 'Aggressive Pre-Mortem', ?, ?)
        ");
        $stmt->execute([
            $decisionId,
            $cleanJson,
            $data['mitigation'] ?? 'No mitigation provided.'
        ]);

        return $data;
    }
}
