-- Database: inventory_db

CREATE DATABASE IF NOT EXISTS inventory_db;
USE inventory_db;

-- Table: device_types (master data jenis device)
CREATE TABLE IF NOT EXISTS device_types (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) UNIQUE NOT NULL,
    type_code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: brands (master data merk dengan kategori)
CREATE TABLE IF NOT EXISTS brands (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    brand_name VARCHAR(100) NOT NULL,
    brand_code VARCHAR(20) UNIQUE NOT NULL,
    category ENUM('Access Point', 'Switch', 'Router') NOT NULL,
    description TEXT,
    website VARCHAR(255),
    logo VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_brand_category (brand_name, category),
    INDEX idx_category (category),
    INDEX idx_active (is_active)
);

-- Table: devices (main table)
CREATE TABLE IF NOT EXISTS devices (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    device_type_id INT(11) NOT NULL,
    brand_id INT(11) NOT NULL,
    model VARCHAR(100) NOT NULL,
    serial_number VARCHAR(100) UNIQUE NOT NULL,
    quantity INT(11) NOT NULL DEFAULT 1,
    status ENUM('idle', 'terpasang', 'maintenance', 'retired') NOT NULL DEFAULT 'idle',
    location VARCHAR(255) DEFAULT NULL,
    purchase_date DATE,
    warranty_expiry DATE,
    price DECIMAL(15,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (device_type_id) REFERENCES device_types(id) ON DELETE CASCADE,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE,
    INDEX idx_device_type (device_type_id),
    INDEX idx_brand (brand_id),
    INDEX idx_status (status),
    INDEX idx_serial (serial_number)
);

-- Table: users (user authentication data)
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'view_only') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default users
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$Gd0mYM94jll482IduWD.c.fa..3jw7oZOR3fW09NT6SQO1tdJx5m6', 'admin'),
('viewonly', '$2y$10$n7/Kdjno1wUdGCxBPz/2K.RHjFY06vnOKKR9DXe1sDZSH8PhdbyOm', 'view_only');

-- Insert device types
INSERT INTO device_types (type_name, type_code, description, icon) VALUES
('Access Point', 'AP', 'Wireless Access Point untuk jaringan WiFi', '📶'),
('Switch', 'SW', 'Network Switch untuk koneksi perangkat', '🔀'),
('Router', 'RT', 'Router untuk koneksi internet dan routing', '🌐');

-- Insert brands with category separation
-- ACCESS POINT BRANDS
INSERT INTO brands (brand_name, brand_code, category, description, website) VALUES
('Cisco', 'CIS-AP', 'Access Point', 'Cisco Systems - Enterprise Access Points', 'https://www.cisco.com'),
('Aruba', 'ARU-AP', 'Access Point', 'Aruba Networks - Enterprise Wireless Solutions', 'https://www.arubanetworks.com'),
('Ubiquiti', 'UBI-AP', 'Access Point', 'Ubiquiti Networks - Wireless Access Points', 'https://www.ui.com'),
('TP-Link', 'TPL-AP', 'Access Point', 'TP-Link - Access Point Solutions', 'https://www.tp-link.com'),
('Huawei', 'HUA-AP', 'Access Point', 'Huawei - Enterprise Access Points', 'https://www.huawei.com'),
('MikroTik', 'MIK-AP', 'Access Point', 'MikroTik - Wireless Access Points', 'https://mikrotik.com'),
('Ruckus', 'RUC-AP', 'Access Point', 'Ruckus Wireless - Enterprise WiFi', 'https://www.ruckuswireless.com'),
('Juniper', 'JUN-AP', 'Access Point', 'Juniper Networks - Wireless Solutions', 'https://www.juniper.net'),
('Cambium', 'CAM-AP', 'Access Point', 'Cambium Networks - Wireless Access Points', 'https://www.cambiumnetworks.com'),
('Grandstream', 'GRA-AP', 'Access Point', 'Grandstream - Access Point Solutions', 'https://www.grandstream.com'),
('D-Link', 'DLK-AP', 'Access Point', 'D-Link - Wireless Access Points', 'https://www.dlink.com'),
('Zyxel', 'ZYX-AP', 'Access Point', 'Zyxel - Networking Solutions', 'https://www.zyxel.com'),

