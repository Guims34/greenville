CREATE DATABASE IF NOT EXISTS greenville;
USE greenville;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    level INT DEFAULT 1,
    coins INT DEFAULT 1000,
    premium_coins INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE plants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    strain VARCHAR(100) NOT NULL,
    stage INT DEFAULT 1,
    health INT DEFAULT 100,
    humidity INT DEFAULT 50,
    temperature INT DEFAULT 20,
    water_level INT DEFAULT 100,
    growth_time INT NOT NULL,
    last_watered TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Index pour optimiser les requêtes
CREATE INDEX idx_plants_user ON plants(user_id);
CREATE INDEX idx_chat_user ON chat_messages(user_id);