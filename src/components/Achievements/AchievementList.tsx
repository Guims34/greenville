import React from 'react';
import { motion } from 'framer-motion';
import { useAchievementStore } from '../../store/achievementStore';
import AchievementCard from './AchievementCard';

const AchievementList = () => {
  const { achievements } = useAchievementStore();

  const categories = ['débutant', 'intermédiaire', 'expert', 'secret'] as const;

  return (
    <div className="space-y-8">
      {categories.map((category, index) => {
        const categoryAchievements = achievements.filter(
          (a) => a.category === category
        );

        if (categoryAchievements.length === 0) return null;

        return (
          <motion.div
            key={category}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: index * 0.1 }}
          >
            <h3 className="text-xl font-bold mb-4 capitalize">{category}</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {categoryAchievements.map((achievement) => (
                <AchievementCard
                  key={achievement.id}
                  achievement={achievement}
                />
              ))}
            </div>
          </motion.div>
        );
      })}
    </div>
  );
};

export default AchievementList;