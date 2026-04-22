<?php
$host = 'localhost';
$dbname = 'geotraverse_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    echo "<h1>Password Reset Tokens</h1>";
    
    // Show all tokens
    $stmt = $pdo->query("SELECT id, email, token, expires_at, 
                         CASE WHEN expires_at > NOW() THEN 'Valid' ELSE 'Expired' END as status 
                         FROM password_resets ORDER BY id DESC");
    $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($tokens) == 0) {
        echo "<p>No tokens found in database.</p>";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Email</th><th>Token (first 20 chars)</th><th>Expires At</th><th>Status</th></tr>";
        foreach ($tokens as $token) {
            echo "<tr>";
            echo "<td>{$token['id']}</td>";
            echo "<td>{$token['email']}</td>";
            echo "<td>" . substr($token['token'], 0, 20) . "...</td>";
            echo "<td>{$token['expires_at']}</td>";
            echo "<td style='color:" . ($token['status'] == 'Valid' ? 'green' : 'red') . "'>{$token['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>