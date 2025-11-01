-- =====================================================
-- AWDP (TechCorp Solutions) - Complete Database Schema
-- Supabase PostgreSQL Database Schema
-- =====================================================
-- 
-- This file contains the complete database schema for the AWDP project.
-- Copy and paste this entire file into your Supabase SQL Editor to create
-- all tables, indexes, triggers, and sample data.
--
-- Tables:
--   1. contacts - Contact form submissions
--   2. testimonials - Client testimonials and reviews
--   3. services - Services offered by the company
--   4. projects - Portfolio projects and case studies
--   5. blog_posts - Blog articles and news posts
--   6. newsletter_subscribers - Email newsletter subscriptions
--
-- Last Updated: 2025-11-01
-- =====================================================

-- Enable necessary extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- =====================================================
-- 1. CONTACTS TABLE
-- =====================================================
-- Stores contact form submissions from website visitors
CREATE TABLE IF NOT EXISTS contacts (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    company VARCHAR(255),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'new' CHECK (status IN ('new', 'read', 'replied'))
);

-- Create indexes for contacts table
CREATE INDEX IF NOT EXISTS idx_contacts_email ON contacts(email);
CREATE INDEX IF NOT EXISTS idx_contacts_created_at ON contacts(created_at);
CREATE INDEX IF NOT EXISTS idx_contacts_status ON contacts(status);

-- Add comments for documentation
COMMENT ON TABLE contacts IS 'Contact form submissions from website visitors';
COMMENT ON COLUMN contacts.status IS 'Status of the contact: new, read, or replied';

-- =====================================================
-- 2. TESTIMONIALS TABLE
-- =====================================================
-- Stores client testimonials and reviews
CREATE TABLE IF NOT EXISTS testimonials (
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
);

-- Create indexes for testimonials table
CREATE INDEX IF NOT EXISTS idx_testimonials_featured ON testimonials(is_featured);
CREATE INDEX IF NOT EXISTS idx_testimonials_active ON testimonials(is_active);
CREATE INDEX IF NOT EXISTS idx_testimonials_rating ON testimonials(rating);

-- Add comments for documentation
COMMENT ON TABLE testimonials IS 'Client testimonials and reviews';
COMMENT ON COLUMN testimonials.is_featured IS 'Whether testimonial should be displayed on homepage';
COMMENT ON COLUMN testimonials.is_active IS 'Whether testimonial is active and visible';

