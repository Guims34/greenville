import React from 'react';
import { Sprout, Award, Users } from 'lucide-react';
import { motion } from 'framer-motion';
import { useNavigate } from 'react-router-dom';

const Hero = () => {
  const navigate = useNavigate();

  return (
    <div className="relative bg-emerald-900 text-white">
      <div className="relative container mx-auto px-4 py-16 md:py-24">
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="max-w-3xl mx-auto text-center"
        >
          <h1 className="text-4xl md:text-6xl font-bold mb-6">
            Bienvenue dans GreenVille
          </h1>
          <p className="text-xl md:text-2xl text-emerald-200 mb-8">
            Devenez le maître cultivateur dans notre simulation de culture
          </p>
          <div className="flex flex-col sm:flex-row justify-center gap-4 mb-12">
            <motion.button 
              onClick={() => navigate('/register')}
              whileHover={{ scale: 1.05 }}
              whileTap={{ scale: 0.95 }}
              className="px-8 py-4 bg-emerald-500 hover:bg-emerald-400 rounded-lg text-lg font-semibold transition"
            >
              Commencer l'aventure
            </motion.button>
            <motion.button 
              onClick={() => navigate('/login')}
              whileHover={{ scale: 1.05 }}
              whileTap={{ scale: 0.95 }}
              className="px-8 py-4 bg-emerald-700 hover:bg-emerald-600 rounded-lg text-lg font-semibold transition"
            >
              Se connecter
            </motion.button>
          </div>
        </motion.div>

        <div className="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto mt-16">
          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.2 }}
            className="bg-emerald-800/50 p-6 rounded-xl"
          >
            <Sprout className="h-12 w-12 mb-4 text-emerald-400" />
            <h3 className="text-xl font-semibold mb-2">Cultivez</h3>
            <p className="text-emerald-200">
              Gérez votre propre exploitation avec différentes variétés
            </p>
          </motion.div>
          
          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.3 }}
            className="bg-emerald-800/50 p-6 rounded-xl"
          >
            <Award className="h-12 w-12 mb-4 text-emerald-400" />
            <h3 className="text-xl font-semibold mb-2">Progressez</h3>
            <p className="text-emerald-200">
              Montez en niveau et débloquez de nouveaux équipements
            </p>
          </motion.div>
          
          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.4 }}
            className="bg-emerald-800/50 p-6 rounded-xl"
          >
            <Users className="h-12 w-12 mb-4 text-emerald-400" />
            <h3 className="text-xl font-semibold mb-2">Communauté</h3>
            <p className="text-emerald-200">
              Rejoignez une communauté active et échangez avec d'autres joueurs
            </p>
          </motion.div>
        </div>

        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.5 }}
          className="mt-20 grid grid-cols-2 md:grid-cols-4 gap-8 text-center"
        >
          <div>
            <div className="text-4xl font-bold text-emerald-400">10K+</div>
            <div className="text-emerald-200 mt-2">Joueurs Actifs</div>
          </div>
          <div>
            <div className="text-4xl font-bold text-emerald-400">50+</div>
            <div className="text-emerald-200 mt-2">Variétés</div>
          </div>
          <div>
            <div className="text-4xl font-bold text-emerald-400">24/7</div>
            <div className="text-emerald-200 mt-2">Support</div>
          </div>
          <div>
            <div className="text-4xl font-bold text-emerald-400">100%</div>
            <div className="text-emerald-200 mt-2">Gratuit</div>
          </div>
        </motion.div>
      </div>
    </div>
  );
};

export default Hero;