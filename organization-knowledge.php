<?php
/**
 * File Path: organization-knowledge.php
 * Description: Enterprise interface for managing internal datasets for RAG.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$user = getCurrentUser();
$orgId = $_SESSION['current_org_id'];
$pdo = getDbConnection();

// Fetch existing documents
$stmt = $pdo->prepare("
    SELECT d.*, u.name as uploader 
    FROM organization_documents d 
    JOIN users u ON d.uploaded_by = u.id 
    WHERE d.organization_id = ? 
    ORDER BY d.created_at DESC
");
$stmt->execute([$orgId]);
$documents = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Institutional Knowledge | DecisionVault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .knowledge-card { background: white; border: 1px solid #e2e8f0; border-radius: 1.25rem; transition: all 0.2s; }
        .knowledge-card:hover { border-color: #6366f1; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-12 px-6 w-full flex-grow">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-12">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600">Privacy Shield RAG Active</span>
                </div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">Institutional Knowledge</h1>
                <p class="text-sm text-slate-500 mt-2">Link internal datasets to train the AI context without exposing data to global models.</p>
            </div>
            
            <form action="api/upload-document.php" method="POST" enctype="multipart/form-data" class="flex gap-2">
                <input type="file" name="doc" id="fileInput" class="hidden" onchange="this.form.submit()">
                <button type="button" onclick="document.getElementById('fileInput').click()" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest shadow-lg hover:bg-indigo-700 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round"/></svg>
                    Upload Internal Dataset
                </button>
            </form>
        </header>

        <div class="grid lg:grid-cols-4 gap-8">
            <div class="lg:col-span-3">
                <div class="knowledge-card overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Document</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Intelligence Link</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($documents)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-20 text-center text-slate-400 font-medium">No internal datasets linked. Upload PDFs or policy docs to enable RAG intelligence.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($documents as $doc): ?>
                                    <tr class="hover:bg-slate-50 transition group">
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 font-bold text-xs">PDF</div>
                                                <div>
                                                    <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($doc['file_name']); ?></div>
                                                    <div class="text-[10px] text-slate-400 font-medium">Uploaded by <?php echo htmlspecialchars($doc['uploader']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-xs font-bold text-slate-500 uppercase"><?php echo $doc['file_type']; ?></td>
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-2">
                                                <div class="w-1.5 h-1.5 rounded-full <?php echo $doc['status'] === 'ready' ? 'bg-emerald-500' : 'bg-amber-500'; ?>"></div>
                                                <span class="text-[10px] font-black uppercase tracking-tight text-slate-600"><?php echo $doc['status']; ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-xs font-bold text-indigo-600">Decision Modeling</td>
                                        <td class="px-6 py-5 text-right">
                                            <button class="text-slate-300 hover:text-red-500 transition opacity-0 group-hover:opacity-100">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="p-6 bg-slate-900 text-white rounded-[2rem] shadow-xl relative overflow-hidden">
                    <div class="relative z-10">
                        <h3 class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-6">Zero-Training Guarantee</h3>
                        <p class="text-xs text-slate-400 leading-relaxed font-medium mb-6">
                            DecisionVault uses <strong>In-Memory Context Injection (RAG)</strong>. Your internal datasets are indexed in a private silo and are never used to train global AI models.
                        </p>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-500">✓</div>
                                <span class="text-[10px] font-black uppercase">AES-256 Encryption</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-500">✓</div>
                                <span class="text-[10px] font-black uppercase">Siloed Persistence</span>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-indigo-500/10 blur-[50px] rounded-full"></div>
                </div>
                
                <div class="p-6 bg-white border border-slate-200 rounded-[2rem]">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Supported Datasets</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-2 text-xs font-bold text-slate-700">
                            <span class="w-1.5 h-1.5 bg-indigo-600 rounded-full"></span> Internal Wikis (.pdf)
                        </li>
                        <li class="flex items-center gap-2 text-xs font-bold text-slate-700">
                            <span class="w-1.5 h-1.5 bg-indigo-600 rounded-full"></span> Strategic Plans (.docx)
                        </li>
                        <li class="flex items-center gap-2 text-xs font-bold text-slate-700">
                            <span class="w-1.5 h-1.5 bg-indigo-600 rounded-full"></span> Financial Proxies (.xlsx)
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
