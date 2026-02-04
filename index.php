<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();

// Get featured products
$query = "SELECT * FROM products ORDER BY created_at DESC LIMIT 6";
$stmt = $db->prepare($query);
$stmt->execute();
$featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Beranda - E-Commerce Store";
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="hero-section">
    <div class="hero-content">
        <h1>Selamat Datang di Toko Kami</h1>
        <p>Temukan produk terbaik dengan harga terjangkau</p>
        <a href="user/index.php" class="btn btn-primary">Belanja Sekarang</a>
    </div>
</div>

<div class="container">
    <section class="featured-products">
        <h2>Produk Terbaru</h2>
        <div class="products-grid">
            <?php foreach($featured_products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="assets/img/products/<?php echo $product['image'] ?: 'default.jpg'; ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                    <div class="product-info">
                        <h3><?php echo $product['name']; ?></h3>
                        <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
                        <p class="product-stock">Stok: <?php echo $product['stock']; ?></p>
                        <a href="user/product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">Lihat Detail</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>