<?php
/**
 * Database Configuration for TechCorp Solutions
 * Railway-compatible database connection with environment variables
 */

// Load environment variables from .env file if it exists
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, '"\''); // Remove quotes
            
            if (!empty($name) && (!array_key_exists($name, $_ENV) && !getenv($name))) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Load .env file
loadEnv(__DIR__ . '/../.env');

class Database {
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        $connected = false;
        
        // Try Railway DATABASE_URL first
        $databaseUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');
        if ($databaseUrl && !$connected) {
            try {
                $dbParts = parse_url($databaseUrl);
                $dsn = sprintf(
                    "pgsql:host=%s;port=%d;dbname=%s;sslmode=require",
                    $dbParts['host'],
                    $dbParts['port'],
                    ltrim($dbParts['path'], '/')
                );
                
                $this->connection = new PDO($dsn, $dbParts['user'], $dbParts['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 30,
                ]);
                
                $connected = true;
                error_log("Connected using Railway DATABASE_URL");
                
            } catch (PDOException $e) {
                error_log("Railway DATABASE_URL failed: " . $e->getMessage());
            }
        }
        
        // Try Supabase Transaction Pooler if Railway failed
        if (!$connected) {
            try {
                $host = $_ENV['SUPABASE_HOST'] ?? getenv('SUPABASE_HOST') ?? 'aws-1-ap-south-1.pooler.supabase.com';
                $port = $_ENV['SUPABASE_PORT'] ?? getenv('SUPABASE_PORT') ?? '6543';
                $database = $_ENV['SUPABASE_DATABASE'] ?? getenv('SUPABASE_DATABASE') ?? 'postgres';
                $username = $_ENV['SUPABASE_USERNAME'] ?? getenv('SUPABASE_USERNAME') ?? 'postgres.brdavdukxvilpdzgbsqd';
                $password = $_ENV['SUPABASE_PASSWORD'] ?? getenv('SUPABASE_PASSWORD') ?? '1f73m7bxpj1i6iaQ';
                
                $dsn = "pgsql:host={$host};port={$port};dbname={$database};sslmode=require";
                
                $this->connection = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 30,
                ]);
                
                $connected = true;
                error_log("Connected using Supabase Transaction Pooler at: {$host}:{$port}");
                
            } catch (PDOException $e) {
                error_log("Supabase Transaction Pooler failed: " . $e->getMessage());
            }
        }
        
        // Fall back to SQLite only if both PostgreSQL options failed
        if (!$connected) {
            try {
                $sqliteFile = __DIR__ . '/../data/database.sqlite';
                $dataDir = dirname($sqliteFile);
                
                if (!file_exists($dataDir)) {
                    mkdir($dataDir, 0755, true);
                }
                
                $this->connection = new PDO("sqlite:" . $sqliteFile, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                
                $this->connection->exec('PRAGMA foreign_keys = ON');
                $this->connection->exec('PRAGMA busy_timeout = 30000');
                
                $connected = true;
                error_log("Fell back to SQLite database at: " . $sqliteFile);
                
            } catch (PDOException $e) {
                error_log("SQLite fallback also failed: " . $e->getMessage());
                throw new Exception("All database connection methods failed");
            }
        }
        
        if (!$connected) {
            throw new Exception("Unable to establish database connection");
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function getDriverName() {
        return $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
    }
    
    public function getConnectionInfo() {
        $driver = $this->getDriverName();
        $info = ['driver' => $driver];
        
        if ($driver === 'sqlite') {
            $stmt = $this->connection->query("PRAGMA database_list");
            $databases = $stmt->fetchAll();
            foreach ($databases as $db) {
                if ($db['name'] === 'main') {
                    $info['file'] = $db['file'];
                    break;
                }
            }
        } else {
            // For PostgreSQL, get connection info
            $info['host'] = 'Connected to PostgreSQL';
        }
        
        return $info;
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
    
    public function createTables() {
        // Check if we're using SQLite or PostgreSQL
        $driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        if ($driver === 'sqlite') {
            $tables = [
                // Contacts table (SQLite)
                "CREATE TABLE IF NOT EXISTS contacts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    phone VARCHAR(20),
                    company VARCHAR(255),
                    subject VARCHAR(255),
                    message TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    status VARCHAR(20) DEFAULT 'new' CHECK (status IN ('new', 'read', 'replied'))
                )",
                
                // Testimonials table (SQLite)
                "CREATE TABLE IF NOT EXISTS testimonials (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(255) NOT NULL,
                    company VARCHAR(255),
                    position VARCHAR(255),
                    testimonial TEXT NOT NULL,
                    rating INTEGER DEFAULT 5 CHECK (rating >= 1 AND rating <= 5),
                    image_url VARCHAR(500),
                    is_featured BOOLEAN DEFAULT 0,
                    is_active BOOLEAN DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )"
            ];
        } else {
            $tables = [
                // Contacts table (PostgreSQL)
                "CREATE TABLE IF NOT EXISTS contacts (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    phone VARCHAR(20),
                    company VARCHAR(255),
                    subject VARCHAR(255),
                    message TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    status VARCHAR(20) DEFAULT 'new' CHECK (status IN ('new', 'read', 'replied'))
                )",
                
                // Testimonials table (PostgreSQL)
                "CREATE TABLE IF NOT EXISTS testimonials (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    company VARCHAR(255),
                    position VARCHAR(255),
                    testimonial TEXT NOT NULL,
                    rating INTEGER DEFAULT 5 CHECK (rating >= 1 AND rating <= 5),
                    image_url VARCHAR(500),
                    is_featured BOOLEAN DEFAULT FALSE,
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )"
            ];
        }
        
        foreach ($tables as $sql) {
            try {
                $this->connection->exec($sql);
            } catch (PDOException $e) {
                throw new Exception("Failed to create table: " . $e->getMessage());
            }
        }
        
        // Insert sample testimonials
        $this->insertSampleData();
    }
    
    private function insertSampleData() {
        $testimonials = [
            [
                'name' => 'Sarah Johnson',
                'company' => 'Digital Marketing Pro',
                'position' => 'CEO',
                'testimonial' => 'TechCorp Solutions transformed our business with their innovative web platform. The team\'s expertise and dedication exceeded our expectations.',
                'rating' => 5,
                'is_featured' => 1
            ],
            [
                'name' => 'Michael Chen',
                'company' => 'StartupVenture Inc.',
                'position' => 'CTO',
                'testimonial' => 'Outstanding mobile app development. They delivered a high-quality solution on time and within budget.',
                'rating' => 5,
                'is_featured' => 1
            ],
            [
                'name' => 'Emily Rodriguez',
                'company' => 'HealthTech Solutions',
                'position' => 'Product Manager',
                'testimonial' => 'The cloud migration services were seamless. Our infrastructure is now more scalable and secure than ever.',
                'rating' => 5,
                'is_featured' => 0
            ]
        ];
        
        foreach ($testimonials as $testimonial) {
            try {
                // Check if testimonial already exists
                $existing = $this->fetchOne("SELECT id FROM testimonials WHERE name = ? AND company = ?", 
                                           [$testimonial['name'], $testimonial['company']]);
                if (!$existing) {
                    $this->insert('testimonials', $testimonial);
                }
            } catch (Exception $e) {
                // Testimonial might already exist or table might not be ready, skip
                error_log("Failed to insert sample testimonial: " . $e->getMessage());
            }
        }
    }
}

// Initialize database connection (but don't create global variable here)
// Each file should instantiate its own database connection
?>
