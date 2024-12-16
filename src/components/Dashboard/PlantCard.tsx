import React from 'react';
import { Timer, Droplets, Thermometer } from 'lucide-react';
import { motion } from 'framer-motion';

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

interface PlantCardProps {
  plant: Plant;
}

function PlantCard({ plant }: PlantCardProps) {
  const timeLeft = Math.floor(Math.random() * 7 + 1); // Simulation

  return (
    <motion.div 
      initial={{ opacity: 0, scale: 0.9 }}
      animate={{ opacity: 1, scale: 1 }}
      className="bg-white rounded-lg shadow-md overflow-hidden"
    >
      <div className="h-48 bg-emerald-100 relative">
        <img 
          src={`https://images.unsplash.com/photo-1536819114556-1c5b57e64b92?w=800`} 
          alt={plant.name} 
          className="w-full h-full object-cover"
        />
        <div className="absolute top-2 right-2 bg-emerald-500 text-white px-2 py-1 rounded-full text-sm">
          Stage {plant.stage}/4
        </div>
      </div>
      
      <div className="p-4">
        <h3 className="text-lg font-semibold mb-2">{plant.name}</h3>
        
        <div className="space-y-2">
          <div className="flex items-center text-gray-600">
            <Timer className="w-4 h-4 mr-2" />
            <span>{timeLeft} jours restants</span>
          </div>
          
          <div className="flex items-center text-gray-600">
            <Droplets className="w-4 h-4 mr-2" />
            <span>Humidité: {plant.humidity}%</span>
          </div>
          
          <div className="flex items-center text-gray-600">
            <Thermometer className="w-4 h-4 mr-2" />
            <span>Température: {plant.temperature}°C</span>
          </div>
        </div>
        
        <div className="mt-4 flex space-x-2">
          <motion.button 
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
            className="flex-1 bg-emerald-500 text-white py-2 rounded hover:bg-emerald-600 transition"
          >
            Arroser
          </motion.button>
          <motion.button 
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
            className="flex-1 bg-emerald-100 text-emerald-800 py-2 rounded hover:bg-emerald-200 transition"
          >
            Détails
          </motion.button>
        </div>
      </div>
    </motion.div>
  );
}

export default PlantCard;