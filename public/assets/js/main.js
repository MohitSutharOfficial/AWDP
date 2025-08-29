// Main JavaScript for TechCorp Solutions
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize all components
    initNavigation();
    initPortfolio();
    initAnimations();
    initContactForm();
    initScrollEffects();
    
});

// Navigation functionality
function initNavigation() {
    const navbar = document.getElementById('mainNav');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Handle scroll effects on navigation
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Smooth scrolling for navigation links
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Only handle internal links (starting with #)
            if (href && href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                
                if (target) {
                    const offsetTop = target.offsetTop - 70; // Account for fixed navbar
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
}

// Portfolio section with dynamic content
function initPortfolio() {
    const portfolioContainer = document.getElementById('portfolioItems');
    
    if (!portfolioContainer) return;
    
    const portfolioItems = [
        {
            title: 'E-Commerce Platform',
            description: 'Modern online shopping platform with payment integration',
            icon: 'fas fa-shopping-cart',
            technologies: ['React', 'Node.js', 'MongoDB', 'Stripe']
        },
        {
            title: 'Healthcare Management System',
            description: 'Comprehensive patient management and scheduling system',
            icon: 'fas fa-hospital-alt',
            technologies: ['Vue.js', 'Laravel', 'MySQL', 'Chart.js']
        },
        {
            title: 'Financial Dashboard',
            description: 'Real-time financial analytics and reporting dashboard',
            icon: 'fas fa-chart-line',
            technologies: ['Angular', 'Python', 'PostgreSQL', 'D3.js']
        },
        {
            title: 'Educational Platform',
            description: 'Online learning management system with video streaming',
            icon: 'fas fa-graduation-cap',
            technologies: ['Next.js', 'Express', 'Redis', 'AWS']
        },
        {
            title: 'IoT Monitoring System',
            description: 'Real-time IoT device monitoring and control platform',
            icon: 'fas fa-microchip',
            technologies: ['React', 'Socket.io', 'InfluxDB', 'Docker']
        },
        {
            title: 'Social Media App',
            description: 'Feature-rich social networking mobile application',
            icon: 'fas fa-users',
            technologies: ['React Native', 'Firebase', 'GraphQL', 'Redux']
        }
    ];
    
    portfolioContainer.innerHTML = portfolioItems.map(item => `
        <div class="col-lg-4 col-md-6">
            <div class="portfolio-item">
                <div class="portfolio-image">
                    <i class="${item.icon}"></i>
                </div>
                <div class="portfolio-content">
                    <h5>${item.title}</h5>
                    <p class="text-muted">${item.description}</p>
                    <div class="portfolio-tech">
                        ${item.technologies.map(tech => `<span class="tech-tag">${tech}</span>`).join('')}
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

// Animation effects
function initAnimations() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                entry.target.classList.add('animate-fade-in');
            }
        });
    }, observerOptions);
    
    // Observe service cards, portfolio items, and other elements
    document.querySelectorAll('.service-card, .portfolio-item, .stat-card').forEach(el => {
        observer.observe(el);
    });
    
    // Typing effect for hero title (optional enhancement)
    const heroTitle = document.querySelector('.hero-content h1');
    if (heroTitle) {
        typeWriter(heroTitle);
    }
}

// Typing animation function
function typeWriter(element) {
    const text = element.textContent;
    element.textContent = '';
    element.style.borderRight = '3px solid white';
    
    let i = 0;
    const timer = setInterval(function() {
        element.textContent += text.charAt(i);
        i++;
        
        if (i === text.length) {
            clearInterval(timer);
            setTimeout(() => {
                element.style.borderRight = 'none';
            }, 1000);
        }
    }, 50);
}

// Contact form handling
function initContactForm() {
    const contactForm = document.getElementById('contactForm');
    
    if (!contactForm) return;
    
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<span class="loading"></span> Sending...';
        submitBtn.disabled = true;
        
        // Detect if we're on Vercel or traditional hosting
        const isVercel = window.location.hostname.includes('vercel.app') || 
                        window.location.hostname.includes('vercel.com') ||
                        document.querySelector('meta[name="vercel-deployment"]');
        
        let submitPromise;
        
        if (isVercel) {
            // Vercel serverless function - use JSON API
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            submitPromise = fetch('/api/contact', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
        } else {
            // Traditional PHP hosting
            submitPromise = fetch('contact.php', {
                method: 'POST',
                body: formData
            });
        }
        
        submitPromise
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message || 'Thank you! Your message has been sent successfully.');
                contactForm.reset();
            } else {
                showAlert('danger', data.message || 'Sorry, there was an error sending your message. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Sorry, there was an error sending your message. Please try again.');
        })
        .finally(() => {
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
}

// Show alert messages
function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

// Scroll effects
function initScrollEffects() {
    // Parallax effect for hero section
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.5;
        
        const heroSection = document.querySelector('.hero-section');
        if (heroSection) {
            heroSection.style.transform = `translateY(${rate}px)`;
        }
    });
    
    // Progress bar (optional)
    createScrollProgressBar();
}

// Create scroll progress bar
function createScrollProgressBar() {
    const progressBar = document.createElement('div');
    progressBar.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 3px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        z-index: 9999;
        transition: width 0.3s ease;
    `;
    
    document.body.appendChild(progressBar);
    
    window.addEventListener('scroll', function() {
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        progressBar.style.width = scrolled + '%';
    });
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Add smooth reveal animations for elements
function revealOnScroll() {
    const reveals = document.querySelectorAll('.reveal');
    
    reveals.forEach(reveal => {
        const windowHeight = window.innerHeight;
        const revealTop = reveal.getBoundingClientRect().top;
        const revealPoint = 150;
        
        if (revealTop < windowHeight - revealPoint) {
            reveal.classList.add('active');
        }
    });
}

// Form validation helpers
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[\+]?[1-9][\d]{0,15}$/;
    return re.test(phone.replace(/\s/g, ''));
}

// Local storage helpers
function saveToLocalStorage(key, data) {
    try {
        localStorage.setItem(key, JSON.stringify(data));
    } catch (e) {
        console.error('Error saving to localStorage:', e);
    }
}

function getFromLocalStorage(key) {
    try {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : null;
    } catch (e) {
        console.error('Error reading from localStorage:', e);
        return null;
    }
}

// Performance optimization
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript error:', e.error);
    // You could send this to an error tracking service
});

// Service worker registration (for PWA features)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('ServiceWorker registration successful');
            })
            .catch(function(err) {
                console.log('ServiceWorker registration failed');
            });
    });
}

// Export functions for global access
window.TechCorp = {
    showAlert,
    validateEmail,
    validatePhone,
    saveToLocalStorage,
    getFromLocalStorage
};
