import { create } from 'zustand';
import { persist } from 'zustand/middleware';

interface Plant {
  id: string;
  name: string;
  strain: string;
  stage: number;
  health: number;
  humidity: number;
  temperature: number;
  waterLevel: number;
  growthTime: number;
  lastWatered: Date;
}

interface GameState {
  level: number;
  experience: number;
  coins: number;
  premiumCoins: number;
  plants: Plant[];
  inventory: {
    seeds: { [key: string]: number };
    fertilizers: { [key: string]: number };
    pots: { [key: string]: number };
  };
  addPlant: (plant: Plant) => void;
  waterPlant: (plantId: string) => void;
  harvestPlant: (plantId: string) => void;
  addExperience: (amount: number) => void;
  addCoins: (amount: number) => void;
}

export const useGameStore = create<GameState>()(
  persist(
    (set) => ({
      level: 1,
      experience: 0,
      coins: 1000,
      premiumCoins: 0,
      plants: [],
      inventory: {
        seeds: { 'basic-seed': 5 },
        fertilizers: { 'basic-fertilizer': 3 },
        pots: { 'small-pot': 2 },
      },
      addPlant: (plant) =>
        set((state) => ({ plants: [...state.plants, plant] })),
      waterPlant: (plantId) =>
        set((state) => ({
          plants: state.plants.map((p) =>
            p.id === plantId
              ? { ...p, waterLevel: 100, lastWatered: new Date() }
              : p
          ),
        })),
      harvestPlant: (plantId) =>
        set((state) => ({
          plants: state.plants.filter((p) => p.id !== plantId),
          coins: state.coins + 500, // Récompense de base pour la récolte
          experience: state.experience + 100,
        })),
      addExperience: (amount) =>
        set((state) => {
          const newExp = state.experience + amount;
          const expNeeded = state.level * 1000;
          if (newExp >= expNeeded) {
            return {
              experience: newExp - expNeeded,
              level: state.level + 1,
              coins: state.coins + 1000, // Bonus de niveau
            };
          }
          return { experience: newExp };
        }),
      addCoins: (amount) =>
        set((state) => ({ coins: state.coins + amount })),
    }),
    {
      name: 'game-storage',
    }
  )
);