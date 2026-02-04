<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if(isLoggedIn()) {
    redirect('../index.php');
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if(empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$username, $username]);
        
        if($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                if($user['role'] === 'admin') {
                    redirect('../admin/index.php');
                } else {
                    redirect('../index.php');
                }
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Username/email tidak ditemukan!';
        }
    }
}

$page_title = "Login";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="auth-container">
    <div class="auth-form">
        <h2>Login ke Akun Anda</h2>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Username atau Email:</label>
                <input type="text" name="username" value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        
        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>