<?php if (!defined('INCLUDED_FROM_HEADER')) exit; ?>
<div class="relative dropdown">
    <button class="text-red-600 hover:text-red-800 px-4 py-2 rounded-lg">
        Administration ▼
    </button>
    <div class="dropdown-menu bg-white rounded-lg shadow-lg py-2">
        <a href="index.php?page=admin" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
            Gestion Utilisateurs
        </a>
        <a href="index.php?page=admin_plants" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
            Gestion Plantes
        </a>
        <a href="index.php?page=admin_missions" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
            Gestion des Missions
        </a>
        <div class="border-t border-gray-100 my-2"></div>
        <a href="index.php?page=mod_logs" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
            Logs de Modération
        </a>
        <a href="index.php?page=reports" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
            Signalements
        </a>
        <a href="index.php?page=system_logs" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
            Logs Système
        </a>
    </div>
</div>