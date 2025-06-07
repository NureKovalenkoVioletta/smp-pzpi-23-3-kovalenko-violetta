<?php
require_once 'includes/header.php';
if (empty($_SESSION['user'])) {
    header('Location: /page404.php');
    exit;
}

if (isset($_GET['remove'])) {
    $productId = (int)$_GET['remove'];
    removeFromCart($productId);
    header('Location: basket.php');
    exit;
}

$cartProducts = getCartProducts();
$cartTotal = getCartTotal();
?>

<h1>Кошик</h1>

<?php if (empty($cartProducts)): ?>
    <div class="empty-cart">
        <p>Ваш кошик порожній.</p>
        <a href="index.php" class="button">Перейти до покупок</a>
    </div>
<?php else: ?>
    <table class="basket">
        <thead>
            <tr>
                <th>Товар</th>
                <th>Ціна</th>
                <th>Кількість</th>
                <th>Сума</th>
                <th>Дія</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cartProducts as $product): ?>
                <tr>
                    <td><?php echo h($product['name']); ?></td>
                    <td><?php echo formatPrice($product['price']); ?> ₴</td>
                    <td><?php echo h($product['quantity']); ?></td>
                    <td><?php echo formatPrice($product['total']); ?> ₴</td>
                    <td>
                        <a href="?remove=<?php echo $product['id']; ?>" 
                           class="remove-button"
                           onclick="return confirm('Ви впевнені, що хочете видалити цей товар?')">
                            Видалити
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="total-label">Загальна сума:</td>
                <td colspan="2" class="total-value"><?php echo formatPrice($cartTotal); ?> ₴</td>
            </tr>
        </tfoot>
    </table>

    <div class="basket-actions">
        <a href="index.php" class="button">Продовжити покупки</a>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?> 