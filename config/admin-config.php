<?php
/**
 * Enhanced Admin Configuration
 * Centralized configuration for the admin system
 */

class AdminConfig {
    
    // Database table names
    const TABLES = [
        'contacts' => 'contacts',
        'testimonials' => 'testimonials',
        'services' => 'services',
        'projects' => 'projects',
        'blog_posts' => 'blog_posts',
        'newsletter_subscribers' => 'newsletter_subscribers'
    ];
    
    // Pagination settings
    const PAGINATION = [
        'default_limit' => 20,
        'max_limit' => 100,
        'min_limit' => 5
    ];
    
    // File upload settings
    const UPLOADS = [
        'max_file_size' => 5 * 1024 * 1024, // 5MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'upload_path' => 'uploads/',
        'image_types' => ['jpg', 'jpeg', 'png', 'gif'],
        'max_image_width' => 1920,
        'max_image_height' => 1080
    ];
    
    // Status configurations
    const STATUSES = [
        'contacts' => [
            'new' => ['label' => 'New', 'class' => 'bg-warning', 'icon' => 'fas fa-exclamation'],
            'read' => ['label' => 'Read', 'class' => 'bg-info', 'icon' => 'fas fa-eye'],
            'replied' => ['label' => 'Replied', 'class' => 'bg-success', 'icon' => 'fas fa-reply']
        ],
        'testimonials' => [
            'active' => ['label' => 'Active', 'class' => 'bg-success', 'icon' => 'fas fa-check'],
            'inactive' => ['label' => 'Inactive', 'class' => 'bg-secondary', 'icon' => 'fas fa-times']
        ],
        'services' => [
            'active' => ['label' => 'Active', 'class' => 'bg-success', 'icon' => 'fas fa-check'],
            'inactive' => ['label' => 'Inactive', 'class' => 'bg-secondary', 'icon' => 'fas fa-times']
        ],
        'projects' => [
            'active' => ['label' => 'Active', 'class' => 'bg-success', 'icon' => 'fas fa-check'],
            'inactive' => ['label' => 'Inactive', 'class' => 'bg-secondary', 'icon' => 'fas fa-times'],
            'featured' => ['label' => 'Featured', 'class' => 'bg-warning', 'icon' => 'fas fa-star']
        ],
        'blog_posts' => [
            'published' => ['label' => 'Published', 'class' => 'bg-success', 'icon' => 'fas fa-check'],
            'draft' => ['label' => 'Draft', 'class' => 'bg-warning', 'icon' => 'fas fa-edit'],
            'archived' => ['label' => 'Archived', 'class' => 'bg-secondary', 'icon' => 'fas fa-archive']
        ]
    ];
    
    // Menu configuration for admin sidebar
    const MENU_ITEMS = [
        [
            'id' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'shortcut' => 'Ctrl+1',
            'href' => '#dashboard'
        ],
        [
            'id' => 'contacts',
            'label' => 'Contacts',
            'icon' => 'fas fa-envelope',
            'shortcut' => 'Ctrl+2',
            'href' => '#contacts',
            'badge' => 'new_contacts_count'
        ],
        [
            'id' => 'testimonials',
            'label' => 'Testimonials',
            'icon' => 'fas fa-star',
            'shortcut' => 'Ctrl+3',
            'href' => '#testimonials'
        ],
        [
            'id' => 'services',
            'label' => 'Services',
            'icon' => 'fas fa-cogs',
            'shortcut' => 'Ctrl+4',
            'href' => '#services'
        ],
        [
            'id' => 'projects',
            'label' => 'Projects',
            'icon' => 'fas fa-project-diagram',
            'shortcut' => 'Ctrl+5',
            'href' => '#projects'
        ],
        [
            'id' => 'blog',
            'label' => 'Blog Posts',
            'icon' => 'fas fa-blog',
            'shortcut' => 'Ctrl+6',
            'href' => '#blog'
        ],
        [
            'id' => 'newsletter',
            'label' => 'Newsletter',
            'icon' => 'fas fa-newspaper',
            'shortcut' => 'Ctrl+7',
            'href' => '#newsletter'
        ],
        [
            'id' => 'database',
            'label' => 'Database',
            'icon' => 'fas fa-database',
            'shortcut' => 'Ctrl+8',
            'href' => '#database'
        ]
    ];
    
