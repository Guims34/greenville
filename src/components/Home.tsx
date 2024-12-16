import React from 'react';
import { useNavigate } from 'react-router-dom';

const Home = () => {
  const navigate = useNavigate();

  return (
    <div className="max-w-2xl mx-auto text-center">
      <h1 className="text-4xl font-bold mb-6">
        Bienvenue sur MonApp
      </h1>
      <p className="text-xl text-gray-600 mb-8">
        Une application simple et efficace pour gérer vos projets.
      </p>
      <button
        onClick={() => navigate('/register')}
        className="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600"
      >
        Commencer
      </button>
    </div>
  );
};

export default Home;