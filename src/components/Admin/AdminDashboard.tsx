import React, { useState } from 'react';
import { Users, Sprout, Settings, AlertTriangle } from 'lucide-react';
import { motion } from 'framer-motion';
import UserManagement from './UserManagement';
import PlantManagement from './PlantManagement';

const AdminDashboard = () => {
  const [activeTab, setActiveTab] = useState<'overview' | 'users' | 'plants'>('overview');
  
  const stats = {
    totalUsers: 1250,
    activePlants: 3420,
    reportedIssues: 8,
    systemHealth: 98
  };

  const renderContent = () => {
    switch (activeTab) {
      case 'users':
        return <UserManagement />;
      case 'plants':
        return <PlantManagement />;
      default:
        return (
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div className="bg-white rounded-lg shadow-md p-6">
              <h2 className="text-xl font-semibold mb-4">Dernières Inscriptions</h2>
              <div className="space-y-4">
                {[1, 2, 3, 4, 5].map((user) => (
                  <div key={user} className="flex items-center justify-between p-4 hover:bg-gray-50 rounded-lg">
                    <div className="flex items-center">
                      <div className="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                        <Users className="w-5 h-5 text-emerald-600" />
                      </div>
                      <div className="ml-4">
                        <p className="font-medium">Utilisateur {user}</p>
                        <p className="text-sm text-gray-500">il y a {user} heure(s)</p>
                      </div>
                    </div>
                    <button className="text-emerald-600 hover:text-emerald-700">
                      Voir détails
                    </button>
                  </div>
                ))}
              </div>
            </div>

            <div className="bg-white rounded-lg shadow-md p-6">
              <h2 className="text-xl font-semibold mb-4">Actions Rapides</h2>
              <div className="grid grid-cols-2 gap-4">
                <button 
                  onClick={() => setActiveTab('users')}
                  className="p-4 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition"
                >
                  <Users className="w-6 h-6 text-emerald-600 mx-auto mb-2" />
                  <span className="text-sm font-medium">Gérer Utilisateurs</span>
                </button>
                <button className="p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                  <Settings className="w-6 h-6 text-blue-600 mx-auto mb-2" />
                  <span className="text-sm font-medium">Configuration</span>
                </button>
                <button 
                  onClick={() => setActiveTab('plants')}
                  className="p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition"
                >
                  <Sprout className="w-6 h-6 text-purple-600 mx-auto mb-2" />
                  <span className="text-sm font-medium">Gérer Plantes</span>
                </button>
                <button className="p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition">
                  <AlertTriangle className="w-6 h-6 text-orange-600 mx-auto mb-2" />
                  <span className="text-sm font-medium">Voir Alertes</span>
                </button>
              </div>
            </div>
          </div>
        );
    }
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex justify-between items-center mb-8">
        <h1 className="text-2xl font-bold text-gray-800">Administration</h1>
        <div className="flex gap-4">
          <button 
            onClick={() => setActiveTab('overview')}
            className={`px-4 py-2 rounded-lg transition ${
              activeTab === 'overview' 
                ? 'bg-emerald-500 text-white' 
                : 'text-gray-600 hover:bg-gray-100'
            }`}
          >
            Vue d'ensemble
          </button>
          <button 
            onClick={() => setActiveTab('users')}
            className={`px-4 py-2 rounded-lg transition ${
              activeTab === 'users' 
                ? 'bg-emerald-500 text-white' 
                : 'text-gray-600 hover:bg-gray-100'
            }`}
          >
            Utilisateurs
          </button>
          <button 
            onClick={() => setActiveTab('plants')}
            className={`px-4 py-2 rounded-lg transition ${
              activeTab === 'plants' 
                ? 'bg-emerald-500 text-white' 
                : 'text-gray-600 hover:bg-gray-100'
            }`}
          >
            Plantes
          </button>
        </div>
      </div>

      {activeTab === 'overview' && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="bg-white p-6 rounded-lg shadow-md"
          >
            <div className="flex items-center justify-between mb-4">
              <Users className="h-8 w-8 text-blue-500" />
              <span className="text-sm text-gray-500">Utilisateurs</span>
            </div>
            <p className="text-2xl font-bold">{stats.totalUsers}</p>
            <p className="text-sm text-green-500">+12% ce mois</p>
          </motion.div>

          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.1 }}
            className="bg-white p-6 rounded-lg shadow-md"
          >
            <div className="flex items-center justify-between mb-4">
              <Sprout className="h-8 w-8 text-emerald-500" />
              <span className="text-sm text-gray-500">Plantes Actives</span>
            </div>
            <p className="text-2xl font-bold">{stats.activePlants}</p>
            <p className="text-sm text-green-500">+8% cette semaine</p>
          </motion.div>

          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.2 }}
            className="bg-white p-6 rounded-lg shadow-md"
          >
            <div className="flex items-center justify-between mb-4">
              <AlertTriangle className="h-8 w-8 text-orange-500" />
              <span className="text-sm text-gray-500">Problèmes Signalés</span>
            </div>
            <p className="text-2xl font-bold">{stats.reportedIssues}</p>
            <p className="text-sm text-orange-500">3 nouveaux</p>
          </motion.div>

          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.3 }}
            className="bg-white p-6 rounded-lg shadow-md"
          >
            <div className="flex items-center justify-between mb-4">
              <Settings className="h-8 w-8 text-gray-500" />
              <span className="text-sm text-gray-500">Santé Système</span>
            </div>
            <p className="text-2xl font-bold">{stats.systemHealth}%</p>
            <p className="text-sm text-green-500">Optimal</p>
          </motion.div>
        </div>
      )}

      {renderContent()}
    </div>
  );
};

export default AdminDashboard;