    // Form field configurations
    const FORM_FIELDS = [
        'contacts' => [
            'name' => ['type' => 'text', 'required' => true, 'max_length' => 100],
            'email' => ['type' => 'email', 'required' => true, 'max_length' => 255],
            'phone' => ['type' => 'tel', 'required' => false, 'max_length' => 20],
            'company' => ['type' => 'text', 'required' => false, 'max_length' => 100],
            'subject' => ['type' => 'text', 'required' => false, 'max_length' => 200],
            'message' => ['type' => 'textarea', 'required' => true, 'max_length' => 2000],
            'status' => ['type' => 'select', 'options' => ['new', 'read', 'replied'], 'default' => 'new']
        ],
        'testimonials' => [
            'name' => ['type' => 'text', 'required' => true, 'max_length' => 100],
            'email' => ['type' => 'email', 'required' => false, 'max_length' => 255],
            'company' => ['type' => 'text', 'required' => false, 'max_length' => 100],
            'position' => ['type' => 'text', 'required' => false, 'max_length' => 100],
            'testimonial' => ['type' => 'textarea', 'required' => true, 'max_length' => 1000],
            'rating' => ['type' => 'number', 'required' => true, 'min' => 1, 'max' => 5],
            'is_featured' => ['type' => 'checkbox', 'default' => false],
            'is_active' => ['type' => 'checkbox', 'default' => true]
        ],
        'services' => [
            'title' => ['type' => 'text', 'required' => true, 'max_length' => 200],
            'description' => ['type' => 'textarea', 'required' => true, 'max_length' => 1000],
            'short_description' => ['type' => 'textarea', 'required' => false, 'max_length' => 300],
            'price' => ['type' => 'number', 'required' => false, 'min' => 0, 'step' => 0.01],
            'currency' => ['type' => 'text', 'required' => false, 'max_length' => 3, 'default' => 'USD'],
            'icon' => ['type' => 'text', 'required' => false, 'max_length' => 50],
            'is_featured' => ['type' => 'checkbox', 'default' => false],
            'is_active' => ['type' => 'checkbox', 'default' => true],
            'sort_order' => ['type' => 'number', 'required' => false, 'min' => 0]
        ],
        'projects' => [
            'title' => ['type' => 'text', 'required' => true, 'max_length' => 200],
            'description' => ['type' => 'textarea', 'required' => true, 'max_length' => 2000],
            'short_description' => ['type' => 'textarea', 'required' => false, 'max_length' => 300],
            'client_name' => ['type' => 'text', 'required' => false, 'max_length' => 100],
            'project_url' => ['type' => 'url', 'required' => false, 'max_length' => 500],
            'technologies' => ['type' => 'text', 'required' => false, 'max_length' => 500],
            'completion_date' => ['type' => 'date', 'required' => false],
            'is_featured' => ['type' => 'checkbox', 'default' => false],
            'is_active' => ['type' => 'checkbox', 'default' => true],
            'sort_order' => ['type' => 'number', 'required' => false, 'min' => 0]
        ],
        'blog_posts' => [
            'title' => ['type' => 'text', 'required' => true, 'max_length' => 200],
            'content' => ['type' => 'textarea', 'required' => true, 'max_length' => 10000],
            'excerpt' => ['type' => 'textarea', 'required' => false, 'max_length' => 500],
            'meta_description' => ['type' => 'textarea', 'required' => false, 'max_length' => 160],
            'tags' => ['type' => 'text', 'required' => false, 'max_length' => 500],
            'category' => ['type' => 'text', 'required' => false, 'max_length' => 100],
            'author' => ['type' => 'text', 'required' => false, 'max_length' => 100],
            'publish_date' => ['type' => 'datetime-local', 'required' => false],
            'is_featured' => ['type' => 'checkbox', 'default' => false],
            'status' => ['type' => 'select', 'options' => ['draft', 'published', 'archived'], 'default' => 'draft']
        ]
    ];
    
