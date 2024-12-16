<?php if (!defined('INCLUDED_FROM_HEADER')) exit; ?>
<a href="index.php?page=dashboard" class="text-gray-600 hover:text-gray-800">Dashboard</a>
<a href="index.php?page=social" class="text-gray-600 hover:text-gray-800">Sociale</a>
<a href="index.php?page=messages" class="text-gray-600 hover:text-gray-800">Messages</a>
<a href="index.php?page=leaderboard" class="text-gray-600 hover:text-gray-800">Classement</a>
<a href="index.php?page=guilds" class="text-gray-600 hover:text-gray-800">Guildes</a>
<a href="index.php?page=progression" class="text-gray-600 hover:text-gray-800">Progression</a>
<a href="index.php?page=missions" class="text-gray-600 hover:text-gray-800">
    Missions
    <?php if ($notifications['missions'] > 0): ?>
        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-emerald-500 rounded-full ml-2">
            <?php echo $notifications['missions']; ?>
        </span>
    <?php endif; ?>
</a>