<?php
// Navigation and routing helper functions
class Navigation {
    private static $routes = [
        'dashboard' => ['title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'shortcut' => '1'],
        'contacts' => ['title' => 'Contacts', 'icon' => 'fas fa-envelope', 'shortcut' => '2'],
        'testimonials' => ['title' => 'Testimonials', 'icon' => 'fas fa-star', 'shortcut' => '3'],
        'services' => ['title' => 'Services', 'icon' => 'fas fa-cogs', 'shortcut' => '4'],
        'projects' => ['title' => 'Projects', 'icon' => 'fas fa-briefcase', 'shortcut' => '5'],
        'blog' => ['title' => 'Blog Posts', 'icon' => 'fas fa-blog', 'shortcut' => '6'],
        'newsletter' => ['title' => 'Newsletter', 'icon' => 'fas fa-mail-bulk', 'shortcut' => '7'],
        'database' => ['title' => 'Database', 'icon' => 'fas fa-database', 'shortcut' => '8']
    ];
    
    public static function getRoutes() {
        return self::$routes;
    }
    
    public static function renderBreadcrumb($currentPage = 'dashboard') {
        $routes = self::getRoutes();
        $current = $routes[$currentPage] ?? $routes['dashboard'];
        
        return "
        <nav aria-label=\"breadcrumb\" class=\"mb-3\">
            <ol class=\"breadcrumb\">
                <li class=\"breadcrumb-item\">
                    <a href=\"#dashboard\" class=\"text-decoration-none\">
                        <i class=\"fas fa-home me-1\"></i>Home
                    </a>
                </li>
                <li class=\"breadcrumb-item active\" aria-current=\"page\">
                    <i class=\"{$current['icon']} me-1\"></i>{$current['title']}
                </li>
            </ol>
        </nav>";
    }
    
    public static function renderSidebar($activeTab = 'dashboard', $stats = []) {
        $routes = self::getRoutes();
        $html = '<nav class="admin-nav">';
        
        foreach ($routes as $route => $config) {
            $activeClass = $route === $activeTab ? 'active' : '';
            $badge = '';
            
            // Add badges for specific routes
            if ($route === 'contacts' && isset($stats['new_contacts']) && $stats['new_contacts'] > 0) {
                $badge = "<span class=\"badge bg-warning ms-2\">{$stats['new_contacts']}</span>";
            } elseif ($route === 'blog' && isset($stats['draft_posts']) && $stats['draft_posts'] > 0) {
                $badge = "<span class=\"badge bg-info ms-2\">{$stats['draft_posts']}</span>";
            }
            
            $html .= "
            <a href=\"#{$route}\" class=\"admin-nav-link {$activeClass}\" data-tab=\"{$route}\">
                <i class=\"{$config['icon']} me-2\"></i>{$config['title']}
                {$badge}
                <kbd class=\"ms-auto\">Ctrl+{$config['shortcut']}</kbd>
            </a>";
        }
        
        $html .= '
            <hr class="my-3 opacity-50">
            <a href="?action=logout" class="admin-nav-link text-white-50">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </nav>';
        
        return $html;
    }
    
    public static function renderProgressBar($current, $total, $label = '', $color = 'primary') {
        if ($total == 0) return '';
        
        $percentage = min(100, ($current / $total) * 100);
        
        return "
        <div class=\"mb-3\">
            <div class=\"d-flex justify-content-between align-items-center mb-2\">
                <span>{$label}</span>
                <span class=\"badge bg-{$color}\">{$current}/{$total}</span>
            </div>
            <div class=\"progress\" style=\"height: 8px;\">
                <div class=\"progress-bar bg-{$color}\" role=\"progressbar\" 
                     style=\"width: {$percentage}%\" 
                     aria-valuenow=\"{$current}\" 
                     aria-valuemin=\"0\" 
                     aria-valuemax=\"{$total}\">
                </div>
            </div>
        </div>";
    }
    
    public static function renderStatusBadge($status, $type = 'general') {
        $badges = [
            'general' => [
                'active' => 'success',
                'inactive' => 'secondary',
                'draft' => 'warning',
                'published' => 'success',
                'new' => 'warning',
                'read' => 'info',
                'replied' => 'success'
            ],
            'priority' => [
                'high' => 'danger',
                'medium' => 'warning',
                'low' => 'info'
            ]
        ];
        
        $colorMap = $badges[$type] ?? $badges['general'];
        $color = $colorMap[strtolower($status)] ?? 'secondary';
        
        return "<span class=\"badge bg-{$color}\">" . ucfirst($status) . "</span>";
    }
}

// Database statistics helper
class AdminStats {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function getAllStats() {
        try {
            return [
                'contacts' => $this->getContactStats(),
                'testimonials' => $this->getTestimonialStats(),
                'services' => $this->getServiceStats(),
                'projects' => $this->getProjectStats(),
                'blog' => $this->getBlogStats(),
                'newsletter' => $this->getNewsletterStats()
            ];
        } catch (Exception $e) {
            error_log("Stats error: " . $e->getMessage());
            return [];
        }
    }
    
