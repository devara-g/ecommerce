<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$database = new Database();
$db = $database->getConnection();

// Handle order status update
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = intval($_POST['order_id']);
    $status = sanitize($_POST['status']);
    
    $query = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$status, $order_id]);
    
    $success = "Status pesanan berhasil diupdate!";
}

// Get all orders
$query = "SELECT o.*, u.username, u.email FROM orders o 
         JOIN users u ON o.user_id = u.id 
         ORDER BY o.created_at DESC";
$orders = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Kelola Pesanan";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Kelola Pesanan</h1>
    </div>

    <?php if(isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Metode Bayar</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td>
                            <strong><?php echo $order['username']; ?></strong><br>
                            <small><?php echo $order['email']; ?></small>
                        </td>
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
                        <td><?php echo ucfirst($order['payment_method']); ?></td>
                        <td><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-sm" onclick="viewOrder(<?php echo $order['id']; ?>)">Detail</button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" onchange="this.form.submit()" class="status-select">
                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                                    <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Diproses</option>
                                    <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Selesai</option>
                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Batal</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Order Detail Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content large">
        <span class="close">&times;</span>
        <div id="orderDetails"></div>
    </div>
</div>

<script>
function viewOrder(orderId) {
    fetch(`get_order_details.php?id=${orderId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('orderDetails').innerHTML = data;
            document.getElementById('orderModal').style.display = 'block';
        });
}

// Modal functionality
const modal = document.getElementById('orderModal');
const span = document.getElementsByClassName('close')[0];

span.onclick = function() {
    modal.style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>