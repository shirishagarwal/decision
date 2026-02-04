<?php
/**
 * DecisionVault - The Review Loop
 * Collects actual outcomes to calculate Decision IQ.
 */

require_once __DIR__ . '/lib/auth.php';
requireOrgAccess();

$decisionId = $_GET['id'] ?? null;
$pdo = getDbConnection();

// Fetch Decision for Review
$stmt = $pdo->prepare("SELECT * FROM decisions WHERE id = ?");
$stmt->execute([$decisionId]);
$decision = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['review_rating'];
    $outcome = $_POST['actual_outcome'];

    // Update Decision with Outcome Data
    $stmt = $pdo->prepare("
        UPDATE decisions 
        SET review_rating = ?, actual_outcome = ?, review_completed_at = NOW(), status = 'Implemented'
        WHERE id = ?
    ");
    $stmt->execute([$rating, $outcome, $decisionId]);

    header("Location: /decision.php?id=$decisionId&success=reviewed");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Review: <?php echo $decision['title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-indigo-50 flex items-center justify-center min-h-screen p-4">
    <form method="POST" class="bg-white max-w-xl w-full p-10 rounded-3xl shadow-2xl border-4 border-white">
        <h2 class="text-3xl font-black mb-2 text-center">Closing the Loop 🧠</h2>
        <p class="text-center text-gray-500 mb-8">How did "<?php echo $decision['title']; ?>" turn out?</p>

        <div class="mb-8">
            <label class="block font-bold mb-4">The Outcome Rating:</label>
            <div class="grid grid-cols-5 gap-2">
                <?php
                $ratings = ['much_worse', 'worse', 'as_expected', 'better', 'much_better'];
                foreach($ratings as $r): ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="review_rating" value="<?php echo $r; ?>" class="peer hidden" required>
                        <div class="p-3 border-2 rounded-xl text-center peer-checked:bg-indigo-600 peer-checked:text-white transition">
                            <?php echo str_replace('_', ' ', $r); ?>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mb-8">
            <label class="block font-bold mb-2">What actually happened?</label>
            <textarea name="actual_outcome" rows="5" required placeholder="Be honest. Did the senior hire ramp up? Did the pricing change cause churn?"
                      class="w-full border-2 rounded-2xl p-4 outline-indigo-600"></textarea>
        </div>

        <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black text-lg shadow-xl hover:bg-indigo-700 transition">
            Save Review & Update IQ
        </button>
    </form>
</body>
</html>
