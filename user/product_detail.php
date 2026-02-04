<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isset($_GET['id'])) {
    redirect('index.php');
}

$product_id = intval($_GET['id']);
$database = new Database();
$db = $database->getConnection();

$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$product) {
    redirect('index.php');
}

$page_title = $product['name'] . " - E-Commerce Store";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <div class="product-detail">
        <div class="product-images">
            <img src="../assets/img/products/<?php echo $product['image'] ?: 'default.jpg'; ?>" alt="<?php echo $product['name']; ?>">
        </div>
        
        <div class="product-info">
            <h1><?php echo $product['name']; ?></h1>
            <p class="product-category">Kategori: <?php echo $product['category_name']; ?></p>
            <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
            <p class="product-stock">Stok: <?php echo $product['stock']; ?></p>
            
            <div class="product-description">
                <h3>Deskripsi Produk</h3>
                <p><?php echo nl2br($product['description']); ?></p>
            </div>
            
            <?php if($product['stock'] > 0): ?>
                <form method="POST" action="cart.php" class="add-to-cart-form">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="quantity-selector">
                        <label>Jumlah:</label>
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-large">Tambah ke Keranjang</button>
                </form>
            <?php else: ?>
                <button class="btn btn-disabled btn-large" disabled>Stok Habis</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>