-- SWITCH BRANDS
('Cisco', 'CIS-SW', 'Switch', 'Cisco Systems - Enterprise Switches', 'https://www.cisco.com'),
('HPE', 'HPE-SW', 'Switch', 'Hewlett Packard Enterprise - Networking Switches', 'https://www.hpe.com'),
('Juniper', 'JUN-SW', 'Switch', 'Juniper Networks - Switching Solutions', 'https://www.juniper.net'),
('Dell', 'DEL-SW', 'Switch', 'Dell Technologies - Networking Switches', 'https://www.dell.com'),
('Huawei', 'HUA-SW', 'Switch', 'Huawei - Enterprise Switches', 'https://www.huawei.com'),
('ZTE', 'ZTE-SW', 'Switch', 'ZTE Corporation - Telecommunications Switches', 'https://www.zte.com.cn'),
('Alcatel-Lucent', 'ALC-SW', 'Switch', 'Alcatel-Lucent Enterprise - Networking', 'https://www.al-enterprise.com'),
('Extreme', 'EXT-SW', 'Switch', 'Extreme Networks - Switching Solutions', 'https://www.extremenetworks.com'),
('TP-Link', 'TPL-SW', 'Switch', 'TP-Link - Switch Solutions', 'https://www.tp-link.com'),
('D-Link', 'DLK-SW', 'Switch', 'D-Link - Network Switches', 'https://www.dlink.com'),
('Netgear', 'NET-SW', 'Switch', 'Netgear - Networking Switches', 'https://www.netgear.com'),
('MikroTik', 'MIK-SW', 'Switch', 'MikroTik - Switch Solutions', 'https://mikrotik.com'),

-- ROUTER BRANDS
('Cisco', 'CIS-RT', 'Router', 'Cisco Systems - Enterprise Routers', 'https://www.cisco.com'),
('MikroTik', 'MIK-RT', 'Router', 'MikroTik - Router Solutions', 'https://mikrotik.com'),
('Huawei', 'HUA-RT', 'Router', 'Huawei - Enterprise Routers', 'https://www.huawei.com'),
('Juniper', 'JUN-RT', 'Router', 'Juniper Networks - Routing Solutions', 'https://www.juniper.net'),
('TP-Link', 'TPL-RT', 'Router', 'TP-Link - Router Solutions', 'https://www.tp-link.com'),
('D-Link', 'DLK-RT', 'Router', 'D-Link - Router Solutions', 'https://www.dlink.com'),
('Asus', 'ASU-RT', 'Router', 'ASUS - Networking Products', 'https://www.asus.com'),
('Netgear', 'NET-RT', 'Router', 'Netgear - Router Solutions', 'https://www.netgear.com'),
('Zyxel', 'ZYX-RT', 'Router', 'Zyxel - Router Solutions', 'https://www.zyxel.com'),
('Ubiquiti', 'UBI-RT', 'Router', 'Ubiquiti - EdgeRouter & UniFi Gateway', 'https://www.ui.com'),
('HPE', 'HPE-RT', 'Router', 'HPE - Routing Solutions', 'https://www.hpe.com'),
('Fortinet', 'FOR-RT', 'Router', 'Fortinet - Security Routers', 'https://www.fortinet.com');

-- Insert sample data
INSERT INTO devices (device_type_id, brand_id, model, serial_number, quantity, status, location, purchase_date, warranty_expiry, price, notes) VALUES
-- Access Points
(1, 3, 'UniFi U6-LR', 'AP-UBI-2024-001', 5, 'idle', NULL, '2024-01-15', '2026-01-15', 2500000, 'Access Point WiFi 6 Long Range'),
(1, 1, 'Catalyst 9130', 'AP-CIS-2024-002', 3, 'terpasang', 'Ruang Server Lt.2', '2024-02-20', '2027-02-20', 3500000, 'Enterprise WiFi 6 Access Point'),
(1, 2, 'Aruba AP-535', 'AP-ARU-2024-003', 2, 'terpasang', 'Gedung Utama Lt.3', '2024-03-10', '2026-03-10', 2800000, 'High Performance WiFi 6 AP'),
(1, 4, 'EAP670', 'AP-TPL-2024-004', 10, 'idle', NULL, '2024-04-05', '2025-04-05', 1500000, 'Omada WiFi 6 Access Point'),
(1, 7, 'Ruckus R650', 'AP-RUC-2024-005', 2, 'terpasang', 'Ruang Rapat Executive', '2024-05-01', '2027-05-01', 4200000, 'Enterprise WiFi 6 AP'),
(1, 6, 'mANTBox 19', 'AP-MIK-2024-006', 4, 'idle', NULL, '2024-06-15', '2025-06-15', 1200000, 'Outdoor Access Point'),

