import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../store/authStore';

const Register = () => {
  const navigate = useNavigate();
  const login = useAuthStore((state) => state.login);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const user = {
      id: '1',
      username: 'Nouvel Utilisateur',
      email: 'new@example.com',
      level: 1,
      coins: 0,
      premiumCoins: 0,
    };
    login(user, 'fake-token');
    navigate('/dashboard');
  };

  return (
    <div className="max-w-md mx-auto">
      <h2 className="text-2xl font-bold mb-6">Inscription</h2>
      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label htmlFor="username" className="block text-sm font-medium text-gray-700">
            Nom d'utilisateur
          </label>
          <input
            type="text"
            id="username"
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div>
          <label htmlFor="email" className="block text-sm font-medium text-gray-700">
            Email
          </label>
          <input
            type="email"
            id="email"
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div>
          <label htmlFor="password" className="block text-sm font-medium text-gray-700">
            Mot de passe
          </label>
          <input
            type="password"
            id="password"
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <button
          type="submit"
          className="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
        >
          S'inscrire
        </button>
      </form>
    </div>
  );
};

export default Register;