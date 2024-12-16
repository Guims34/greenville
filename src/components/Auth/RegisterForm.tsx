import React from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { UserPlus } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../store/authStore';
import SocialLogin from './SocialLogin';

const registerSchema = z.object({
  username: z.string().min(3, 'Le nom d\'utilisateur doit contenir au moins 3 caractères'),
  email: z.string().email('Email invalide'),
  password: z.string().min(6, 'Le mot de passe doit contenir au moins 6 caractères'),
  confirmPassword: z.string(),
}).refine((data) => data.password === data.confirmPassword, {
  message: "Les mots de passe ne correspondent pas",
  path: ["confirmPassword"],
});

type RegisterFormData = z.infer<typeof registerSchema>;

const RegisterForm = () => {
  const navigate = useNavigate();
  const login = useAuthStore((state) => state.login);
  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<RegisterFormData>({
    resolver: zodResolver(registerSchema),
  });

  const onSubmit = async (data: RegisterFormData) => {
    try {
      // Simulation d'une API
      const response = await new Promise((resolve) => {
        setTimeout(() => {
          resolve({
            user: {
              id: '1',
              username: data.username,
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
      console.error('Erreur d\'inscription:', error);
    }
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
        <div className="flex items-center justify-center mb-8">
          <UserPlus className="w-8 h-8 text-emerald-500 mr-2" />
          <h2 className="text-2xl font-bold">Inscription</h2>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
          <div>
            <label htmlFor="username" className="block text-sm font-medium text-gray-700">
              Nom d'utilisateur
            </label>
            <input
              {...register('username')}
              type="text"
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
            />
            {errors.username && (
              <p className="mt-1 text-sm text-red-600">{errors.username.message}</p>
            )}
          </div>

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

          <div>
            <label htmlFor="confirmPassword" className="block text-sm font-medium text-gray-700">
              Confirmer le mot de passe
            </label>
            <input
              {...register('confirmPassword')}
              type="password"
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
            />
            {errors.confirmPassword && (
              <p className="mt-1 text-sm text-red-600">{errors.confirmPassword.message}</p>
            )}
          </div>

          <button
            type="submit"
            disabled={isSubmitting}
            className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50 transition-colors"
          >
            {isSubmitting ? 'Inscription...' : 'S\'inscrire'}
          </button>
        </form>

        <div className="mt-6">
          <SocialLogin />
        </div>
      </div>
    </div>
  );
};

export default RegisterForm;