<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn()) {
    redirect('../auth/login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get user orders
$query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Riwayat Pesanan";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Riwayat Pesanan</h1>
    </div>

    <?php if(empty($orders)): ?>
        <div class="empty-orders">
            <i class="fas fa-receipt fa-3x"></i>
            <h2>Belum Ada Pesanan</h2>
            <p>Anda belum membuat pesanan apapun.</p>
            <a href="index.php" class="btn btn-primary">Mulai Belanja</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h3>Pesanan #<?php echo $order['id']; ?></h3>
                            <p class="order-date">Tanggal: <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div class="order-status">
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
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <div class="order-summary">
                            <p><strong>Total: <?php echo formatPrice($order['total_amount']); ?></strong></p>
                            <p>Metode Pembayaran: <?php echo ucfirst($order['payment_method']); ?></p>
                            <p>Alamat: <?php echo $order['shipping_address']; ?></p>
                            <?php if($order['notes']): ?>
                                <p>Catatan: <?php echo $order['notes']; ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="order-items">
                            <h4>Items:</h4>
                            <?php
                            $items_query = "SELECT oi.*, p.name, p.image FROM order_items oi 
                                          JOIN products p ON oi.product_id = p.id 
                                          WHERE oi.order_id = ?";
                            $items_stmt = $db->prepare($items_query);
                            $items_stmt->execute([$order['id']]);
                            $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach($items as $item): ?>
                                <div class="order-item">
                                    <img src="../assets/img/products/<?php echo $item['image'] ?: 'default.jpg'; ?>" alt="<?php echo $item['name']; ?>">
                                    <div class="item-info">
                                        <h5><?php echo $item['name']; ?></h5>
                                        <p>Qty: <?php echo $item['quantity']; ?> x <?php echo formatPrice($item['price']); ?></p>
                                    </div>
                                    <div class="item-total">
                                        <?php echo formatPrice($item['quantity'] * $item['price']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>