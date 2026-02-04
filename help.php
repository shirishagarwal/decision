<?php
require_once __DIR__ . '/config.php';
$isLoggedIn = isLoggedIn();
$pageTitle = "Help & Support";
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
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-purple-600 to-pink-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold">D</span>
                </div>
                <span class="text-xl font-bold text-gray-900">DecisionVault</span>
            </a>
            <?php if ($isLoggedIn): ?>
                <a href="/dashboard" class="text-purple-600 hover:text-purple-700 font-medium">← Back to Dashboard</a>
            <?php else: ?>
                <a href="/" class="text-purple-600 hover:text-purple-700 font-medium">← Back to Home</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 py-12">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-black text-gray-900 mb-4"><?php echo $pageTitle; ?></h1>
            <p class="text-xl text-gray-600">Find answers to common questions or get in touch with us</p>
        </div>

        <!-- Search (future enhancement) -->
        <div class="mb-12 max-w-2xl mx-auto">
            <div class="relative">
                <input type="text" 
                       placeholder="Search help articles..." 
                       class="w-full px-6 py-4 rounded-xl border-2 border-gray-200 focus:border-purple-600 focus:outline-none text-lg">
                <svg class="absolute right-6 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="grid md:grid-cols-3 gap-6 mb-16">
            <a href="#getting-started" class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow border-2 border-transparent hover:border-purple-600">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Getting Started</h3>
                <p class="text-gray-600 text-sm">Learn the basics of using DecisionVault</p>
            </a>

            <a href="#features" class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow border-2 border-transparent hover:border-purple-600">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Features Guide</h3>
                <p class="text-gray-600 text-sm">Deep dive into AI recommendations and more</p>
            </a>

            <a href="#contact" class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow border-2 border-transparent hover:border-purple-600">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Contact Support</h3>
                <p class="text-gray-600 text-sm">Get help from our team</p>
            </a>
        </div>

        <!-- FAQ Sections -->
        <div class="max-w-4xl mx-auto space-y-12">
            
            <!-- Getting Started -->
            <div id="getting-started">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600">🚀</span>
                    Getting Started
                </h2>
                
                <div class="space-y-4">
                    <details class="bg-white rounded-lg p-6 shadow-sm group">
                        <summary class="font-semibold text-gray-900 cursor-pointer flex items-center justify-between">
                            <span>How do I create my first decision?</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="mt-4 text-gray-600 space-y-2">
                            <p>1. Click "Create Decision" from your dashboard</p>
                            <p>2. Enter your decision question (e.g., "Should we hire a VP of Sales?")</p>
                            <p>3. Add context about your situation</p>
                            <p>4. Our AI will automatically suggest options based on similar decisions</p>
                            <p>5. Review AI recommendations, add your own options, or use suggested ones</p>
                            <p>6. Invite team members to collaborate</p>
                            <p>7. Set a review date to track outcomes</p>
                        </div>
                    </details>

                    <details class="bg-white rounded-lg p-6 shadow-sm group">
                        <summary class="font-semibold text-gray-900 cursor-pointer flex items-center justify-between">
                            <span>How does the AI recommendation work?</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="mt-4 text-gray-600">
                            <p>Our AI analyzes your decision against a database of 2,000+ real company outcomes:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>Startup failures (what went wrong and why)</li>
                                <li>Success patterns from similar companies</li>
                                <li>Industry benchmarks and success rates</li>
                                <li>Your own historical decision data</li>
                            </ul>
                            <p class="mt-2">It then suggests options with success rates, pros/cons, and warnings about common mistakes.</p>
                        </div>
                    </details>

                    <details class="bg-white rounded-lg p-6 shadow-sm group">
                        <summary class="font-semibold text-gray-900 cursor-pointer flex items-center justify-between">
                            <span>How do I invite team members?</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="mt-4 text-gray-600">
                            <p><strong>Option 1: From a Decision</strong></p>
                            <p>• Open any decision → Step 3: Team → Enter email addresses → Send invites</p>
                            <p class="mt-2"><strong>Option 2: From Settings</strong></p>
                            <p>• Go to Settings → Team → Invite Members → Enter emails</p>
                            <p class="mt-2">Team members will receive an email invitation and can create an account to join.</p>
                        </div>
                    </details>
                </div>
            </div>

            <!-- Features -->
            <div id="features">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">⚡</span>
                    Features
                </h2>
                
                <div class="space-y-4">
                    <details class="bg-white rounded-lg p-6 shadow-sm group">
                        <summary class="font-semibold text-gray-900 cursor-pointer flex items-center justify-between">
                            <span>What is the Learning Loop?</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="mt-4 text-gray-600">
                            <p>The Learning Loop helps you track what actually worked:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>Set a review date when creating decisions (3-6 months out)</li>
                                <li>You'll receive a reminder to review the outcome</li>
                                <li>Record what happened (success, failure, or mixed results)</li>
                                <li>AI learns from your outcomes to improve future recommendations</li>
                                <li>Build institutional knowledge about what works for YOUR team</li>
                            </ul>
                        </div>
                    </details>

                    <details class="bg-white rounded-lg p-6 shadow-sm group">
                        <summary class="font-semibold text-gray-900 cursor-pointer flex items-center justify-between">
                            <span>How accurate are the success rates?</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="mt-4 text-gray-600">
                            <p>Success rates come from:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li><strong>Research data:</strong> CB Insights analysis of 483 startups</li>
                                <li><strong>Industry benchmarks:</strong> SaaS Capital, ProfitWell studies</li>
                                <li><strong>Real failures:</strong> Documented startup post-mortems</li>
                                <li><strong>Your data:</strong> Your own decision outcomes over time</li>
                            </ul>
                            <p class="mt-2"><strong>Important:</strong> These are predictions, not guarantees. Use them as data points alongside your judgment.</p>
                        </div>
                    </details>

                    <details class="bg-white rounded-lg p-6 shadow-sm group">
                        <summary class="font-semibold text-gray-900 cursor-pointer flex items-center justify-between">
                            <span>Can I export my decision data?</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="mt-4 text-gray-600">
                            <p>Yes! Go to Settings → Export Data to download:</p>
                            <ul class="list-disc pl-6 mt-2">
                                <li>All your decisions in CSV or JSON format</li>
                                <li>Options, votes, and outcomes</li>
                                <li>Team member contributions</li>
                                <li>AI recommendations and insights</li>
                            </ul>
                        </div>
                    </details>
                </div>
            </div>

            <!-- Account & Billing -->
            <div id="billing">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center text-green-600">💳</span>
                    Account & Billing
                </h2>
                
                <div class="space-y-4">
                    <details class="bg-white rounded-lg p-6 shadow-sm group">
                        <summary class="font-semibold text-gray-900 cursor-pointer flex items-center justify-between">
                            <span>How do I upgrade my plan?</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="mt-4 text-gray-600">
                            <p>Go to Settings → Billing → Choose Plan → Enter payment details</p>
                            <p class="mt-2">You'll be charged immediately and have access to Pro features right away.</p>
                        </div>
                    </details>

                    <details class="bg-white rounded-lg p-6 shadow-sm group">
                        <summary class="font-semibold text-gray-900 cursor-pointer flex items-center justify-between">
                            <span>Can I cancel anytime?</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="mt-4 text-gray-600">
                            <p>Yes, cancel anytime from Settings → Billing → Cancel Subscription</p>
                            <p class="mt-2">You'll keep access until the end of your billing period. No refunds for partial months.</p>
                        </div>
                    </details>

                    <details class="bg-white rounded-lg p-6 shadow-sm group">
                        <summary class="font-semibold text-gray-900 cursor-pointer flex items-center justify-between">
                            <span>What happens to my data if I cancel?</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="mt-4 text-gray-600">
                            <p>Your data remains accessible for 30 days after cancellation.</p>
                            <p class="mt-2">Download your data from Settings → Export Data before it's permanently deleted.</p>
                        </div>
                    </details>
                </div>
            </div>

            <!-- Troubleshooting -->
            <div id="troubleshooting">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-red-600">🔧</span>
                    Troubleshooting
                </h2>
                
                <div class="space-y-4">
                    <details class="bg-white rounded-lg p-6 shadow-sm group">
                        <summary class="font-semibold text-gray-900 cursor-pointer flex items-center justify-between">
                            <span>AI recommendations aren't showing up</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="mt-4 text-gray-600">
                            <p>Try these fixes:</p>
                            <ul class="list-disc pl-6 mt-2">
                                <li>Add more context to your decision (AI needs details to make recommendations)</li>
                                <li>Check your internet connection</li>
                                <li>Refresh the page</li>
                                <li>If still not working, contact support@yourdomain.com</li>
                            </ul>
                        </div>
                    </details>

                    <details class="bg-white rounded-lg p-6 shadow-sm group">
                        <summary class="font-semibold text-gray-900 cursor-pointer flex items-center justify-between">
                            <span>I forgot my password</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="mt-4 text-gray-600">
                            <p>Click "Forgot Password" on the login page → Enter your email → Check for reset link</p>
                            <p class="mt-2">If you don't receive the email within 5 minutes, check your spam folder.</p>
                        </div>
                    </details>
                </div>
            </div>
        </div>

        <!-- Contact Support Section -->
        <div id="contact" class="mt-16 bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl p-8 md:p-12 text-white">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="text-3xl font-bold mb-4">Still Need Help?</h2>
                <p class="text-xl text-purple-100 mb-8">
                    Our support team is here to help you succeed
                </p>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <a href="mailto:support@yourdomain.com" class="bg-white text-purple-600 rounded-xl p-6 hover:shadow-lg transition-shadow">
                        <svg class="w-8 h-8 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <h3 class="font-bold text-lg mb-1">Email Support</h3>
                        <p class="text-sm text-gray-600">support@yourdomain.com</p>
                        <p class="text-xs text-gray-500 mt-2">Response within 24 hours</p>
                    </a>

                    <div class="bg-white/10 backdrop-blur rounded-xl p-6">
                        <svg class="w-8 h-8 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <h3 class="font-bold text-lg mb-1">Live Chat</h3>
                        <p class="text-sm text-purple-100">Coming Soon</p>
                        <p class="text-xs text-purple-200 mt-2">We're working on it!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-gray-200 mt-16">
        <div class="max-w-6xl mx-auto px-4 py-8">
            <div class="text-center text-sm text-gray-600">
                <p>
                    <a href="/terms" class="hover:text-purple-600">Terms</a>
                    <span class="mx-2">•</span>
                    <a href="/privacy" class="hover:text-purple-600">Privacy</a>
                    <span class="mx-2">•</span>
                    <a href="/" class="hover:text-purple-600">Home</a>
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Auto-open FAQ if URL has hash
        if (window.location.hash) {
            const target = document.querySelector(window.location.hash);
            if (target && target.tagName === 'DETAILS') {
                target.open = true;
            }
        }
    </script>
</body>
</html>