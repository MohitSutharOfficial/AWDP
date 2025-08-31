<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechCorp Solutions - Live!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: white;
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="container text-center">
            <div class="pulse mb-4">
                <i class="fas fa-rocket fa-5x text-warning"></i>
            </div>
            <h1 class="display-3 mb-4">
                🎉 TechCorp Solutions is LIVE! 🎉
            </h1>
            <p class="lead mb-4">Railway Deployment Successful - Advanced Web Development Project</p>
            
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card bg-white text-dark p-4 shadow-lg">
                        <h3 class="text-primary mb-4">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Application Status: ONLINE
                        </h3>
                        
                        <div class="row g-3">
                            <div class="col-md-3">
                                <a href="/home" class="btn btn-primary w-100 btn-lg">
                                    <i class="fas fa-home me-2"></i>Home
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="/contact" class="btn btn-success w-100 btn-lg">
                                    <i class="fas fa-envelope me-2"></i>Contact
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="/admin" class="btn btn-warning w-100 btn-lg">
                                    <i class="fas fa-cog me-2"></i>Admin Panel
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="/health" class="btn btn-info w-100 btn-lg">
                                    <i class="fas fa-heartbeat me-2"></i>Health Check
                                </a>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="row text-start">
                            <div class="col-md-6">
                                <h5 class="text-success">
                                    <i class="fas fa-server me-2"></i>Server Info
                                </h5>
                                <ul class="list-unstyled">
                                    <li><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></li>
                                    <li><strong>Server:</strong> Railway + Nixpacks</li>
                                    <li><strong>Database:</strong> SQLite (Auto-created)</li>
                                    <li><strong>Status:</strong> <span class="badge bg-success">Active</span></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5 class="text-primary">
                                    <i class="fas fa-tools me-2"></i>Features
                                </h5>
                                <ul class="list-unstyled">
                                    <li>✅ Contact Form with Validation</li>
                                    <li>✅ Admin Dashboard</li>
                                    <li>✅ Testimonials Management</li>
                                    <li>✅ Mobile Responsive Design</li>
                                    <li>✅ Real-time Data Operations</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <small class="text-light">
                    🚀 Deployed on Railway | 
                    📅 <?php echo date('Y-m-d H:i:s'); ?> | 
                    🔗 Repository: MohitSutharOfficial/AWDP
                </small>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-refresh status every 30 seconds
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
