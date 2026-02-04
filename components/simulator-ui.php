<div class="mt-12">
    <button onclick="runSimulation()" id="simBtn" class="w-full bg-red-600/10 border-2 border-red-500/50 text-red-500 py-4 rounded-2xl font-black text-lg hover:bg-red-600 hover:text-white transition group">
        <span class="group-hover:animate-pulse">⚠️ RUN STRATEGIC STRESS TEST</span>
    </button>

    <div id="sim-results" class="hidden mt-8 space-y-4">
        <h3 class="text-red-500 font-black uppercase tracking-widest text-sm">Aggressive Failure Projection</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <div class="bg-red-950/30 border border-red-900/50 p-6 rounded-2xl">
                <div class="text-xs font-bold text-red-400 mb-2">DAY 30</div>
                <p id="day30-text" class="text-sm text-gray-300"></p>
            </div>
            <div class="bg-red-950/40 border border-red-900/60 p-6 rounded-2xl">
                <div class="text-xs font-bold text-red-400 mb-2">DAY 90</div>
                <p id="day90-text" class="text-sm text-gray-300"></p>
            </div>
            <div class="bg-red-950/50 border border-red-900/80 p-6 rounded-2xl">
                <div class="text-xs font-bold text-red-400 mb-2">DAY 365</div>
                <p id="day365-text" class="text-sm text-gray-300"></p>
            </div>
        </div>
    </div>
</div>

<script>
async function runSimulation() {
    const btn = document.getElementById('simBtn');
    btn.innerText = "SIMULATING COLLAPSE...";
    
    // Call the simulation API
    const res = await fetch(`/api/simulate.php?id=<?php echo $decisionId; ?>`);
    const data = await res.json();
    
    document.getElementById('sim-results').classList.remove('hidden');
    document.getElementById('day30-text').innerText = data.day30;
    document.getElementById('day90-text').innerText = data.day90;
    document.getElementById('day365-text').innerText = data.day365;
    
    btn.classList.add('hidden');
}
</script>
