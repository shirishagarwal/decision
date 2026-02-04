<div class="mt-12 bg-red-950/20 border-2 border-red-900/40 p-8 rounded-3xl shadow-2xl">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-black text-red-500 uppercase tracking-tighter">Strategic Stress Test</h2>
            <p class="text-red-400/60 text-sm">Simulate a catastrophic failure to find your weak points.</p>
        </div>
        <button onclick="runSimulation()" id="simBtn" class="bg-red-600 text-white px-8 py-3 rounded-2xl font-black hover:bg-red-700 transition shadow-xl shadow-red-900/20">
            Run Pre-Mortem
        </button>
    </div>

    <div id="sim-loader" class="hidden py-10 text-center font-mono text-red-500 animate-pulse uppercase">Identifying failure vectors...</div>

    <div id="sim-results" class="hidden grid md:grid-cols-3 gap-6">
        <div class="bg-black/40 p-6 rounded-2xl border border-red-900/50">
            <div class="text-xs font-black text-red-500 mb-2 uppercase tracking-widest">Day 30 Red Flags</div>
            <p id="d30" class="text-gray-300 text-sm leading-relaxed"></p>
        </div>
        <div class="bg-black/40 p-6 rounded-2xl border border-red-900/50">
            <div class="text-xs font-black text-red-500 mb-2 uppercase tracking-widest">Day 90 Drift</div>
            <p id="d90" class="text-gray-300 text-sm leading-relaxed"></p>
        </div>
        <div class="bg-black/40 p-6 rounded-2xl border border-red-900/50">
            <div class="text-xs font-black text-red-500 mb-2 uppercase tracking-widest">Day 365 Autopsy</div>
            <p id="d365" class="text-gray-300 text-sm leading-relaxed"></p>
        </div>
    </div>
</div>

<script>
async function runSimulation() {
    const btn = document.getElementById('simBtn');
    const loader = document.getElementById('sim-loader');
    const results = document.getElementById('sim-results');

    btn.classList.add('hidden');
    loader.classList.remove('hidden');

    try {
        const res = await fetch(`/api/simulate.php?id=<?php echo $decisionId; ?>`);
        const data = await res.json();
        
        document.getElementById('d30').innerText = data.day30;
        document.getElementById('d90').innerText = data.day90;
        document.getElementById('d365').innerText = data.day365;

        loader.classList.add('hidden');
        results.classList.remove('hidden');
    } catch (e) {
        alert("Simulation failed. Check your Gemini API Key.");
        btn.classList.remove('hidden');
    }
}
</script>
