<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatPrice($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

function getCartCount() {
    if(isset($_SESSION['cart'])) {
        return array_sum($_SESSION['cart']);
    }
    return 0;
}

function addToCart($product_id, $quantity = 1) {
    if(!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if(isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

function removeFromCart($product_id) {
    if(isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

function updateCartQuantity($product_id, $quantity) {
    if(isset($_SESSION['cart'][$product_id])) {
        if($quantity <= 0) {
            removeFromCart($product_id);
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }
}

function getCartItems($pdo) {
    $cartItems = [];
    if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $product_ids = array_keys($_SESSION['cart']);
        $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
        
        $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($product_ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($products as $product) {
            $cartItems[] = [
                'product' => $product,
                'quantity' => $_SESSION['cart'][$product['id']],
                'subtotal' => $product['price'] * $_SESSION['cart'][$product['id']]
            ];
        }
    }
    return $cartItems;
}

function getCartTotal($cartItems) {
    $total = 0;
    foreach($cartItems as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}
?>