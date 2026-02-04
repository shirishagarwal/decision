<!--
    Empty State Component for Dashboard
    Show this when user has zero decisions
    
    Usage in dashboard.php:
    <?php if (empty($decisions)): ?>
        <?php include 'components/empty-state-dashboard.php'; ?>
    <?php else: ?>
        <!-- Show decisions list -->
    <?php endif; ?>
-->

<div class="max-w-3xl mx-auto text-center py-16 px-4">
    <!-- Illustration/Icon -->
    <div class="mb-8">
        <div class="w-32 h-32 bg-gradient-to-br from-purple-100 to-pink-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-16 h-16 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
        </div>
    </div>

    <!-- Message -->
    <h2 class="text-3xl font-black text-gray-900 mb-4">
        Ready to Make Better Decisions?
    </h2>
    <p class="text-xl text-gray-600 mb-8">
        Get started by creating your first decision and see AI-powered recommendations in action.
    </p>

    <!-- CTA Button -->
    <a href="/decisions/create" class="inline-flex items-center justify-center px-8 py-4 bg-purple-600 text-white rounded-xl font-bold text-lg hover:bg-purple-700 transition-all shadow-lg hover:shadow-xl mb-8">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Create Your First Decision
    </a>

    <!-- How it works - Quick overview -->
    <div class="grid md:grid-cols-3 gap-6 mt-12">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
                <span class="text-2xl">📝</span>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Describe Your Decision</h3>
            <p class="text-sm text-gray-600">
                Tell us what you're trying to decide and provide context
            </p>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
                <span class="text-2xl">🤖</span>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Get AI Recommendations</h3>
            <p class="text-sm text-gray-600">
                See options with success rates from 2,000+ companies
            </p>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
                <span class="text-2xl">📊</span>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Track & Learn</h3>
            <p class="text-sm text-gray-600">
                Review outcomes and improve future decisions
            </p>
        </div>
    </div>

    <!-- Example decisions -->
    <div class="mt-12 bg-purple-50 rounded-xl p-6">
        <p class="text-sm font-semibold text-purple-900 mb-4">💡 Example decisions you can create:</p>
        <div class="flex flex-wrap gap-2 justify-center">
            <span class="px-4 py-2 bg-white rounded-lg text-sm text-gray-700 border border-purple-200">
                "Should we hire a VP of Sales?"
            </span>
            <span class="px-4 py-2 bg-white rounded-lg text-sm text-gray-700 border border-purple-200">
                "Which pricing model should we use?"
            </span>
            <span class="px-4 py-2 bg-white rounded-lg text-sm text-gray-700 border border-purple-200">
                "Should we pivot our product strategy?"
            </span>
            <span class="px-4 py-2 bg-white rounded-lg text-sm text-gray-700 border border-purple-200">
                "When should we raise our Series A?"
            </span>
        </div>
    </div>

    <!-- Help link -->
    <div class="mt-8 text-sm text-gray-600">
        <p>
            Need help getting started?
            <a href="/help" class="text-purple-600 hover:text-purple-700 font-semibold">
                Check out our guide →
            </a>
        </p>
    </div>
</div>