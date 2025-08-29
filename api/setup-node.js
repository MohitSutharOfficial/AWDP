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

  if (req.method === 'POST') {
    try {
      await client.connect();
      
      // Create tables
      const tables = [
        `CREATE TABLE IF NOT EXISTS contacts (
          id SERIAL PRIMARY KEY,
          name VARCHAR(255) NOT NULL,
          email VARCHAR(255) NOT NULL,
          phone VARCHAR(20),
          company VARCHAR(255),
          subject VARCHAR(255),
          message TEXT NOT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          status VARCHAR(20) DEFAULT 'new' CHECK (status IN ('new', 'read', 'replied'))
        )`,
        
        `CREATE TABLE IF NOT EXISTS testimonials (
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
        )`
      ];
      
      for (const sql of tables) {
        await client.query(sql);
      }
      
      // Insert sample testimonials
      const testimonials = [
        ['Sarah Johnson', 'Digital Marketing Pro', 'CEO', 'TechCorp Solutions transformed our business with their innovative web platform. The team\'s expertise and dedication exceeded our expectations.', 5, true],
        ['Michael Chen', 'StartupVenture Inc.', 'CTO', 'Outstanding mobile app development. They delivered a high-quality solution on time and within budget.', 5, true],
        ['Emily Rodriguez', 'HealthTech Solutions', 'Product Manager', 'The cloud migration services were seamless. Our infrastructure is now more scalable and secure than ever.', 5, false]
      ];
      
      for (const [name, company, position, testimonial, rating, is_featured] of testimonials) {
        try {
          await client.query(
            'INSERT INTO testimonials (name, company, position, testimonial, rating, is_featured) VALUES ($1, $2, $3, $4, $5, $6) ON CONFLICT DO NOTHING',
            [name, company, position, testimonial, rating, is_featured]
          );
        } catch (e) {
          // Ignore duplicate entries
        }
      }
      
      await client.end();
      
      return res.status(200).json({ 
        success: true, 
        message: 'Database tables created and sample data inserted successfully!' 
      });
      
    } catch (error) {
      console.error('Database setup error:', error);
      return res.status(500).json({ 
        success: false, 
        error: 'Failed to setup database: ' + error.message 
      });
    }
  }

  // GET request - return setup page HTML
  const html = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - TechCorp Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .setup-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        .btn-setup {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 12px 40px;
            color: white;
            font-weight: 600;
        }
        .btn-setup:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .info-card {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            border: 2px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="setup-container">
                    <div class="text-center mb-4">
                        <h1 class="fw-bold text-primary">
                            <i class="fas fa-database me-3"></i>Database Setup
                        </h1>
                        <p class="lead text-muted">Initialize TechCorp Solutions Database</p>
                    </div>
                    
                    <div class="info-card">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-info-circle me-2"></i>Setup Information
                        </h4>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i><strong>Database:</strong> Supabase PostgreSQL</li>
                            <li><i class="fas fa-check text-success me-2"></i><strong>Tables:</strong> contacts, testimonials</li>
                            <li><i class="fas fa-check text-success me-2"></i><strong>Sample Data:</strong> Customer testimonials</li>
                            <li><i class="fas fa-check text-success me-2"></i><strong>Connection:</strong> SSL secured</li>
                        </ul>
                    </div>
                    
                    <div id="message"></div>
                    
                    <div class="text-center mb-4">
                        <button onclick="setupDatabase()" class="btn btn-setup btn-lg">
                            <i class="fas fa-cog me-2"></i>Initialize Database
                        </button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card">
                                <h5 class="text-primary">
                                    <i class="fas fa-table me-2"></i>Contacts Table
                                </h5>
                                <p class="text-muted mb-0">Stores contact form submissions with validation</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <h5 class="text-primary">
                                    <i class="fas fa-star me-2"></i>Testimonials Table
                                </h5>
                                <p class="text-muted mb-0">Manages client reviews and ratings</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="/" class="btn btn-outline-primary">
                            <i class="fas fa-home me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function setupDatabase() {
            document.getElementById('message').innerHTML = 
                '<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Setting up database...</div>';
            
            try {
                const response = await fetch('/setup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('message').innerHTML = 
                        '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>' + result.message + '</div>';
                } else {
                    document.getElementById('message').innerHTML = 
                        '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>' + result.error + '</div>';
                }
            } catch (error) {
                document.getElementById('message').innerHTML = 
                    '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Setup failed. Please try again.</div>';
            }
        }
    </script>
</body>
</html>`;

  res.setHeader('Content-Type', 'text/html');
  res.status(200).send(html);
};
