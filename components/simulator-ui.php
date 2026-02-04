<?php
/**
 * File Path: components/simulator-ui.php
 * Description: Visual component to run and display Strategic Stress Tests.
 */
?>
<!-- Aggressive Pre-Mortem UI -->
<div class="mt-12 bg-red-950/20 border-2 border-red-900/40 p-8 rounded-3xl shadow-2xl">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-black text-red-500 uppercase tracking-tighter">Strategic Stress Test</h2>
            <p class="text-red-400/60 text-sm">Our AI 'Chief Disaster Officer' simulates a catastrophic collapse to find your weak points.</p>
        </div>
        <button onclick="runSimulation()" id="simBtn" class="bg-red-600 text-white px-8 py-3 rounded-2xl font-black hover:bg-red-700 transition shadow-xl shadow-red-900/20 active:scale-95">
            Run Pre-Mortem
        </button>
    </div>

    <!-- Loading State -->
    <div id="sim-loader" class="hidden py-12 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-red-500 mb-4"></div>
        <div class="font-mono text-red-500 uppercase tracking-widest text-sm">Identifying failure vectors...</div>
    </div>

    <!-- Results Display -->
    <div id="sim-results" class="hidden grid md:grid-cols-3 gap-6">
        <div class="bg-black/40 p-6 rounded-2xl border border-red-900/50">
            <div class="text-xs font-black text-red-500 mb-3 uppercase tracking-widest opacity-70">Day 30: Red Flags</div>
            <p id="d30" class="text-gray-300 text-sm leading-relaxed"></p>
        </div>
        <div class="bg-black/40 p-6 rounded-2xl border border-red-900/50">
            <div class="text-xs font-black text-red-500 mb-3 uppercase tracking-widest opacity-70">Day 90: Critical Drift</div>
            <p id="d90" class="text-gray-300 text-sm leading-relaxed"></p>
        </div>
        <div class="bg-black/40 p-6 rounded-2xl border border-red-900/50">
            <div class="text-xs font-black text-red-500 mb-3 uppercase tracking-widest opacity-70">Day 365: Final Autopsy</div>
            <p id="d365" class="text-gray-300 text-sm leading-relaxed"></p>
        </div>
    </div>
</div>

<script>
/**
 * Trigger the simulation and update the UI with results.
 */
async function runSimulation() {
    const btn = document.getElementById('simBtn');
    const loader = document.getElementById('sim-loader');
    const results = document.getElementById('sim-results');

    btn.classList.add('hidden');
    loader.classList.remove('hidden');

    try {
        const response = await fetch(`/api/simulate.php?id=<?php echo $decisionId; ?>`);
        const data = await response.json();
        
        if (data.error) throw new Error(data.error);

        document.getElementById('d30').innerText = data.day30;
        document.getElementById('d90').innerText = data.day90;
        document.getElementById('d365').innerText = data.day365;

        loader.classList.add('hidden');
        results.classList.remove('hidden');
        results.scrollIntoView({ behavior: 'smooth' });
    } catch (e) {
        console.error(e);
        alert("Strategic Simulation Failed: " + e.message);
        btn.classList.remove('hidden');
        loader.classList.add('hidden');
    }
}
</script>
