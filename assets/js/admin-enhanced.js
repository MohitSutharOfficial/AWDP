/**
 * Enhanced Admin Dashboard JavaScript
 * Complete CRUD Management System
 */

class AdminDashboard {
    constructor() {
        this.currentTab = 'dashboard';
        this.currentPage = 1;
        this.currentSearch = '';
        this.currentFilters = {};
        this.apiBase = '/api/admin-crud.php';
        
        this.init();
    }
    
    init() {
        this.initializeEventListeners();
        this.initializeMobileSidebar();
        this.loadInitialData();
        this.initializeKeyboardShortcuts();
    }
    
    // ==================== INITIALIZATION ====================
    initializeEventListeners() {
        // Tab switching
        document.querySelectorAll('.admin-nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                if (link.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    const tabId = link.getAttribute('href').substring(1);
                    this.switchTab(tabId);
                }
            });
        });
        
        // Global search
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                this.showGlobalSearch();
            }
        });
    }
    
    initializeMobileSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileOverlay');
        const toggleBtn = document.getElementById('mobileToggle');
        
        if (toggleBtn && sidebar && overlay) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            });
            
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
            
            // Auto-close on navigation
            document.querySelectorAll('.admin-nav-link').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 992) {
                        sidebar.classList.remove('show');
                        overlay.classList.remove('show');
                    }
                });
            });
        }
    }
    
    initializeKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                const shortcuts = {
                    '1': 'dashboard',
                    '2': 'contacts',
                    '3': 'testimonials',
                    '4': 'services',
                    '5': 'projects',
                    '6': 'blog',
                    '7': 'newsletter',
                    '8': 'database'
                };
                
                if (shortcuts[e.key]) {
                    e.preventDefault();
                    this.switchTab(shortcuts[e.key]);
                }
            }
        });
    }
    
    loadInitialData() {
        // Load stats for dashboard
        this.loadStats();
    }
    
    // ==================== TAB MANAGEMENT ====================
    switchTab(tabId) {
        // Remove active classes
        document.querySelectorAll('.admin-nav-link').forEach(l => l.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        // Add active class to current tab
        const activeLink = document.querySelector(`[href="#${tabId}"]`);
        if (activeLink) activeLink.classList.add('active');
        
        const tabContent = document.getElementById(tabId);
        if (tabContent) {
            tabContent.classList.add('active');
            this.currentTab = tabId;
            
            // Load tab-specific content
            this.loadTabContent(tabId);
        }
    }
    
    loadTabContent(tabId) {
        const loadingHtml = this.getLoadingHtml();
        const tabElement = document.getElementById(tabId);
        
        switch (tabId) {
            case 'contacts':
                tabElement.innerHTML = loadingHtml;
                this.loadContactsTab();
                break;
            case 'testimonials':
                tabElement.innerHTML = loadingHtml;
                this.loadTestimonialsTab();
                break;
            case 'services':
                tabElement.innerHTML = loadingHtml;
                this.loadServicesTab();
                break;
            case 'projects':
                tabElement.innerHTML = loadingHtml;
                this.loadProjectsTab();
                break;
            case 'blog':
                tabElement.innerHTML = loadingHtml;
                this.loadBlogTab();
                break;
            case 'newsletter':
                tabElement.innerHTML = loadingHtml;
                this.loadNewsletterTab();
                break;
            case 'database':
                tabElement.innerHTML = loadingHtml;
                this.loadDatabaseTab();
                break;
        }
    }
    
    // ==================== API HELPERS ====================
    async makeRequest(action, data = {}, method = 'GET') {
        const isFormData = data instanceof FormData;
        
        const options = {
            method: method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        if (method === 'GET') {
            const params = new URLSearchParams(data);
            const url = `${this.apiBase}?action=${action}&${params.toString()}`;
            const response = await fetch(url, options);
            return await response.json();
        } else {
            if (!isFormData) {
                data.action = action;
                const formData = new FormData();
                Object.keys(data).forEach(key => {
                    formData.append(key, data[key]);
                });
                options.body = formData;
            } else {
                data.append('action', action);
                options.body = data;
            }
            
            const response = await fetch(this.apiBase, options);
            return await response.json();
        }
    }
    
    showLoading(show = true) {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.toggle('d-none', !show);
        }
    }
    
    showNotification(message, type = 'info', duration = 5000) {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;';
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                <div>${message}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, duration);
    }
    
    getLoadingHtml() {
        return `
        <div class="text-center py-5">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h5>Loading data...</h5>
        </div>`;
    }
    
    // ==================== STATS LOADING ====================
    async loadStats() {
        try {
            const result = await this.makeRequest('get_stats');
            if (result.success) {
                this.updateDashboardStats(result.data);
            }
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }
    
    updateDashboardStats(stats) {
        // Update stat widgets if they exist
        const widgets = document.querySelectorAll('.stat-widget');
        if (widgets.length > 0) {
            // Update contact stats
            this.updateStatWidget('contacts', stats.contacts?.total || 0);
            this.updateStatWidget('testimonials', stats.testimonials?.total || 0);
            this.updateStatWidget('services', stats.services?.total || 0);
            this.updateStatWidget('projects', stats.projects?.total || 0);
        }
    }
    
    updateStatWidget(type, value) {
        const widget = document.querySelector(`[data-stat="${type}"] .stat-number`);
        if (widget) {
            widget.textContent = value;
        }
    }
    
    // ==================== CONTACTS TAB ====================
    async loadContactsTab() {
        const tabContent = document.getElementById('contacts');
        
        try {
            const result = await this.makeRequest('get_contacts', {
                page: this.currentPage,
                limit: 20,
                search: this.currentSearch
            });
            
            if (result.success) {
                tabContent.innerHTML = this.renderContactsHTML(result.data, result.pagination);
                this.bindContactEvents();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            tabContent.innerHTML = this.renderErrorHTML('Failed to load contacts', error.message);
        }
    }
    
    renderContactsHTML(contacts, pagination) {
        return `
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-envelope me-2"></i>Contact Management</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="admin.exportData('contacts')">
                    <i class="fas fa-download me-2"></i>Export
                </button>
                <button class="btn btn-success" onclick="admin.markAllContactsRead()">
                    <i class="fas fa-check-double me-2"></i>Mark All Read
                </button>
            </div>
        </div>
        
        <div class="search-filter-bar">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search contacts..." 
                               id="contactSearch" value="${this.currentSearch}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="contactStatusFilter">
                        <option value="">All Status</option>
                        <option value="new">New</option>
                        <option value="read">Read</option>
                        <option value="replied">Replied</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" onclick="admin.applyContactFilters()">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>
            </div>
        </div>
        
        <div class="data-table">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="selectAllContacts"></th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${contacts.map(contact => this.renderContactRow(contact)).join('')}
                    </tbody>
                </table>
            </div>
            
            ${this.renderPagination(pagination)}
        </div>`;
    }
    
    renderContactRow(contact) {
        const statusBadge = this.getStatusBadge(contact.status);
        
        return `
        <tr data-id="${contact.id}">
            <td><input type="checkbox" class="contact-checkbox" value="${contact.id}"></td>
            <td>
                <div class="fw-semibold">${this.escapeHtml(contact.name)}</div>
                <small class="text-muted">${this.escapeHtml(contact.phone || '')}</small>
            </td>
            <td>
                <a href="mailto:${this.escapeHtml(contact.email)}" class="text-decoration-none">
                    ${this.escapeHtml(contact.email)}
                </a>
            </td>
            <td>${this.escapeHtml(contact.company || '-')}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="admin.viewContactMessage(${contact.id}, '${this.escapeHtml(contact.subject || 'No Subject')}', '${this.escapeHtml(contact.message)}')">
                    ${this.truncateText(contact.subject || 'No Subject', 30)}
                </button>
            </td>
            <td>
                <small>${this.formatDate(contact.created_at)}</small>
            </td>
            <td>${statusBadge}</td>
            <td>
                <div class="action-buttons">
                    ${contact.status === 'new' ? `
                        <button class="btn btn-sm btn-success" onclick="admin.updateContactStatus(${contact.id}, 'read')" title="Mark as Read">
                            <i class="fas fa-check"></i>
                        </button>
                    ` : ''}
                    <button class="btn btn-sm btn-info" onclick="admin.updateContactStatus(${contact.id}, 'replied')" title="Mark as Replied">
                        <i class="fas fa-reply"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="admin.deleteContact(${contact.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }
    
    bindContactEvents() {
        // Search functionality
        const searchInput = document.getElementById('contactSearch');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.currentSearch = e.target.value;
                    this.currentPage = 1;
                    this.loadContactsTab();
                }, 500);
            });
        }
        
        // Select all functionality
        const selectAll = document.getElementById('selectAllContacts');
        if (selectAll) {
            selectAll.addEventListener('change', (e) => {
                document.querySelectorAll('.contact-checkbox').forEach(cb => {
                    cb.checked = e.target.checked;
                });
            });
        }
    }
    
    async updateContactStatus(contactId, status) {
        try {
            const result = await this.makeRequest('update_contact_status', {
                contact_id: contactId,
                status: status
            }, 'POST');
            
            if (result.success) {
                this.showNotification(result.message, 'success');
                this.loadContactsTab(); // Refresh
                this.loadStats(); // Update stats
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Error updating contact status', 'error');
        }
    }
    
    async deleteContact(contactId) {
        if (!confirm('Are you sure you want to delete this contact?')) return;
        
        try {
            const result = await this.makeRequest('delete_contact', {
                contact_id: contactId
            }, 'POST');
            
            if (result.success) {
                this.showNotification(result.message, 'success');
                this.loadContactsTab(); // Refresh
                this.loadStats(); // Update stats
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Error deleting contact', 'error');
        }
    }
    
    async markAllContactsRead() {
        if (!confirm('Mark all new contacts as read?')) return;
        
        try {
            const result = await this.makeRequest('mark_all_contacts_read', {}, 'POST');
            
            if (result.success) {
                this.showNotification(result.message, 'success');
                this.loadContactsTab(); // Refresh
                this.loadStats(); // Update stats
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Error marking contacts as read', 'error');
        }
    }
    
    viewContactMessage(contactId, subject, message) {
        const modal = this.createModal('Contact Message', `
            <div class="mb-3">
                <h6 class="text-muted">Subject:</h6>
                <p class="fw-semibold">${this.escapeHtml(subject)}</p>
            </div>
            <div class="mb-3">
                <h6 class="text-muted">Message:</h6>
                <div class="border rounded p-3 bg-light" style="white-space: pre-wrap; max-height: 300px; overflow-y: auto;">
                    ${this.escapeHtml(message)}
                </div>
            </div>
        `);
        modal.show();
    }
    
    applyContactFilters() {
        const statusFilter = document.getElementById('contactStatusFilter').value;
        this.currentFilters = { status: statusFilter };
        this.currentPage = 1;
        this.loadContactsTab();
    }
    
    // ==================== TESTIMONIALS TAB ====================
    async loadTestimonialsTab() {
        const tabContent = document.getElementById('testimonials');
        
        try {
            const result = await this.makeRequest('get_testimonials', {
                page: this.currentPage,
                limit: 20,
                search: this.currentSearch
            });
            
            if (result.success) {
                tabContent.innerHTML = this.renderTestimonialsHTML(result.data, result.pagination);
                this.bindTestimonialEvents();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            tabContent.innerHTML = this.renderErrorHTML('Failed to load testimonials', error.message);
        }
    }
    
    renderTestimonialsHTML(testimonials, pagination) {
        return `
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-star me-2"></i>Testimonial Management</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-success" onclick="admin.showAddTestimonialModal()">
                    <i class="fas fa-plus me-2"></i>Add New
                </button>
                <button class="btn btn-outline-primary" onclick="admin.exportData('testimonials')">
                    <i class="fas fa-download me-2"></i>Export
                </button>
            </div>
        </div>
        
        <div class="search-filter-bar">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search testimonials..." 
                               id="testimonialSearch" value="${this.currentSearch}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="testimonialStatusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="featured">Featured</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" onclick="admin.applyTestimonialFilters()">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>
            </div>
        </div>
        
        <div class="data-table">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Rating</th>
                            <th>Testimonial</th>
                            <th>Featured</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${testimonials.map(testimonial => this.renderTestimonialRow(testimonial)).join('')}
                    </tbody>
                </table>
            </div>
            
            ${this.renderPagination(pagination)}
        </div>`;
    }
    
    renderTestimonialRow(testimonial) {
        const stars = this.renderStars(testimonial.rating);
        
        return `
        <tr data-id="${testimonial.id}">
            <td>
                <div class="fw-semibold">${this.escapeHtml(testimonial.name)}</div>
                <small class="text-muted">${this.escapeHtml(testimonial.position || '')}</small>
            </td>
            <td>${this.escapeHtml(testimonial.company || '-')}</td>
            <td>
                <div class="d-flex align-items-center">
                    ${stars}
                    <small class="text-muted ms-2">(${testimonial.rating})</small>
                </div>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="admin.viewTestimonial(${testimonial.id}, '${this.escapeHtml(testimonial.testimonial)}')">
                    <i class="fas fa-eye me-1"></i>View
                </button>
            </td>
            <td>
                <button class="btn btn-sm btn-${testimonial.is_featured ? 'warning' : 'outline-secondary'}" 
                        onclick="admin.toggleTestimonialFeatured(${testimonial.id})">
                    <i class="fas fa-star me-1"></i>
                    ${testimonial.is_featured ? 'Featured' : 'Regular'}
                </button>
            </td>
            <td>
                <button class="btn btn-sm btn-${testimonial.is_active ? 'success' : 'danger'}" 
                        onclick="admin.toggleTestimonialStatus(${testimonial.id})">
                    <i class="fas fa-${testimonial.is_active ? 'check' : 'times'} me-1"></i>
                    ${testimonial.is_active ? 'Active' : 'Inactive'}
                </button>
            </td>
            <td><small>${this.formatDate(testimonial.created_at)}</small></td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-primary" onclick="admin.editTestimonial(${testimonial.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="admin.deleteTestimonial(${testimonial.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }
    
    // ==================== SERVICES TAB ====================
    async loadServicesTab() {
        const tabContent = document.getElementById('services');
        
        try {
            const result = await this.makeRequest('get_services', {
                page: this.currentPage,
                limit: 20,
                search: this.currentSearch
            });
            
            if (result.success) {
                tabContent.innerHTML = this.renderServicesHTML(result.data, result.pagination);
                this.bindServiceEvents();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            tabContent.innerHTML = this.renderErrorHTML('Failed to load services', error.message);
        }
    }
    
    renderServicesHTML(services, pagination) {
        return `
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-cogs me-2"></i>Service Management</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-success" onclick="admin.showAddServiceModal()">
                    <i class="fas fa-plus me-2"></i>Add New Service
                </button>
                <button class="btn btn-outline-primary" onclick="admin.exportData('services')">
                    <i class="fas fa-download me-2"></i>Export
                </button>
            </div>
        </div>
        
        <div class="search-filter-bar">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search services..." 
                               id="serviceSearch" value="${this.currentSearch}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="serviceStatusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="featured">Featured</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" onclick="admin.applyServiceFilters()">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>
            </div>
        </div>
        
        <div class="data-table">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Featured</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${services.map(service => this.renderServiceRow(service)).join('')}
                    </tbody>
                </table>
            </div>
            
            ${this.renderPagination(pagination)}
        </div>`;
    }
    
    renderServiceRow(service) {
        return `
        <tr data-id="${service.id}">
            <td>
                <div class="fw-semibold">${this.escapeHtml(service.title)}</div>
                ${service.icon ? `<small class="text-muted"><i class="${service.icon}"></i> ${service.icon}</small>` : ''}
            </td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="admin.viewServiceDescription(${service.id}, '${this.escapeHtml(service.description)}')">
                    <i class="fas fa-eye me-1"></i>View
                </button>
            </td>
            <td>
                ${service.price ? `$${parseFloat(service.price).toFixed(2)}` : 'Contact for quote'}
            </td>
            <td>
                <button class="btn btn-sm btn-${service.is_featured ? 'warning' : 'outline-secondary'}" 
                        onclick="admin.toggleServiceFeatured(${service.id})">
                    <i class="fas fa-star me-1"></i>
                    ${service.is_featured ? 'Featured' : 'Regular'}
                </button>
            </td>
            <td>
                <button class="btn btn-sm btn-${service.is_active ? 'success' : 'danger'}" 
                        onclick="admin.toggleServiceStatus(${service.id})">
                    <i class="fas fa-${service.is_active ? 'check' : 'times'} me-1"></i>
                    ${service.is_active ? 'Active' : 'Inactive'}
                </button>
            </td>
            <td><small>${this.formatDate(service.created_at)}</small></td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-primary" onclick="admin.editService(${service.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="admin.deleteService(${service.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }
    
    bindServiceEvents() {
        const searchInput = document.getElementById('serviceSearch');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.currentSearch = e.target.value;
                    this.currentPage = 1;
                    this.loadServicesTab();
                }, 500);
            });
        }
    }
    
    // ==================== PROJECTS TAB ====================
    async loadProjectsTab() {
        const tabContent = document.getElementById('projects');
        
        try {
            const result = await this.makeRequest('get_projects', {
                page: this.currentPage,
                limit: 20,
                search: this.currentSearch
            });
            
            if (result.success) {
                tabContent.innerHTML = this.renderProjectsHTML(result.data, result.pagination);
                this.bindProjectEvents();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            tabContent.innerHTML = this.renderErrorHTML('Failed to load projects', error.message);
        }
    }
    
    renderProjectsHTML(projects, pagination) {
        return `
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-project-diagram me-2"></i>Project Management</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-success" onclick="admin.showAddProjectModal()">
                    <i class="fas fa-plus me-2"></i>Add New Project
                </button>
                <button class="btn btn-outline-primary" onclick="admin.exportData('projects')">
                    <i class="fas fa-download me-2"></i>Export
                </button>
            </div>
        </div>
        
        <div class="data-table">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Client</th>
                            <th>Technologies</th>
                            <th>URL</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${projects.map(project => this.renderProjectRow(project)).join('')}
                    </tbody>
                </table>
            </div>
            
            ${this.renderPagination(pagination)}
        </div>`;
    }
    
    renderProjectRow(project) {
        return `
        <tr data-id="${project.id}">
            <td>
                <div class="fw-semibold">${this.escapeHtml(project.title)}</div>
                <small class="text-muted">${this.truncateText(project.short_description || '', 50)}</small>
            </td>
            <td>${this.escapeHtml(project.client_name || '-')}</td>
            <td>
                <small class="badge bg-light text-dark">${this.escapeHtml(project.technologies || 'N/A')}</small>
            </td>
            <td>
                ${project.project_url ? 
                    `<a href="${project.project_url}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt"></i>
                    </a>` : 
                    '<span class="text-muted">No URL</span>'
                }
            </td>
            <td>
                <button class="btn btn-sm btn-${project.is_active ? 'success' : 'danger'}" 
                        onclick="admin.toggleProjectStatus(${project.id})">
                    <i class="fas fa-${project.is_active ? 'check' : 'times'} me-1"></i>
                    ${project.is_active ? 'Active' : 'Inactive'}
                </button>
            </td>
            <td>
                <button class="btn btn-sm btn-${project.is_featured ? 'warning' : 'outline-secondary'}" 
                        onclick="admin.toggleProjectFeatured(${project.id})">
                    <i class="fas fa-star me-1"></i>
                    ${project.is_featured ? 'Featured' : 'Regular'}
                </button>
            </td>
            <td><small>${this.formatDate(project.created_at)}</small></td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-info" onclick="admin.viewProject(${project.id})" title="View">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="admin.editProject(${project.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="admin.deleteProject(${project.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }
    
    bindProjectEvents() {
        // Add project-specific event bindings here
    }
    
    // ==================== BLOG TAB ====================
    async loadBlogTab() {
        const tabContent = document.getElementById('blog');
        
        try {
            const result = await this.makeRequest('get_blog_posts', {
                page: this.currentPage,
                limit: 20,
                search: this.currentSearch
            });
            
            if (result.success) {
                tabContent.innerHTML = this.renderBlogHTML(result.data, result.pagination);
                this.bindBlogEvents();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            tabContent.innerHTML = this.renderErrorHTML('Failed to load blog posts', error.message);
        }
    }
    
    renderBlogHTML(posts, pagination) {
        return `
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-blog me-2"></i>Blog Management</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-success" onclick="admin.showAddBlogModal()">
                    <i class="fas fa-plus me-2"></i>Add New Post
                </button>
                <button class="btn btn-outline-primary" onclick="admin.exportData('blog_posts')">
                    <i class="fas fa-download me-2"></i>Export
                </button>
            </div>
        </div>
        
        <div class="data-table">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${posts.map(post => this.renderBlogRow(post)).join('')}
                    </tbody>
                </table>
            </div>
            
            ${this.renderPagination(pagination)}
        </div>`;
    }
    
    renderBlogRow(post) {
        return `
        <tr data-id="${post.id}">
            <td>
                <div class="fw-semibold">${this.escapeHtml(post.title)}</div>
                <small class="text-muted">${this.truncateText(post.excerpt || '', 50)}</small>
            </td>
            <td>${this.escapeHtml(post.author || 'Admin')}</td>
            <td>
                <span class="badge bg-info">${this.escapeHtml(post.category || 'General')}</span>
            </td>
            <td>
                <span class="badge ${post.status === 'published' ? 'bg-success' : post.status === 'draft' ? 'bg-warning' : 'bg-secondary'}">
                    ${this.capitalize(post.status)}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-${post.is_featured ? 'warning' : 'outline-secondary'}" 
                        onclick="admin.toggleBlogFeatured(${post.id})">
                    <i class="fas fa-star me-1"></i>
                    ${post.is_featured ? 'Featured' : 'Regular'}
                </button>
            </td>
            <td><small>${this.formatDate(post.created_at)}</small></td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-info" onclick="admin.viewBlogPost(${post.id})" title="View">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="admin.editBlogPost(${post.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="admin.deleteBlogPost(${post.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }
    
    bindBlogEvents() {
        // Add blog-specific event bindings here
    }
    
    // ==================== NEWSLETTER TAB ====================
    async loadNewsletterTab() {
        const tabContent = document.getElementById('newsletter');
        
        try {
            const result = await this.makeRequest('get_newsletter_subscribers', {
                page: this.currentPage,
                limit: 20,
                search: this.currentSearch
            });
            
            if (result.success) {
                tabContent.innerHTML = this.renderNewsletterHTML(result.data, result.pagination);
                this.bindNewsletterEvents();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            tabContent.innerHTML = this.renderErrorHTML('Failed to load newsletter subscribers', error.message);
        }
    }
    
    renderNewsletterHTML(subscribers, pagination) {
        return `
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-newspaper me-2"></i>Newsletter Management</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-success" onclick="admin.exportSubscribers()">
                    <i class="fas fa-download me-2"></i>Export Subscribers
                </button>
                <button class="btn btn-primary" onclick="admin.sendNewsletter()">
                    <i class="fas fa-paper-plane me-2"></i>Send Newsletter
                </button>
            </div>
        </div>
        
        <div class="data-table">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="selectAllSubscribers"></th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Subscribed Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${subscribers.map(subscriber => this.renderSubscriberRow(subscriber)).join('')}
                    </tbody>
                </table>
            </div>
            
            ${this.renderPagination(pagination)}
        </div>`;
    }
    
    renderSubscriberRow(subscriber) {
        return `
        <tr data-id="${subscriber.id}">
            <td><input type="checkbox" class="subscriber-checkbox" value="${subscriber.id}"></td>
            <td>
                <div class="fw-semibold">${this.escapeHtml(subscriber.email)}</div>
            </td>
            <td>
                <span class="badge ${subscriber.is_active ? 'bg-success' : 'bg-danger'}">
                    ${subscriber.is_active ? 'Active' : 'Unsubscribed'}
                </span>
            </td>
            <td><small>${this.formatDate(subscriber.created_at)}</small></td>
            <td>
                <div class="action-buttons">
                    ${!subscriber.is_active ? `
                        <button class="btn btn-sm btn-success" onclick="admin.activateSubscriber(${subscriber.id})" title="Reactivate">
                            <i class="fas fa-undo"></i>
                        </button>
                    ` : `
                        <button class="btn btn-sm btn-warning" onclick="admin.unsubscribeUser(${subscriber.id})" title="Unsubscribe">
                            <i class="fas fa-user-times"></i>
                        </button>
                    `}
                    <button class="btn btn-sm btn-danger" onclick="admin.deleteSubscriber(${subscriber.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }
    
    bindNewsletterEvents() {
        const selectAll = document.getElementById('selectAllSubscribers');
        if (selectAll) {
            selectAll.addEventListener('change', (e) => {
                document.querySelectorAll('.subscriber-checkbox').forEach(cb => {
                    cb.checked = e.target.checked;
                });
            });
        }
    }
    
    // ==================== DATABASE TAB ====================
    async loadDatabaseTab() {
        const tabContent = document.getElementById('database');
        
        try {
            const result = await this.makeRequest('get_database_stats');
            
            if (result.success) {
                tabContent.innerHTML = this.renderDatabaseHTML(result.data);
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            tabContent.innerHTML = this.renderErrorHTML('Failed to load database information', error.message);
        }
    }
    
    renderDatabaseHTML(stats) {
        return `
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-database me-2"></i>Database Management</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-success" onclick="admin.backupDatabase()">
                    <i class="fas fa-download me-2"></i>Backup Database
                </button>
                <button class="btn btn-warning" onclick="admin.optimizeDatabase()">
                    <i class="fas fa-wrench me-2"></i>Optimize
                </button>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="form-container">
                    <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Database Information</h5>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Database Type:</strong></td>
                                <td>PostgreSQL (Supabase)</td>
                            </tr>
                            <tr>
                                <td><strong>Connection Status:</strong></td>
                                <td><span class="badge bg-success">Connected</span></td>
                            </tr>
                            <tr>
                                <td><strong>Total Tables:</strong></td>
                                <td>${stats.total_tables || 0}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Records:</strong></td>
                                <td>${stats.total_records || 0}</td>
                            </tr>
                            <tr>
                                <td><strong>Database Size:</strong></td>
                                <td>${stats.database_size || 'Unknown'}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="form-container">
                    <h5 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Table Statistics</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Table</th>
                                    <th>Records</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>contacts</td>
                                    <td>${stats.contacts_count || 0}</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                                <tr>
                                    <td>testimonials</td>
                                    <td>${stats.testimonials_count || 0}</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                                <tr>
                                    <td>services</td>
                                    <td>${stats.services_count || 0}</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                                <tr>
                                    <td>projects</td>
                                    <td>${stats.projects_count || 0}</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                                <tr>
                                    <td>blog_posts</td>
                                    <td>${stats.blog_posts_count || 0}</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                                <tr>
                                    <td>newsletter_subscribers</td>
                                    <td>${stats.newsletter_count || 0}</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-12">
                <div class="form-container">
                    <h5 class="mb-3"><i class="fas fa-history me-2"></i>Recent Activity Log</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Action</th>
                                    <th>Table</th>
                                    <th>User</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><small>${this.formatDate(new Date())}</small></td>
                                    <td><span class="badge bg-info">VIEW</span></td>
                                    <td>database</td>
                                    <td>admin</td>
                                    <td>Viewed database statistics</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>`;
    }
    
    // ==================== MODAL METHODS ====================
    showAddTestimonialModal() {
        const modalContent = `
        <form id="addTestimonialForm">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Company</label>
                    <input type="text" class="form-control" name="company">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Position</label>
                    <input type="text" class="form-control" name="position">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Testimonial *</label>
                <textarea class="form-control" name="testimonial" rows="4" required></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Rating *</label>
                    <select class="form-control" name="rating" required>
                        <option value="">Select Rating</option>
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-check mt-4">
                        <input type="checkbox" class="form-check-input" name="is_featured" id="testimonialFeatured">
                        <label class="form-check-label" for="testimonialFeatured">Featured Testimonial</label>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Add Testimonial</button>
            </div>
        </form>`;
        
        const modal = this.createModal('Add New Testimonial', modalContent);
        modal.show();
        
        // Handle form submission
        document.getElementById('addTestimonialForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const result = await this.makeRequest('create_testimonial', formData, 'POST');
                if (result.success) {
                    this.showNotification(result.message, 'success');
                    modal.hide();
                    this.loadTestimonialsTab();
                } else {
                    this.showNotification(result.message, 'error');
                }
            } catch (error) {
                this.showNotification('Error adding testimonial', 'error');
            }
        });
    }
    
    viewTestimonial(id, testimonial) {
        const modal = this.createModal('Testimonial Details', `
            <div class="testimonial-view">
                <div class="mb-3">
                    <h6 class="text-muted">Testimonial:</h6>
                    <div class="border rounded p-3 bg-light" style="white-space: pre-wrap; max-height: 300px; overflow-y: auto;">
                        "${this.escapeHtml(testimonial)}"
                    </div>
                </div>
            </div>
        `);
        modal.show();
    }
    
    async toggleTestimonialFeatured(testimonialId) {
        try {
            const result = await this.makeRequest('toggle_testimonial_featured', {
                testimonial_id: testimonialId
            }, 'POST');
            
            if (result.success) {
                this.showNotification(result.message, 'success');
                this.loadTestimonialsTab();
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Error updating testimonial', 'error');
        }
    }
    
    async toggleTestimonialStatus(testimonialId) {
        try {
            const result = await this.makeRequest('toggle_testimonial_status', {
                testimonial_id: testimonialId
            }, 'POST');
            
            if (result.success) {
                this.showNotification(result.message, 'success');
                this.loadTestimonialsTab();
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Error updating testimonial status', 'error');
        }
    }
    
    async deleteTestimonial(testimonialId) {
        if (!confirm('Are you sure you want to delete this testimonial?')) return;
        
        try {
            const result = await this.makeRequest('delete_testimonial', {
                testimonial_id: testimonialId
            }, 'POST');
            
            if (result.success) {
                this.showNotification(result.message, 'success');
                this.loadTestimonialsTab();
                this.loadStats();
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Error deleting testimonial', 'error');
        }
    }
    
    // ==================== UTILITY METHODS ====================
    getStatusBadge(status) {
        const badges = {
            'new': 'bg-warning',
            'read': 'bg-info',
            'replied': 'bg-success',
            'active': 'bg-success',
            'inactive': 'bg-secondary',
            'published': 'bg-success',
            'draft': 'bg-warning'
        };
        
        const color = badges[status] || 'bg-secondary';
        return `<span class="badge ${color}">${this.capitalize(status)}</span>`;
    }
    
    renderStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<i class="fas fa-star ${i <= rating ? 'text-warning' : 'text-muted'}"></i>`;
        }
        return stars;
    }
    
    renderPagination(pagination) {
        if (pagination.pages <= 1) return '';
        
        const { page, pages, total, limit } = pagination;
        const start = (page - 1) * limit + 1;
        const end = Math.min(page * limit, total);
        
        let html = `
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <div class="pagination-info">
                Showing ${start} to ${end} of ${total} entries
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">`;
        
        // Previous button
        html += `<li class="page-item ${page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="admin.changePage(${page - 1})">Previous</a>
                 </li>`;
        
        // Page numbers
        const startPage = Math.max(1, page - 2);
        const endPage = Math.min(pages, page + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i === page ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="admin.changePage(${i})">${i}</a>
                     </li>`;
        }
        
        // Next button
        html += `<li class="page-item ${page === pages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="admin.changePage(${page + 1})">Next</a>
                 </li>`;
        
        html += `</ul></nav></div>`;
        return html;
    }
    
    changePage(page) {
        this.currentPage = page;
        this.loadTabContent(this.currentTab);
    }
    
    createModal(title, content, size = 'modal-lg') {
        const modalId = 'dynamicModal';
        
        // Remove existing modal
        const existingModal = document.getElementById(modalId);
        if (existingModal) existingModal.remove();
        
        const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog ${size}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>`;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        return new bootstrap.Modal(document.getElementById(modalId));
    }
    
    renderErrorHTML(title, message) {
        return `
        <div class="text-center py-5">
            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
            <h5>${title}</h5>
            <p class="text-muted">${message}</p>
            <button class="btn btn-primary" onclick="admin.loadTabContent('${this.currentTab}')">
                <i class="fas fa-refresh me-2"></i>Retry
            </button>
        </div>`;
    }
    
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
    
    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    truncateText(text, length = 50) {
        if (text.length <= length) return text;
        return text.substring(0, length) + '...';
    }
    
    formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }
}

// Initialize the admin dashboard
let admin;
document.addEventListener('DOMContentLoaded', function() {
    admin = new AdminDashboard();
});

// Make admin globally available for onclick handlers
window.admin = admin;
