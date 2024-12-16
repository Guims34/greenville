import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../store/authStore';
import { LogOut, User } from 'lucide-react';

const Header = () => {
  const navigate = useNavigate();
  const { isAuthenticated, user, logout } = useAuthStore();

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  const isAdmin = user?.email === "admin@greenville.com";

  return (
    <header className="bg-white shadow">
      <nav className="container mx-auto px-4 py-4">
        <div className="flex justify-between items-center">
          <Link to="/" className="text-xl font-bold text-emerald-600">
            GreenVille
          </Link>
          
          <div className="flex items-center gap-4">
            {isAuthenticated ? (
              <>
                <Link to="/dashboard" className="text-gray-600 hover:text-gray-800">
                  Dashboard
                </Link>
                {isAdmin && (
                  <Link to="/admin" className="text-gray-600 hover:text-gray-800">
                    Admin
                  </Link>
                )}
                <div className="flex items-center gap-2">
                  <User className="w-5 h-5 text-emerald-600" />
                  <span className="text-gray-700">{user?.username}</span>
                </div>
                <button
                  onClick={handleLogout}
                  className="flex items-center gap-2 text-gray-600 hover:text-gray-800"
                >
                  <LogOut className="w-5 h-5" />
                  Déconnexion
                </button>
              </>
            ) : (
              <>
                <Link 
                  to="/login" 
                  className="text-emerald-600 hover:text-emerald-700 px-4 py-2 rounded-lg"
                >
                  Connexion
                </Link>
                <Link 
                  to="/register" 
                  className="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700"
                >
                  Inscription
                </Link>
              </>
            )}
          </div>
        </div>
      </nav>
    </header>
  );
};

export default Header;