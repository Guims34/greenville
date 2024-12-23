<?php
class AdminNav {
    public static function render() {
        ?>
        <div x-data="{ open: false }" class="relative">
            <button 
                @click="open = !open"
                class="flex items-center gap-1 text-red-600 hover:text-red-800 px-4 py-2 rounded-lg"
                type="button"
            >
                <span>Administration</span>
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
                class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 z-50 md:right-0 md:left-auto"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                style="display: none;"
            >
                <a href="index.php?page=admin" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                    <span class="inline-block w-6">👥</span>
                    Gestion Utilisateurs
                </a>
                <a href="index.php?page=admin_plants" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                    <span class="inline-block w-6">🌱</span>
                    Gestion Plantes
                </a>
                <a href="index.php?page=admin_missions" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                    <span class="inline-block w-6">🎯</span>
                    Gestion des Missions
                </a>
                <div class="border-t border-gray-100 my-2"></div>
                <a href="index.php?page=mod_logs" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                    <span class="inline-block w-6">📋</span>
                    Logs de Modération
                </a>
                <a href="index.php?page=reports" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                    <span class="inline-block w-6">⚠️</span>
                    Signalements
                </a>
                <a href="index.php?page=system_logs" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                    <span class="inline-block w-6">🔧</span>
                    Logs Système
                </a>
            </div>
        </div>
        <?php
    }
}
