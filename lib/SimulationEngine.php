<?php
/**
 * File Path: lib/SimulationEngine.php
 * Description: Robust AI Simulation logic with advanced JSON cleaning for poorly formatted AI output.
 */

class SimulationEngine {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    /**
     * Executes the aggressive pre-mortem using Gemini.
     */
    public function runStressTest($decisionId) {
        $stmt = $this->pdo->prepare("SELECT title, problem_statement, category FROM decisions WHERE id = ?");
        $stmt->execute([$decisionId]);
        $decision = $stmt->fetch();

        if (!$decision) return ['error' => 'Decision not found'];

        // THE AGGRESSIVE PROMPT
        $prompt = "You are a 'Chief Disaster Officer'. This decision has FAILED catastrophically: '{$decision['title']}'. 
        Context: '{$decision['problem_statement']}'. 
        Category: '{$decision['category']}'.
        
        Assume it is 1 year in the future. Describe the autopsy of this failure.
        YOU MUST RETURN ONLY A RAW JSON OBJECT with these keys: 
        'day30' (early warning signs), 
        'day90' (critical drift point), 
        'day365' (final collapse autopsy),
        'mitigation' (what they should have done).
        
        DO NOT include markdown formatting like ```json. Just raw text.";

        $url = "[https://generativelanguage.googleapis.com/v1beta/models/](https://generativelanguage.googleapis.com/v1beta/models/)" . GEMINI_MODEL . ":generateContent?key=" . GEMINI_API_KEY;
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
        
        // ADVANCED CLEANING: Gemini often ignores "RAW JSON" and wraps it in markdown
        // This regex aggressively strips leading/trailing markdown code blocks and whitespace
        $cleanJson = preg_replace('/^.*?({.*}).*?$/s', '$1', trim($rawText));
        
        $data = json_decode($cleanJson, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Fallback for extremely broken output
            return [
                'day30' => 'AI Output Format Error',
                'day90' => 'Could not parse JSON response from LLM.',
                'day365' => 'Raw Response: ' . substr($rawText, 0, 100) . '...',
                'mitigation' => 'Check API connection and model availability.'
            ];
        }

        return $data;
    }
}
