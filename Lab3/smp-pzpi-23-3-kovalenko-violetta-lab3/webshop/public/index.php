<?php
require_once 'includes/header.php';

$error = '';
$products = getProducts();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hasError = false;
    
    foreach ($_POST['quantity'] as $productId => $quantity) {
        if ($quantity > 0 && !validateQuantity($quantity)) {
            $hasError = true;
            break;
        }
    }
    
    if (!$hasError) {
        foreach ($_POST['quantity'] as $productId => $quantity) {
            if ($quantity > 0) {
                addToCart($productId, (int)$quantity);
            }
        }
        header('Location: basket.php');
        exit;
    } else {
        $error = 'Будь ласка, перевірте введені значення.';
    }
}
?>

<h1>Товари</h1>

<?php if ($error): ?>
    <div class="error"><?php echo h($error); ?></div>
<?php endif; ?>

<form method="POST" action="">
    <div class="products-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="product-image-container">
                    <img src="/assets/images/<?php echo h($product['image']); ?>" 
                         alt="<?php echo h($product['name']); ?>"
                         class="product-image">
                </div>
                <div class="product-info">
                    <h3 class="product-name"><?php echo h($product['name']); ?></h3>
                    <p class="product-price"><?php echo formatPrice($product['price']); ?> ₴</p>
                    <div class="product-quantity">
                        <label for="quantity-<?php echo $product['id']; ?>">Кількість:</label>
                        <input type="number" 
                               id="quantity-<?php echo $product['id']; ?>"
                               name="quantity[<?php echo $product['id']; ?>]" 
                               value="<?php echo isset($_POST['quantity'][$product['id']]) ? h($_POST['quantity'][$product['id']]) : '0'; ?>"
                               min="0"
                               required>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="form-actions">
        <button type="submit">Додати до кошика</button>
    </div>
</form>

<?php require_once 'includes/footer.php'; ?> 