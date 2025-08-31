<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechCorp Solutions - Welcome</title>
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
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 mb-4">
                <i class="fas fa-rocket me-3"></i>
                TechCorp Solutions
            </h1>
            <p class="lead mb-4">Advanced Web Development Project - Railway Deployment</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card bg-white text-dark p-4">
                        <h3>Quick Navigation</h3>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="/home" class="btn btn-primary"><i class="fas fa-home me-2"></i>Home</a>
                            <a href="/contact" class="btn btn-success"><i class="fas fa-envelope me-2"></i>Contact</a>
                            <a href="/admin" class="btn btn-warning"><i class="fas fa-cog me-2"></i>Admin</a>
                            <a href="/health.php" class="btn btn-info"><i class="fas fa-heartbeat me-2"></i>Health</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <small class="text-light">
                    Deployment Status: 
                    <span class="badge bg-success">
                        <i class="fas fa-check-circle me-1"></i>Online
                    </span>
                    | PHP <?php echo PHP_VERSION; ?>
                </small>
            </div>
        </div>
    </div>
</body>
</html>
