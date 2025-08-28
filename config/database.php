<?php
/**
 * Database Configuration for TechCorp Solutions
 * 
 * This file contains database connection settings and helper functions.
 * Make sure to update the credentials according to your hosting environment.
 */

class Database {
    private $host;
    private $database;
    private $username;
    private $password;
    private $connection;
    
    public function __construct() {
        // Database configuration - Update these settings for your environment
        $this->host = 'localhost';
        $this->database = 'techcorp_db';
        $this->username = 'root';
        $this->password = '';
        
        // For production/hosting, uncomment and use these settings:
        // $this->host = 'your_host';
        // $this->database = 'your_database';
        // $this->username = 'your_username';
        // $this->password = 'your_password';
        
        $this->connect();
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function insert($table, $data) {
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = ':' . implode(', :', $fields);
        $fieldList = implode(', ', $fields);
        
        $sql = "INSERT INTO {$table} ({$fieldList}) VALUES ({$placeholders})";
        return $this->query($sql, array_combine($fields, $values));
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $fields = array_keys($data);
        $setClause = implode(' = ?, ', $fields) . ' = ?';
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);
        
        return $this->query($sql, $params);
    }
    
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params);
    }
    
    public function createTables() {
        $tables = [
            // Contacts table
            "CREATE TABLE IF NOT EXISTS contacts (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Testimonials table
            "CREATE TABLE IF NOT EXISTS testimonials (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Services table
            "CREATE TABLE IF NOT EXISTS services (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Projects table
            "CREATE TABLE IF NOT EXISTS projects (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                technologies JSON,
                image_url VARCHAR(500),
                project_url VARCHAR(500),
                github_url VARCHAR(500),
                client_name VARCHAR(255),
                completion_date DATE,
                is_featured BOOLEAN DEFAULT FALSE,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_featured (is_featured),
                INDEX idx_active (is_active),
                INDEX idx_completion_date (completion_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Blog posts table
            "CREATE TABLE IF NOT EXISTS blog_posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE NOT NULL,
                excerpt TEXT,
                content LONGTEXT,
                author VARCHAR(255),
                featured_image VARCHAR(500),
                tags JSON,
                is_published BOOLEAN DEFAULT FALSE,
                published_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_slug (slug),
                INDEX idx_published (is_published),
                INDEX idx_published_at (published_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Newsletter subscribers table
            "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) UNIQUE NOT NULL,
                name VARCHAR(255),
                is_active BOOLEAN DEFAULT TRUE,
                subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                unsubscribed_at TIMESTAMP NULL,
                INDEX idx_email (email),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];
        
        foreach ($tables as $sql) {
            try {
                $this->connection->exec($sql);
            } catch (PDOException $e) {
                throw new Exception("Failed to create table: " . $e->getMessage());
            }
        }
        
        // Insert sample data
        $this->insertSampleData();
    }
    
    private function insertSampleData() {
        // Sample testimonials
        $testimonials = [
            [
                'name' => 'Sarah Johnson',
                'company' => 'Digital Marketing Pro',
                'position' => 'CEO',
                'testimonial' => 'TechCorp Solutions transformed our business with their innovative web platform. The team\'s expertise and dedication exceeded our expectations.',
                'rating' => 5,
                'is_featured' => true
            ],
            [
                'name' => 'Michael Chen',
                'company' => 'StartupVenture Inc.',
                'position' => 'CTO',
                'testimonial' => 'Outstanding mobile app development. They delivered a high-quality solution on time and within budget.',
                'rating' => 5,
                'is_featured' => true
            ],
            [
                'name' => 'Emily Rodriguez',
                'company' => 'HealthTech Solutions',
                'position' => 'Product Manager',
                'testimonial' => 'The cloud migration services were seamless. Our infrastructure is now more scalable and secure than ever.',
                'rating' => 5,
                'is_featured' => false
            ]
        ];
        
        foreach ($testimonials as $testimonial) {
            try {
                $this->insert('testimonials', $testimonial);
            } catch (Exception $e) {
                // Testimonial might already exist, skip
            }
        }
        
        // Sample services
        $services = [
            [
                'title' => 'Web Development',
                'description' => 'Custom web applications built with modern frameworks and responsive design principles.',
                'icon' => 'fas fa-code',
                'features' => json_encode(['Responsive Design', 'Modern Frameworks', 'Database Integration']),
                'price_range' => '$2,000 - $10,000'
            ],
            [
                'title' => 'Mobile Development',
                'description' => 'Native and cross-platform mobile applications for iOS and Android devices.',
                'icon' => 'fas fa-mobile-alt',
                'features' => json_encode(['iOS & Android', 'Cross-platform', 'User-friendly UI/UX']),
                'price_range' => '$5,000 - $25,000'
            ],
            [
                'title' => 'Cloud Solutions',
                'description' => 'Scalable cloud infrastructure and migration services for modern businesses.',
                'icon' => 'fas fa-cloud',
                'features' => json_encode(['Cloud Migration', 'Infrastructure Setup', '24/7 Support']),
                'price_range' => '$1,000 - $15,000'
            ]
        ];
        
        foreach ($services as $service) {
            try {
                $this->insert('services', $service);
            } catch (Exception $e) {
                // Service might already exist, skip
            }
        }
    }
    
    public function getLastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollBack();
    }
}

// Helper functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateSlug($string) {
    $slug = strtolower($string);
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

function formatDate($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Initialize database connection
try {
    $db = new Database();
    // Uncomment the line below to create tables (run once)
    // $db->createTables();
} catch (Exception $e) {
    die("Database initialization failed: " . $e->getMessage());
}
?>
