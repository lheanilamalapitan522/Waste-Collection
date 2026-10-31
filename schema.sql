CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NULL,
    role ENUM('admin', 'collector', 'developer') NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS developer_maintenance_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    details TEXT NULL,
    status ENUM('in_progress', 'completed') NOT NULL DEFAULT 'in_progress',
    created_by VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    INDEX idx_maintenance_status_created (status, created_at)
);

CREATE TABLE IF NOT EXISTS routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    barangay VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    UNIQUE KEY uq_route_name (name)
);

CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_number VARCHAR(50) NOT NULL UNIQUE,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'
);

CREATE TABLE IF NOT EXISTS drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    vehicle_id INT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS route_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_id INT NOT NULL,
    point_order INT NOT NULL,
    point_name VARCHAR(100) NOT NULL,
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE,
    UNIQUE KEY uq_route_point_order (route_id, point_order)
);

CREATE TABLE IF NOT EXISTS schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_id INT NULL,
    day_name VARCHAR(20) NOT NULL,
    collection_time VARCHAR(50) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS gps_locations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    collector_username VARCHAR(100) NOT NULL,
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    accuracy DECIMAL(10, 2) NULL,
    recorded_at DATETIME NOT NULL,
    INDEX idx_gps_latest (collector_username, recorded_at)
);

INSERT IGNORE INTO routes (id, name, barangay, status) VALUES
    (1, 'Route 1', 'Brgy. San Isidro', 'active'),
    (2, 'Route 2', 'Brgy. Bagong Bayan', 'active'),
    (3, 'Route 3', 'Brgy. Poblacion', 'inactive');

INSERT IGNORE INTO vehicles (id, vehicle_number, status) VALUES
    (1, 'Truck 01', 'active'),
    (2, 'Truck 02', 'active');

INSERT IGNORE INTO drivers (id, name, vehicle_id, status) VALUES
    (1, 'Juan Cruz', 1, 'active'),
    (2, 'Pedro Santos', 2, 'active'),
    (3, 'Mark Reyes', NULL, 'inactive');

INSERT IGNORE INTO route_points
    (route_id, point_order, point_name, latitude, longitude) VALUES
    (1, 1, 'Brgy. San Isidro', 14.1852000, 121.6835000),
    (1, 2, 'Brgy. Pag-asa', 14.1828000, 121.6860000),
    (1, 3, 'Brgy. Bagong Bayan', 14.1805000, 121.6885000),
    (1, 4, 'Brgy. Poblacion', 14.1780000, 121.6910000);

INSERT IGNORE INTO schedule
    (id, route_id, day_name, collection_time, status) VALUES
    (1, 1, 'Monday', '8:00 AM - 11:00 AM', 'active'),
    (2, 1, 'Wednesday', '8:00 AM - 11:00 AM', 'active'),
    (3, 1, 'Friday', '8:00 AM - 11:00 AM', 'active'),
    (4, 2, 'Tuesday', '9:00 AM - 12:00 PM', 'active'),
    (5, 2, 'Thursday', '9:00 AM - 12:00 PM', 'active'),
    (6, 3, 'Monday', '1:00 PM - 4:00 PM', 'inactive');
