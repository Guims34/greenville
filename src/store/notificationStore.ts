import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export interface Notification {
  id: string;
  title: string;
  message: string;
  type: 'info' | 'success' | 'warning' | 'error';
  read: boolean;
  timestamp: Date;
}

interface NotificationState {
  notifications: Notification[];
  addNotification: (notification: Omit<Notification, 'id' | 'read' | 'timestamp'>) => void;
  markAsRead: (id: string) => void;
  clearNotifications: () => void;
  hasUnread: boolean;
}

export const useNotificationStore = create<NotificationState>()(
  persist(
    (set, get) => ({
      notifications: [],
      hasUnread: false,
      addNotification: (notification) => {
        const newNotification = {
          ...notification,
          id: crypto.randomUUID(),
          read: false,
          timestamp: new Date()
        };
        set((state) => ({
          notifications: [newNotification, ...state.notifications],
          hasUnread: true
        }));

        // Demander la permission pour les notifications push si pas déjà accordée
        if (Notification.permission === 'default') {
          Notification.requestPermission();
        }

        // Envoyer une notification push si autorisé
        if (Notification.permission === 'granted') {
          new Notification(notification.title, {
            body: notification.message,
            icon: '/cannabis.svg'
          });
        }
      },
      markAsRead: (id) => set((state) => ({
        notifications: state.notifications.map((n) =>
          n.id === id ? { ...n, read: true } : n
        ),
        hasUnread: state.notifications.some((n) => !n.read && n.id !== id)
      })),
      clearNotifications: () => set({ notifications: [], hasUnread: false })
    }),
    {
      name: 'notifications-storage'
    }
  )
);