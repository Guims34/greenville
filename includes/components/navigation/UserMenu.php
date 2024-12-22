<?php
class UserMenu {
    public static function render() {
        ob_start();
        ?>
        <div class="relative" x-data="{ open: false }">
            <button 
                @click="open = !open"
                class="flex items-center gap-2 text-gray-700 hover:text-gray-900"
            >
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            
            <div 
                x-show="open"
                @click.away="open = false"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50"
                style="display: none;"
            >
                <form action="index.php?page=logout" method="POST" class="block">
                    <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <span class="inline-block w-6">🚪</span>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
