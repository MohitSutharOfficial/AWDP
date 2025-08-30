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
    
    // Continue with other CRUD operations for services, projects, etc...
    // [Additional methods would continue here for Services, Projects, Blog, Newsletter tabs]
    
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
