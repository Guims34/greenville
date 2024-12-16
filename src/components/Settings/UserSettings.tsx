import React from 'react';
import { Settings, User } from 'lucide-react';
import DeleteAccount from '../Auth/DeleteAccount';

function UserSettings() {
  return (
    <div className="container mx-auto px-4 py-8">
      <div className="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
        <div className="flex items-center mb-6">
          <Settings className="w-6 h-6 text-emerald-500 mr-2" />
          <h2 className="text-2xl font-bold">Paramètres du compte</h2>
        </div>

        <div className="space-y-6">
          <section>
            <h3 className="text-lg font-semibold mb-4">Informations personnelles</h3>
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700">
                  Nom d'utilisateur
                </label>
                <input
                  type="text"
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700">
                  Email
                </label>
                <input
                  type="email"
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                />
              </div>
            </div>
          </section>

          <section>
            <h3 className="text-lg font-semibold mb-4">Préférences</h3>
            <div className="space-y-4">
              <div className="flex items-center">
                <input
                  type="checkbox"
                  id="notifications"
                  className="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                />
                <label htmlFor="notifications" className="ml-2 block text-sm text-gray-700">
                  Recevoir les notifications
                </label>
              </div>
              <div className="flex items-center">
                <input
                  type="checkbox"
                  id="newsletter"
                  className="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                />
                <label htmlFor="newsletter" className="ml-2 block text-sm text-gray-700">
                  S'abonner à la newsletter
                </label>
              </div>
            </div>
          </section>

          <div className="pt-4 border-t">
            <button className="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">
              Sauvegarder les modifications
            </button>
          </div>

          <DeleteAccount />
        </div>
      </div>
    </div>
  );
}

export default UserSettings;