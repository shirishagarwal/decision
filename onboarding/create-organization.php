<?php
require_once __DIR__ . '/../config.php';

if (!isLoggedIn()) {
    redirect(APP_URL . '/index.php');
}

$user = getCurrentUser();
$pdo = getDbConnection();

// Check if user already has organizations
$stmt = $pdo->prepare("
    SELECT COUNT(*) as org_count 
    FROM organization_members 
    WHERE user_id = ? AND status = 'active'
");
$stmt->execute([$user['id']]);
$hasOrgs = $stmt->fetch()['org_count'] > 0;

// If already has org and trying to create another, that's fine (multi-org support)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Organization - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-purple-50 to-pink-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Create Your Organization</h1>
            <p class="text-gray-600">Set up your team's decision-making workspace</p>
        </div>

        <form id="createOrgForm" class="bg-white rounded-2xl border border-gray-200 p-8 space-y-6">
            <!-- Organization Name -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">
                    Organization Name *
                </label>
                <input
                    type="text"
                    id="orgName"
                    required
                    placeholder="Acme Corporation"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
                <p class="text-xs text-gray-500 mt-1">Your company or team name</p>
            </div>

            <!-- URL Slug -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">
                    Organization URL
                </label>
                <div class="flex items-center gap-2">
                    <span class="text-gray-500 text-sm"><?php echo APP_URL; ?>/</span>
                    <input
                        type="text"
                        id="orgSlug"
                        required
                        placeholder="acme-corp"
                        pattern="[a-z0-9-]+"
                        class="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>
                <p class="text-xs text-gray-500 mt-1">Lowercase letters, numbers, and hyphens only</p>
            </div>

            <!-- Industry -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">
                    Industry
                </label>
                <select
                    id="industry"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Select industry...</option>
                    <option value="Technology">Technology</option>
                    <option value="SaaS">SaaS</option>
                    <option value="E-commerce">E-commerce</option>
                    <option value="Finance">Finance</option>
                    <option value="Healthcare">Healthcare</option>
                    <option value="Education">Education</option>
                    <option value="Manufacturing">Manufacturing</option>
                    <option value="Consulting">Consulting</option>
                    <option value="Marketing">Marketing</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <!-- Company Size -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">
                    Company Size
                </label>
                <select
                    id="companySize"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Select size...</option>
                    <option value="1-10">1-10 employees</option>
                    <option value="11-50">11-50 employees</option>
                    <option value="51-200">51-200 employees</option>
                    <option value="201-1000">201-1,000 employees</option>
                    <option value="1001+">1,001+ employees</option>
                </select>
            </div>

            <!-- Website (Optional) -->
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">
                    Website
                </label>
                <input
                    type="url"
                    id="website"
                    placeholder="https://acmecorp.com"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
            </div>

            <!-- Plan Selection -->
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6">
                <h3 class="font-bold text-gray-900 mb-4">Choose Your Plan</h3>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-4 bg-white rounded-lg cursor-pointer border-2 border-transparent hover:border-indigo-500 transition">
                        <input type="radio" name="plan" value="team" checked class="w-5 h-5 text-indigo-600">
                        <div class="flex-1">
                            <div class="font-bold text-gray-900">Team - $49/month</div>
                            <div class="text-sm text-gray-600">5-25 users • 500 decisions/mo</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-4 bg-white rounded-lg cursor-pointer border-2 border-transparent hover:border-indigo-500 transition">
                        <input type="radio" name="plan" value="business" class="w-5 h-5 text-indigo-600">
                        <div class="flex-1">
                            <div class="font-bold text-gray-900">Business - $99/month</div>
                            <div class="text-sm text-gray-600">26-100 users • 1,000 decisions/mo</div>
                        </div>
                    </label>
                </div>
                <div class="mt-4 text-sm text-gray-600">
                    ✨ <strong>14-day free trial</strong> • No credit card required
                </div>
            </div>

            <!-- Submit -->
            <button
                type="submit"
                class="w-full px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 font-bold text-lg"
            >
                Create Organization →
            </button>

            <div class="text-center text-sm text-gray-500">
                By creating an organization, you agree to our <a href="#" class="text-indigo-600 hover:underline">Terms of Service</a>
            </div>
        </form>
    </div>

    <script>
        // Auto-generate slug from name
        document.getElementById('orgName').addEventListener('input', (e) => {
            const name = e.target.value;
            const slug = name
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '');
            document.getElementById('orgSlug').value = slug;
        });

        // Form submission
        document.getElementById('createOrgForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = {
                name: document.getElementById('orgName').value.trim(),
                slug: document.getElementById('orgSlug').value.trim(),
                industry: document.getElementById('industry').value,
                company_size: document.getElementById('companySize').value,
                website: document.getElementById('website').value.trim(),
                plan_type: document.querySelector('input[name="plan"]:checked').value
            };

            // Validation
            if (!formData.name) {
                alert('Please enter an organization name');
                return;
            }

            if (!formData.slug || !/^[a-z0-9-]+$/.test(formData.slug)) {
                alert('Please enter a valid URL slug (lowercase letters, numbers, hyphens only)');
                return;
            }

            try {
                const response = await fetch('<?php echo APP_URL; ?>/api/organizations.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    // Success! Redirect to organization dashboard
                    window.location.href = '<?php echo APP_URL; ?>/organization-dashboard.php?id=' + data.organization_id;
                } else {
                    alert('Error: ' + (data.error || 'Failed to create organization'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to create organization. Please try again.');
            }
        });
    </script>
</body>
</html>