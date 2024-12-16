import React, { useState, useRef, useEffect } from 'react';
import { Send, Smile } from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';
import { useAuthStore } from '../../store/authStore';
import { useChatStore } from '../../store/chatStore';
import EmojiPicker from './EmojiPicker';

const ChatWindow = () => {
  const [newMessage, setNewMessage] = useState('');
  const [showEmoji, setShowEmoji] = useState(false);
  const { user } = useAuthStore();
  const { messages, addMessage } = useChatStore();
  const messagesEndRef = useRef<HTMLDivElement>(null);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newMessage.trim() || !user) return;

    addMessage({
      senderId: user.id,
      senderName: user.username,
      content: newMessage.trim()
    });

    setNewMessage('');
  };

  const handleEmojiSelect = (emoji: string) => {
    setNewMessage(prev => prev + emoji);
    setShowEmoji(false);
  };

  return (
    <div className="bg-white rounded-lg shadow-md h-[500px] flex flex-col">
      <div className="p-4 border-b">
        <h2 className="text-lg font-semibold">Chat</h2>
      </div>

      <div className="flex-1 overflow-y-auto p-4 space-y-4">
        {messages.map((msg) => (
          <motion.div
            key={msg.id}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className={`flex ${msg.senderId === user?.id ? 'justify-end' : 'justify-start'}`}
          >
            <div
              className={`max-w-[70%] rounded-lg p-3 ${
                msg.senderId === user?.id ? 'bg-emerald-500 text-white' : 'bg-gray-100'
              }`}
            >
              <p className="text-xs font-medium mb-1">
                {msg.senderId === user?.id ? 'Vous' : msg.senderName}
              </p>
              <p className="break-words">{msg.content}</p>
              <p className="text-xs opacity-70 mt-1">
                {new Date(msg.timestamp).toLocaleTimeString()}
              </p>
            </div>
          </motion.div>
        ))}
        <div ref={messagesEndRef} />
      </div>

      <form onSubmit={handleSubmit} className="p-4 border-t">
        <div className="flex space-x-2">
          <div className="relative flex-1">
            <input
              type="text"
              value={newMessage}
              onChange={(e) => setNewMessage(e.target.value)}
              placeholder="Votre message..."
              className="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 pr-10"
              maxLength={500}
            />
            <button
              type="button"
              onClick={() => setShowEmoji(!showEmoji)}
              className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
            >
              <Smile className="w-5 h-5" />
            </button>
            <AnimatePresence>
              {showEmoji && (
                <div className="absolute bottom-full right-0 mb-2">
                  <EmojiPicker
                    onSelect={handleEmojiSelect}
                    onClose={() => setShowEmoji(false)}
                  />
                </div>
              )}
            </AnimatePresence>
          </div>
          <button
            type="submit"
            disabled={!newMessage.trim()}
            className="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 disabled:opacity-50"
          >
            <Send className="w-5 h-5" />
          </button>
        </div>
      </form>
    </div>
  );
};

export default ChatWindow;