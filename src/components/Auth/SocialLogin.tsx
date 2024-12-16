import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useGoogleLogin } from '@react-oauth/google';
import { useAuthStore } from '../../store/authStore';
import { useUserStore } from '../../store/userStore';
import toast from 'react-hot-toast';

const SocialLogin = () => {
  const navigate = useNavigate();
  const login = useAuthStore((state) => state.login);
  const { users, setUsers } = useUserStore();

  const handleGoogleSuccess = async (tokenResponse: any) => {
    try {
      const userInfoResponse = await fetch('https://www.googleapis.com/oauth2/v3/userinfo', {
        headers: { Authorization: `Bearer ${tokenResponse.access_token}` },
      });
      
      const userInfo = await userInfoResponse.json();
      
      // Créer un nouvel utilisateur avec les données Google
      const newUser = {
        id: userInfo.sub,
        username: userInfo.name,
        email: userInfo.email,
        level: 1,
        coins: 1000,
        premiumCoins: 0,
        status: 'active' as const,
        joinDate: new Date().toISOString()
      };

      // Vérifier si l'utilisateur existe déjà
      const existingUser = users.find(u => u.email === userInfo.email);
      
      if (!existingUser) {
        // Ajouter le nouvel utilisateur à la liste des utilisateurs
        setUsers([...users, newUser]);
      }

      // Connecter l'utilisateur
      login(newUser, tokenResponse.access_token);
      
      toast.success('Connexion réussie !');
      navigate('/dashboard');
    } catch (error) {
      console.error('Erreur lors de la connexion Google:', error);
      toast.error('Erreur de connexion');
    }
  };

  const loginWithGoogle = useGoogleLogin({
    onSuccess: handleGoogleSuccess,
    onError: () => {
      console.error('Erreur de connexion Google');
      toast.error('Erreur de connexion Google');
    },
    flow: 'implicit',
    popup: true
  });

  return (
    <div className="space-y-4">
      <div className="relative">
        <div className="absolute inset-0 flex items-center">
          <div className="w-full border-t border-gray-300"></div>
        </div>
        <div className="relative flex justify-center text-sm">
          <span className="px-2 bg-white text-gray-500">Ou continuer avec</span>
        </div>
      </div>

      <button
        onClick={() => loginWithGoogle()}
        className="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"
      >
        <img
          className="h-5 w-5 mr-2"
          src="https://www.svgrepo.com/show/475656/google-color.svg"
          alt="Google"
        />
        Google
      </button>
    </div>
  );
};

export default SocialLogin;