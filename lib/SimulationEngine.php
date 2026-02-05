<?php
/**
 * File Path: lib/SimulationEngine.php
 * Description: Advanced AI logic with aggressive JSON extraction to fix "undefined" errors.
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

        $prompt = "Act as a 'Chief Disaster Officer'. This decision has FAILED catastrophically: '{$decision['title']}'. 
        Context: '{$decision['problem_statement']}'.
        
        Provide a JSON response with THESE EXACT KEYS: 
        'day30' (early warning signs), 
        'day90' (critical drift point), 
        'day365' (final collapse autopsy),
        'mitigation' (how to prevent it).
        
        Return ONLY the JSON object. Do not explain.";

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
        
        // AGGRESSIVE CLEANING: Strip backticks or preamble if the AI ignored the JSON config
        if (preg_match('/\{.*\}/s', $rawText, $matches)) {
            $data = json_decode($matches[0], true);
        } else {
            $data = json_decode($rawText, true);
        }

        if (!$data) return ['error' => 'AI returned malformed data.'];

        // Standardize output to ensure the UI finds the keys it needs
        return [
            'day30' => $data['day30'] ?? ($data['Day 30'] ?? 'No early flags identified.'),
            'day90' => $data['day90'] ?? ($data['Day 90'] ?? 'No drift patterns identified.'),
            'day365' => $data['day365'] ?? ($data['Day 365'] ?? 'Collapse autopsy unavailable.'),
            'mitigation' => $data['mitigation'] ?? ($data['mitigation_plan'] ?? 'Follow standard strategic protocols.')
        ];
    }
}
