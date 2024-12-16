import React from 'react';
import { TrendingUp, Award, Star } from 'lucide-react';
import { useAuthStore } from '../../store/authStore';

const StatsOverview = () => {
  const user = useAuthStore((state) => state.user);

  const stats = [
    {
      label: 'Niveau',
      value: user?.level || 1,
      icon: Award,
      color: 'text-blue-500',
      trend: '+2 cette semaine'
    },
    {
      label: 'Points',
      value: '2,450',
      icon: Star,
      color: 'text-yellow-500',
      trend: '+180 aujourd\'hui'
    },
    {
      label: 'Progression',
      value: '87%',
      icon: TrendingUp,
      color: 'text-green-500',
      trend: '+12% ce mois'
    }
  ];

  return (
    <>
      {stats.map((stat, index) => (
        <div key={index} className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center justify-between mb-4">
            <div className="flex items-center">
              <stat.icon className={`w-6 h-6 ${stat.color} mr-2`} />
              <h3 className="text-gray-600">{stat.label}</h3>
            </div>
          </div>
          <div className="flex items-end justify-between">
            <div>
              <p className="text-2xl font-bold">{stat.value}</p>
              <p className="text-sm text-gray-500">{stat.trend}</p>
            </div>
          </div>
        </div>
      ))}
    </>
  );
};

export default StatsOverview;