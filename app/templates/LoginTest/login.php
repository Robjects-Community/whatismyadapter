<!DOCTYPE html>
<html>
<head>
    <title>Simple Admin Login</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 400px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="email"], input[type="password"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .error { color: red; margin: 10px 0; }
        .credentials { background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>🔐 Simple Admin Login</h1>
    
    <?= $this->Flash->render() ?>
    
    <div class="credentials">
        <strong>Use these credentials:</strong><br>
        Email: <code>admin@test.com</code><br>
        Password: <code>password123</code>
    </div>
    
    <?= $this->Form->create() ?>
        <div class="form-group">
            <?= $this->Form->label('email', 'Email') ?>
            <?= $this->Form->email('email', ['required' => true, 'value' => 'admin@test.com']) ?>
        </div>
        
        <div class="form-group">
            <?= $this->Form->label('password', 'Password') ?>
            <?= $this->Form->password('password', ['required' => true, 'value' => 'password123']) ?>
        </div>
        
        <?= $this->Form->button('Login', ['type' => 'submit']) ?>
    <?= $this->Form->end() ?>
    
    <p><a href="<?= $this->Url->build(['action' => 'index']) ?>">← Back to Test Page</a></p>
</body>
</html>
