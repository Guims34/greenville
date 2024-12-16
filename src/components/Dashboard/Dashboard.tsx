import React from 'react';
import { useAuthStore } from '../../store/authStore';
import StatsOverview from './StatsOverview';
import ActivityFeed from './ActivityFeed';
import ProgressChart from './ProgressChart';
import DailyMissions from './DailyMissions';
import LeaderboardWidget from '../Leaderboard/LeaderboardWidget';
import ChatWindow from '../Chat/ChatWindow';
import NotificationCenter from '../Notifications/NotificationCenter';
import AchievementList from '../Achievements/AchievementList';
import PlayerStats from './PlayerStats';

const Dashboard = () => {
  const user = useAuthStore((state) => state.user);

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex justify-between items-center mb-8">
        <h1 className="text-2xl font-bold text-gray-800">
          Tableau de bord
        </h1>
        <div className="flex items-center gap-4">
          <NotificationCenter />
          <span className="text-sm text-gray-500">
            Bienvenue, {user?.username}
          </span>
        </div>
      </div>

      <PlayerStats />

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <DailyMissions />
        <LeaderboardWidget />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-lg font-semibold mb-4">Progression</h2>
          <ProgressChart />
        </div>
        
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-lg font-semibold mb-4">Activités récentes</h2>
          <ActivityFeed />
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <ChatWindow />
        <div>
          <h2 className="text-xl font-bold mb-6">Succès</h2>
          <AchievementList />
        </div>
      </div>
    </div>
  );
};

export default Dashboard;