<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get statistics
$products_count = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$users_count = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$orders_count = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_orders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

// Get recent orders
$recent_orders_query = "SELECT o.*, u.username FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       ORDER BY o.created_at DESC LIMIT 5";
$recent_orders = $db->query($recent_orders_query)->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Dashboard Admin";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Dashboard Admin</h1>
    </div>

    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $products_count; ?></h3>
                <p>Total Produk</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $users_count; ?></h3>
                <p>Total Pengguna</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $orders_count; ?></h3>
                <p>Total Pesanan</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $pending_orders; ?></h3>
                <p>Pesanan Menunggu</p>
            </div>
        </div>
    </div>

    <div class="admin-content">
        <div class="recent-orders">
            <h2>Pesanan Terbaru</h2>
            <?php if($recent_orders): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo $order['username']; ?></td>
                                <td><?php echo formatPrice($order['total_amount']); ?></td>
                                <td>
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
                                </td>
                                <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="manage_orders.php?action=view&id=<?php echo $order['id']; ?>" class="btn btn-sm">Lihat</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Tidak ada pesanan.</p>
            <?php endif; ?>
        </div>

        <div class="admin-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="manage_products.php" class="btn btn-primary">
                    <i class="fas fa-box"></i> Kelola Produk
                </a>
                <a href="manage_orders.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Kelola Pesanan
                </a>
                <a href="manage_users.php" class="btn btn-primary">
                    <i class="fas fa-users"></i> Kelola Pengguna
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>