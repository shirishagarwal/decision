<?php
/**
 * File Path: privacy.php
 * Description: Enterprise-grade Privacy Policy for DecisionVault.
 */
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; color: #334155; }
        .legal-content h2 { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-top: 2.5rem; margin-bottom: 1rem; letter-spacing: -0.025em; }
        .legal-content p { margin-bottom: 1.25rem; line-height: 1.75; }
        .legal-content ul { list-style-type: disc; margin-left: 1.5rem; margin-bottom: 1.25rem; }
        .legal-content li { margin-bottom: 0.5rem; }
    </style>
</head>
<body class="bg-gray-50">

    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="max-w-4xl mx-auto py-20 px-6">
        <div class="bg-white p-12 md:p-20 rounded-[3rem] shadow-sm border border-gray-100">
            <header class="mb-12 border-b border-gray-100 pb-12">
                <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tighter mb-4">Privacy Policy</h1>
                <p class="text-slate-500 font-medium uppercase tracking-widest text-xs">Last Updated: February 6, 2026</p>
            </header>

            <div class="legal-content">
                <p>At DecisionVault, your strategic privacy is our highest priority. This policy outlines how we protect your data and the steps we take to ensure your Institutional Memory remains secure.</p>

                <h2>1. Information Collection</h2>
                <p>We collect information necessary to provide and improve our strategic intelligence services, including:</p>
                <ul>
                    <li><strong>Account Information:</strong> Name, email, and profile data provided via Google OAuth.</li>
                    <li><strong>Strategic Data:</strong> Decision titles, problem statements, and rationale logs.</li>
                    <li><strong>Connector Metadata:</strong> Encrypted tokens and business metrics fetched from third-party integrations (Stripe, HubSpot, etc.).</li>
                </ul>

                <h2>2. How We Use Data</h2>
                <p>Your data is used specifically for:</p>
                <ul>
                    <li>Generating AI-driven Stress Tests and Pre-Mortem simulations.</li>
                    <li>Calculating your Organization's Decision Maturity Index.</li>
                    <li>Providing relevant "Pattern Matches" from the Global Intelligence Library.</li>
                    <li>Improving the overall accuracy of our AI Strategic models through anonymized learning.</li>
                </ul>

                <h2>3. Data Siloing and Isolation</h2>
                <p>We utilize a multi-tenant architecture. Your private strategic logs are isolated at the database level using <code>organization_id</code> filters. One organization's private logic is never accessible to another.</p>

                <h2>4. Data Encryption</h2>
                <p>All sensitive data, including OAuth tokens and strategic rationale, are encrypted at rest using AES-256 standards. All transmissions are secured via TLS/SSL.</p>

                <h2>5. Third-Party Sharing</h2>
                <p>We do not sell your strategic data. We only share information with third parties (like the Gemini API) to perform necessary AI processing. In these cases, only the relevant textual context is shared, never your organizational identity.</p>

                <h2>6. Your Rights</h2>
                <p>You have the right to:</p>
                <ul>
                    <li>Export your strategic logs in JSON or CSV format.</li>
                    <li>Request the permanent deletion of your organization's vault.</li>
                    <li>Manage or disconnect third-party data integrations at any time.</li>
                </ul>

                <h2>7. Policy Updates</h2>
                <p>We may update this policy to reflect changes in governance or technology. We will notify active users of any significant changes via the Executive Dashboard.</p>

                <h2>8. Contact</h2>
                <p>For inquiries regarding strategic privacy, contact <code>security@decisionvault.ai</code>.</p>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