-- Switches
(2, 13, 'Catalyst 9200', 'SW-CIS-2024-001', 3, 'terpasang', 'Ruang Server Lt.2', '2024-01-20', '2027-01-20', 4500000, 'Enterprise Switch 24 Port'),
(2, 14, 'Aruba 2930F', 'SW-HPE-2024-002', 2, 'idle', NULL, '2024-02-15', '2026-02-15', 3200000, 'Aruba Switch 24 Port PoE'),
(2, 16, 'S5248F', 'SW-DEL-2024-003', 1, 'terpasang', 'Ruang Meeting Lt.3', '2024-03-01', '2026-03-01', 2800000, 'Dell Switch 48 Port'),
(2, 17, 'S6720', 'SW-HUA-2024-004', 4, 'idle', NULL, '2024-04-10', '2025-04-10', 3800000, 'Huawei Cloud Switch'),
(2, 20, 'X440-G2', 'SW-EXT-2024-005', 2, 'terpasang', 'Ruang IT Lt.1', '2024-05-15', '2026-05-15', 2600000, 'Extreme Networks Switch'),
(2, 21, 'TL-SG3428', 'SW-TPL-2024-006', 5, 'idle', NULL, '2024-06-01', '2025-06-01', 1800000, 'TP-Link Switch 24 Port'),

-- Routers
(3, 25, 'ISR 4451', 'RT-CIS-2024-001', 2, 'terpasang', 'Ruang IT Lt.1', '2024-01-10', '2027-01-10', 5500000, 'Cisco ISR Router'),
(3, 26, 'CCR1036', 'RT-MIK-2024-002', 3, 'idle', NULL, '2024-02-25', '2026-02-25', 4200000, 'MikroTik Cloud Core Router'),
(3, 27, 'AR6300', 'RT-HUA-2024-003', 1, 'terpasang', 'Data Center', '2024-03-15', '2026-03-15', 4800000, 'Huawei Enterprise Router'),
(3, 29, 'TL-ER7206', 'RT-TPL-2024-004', 5, 'idle', NULL, '2024-04-20', '2025-04-20', 1800000, 'TP-Link Gigabit Router'),
(3, 34, 'EdgeRouter 4', 'RT-UBI-2024-005', 2, 'terpasang', 'Ruang Server Lt.2', '2024-05-10', '2026-05-10', 3200000, 'Ubiquiti EdgeRouter'),
(3, 32, 'RT-AX88U', 'RT-ASU-2024-006', 3, 'idle', NULL, '2024-06-20', '2025-06-20', 2300000, 'ASUS WiFi 6 Router');

-- Create view for easy reporting
CREATE OR REPLACE VIEW v_device_inventory AS
SELECT 
    d.id,
    dt.type_name AS device_type,
    dt.type_code,
    dt.icon,
    b.brand_name,
    b.brand_code,
    b.category AS brand_category,
    d.model,
    d.serial_number,
    d.quantity,
    d.status,
    d.location,
    d.purchase_date,
    d.warranty_expiry,
    d.price,
    d.notes,
    d.created_at,
    d.updated_at,
    DATEDIFF(d.warranty_expiry, CURDATE()) AS days_remaining_warranty,
    CASE 
        WHEN d.warranty_expiry < CURDATE() THEN 'Expired'
        WHEN DATEDIFF(d.warranty_expiry, CURDATE()) <= 30 THEN 'Soon to Expire'
        ELSE 'Active'
    END AS warranty_status
FROM devices d
JOIN device_types dt ON d.device_type_id = dt.id
JOIN brands b ON d.brand_id = b.id;

-- Create stored procedure untuk report per kategori
DELIMITER //
CREATE PROCEDURE sp_report_by_category(IN category_name VARCHAR(50))
BEGIN
    SELECT 
        b.brand_name,
        COUNT(d.id) AS total_items,
        SUM(d.quantity) AS total_quantity,
        SUM(d.price * d.quantity) AS total_value,
        d.status,
        COUNT(CASE WHEN d.status = 'idle' THEN 1 END) AS idle_count,
        COUNT(CASE WHEN d.status = 'terpasang' THEN 1 END) AS installed_count
    FROM devices d
    JOIN brands b ON d.brand_id = b.id
    WHERE b.category = category_name
    GROUP BY b.brand_name, d.status
    ORDER BY b.brand_name, d.status;
END//
DELIMITER ;

-- Create trigger untuk update warranty status
DELIMITER //
CREATE TRIGGER before_device_update
BEFORE UPDATE ON devices
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END//
DELIMITER ;