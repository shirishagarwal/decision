<?php
/**
 * File Path: lib/SimulationEngine.php
 * Description: AI logic for the Aggressive Stress Test (Pre-Mortem).
 */

class SimulationEngine {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    /**
     * Runs a pre-mortem simulation using Gemini.
     */
    public function runStressTest($decisionId) {
        $stmt = $this->pdo->prepare("
            SELECT d.*, GROUP_CONCAT(do.name SEPARATOR ', ') as options_list
            FROM decisions d
            LEFT JOIN decision_options do ON d.id = do.decision_id
            WHERE d.id = ?
            GROUP BY d.id
        ");
        $stmt->execute([$decisionId]);
        $decision = $stmt->fetch();

        if (!$decision) return ['error' => 'Decision context not found.'];

        $prompt = "Act as a 'Chief Disaster Officer' performing a brutal Pre-Mortem. 
        The following decision was made: '{$decision['title']}'.
        Context: '{$decision['problem_statement']}'.
        Options considered: '{$decision['options_list']}'.

        ASSUME IT IS 12 MONTHS LATER AND THIS DECISION HAS FAILED COMPLETELY.
        Provide a JSON response with these keys:
        1. 'day30': Subtle early warning signs that were ignored.
        2. 'day90': The specific point where the strategy drifted into crisis.
        3. 'day365': The final autopsy of the collapse.
        4. 'mitigation': One specific 'fail-safe' the user should implement NOW to prevent this.

        Return ONLY raw JSON.";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . GEMINI_API_KEY;
        
        $payload = [
            "contents" => [["parts" => [["text" => $prompt]]]],
            "generationConfig" => ["responseMimeType" => "application/json"]
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
        return json_decode($rawText, true);
    }
}
