import React from 'react';
import { Bell, X } from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';
import { useNotificationStore } from '../../store/notificationStore';

const NotificationCenter = () => {
  const [isOpen, setIsOpen] = React.useState(false);
  const { notifications, markAsRead, clearNotifications, hasUnread } = useNotificationStore();

  const handleOpen = () => {
    setIsOpen(true);
    notifications.forEach((notification) => {
      if (!notification.read) {
        markAsRead(notification.id);
      }
    });
  };

  return (
    <div className="relative">
      <button
        onClick={handleOpen}
        className="relative p-2 rounded-full hover:bg-gray-100"
      >
        <Bell className="w-6 h-6" />
        {hasUnread && (
          <span className="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full" />
        )}
      </button>

      <AnimatePresence>
        {isOpen && (
          <>
            <div
              className="fixed inset-0 bg-black bg-opacity-50 z-40"
              onClick={() => setIsOpen(false)}
            />
            <motion.div
              initial={{ opacity: 0, y: -10 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -10 }}
              className="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50"
            >
              <div className="p-4 border-b flex justify-between items-center">
                <h3 className="font-semibold">Notifications</h3>
                <button
                  onClick={() => setIsOpen(false)}
                  className="text-gray-500 hover:text-gray-700"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>

              <div className="max-h-96 overflow-y-auto">
                {notifications.length === 0 ? (
                  <p className="p-4 text-center text-gray-500">
                    Aucune notification
                  </p>
                ) : (
                  notifications.map((notification) => (
                    <motion.div
                      key={notification.id}
                      initial={{ opacity: 0 }}
                      animate={{ opacity: 1 }}
                      className={`p-4 border-b ${
                        !notification.read ? 'bg-blue-50' : ''
                      }`}
                    >
                      <h4 className="font-medium">{notification.title}</h4>
                      <p className="text-sm text-gray-600">
                        {notification.message}
                      </p>
                      <p className="text-xs text-gray-500 mt-1">
                        {new Date(notification.timestamp).toLocaleString()}
                      </p>
                    </motion.div>
                  ))
                )}
              </div>

              {notifications.length > 0 && (
                <div className="p-4 border-t">
                  <button
                    onClick={clearNotifications}
                    className="w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded"
                  >
                    Effacer toutes les notifications
                  </button>
                </div>
              )}
            </motion.div>
          </>
        )}
      </AnimatePresence>
    </div>
  );
};

export default NotificationCenter;