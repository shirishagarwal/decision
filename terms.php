<?php
/**
 * File Path: terms.php
 * Description: Professional Terms of Service for DecisionVault.
 */
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service | DecisionVault</title>
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
                <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tighter mb-4">Terms of Service</h1>
                <p class="text-slate-500 font-medium uppercase tracking-widest text-xs">Last Updated: February 6, 2026</p>
            </header>

            <div class="legal-content">
                <p>Welcome to DecisionVault. By accessing or using our Strategic Intelligence OS, you agree to be bound by these Terms of Service. Please read them carefully.</p>

                <h2>1. Acceptance of Terms</h2>
                <p>By creating an account and initializing your Strategic Vault, you agree to these Terms and all applicable laws and regulations. If you do not agree, you are prohibited from using the service.</p>

                <h2>2. The Strategic Intelligence OS</h2>
                <p>DecisionVault provides a platform for recording strategic logic, performing AI-driven stress tests, and building institutional memory. You understand that the "Decision Maturity Index" and "Strategic Intelligence" metrics are provided for informational and benchmarking purposes only.</p>

                <h2>3. User Responsibilities & Conduct</h2>
                <p>You are responsible for maintaining the confidentiality of your account and for all activities that occur under your vault. You agree not to use the service for any illegal or unauthorized purpose.</p>

                <h2>4. Data Sovereignty and Ownership</h2>
                <ul>
                    <li><strong>Private Vault Data:</strong> You retain full ownership of all strategic decisions, rationale, and proprietary data entered into your private organization silo.</li>
                    <li><strong>Global Intelligence:</strong> Patterns sourced from the public web (the "Scout" engine) and proprietary seed data belong to DecisionVault.</li>
                    <li><strong>Anonymized Benchmarks:</strong> You grant DecisionVault a non-exclusive license to use anonymized metadata (e.g., sector, decision type, and high-level outcomes) to improve global benchmarking and AI accuracy. Individual company identities are never shared.</li>
                </ul>

                <h2>5. Third-Party Connectors</h2>
                <p>By connecting services like Stripe or HubSpot, you authorize DecisionVault to access relevant business metrics for the sole purpose of resolving Intelligence Gaps and enhancing strategic simulations. We do not store financial credentials; all tokens are encrypted as per industry standards.</p>

                <h2>6. Limitation of Liability</h2>
                <p>DecisionVault is a strategic tool, not a crystal ball. We are not responsible for business losses resulting from decisions made using the platform. The "Aggressive Stress Test" is a simulation and does not guarantee future outcomes.</p>

                <h2>7. Termination</h2>
                <p>We reserve the right to terminate access to the service for violations of these terms. Upon termination, you may request an export of your private strategic logs within 30 days.</p>

                <h2>8. Modifications</h2>
                <p>DecisionVault may revise these terms at any time. By continuing to use the service, you agree to be bound by the current version of these terms.</p>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
