import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';

export interface Achievement {
  id: string;
  title: string;
  description: string;
  icon: string;
  category: 'débutant' | 'intermédiaire' | 'expert' | 'secret';
  progress: number;
  maxProgress: number;
  completed: boolean;
  completedAt?: Date;
  reward: {
    coins?: number;
    experience?: number;
    badge?: string;
  };
}

interface AchievementState {
  achievements: Achievement[];
  unlockedAchievements: string[];
  updateProgress: (achievementId: string, progress: number) => void;
  completeAchievement: (achievementId: string) => void;
  initializeAchievements: () => void;
}

const defaultAchievements: Achievement[] = [
  {
    id: 'first-login',
    title: 'Premier Pas',
    description: 'Connectez-vous pour la première fois',
    icon: '🌱',
    category: 'débutant',
    progress: 0,
    maxProgress: 1,
    completed: false,
    reward: {
      coins: 100,
      experience: 50,
      badge: 'Nouveau Cultivateur'
    }
  },
  {
    id: 'reach-level-5',
    title: 'Apprenti Cultivateur',
    description: 'Atteignez le niveau 5',
    icon: '🌿',
    category: 'débutant',
    progress: 0,
    maxProgress: 5,
    completed: false,
    reward: {
      coins: 500,
      experience: 200,
      badge: 'Apprenti'
    }
  },
  {
    id: 'daily-streak-7',
    title: 'Cultivateur Assidu',
    description: 'Connectez-vous 7 jours de suite',
    icon: '📅',
    category: 'intermédiaire',
    progress: 0,
    maxProgress: 7,
    completed: false,
    reward: {
      coins: 1000,
      experience: 500,
      badge: 'Assidu'
    }
  }
];

export const useAchievementStore = create<AchievementState>()(
  persist(
    (set) => ({
      achievements: [],
      unlockedAchievements: [],
      
      updateProgress: (achievementId, progress) =>
        set((state) => ({
          achievements: state.achievements.map((achievement) =>
            achievement.id === achievementId
              ? {
                  ...achievement,
                  progress: Math.min(progress, achievement.maxProgress),
                  completed:
                    progress >= achievement.maxProgress && !achievement.completed,
                  completedAt:
                    progress >= achievement.maxProgress && !achievement.completed
                      ? new Date()
                      : achievement.completedAt,
                }
              : achievement
          ),
        })),

      completeAchievement: (achievementId) =>
        set((state) => ({
          achievements: state.achievements.map((achievement) =>
            achievement.id === achievementId
              ? {
                  ...achievement,
                  completed: true,
                  completedAt: new Date(),
                  progress: achievement.maxProgress,
                }
              : achievement
          ),
          unlockedAchievements: [...state.unlockedAchievements, achievementId],
        })),

      initializeAchievements: () =>
        set((state) => ({
          achievements:
            state.achievements.length === 0 ? defaultAchievements : state.achievements,
        })),
    }),
    {
      name: 'achievements-storage',
      storage: createJSONStorage(() => localStorage),
    }
  )
);