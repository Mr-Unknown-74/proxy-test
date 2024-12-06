<?php
// Start a secure session
session_start();

// Set authentication credentials
define('AUTH_USER', 'yourUsername'); // Replace with your username
define('AUTH_PASS', 'yourPassword'); // Replace with your password

// Function to check authentication
function checkAuth() {
    if (!isset($_SERVER['PHP_AUTH_USER']) || 
        $_SERVER['PHP_AUTH_USER'] !== AUTH_USER || 
        $_SERVER['PHP_AUTH_PW'] !== AUTH_PASS) {
        header('WWW-Authenticate: Basic realm="Secure Web Proxy"');
        header('HTTP/1.0 401 Unauthorized');
        echo "Unauthorized access.";
        exit;
    }
}

// Enforce HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

// Check authentication
checkAuth();

// Function to fetch a URL
function fetchUrl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For HTTPS
    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Proxy Browser'); // Optional: Set a custom User-Agent
    $output = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return "Error: " . htmlspecialchars($error);
    }

    return $output;
}

// Handle the proxy functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);

    if (filter_var($url, FILTER_VALIDATE_URL)) {
        echo fetchUrl($url);
    } else {
        echo "Invalid URL.";
    }
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Web Proxy</title>
</head>
<body>
    <h1>Secure Web Proxy</h1>
    <form method="POST" style="margin: 20px 0;">
        <input type="url" name="url" placeholder="Enter a URL (e.g., https://example.com)" required style="width: 70%; padding: 8px;">
        <button type="submit" style="padding: 8px 15px;">Browse</button>
    </form>
</body>
</html>
<?php
}
?>
