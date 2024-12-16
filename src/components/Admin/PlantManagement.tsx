import React, { useState } from 'react';
import { Sprout, Search, AlertTriangle } from 'lucide-react';

interface PlantData {
  id: string;
  owner: string;
  strain: string;
  stage: number;
  health: number;
  startDate: string;
  status: 'healthy' | 'warning' | 'critical';
}

const PlantManagement = () => {
  const [searchTerm, setSearchTerm] = useState('');

  // Données simulées
  const plants: PlantData[] = [
    {
      id: '1',
      owner: 'JohnDoe',
      strain: 'Northern Lights',
      stage: 3,
      health: 95,
      startDate: '2024-01-20',
      status: 'healthy'
    },
    {
      id: '2',
      owner: 'JaneSmith',
      strain: 'Blue Dream',
      stage: 2,
      health: 75,
      startDate: '2024-02-05',
      status: 'warning'
    },
    {
      id: '3',
      owner: 'BobJohnson',
      strain: 'White Widow',
      stage: 1,
      health: 45,
      startDate: '2024-02-10',
      status: 'critical'
    }
  ];

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'healthy':
        return 'bg-green-100 text-green-800';
      case 'warning':
        return 'bg-yellow-100 text-yellow-800';
      case 'critical':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const filteredPlants = plants.filter(plant => 
    plant.owner.toLowerCase().includes(searchTerm.toLowerCase()) ||
    plant.strain.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-xl font-semibold mb-6">Gestion des Plantes</h2>
      
      <div className="mb-6 relative">
        <input
          type="text"
          placeholder="Rechercher une plante..."
          className="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
        />
        <Search className="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
      </div>

      <div className="overflow-x-auto">
        <table className="min-w-full">
          <thead>
            <tr className="bg-gray-50">
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Plante
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Propriétaire
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Stade
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Santé
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Date de début
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Statut
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {filteredPlants.map((plant) => (
              <tr key={plant.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="flex items-center">
                    <div className="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center">
                      <Sprout className="h-5 w-5 text-emerald-600" />
                    </div>
                    <div className="ml-4">
                      <div className="text-sm font-medium text-gray-900">
                        {plant.strain}
                      </div>
                      <div className="text-sm text-gray-500">
                        ID: {plant.id}
                      </div>
                    </div>
                  </div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {plant.owner}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  Stage {plant.stage}/4
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="flex items-center">
                    <div className="w-full bg-gray-200 rounded-full h-2.5">
                      <div 
                        className={`h-2.5 rounded-full ${
                          plant.health > 80 ? 'bg-green-500' :
                          plant.health > 50 ? 'bg-yellow-500' : 'bg-red-500'
                        }`}
                        style={{ width: `${plant.health}%` }}
                      ></div>
                    </div>
                    <span className="ml-2 text-sm text-gray-600">
                      {plant.health}%
                    </span>
                  </div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {new Date(plant.startDate).toLocaleDateString()}
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(plant.status)}`}>
                    {plant.status === 'healthy' && 'Saine'}
                    {plant.status === 'warning' && 'Attention'}
                    {plant.status === 'critical' && 'Critique'}
                  </span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default PlantManagement;