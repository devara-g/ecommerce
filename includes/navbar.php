<?php
require_once 'functions.php';
?>
<nav class="navbar">
    <div class="nav-brand">
        <a href="../index.php">E-Commerce Store</a>
    </div>
    
    <div class="nav-search">
        <form action="../user/index.php" method="GET">
            <input type="text" name="search" placeholder="Cari produk..." value="<?php echo isset($_GET['search']) ? sanitize($_GET['search']) : ''; ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>
    
    <div class="nav-links">
        <a href="../index.php"><i class="fas fa-home"></i> Beranda</a>
        <a href="../user/index.php"><i class="fas fa-store"></i> Produk</a>
        
        <?php if(isLoggedIn()): ?>
            <a href="../user/cart.php">
                <i class="fas fa-shopping-cart"></i> Keranjang 
                <?php if(getCartCount() > 0): ?>
                    <span class="cart-count"><?php echo getCartCount(); ?></span>
                <?php endif; ?>
            </a>
            <a href="../user/order_history.php"><i class="fas fa-history"></i> Riwayat</a>
            
            <?php if(isAdmin()): ?>
                <a href="../admin/index.php"><i class="fas fa-cog"></i> Admin</a>
            <?php endif; ?>
            
            <a href="../user/profile.php"><i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?></a>
            <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php else: ?>
            <a href="auth/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            <a href="../auth/register.php"><i class="fas fa-user-plus"></i> Register</a>
        <?php endif; ?>
    </div>
    
    <div class="nav-toggle">
        <i class="fas fa-bars"></i>
    </div>
</nav>