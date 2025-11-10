<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OUTSINC Installation</title>
    <link rel="stylesheet" href="/public/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container" style="max-width: 800px; margin: 3rem auto;">
        <div class="card">
            <div class="card-header">
                <h1><i class="fas fa-cog"></i> OUTSINC Installation</h1>
                <p>Set up your OUTSINC platform</p>
            </div>
            <div class="card-body">
                <?php
                $step = $_GET['step'] ?? 1;
                $message = '';
                $error = '';
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
                    // Get database credentials
                    $host = $_POST['db_host'] ?? 'localhost';
                    $user = $_POST['db_user'] ?? 'root';
                    $pass = $_POST['db_pass'] ?? '';
                    $dbname = $_POST['db_name'] ?? 'outsinc';
                    
                    try {
                        // Connect to MySQL
                        $conn = new mysqli($host, $user, $pass);
                        
                        if ($conn->connect_error) {
                            throw new Exception("Connection failed: " . $conn->connect_error);
                        }
                        
                        // Create database
                        $conn->query("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        $conn->select_db($dbname);
                        
                        // Read and execute schema
                        $schema = file_get_contents(__DIR__ . '/database/schema.sql');
                        
                        // Remove the CREATE DATABASE and USE commands from schema
                        $schema = preg_replace('/CREATE DATABASE.*?;/s', '', $schema);
                        $schema = preg_replace('/USE.*?;/s', '', $schema);
                        
                        // Split into individual queries
                        $queries = array_filter(array_map('trim', explode(';', $schema)));
                        
                        foreach ($queries as $query) {
                            if (!empty($query)) {
                                if (!$conn->query($query)) {
                                    throw new Exception("Error executing query: " . $conn->error);
                                }
                            }
                        }
                        
                        // Update config file
                        $configContent = file_get_contents(__DIR__ . '/includes/config.php');
                        $configContent = preg_replace("/define\('DB_HOST', '.*?'\);/", "define('DB_HOST', '$host');", $configContent);
                        $configContent = preg_replace("/define\('DB_USER', '.*?'\);/", "define('DB_USER', '$user');", $configContent);
                        $configContent = preg_replace("/define\('DB_PASS', '.*?'\);/", "define('DB_PASS', '$pass');", $configContent);
                        $configContent = preg_replace("/define\('DB_NAME', '.*?'\);/", "define('DB_NAME', '$dbname');", $configContent);
                        file_put_contents(__DIR__ . '/includes/config.php', $configContent);
                        
                        $conn->close();
                        
                        $message = 'Installation successful! You can now <a href="/public/index.php">visit your site</a>.';
                        $step = 3;
                        
                    } catch (Exception $e) {
                        $error = "Installation failed: " . $e->getMessage();
                    }
                }
                
                if ($step == 1): ?>
                    <h2>Welcome!</h2>
                    <p>This wizard will help you set up OUTSINC on your server.</p>
                    
                    <div style="margin: 2rem 0;">
                        <h3>Requirements Check:</h3>
                        <ul style="list-style: none; padding: 0;">
                            <li style="padding: 0.5rem; margin: 0.5rem 0; background: var(--light-color); border-radius: 8px;">
                                <i class="fas fa-check" style="color: var(--secondary-color);"></i>
                                PHP Version: <?php echo PHP_VERSION; ?> 
                                <?php echo version_compare(PHP_VERSION, '7.4.0', '>=') ? '✓' : '✗ (7.4+ required)'; ?>
                            </li>
                            <li style="padding: 0.5rem; margin: 0.5rem 0; background: var(--light-color); border-radius: 8px;">
                                <i class="fas fa-check" style="color: var(--secondary-color);"></i>
                                MySQLi Extension: <?php echo extension_loaded('mysqli') ? '✓ Loaded' : '✗ Not loaded'; ?>
                            </li>
                            <li style="padding: 0.5rem; margin: 0.5rem 0; background: var(--light-color); border-radius: 8px;">
                                <i class="fas fa-check" style="color: var(--secondary-color);"></i>
                                Upload Directory: <?php echo is_writable(__DIR__ . '/public/uploads') ? '✓ Writable' : '✗ Not writable'; ?>
                            </li>
                        </ul>
                    </div>
                    
                    <a href="?step=2" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Next: Database Setup
                    </a>
                    
                <?php elseif ($step == 2): ?>
                    <h2>Database Configuration</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger" style="padding: 1rem; background: var(--danger-color); color: white; border-radius: 8px; margin-bottom: 1rem;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="?step=2">
                        <div class="form-group">
                            <label for="db_host">Database Host</label>
                            <input type="text" id="db_host" name="db_host" value="localhost" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="db_user">Database Username</label>
                            <input type="text" id="db_user" name="db_user" value="root" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="db_pass">Database Password</label>
                            <input type="password" id="db_pass" name="db_pass">
                            <small>Leave blank if no password</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="db_name">Database Name</label>
                            <input type="text" id="db_name" name="db_name" value="outsinc" required>
                            <small>Will be created if it doesn't exist</small>
                        </div>
                        
                        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                            <a href="?step=1" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <button type="submit" name="install" class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-download"></i> Install OUTSINC
                            </button>
                        </div>
                    </form>
                    
                <?php elseif ($step == 3): ?>
                    <div style="text-align: center; padding: 2rem;">
                        <i class="fas fa-check-circle" style="font-size: 5rem; color: var(--secondary-color); margin-bottom: 1rem;"></i>
                        <h2>Installation Complete!</h2>
                        
                        <?php if ($message): ?>
                            <div class="alert alert-success" style="padding: 1rem; background: var(--secondary-color); color: white; border-radius: 8px; margin: 1rem 0;">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div style="background: var(--light-color); padding: 1.5rem; border-radius: 8px; margin: 2rem 0; text-align: left;">
                            <h3>Default Admin Account:</h3>
                            <p><strong>Email:</strong> admin@outsinc.org</p>
                            <p><strong>Password:</strong> admin123</p>
                            <p style="color: var(--danger-color); margin-top: 1rem;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Important:</strong> Change this password immediately after logging in!
                            </p>
                        </div>
                        
                        <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                            <a href="/public/index.php" class="btn btn-primary">
                                <i class="fas fa-home"></i> Go to Homepage
                            </a>
                            <a href="/public/dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </div>
                        
                        <p style="margin-top: 2rem; color: #7f8c8d;">
                            <i class="fas fa-info-circle"></i>
                            For security, please delete or rename this install.php file.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 2rem; color: #7f8c8d;">
            <p>OUTSINC - Outreach Someone In Need of Change</p>
        </div>
    </div>
</body>
</html>
