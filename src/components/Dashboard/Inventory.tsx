import React from 'react';
import { Package, Sprout, Droplets, FlowerIcon } from 'lucide-react';
import { motion } from 'framer-motion';
import { useGameStore } from '../../store/gameStore';

function Inventory() {
  const inventory = useGameStore((state) => state.inventory);

  const categories = [
    { title: 'Graines', icon: Sprout, items: inventory.seeds },
    { title: 'Engrais', icon: Droplets, items: inventory.fertilizers },
    { title: 'Pots', icon: FlowerIcon, items: inventory.pots },
  ];

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <div className="flex items-center mb-4">
        <Package className="h-6 w-6 text-emerald-500 mr-2" />
        <h2 className="text-2xl font-bold">Inventaire</h2>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {categories.map(({ title, icon: Icon, items }) => (
          <motion.div
            key={title}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="bg-emerald-50 rounded-lg p-4"
          >
            <div className="flex items-center mb-3">
              <Icon className="h-5 w-5 text-emerald-600 mr-2" />
              <h3 className="font-semibold">{title}</h3>
            </div>
            <div className="space-y-2">
              {Object.entries(items).map(([item, quantity]) => (
                <div
                  key={item}
                  className="flex justify-between items-center bg-white p-2 rounded"
                >
                  <span className="capitalize">
                    {item.replace(/-/g, ' ')}
                  </span>
                  <span className="font-medium text-emerald-600">
                    {quantity}
                  </span>
                </div>
              ))}
            </div>
          </motion.div>
        ))}
      </div>
    </div>
  );
}

export default Inventory;