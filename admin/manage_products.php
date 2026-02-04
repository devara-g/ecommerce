<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$database = new Database();
$db = $database->getConnection();

// Handle product actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'add':
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            $price = floatval($_POST['price']);
            $stock = intval($_POST['stock']);
            $category_id = intval($_POST['category_id']);
            
            // Handle image upload
            $image = 'default.jpg';
            if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $upload_dir = '../assets/img/products/';
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $image = uniqid() . '.' . $file_extension;
                
                if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image)) {
                    // File uploaded successfully
                }
            }
            
            $query = "INSERT INTO products (name, description, price, stock, image, category_id) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$name, $description, $price, $stock, $image, $category_id]);
            
            $success = "Produk berhasil ditambahkan!";
            break;
            
        case 'edit':
            $id = intval($_POST['id']);
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            $price = floatval($_POST['price']);
            $stock = intval($_POST['stock']);
            $category_id = intval($_POST['category_id']);
            
            $query = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$name, $description, $price, $stock, $category_id, $id]);
            
            $success = "Produk berhasil diupdate!";
            break;
            
        case 'delete':
            $id = intval($_POST['id']);
            $query = "DELETE FROM products WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$id]);
            
            $success = "Produk berhasil dihapus!";
            break;
    }
}

// Get all products
$query = "SELECT p.*, c.name as category_name FROM products p 
         LEFT JOIN categories c ON p.category_id = c.id 
         ORDER BY p.created_at DESC";
$products = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Get categories
$categories = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Kelola Produk";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Kelola Produk</h1>
    </div>

    <?php if(isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="admin-tabs">
        <button class="tab-button active" onclick="openTab('products-list')">Daftar Produk</button>
        <button class="tab-button" onclick="openTab('add-product')">Tambah Produk</button>
    </div>

    <div id="products-list" class="tab-content active">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $product): ?>
                        <tr>
                            <td>
                                <img src="../assets/img/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="product-thumb">
                            </td>
                            <td><?php echo $product['name']; ?></td>
                            <td><?php echo $product['category_name']; ?></td>
                            <td><?php echo formatPrice($product['price']); ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td>
                                <button class="btn btn-sm" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">Edit</button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus produk ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="add-product" class="tab-content">
        <form method="POST" enctype="multipart/form-data" class="product-form">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label>Nama Produk:</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Deskripsi:</label>
                <textarea name="description" rows="4" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Harga:</label>
                    <input type="number" name="price" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Stok:</label>
                    <input type="number" name="stock" required>
                </div>
                
                <div class="form-group">
                    <label>Kategori:</label>
                    <select name="category_id" required>
                        <option value="">Pilih Kategori</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Gambar Produk:</label>
                <input type="file" name="image" accept="image/*">
            </div>
            
            <button type="submit" class="btn btn-primary">Tambah Produk</button>
        </form>
    </div>

    <!-- Edit Product Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Produk</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label>Nama Produk:</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi:</label>
                    <textarea name="description" id="edit_description" rows="4" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Harga:</label>
                        <input type="number" name="price" id="edit_price" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Stok:</label>
                        <input type="number" name="stock" id="edit_stock" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Kategori:</label>
                        <select name="category_id" id="edit_category_id" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Produk</button>
            </form>
        </div>
    </div>
</div>

<script>
function openTab(tabName) {
    const tabcontents = document.getElementsByClassName("tab-content");
    for (let i = 0; i < tabcontents.length; i++) {
        tabcontents[i].classList.remove("active");
    }
    
    const tabbuttons = document.getElementsByClassName("tab-button");
    for (let i = 0; i < tabbuttons.length; i++) {
        tabbuttons[i].classList.remove("active");
    }
    
    document.getElementById(tabName).classList.add("active");
    event.currentTarget.classList.add("active");
}

function editProduct(product) {
    document.getElementById('edit_id').value = product.id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_description').value = product.description;
    document.getElementById('edit_price').value = product.price;
    document.getElementById('edit_stock').value = product.stock;
    document.getElementById('edit_category_id').value = product.category_id;
    
    document.getElementById('editModal').style.display = 'block';
}

// Modal functionality
const modal = document.getElementById('editModal');
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