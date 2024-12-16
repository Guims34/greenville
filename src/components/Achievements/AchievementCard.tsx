import React from 'react';
import { motion } from 'framer-motion';
import { Lock, Trophy } from 'lucide-react';
import type { Achievement } from '../../store/achievementStore';

interface AchievementCardProps {
  achievement: Achievement;
}

const AchievementCard = ({ achievement }: AchievementCardProps) => {
  const progressPercentage = (achievement.progress / achievement.maxProgress) * 100;

  return (
    <motion.div
      whileHover={{ scale: 1.02 }}
      className={`bg-white rounded-lg shadow-md p-4 ${
        achievement.completed ? 'border-2 border-emerald-500' : ''
      }`}
    >
      <div className="flex items-start justify-between mb-2">
        <div className="flex items-center">
          <span className="text-2xl mr-2">{achievement.icon}</span>
          <h4 className="font-semibold">{achievement.title}</h4>
        </div>
        {achievement.completed ? (
          <Trophy className="w-5 h-5 text-emerald-500" />
        ) : achievement.category === 'secret' ? (
          <Lock className="w-5 h-5 text-gray-400" />
        ) : null}
      </div>

      <p className="text-sm text-gray-600 mb-3">{achievement.description}</p>

      <div className="space-y-2">
        <div className="w-full bg-gray-200 rounded-full h-2">
          <div
            className={`h-2 rounded-full transition-all ${
              achievement.completed ? 'bg-emerald-500' : 'bg-blue-500'
            }`}
            style={{ width: `${progressPercentage}%` }}
          />
        </div>

        <div className="flex justify-between items-center text-sm">
          <span className="text-gray-500">
            {achievement.progress}/{achievement.maxProgress}
          </span>
          <div className="text-right">
            {achievement.reward.coins && (
              <span className="text-yellow-500 mr-2">
                {achievement.reward.coins} 🪙
              </span>
            )}
            {achievement.reward.experience && (
              <span className="text-blue-500">{achievement.reward.experience} XP</span>
            )}
          </div>
        </div>

        {achievement.completed && achievement.completedAt && (
          <p className="text-xs text-gray-500 mt-2">
            Obtenu le {new Date(achievement.completedAt).toLocaleDateString()}
          </p>
        )}
      </div>
    </motion.div>
  );
};

export default AchievementCard;