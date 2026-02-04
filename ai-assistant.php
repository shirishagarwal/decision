<?php
require_once __DIR__ . '/lib/auth.php';
requireOrgAccess();
?>
<!DOCTYPE html>
<html>
<head>
    <title>AI Assistant | <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-indigo-50 min-h-screen">
    <div class="max-w-2xl mx-auto pt-12 px-4">
        <a href="/dashboard.php" class="text-indigo-600 font-bold mb-8 inline-block">← Back to Dashboard</a>
        
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border-4 border-white">
            <div class="bg-indigo-600 p-6 text-white text-center">
                <h2 class="text-xl font-bold">Intelligent Strategy Assistant</h2>
                <p class="text-indigo-100 text-sm">Powered by <?php echo GEMINI_MODEL; ?></p> </div>

            <div id="chat-window" class="h-96 overflow-y-auto p-6 space-y-4 bg-gray-50">
                <div class="bg-indigo-100 p-4 rounded-2xl rounded-tl-none mr-12 text-sm">
                    Hello! I am your decision co-pilot. What's on your mind? Are we hiring, pricing, or choosing a new tech stack?
                </div>
            </div>

            <div class="p-4 border-t bg-white">
                <div class="flex gap-2">
                    <input id="user-input" type="text" placeholder="e.g. Should we hire a VP of Sales now?"
                           class="flex-1 p-3 border-2 rounded-xl focus:border-indigo-600 outline-none">
                    <button onclick="askAI()" class="bg-indigo-600 text-white px-6 rounded-xl font-bold">Ask</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function askAI() {
            const input = document.getElementById('user-input');
            const window = document.getElementById('chat-window');
            if (!input.value.trim()) return;

            // Add user message to UI
            window.innerHTML += `<div class="bg-white border p-4 rounded-2xl rounded-tr-none ml-12 text-sm shadow-sm">${input.value}</div>`;
            const msg = input.value;
            input.value = '';

            // Call API (We will build this next)
            const response = await fetch('/api/ai-chat.php', {
                method: 'POST',
                body: JSON.stringify({ message: msg })
            });
            const data = await response.json();
            
            window.innerHTML += `<div class="bg-indigo-100 p-4 rounded-2xl rounded-tl-none mr-12 text-sm">${data.response}</div>`;
            window.scrollTop = window.scrollHeight;
        }
    </script>
</body>
</html>
