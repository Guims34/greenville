import React from 'react';
import { motion } from 'framer-motion';
import { useAchievementStore } from '../../store/achievementStore';

const AchievementProgress = () => {
  const { achievements } = useAchievementStore();
  
  const totalAchievements = achievements.length;
  const completedAchievements = achievements.filter(a => a.completed).length;
  const progressPercentage = (completedAchievements / totalAchievements) * 100;

  const categories = ['débutant', 'intermédiaire', 'expert', 'secret'] as const;
  const categoryProgress = categories.map(category => {
    const categoryAchievements = achievements.filter(a => a.category === category);
    const completed = categoryAchievements.filter(a => a.completed).length;
    return {
      category,
      total: categoryAchievements.length,
      completed,
      percentage: (completed / categoryAchievements.length) * 100
    };
  });

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-xl font-bold mb-6">Progression des Succès</h2>

      <div className="mb-6">
        <div className="flex justify-between mb-2">
          <span className="font-medium">Progression Globale</span>
          <span>{completedAchievements}/{totalAchievements}</span>
        </div>
        <div className="w-full bg-gray-200 rounded-full h-4">
          <motion.div
            initial={{ width: 0 }}
            animate={{ width: `${progressPercentage}%` }}
            className="bg-emerald-500 h-4 rounded-full"
            transition={{ duration: 1 }}
          />
        </div>
      </div>

      <div className="space-y-4">
        {categoryProgress.map(({ category, total, completed, percentage }) => (
          <div key={category}>
            <div className="flex justify-between mb-1">
              <span className="capitalize">{category}</span>
              <span>{completed}/{total}</span>
            </div>
            <div className="w-full bg-gray-200 rounded-full h-2">
              <motion.div
                initial={{ width: 0 }}
                animate={{ width: `${percentage}%` }}
                className={`h-2 rounded-full ${
                  category === 'débutant' ? 'bg-green-500' :
                  category === 'intermédiaire' ? 'bg-blue-500' :
                  category === 'expert' ? 'bg-purple-500' :
                  'bg-yellow-500'
                }`}
                transition={{ duration: 1 }}
              />
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default AchievementProgress;