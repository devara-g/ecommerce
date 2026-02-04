<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn()) {
    redirect('../auth/login.php');
}

$database = new Database();
$db = $database->getConnection();

$cartItems = getCartItems($db);
$cartTotal = getCartTotal($cartItems);

if(empty($cartItems)) {
    redirect('cart.php');
}

// Handle checkout
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shipping_address = sanitize($_POST['shipping_address']);
    $payment_method = sanitize($_POST['payment_method']);
    $notes = sanitize($_POST['notes']);
    
    if(empty($shipping_address) || empty($payment_method)) {
        $error = "Alamat pengiriman dan metode pembayaran harus diisi!";
    } else {
        try {
            $db->beginTransaction();
            
            // Create order
            $order_query = "INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, notes) 
                           VALUES (?, ?, ?, ?, ?)";
            $order_stmt = $db->prepare($order_query);
            $order_stmt->execute([
                $_SESSION['user_id'],
                $cartTotal,
                $shipping_address,
                $payment_method,
                $notes
            ]);
            
            $order_id = $db->lastInsertId();
            
            // Create order items and update product stock
            foreach($cartItems as $item) {
                $order_item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                                   VALUES (?, ?, ?, ?)";
                $order_item_stmt = $db->prepare($order_item_query);
                $order_item_stmt->execute([
                    $order_id,
                    $item['product']['id'],
                    $item['quantity'],
                    $item['product']['price']
                ]);
                
                // Update product stock
                $update_stock_query = "UPDATE products SET stock = stock - ? WHERE id = ?";
                $update_stock_stmt = $db->prepare($update_stock_query);
                $update_stock_stmt->execute([$item['quantity'], $item['product']['id']]);
            }
            
            $db->commit();
            
            // Clear cart
            unset($_SESSION['cart']);
            
            $success = "Pesanan berhasil dibuat! Nomor pesanan: #" . $order_id;
            
        } catch(Exception $e) {
            $db->rollBack();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Get user info for address
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = $db->prepare($user_query);
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

$page_title = "Checkout";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Checkout</h1>
    </div>

    <?php if(isset($success)): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
            <br>
            <a href="order_history.php" class="btn btn-primary">Lihat Riwayat Pesanan</a>
            <a href="index.php" class="btn btn-secondary">Lanjutkan Belanja</a>
        </div>
    <?php else: ?>
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="checkout-layout">
            <div class="checkout-form">
                <h2>Informasi Pengiriman & Pembayaran</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Alamat Pengiriman:</label>
                        <textarea name="shipping_address" rows="4" required><?php echo $user['address'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Metode Pembayaran:</label>
                        <select name="payment_method" required>
                            <option value="">Pilih Metode Pembayaran</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="cod">Cash on Delivery (COD)</option>
                            <option value="e-wallet">E-Wallet</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan (Opsional):</label>
                        <textarea name="notes" rows="3" placeholder="Catatan untuk penjual..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-large">Buat Pesanan</button>
                </form>
            </div>
            
            <div class="order-summary">
                <h2>Ringkasan Pesanan</h2>
                <div class="summary-items">
                    <?php foreach($cartItems as $item): ?>
                        <div class="summary-item">
                            <span class="item-name"><?php echo $item['product']['name']; ?> (x<?php echo $item['quantity']; ?>)</span>
                            <span class="item-price"><?php echo formatPrice($item['subtotal']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="summary-total">
                    <strong>Total: <?php echo formatPrice($cartTotal); ?></strong>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?> 