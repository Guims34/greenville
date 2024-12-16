import React from 'react';
import { useAuthStore } from '../store/authStore';

const Dashboard = () => {
  const user = useAuthStore((state) => state.user);

  return (
    <div className="max-w-4xl mx-auto">
      <h2 className="text-2xl font-bold mb-6">Dashboard</h2>
      <div className="bg-white shadow rounded-lg p-6">
        <h3 className="text-lg font-medium mb-4">Bienvenue, {user?.username}</h3>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div className="bg-gray-50 p-4 rounded">
            <h4 className="font-medium mb-2">Statistiques</h4>
            <p>Niveau: {user?.level}</p>
          </div>
          <div className="bg-gray-50 p-4 rounded">
            <h4 className="font-medium mb-2">Activité récente</h4>
            <p>Aucune activité récente</p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;