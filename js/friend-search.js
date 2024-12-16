function searchUsers(query) {
    if (query.length < 2) {
        document.getElementById('searchResults').innerHTML = 
            '<p class="text-gray-500 text-center py-4">Entrez au moins 2 caractères</p>';
        return;
    }

    fetch(`ajax/search_users.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const results = document.getElementById('searchResults');
            
            if (!data.success) {
                results.innerHTML = `<p class="text-red-500 text-center py-4">${data.error}</p>`;
                return;
            }

            if (data.users.length === 0) {
                results.innerHTML = '<p class="text-gray-500 text-center py-4">Aucun utilisateur trouvé</p>';
                return;
            }

            results.innerHTML = data.users.map(user => `
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                            <span>👤</span>
                        </div>
                        <div>
                            <h4 class="font-medium">${escapeHtml(user.username)}</h4>
                            <p class="text-sm text-gray-500">Niveau ${user.level}</p>
                        </div>
                    </div>
                    ${getFriendshipButton(user)}
                </div>
            `).join('');
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('searchResults').innerHTML = 
                '<p class="text-red-500 text-center py-4">Une erreur est survenue</p>';
        });
}

function getFriendshipButton(user) {
    switch(user.friendship_status) {
        case 'none':
            return `<button 
                onclick="addFriend(${user.id})"
                class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600"
            >
                Ajouter
            </button>`;
        
        case 'sent':
            return `<span class="text-gray-500">Demande envoyée</span>`;
            
        case 'received':
            return `<div class="flex gap-2">
                <button 
                    onclick="acceptFriend(${user.id})"
                    class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600"
                >
                    Accepter
                </button>
                <button 
                    onclick="rejectFriend(${user.id})"
                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                >
                    Refuser
                </button>
            </div>`;
            
        case 'friends':
            return `<span class="text-emerald-500">Déjà amis</span>`;
            
        default:
            return '';
    }
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}