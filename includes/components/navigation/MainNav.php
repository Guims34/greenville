<?php
class MainNav {
    public static function render($notifications) {
        ?>
        <div class="hidden md:flex md:items-center md:gap-4">
            <a href="index.php?page=dashboard" class="flex items-center gap-1 text-gray-600 hover:text-gray-800">
                <span class="inline-block w-5">📊</span>
                Dashboard
            </a>
            <a href="index.php?page=social" class="flex items-center gap-1 text-gray-600 hover:text-gray-800">
                <span class="inline-block w-5">👥</span>
                Sociale
            </a>
            <a href="index.php?page=messages" class="flex items-center gap-1 text-gray-600 hover:text-gray-800">
                <span class="inline-block w-5">💬</span>
                Messages
            </a>
            <a href="index.php?page=leaderboard" class="flex items-center gap-1 text-gray-600 hover:text-gray-800">
                <span class="inline-block w-5">🏆</span>
                Classement
            </a>
            <a href="index.php?page=guilds" class="flex items-center gap-1 text-gray-600 hover:text-gray-800">
                <span class="inline-block w-5">⚔️</span>
                Guildes
            </a>
            <a href="index.php?page=progression" class="flex items-center gap-1 text-gray-600 hover:text-gray-800">
                <span class="inline-block w-5">📈</span>
                Progression
            </a>
            <a href="index.php?page=missions" class="flex items-center gap-1 text-gray-600 hover:text-gray-800">
                <span class="inline-block w-5">🎯</span>
                Missions
                <?php if ($notifications['missions'] > 0): ?>
                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-emerald-500 rounded-full ml-2">
                        <?php echo $notifications['missions']; ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Version mobile -->
        <div class="md:hidden">
            <div x-data="{ open: false }" class="relative">
                <button 
                    @click="open = !open"
                    class="flex items-center gap-1 text-gray-600 hover:text-gray-800 px-4 py-2 rounded-lg"
                    type="button"
                >
                    <span>Menu</span>
                    <svg 
                        class="w-4 h-4 transition-transform duration-200"
                        :class="{ 'rotate-180': open }"
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div 
                    x-show="open"
                    @click.away="open = false"
                    class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95"
                    style="display: none;"
                >
                    <a href="index.php?page=dashboard" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                        <span class="inline-block w-6">📊</span>
                        Dashboard
                    </a>
                    <a href="index.php?page=social" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                        <span class="inline-block w-6">👥</span>
                        Sociale
                    </a>
                    <a href="index.php?page=messages" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                        <span class="inline-block w-6">💬</span>
                        Messages
                    </a>
                    <a href="index.php?page=leaderboard" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                        <span class="inline-block w-6">🏆</span>
                        Classement
                    </a>
                    <a href="index.php?page=guilds" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                        <span class="inline-block w-6">⚔️</span>
                        Guildes
                    </a>
                    <a href="index.php?page=progression" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                        <span class="inline-block w-6">📈</span>
                        Progression
                    </a>
                    <a href="index.php?page=missions" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                        <span class="inline-block w-6">🎯</span>
                        Missions
                        <?php if ($notifications['missions'] > 0): ?>
                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-emerald-500 rounded-full ml-2">
                                <?php echo $notifications['missions']; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
}
