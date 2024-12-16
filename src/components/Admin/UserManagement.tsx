import React, { useState } from 'react';
import { User, Edit, Trash2, Search } from 'lucide-react';
import EditUserModal from './EditUserModal';
import toast from 'react-hot-toast';
import { useUserStore } from '../../store/userStore';
import type { User as UserType } from '../../store/userStore';
import { useAuthStore } from '../../store/authStore';
import { useNavigate } from 'react-router-dom';

const UserManagement = () => {
  const navigate = useNavigate();
  const { user: currentUser } = useAuthStore();
  const [searchTerm, setSearchTerm] = useState('');
  const [editingUser, setEditingUser] = useState<UserType | null>(null);
  const { users, updateUser, deleteUser } = useUserStore();

  // Vérification des permissions
  React.useEffect(() => {
    if (!currentUser || currentUser.email !== 'admin@greenville.com') {
      navigate('/dashboard');
      toast.error('Accès non autorisé');
    }
  }, [currentUser, navigate]);

  const handleEdit = (user: UserType) => {
    // Empêcher la modification du compte admin par d'autres utilisateurs
    if (user.email === 'admin@greenville.com' && currentUser?.email !== 'admin@greenville.com') {
      toast.error('Vous n\'avez pas les permissions nécessaires');
      return;
    }
    setEditingUser(user);
  };

  const handleDelete = (userId: string) => {
    const userToDelete = users.find(u => u.id === userId);
    
    // Empêcher la suppression du compte admin
    if (userToDelete?.email === 'admin@greenville.com') {
      toast.error('Le compte administrateur ne peut pas être supprimé');
      return;
    }

    if (window.confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
      try {
        deleteUser(userId);
        toast.success('Utilisateur supprimé avec succès');
      } catch (error) {
        toast.error('Erreur lors de la suppression');
        console.error('Delete error:', error);
      }
    }
  };

  const handleSaveEdit = (data: Partial<UserType>) => {
    if (!editingUser) return;

    try {
      // Validation supplémentaire des données
      if (data.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
        throw new Error('Email invalide');
      }
      if (data.username && (data.username.length < 3 || data.username.length > 50)) {
        throw new Error('Le nom d\'utilisateur doit contenir entre 3 et 50 caractères');
      }
      if (data.level && (data.level < 1 || data.level > 100)) {
        throw new Error('Le niveau doit être compris entre 1 et 100');
      }

      updateUser(editingUser.id, data);
      toast.success('Utilisateur modifié avec succès');
      setEditingUser(null);
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Erreur lors de la modification');
      console.error('Update error:', error);
    }
  };

  const sanitizeSearchTerm = (term: string): string => {
    return term.replace(/[<>]/g, '').slice(0, 50);
  };

  const filteredUsers = users.filter(user => {
    const search = sanitizeSearchTerm(searchTerm).toLowerCase();
    return user.username.toLowerCase().includes(search) ||
           user.email.toLowerCase().includes(search);
  });

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-xl font-semibold mb-6">Gestion des Utilisateurs</h2>
      
      <div className="mb-6 relative">
        <input
          type="text"
          placeholder="Rechercher un utilisateur..."
          className="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          maxLength={50}
        />
        <Search className="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
      </div>

      <div className="overflow-x-auto">
        <table className="min-w-full">
          <thead>
            <tr className="bg-gray-50">
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Utilisateur
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Email
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Niveau
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Statut
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Date d'inscription
              </th>
              <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {filteredUsers.map((user) => (
              <tr key={user.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="flex items-center">
                    <div className="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center">
                      <User className="h-5 w-5 text-emerald-600" />
                    </div>
                    <div className="ml-4">
                      <div className="text-sm font-medium text-gray-900">
                        {user.username}
                      </div>
                    </div>
                  </div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="text-sm text-gray-900">{user.email}</div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="text-sm text-gray-900">Niveau {user.level}</div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                    user.status === 'active' 
                      ? 'bg-green-100 text-green-800' 
                      : 'bg-red-100 text-red-800'
                  }`}>
                    {user.status === 'active' ? 'Actif' : 'Suspendu'}
                  </span>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {new Date(user.joinDate).toLocaleDateString()}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button 
                    onClick={() => handleEdit(user)}
                    className="text-emerald-600 hover:text-emerald-900 mr-3"
                  >
                    <Edit className="h-5 w-5" />
                  </button>
                  <button 
                    onClick={() => handleDelete(user.id)}
                    className="text-red-600 hover:text-red-900"
                  >
                    <Trash2 className="h-5 w-5" />
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {editingUser && (
        <EditUserModal
          user={editingUser}
          onClose={() => setEditingUser(null)}
          onSave={handleSaveEdit}
        />
      )}
    </div>
  );
};

export default UserManagement;