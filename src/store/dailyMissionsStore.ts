import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export interface Mission {
  id: string;
  title: string;
  description: string;
  reward: {
    coins: number;
    experience: number;
  };
  progress: number;
  target: number;
  completed: boolean;
  expiresAt: Date;
}

interface DailyMissionsState {
  missions: Mission[];
  generateDailyMissions: () => void;
  updateProgress: (missionId: string, progress: number) => void;
  claimReward: (missionId: string) => boolean;
}

const generateMission = (): Mission => {
  const missions = [
    {
      title: 'Cultivateur Matinal',
      description: 'Connectez-vous avant 10h',
      reward: { coins: 100, experience: 50 },
      target: 1
    },
    {
      title: 'Jardinier Assidu',
      description: 'Arrosez 5 plantes',
      reward: { coins: 200, experience: 100 },
      target: 5
    },
    {
      title: 'Maître Cultivateur',
      description: 'Atteignez le niveau 2 avec une plante',
      reward: { coins: 300, experience: 150 },
      target: 1
    }
  ];

  const mission = missions[Math.floor(Math.random() * missions.length)];
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  tomorrow.setHours(0, 0, 0, 0);

  return {
    id: crypto.randomUUID(),
    ...mission,
    progress: 0,
    completed: false,
    expiresAt: tomorrow
  };
};

export const useDailyMissionsStore = create<DailyMissionsState>()(
  persist(
    (set, get) => ({
      missions: [],
      generateDailyMissions: () => {
        const now = new Date();
        const currentMissions = get().missions;
        
        // Nettoyer les missions expirées
        const validMissions = currentMissions.filter(
          (mission) => new Date(mission.expiresAt) > now
        );

        // Générer de nouvelles missions si nécessaire
        if (validMissions.length < 3) {
          const newMissions = Array(3 - validMissions.length)
            .fill(null)
            .map(() => generateMission());

          set({ missions: [...validMissions, ...newMissions] });
        }
      },
      updateProgress: (missionId, progress) => {
        set((state) => ({
          missions: state.missions.map((mission) =>
            mission.id === missionId
              ? {
                  ...mission,
                  progress: Math.min(progress, mission.target),
                  completed: progress >= mission.target
                }
              : mission
          )
        }));
      },
      claimReward: (missionId) => {
        const mission = get().missions.find((m) => m.id === missionId);
        if (!mission || !mission.completed) return false;

        set((state) => ({
          missions: state.missions.filter((m) => m.id !== missionId)
        }));

        return true;
      }
    }),
    {
      name: 'daily-missions-storage'
    }
  )
);