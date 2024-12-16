import React from 'react';

const ProgressChart = () => {
  return (
    <div className="h-64 flex items-center justify-center">
      <div className="w-full max-w-md">
        <div className="mb-4">
          <div className="flex justify-between mb-1">
            <span className="text-sm font-medium text-gray-700">Objectif hebdomadaire</span>
            <span className="text-sm font-medium text-gray-700">75%</span>
          </div>
          <div className="w-full bg-gray-200 rounded-full h-2.5">
            <div className="bg-blue-600 h-2.5 rounded-full" style={{ width: '75%' }}></div>
          </div>
        </div>
        
        <div className="mb-4">
          <div className="flex justify-between mb-1">
            <span className="text-sm font-medium text-gray-700">Objectif mensuel</span>
            <span className="text-sm font-medium text-gray-700">45%</span>
          </div>
          <div className="w-full bg-gray-200 rounded-full h-2.5">
            <div className="bg-green-600 h-2.5 rounded-full" style={{ width: '45%' }}></div>
          </div>
        </div>
        
        <div>
          <div className="flex justify-between mb-1">
            <span className="text-sm font-medium text-gray-700">Objectif annuel</span>
            <span className="text-sm font-medium text-gray-700">20%</span>
          </div>
          <div className="w-full bg-gray-200 rounded-full h-2.5">
            <div className="bg-yellow-600 h-2.5 rounded-full" style={{ width: '20%' }}></div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProgressChart;