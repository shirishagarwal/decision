<?php
/**
 * REVIEW REMINDER CRON JOB
 * 
 * Setup: Run this daily via cron
 * Command: php /path/to/your/app/cron/send-review-reminders.php
 * 
 * Or in cPanel: Add cron job:
 * 0 9 * * * /usr/bin/php /home/username/public_html/cron/send-review-reminders.php
 * (Runs daily at 9 AM)
 */

require_once __DIR__ . '/../config.php';

$pdo = getDbConnection();

// Find decisions that need review TODAY
$stmt = $pdo->query("
    SELECT 
        d.id,
        d.title,
        d.review_date,
        d.expected_outcome,
        u.id as user_id,
        u.name as user_name,
        u.email as user_email
    FROM decisions d
    INNER JOIN users u ON d.created_by = u.id
    WHERE d.review_date = CURDATE()
    AND d.review_completed_at IS NULL
    AND d.status != 'Archived'
");

$decisions = $stmt->fetchAll();

$sentCount = 0;
$errorCount = 0;

foreach ($decisions as $decision) {
    try {
        $sent = sendReviewReminder($decision);
        if ($sent) {
            $sentCount++;
            // Log that we sent the reminder
            logReminderSent($pdo, $decision['id']);
        } else {
            $errorCount++;
        }
    } catch (Exception $e) {
        error_log("Failed to send reminder for decision {$decision['id']}: " . $e->getMessage());
        $errorCount++;
    }
}

// Log results
$logMessage = date('Y-m-d H:i:s') . " - Review Reminders: {$sentCount} sent, {$errorCount} errors\n";
file_put_contents(__DIR__ . '/reminder-log.txt', $logMessage, FILE_APPEND);

echo $logMessage;

function sendReviewReminder($decision) {
    $to = $decision['user_email'];
    $name = $decision['user_name'];
    $decisionTitle = $decision['title'];
    $decisionId = $decision['id'];
    $expectedOutcome = $decision['expected_outcome'];
    
    $reviewUrl = APP_URL . '/review-decision.php?id=' . $decisionId;
    
    $subject = "🔔 Time to review: {$decisionTitle}";
    
    $message = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px 10px 0 0; text-align: center; }
        .content { background: white; padding: 30px; border: 1px solid #e5e7eb; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
        .expected { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1 style='margin: 0; font-size: 28px;'>⏰ Review Time!</h1>
        </div>
        <div class='content'>
            <p>Hi {$name},</p>
            
            <p>Remember when you decided:</p>
            <h2 style='color: #667eea; margin: 20px 0;'>{$decisionTitle}</h2>
            
            " . ($expectedOutcome ? "
            <div class='expected'>
                <strong style='color: #3b82f6;'>You expected:</strong><br>
                {$expectedOutcome}
            </div>
            " : "") . "
            
            <p><strong>Did it work out as planned?</strong></p>
            
            <p>Take 2 minutes to review this decision. You'll:</p>
            <ul>
                <li>📊 Track your decision accuracy</li>
                <li>🧠 Learn from outcomes</li>
                <li>📈 Get smarter over time</li>
            </ul>
            
            <div style='text-align: center;'>
                <a href='{$reviewUrl}' class='button'>Review This Decision →</a>
            </div>
            
            <p style='margin-top: 30px; font-size: 14px; color: #6b7280;'>
                <em>This is how you build Decision Intelligence. Each review makes you better at deciding!</em>
            </p>
        </div>
        <div class='footer'>
            <p>This email was sent because you set a review date for this decision in " . APP_NAME . ".</p>
            <p>Don't want reminders? Update your notification settings in your account.</p>
        </div>
    </div>
</body>
</html>";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . APP_NAME . " <noreply@" . parse_url(APP_URL, PHP_URL_HOST) . ">\r\n";
    
    return mail($to, $subject, $message, $headers);
}

function logReminderSent($pdo, $decisionId) {
    // Optional: Create a table to track sent reminders
    try {
        $pdo->prepare("
            INSERT INTO reminder_logs (decision_id, sent_at, type)
            VALUES (?, NOW(), 'review')
        ")->execute([$decisionId]);
    } catch (Exception $e) {
        // Table might not exist, that's okay
    }
}
