import React from 'react';
import { X } from 'lucide-react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';

const editUserSchema = z.object({
  username: z.string()
    .min(3, 'Le nom d\'utilisateur doit contenir au moins 3 caractères')
    .max(50, 'Le nom d\'utilisateur ne doit pas dépasser 50 caractères')
    .regex(/^[a-zA-Z0-9_-]+$/, 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres, tirets et underscores'),
  email: z.string()
    .email('Email invalide')
    .max(100, 'L\'email ne doit pas dépasser 100 caractères'),
  level: z.number()
    .int('Le niveau doit être un nombre entier')
    .min(1, 'Le niveau doit être supérieur à 0')
    .max(100, 'Le niveau ne peut pas dépasser 100'),
  status: z.enum(['active', 'suspended'])
});

type EditUserFormData = z.infer<typeof editUserSchema>;

interface EditUserModalProps {
  user: {
    id: string;
    username: string;
    email: string;
    level: number;
    status: 'active' | 'suspended';
  };
  onClose: () => void;
  onSave: (data: EditUserFormData) => void;
}

const EditUserModal = ({ user, onClose, onSave }: EditUserModalProps) => {
  const { register, handleSubmit, formState: { errors } } = useForm<EditUserFormData>({
    resolver: zodResolver(editUserSchema),
    defaultValues: {
      username: user.username,
      email: user.email,
      level: user.level,
      status: user.status
    }
  });

  const onSubmit = (data: EditUserFormData) => {
    // Sanitization supplémentaire
    const sanitizedData = {
      ...data,
      username: data.username.trim(),
      email: data.email.trim().toLowerCase()
    };
    onSave(sanitizedData);
    onClose();
  };

  // Gestionnaire d'échap pour fermer la modal
  React.useEffect(() => {
    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        onClose();
      }
    };
    window.addEventListener('keydown', handleEscape);
    return () => window.removeEventListener('keydown', handleEscape);
  }, [onClose]);

  return (
    <div 
      className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      onClick={(e) => {
        if (e.target === e.currentTarget) onClose();
      }}
    >
      <div className="bg-white rounded-lg p-6 w-full max-w-md" onClick={e => e.stopPropagation()}>
        <div className="flex justify-between items-center mb-4">
          <h3 className="text-lg font-semibold">Modifier l'utilisateur</h3>
          <button 
            onClick={onClose}
            className="text-gray-500 hover:text-gray-700"
            aria-label="Fermer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">
              Nom d'utilisateur
            </label>
            <input
              {...register('username')}
              type="text"
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              maxLength={50}
            />
            {errors.username && (
              <p className="mt-1 text-sm text-red-600">{errors.username.message}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">
              Email
            </label>
            <input
              {...register('email')}
              type="email"
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              maxLength={100}
            />
            {errors.email && (
              <p className="mt-1 text-sm text-red-600">{errors.email.message}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">
              Niveau
            </label>
            <input
              {...register('level', { valueAsNumber: true })}
              type="number"
              min="1"
              max="100"
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
            />
            {errors.level && (
              <p className="mt-1 text-sm text-red-600">{errors.level.message}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">
              Statut
            </label>
            <select
              {...register('status')}
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
            >
              <option value="active">Actif</option>
              <option value="suspended">Suspendu</option>
            </select>
            {errors.status && (
              <p className="mt-1 text-sm text-red-600">{errors.status.message}</p>
            )}
          </div>

          <div className="flex justify-end gap-4 mt-6">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
              Annuler
            </button>
            <button
              type="submit"
              className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700"
            >
              Sauvegarder
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default EditUserModal;