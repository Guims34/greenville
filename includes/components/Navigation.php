<?php
class Navigation {
    private static function getNotifications() {
        global $db;
        $notifications = ['missions' => 0];
        
        if (isLoggedIn()) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM user_missions WHERE user_id = ? AND completed = TRUE AND claimed = FALSE");
            $stmt->execute([$_SESSION['user_id']]);
            $notifications['missions'] = $stmt->fetch()['count'];
        }
        
        return $notifications;
    }

    public static function render() {
        $notifications = self::getNotifications();
        ?>
        <div class="flex items-center">
            <!-- Bouton hamburger pour mobile -->
            <button id="mobile-menu-button" class="md:hidden p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Menu principal -->
            <div id="mobile-menu" class="hidden md:flex md:items-center absolute md:relative top-16 md:top-0 left-0 right-0 md:left-auto md:right-auto bg-white md:bg-transparent shadow-lg md:shadow-none p-4 md:p-0 z-50">
                <div class="flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-4">
                    <?php
                    require_once __DIR__ . '/navigation/MainNav.php';
                    MainNav::render($notifications);
                    
                    require_once __DIR__ . '/navigation/CommerceNav.php';
                    CommerceNav::render();
                    
                    if (isAdmin()) {
                        require_once __DIR__ . '/navigation/AdminNav.php';
                        AdminNav::render();
                    }
                    ?>
                </div>
            </div>
        </div>

        <script>
            // Gestion du menu mobile
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });

            // Fermer le menu mobile lors du clic en dehors
            document.addEventListener('click', (e) => {
                if (!mobileMenu.contains(e.target) && !mobileMenuButton.contains(e.target)) {
                    mobileMenu.classList.add('hidden');
                }
            });

            // Gestion des sous-menus sur mobile
            const dropdownButtons = document.querySelectorAll('.dropdown-button');
            dropdownButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const menu = button.nextElementSibling;
                    menu.classList.toggle('hidden');
                });
            });
        </script>
		
		<script>
function toggleDropdown(button) {
    // Fermer tous les autres menus déroulants
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        if (menu !== button.nextElementSibling) {
            menu.classList.add('hidden');
            menu.previousElementSibling.setAttribute('aria-expanded', 'false');
            menu.previousElementSibling.querySelector('svg').classList.remove('rotate-180');
        }
    });

    // Basculer le menu actuel
    const menu = button.nextElementSibling;
    const isHidden = menu.classList.contains('hidden');
    
    menu.classList.toggle('hidden');
    button.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
    button.querySelector('svg').classList.toggle('rotate-180', isHidden);
}

// Fermer les menus lors du clic en dehors
document.addEventListener('click', (e) => {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.add('hidden');
            menu.previousElementSibling.setAttribute('aria-expanded', 'false');
            menu.previousElementSibling.querySelector('svg').classList.remove('rotate-180');
        });
    }
});

// Gestion du hover sur desktop
if (window.matchMedia('(min-width: 768px)').matches) {
    document.querySelectorAll('.dropdown').forEach(dropdown => {
        dropdown.addEventListener('mouseenter', () => {
            const button = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');
            menu.classList.remove('hidden');
            button.setAttribute('aria-expanded', 'true');
            button.querySelector('svg').classList.add('rotate-180');
        });

        dropdown.addEventListener('mouseleave', () => {
            const button = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');
            menu.classList.add('hidden');
            button.setAttribute('aria-expanded', 'false');
            button.querySelector('svg').classList.remove('rotate-180');
        });
    });
}
</script>

        <?php
    }
}
