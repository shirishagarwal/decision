<?php
/**
 * File Path: components/simulator-ui.php
 * Usage: Included in decision.php to handle AI simulations.
 */
?>
<div class="p-8 premium-card bg-slate-900 text-white shadow-2xl shadow-indigo-200/50 border-none relative overflow-hidden">
    <!-- Background Decor -->
    <div class="absolute -top-10 -right-10 w-40 h-40 bg-indigo-600/20 blur-3xl rounded-full"></div>

    <div class="relative z-10">
        <div class="flex items-center gap-2 mb-8">
            <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
            <h2 class="text-[10px] font-black text-red-400 uppercase tracking-[0.2em]">Aggressive Stress Test</h2>
        </div>

        <div id="sim-intro" class="<?php echo $simulation ? 'hidden' : ''; ?>">
            <p class="text-sm text-slate-400 leading-relaxed mb-8">
                Our AI 'Chief Disaster Officer' will simulate a catastrophic collapse of this decision to identify your hidden weak points.
            </p>
            <button onclick="runStressTest()" id="simBtn" class="w-full bg-red-600 text-white py-4 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-red-700 transition-all shadow-xl shadow-red-900/40">
                Simulate Failure
            </button>
        </div>

        <!-- Loading State -->
        <div id="sim-loader" class="hidden py-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-red-500 mb-4"></div>
            <div class="text-[10px] font-black text-red-500 uppercase tracking-widest">Calculating failure vectors...</div>
        </div>

        <!-- Results Display -->
        <div id="sim-results" class="<?php echo !$simulation ? 'hidden' : ''; ?> space-y-6">
            <div class="space-y-4">
                <div class="p-4 bg-white/5 border border-white/10 rounded-2xl">
                    <div class="text-[10px] font-black text-red-500 mb-1 uppercase">Day 30 Red Flags</div>
                    <p id="d30" class="text-xs text-slate-300 leading-relaxed font-medium"><?php echo $simulation['day30'] ?? ''; ?></p>
                </div>
                <div class="p-4 bg-white/5 border border-white/10 rounded-2xl">
                    <div class="text-[10px] font-black text-red-500 mb-1 uppercase">Day 90 Critical Drift</div>
                    <p id="d90" class="text-xs text-slate-300 leading-relaxed font-medium"><?php echo $simulation['day90'] ?? ''; ?></p>
                </div>
                <div class="p-4 bg-white/5 border border-white/10 rounded-2xl">
                    <div class="text-[10px] font-black text-red-500 mb-1 uppercase">Day 365 Autopsy</div>
                    <p id="d365" class="text-xs text-slate-300 leading-relaxed font-medium"><?php echo $simulation['day365'] ?? ''; ?></p>
                </div>
            </div>
            
            <div class="pt-4 border-t border-white/10">
                <div class="text-[10px] font-black text-indigo-400 mb-2 uppercase">Recommended Mitigation</div>
                <p id="mitigate" class="text-xs text-indigo-100 font-bold leading-relaxed italic"><?php echo $simulation['mitigation_plan'] ?? ''; ?></p>
            </div>
        </div>
    </div>
</div>

<script>
async function runStressTest() {
    const btn = document.getElementById('simBtn');
    const loader = document.getElementById('sim-loader');
    const results = document.getElementById('sim-results');
    const intro = document.getElementById('sim-intro');

    intro.classList.add('hidden');
    loader.classList.remove('hidden');

    try {
        const response = await fetch('/api/simulate.php?id=<?php echo $targetDecisionId; ?>');
        const data = await response.json();
        
        if (data.error) throw new Error(data.error);

        document.getElementById('d30').innerText = data.day30;
        document.getElementById('d90').innerText = data.day90;
        document.getElementById('d365').innerText = data.day365;
        document.getElementById('mitigate').innerText = data.mitigation;

        loader.classList.add('hidden');
        results.classList.remove('hidden');
    } catch (e) {
        alert("Stress Test Failed: " + e.message);
        loader.classList.add('hidden');
        intro.classList.remove('hidden');
    }
}
</script>
