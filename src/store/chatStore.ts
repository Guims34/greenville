import { create } from 'zustand';
import { persist } from 'zustand/middleware';

interface Message {
  id: string;
  senderId: string;
  senderName: string;
  content: string;
  timestamp: Date;
}

interface ChatState {
  messages: Message[];
  addMessage: (message: Omit<Message, 'id' | 'timestamp'>) => void;
  clearMessages: () => void;
}

export const useChatStore = create<ChatState>()(
  persist(
    (set) => ({
      messages: [],
      addMessage: (message) => {
        const newMessage = {
          ...message,
          id: crypto.randomUUID(),
          timestamp: new Date()
        };
        
        set((state) => ({
          messages: [...state.messages, newMessage]
        }));
      },
      clearMessages: () => set({ messages: [] })
    }),
    {
      name: 'chat-storage',
      storage: localStorage
    }
  )
);