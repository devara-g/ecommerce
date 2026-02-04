<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn()) {
    redirect('../auth/login.php');
}

$database = new Database();
$db = $database->getConnection();

// Handle cart actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = intval($_POST['product_id'] ?? 0);
    
    switch($action) {
        case 'add':
            $quantity = intval($_POST['quantity'] ?? 1);
            addToCart($product_id, $quantity);
            break;
            
        case 'update':
            $quantity = intval($_POST['quantity'] ?? 0);
            updateCartQuantity($product_id, $quantity);
            break;
            
        case 'remove':
            removeFromCart($product_id);
            break;
            
        case 'clear':
            unset($_SESSION['cart']);
            break;
    }
    
    // Redirect to prevent form resubmission
    redirect('cart.php');
}

$cartItems = getCartItems($db);
$cartTotal = getCartTotal($cartItems);

$page_title = "Keranjang Belanja";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Keranjang Belanja</h1>
    </div>

    <?php if(empty($cartItems)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart fa-3x"></i>
            <h2>Keranjang Anda Kosong</h2>
            <p>Silakan tambahkan produk ke keranjang belanja Anda.</p>
            <a href="index.php" class="btn btn-primary">Lanjutkan Belanja</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-items">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cartItems as $item): ?>
                            <tr>
                                <td class="product-info">
                                    <img src="../assets/img/products/<?php echo $item['product']['image'] ?: 'default.jpg'; ?>" alt="<?php echo $item['product']['name']; ?>">
                                    <div>
                                        <h4><?php echo $item['product']['name']; ?></h4>
                                        <p>Stok: <?php echo $item['product']['stock']; ?></p>
                                    </div>
                                </td>
                                <td class="product-price"><?php echo formatPrice($item['product']['price']); ?></td>
                                <td class="product-quantity">
                                    <form method="POST" action="cart.php" class="quantity-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                               min="1" max="<?php echo $item['product']['stock']; ?>">
                                        <button type="submit" class="btn btn-sm">Update</button>
                                    </form>
                                </td>
                                <td class="product-subtotal"><?php echo formatPrice($item['subtotal']); ?></td>
                                <td class="product-actions">
                                    <form method="POST" action="cart.php">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-actions">
                    <form method="POST" action="cart.php">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-danger">Kosongkan Keranjang</button>
                    </form>
                    <a href="index.php" class="btn btn-secondary">Lanjutkan Belanja</a>
                </div>
            </div>
            
            <div class="cart-summary">
                <div class="summary-card">
                    <h3>Ringkasan Belanja</h3>
                    <div class="summary-row">
                        <span>Total Harga:</span>
                        <span><?php echo formatPrice($cartTotal); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Bayar:</span>
                        <span><?php echo formatPrice($cartTotal); ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary btn-large">Proses Checkout</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>