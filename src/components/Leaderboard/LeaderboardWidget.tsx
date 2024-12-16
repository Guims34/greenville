import React from 'react';
import { Trophy, Medal, Award } from 'lucide-react';
import { motion } from 'framer-motion';
import { useLeaderboardStore } from '../../store/leaderboardStore';
import { useAuthStore } from '../../store/authStore';

const LeaderboardWidget = () => {
  const { getTopPlayers, getUserRank } = useLeaderboardStore();
  const { user } = useAuthStore();
  const topPlayers = getTopPlayers(10);
  const userRank = user ? getUserRank(user.id) : null;

  const getRankIcon = (rank: number) => {
    switch (rank) {
      case 1:
        return <Trophy className="w-6 h-6 text-yellow-500" />;
      case 2:
        return <Medal className="w-6 h-6 text-gray-400" />;
      case 3:
        return <Award className="w-6 h-6 text-amber-600" />;
      default:
        return <span className="w-6 h-6 flex items-center justify-center font-bold">{rank}</span>;
    }
  };

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-xl font-bold mb-6">Classement</h2>

      <div className="space-y-4">
        {topPlayers.map((entry, index) => (
          <motion.div
            key={entry.userId}
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ delay: index * 0.1 }}
            className={`flex items-center p-3 rounded-lg ${
              entry.userId === user?.id ? 'bg-emerald-50' : 'hover:bg-gray-50'
            }`}
          >
            <div className="flex items-center justify-center w-10">
              {getRankIcon(entry.rank)}
            </div>
            <div className="flex-1 ml-4">
              <p className="font-medium">{entry.username}</p>
              <p className="text-sm text-gray-500">Niveau {entry.level}</p>
            </div>
            <div className="text-right">
              <p className="font-bold">{entry.score}</p>
              <p className="text-sm text-gray-500">{entry.plantsGrown} plantes</p>
            </div>
          </motion.div>
        ))}
      </div>

      {user && userRank && userRank > 10 && (
        <div className="mt-6 p-4 border-t">
          <div className="flex items-center">
            <div className="w-10 flex justify-center">
              <span className="font-bold">#{userRank}</span>
            </div>
            <div className="flex-1 ml-4">
              <p className="font-medium">Vous</p>
              <p className="text-sm text-gray-500">Niveau {user.level}</p>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default LeaderboardWidget;