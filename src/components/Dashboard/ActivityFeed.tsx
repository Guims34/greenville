import React from 'react';
import { Circle } from 'lucide-react';

const ActivityFeed = () => {
  const activities = [
    {
      id: 1,
      type: 'achievement',
      message: 'Niveau 5 atteint',
      time: 'Il y a 2 heures'
    },
    {
      id: 2,
      type: 'reward',
      message: 'Récompense quotidienne collectée',
      time: 'Il y a 5 heures'
    },
    {
      id: 3,
      type: 'progress',
      message: 'Nouveau record personnel',
      time: 'Hier'
    }
  ];

  return (
    <div className="space-y-4">
      {activities.map((activity) => (
        <div key={activity.id} className="flex items-start space-x-3">
          <Circle className="w-2 h-2 mt-2 text-blue-500" />
          <div>
            <p className="text-gray-800">{activity.message}</p>
            <p className="text-sm text-gray-500">{activity.time}</p>
          </div>
        </div>
      ))}
    </div>
  );
};

export default ActivityFeed;