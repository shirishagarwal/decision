<div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-6 text-white text-center shadow-xl">
    <div class="text-sm font-bold uppercase tracking-widest opacity-80 mb-2">My Decision IQ</div>
    <div class="text-6xl font-black mb-4"><?php echo $decisionIQ; ?></div>
    
    <p class="text-sm text-purple-100 mb-6">
        You are in the <strong>top 12%</strong> of strategic thinkers this month.
    </p>

    <button onclick="shareIQ()" class="bg-white text-purple-600 px-6 py-2 rounded-xl font-black hover:scale-105 transition shadow-lg">
        📤 Share My Rank
    </button>
</div>

<script>
function shareIQ() {
    const text = `My Decision IQ is <?php echo $decisionIQ; ?>! I'm tracking my strategic accuracy with DecisionVault. 🧠📊`;
    if (navigator.share) {
        navigator.share({ title: 'Decision IQ', text: text, url: window.location.origin });
    } else {
        navigator.clipboard.writeText(text);
        alert("Copied to clipboard! Share on LinkedIn or Twitter to grow your Strategic Brand.");
    }
}
</script>
