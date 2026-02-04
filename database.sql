-- DecisionVault Database Schema
-- Execute this file in your MySQL database (phpMyAdmin or command line)

-- Create database (optional - you may already have a database from Hostinger)
-- CREATE DATABASE IF NOT EXISTS decisionvault CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE decisionvault;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    google_id VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    avatar_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_google_id (google_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Workspaces table
CREATE TABLE IF NOT EXISTS workspaces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('personal', 'family', 'team', 'business') DEFAULT 'personal',
    owner_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    settings JSON,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner (owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Workspace members table
CREATE TABLE IF NOT EXISTS workspace_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    workspace_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('admin', 'member', 'viewer') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_workspace_user (workspace_id, user_id),
    INDEX idx_workspace (workspace_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Decisions table
CREATE TABLE IF NOT EXISTS decisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    workspace_id INT NOT NULL,
    title VARCHAR(500) NOT NULL,
    description TEXT,
    problem_statement TEXT,
    final_decision TEXT,
    rationale TEXT,
    category ENUM('Strategic', 'Financial', 'Product', 'Personnel', 'Family', 'Health', 'Education', 'Lifestyle', 'Other') DEFAULT 'Other',
    status ENUM('Proposed', 'Decided', 'Implemented', 'Reviewing', 'Abandoned') DEFAULT 'Proposed',
    confidence_level INT DEFAULT 3 CHECK (confidence_level BETWEEN 1 AND 5),
    decided_at DATE,
    deadline DATE,
    expected_impact TEXT,
    actual_outcome TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ai_generated BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_workspace (workspace_id),
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_created_by (created_by),
    INDEX idx_decided_at (decided_at),
    FULLTEXT idx_search (title, description, problem_statement, final_decision)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Decision makers junction table
CREATE TABLE IF NOT EXISTS decision_makers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    decision_id INT NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (decision_id) REFERENCES decisions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_decision_user (decision_id, user_id),
    INDEX idx_decision (decision_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Options table
CREATE TABLE IF NOT EXISTS options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    decision_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    estimated_cost DECIMAL(15, 2),
    estimated_effort VARCHAR(100),
    was_chosen BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (decision_id) REFERENCES decisions(id) ON DELETE CASCADE,
    INDEX idx_decision (decision_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Option pros and cons table
CREATE TABLE IF NOT EXISTS option_pros_cons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    option_id INT NOT NULL,
    type ENUM('pro', 'con') NOT NULL,
    content TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (option_id) REFERENCES options(id) ON DELETE CASCADE,
    INDEX idx_option (option_id),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tags table
CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    workspace_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) DEFAULT '#6B7280',
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    UNIQUE KEY unique_workspace_tag (workspace_id, name),
    INDEX idx_workspace (workspace_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Decision tags junction table
CREATE TABLE IF NOT EXISTS decision_tags (
    decision_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (decision_id, tag_id),
    FOREIGN KEY (decision_id) REFERENCES decisions(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    INDEX idx_decision (decision_id),
    INDEX idx_tag (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments table
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    decision_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (decision_id) REFERENCES decisions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_decision (decision_id),
    INDEX idx_user (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI conversations table (for storing AI assistant chats)
CREATE TABLE IF NOT EXISTS ai_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    workspace_id INT,
    decision_id INT,
    conversation_data JSON,
    status ENUM('active', 'completed', 'abandoned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE SET NULL,
    FOREIGN KEY (decision_id) REFERENCES decisions(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_workspace (workspace_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity log table
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    workspace_id INT NOT NULL,
    user_id INT NOT NULL,
    decision_id INT,
    action_type ENUM('created', 'updated', 'deleted', 'commented', 'status_changed', 'shared') NOT NULL,
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (decision_id) REFERENCES decisions(id) ON DELETE SET NULL,
    INDEX idx_workspace (workspace_id),
    INDEX idx_user (user_id),
    INDEX idx_decision (decision_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions table for user sessions
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id INT NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert a demo user (optional - for testing)
-- You can remove this after testing
INSERT INTO users (google_id, email, name, avatar_url) VALUES 
('demo_user_123', 'demo@decisionvault.com', 'Demo User', 'https://ui-avatars.com/api/?name=Demo+User&background=6366f1&color=fff')
ON DUPLICATE KEY UPDATE email=email;

-- Insert a demo workspace
INSERT INTO workspaces (name, type, owner_id) 
SELECT 'My Workspace', 'personal', id FROM users WHERE email = 'demo@decisionvault.com' LIMIT 1
ON DUPLICATE KEY UPDATE name=name;

-- All tables created successfully!
-- You can now proceed with setting up the application.
