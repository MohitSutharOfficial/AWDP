module.exports = async (req, res) => {
  // Set CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  if (req.method === 'POST') {
    const { username, password } = req.body;
    
    // Simple authentication
    if (username === 'admin' && password === 'admin123') {
      return res.status(200).json({ 
        success: true, 
        message: 'Login successful',
        redirect: '/admin?logged=true'
      });
    } else {
      return res.status(401).json({ 
        success: false, 
        error: 'Invalid credentials' 
      });
    }
  }

  // GET request - return admin panel HTML
  const html = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - TechCorp Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .admin-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        .btn-admin {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 12px 40px;
            color: white;
            font-weight: 600;
        }
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .admin-card {
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
            <div class="col-lg-10">
                <div class="admin-container">
                    <div class="text-center mb-4">
                        <h1 class="fw-bold text-primary">
                            <i class="fas fa-user-shield me-3"></i>Admin Panel
                        </h1>
                        <p class="lead text-muted">TechCorp Solutions Management Dashboard</p>
                    </div>
                    
                    <div id="loginSection">
                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <div class="admin-card">
                                    <h3 class="text-center mb-4">
                                        <i class="fas fa-lock me-2"></i>Admin Login
                                    </h3>
                                    <div id="message"></div>
                                    <form id="loginForm">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" value="admin">
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="password" name="password" value="admin123">
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-admin">
                                                <i class="fas fa-sign-in-alt me-2"></i>Login
                                            </button>
                                        </div>
                                    </form>
                                    <div class="text-center mt-3">
                                        <small class="text-muted">Default: admin / admin123</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="dashboardSection" style="display: none;">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="admin-card text-center">
                                    <i class="fas fa-envelope fa-2x text-primary mb-3"></i>
                                    <h5>Contact Management</h5>
                                    <p class="text-muted">View and manage contact submissions</p>
                                    <button class="btn btn-outline-primary">
                                        <i class="fas fa-eye me-2"></i>View Contacts
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="admin-card text-center">
                                    <i class="fas fa-star fa-2x text-warning mb-3"></i>
                                    <h5>Testimonials</h5>
                                    <p class="text-muted">Manage customer testimonials</p>
                                    <a href="/testimonials" class="btn btn-outline-warning">
                                        <i class="fas fa-comments me-2"></i>Manage Reviews
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="admin-card text-center">
                                    <i class="fas fa-database fa-2x text-danger mb-3"></i>
                                    <h5>Database Setup</h5>
                                    <p class="text-muted">Initialize and manage database</p>
                                    <a href="/setup" class="btn btn-outline-danger">
                                        <i class="fas fa-cog me-2"></i>Setup Database
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button onclick="logout()" class="btn btn-outline-secondary">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </button>
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
        // Check if already logged in
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('logged') === 'true') {
            showDashboard();
        }

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch('/admin', {
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
                    setTimeout(showDashboard, 1000);
                } else {
                    document.getElementById('message').innerHTML = 
                        '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>' + result.error + '</div>';
                }
            } catch (error) {
                document.getElementById('message').innerHTML = 
                    '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Login failed. Please try again.</div>';
            }
        });

        function showDashboard() {
            document.getElementById('loginSection').style.display = 'none';
            document.getElementById('dashboardSection').style.display = 'block';
        }

        function logout() {
            document.getElementById('loginSection').style.display = 'block';
            document.getElementById('dashboardSection').style.display = 'none';
            document.getElementById('loginForm').reset();
            document.getElementById('message').innerHTML = '';
        }
    </script>
</body>
</html>`;

  res.setHeader('Content-Type', 'text/html');
  res.status(200).send(html);
};
