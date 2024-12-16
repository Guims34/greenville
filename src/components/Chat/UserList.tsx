import React from 'react';
import { motion } from 'framer-motion';
import { useUserStore } from '../../store/userStore';
import { User } from 'lucide-react';

interface UserListProps {
  onSelect: (username: string) => void;
  onClose: () => void;
}

const UserList: React.FC<UserListProps> = ({ onSelect, onClose }) => {
  const { users } = useUserStore();

  return (
    <motion.div
      initial={{ opacity: 0, y: 10 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: 10 }}
      className="absolute bottom-full mb-2 left-0 bg-white rounded-lg shadow-lg p-4 w-64 max-h-64 overflow-y-auto"
    >
      <div className="space-y-2">
        {users.map((user) => (
          <button
            key={user.id}
            onClick={() => onSelect(user.username)}
            className="w-full flex items-center gap-2 p-2 hover:bg-gray-100 rounded-lg transition-colors"
          >
            <div className="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
              <User className="w-4 h-4 text-emerald-600" />
            </div>
            <span className="text-sm font-medium">{user.username}</span>
          </button>
        ))}
      </div>
    </motion.div>
  );
};

export default UserList;