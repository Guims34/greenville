import React from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { LogIn } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../store/authStore';
import SocialLogin from './SocialLogin';

const loginSchema = z.object({
  email: z.string().email('Email invalide'),
  password: z.string().min(6, 'Le mot de passe doit contenir au moins 6 caractères'),
});

type LoginFormData = z.infer<typeof loginSchema>;

const ADMIN_CREDENTIALS = {
  email: 'admin@greenville.com',
  password: 'admin123456'
};

const LoginForm = () => {
  const navigate = useNavigate();
  const login = useAuthStore((state) => state.login);
  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<LoginFormData>({
    resolver: zodResolver(loginSchema),
  });

  const onSubmit = async (data: LoginFormData) => {
    try {
      // Vérification des identifiants admin
      if (data.email === ADMIN_CREDENTIALS.email) {
        if (data.password !== ADMIN_CREDENTIALS.password) {
          setError('password', { message: 'Mot de passe incorrect' });
          return;
        }
        
        login({
          id: 'admin',
          username: 'Administrateur',
          email: ADMIN_CREDENTIALS.email,
          level: 99,
          coins: 999999,
          premiumCoins: 999999,
        }, 'admin-token');
        
        navigate('/admin');
        return;
      }

      // Simulation d'une API pour les utilisateurs normaux
      const response = await new Promise((resolve) => {
        setTimeout(() => {
          resolve({
            user: {
              id: '1',
              username: 'Utilisateur Test',
              email: data.email,
              level: 1,
              coins: 1000,
              premiumCoins: 0,
            },
            token: 'fake-jwt-token',
          });
        }, 1000);
      });

      login(response.user, response.token);
      navigate('/dashboard');
    } catch (error) {
      console.error('Erreur de connexion:', error);
    }
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
        <div className="flex items-center justify-center mb-8">
          <LogIn className="w-8 h-8 text-emerald-500 mr-2" />
          <h2 className="text-2xl font-bold">Connexion</h2>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
          <div>
            <label htmlFor="email" className="block text-sm font-medium text-gray-700">
              Email
            </label>
            <input
              {...register('email')}
              type="email"
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
            />
            {errors.email && (
              <p className="mt-1 text-sm text-red-600">{errors.email.message}</p>
            )}
          </div>

          <div>
            <label htmlFor="password" className="block text-sm font-medium text-gray-700">
              Mot de passe
            </label>
            <input
              {...register('password')}
              type="password"
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
            />
            {errors.password && (
              <p className="mt-1 text-sm text-red-600">{errors.password.message}</p>
            )}
          </div>

          <button
            type="submit"
            disabled={isSubmitting}
            className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50 transition-colors"
          >
            {isSubmitting ? 'Connexion...' : 'Se connecter'}
          </button>
        </form>

        <div className="mt-6">
          <SocialLogin />
        </div>
      </div>
    </div>
  );
};

export default LoginForm;