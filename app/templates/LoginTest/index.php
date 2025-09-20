<!DOCTYPE html>
<html>
<head>
    <title>Admin Login Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .message { font-size: 18px; margin: 20px 0; }
        a { color: #007bff; text-decoration: none; padding: 10px 20px; border: 1px solid #007bff; border-radius: 4px; }
        a:hover { background-color: #007bff; color: white; }
    </style>
</head>
<body>
    <h1>🔐 Admin Authentication Test</h1>
    <div class="message"><?= $message ?></div>
    
    <p><strong>Instructions:</strong></p>
    <ol>
        <li>If you're not logged in, click "Login Here"</li>
        <li>Use credentials: <code>admin@test.com</code> / <code>password123</code></li>
        <li>After login, you'll see a "Go to Admin Panel" link</li>
        <li>Click that link to access the admin products forms page</li>
    </ol>
</body>
</html>
