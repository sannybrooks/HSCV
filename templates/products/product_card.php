<div class="product-card">
    <img src="assets/images/product1.jpg" alt="<?php echo htmlspecialchars($product['name']); ?>">
    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
    <p><?php echo htmlspecialchars($product['description']); ?></p>
    <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn">Подробнее</a>
</div>