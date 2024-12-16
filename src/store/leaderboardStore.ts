import { create } from 'zustand';
import { persist } from 'zustand/middleware';

interface LeaderboardEntry {
  userId: string;
  username: string;
  score: number;
  rank: number;
  level: number;
  plantsGrown: number;
}

interface LeaderboardState {
  entries: LeaderboardEntry[];
  updateLeaderboard: (entry: Omit<LeaderboardEntry, 'rank'>) => void;
  getTopPlayers: (limit?: number) => LeaderboardEntry[];
  getUserRank: (userId: string) => number | null;
}

export const useLeaderboardStore = create<LeaderboardState>()(
  persist(
    (set, get) => ({
      entries: [],
      updateLeaderboard: (entry) => {
        set((state) => {
          const newEntries = state.entries
            .filter((e) => e.userId !== entry.userId)
            .concat({
              ...entry,
              rank: 0
            })
            .sort((a, b) => b.score - a.score)
            .map((e, index) => ({
              ...e,
              rank: index + 1
            }));

          return { entries: newEntries };
        });
      },
      getTopPlayers: (limit = 10) => {
        return get().entries.slice(0, limit);
      },
      getUserRank: (userId) => {
        const entry = get().entries.find((e) => e.userId === userId);
        return entry ? entry.rank : null;
      }
    }),
    {
      name: 'leaderboard-storage'
    }
  )
);