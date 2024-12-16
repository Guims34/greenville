import React from 'react';
import { Plus } from 'lucide-react';
import { motion } from 'framer-motion';
import PlantCard from './PlantCard';
import { useGameStore } from '../../store/gameStore';

function GrowSpace() {
  const plants = useGameStore((state) => state.plants);
  const level = useGameStore((state) => state.level);
  const maxPlants = Math.min(3 + Math.floor(level / 2), 12); // Max 12 plantes

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-2xl font-bold mb-4">Espace de Culture</h2>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {plants.map((plant) => (
          <PlantCard key={plant.id} plant={plant} />
        ))}
        {plants.length < maxPlants && (
          <motion.button
            whileHover={{ scale: 1.02 }}
            whileTap={{ scale: 0.98 }}
            className="h-64 flex flex-col items-center justify-center border-2 border-dashed border-emerald-300 rounded-lg hover:border-emerald-500 transition-colors"
          >
            <Plus className="h-12 w-12 text-emerald-500 mb-2" />
            <span className="text-emerald-600 font-medium">Nouvelle Plante</span>
          </motion.button>
        )}
      </div>
    </div>
  );
}

export default GrowSpace;