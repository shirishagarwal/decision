<?php
require_once __DIR__ . '/config.php';
$pageTitle = "Privacy Policy";
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

    <div class="max-w-4xl mx-auto px-4 py-12">
        <div class="bg-white rounded-xl shadow-sm p-8 md:p-12">
            <h1 class="text-4xl font-black text-gray-900 mb-2"><?php echo $pageTitle; ?></h1>
            <p class="text-gray-600 mb-8">Last updated: <?php echo date('F d, Y'); ?></p>
            
            <div class="prose max-w-none">
                <p>
                    At DecisionVault, we take your privacy seriously. This Privacy Policy explains how we collect, use, disclose, 
                    and safeguard your information when you use our service.
                </p>

                <h2>1. Information We Collect</h2>
                
                <h3>1.1 Information You Provide</h3>
                <p>We collect information you voluntarily provide when using DecisionVault:</p>
                <ul>
                    <li><strong>Account Information:</strong> Name, email address, password, company name</li>
                    <li><strong>Profile Information:</strong> Job title, department, profile photo (optional)</li>
                    <li><strong>Decision Data:</strong> Decisions you create, options, context, team members, votes, outcomes</li>
                    <li><strong>Payment Information:</strong> Billing details (processed securely by Stripe; we never store credit card numbers)</li>
                    <li><strong>Communications:</strong> Support requests, feedback, survey responses</li>
                </ul>

                <h3>1.2 Information Collected Automatically</h3>
                <p>When you use DecisionVault, we automatically collect:</p>
                <ul>
                    <li><strong>Usage Data:</strong> Pages visited, features used, time spent, click patterns</li>
                    <li><strong>Device Information:</strong> Browser type, operating system, device type, IP address</li>
                    <li><strong>Cookies:</strong> Session cookies for authentication, analytics cookies (see Section 6)</li>
                    <li><strong>Log Data:</strong> Access times, error logs, performance metrics</li>
                </ul>

                <h3>1.3 Information from Third Parties</h3>
                <ul>
                    <li><strong>Authentication:</strong> If you sign in with Google/Microsoft, we receive your name and email</li>
                    <li><strong>Payment Processor:</strong> Stripe shares payment confirmation and billing information</li>
                </ul>

                <h2>2. How We Use Your Information</h2>
                
                <p>We use your information to:</p>
                
                <h3>2.1 Provide the Service</h3>
                <ul>
                    <li>Create and manage your account</li>
                    <li>Process payments and send receipts</li>
                    <li>Provide AI recommendations based on your decisions</li>
                    <li>Enable team collaboration features</li>
                    <li>Send service-related notifications (decision reminders, reviews, updates)</li>
                </ul>

                <h3>2.2 Improve the Service</h3>
                <ul>
                    <li>Analyze usage patterns to improve features</li>
                    <li>Train and improve our AI recommendation engine using anonymized data</li>
                    <li>Fix bugs and optimize performance</li>
                    <li>Develop new features based on user behavior</li>
                </ul>

                <h3>2.3 Communicate with You</h3>
                <ul>
                    <li>Respond to support requests</li>
                    <li>Send product updates and announcements (you can opt out)</li>
                    <li>Request feedback or conduct surveys</li>
                    <li>Send security alerts and important account notifications</li>
                </ul>

                <h3>2.4 Ensure Security</h3>
                <ul>
                    <li>Detect and prevent fraud and abuse</li>
                    <li>Monitor for suspicious activity</li>
                    <li>Enforce our Terms of Service</li>
                </ul>

                <h2>3. How We Share Your Information</h2>
                
                <p>We do NOT sell your personal information. We share data only in these limited circumstances:</p>

                <h3>3.1 With Your Team Members</h3>
                <p>
                    Decisions you create are shared with team members you invite. They can see the decision details, 
                    options, votes, and outcomes.
                </p>

                <h3>3.2 With Service Providers</h3>
                <p>We share data with trusted third parties who help us operate our service:</p>
                <ul>
                    <li><strong>Hosting:</strong> AWS/DigitalOcean for infrastructure</li>
                    <li><strong>Payments:</strong> Stripe for payment processing</li>
                    <li><strong>Email:</strong> SendGrid/Mailgun for transactional emails</li>
                    <li><strong>Analytics:</strong> Google Analytics (anonymized)</li>
                    <li><strong>Support:</strong> Intercom/Zendesk for customer support</li>
                </ul>
                <p>These providers are contractually obligated to protect your data and use it only for providing services to us.</p>

                <h3>3.3 For Legal Reasons</h3>
                <p>We may disclose information if required by law or to:</p>
                <ul>
                    <li>Comply with legal processes (subpoenas, court orders)</li>
                    <li>Protect our rights or property</li>
                    <li>Prevent fraud or security threats</li>
                    <li>Protect the safety of our users or the public</li>
                </ul>

                <h3>3.4 Business Transfers</h3>
                <p>
                    If DecisionVault is acquired or merged with another company, your data may be transferred to the new owner. 
                    We will notify you before this happens.
                </p>

                <h3>3.5 Anonymized Data</h3>
                <p>
                    We may share anonymized, aggregated data that cannot identify you (e.g., "80% of users review decisions within 3 months"). 
                    This helps improve AI recommendations for all users.
                </p>

                <h2>4. Data Retention</h2>
                
                <p>We retain your data as follows:</p>
                <ul>
                    <li><strong>Account Data:</strong> Until you delete your account</li>
                    <li><strong>Decision Data:</strong> Until you delete decisions or your account</li>
                    <li><strong>Usage Logs:</strong> 90 days for active users, 30 days after account deletion</li>
                    <li><strong>Backup Data:</strong> Up to 30 days in encrypted backups</li>
                    <li><strong>Legal Requirements:</strong> Longer if required by law (e.g., tax records)</li>
                </ul>

                <h2>5. Your Rights and Choices</h2>
                
                <h3>5.1 Access and Export</h3>
                <p>You can access and export your data at any time through Settings → Export Data.</p>

                <h3>5.2 Correction</h3>
                <p>You can update your account information and decision data directly in the app.</p>

                <h3>5.3 Deletion</h3>
                <p>
                    You can delete individual decisions or your entire account through Settings → Delete Account. 
                    Deletion is permanent and cannot be undone.
                </p>

                <h3>5.4 Opt-Out of Marketing</h3>
                <p>
                    Unsubscribe from marketing emails using the link in any email. You'll still receive essential 
                    service notifications (payment confirmations, security alerts).
                </p>

                <h3>5.5 Cookie Preferences</h3>
                <p>
                    You can disable cookies in your browser settings. Note that some features may not work without cookies.
                </p>

                <h3>5.6 GDPR Rights (EU Users)</h3>
                <p>If you're in the EU, you have additional rights:</p>
                <ul>
                    <li>Right to data portability</li>
                    <li>Right to restrict processing</li>
                    <li>Right to object to processing</li>
                    <li>Right to lodge a complaint with your data protection authority</li>
                </ul>

                <h3>5.7 CCPA Rights (California Users)</h3>
                <p>California residents can request:</p>
                <ul>
                    <li>What personal information we collect</li>
                    <li>How we use and share it</li>
                    <li>Deletion of your personal information</li>
                    <li>Opt-out of sale (we don't sell data, but you can still request this)</li>
                </ul>

                <p>To exercise these rights, email us at privacy@yourdomain.com.</p>

                <h2>6. Cookies and Tracking</h2>
                
                <p>We use cookies and similar technologies:</p>

                <h3>6.1 Essential Cookies</h3>
                <p>Required for the service to function (authentication, security). You cannot disable these.</p>

                <h3>6.2 Analytics Cookies</h3>
                <p>Help us understand how you use DecisionVault (Google Analytics). You can opt out.</p>

                <h3>6.3 Third-Party Cookies</h3>
                <p>Set by our service providers (Stripe, Intercom). Governed by their privacy policies.</p>

                <h2>7. Data Security</h2>
                
                <p>We protect your data with industry-standard security measures:</p>
                <ul>
                    <li><strong>Encryption:</strong> Data encrypted in transit (TLS/SSL) and at rest (AES-256)</li>
                    <li><strong>Access Controls:</strong> Limited employee access on a need-to-know basis</li>
                    <li><strong>Authentication:</strong> Password hashing with bcrypt, optional 2FA</li>
                    <li><strong>Infrastructure:</strong> Secure cloud hosting with SOC 2 compliance</li>
                    <li><strong>Monitoring:</strong> 24/7 security monitoring and intrusion detection</li>
                    <li><strong>Audits:</strong> Regular security audits and penetration testing</li>
                </ul>

                <p>
                    However, no system is 100% secure. If we detect a data breach affecting you, we will notify you promptly 
                    as required by law.
                </p>

                <h2>8. Children's Privacy</h2>
                
                <p>
                    DecisionVault is not intended for children under 18. We do not knowingly collect data from children. 
                    If we learn we have collected data from a child, we will delete it immediately.
                </p>

                <h2>9. International Data Transfers</h2>
                
                <p>
                    DecisionVault is based in [Your Country]. If you access our service from outside [Your Country], your data 
                    may be transferred to and processed in [Your Country] or other countries where our service providers operate.
                </p>
                <p>
                    We ensure appropriate safeguards are in place for international transfers, including Standard Contractual Clauses 
                    for EU data transfers.
                </p>

                <h2>10. Third-Party Links</h2>
                
                <p>
                    DecisionVault may contain links to third-party websites. We are not responsible for their privacy practices. 
                    Please review their privacy policies before providing them with information.
                </p>

                <h2>11. Changes to This Privacy Policy</h2>
                
                <p>
                    We may update this Privacy Policy from time to time. We will notify you of material changes via email or 
                    through the service. The "Last updated" date at the top shows when the policy was last revised.
                </p>
                <p>
                    Continued use of DecisionVault after changes constitutes acceptance of the updated policy.
                </p>

                <h2>12. Contact Us</h2>
                
                <p>If you have questions about this Privacy Policy or want to exercise your rights, contact us:</p>
                <ul>
                    <li><strong>Email:</strong> privacy@yourdomain.com</li>
                    <li><strong>Support:</strong> support@yourdomain.com</li>
                    <li><strong>Mail:</strong> [Your Company Address]</li>
                </ul>

                <p>We will respond to your request within 30 days.</p>

                <h2>13. Your California Privacy Rights</h2>
                
                <p>
                    California residents have the right to request information about how we share certain personal information 
                    with third parties for their direct marketing purposes. We do not share personal information with third parties 
                    for their direct marketing purposes.
                </p>

                <h2>14. Do Not Track</h2>
                
                <p>
                    Some browsers have a "Do Not Track" feature. We currently do not respond to Do Not Track signals because 
                    there is no industry standard for compliance.
                </p>
            </div>
        </div>

        <div class="mt-8 text-center text-sm text-gray-600">
            <p>
                <a href="/terms" class="text-purple-600 hover:text-purple-700">Terms of Service</a>
                <span class="mx-2">•</span>
                <a href="/help" class="text-purple-600 hover:text-purple-700">Help Center</a>
                <span class="mx-2">•</span>
                <a href="mailto:privacy@yourdomain.com" class="text-purple-600 hover:text-purple-700">Privacy Questions</a>
            </p>
        </div>
    </div>
</body>
</html>