<?php
function getDbConnection() {
    static $db = null;
    if ($db === null) {
        $dbFile = __DIR__ . '/../../db/webshop.sqlite';
        try {
            $db = new PDO('sqlite:' . $dbFile);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    return $db;
}

function getProducts() {
    $db = getDbConnection();
    $stmt = $db->query("SELECT * FROM products ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductById($id) {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function initCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function addToCart($productId, $quantity) {
    initCart();
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

function removeFromCart($productId) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

function getCartProducts() {
    $cartProducts = [];
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $product = getProductById($productId);
            if ($product) {
                $product['quantity'] = $quantity;
                $product['total'] = $product['price'] * $quantity;
                $cartProducts[] = $product;
            }
        }
    }
    return $cartProducts;
}

function getCartTotal() {
    $total = 0;
    foreach (getCartProducts() as $product) {
        $total += $product['total'];
    }
    return $total;
}

function validateQuantity($quantity) {
    return is_numeric($quantity) && $quantity > 0;
}

function formatPrice($price) {
    return number_format($price, 2);
}

function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?> 