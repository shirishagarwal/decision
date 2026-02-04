<?php
require_once __DIR__ . '/config.php';
$pageTitle = "Terms of Service";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .prose h2 { font-size: 1.5rem; font-weight: 700; margin-top: 2rem; margin-bottom: 1rem; }
        .prose h3 { font-size: 1.25rem; font-weight: 600; margin-top: 1.5rem; margin-bottom: 0.75rem; }
        .prose p { margin-bottom: 1rem; line-height: 1.75; color: #4b5563; }
        .prose ul { margin: 1rem 0; padding-left: 2rem; }
        .prose li { margin-bottom: 0.5rem; color: #4b5563; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-purple-600 to-pink-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold">D</span>
                </div>
                <span class="text-xl font-bold text-gray-900">DecisionVault</span>
            </a>
            <a href="/" class="text-purple-600 hover:text-purple-700 font-medium">← Back to Home</a>
        </div>
    </nav>

    <!-- Content -->
    <div class="max-w-4xl mx-auto px-4 py-12">
        <div class="bg-white rounded-xl shadow-sm p-8 md:p-12">
            <h1 class="text-4xl font-black text-gray-900 mb-2"><?php echo $pageTitle; ?></h1>
            <p class="text-gray-600 mb-8">Last updated: <?php echo date('F d, Y'); ?></p>
            
            <div class="prose max-w-none">
                <p>
                    Welcome to DecisionVault. By accessing or using our service, you agree to be bound by these Terms of Service ("Terms"). 
                    Please read them carefully.
                </p>

                <h2>1. Acceptance of Terms</h2>
                <p>
                    By creating an account or using DecisionVault, you agree to these Terms and our Privacy Policy. 
                    If you do not agree, please do not use our service.
                </p>

                <h2>2. Description of Service</h2>
                <p>
                    DecisionVault is a decision intelligence platform that helps teams make better decisions using AI-powered recommendations 
                    based on historical data and machine learning.
                </p>
                <p>Our service includes:</p>
                <ul>
                    <li>Decision tracking and documentation</li>
                    <li>AI-powered decision recommendations</li>
                    <li>Team collaboration features</li>
                    <li>Analytics and insights</li>
                    <li>Review and learning loop features</li>
                </ul>

                <h2>3. User Accounts</h2>
                <h3>3.1 Account Creation</h3>
                <p>
                    You must provide accurate and complete information when creating an account. You are responsible for 
                    maintaining the confidentiality of your account credentials.
                </p>
                
                <h3>3.2 Account Security</h3>
                <p>
                    You are responsible for all activities that occur under your account. Notify us immediately of any 
                    unauthorized use of your account.
                </p>

                <h3>3.3 Account Termination</h3>
                <p>
                    We reserve the right to suspend or terminate accounts that violate these Terms or engage in fraudulent, 
                    abusive, or illegal activity.
                </p>

                <h2>4. User Content and Data</h2>
                <h3>4.1 Your Data</h3>
                <p>
                    You retain all ownership rights to the decisions, data, and content you create in DecisionVault. 
                    We do not claim ownership of your data.
                </p>

                <h3>4.2 License to Use Your Data</h3>
                <p>
                    By using DecisionVault, you grant us a limited license to store, process, and analyze your data solely 
                    for the purpose of providing and improving our service.
                </p>

                <h3>4.3 Anonymized Data</h3>
                <p>
                    We may use anonymized and aggregated data from decisions across all users to improve our AI recommendations. 
                    This data cannot be traced back to you or your organization.
                </p>

                <h3>4.4 Prohibited Content</h3>
                <p>You may not use DecisionVault to store or share:</p>
                <ul>
                    <li>Illegal, fraudulent, or harmful content</li>
                    <li>Malware, viruses, or malicious code</li>
                    <li>Content that violates intellectual property rights</li>
                    <li>Personal data of others without consent</li>
                    <li>Spam or unsolicited advertisements</li>
                </ul>

                <h2>5. Acceptable Use</h2>
                <p>You agree not to:</p>
                <ul>
                    <li>Reverse engineer, decompile, or attempt to extract our source code</li>
                    <li>Use automated systems to access our service without permission</li>
                    <li>Interfere with or disrupt our servers or networks</li>
                    <li>Impersonate others or misrepresent your affiliation</li>
                    <li>Violate any applicable laws or regulations</li>
                </ul>

                <h2>6. Payments and Subscriptions</h2>
                <h3>6.1 Pricing</h3>
                <p>
                    Current pricing is available on our Pricing page. We reserve the right to change prices with 30 days' notice 
                    to existing customers.
                </p>

                <h3>6.2 Billing</h3>
                <p>
                    Subscriptions are billed in advance on a monthly or annual basis. All payments are processed securely through 
                    our payment processor (Stripe).
                </p>

                <h3>6.3 Refunds</h3>
                <p>
                    We offer a 14-day money-back guarantee for new subscriptions. After 14 days, payments are non-refundable except 
                    where required by law.
                </p>

                <h3>6.4 Cancellation</h3>
                <p>
                    You may cancel your subscription at any time. Cancellations take effect at the end of the current billing period. 
                    You will retain access until that date.
                </p>

                <h2>7. Intellectual Property</h2>
                <h3>7.1 Our IP</h3>
                <p>
                    DecisionVault, our logo, and all related marks are our property. Our software, algorithms, and AI models are 
                    protected by copyright, patent, and trade secret laws.
                </p>

                <h3>7.2 Limited License</h3>
                <p>
                    We grant you a limited, non-exclusive, non-transferable license to use DecisionVault for your internal business 
                    purposes during your subscription period.
                </p>

                <h2>8. AI Recommendations Disclaimer</h2>
                <p>
                    <strong>Important:</strong> AI recommendations provided by DecisionVault are for informational purposes only. 
                    They are based on historical data and statistical analysis, not professional advice.
                </p>
                <ul>
                    <li>Recommendations are predictions, not guarantees of outcomes</li>
                    <li>We are not liable for decisions made based on our recommendations</li>
                    <li>Always exercise independent judgment and consult appropriate professionals</li>
                    <li>Past performance does not guarantee future results</li>
                </ul>

                <h2>9. Limitation of Liability</h2>
                <p>
                    TO THE MAXIMUM EXTENT PERMITTED BY LAW, DECISIONVAULT SHALL NOT BE LIABLE FOR ANY INDIRECT, INCIDENTAL, 
                    SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, OR ANY LOSS OF PROFITS OR REVENUES, WHETHER INCURRED DIRECTLY 
                    OR INDIRECTLY, OR ANY LOSS OF DATA, USE, GOODWILL, OR OTHER INTANGIBLE LOSSES.
                </p>
                <p>
                    Our total liability to you for all claims arising from or related to these Terms or the service shall not exceed 
                    the amount you paid us in the 12 months preceding the claim.
                </p>

                <h2>10. Warranties Disclaimer</h2>
                <p>
                    THE SERVICE IS PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED, 
                    INCLUDING BUT NOT LIMITED TO IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND 
                    NON-INFRINGEMENT.
                </p>

                <h2>11. Indemnification</h2>
                <p>
                    You agree to indemnify and hold harmless DecisionVault from any claims, damages, losses, and expenses (including 
                    legal fees) arising from your use of the service or violation of these Terms.
                </p>

                <h2>12. Privacy and Data Protection</h2>
                <p>
                    Your use of DecisionVault is subject to our Privacy Policy, which explains how we collect, use, and protect 
                    your data. Please review our <a href="/privacy" class="text-purple-600 hover:text-purple-700 underline">Privacy Policy</a>.
                </p>

                <h2>13. Changes to Terms</h2>
                <p>
                    We may update these Terms from time to time. We will notify you of material changes via email or through the service. 
                    Continued use after changes constitutes acceptance of the new Terms.
                </p>

                <h2>14. Termination</h2>
                <p>
                    Either party may terminate this agreement at any time. Upon termination:
                </p>
                <ul>
                    <li>Your access to the service will end</li>
                    <li>You may download your data within 30 days</li>
                    <li>After 30 days, we may delete your data</li>
                    <li>Provisions that should survive termination will remain in effect</li>
                </ul>

                <h2>15. Governing Law</h2>
                <p>
                    These Terms are governed by the laws of [Your State/Country], without regard to conflict of law provisions. 
                    Any disputes shall be resolved in the courts of [Your Jurisdiction].
                </p>

                <h2>16. Dispute Resolution</h2>
                <p>
                    For any disputes, we encourage you to first contact us at legal@yourdomain.com to seek resolution informally. 
                    If informal resolution fails, disputes may be resolved through binding arbitration or in court as specified in 
                    Section 15.
                </p>

                <h2>17. Entire Agreement</h2>
                <p>
                    These Terms, together with our Privacy Policy, constitute the entire agreement between you and DecisionVault 
                    regarding the service.
                </p>

                <h2>18. Contact Us</h2>
                <p>
                    If you have questions about these Terms, please contact us:
                </p>
                <ul>
                    <li>Email: legal@yourdomain.com</li>
                    <li>Website: yourdomain.com/help</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-600">
            <p>
                <a href="/privacy" class="text-purple-600 hover:text-purple-700">Privacy Policy</a>
                <span class="mx-2">•</span>
                <a href="/help" class="text-purple-600 hover:text-purple-700">Help Center</a>
                <span class="mx-2">•</span>
                <a href="mailto:support@yourdomain.com" class="text-purple-600 hover:text-purple-700">Contact Support</a>
            </p>
        </div>
    </div>
</body>
</html>