const { Client } = require('pg');

// Supabase PostgreSQL connection
const client = new Client({
  host: 'db.brdavdukxvilpdzgbsqd.supabase.co',
  port: 5432,
  database: 'postgres',
  user: 'postgres',
  password: 'rsMwRvhAs3qxIWQ8',
  ssl: { rejectUnauthorized: false }
});

module.exports = async (req, res) => {
  // Set CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  let testimonials = [];
  
  try {
    await client.connect();
    const result = await client.query('SELECT * FROM testimonials WHERE is_active = true ORDER BY created_at DESC');
    testimonials = result.rows;
    await client.end();
  } catch (error) {
    console.error('Database error:', error);
    // Use fallback data if database fails
    testimonials = [
      {
        id: 1,
        name: 'Sarah Johnson',
        company: 'Digital Marketing Pro',
        position: 'CEO',
        testimonial: 'TechCorp Solutions transformed our business with their innovative web platform. The team\'s expertise and dedication exceeded our expectations.',
        rating: 5,
        is_featured: true
      },
      {
        id: 2,
        name: 'Michael Chen',
        company: 'StartupVenture Inc.',
        position: 'CTO',
        testimonial: 'Outstanding mobile app development. They delivered a high-quality solution on time and within budget.',
        rating: 5,
        is_featured: true
      },
      {
        id: 3,
        name: 'Emily Rodriguez',
        company: 'HealthTech Solutions',
        position: 'Product Manager',
        testimonial: 'The cloud migration services were seamless. Our infrastructure is now more scalable and secure than ever.',
        rating: 5,
        is_featured: false
      }
    ];
  }

  // GET request - return testimonials page HTML
  const html = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimonials - TechCorp Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .testimonials-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        .testimonial-card {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            border: 2px solid #dee2e6;
            transition: transform 0.3s ease;
        }
        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .stars {
            color: #ffc107;
        }
        .featured-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="testimonials-container">
            <div class="text-center mb-5">
                <h1 class="fw-bold text-primary">
                    <i class="fas fa-star me-3"></i>Client Testimonials
                </h1>
                <p class="lead text-muted">What our clients say about TechCorp Solutions</p>
            </div>
            
            <div class="row">
                ${testimonials.map(testimonial => `
                    <div class="col-lg-4 mb-4">
                        <div class="testimonial-card h-100">
                            ${testimonial.is_featured ? '<div class="text-center mb-3"><span class="featured-badge">Featured</span></div>' : ''}
                            
                            <div class="text-center mb-3">
                                <div class="stars mb-2">
                                    ${'★'.repeat(testimonial.rating)}${'☆'.repeat(5 - testimonial.rating)}
                                </div>
                                <h5 class="fw-bold text-primary">${testimonial.name}</h5>
                                <p class="text-muted mb-0">${testimonial.position}</p>
                                <p class="text-muted"><strong>${testimonial.company}</strong></p>
                            </div>
                            
                            <blockquote class="text-center">
                                <p class="fst-italic">"${testimonial.testimonial}"</p>
                            </blockquote>
                        </div>
                    </div>
                `).join('')}
            </div>
            
            <div class="text-center mt-5">
                <div class="row">
                    <div class="col-md-4">
                        <h3 class="text-primary">50+</h3>
                        <p class="text-muted">Happy Clients</p>
                    </div>
                    <div class="col-md-4">
                        <h3 class="text-primary">100+</h3>
                        <p class="text-muted">Projects Completed</p>
                    </div>
                    <div class="col-md-4">
                        <h3 class="text-primary">98%</h3>
                        <p class="text-muted">Client Satisfaction</p>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="/contact" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-envelope me-2"></i>Get Started
                </a>
                <a href="/" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-home me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>`;

  res.setHeader('Content-Type', 'text/html');
  res.status(200).send(html);
};
