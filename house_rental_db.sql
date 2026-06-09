-- Database: house_rental_db
CREATE DATABASE IF NOT EXISTS house_rental_db;
USE house_rental_db;

CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE
);

INSERT INTO admins (admin_id, name, username, password, email) VALUES
(1, 'Admin User', 'admin', 'admin123', 'admin@houserental.com'),
(2, 'ADMIN USER', 'NIMMU', 'NIM@12', 'nim12@gmail.com');

CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL
);

INSERT INTO categories (category_name) VALUES
('Apartment'),
('House'),
('Villa'),
('Studio'),
('Penthouse');

CREATE TABLE IF NOT EXISTS properties (
    property_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    owner_name VARCHAR(100) NOT NULL,
    rent DECIMAL(10,2) NOT NULL,
    category_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'Available',
    image VARCHAR(255) DEFAULT NULL,
    bedrooms INT DEFAULT 1,
    bathrooms INT DEFAULT 1,
    area_sqft INT DEFAULT NULL,
    description TEXT,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tenants (
    tenant_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    contact VARCHAR(20) NOT NULL,
    aadhar_number VARCHAR(20) DEFAULT NULL,
    emergency_contact VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS leases (
    lease_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    tenant_id INT NOT NULL,
    property_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    monthly_rent DECIMAL(10,2) NOT NULL,
    security_deposit DECIMAL(10,2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS maintenance_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    property_id INT NOT NULL,
    tenant_id INT NOT NULL,
    description TEXT NOT NULL,
    request_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'Pending',
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    lease_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    invoice_no VARCHAR(20) NOT NULL,
    payment_type VARCHAR(50) DEFAULT 'Other',
    notes TEXT,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE CASCADE,
    FOREIGN KEY (lease_id) REFERENCES leases(lease_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS rent_collection (
    rent_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    lease_id INT NOT NULL,
    month VARCHAR(20) NOT NULL,
    year INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'Cash',
    status VARCHAR(20) DEFAULT 'Paid',
    receipt_no VARCHAR(20) NOT NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE CASCADE,
    FOREIGN KEY (lease_id) REFERENCES leases(lease_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tenant_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    tenant_id INT NOT NULL,
    property_id INT NOT NULL,
    lease_id INT NOT NULL,
    move_in_date DATE NOT NULL,
    move_out_date DATE,
    total_rent_paid DECIMAL(10,2) DEFAULT 0,
    notes TEXT,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS monthly_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    month VARCHAR(20) NOT NULL,
    year INT NOT NULL,
    total_rent_collected DECIMAL(10,2) DEFAULT 0,
    total_maintenance_cost DECIMAL(10,2) DEFAULT 0,
    total_properties INT DEFAULT 0,
    occupied_properties INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE CASCADE
);

INSERT INTO properties (admin_id, address, city, owner_name, rent, category_id, status, image, bedrooms, bathrooms, area_sqft, description) VALUES
(1, '12 Green Park Road', 'Bangalore', 'Rajesh Kumar', 18000.00, 1, 'Rented', NULL, 2, 2, 950, 'Two bedroom apartment near metro station.'),
(1, '45 Lake View Colony', 'Mysore', 'Anita Sharma', 25000.00, 2, 'Rented', NULL, 3, 2, 1400, 'Independent house with parking and garden.'),
(1, '78 Royal Street', 'Hyderabad', 'Vikram Reddy', 45000.00, 3, 'Available', NULL, 4, 3, 2200, 'Premium villa in a gated community.'),
(1, '21 Studio Heights', 'Pune', 'Meera Patil', 12000.00, 4, 'Rented', NULL, 1, 1, 550, 'Compact studio apartment for working professionals.'),
(1, '90 Skyline Towers', 'Mumbai', 'Amit Verma', 60000.00, 5, 'Available', NULL, 3, 3, 1800, 'Penthouse with city view and modern facilities.');

INSERT INTO tenants (admin_id, full_name, email, contact, aadhar_number, emergency_contact) VALUES
(1, 'Rahul Mehta', 'rahul.mehta@example.com', '9876543210', '1234-5678-9012', '9123456780'),
(1, 'Priya Nair', 'priya.nair@example.com', '9988776655', '2345-6789-0123', '9234567890'),
(1, 'Karan Singh', 'karan.singh@example.com', '9090909090', '3456-7890-1234', '9345678901'),
(1, 'Sneha Iyer', 'sneha.iyer@example.com', '9191919191', '4567-8901-2345', '9456789012'),
(1, 'Arjun Das', 'arjun.das@example.com', '9292929292', '5678-9012-3456', '9567890123');

INSERT INTO leases (admin_id, tenant_id, property_id, start_date, end_date, monthly_rent, security_deposit, status) VALUES
(1, 1, 1, '2026-01-01', '2026-12-31', 18000.00, 36000.00, 'Active'),
(1, 2, 2, '2026-02-01', '2027-01-31', 25000.00, 50000.00, 'Active'),
(1, 3, 4, '2026-03-01', '2027-02-28', 12000.00, 24000.00, 'Active'),
(1, 4, 5, '2025-06-01', '2026-05-31', 55000.00, 110000.00, 'Terminated');

INSERT INTO maintenance_requests (admin_id, property_id, tenant_id, description, request_date, status) VALUES
(1, 1, 1, 'Water leakage in bathroom tap.', '2026-06-02', 'Pending'),
(1, 2, 2, 'Kitchen light is not working.', '2026-06-04', 'Resolved'),
(1, 4, 3, 'Air conditioner cooling issue.', '2026-06-05', 'Pending'),
(1, 5, 4, 'Main door lock replacement required.', '2026-05-15', 'Resolved');

INSERT INTO payments (admin_id, lease_id, amount, payment_date, invoice_no, payment_type, notes) VALUES
(1, 1, 36000.00, '2026-01-01', 'INV001', 'Security Deposit', 'Security deposit received during lease creation.'),
(1, 2, 50000.00, '2026-02-01', 'INV002', 'Security Deposit', 'Security deposit received from tenant.'),
(1, 3, 24000.00, '2026-03-01', 'INV003', 'Security Deposit', 'Security deposit for studio apartment.'),
(1, 1, 1500.00, '2026-06-03', 'INV004', 'Maintenance Fee', 'Bathroom tap repair charge.');

INSERT INTO rent_collection (admin_id, lease_id, month, year, amount, payment_date, payment_method, status, receipt_no) VALUES
(1, 1, 'April', 2026, 18000.00, '2026-04-05', 'UPI', 'Paid', 'RCP001'),
(1, 1, 'May', 2026, 18000.00, '2026-05-05', 'UPI', 'Paid', 'RCP002'),
(1, 1, 'June', 2026, 18000.00, '2026-06-05', 'UPI', 'Paid', 'RCP003'),
(1, 2, 'May', 2026, 25000.00, '2026-05-07', 'Bank Transfer', 'Paid', 'RCP004'),
(1, 2, 'June', 2026, 25000.00, '2026-06-07', 'Bank Transfer', 'Paid', 'RCP005'),
(1, 3, 'June', 2026, 12000.00, '2026-06-06', 'Cash', 'Paid', 'RCP006');

INSERT INTO tenant_history (admin_id, tenant_id, property_id, lease_id, move_in_date, move_out_date, total_rent_paid, notes) VALUES
(1, 1, 1, 1, '2026-01-01', NULL, 54000.00, 'Current tenant with active lease.'),
(1, 2, 2, 2, '2026-02-01', NULL, 50000.00, 'Current tenant with active lease.'),
(1, 3, 4, 3, '2026-03-01', NULL, 12000.00, 'Current tenant with active lease.'),
(1, 4, 5, 4, '2025-06-01', '2026-05-31', 660000.00, 'Lease completed and tenant moved out.');

INSERT INTO monthly_reports (admin_id, month, year, total_rent_collected, total_maintenance_cost, total_properties, occupied_properties) VALUES
(1, 'April', 2026, 18000.00, 0.00, 5, 3),
(1, 'May', 2026, 43000.00, 2500.00, 5, 3),
(1, 'June', 2026, 55000.00, 1500.00, 5, 3);
