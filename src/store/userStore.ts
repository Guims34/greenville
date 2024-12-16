import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { z } from 'zod';

const userSchema = z.object({
  id: z.string().min(1),
  username: z.string().min(3).max(50),
  email: z.string().email(),
  level: z.number().int().min(1).max(100),
  status: z.enum(['active', 'suspended']),
  joinDate: z.string()
});

export type User = z.infer<typeof userSchema>;

interface UserState {
  users: User[];
  setUsers: (users: User[]) => void;
  updateUser: (userId: string, userData: Partial<User>) => void;
  deleteUser: (userId: string) => void;
}

const sanitizeString = (str: string): string => {
  return str.replace(/[<>]/g, '');
};

const validateUser = (user: Partial<User>): boolean => {
  try {
    if ('username' in user) {
      user.username = sanitizeString(user.username);
    }
    if ('email' in user) {
      user.email = sanitizeString(user.email);
    }
    return true;
  } catch (error) {
    console.error('Validation error:', error);
    return false;
  }
};

const initialUsers = [
  {
    id: '1',
    username: 'JohnDoe',
    email: 'john@example.com',
    level: 15,
    status: 'active' as const,
    joinDate: new Date().toISOString()
  },
  {
    id: '2',
    username: 'JaneSmith',
    email: 'jane@example.com',
    level: 8,
    status: 'active' as const,
    joinDate: new Date().toISOString()
  },
  {
    id: '3',
    username: 'BobJohnson',
    email: 'bob@example.com',
    level: 5,
    status: 'suspended' as const,
    joinDate: new Date().toISOString()
  }
];

export const useUserStore = create<UserState>()(
  persist(
    (set) => ({
      users: initialUsers,
      setUsers: (users) => {
        const validUsers = users.filter(user => {
          try {
            userSchema.parse(user);
            return true;
          } catch (error) {
            console.error('Invalid user data:', error);
            return false;
          }
        });
        set({ users: validUsers });
      },
      updateUser: (userId, userData) => {
        if (!validateUser(userData)) {
          throw new Error('Invalid user data update');
        }
        set((state) => ({
          users: state.users.map((user) =>
            user.id === userId
              ? { ...user, ...userData }
              : user
          ),
        }));
      },
      deleteUser: (userId) => {
        if (!userId || typeof userId !== 'string') {
          throw new Error('Invalid user ID for deletion');
        }
        set((state) => ({
          users: state.users.filter((user) => user.id !== userId),
        }));
      },
    }),
    {
      name: 'user-storage',
      skipHydration: false,
    }
  )
);