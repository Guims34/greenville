import React, { useState } from 'react';
import { Trash2 } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../store/authStore';

function DeleteAccount() {
  const [isConfirming, setIsConfirming] = useState(false);
  const navigate = useNavigate();
  const logout = useAuthStore((state) => state.logout);

  const handleDeleteAccount = async () => {
    try {
      // Simulation de l'appel API pour supprimer le compte
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      // Déconnexion et redirection
      logout();
      navigate('/');
      
      // Notification de succès
      alert('Votre compte a été supprimé avec succès.');
    } catch (error) {
      console.error('Erreur lors de la suppression du compte:', error);
      alert('Une erreur est survenue lors de la suppression du compte.');
    }
  };

  return (
    <div className="mt-8 border-t pt-6">
      <h3 className="text-lg font-semibold text-red-600 mb-4">Supprimer le compte</h3>
      
      {!isConfirming ? (
        <button
          onClick={() => setIsConfirming(true)}
          className="flex items-center px-4 py-2 text-red-600 border border-red-600 rounded hover:bg-red-50"
        >
          <Trash2 className="w-4 h-4 mr-2" />
          Supprimer mon compte
        </button>
      ) : (
        <div className="space-y-4">
          <p className="text-gray-600">
            Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.
          </p>
          <div className="flex space-x-4">
            <button
              onClick={handleDeleteAccount}
              className="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
            >
              Confirmer la suppression
            </button>
            <button
              onClick={() => setIsConfirming(false)}
              className="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50"
            >
              Annuler
            </button>
          </div>
        </div>
      )}
    </div>
  );
}

export default DeleteAccount;