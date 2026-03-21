<?php
require_once 'includes/db_cloud.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; }
        .status { padding: 20px; border-radius: 8px; margin: 20px 0; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    </style>
</head>
<body>
    <h1>🔍 Database Connection Test</h1>
    
    <?php
    if ($conn) {
        echo '<div class="status success">';
        echo '<h2>✅ Connection Successful!</h2>';
        echo '<p><strong>Database Host:</strong> ' . DB_HOST . ':' . DB_PORT . '</p>';
        echo '<p><strong>Database Name:</strong> ' . DB_NAME . '</p>';
        echo '<p><strong>Environment:</strong> ' . ($isProduction ? 'Production (Cloud)' : 'Development (Local)') . '</p>';
        
        // Test table creation
        $test_query = "CREATE TABLE IF NOT EXISTS test_table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            message VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (mysqli_query($conn, $test_query)) {
            echo '<p><strong>✅ Table creation works!</strong></p>';
            
            // Test insert
            $insert_query = "INSERT INTO test_table (message) VALUES ('Connection test at " . date('Y-m-d H:i:s') . "')";
            if (mysqli_query($conn, $insert_query)) {
                echo '<p><strong>✅ Data insertion works!</strong></p>';
                
                // Test select
                $select_query = "SELECT * FROM test_table ORDER BY id DESC LIMIT 1";
                $result = mysqli_query($conn, $select_query);
                if ($row = mysqli_fetch_assoc($result)) {
                    echo '<p><strong>✅ Data retrieval works!</strong></p>';
                    echo '<p><strong>Last test message:</strong> ' . htmlspecialchars($row['message']) . '</p>';
                }
            }
        }
        
        echo '</div>';
        
        // Check if main tables exist
        echo '<div class="status info">';
        echo '<h3>📊 Table Status:</h3>';
        
        $tables = ['users', 'inventory', 'orders', 'qr_settings'];
        foreach ($tables as $table) {
            $check_query = "SHOW TABLES LIKE '$table'";
            $result = mysqli_query($conn, $check_query);
            if (mysqli_num_rows($result) > 0) {
                echo "✅ $table table exists<br>";
            } else {
                echo "❌ $table table missing<br>";
            }
        }
        echo '</div>';
        
    } else {
        echo '<div class="status error">';
        echo '<h2>❌ Connection Failed!</h2>';
        echo '<p><strong>Error:</strong> ' . mysqli_connect_error() . '</p>';
        echo '<p>Please check your Railway connection details.</p>';
        echo '</div>';
    }
    ?>
    
    <div style="margin-top: 30px;">
        <a href="setup_railway.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-right: 10px;">← Back to Setup</a>
        <a href="pages/qrmanage.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">QR Management →</a>
    </div>
</body>
</html>
