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
      
      const { name, email, phone, company, subject, message } = req.body;
      
      // Validation
      if (!name || !email || !message) {
        return res.status(400).json({ 
          success: false, 
          error: 'Name, email, and message are required' 
        });
      }

      // Insert into database
      const query = `
        INSERT INTO contacts (name, email, phone, company, subject, message, created_at) 
        VALUES ($1, $2, $3, $4, $5, $6, NOW()) 
        RETURNING id
      `;
      
      const result = await client.query(query, [name, email, phone, company, subject, message]);
      
      await client.end();
      
      return res.status(200).json({ 
        success: true, 
        message: 'Thank you! Your message has been sent successfully.',
        id: result.rows[0].id
      });
      
    } catch (error) {
      console.error('Database error:', error);
      return res.status(500).json({ 
        success: false, 
        error: 'Failed to save contact information' 
      });
    }
  }

  // GET request - return contact form HTML
  const html = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - TechCorp Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .contact-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        .btn-submit {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 12px 40px;
            color: white;
            font-weight: 600;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="contact-container">
                    <div class="text-center mb-4">
                        <h1 class="fw-bold text-primary">
                            <i class="fas fa-envelope me-3"></i>Contact Us
                        </h1>
                        <p class="lead text-muted">Get in touch with TechCorp Solutions</p>
                    </div>
                    
                    <div id="message"></div>
                    
                    <form id="contactForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="company" class="form-label">Company</label>
                                <input type="text" class="form-control" id="company" name="company">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject">
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-submit btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </div>
                    </form>
                    
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
        document.getElementById('contactForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch('/contact', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('message').innerHTML = 
                        '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>' + result.message + '</div>';
                    this.reset();
                } else {
                    document.getElementById('message').innerHTML = 
                        '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>' + result.error + '</div>';
                }
            } catch (error) {
                document.getElementById('message').innerHTML = 
                    '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>An error occurred. Please try again.</div>';
            }
        });
    </script>
</body>
</html>`;

  res.setHeader('Content-Type', 'text/html');
  res.status(200).send(html);
};
