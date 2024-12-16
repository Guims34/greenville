import { useEffect } from 'react';
import { socketService } from '../services/socket';
import { useAuthStore } from '../store/authStore';
import { useChatStore } from '../store/chatStore';

export const useChat = () => {
  const { user } = useAuthStore();
  const { messages, addMessage, addReaction, removeReaction } = useChatStore();

  useEffect(() => {
    // Se connecter au chat seulement si l'utilisateur est authentifié
    if (!user) return;

    // Nettoyer la connexion lors du démontage
    return () => {
      socketService.disconnect();
    };
  }, [user]);

  const sendMessage = (content: string) => {
    if (!user) return;

    const message = {
      senderId: user.id,
      senderName: user.username,
      content
    };

    socketService.sendMessage(message);
  };

  const handleReaction = (messageId: string, emoji: string) => {
    if (!user) return;

    const message = messages.find(m => m.id === messageId);
    if (!message) return;

    const hasReacted = message.reactions?.[emoji]?.includes(user.id);
    
    if (hasReacted) {
      socketService.removeReaction(messageId, emoji);
    } else {
      socketService.addReaction(messageId, emoji);
    }
  };

  return {
    messages,
    sendMessage,
    handleReaction
  };
};