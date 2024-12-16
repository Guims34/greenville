import React from 'react';
import { TrendingUp, Coins, Award, Sprout } from 'lucide-react';
import { motion } from 'framer-motion';
import { useGameStore } from '../../store/gameStore';

function PlayerStats() {
  const { level, experience, coins, plants } = useGameStore();

  return (
    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="bg-white p-4 rounded-lg shadow-md"
      >
        <div className="flex items-center justify-between">
          <div>
            <p className="text-gray-500">Niveau</p>
            <p className="text-2xl font-bold">{level}</p>
          </div>
          <Award className="w-8 h-8 text-emerald-500" />
        </div>
        <div className="mt-2">
          <div className="w-full bg-gray-200 rounded-full h-2">
            <div 
              className="bg-emerald-500 h-2 rounded-full" 
              style={{ width: `${(experience % 1000) / 10}%` }}
            />
          </div>
          <p className="text-sm text-gray-500 mt-1">{experience}/1000 XP</p>
        </div>
      </motion.div>

      <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.1 }}
        className="bg-white p-4 rounded-lg shadow-md"
      >
        <div className="flex items-center justify-between">
          <div>
            <p className="text-gray-500">Pièces</p>
            <p className="text-2xl font-bold">{coins}</p>
          </div>
          <Coins className="w-8 h-8 text-yellow-500" />
        </div>
      </motion.div>

      <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.2 }}
        className="bg-white p-4 rounded-lg shadow-md"
      >
        <div className="flex items-center justify-between">
          <div>
            <p className="text-gray-500">Plantes Actives</p>
            <p className="text-2xl font-bold">{plants.length}</p>
          </div>
          <Sprout className="w-8 h-8 text-emerald-500" />
        </div>
      </motion.div>

      <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.3 }}
        className="bg-white p-4 rounded-lg shadow-md"
      >
        <div className="flex items-center justify-between">
          <div>
            <p className="text-gray-500">Croissance</p>
            <p className="text-2xl font-bold">+24%</p>
          </div>
          <TrendingUp className="w-8 h-8 text-emerald-500" />
        </div>
      </motion.div>
    </div>
  );
}

export default PlayerStats;