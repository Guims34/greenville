import React, { useEffect } from 'react';
import { CheckCircle, Clock } from 'lucide-react';
import { motion } from 'framer-motion';
import { useDailyMissionsStore } from '../../store/dailyMissionsStore';
import { useGameStore } from '../../store/gameStore';
import { useNotificationStore } from '../../store/notificationStore';

const DailyMissions = () => {
  const { missions, generateDailyMissions, claimReward } = useDailyMissionsStore();
  const { addCoins, addExperience } = useGameStore();
  const { addNotification } = useNotificationStore();

  useEffect(() => {
    generateDailyMissions();
  }, [generateDailyMissions]);

  const handleClaimReward = (missionId: string) => {
    const mission = missions.find((m) => m.id === missionId);
    if (!mission || !mission.completed) return;

    if (claimReward(missionId)) {
      addCoins(mission.reward.coins);
      addExperience(mission.reward.experience);
      
      addNotification({
        title: 'Récompense réclamée !',
        message: `Vous avez reçu ${mission.reward.coins} pièces et ${mission.reward.experience} XP`,
        type: 'success'
      });
    }
  };

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-xl font-bold mb-6">Missions Quotidiennes</h2>

      <div className="space-y-4">
        {missions.map((mission) => (
          <motion.div
            key={mission.id}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="border rounded-lg p-4"
          >
            <div className="flex justify-between items-start mb-2">
              <h3 className="font-semibold">{mission.title}</h3>
              <div className="flex items-center text-sm text-gray-500">
                <Clock className="w-4 h-4 mr-1" />
                <span>
                  {new Date(mission.expiresAt).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                  })}
                </span>
              </div>
            </div>

            <p className="text-gray-600 mb-3">{mission.description}</p>

            <div className="space-y-2">
              <div className="w-full bg-gray-200 rounded-full h-2">
                <div
                  className="bg-emerald-500 h-2 rounded-full transition-all"
                  style={{
                    width: `${(mission.progress / mission.target) * 100}%`
                  }}
                />
              </div>

              <div className="flex justify-between items-center">
                <span className="text-sm text-gray-500">
                  {mission.progress}/{mission.target}
                </span>
                {mission.completed ? (
                  <button
                    onClick={() => handleClaimReward(mission.id)}
                    className="flex items-center text-sm text-emerald-500 hover:text-emerald-600"
                  >
                    <CheckCircle className="w-4 h-4 mr-1" />
                    Réclamer
                  </button>
                ) : (
                  <span className="text-sm text-gray-500">
                    {mission.reward.coins} 🪙 | {mission.reward.experience} XP
                  </span>
                )}
              </div>
            </div>
          </motion.div>
        ))}
      </div>
    </div>
  );
};

export default DailyMissions;