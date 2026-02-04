<div class="mt-12 bg-red-950/20 border-2 border-red-900/30 rounded-3xl p-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-black text-red-500 uppercase tracking-tighter">Strategic Stress Test</h2>
            <p class="text-red-400/60 text-sm">Simulate the collapse of this decision to prevent it.</p>
        </div>
        <button onclick="runSimulation()" id="simBtn" class="bg-red-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-red-700 transition shadow-xl shadow-red-900/20">
            Run Pre-Mortem
        </button>
    </div>

    <div id="sim-loader" class="hidden py-12 text-center text-red-500 font-mono animate-pulse">
        CALCULATING FAILURE VECTORS...
    </div>

    <div id="sim-results" class="hidden grid md:grid-cols-3 gap-6">
        <div class="bg-black/40 p-6 rounded-2xl border border-red-900/50">
            <div class="text-xs font-black text-red-500 mb-2 uppercase">Day 30 Red Flags</div>
            <p id="day30-text" class="text-gray-300 text-sm leading-relaxed"></p>
        </div>
        <div class="bg-black/40 p-6 rounded-2xl border border-red-900/50">
            <div class="text-xs font-black text-red-500 mb-2 uppercase">Day 90 Drift</div>
            <p id="day90-text" class="text-gray-300 text-sm leading-relaxed"></p>
        </div>
        <div class="bg-black/40 p-6 rounded-2xl border border-red-900/50">
            <div class="text-xs font-black text-red-500 mb-2 uppercase">Day 365 Autopsy</div>
            <p id="day365-text" class="text-gray-300 text-sm leading-relaxed"></p>
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
        
        document.getElementById('day30-text').innerText = data.day30;
        document.getElementById('day90-text').innerText = data.day90;
        document.getElementById('day365-text').innerText = data.day365;

        loader.classList.add('hidden');
        results.classList.remove('hidden');
    } catch (e) {
        alert("Simulation failed. Check API key.");
        btn.classList.remove('hidden');
    }
}
</script>
