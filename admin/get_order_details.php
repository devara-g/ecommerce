<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

if(!isset($_GET['id'])) {
    die('Order ID required');
}

$order_id = intval($_GET['id']);
$database = new Database();
$db = $database->getConnection();

// Get order details
$order_query = "SELECT o.*, u.username, u.email, u.phone FROM orders o 
               JOIN users u ON o.user_id = u.id 
               WHERE o.id = ?";
$order_stmt = $db->prepare($order_query);
$order_stmt->execute([$order_id]);
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

if(!$order) {
    die('Order not found');
}

// Get order items
$items_query = "SELECT oi.*, p.name, p.image FROM order_items oi 
               JOIN products p ON oi.product_id = p.id 
               WHERE oi.order_id = ?";
$items_stmt = $db->prepare($items_query);
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="order-details-modal">
    <h2>Detail Pesanan #<?php echo $order['id']; ?></h2>
    
    <div class="order-info">
        <h3>Informasi Customer</h3>
        <p><strong>Nama:</strong> <?php echo $order['username']; ?></p>
        <p><strong>Email:</strong> <?php echo $order['email']; ?></p>
        <?php if($order['phone']): ?>
            <p><strong>Telepon:</strong> <?php echo $order['phone']; ?></p>
        <?php endif; ?>
    </div>
    
    <div class="order-shipping">
        <h3>Alamat Pengiriman</h3>
        <p><?php echo nl2br($order['shipping_address']); ?></p>
    </div>
    
    <div class="order-payment">
        <h3>Informasi Pembayaran</h3>
        <p><strong>Metode:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
        <p><strong>Total:</strong> <?php echo formatPrice($order['total_amount']); ?></p>
        <p><strong>Status:</strong> 
            <span class="status-badge status-<?php echo $order['status']; ?>">
                <?php 
                $status_text = [
                    'pending' => 'Menunggu',
                    'processing' => 'Diproses',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan'
                ];
                echo $status_text[$order['status']];
                ?>
            </span>
        </p>
    </div>
    
    <?php if($order['notes']): ?>
        <div class="order-notes">
            <h3>Catatan</h3>
            <p><?php echo nl2br($order['notes']); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="order-items">
        <h3>Items Pesanan</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <img src="../assets/img/products/<?php echo $item['image'] ?: 'default.jpg'; ?>" alt="<?php echo $item['name']; ?>">
                                <span><?php echo $item['name']; ?></span>
                            </div>
                        </td>
                        <td><?php echo formatPrice($item['price']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                    <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>