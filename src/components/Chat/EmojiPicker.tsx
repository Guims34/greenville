import React from 'react';
import { motion } from 'framer-motion';

interface EmojiPickerProps {
  onSelect: (emoji: string) => void;
  onClose: () => void;
}

const EMOJI_CATEGORIES = {
  'Smileys': ['😊', '😂', '🤣', '😍', '🥰', '😎', '🤔', '😅'],
  'Gestes': ['👍', '👎', '👋', '✌️', '🤝', '👊', '🙌', '👏'],
  'Nature': ['🌱', '🌿', '🍀', '🌺', '🌸', '🌼', '🌻', '🌹'],
  'Objets': ['💡', '⭐', '💫', '🎉', '🎨', '📚', '💻', '🎮']
};

const EmojiPicker: React.FC<EmojiPickerProps> = ({ onSelect, onClose }) => {
  return (
    <motion.div
      initial={{ opacity: 0, y: 10 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: 10 }}
      className="absolute bottom-full mb-2 left-0 bg-white rounded-lg shadow-lg p-4 w-64"
    >
      <div className="space-y-4">
        {Object.entries(EMOJI_CATEGORIES).map(([category, emojis]) => (
          <div key={category}>
            <h3 className="text-sm font-medium text-gray-700 mb-2">{category}</h3>
            <div className="grid grid-cols-8 gap-1">
              {emojis.map((emoji) => (
                <button
                  key={emoji}
                  onClick={() => onSelect(emoji)}
                  className="hover:bg-gray-100 rounded p-1 text-xl"
                >
                  {emoji}
                </button>
              ))}
            </div>
          </div>
        ))}
      </div>
    </motion.div>
  );
};

export default EmojiPicker;