-- =====================================================
-- 3. SERVICES TABLE
-- =====================================================
-- Stores services offered by the company
CREATE TABLE IF NOT EXISTS services (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    features JSONB,
    price_range VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for services table
CREATE INDEX IF NOT EXISTS idx_services_active ON services(is_active);
CREATE INDEX IF NOT EXISTS idx_services_sort_order ON services(sort_order);

-- Add comments for documentation
COMMENT ON TABLE services IS 'Services offered by the company';
COMMENT ON COLUMN services.features IS 'JSON array of service features';
COMMENT ON COLUMN services.sort_order IS 'Display order (lower numbers appear first)';

-- =====================================================
-- 4. PROJECTS TABLE
-- =====================================================
-- Stores portfolio projects and case studies
CREATE TABLE IF NOT EXISTS projects (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    technologies JSONB,
    image_url VARCHAR(500),
    project_url VARCHAR(500),
    github_url VARCHAR(500),
    client_name VARCHAR(255),
    completion_date DATE,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for projects table
CREATE INDEX IF NOT EXISTS idx_projects_featured ON projects(is_featured);
CREATE INDEX IF NOT EXISTS idx_projects_active ON projects(is_active);
CREATE INDEX IF NOT EXISTS idx_projects_client ON projects(client_name);
CREATE INDEX IF NOT EXISTS idx_projects_completion_date ON projects(completion_date);

-- Add comments for documentation
COMMENT ON TABLE projects IS 'Portfolio projects and case studies';
COMMENT ON COLUMN projects.technologies IS 'JSON array of technologies used';
COMMENT ON COLUMN projects.is_featured IS 'Whether project should be displayed on homepage';

-- =====================================================
-- 5. BLOG_POSTS TABLE
-- =====================================================
-- Stores blog articles and news posts
CREATE TABLE IF NOT EXISTS blog_posts (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    excerpt TEXT,
    content TEXT,
    author VARCHAR(255),
    featured_image VARCHAR(500),
    tags JSONB,
    is_published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for blog_posts table
CREATE INDEX IF NOT EXISTS idx_blog_posts_slug ON blog_posts(slug);
CREATE INDEX IF NOT EXISTS idx_blog_posts_published ON blog_posts(is_published);
CREATE INDEX IF NOT EXISTS idx_blog_posts_published_at ON blog_posts(published_at);
CREATE INDEX IF NOT EXISTS idx_blog_posts_author ON blog_posts(author);

-- Add comments for documentation
COMMENT ON TABLE blog_posts IS 'Blog articles and news posts';
COMMENT ON COLUMN blog_posts.slug IS 'URL-friendly unique identifier for the post';
COMMENT ON COLUMN blog_posts.tags IS 'JSON array of post tags/categories';

-- =====================================================
-- 6. NEWSLETTER_SUBSCRIBERS TABLE
-- =====================================================
-- Stores email newsletter subscriptions
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL
);

-- Create indexes for newsletter_subscribers table
CREATE INDEX IF NOT EXISTS idx_newsletter_email ON newsletter_subscribers(email);
CREATE INDEX IF NOT EXISTS idx_newsletter_active ON newsletter_subscribers(is_active);
CREATE INDEX IF NOT EXISTS idx_newsletter_subscribed_at ON newsletter_subscribers(subscribed_at);

-- Add comments for documentation
COMMENT ON TABLE newsletter_subscribers IS 'Email newsletter subscription list';
COMMENT ON COLUMN newsletter_subscribers.is_active IS 'Whether subscription is active';

-- =====================================================
-- TRIGGERS FOR AUTOMATIC TIMESTAMP UPDATES
-- =====================================================

-- Create trigger function for updating updated_at column
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Drop existing triggers if they exist (to avoid errors on re-run)
DROP TRIGGER IF EXISTS update_testimonials_updated_at ON testimonials;
DROP TRIGGER IF EXISTS update_services_updated_at ON services;
DROP TRIGGER IF EXISTS update_projects_updated_at ON projects;
DROP TRIGGER IF EXISTS update_blog_posts_updated_at ON blog_posts;

-- Apply updated_at triggers to tables
CREATE TRIGGER update_testimonials_updated_at 
    BEFORE UPDATE ON testimonials 
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_services_updated_at 
    BEFORE UPDATE ON services 
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_projects_updated_at 
    BEFORE UPDATE ON projects 
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_blog_posts_updated_at 
    BEFORE UPDATE ON blog_posts 
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();

-- =====================================================
-- SAMPLE DATA (OPTIONAL)
-- =====================================================
-- Uncomment the sections below if you want to insert sample data

-- Insert sample services (configuration data - recommended to include)
INSERT INTO services (title, description, icon, features, price_range, sort_order) VALUES
('Web Development', 'Custom web applications built with modern frameworks and responsive design principles.', 'fas fa-code', '["Responsive Design", "Modern Frameworks", "Database Integration", "SEO Optimization"]', '$2,000 - $10,000', 1),
('Mobile Development', 'Native and cross-platform mobile applications for iOS and Android devices.', 'fas fa-mobile-alt', '["iOS & Android", "Cross-platform", "User-friendly UI/UX", "App Store Optimization"]', '$5,000 - $25,000', 2),
('Cloud Solutions', 'Scalable cloud infrastructure and migration services for modern businesses.', 'fas fa-cloud', '["Cloud Migration", "Infrastructure Setup", "24/7 Support", "Auto Scaling"]', '$1,000 - $15,000', 3),
('Database Design', 'Robust database architecture and optimization for high-performance applications.', 'fas fa-database', '["Schema Design", "Query Optimization", "Data Migration", "Backup Solutions"]', '$1,500 - $8,000', 4),
('API Development', 'RESTful and GraphQL APIs for seamless application integration.', 'fas fa-plug', '["RESTful APIs", "GraphQL", "Documentation", "Security Best Practices"]', '$2,500 - $12,000', 5),
('DevOps Services', 'Automated deployment pipelines and infrastructure as code solutions.', 'fas fa-cogs', '["CI/CD Pipelines", "Infrastructure as Code", "Monitoring", "Container Orchestration"]', '$3,000 - $15,000', 6)
ON CONFLICT DO NOTHING;

-- =====================================================
-- SAMPLE TESTIMONIALS (Optional - uncomment if needed)
-- =====================================================
/*
INSERT INTO testimonials (name, company, position, testimonial, rating, is_featured, is_active) VALUES
('Sarah Johnson', 'Digital Marketing Pro', 'CEO', 'TechCorp Solutions transformed our business with their innovative web platform. The team''s expertise and dedication exceeded our expectations.', 5, TRUE, TRUE),
('Michael Chen', 'StartupVenture Inc.', 'CTO', 'Outstanding mobile app development. They delivered a high-quality solution on time and within budget.', 5, TRUE, TRUE),
('Emily Rodriguez', 'HealthTech Solutions', 'Product Manager', 'The cloud migration services were seamless. Our infrastructure is now more scalable and secure than ever.', 5, FALSE, TRUE),
('David Wilson', 'E-commerce Plus', 'Founder', 'Professional team with excellent communication. They understood our requirements perfectly and delivered beyond expectations.', 5, TRUE, TRUE),
('Lisa Thompson', 'Tech Innovations', 'VP Engineering', 'Reliable, efficient, and creative solutions. TechCorp is our go-to partner for all development needs.', 5, FALSE, TRUE)
ON CONFLICT DO NOTHING;
*/

-- =====================================================
-- SAMPLE PROJECTS (Optional - uncomment if needed)
-- =====================================================
/*
INSERT INTO projects (title, description, technologies, client_name, is_featured, is_active) VALUES
('E-commerce Platform', 'Modern e-commerce solution with real-time inventory management', '["React", "Node.js", "PostgreSQL", "Stripe"]', 'RetailCo', TRUE, TRUE),
('Healthcare Portal', 'Patient management system with telemedicine capabilities', '["Vue.js", "Django", "MongoDB", "WebRTC"]', 'HealthFirst', TRUE, TRUE),
('Financial Dashboard', 'Real-time analytics dashboard for financial data visualization', '["Angular", "Python", "Redis", "Chart.js"]', 'FinTech Corp', FALSE, TRUE)
ON CONFLICT DO NOTHING;
*/

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================
-- Use these queries to verify the schema was created successfully:

-- Check all tables
-- SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name;

-- Check table row counts
-- SELECT 
--     'contacts' as table_name, COUNT(*) as row_count FROM contacts
-- UNION ALL SELECT 'testimonials', COUNT(*) FROM testimonials
-- UNION ALL SELECT 'services', COUNT(*) FROM services
-- UNION ALL SELECT 'projects', COUNT(*) FROM projects
-- UNION ALL SELECT 'blog_posts', COUNT(*) FROM blog_posts
-- UNION ALL SELECT 'newsletter_subscribers', COUNT(*) FROM newsletter_subscribers;

-- =====================================================
-- SCHEMA CREATION COMPLETE
-- =====================================================
-- All tables, indexes, triggers, and sample data have been created.
-- You can now use your application with this database schema.
-- =====================================================
