-- TechCorp Solutions Database Schema
-- MySQL version for local XAMPP development

CREATE DATABASE IF NOT EXISTS techcorp_db;
USE techcorp_db;

-- Contacts table
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    company VARCHAR(255),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    INDEX idx_email (email),
    INDEX idx_created_at (created_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Testimonials table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    company VARCHAR(255),
    position VARCHAR(255),
    testimonial TEXT NOT NULL,
    rating INT DEFAULT 5 CHECK (rating >= 1 AND rating <= 5),
    image_url VARCHAR(500),
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_featured (is_featured),
    INDEX idx_active (is_active),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample testimonials
INSERT INTO testimonials (name, company, position, testimonial, rating, is_featured) VALUES
('Sarah Johnson', 'Digital Marketing Pro', 'CEO', 'TechCorp Solutions transformed our business with their innovative web platform. The team\'s expertise and dedication exceeded our expectations.', 5, TRUE),
('Michael Chen', 'StartupVenture Inc.', 'CTO', 'Outstanding mobile app development. They delivered a high-quality solution on time and within budget.', 5, TRUE),
('Emily Rodriguez', 'HealthTech Solutions', 'Product Manager', 'The cloud migration services were seamless. Our infrastructure is now more scalable and secure than ever.', 5, FALSE),
('David Wilson', 'E-commerce Plus', 'Founder', 'Professional team with excellent communication. They understood our requirements perfectly and delivered beyond expectations.', 5, TRUE),
('Lisa Thompson', 'Tech Innovations', 'VP Engineering', 'Reliable, efficient, and creative solutions. TechCorp is our go-to partner for all development needs.', 5, FALSE);

-- Services table (optional)
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    features JSON,
    price_range VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample services
INSERT INTO services (title, description, icon, features, price_range, sort_order) VALUES
('Web Development', 'Custom web applications built with modern frameworks and responsive design principles.', 'fas fa-code', '["Responsive Design", "Modern Frameworks", "Database Integration", "SEO Optimization"]', '$2,000 - $10,000', 1),
('Mobile Development', 'Native and cross-platform mobile applications for iOS and Android devices.', 'fas fa-mobile-alt', '["iOS & Android", "Cross-platform", "User-friendly UI/UX", "App Store Optimization"]', '$5,000 - $25,000', 2),
('Cloud Solutions', 'Scalable cloud infrastructure and migration services for modern businesses.', 'fas fa-cloud', '["Cloud Migration", "Infrastructure Setup", "24/7 Support", "Auto Scaling"]', '$1,000 - $15,000', 3);
