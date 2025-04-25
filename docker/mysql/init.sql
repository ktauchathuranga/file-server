-- API Clients
CREATE TABLE api_clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(255) NOT NULL UNIQUE,
    client_secret VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_client_name ON api_clients(client_name);

-- Files
CREATE TABLE files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100),
    size BIGINT,
    uploaded_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES api_clients(id)
);
CREATE INDEX idx_file_id ON files(id);

-- One-Time Links
CREATE TABLE one_time_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_id BIGINT UNSIGNED,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (file_id) REFERENCES files(id)
);
CREATE INDEX idx_token ON one_time_links(token);

-- -- Insert a test client (optional)
-- INSERT INTO api_clients (client_name, client_secret)
-- VALUES ('lms_backend', '$2y$10$your_hashed_secret_here');