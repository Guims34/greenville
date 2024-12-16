import React, { Suspense } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { GoogleOAuthProvider } from '@react-oauth/google';
import { useAuthStore } from './store/authStore';
import { Toaster } from 'react-hot-toast';
import Header from './components/Header';
import Hero from './components/Hero';
import LoginForm from './components/Auth/LoginForm';
import RegisterForm from './components/Auth/RegisterForm';
import Dashboard from './components/Dashboard/Dashboard';
import AdminDashboard from './components/Admin/AdminDashboard';
import PrivacyPolicy from './components/Legal/PrivacyPolicy';
import LoadingSpinner from './components/LoadingSpinner';

function PrivateRoute({ children, requireAdmin = false }: { children: React.ReactNode, requireAdmin?: boolean }) {
  const { isAuthenticated, user } = useAuthStore();
  const isAdmin = user?.email === "admin@greenville.com";

  if (!isAuthenticated) return <Navigate to="/login" />;
  if (requireAdmin && !isAdmin) return <Navigate to="/dashboard" />;
  return <>{children}</>;
}

function App() {
  return (
    <GoogleOAuthProvider 
      clientId={import.meta.env.VITE_GOOGLE_CLIENT_ID}
    >
      <Router>
        <div className="min-h-screen bg-emerald-50">
          <Header />
          <Suspense fallback={<LoadingSpinner />}>
            <Routes>
              <Route path="/" element={<Hero />} />
              <Route path="/login" element={<LoginForm />} />
              <Route path="/register" element={<RegisterForm />} />
              <Route path="/privacy" element={<PrivacyPolicy />} />
              <Route
                path="/dashboard/*"
                element={
                  <PrivateRoute>
                    <Dashboard />
                  </PrivateRoute>
                }
              />
              <Route
                path="/admin/*"
                element={
                  <PrivateRoute requireAdmin>
                    <AdminDashboard />
                  </PrivateRoute>
                }
              />
              <Route path="*" element={<Navigate to="/" replace />} />
            </Routes>
          </Suspense>
          <Toaster 
            position="top-right"
            toastOptions={{
              duration: 3000,
              style: {
                background: '#333',
                color: '#fff',
              },
              success: {
                style: {
                  background: '#059669',
                },
              },
              error: {
                style: {
                  background: '#dc2626',
                },
              },
            }}
          />
        </div>
      </Router>
    </GoogleOAuthProvider>
  );
}

export default App;