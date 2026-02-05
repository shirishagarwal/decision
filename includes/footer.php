<?php
/**
 * File Path: includes/footer.php
 */
?>
<footer class="bg-white border-t border-gray-100 py-12 px-6 mt-20">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8 text-center md:text-left">
        <div>
            <div class="font-black text-xl tracking-tighter text-gray-900 mb-2">DECISION<span class="text-indigo-600">VAULT</span></div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">The Strategic Intelligence OS</p>
        </div>
        
        <div class="flex gap-10">
            <div class="space-y-3">
                <h4 class="text-[10px] font-black text-gray-900 uppercase tracking-widest">Platform</h4>
                <div class="flex flex-col gap-2 text-xs font-bold text-gray-400">
                    <a href="/dashboard.php" class="hover:text-indigo-600">Dashboard</a>
                    <a href="/marketplace.php" class="hover:text-indigo-600">Marketplace</a>
                </div>
            </div>
            <div class="space-y-3">
                <h4 class="text-[10px] font-black text-gray-900 uppercase tracking-widest">Legal</h4>
                <div class="flex flex-col gap-2 text-xs font-bold text-gray-400">
                    <a href="/terms.php" class="hover:text-indigo-600">Terms of Service</a>
                    <a href="/privacy.php" class="hover:text-indigo-600">Privacy Policy</a>
                </div>
            </div>
        </div>
        
        <div class="text-[10px] font-black text-gray-300 uppercase tracking-[0.3em]">
            &copy; <?php echo date('Y'); ?> DecisionVault
        </div>
    </div>
</footer>