    private function getContactStats() {
        return [
            'total' => $this->db->fetchOne("SELECT COUNT(*) as count FROM contacts")['count'] ?? 0,
            'new' => $this->db->fetchOne("SELECT COUNT(*) as count FROM contacts WHERE status = 'new'")['count'] ?? 0,
            'read' => $this->db->fetchOne("SELECT COUNT(*) as count FROM contacts WHERE status = 'read'")['count'] ?? 0,
            'replied' => $this->db->fetchOne("SELECT COUNT(*) as count FROM contacts WHERE status = 'replied'")['count'] ?? 0
        ];
    }
    
    private function getTestimonialStats() {
        return [
            'total' => $this->db->fetchOne("SELECT COUNT(*) as count FROM testimonials")['count'] ?? 0,
            'active' => $this->db->fetchOne("SELECT COUNT(*) as count FROM testimonials WHERE is_active = true")['count'] ?? 0,
            'featured' => $this->db->fetchOne("SELECT COUNT(*) as count FROM testimonials WHERE is_featured = true")['count'] ?? 0,
            'avg_rating' => round($this->db->fetchOne("SELECT AVG(rating) as avg FROM testimonials WHERE is_active = true")['avg'] ?? 0, 1)
        ];
    }
    
    private function getServiceStats() {
        return [
            'total' => $this->db->fetchOne("SELECT COUNT(*) as count FROM services")['count'] ?? 0,
            'active' => $this->db->fetchOne("SELECT COUNT(*) as count FROM services WHERE is_active = true")['count'] ?? 0
        ];
    }
    
    private function getProjectStats() {
        return [
            'total' => $this->db->fetchOne("SELECT COUNT(*) as count FROM projects")['count'] ?? 0,
            'active' => $this->db->fetchOne("SELECT COUNT(*) as count FROM projects WHERE is_active = true")['count'] ?? 0,
            'featured' => $this->db->fetchOne("SELECT COUNT(*) as count FROM projects WHERE is_featured = true")['count'] ?? 0
        ];
    }
    
    private function getBlogStats() {
        return [
            'total' => $this->db->fetchOne("SELECT COUNT(*) as count FROM blog_posts")['count'] ?? 0,
            'published' => $this->db->fetchOne("SELECT COUNT(*) as count FROM blog_posts WHERE is_published = true")['count'] ?? 0,
            'draft' => $this->db->fetchOne("SELECT COUNT(*) as count FROM blog_posts WHERE is_published = false")['count'] ?? 0
        ];
    }
    
    private function getNewsletterStats() {
        return [
            'total' => $this->db->fetchOne("SELECT COUNT(*) as count FROM newsletter_subscribers")['count'] ?? 0,
            'active' => $this->db->fetchOne("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE is_active = true")['count'] ?? 0
        ];
    }
}

// Progress tracker for database operations
class ProgressTracker {
    public static function renderLiveProgress($id, $total = 100) {
        return "
        <div class=\"progress-container mb-3\" id=\"progress-{$id}\">
            <div class=\"d-flex justify-content-between align-items-center mb-2\">
                <span class=\"progress-label\">Processing...</span>
                <span class=\"progress-percentage\">0%</span>
            </div>
            <div class=\"progress\" style=\"height: 12px;\">
                <div class=\"progress-bar progress-bar-striped progress-bar-animated\" 
                     role=\"progressbar\" style=\"width: 0%\" id=\"progressbar-{$id}\">
                </div>
            </div>
            <div class=\"progress-details mt-2 text-muted small\">
                <span class=\"current-step\">Initializing...</span>
            </div>
        </div>";
    }
    
    public static function updateProgressScript() {
        return "
        <script>
        function updateProgress(id, percentage, label = '', step = '') {
            const container = document.getElementById('progress-' + id);
            if (!container) return;
            
            const progressBar = document.getElementById('progressbar-' + id);
            const percentageSpan = container.querySelector('.progress-percentage');
            const labelSpan = container.querySelector('.progress-label');
            const stepSpan = container.querySelector('.current-step');
            
            if (progressBar) {
                progressBar.style.width = percentage + '%';
                progressBar.setAttribute('aria-valuenow', percentage);
            }
            
            if (percentageSpan) percentageSpan.textContent = Math.round(percentage) + '%';
            if (labelSpan && label) labelSpan.textContent = label;
            if (stepSpan && step) stepSpan.textContent = step;
            
            // Add success class when complete
            if (percentage >= 100) {
                progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                progressBar.classList.add('bg-success');
                setTimeout(() => {
                    container.style.transition = 'opacity 0.5s';
                    container.style.opacity = '0';
                    setTimeout(() => container.remove(), 500);
                }, 2000);
            }
        }
        </script>";
    }
}
?>
