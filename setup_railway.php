<!DOCTYPE html>
<html>
<head>
    <title>Railway Setup Helper</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .step { background: #f5f5f5; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .code { background: #000; color: #0f0; padding: 15px; border-radius: 4px; font-family: monospace; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🚀 Railway Database Setup</h1>
    
    <div class="step">
        <h2>Step 1: Get Railway Connection String</h2>
        <ol>
            <li>Go to <a href="https://railway.app" target="_blank">railway.app</a></li>
            <li>Click on your MySQL database</li>
            <li>Go to "Connect" tab</li>
            <li>Copy the "Connection URL"</li>
        </ol>
        
        <p><strong>Your connection string should look like:</strong></p>
        <div class="code">mysql://username:password@containers.railway.app:port/railway</div>
    </div>
    
    <div class="step">
        <h2>Step 2: Update Database Connection</h2>
        <p>Paste your Railway connection string below:</p>
        <form method="post">
            <label>Connection String:</label><br>
            <input type="text" name="connection_string" style="width: 100%; padding: 10px; margin: 10px 0;" placeholder="mysql://user:pass@host:port/db" required>
            <br>
            <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px;">Update Connection</button>
        </form>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['connection_string'])) {
            $conn_str = $_POST['connection_string'];
            
            // Parse the connection string
            if (preg_match('/mysql:\/\/([^:]+):([^@]+)@([^:]+):([^\/]+)\/(.+)/', $conn_str, $matches)) {
                $username = $matches[1];
                $password = $matches[2];
                $host = $matches[3];
                $port = $matches[4];
                $database = $matches[5];
                
                // Update db_cloud.php file
                $db_content = "<?php\n// includes/db_cloud.php — Cloud Database Connection for Production\n\n";
                $db_content .= "// Check if we're in production (Vercel) or development (localhost)\n";
                $db_content .= "\$isProduction = !empty(\$_SERVER['VERCEL']) || \n               (!empty(\$_SERVER['HTTP_HOST']) && \n                strpos(\$_SERVER['HTTP_HOST'], 'vercel.app') !== false) ||\n               (!empty(\$_SERVER['HTTP_HOST']) && \n                strpos(\$_SERVER['HTTP_HOST'], 'localhost') === false);\n\n";
                $db_content .= "if (\$isProduction) {\n";
                $db_content .= "    // Production: Railway Cloud Database\n";
                $db_content .= "    define('DB_HOST', '$host');\n";
                $db_content .= "    define('DB_USER', '$username');\n";
                $db_content .= "    define('DB_PASS', '$password');\n";
                $db_content .= "    define('DB_NAME', '$database');\n";
                $db_content .= "    define('DB_PORT', '$port');\n";
                $db_content .= "} else {\n";
                $db_content .= "    // Development: Local XAMPP\n";
                $db_content .= "    define('DB_HOST', 'localhost');\n";
                $db_content .= "    define('DB_USER', 'root');\n";
                $db_content .= "    define('DB_PASS', '');\n";
                $db_content .= "    define('DB_NAME', 'aaj_aqua_v2');\n";
                $db_content .= "    define('DB_PORT', '3306');\n";
                $db_content .= "}\n\n";
                $db_content .= file_get_contents('includes/db_cloud.php');
                
                file_put_contents('includes/db_cloud.php', $db_content);
                
                echo '<div class="success">✅ Database connection updated successfully!</div>';
            } else {
                echo '<div class="warning">❌ Invalid connection string format</div>';
            }
        }
        ?>
    </div>
    
    <div class="step">
        <h2>Step 3: Test Connection</h2>
        <p>After updating, test your connection:</p>
        <a href="test_connection.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Test Database Connection</a>
    </div>
</body>
</html>
