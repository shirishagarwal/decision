<?php
/**
 * File Path: organization-settings.php
 * Description: Management of the organization and team members.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDbConnection();
$orgId = $_SESSION['current_org_id'];

// 1. Fetch Org Data
$stmt = $pdo->prepare("SELECT * FROM organizations WHERE id = ?");
$stmt->execute([$orgId]);
$org = $stmt->fetch();

// 2. Fetch Team Members
$stmt = $pdo->prepare("
    SELECT u.name, u.email, u.avatar_url, om.role 
    FROM users u 
    JOIN organization_members om ON u.id = om.user_id 
    WHERE om.organization_id = ?
");
$stmt->execute([$orgId]);
$members = $stmt->fetchAll();

$tab = $_GET['tab'] ?? 'general';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings | <?php echo htmlspecialchars($org['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-16 px-6 flex-grow w-full">
        <div class="grid lg:grid-cols-4 gap-12">
            <!-- Sidebar Tabs -->
            <aside class="space-y-2">
                <a href="?tab=general" class="block px-6 py-3 rounded-xl font-bold text-sm <?php echo $tab === 'general' ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-400 hover:text-gray-900'; ?>">General Settings</a>
                <a href="?tab=team" class="block px-6 py-3 rounded-xl font-bold text-sm <?php echo $tab === 'team' ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-400 hover:text-gray-900'; ?>">Team Members</a>
            </aside>

            <!-- Content -->
            <div class="lg:col-span-3">
                <?php if ($tab === 'general'): ?>
                    <section class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm">
                        <h2 class="text-3xl font-black mb-8">Organization Profile</h2>
                        <form class="space-y-6">
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Workspace Name</label>
                                <input type="text" class="w-full p-4 border rounded-2xl bg-gray-50 font-bold" value="<?php echo htmlspecialchars($org['name']); ?>">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Slug (URL)</label>
                                <input type="text" class="w-full p-4 border rounded-2xl bg-gray-50 text-gray-400" disabled value="offduties.com/v/<?php echo $org['slug']; ?>">
                            </div>
                            <button type="button" class="bg-gray-900 text-white px-8 py-4 rounded-2xl font-black uppercase text-xs tracking-widest">Update Profile</button>
                        </form>
                    </section>

                <?php elseif ($tab === 'team'): ?>
                    <section class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm">
                        <div class="flex justify-between items-center mb-10">
                            <h2 class="text-3xl font-black">Team Members</h2>
                            <button class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest shadow-lg shadow-indigo-100">+ Invite Member</button>
                        </div>

                        <div class="space-y-6">
                            <?php foreach($members as $m): ?>
                                <div class="flex items-center justify-between p-6 bg-gray-50 rounded-[2rem] border border-gray-100">
                                    <div class="flex items-center gap-4">
                                        <img src="<?php echo $m['avatar_url'] ?: 'https://ui-avatars.com/api/?name='.urlencode($m['name']); ?>" class="w-12 h-12 rounded-full border">
                                        <div>
                                            <div class="font-bold text-gray-900"><?php echo htmlspecialchars($m['name']); ?></div>
                                            <div class="text-xs text-gray-400"><?php echo htmlspecialchars($m['email']); ?></div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-black px-3 py-1 bg-white border rounded-full uppercase tracking-widest text-gray-500"><?php echo $m['role']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
