<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

// Handle search and filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? intval($_GET['category']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : '';

// Build query
$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if(!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if(!empty($category)) {
    $query .= " AND p.category_id = ?";
    $params[] = $category;
}

if(!empty($min_price)) {
    $query .= " AND p.price >= ?";
    $params[] = $min_price;
}

if(!empty($max_price)) {
    $query .= " AND p.price <= ?";
    $params[] = $max_price;
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$cat_query = "SELECT * FROM categories";
$cat_stmt = $db->prepare($cat_query);
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Produk - E-Commerce Store";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Daftar Produk</h1>
    </div>

    <div class="products-layout">
        <aside class="filter-sidebar">
            <h3>Filter Produk</h3>
            <form method="GET" action="">
                <div class="form-group">
                    <label>Cari:</label>
                    <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Nama produk...">
                </div>
                
                <div class="form-group">
                    <label>Kategori:</label>
                    <select name="category">
                        <option value="">Semua Kategori</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo $cat['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Harga Minimum:</label>
                    <input type="number" name="min_price" value="<?php echo $min_price; ?>" placeholder="Rp">
                </div>
                
                <div class="form-group">
                    <label>Harga Maksimum:</label>
                    <input type="number" name="max_price" value="<?php echo $max_price; ?>" placeholder="Rp">
                </div>
                
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <a href="index.php" class="btn btn-secondary">Reset</a>
            </form>
        </aside>

        <main class="products-main">
            <div class="products-grid">
                <?php if(count($products) > 0): ?>
                    <?php foreach($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="../assets/img/products/<?php echo $product['image'] ?: 'default.jpg'; ?>" alt="<?php echo $product['name']; ?>">
                            </div>
                            <div class="product-info">
                                <h3><?php echo $product['name']; ?></h3>
                                <p class="product-category"><?php echo $product['category_name']; ?></p>
                                <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
                                <p class="product-stock">Stok: <?php echo $product['stock']; ?></p>
                                <div class="product-actions">
                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">Detail</a>
                                    <?php if($product['stock'] > 0): ?>
                                        <form method="POST" action="cart.php" style="display: inline;">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn btn-primary">Tambah ke Keranjang</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-disabled" disabled>Stok Habis</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>Tidak ada produk yang ditemukan.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>