    // Validation rules
    const VALIDATION_RULES = [
        'email' => '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
        'phone' => '/^[\+]?[1-9][\d]{0,15}$/',
        'url' => '/^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)$/',
        'slug' => '/^[a-z0-9]+(?:-[a-z0-9]+)*$/'
    ];
    
    // Export formats
    const EXPORT_FORMATS = [
        'csv' => ['label' => 'CSV', 'mime' => 'text/csv', 'extension' => '.csv'],
        'excel' => ['label' => 'Excel', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'extension' => '.xlsx'],
        'json' => ['label' => 'JSON', 'mime' => 'application/json', 'extension' => '.json'],
        'pdf' => ['label' => 'PDF', 'mime' => 'application/pdf', 'extension' => '.pdf']
    ];
    
    // Dashboard widgets configuration
    const DASHBOARD_WIDGETS = [
        [
            'id' => 'contacts_widget',
            'title' => 'Contacts',
            'icon' => 'fas fa-envelope',
            'color' => 'primary',
            'table' => 'contacts',
            'count_field' => 'id',
            'new_condition' => "status = 'new'"
        ],
        [
            'id' => 'testimonials_widget',
            'title' => 'Testimonials',
            'icon' => 'fas fa-star',
            'color' => 'success',
            'table' => 'testimonials',
            'count_field' => 'id',
            'active_condition' => 'is_active = true'
        ],
        [
            'id' => 'services_widget',
            'title' => 'Services',
            'icon' => 'fas fa-cogs',
            'color' => 'info',
            'table' => 'services',
            'count_field' => 'id',
            'active_condition' => 'is_active = true'
        ],
        [
            'id' => 'projects_widget',
            'title' => 'Projects',
            'icon' => 'fas fa-project-diagram',
            'color' => 'warning',
            'table' => 'projects',
            'count_field' => 'id',
            'active_condition' => 'is_active = true'
        ]
    ];
    
    // System settings
    const SYSTEM_SETTINGS = [
        'session_timeout' => 3600, // 1 hour
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
        'backup_retention_days' => 30,
        'log_retention_days' => 90,
        'timezone' => 'UTC',
        'date_format' => 'Y-m-d H:i:s',
        'items_per_page' => 20,
        'max_bulk_operations' => 1000
    ];
    
    /**
     * Get table name
     */
    public static function getTable($table) {
        return self::TABLES[$table] ?? null;
    }
    
    /**
     * Get pagination settings
     */
    public static function getPaginationSettings() {
        return self::PAGINATION;
    }
    
    /**
     * Get upload settings
     */
    public static function getUploadSettings() {
        return self::UPLOADS;
    }
    
    /**
     * Get status configuration for a table
     */
    public static function getStatuses($table) {
        return self::STATUSES[$table] ?? [];
    }
    
    /**
     * Get menu items
     */
    public static function getMenuItems() {
        return self::MENU_ITEMS;
    }
    
    /**
     * Get form fields for a table
     */
    public static function getFormFields($table) {
        return self::FORM_FIELDS[$table] ?? [];
    }
    
    /**
     * Get validation rules
     */
    public static function getValidationRules() {
        return self::VALIDATION_RULES;
    }
    
    /**
     * Get export formats
     */
    public static function getExportFormats() {
        return self::EXPORT_FORMATS;
    }
    
    /**
     * Get dashboard widgets
     */
    public static function getDashboardWidgets() {
        return self::DASHBOARD_WIDGETS;
    }
    
    /**
     * Get system settings
     */
    public static function getSystemSettings() {
        return self::SYSTEM_SETTINGS;
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file) {
        $errors = [];
        
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'No file uploaded or invalid file';
            return $errors;
        }
        
