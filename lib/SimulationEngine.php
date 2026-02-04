<?php
/**
 * DecisionVault Simulation Engine
 * Runs "Brutal Pre-Mortems" to stress-test logic.
 */
class SimulationEngine {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function runStressTest($decisionId) {
        $stmt = $this->pdo->prepare("SELECT title, problem_statement FROM decisions WHERE id = ?");
        $stmt->execute([$decisionId]);
        $decision = $stmt->fetch();

        if (!$decision) return ['error' => 'Decision not found'];

        $prompt = "You are a 'Chief Disaster Officer' performing a brutal pre-mortem. 
        This decision has FAILED catastrophically: '{$decision['title']}'. 
        Problem context: '{$decision['problem_statement']}'. 
        Provide a RAW JSON response with keys 'day30', 'day90', 'day365' describing the collapse timeline.";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . GEMINI_API_KEY;
        $payload = ["contents" => [["parts" => [["text" => $prompt]]]]];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $result = json_decode($response, true);
        curl_close($ch);

        $rawText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        // Clean markdown backticks if Gemini includes them
        $cleanJson = preg_replace('/^```json\s*|\s*```$/', '', trim($rawText));
        
        return json_decode($cleanJson, true);
    }
}
