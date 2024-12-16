import React from 'react';
import { motion } from 'framer-motion';
import { Shield } from 'lucide-react';

function PrivacyPolicy() {
  return (
    <div className="container mx-auto px-4 py-8">
      <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-8"
      >
        <div className="flex items-center mb-6">
          <Shield className="w-8 h-8 text-emerald-500 mr-2" />
          <h1 className="text-3xl font-bold">Politique de Confidentialité</h1>
        </div>

        <div className="prose prose-emerald max-w-none">
          <p className="text-lg mb-4">Dernière mise à jour : {new Date().toLocaleDateString()}</p>

          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">1. Collecte des Données</h2>
            <p>Nous collectons les informations suivantes :</p>
            <ul className="list-disc pl-6 mb-4">
              <li>Informations de profil (nom, email)</li>
              <li>Données de jeu (progression, scores)</li>
              <li>Informations techniques (IP, appareil)</li>
            </ul>
          </section>

          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">2. Utilisation des Données</h2>
            <p>Vos données sont utilisées pour :</p>
            <ul className="list-disc pl-6 mb-4">
              <li>Gérer votre compte et progression</li>
              <li>Améliorer l'expérience de jeu</li>
              <li>Communiquer sur les mises à jour</li>
            </ul>
          </section>

          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">3. Protection des Données</h2>
            <p>Nous protégeons vos données via :</p>
            <ul className="list-disc pl-6 mb-4">
              <li>Chiffrement SSL/TLS</li>
              <li>Accès restreint aux données</li>
              <li>Mises à jour de sécurité régulières</li>
            </ul>
          </section>

          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">4. Suppression des Données</h2>
            <p>Vous pouvez demander la suppression de vos données :</p>
            <ul className="list-disc pl-6 mb-4">
              <li>Via votre profil utilisateur</li>
              <li>Par email à privacy@greenville.com</li>
              <li>Les données seront supprimées sous 30 jours</li>
            </ul>
          </section>

          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">5. Cookies</h2>
            <p>Nous utilisons des cookies pour :</p>
            <ul className="list-disc pl-6 mb-4">
              <li>Maintenir votre session</li>
              <li>Sauvegarder vos préférences</li>
              <li>Analyser l'utilisation du site</li>
            </ul>
          </section>

          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">6. Contact</h2>
            <p>Pour toute question sur vos données :</p>
            <ul className="list-disc pl-6 mb-4">
              <li>Email : privacy@greenville.com</li>
              <li>Formulaire de contact sur le site</li>
              <li>Délai de réponse : 48h maximum</li>
            </ul>
          </section>
        </div>
      </motion.div>
    </div>
  );
}

export default PrivacyPolicy;