        // Check file size
        if ($file['size'] > self::UPLOADS['max_file_size']) {
            $errors[] = 'File size exceeds maximum allowed size of ' . 
                       number_format(self::UPLOADS['max_file_size'] / 1024 / 1024, 1) . 'MB';
        }
        
        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::UPLOADS['allowed_types'])) {
            $errors[] = 'File type not allowed. Allowed types: ' . 
                       implode(', ', self::UPLOADS['allowed_types']);
        }
        
        // Check image dimensions if it's an image
        if (in_array($extension, self::UPLOADS['image_types'])) {
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
                
                if ($width > self::UPLOADS['max_image_width'] || 
                    $height > self::UPLOADS['max_image_height']) {
                    $errors[] = "Image dimensions exceed maximum allowed size of " . 
                               self::UPLOADS['max_image_width'] . "x" . 
                               self::UPLOADS['max_image_height'] . " pixels";
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate form data
     */
    public static function validateFormData($table, $data) {
        $errors = [];
        $fields = self::getFormFields($table);
        $rules = self::getValidationRules();
        
        foreach ($fields as $fieldName => $fieldConfig) {
            $value = $data[$fieldName] ?? null;
            
            // Check required fields
            if ($fieldConfig['required'] && empty($value)) {
                $errors[$fieldName] = ucfirst($fieldName) . ' is required';
                continue;
            }
            
            // Skip validation if value is empty and not required
            if (empty($value)) {
                continue;
            }
            
            // Check max length
            if (isset($fieldConfig['max_length']) && strlen($value) > $fieldConfig['max_length']) {
                $errors[$fieldName] = ucfirst($fieldName) . ' must not exceed ' . 
                                     $fieldConfig['max_length'] . ' characters';
            }
            
            // Check min/max for numbers
            if ($fieldConfig['type'] === 'number') {
                if (isset($fieldConfig['min']) && $value < $fieldConfig['min']) {
                    $errors[$fieldName] = ucfirst($fieldName) . ' must be at least ' . $fieldConfig['min'];
                }
                if (isset($fieldConfig['max']) && $value > $fieldConfig['max']) {
                    $errors[$fieldName] = ucfirst($fieldName) . ' must not exceed ' . $fieldConfig['max'];
                }
            }
            
            // Validate email
            if ($fieldConfig['type'] === 'email' && !preg_match($rules['email'], $value)) {
                $errors[$fieldName] = 'Invalid email format';
            }
            
            // Validate URL
            if ($fieldConfig['type'] === 'url' && !preg_match($rules['url'], $value)) {
                $errors[$fieldName] = 'Invalid URL format';
            }
            
            // Validate phone
            if ($fieldConfig['type'] === 'tel' && !preg_match($rules['phone'], $value)) {
                $errors[$fieldName] = 'Invalid phone number format';
            }
        }
        
        return $errors;
    }
    
    /**
     * Get status badge HTML
     */
    public static function getStatusBadge($table, $status) {
        $statuses = self::getStatuses($table);
        if (!isset($statuses[$status])) {
            return '<span class="badge bg-secondary">Unknown</span>';
        }
        
        $config = $statuses[$status];
        return sprintf(
            '<span class="badge %s"><i class="%s me-1"></i>%s</span>',
            $config['class'],
            $config['icon'],
            $config['label']
        );
    }
    
    /**
     * Format date according to system settings
     */
    public static function formatDate($date, $format = null) {
        if (!$date) return '-';
        
        $format = $format ?: self::SYSTEM_SETTINGS['date_format'];
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        
        return date($format, $timestamp);
    }
    
    /**
     * Sanitize output for HTML
     */
    public static function sanitize($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generate slug from string
     */
    public static function generateSlug($string) {
        $slug = strtolower(trim($string));
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return $slug;
    }
    
    /**
     * Get file size in human readable format
     */
    public static function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Log admin action
     */
    public static function logAction($action, $table = null, $record_id = null, $details = null) {
        // In a production environment, you would log to a file or database
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user' => $_SESSION['admin_logged_in'] ?? 'Unknown',
            'action' => $action,
            'table' => $table,
            'record_id' => $record_id,
            'details' => $details,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ];
        
        // For now, just log to error_log
        error_log('Admin Action: ' . json_encode($logEntry));
    }
}
