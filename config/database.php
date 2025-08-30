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
    private $preparedStatements = []; // Cache for prepared statements
    
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
                    PDO::ATTR_PERSISTENT => true,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                    PDO::ATTR_CASE => PDO::CASE_NATURAL,
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
                    PDO::ATTR_PERSISTENT => true,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                    PDO::ATTR_CASE => PDO::CASE_NATURAL,
                ]);
                
                $connected = true;
                error_log("Connected using Supabase Transaction Pooler");
                
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
                error_log("Fell back to SQLite database");
                
            } catch (PDOException $e) {
                error_log("SQLite fallback also failed: " . $e->getMessage());
                throw new Exception("All database connection methods failed");
            }
        }
        
        if (!$connected) {
            throw new Exception("Unable to establish database connection");
        }
        
        $this->createTables();
    }
    
    public function createTables() {
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
                
                // Add indexes for better performance (SQLite)
                "CREATE INDEX IF NOT EXISTS idx_contacts_status ON contacts(status)",
                "CREATE INDEX IF NOT EXISTS idx_contacts_created_at ON contacts(created_at)",
                "CREATE INDEX IF NOT EXISTS idx_contacts_email ON contacts(email)",
                
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
                )",
                
                // Add indexes for testimonials (SQLite)
                "CREATE INDEX IF NOT EXISTS idx_testimonials_active ON testimonials(is_active)",
                "CREATE INDEX IF NOT EXISTS idx_testimonials_featured ON testimonials(is_featured)",
                "CREATE INDEX IF NOT EXISTS idx_testimonials_rating ON testimonials(rating)",
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
                
                // Add indexes for better performance (PostgreSQL)
                "CREATE INDEX IF NOT EXISTS idx_contacts_status ON contacts(status)",
                "CREATE INDEX IF NOT EXISTS idx_contacts_created_at ON contacts(created_at)",
                "CREATE INDEX IF NOT EXISTS idx_contacts_email ON contacts(email)",
                
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
                )",
                
                // Add indexes for testimonials (PostgreSQL)
                "CREATE INDEX IF NOT EXISTS idx_testimonials_active ON testimonials(is_active)",
                "CREATE INDEX IF NOT EXISTS idx_testimonials_featured ON testimonials(is_featured)",
                "CREATE INDEX IF NOT EXISTS idx_testimonials_rating ON testimonials(rating)",
            ];
        }
        
        foreach ($tables as $sql) {
            try {
                $this->connection->exec($sql);
            } catch (PDOException $e) {
                throw new Exception("Failed to create table: " . $e->getMessage());
            }
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
            $info['host'] = 'Connected to PostgreSQL';
        }
        
        return $info;
    }
    
    public function query($sql, $params = []) {
        try {
            // Use cached prepared statement if available
            if (!isset($this->preparedStatements[$sql])) {
                $this->preparedStatements[$sql] = $this->connection->prepare($sql);
            }
            $stmt = $this->preparedStatements[$sql];
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }
    
    public function execute($sql, $params = []) {
        try {
            // Use cached prepared statement if available
            if (!isset($this->preparedStatements[$sql])) {
                $this->preparedStatements[$sql] = $this->connection->prepare($sql);
            }
            $stmt = $this->preparedStatements[$sql];
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception("Execute failed: " . $e->getMessage());
        }
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
}

// Create a global database instance
try {
    $db = new Database();
} catch (Exception $e) {
    error_log("Database initialization failed: " . $e->getMessage());
    die("Database connection failed. Please check your configuration.");
}
