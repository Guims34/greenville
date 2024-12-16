CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Index pour optimiser les requêtes
CREATE INDEX idx_chat_user ON chat_messages(user_id);
CREATE INDEX idx_chat_created ON chat_messages(created_at);