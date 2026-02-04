<!-- 
    Onboarding Modal Component
    Include this in dashboard.php for first-time users
    
    Usage:
    <?php if ($user['is_first_login']): ?>
        <?php include 'components/onboarding-modal.php'; ?>
    <?php endif; ?>
-->

<div id="onboarding-modal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl transform transition-all">
        <!-- Progress indicator -->
        <div class="flex items-center justify-center gap-2 pt-6 px-6">
            <div id="step-indicator-1" class="w-2 h-2 rounded-full bg-purple-600"></div>
            <div id="step-indicator-2" class="w-2 h-2 rounded-full bg-gray-300"></div>
            <div id="step-indicator-3" class="w-2 h-2 rounded-full bg-gray-300"></div>
        </div>

        <!-- Step 1: Welcome -->
        <div id="onboarding-step-1" class="p-8 text-center">
            <div class="w-20 h-20 bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                </svg>
            </div>
            
            <h2 class="text-3xl font-black text-gray-900 mb-3">
                Welcome to DecisionVault! 🎉
            </h2>
            <p class="text-lg text-gray-600 mb-8">
                Let's take 30 seconds to show you how to make better decisions with AI
            </p>
            
            <button onclick="nextOnboardingStep(2)" class="w-full bg-purple-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-purple-700 transition-colors">
                Let's Go!
            </button>
            <button onclick="skipOnboarding()" class="w-full text-gray-500 px-8 py-3 rounded-lg font-medium hover:text-gray-700 mt-2">
                Skip tutorial
            </button>
        </div>

        <!-- Step 2: How it works -->
        <div id="onboarding-step-2" class="p-8 hidden">
            <h2 class="text-2xl font-black text-gray-900 mb-6 text-center">
                How DecisionVault Works
            </h2>
            
            <div class="space-y-4 mb-8">
                <div class="flex items-start gap-4 bg-purple-50 rounded-xl p-4">
                    <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-bold text-lg">1</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 mb-1">Create a Decision</h3>
                        <p class="text-sm text-gray-600">
                            Describe what you need to decide (e.g., "Should we hire a VP of Sales?")
                        </p>
                    </div>
                </div>
                
                <div class="flex items-start gap-4 bg-blue-50 rounded-xl p-4">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-bold text-lg">2</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 mb-1">Get AI Recommendations</h3>
                        <p class="text-sm text-gray-600">
                            See options with success rates based on 2,000+ real company outcomes
                        </p>
                    </div>
                </div>
                
                <div class="flex items-start gap-4 bg-green-50 rounded-xl p-4">
                    <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-bold text-lg">3</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 mb-1">Review & Learn</h3>
                        <p class="text-sm text-gray-600">
                            Track what worked and improve future decisions
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button onclick="nextOnboardingStep(1)" class="flex-1 bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-200">
                    Back
                </button>
                <button onclick="nextOnboardingStep(3)" class="flex-1 bg-purple-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-purple-700">
                    Next
                </button>
            </div>
        </div>

        <!-- Step 3: Create first decision -->
        <div id="onboarding-step-3" class="p-8 hidden">
            <h2 class="text-2xl font-black text-gray-900 mb-3 text-center">
                Ready to Start? 🚀
            </h2>
            <p class="text-gray-600 mb-6 text-center">
                Create your first decision to see AI recommendations in action
            </p>
            
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-6 mb-6 text-center">
                <p class="text-sm text-gray-600 mb-2">💡 <strong>Pro tip:</strong></p>
                <p class="text-sm text-gray-700">
                    The more context you provide, the better the AI recommendations. 
                    Include details like company size, industry, and any constraints.
                </p>
            </div>
            
            <div class="space-y-3">
                <a href="/decisions/create" class="block w-full bg-purple-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-purple-700 transition-colors text-center">
                    Create My First Decision
                </a>
                <button onclick="completeOnboarding()" class="w-full bg-white text-purple-600 px-8 py-3 rounded-lg font-semibold border-2 border-purple-600 hover:bg-purple-50">
                    I'll Do This Later
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Onboarding modal logic
function nextOnboardingStep(step) {
    // Hide all steps
    document.getElementById('onboarding-step-1').classList.add('hidden');
    document.getElementById('onboarding-step-2').classList.add('hidden');
    document.getElementById('onboarding-step-3').classList.add('hidden');
    
    // Update indicators
    document.getElementById('step-indicator-1').classList.remove('bg-purple-600');
    document.getElementById('step-indicator-1').classList.add('bg-gray-300');
    document.getElementById('step-indicator-2').classList.remove('bg-purple-600');
    document.getElementById('step-indicator-2').classList.add('bg-gray-300');
    document.getElementById('step-indicator-3').classList.remove('bg-purple-600');
    document.getElementById('step-indicator-3').classList.add('bg-gray-300');
    
    // Show current step
    document.getElementById('onboarding-step-' + step).classList.remove('hidden');
    document.getElementById('step-indicator-' + step).classList.remove('bg-gray-300');
    document.getElementById('step-indicator-' + step).classList.add('bg-purple-600');
}

function skipOnboarding() {
    if (confirm('Are you sure you want to skip the tutorial? You can always access help from the menu.')) {
        completeOnboarding();
    }
}

function completeOnboarding() {
    // Mark onboarding as complete
    fetch('/api/complete-onboarding.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(() => {
        // Close modal with animation
        const modal = document.getElementById('onboarding-modal');
        modal.style.opacity = '0';
        modal.style.transition = 'opacity 0.3s';
        setTimeout(() => {
            modal.remove();
        }, 300);
    });
}

// Prevent closing modal by clicking outside (force completion)
document.getElementById('onboarding-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        e.stopPropagation();
    }
});
</script>

<style>
#onboarding-modal {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>