<?php
$dbFile = __DIR__ . '/webshop.sqlite';

if (file_exists($dbFile)) {
    echo "Database already exists at {$dbFile}\n";
    exit;
}

try {
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            price REAL NOT NULL,
            image TEXT NOT NULL
        );
    ");

    $products = [
        ['name' => "Молоко пастеризоване", "price" => 18.00, "image" => "milk.png"],
        ['name' => "Хліб чорний", "price" => 13.50, "image" => "bread.png"],
        ['name' => "Сир білий", "price" => 31.50, "image" => "cheese.png"],
        ['name' => "Сметана 20%", "price" => 37.50, "image" => "sour-cream.png"],
        ['name' => "Кефір 1%", "price" => 28.50, "image" => "kefir.png"],
        ['name' => "Яйця курячі (10 шт)", "price" => 42.00, "image" => "eggs.png"],
        ['name' => "Олія соняшникова", "price" => 78.00, "image" => "oil.png"],
        ['name' => "Ковбаса варена", "price" => 111.00, "image" => "sausage.png"],
        ['name' => "Макарони", "price" => 27.00, "image" => "pasta.png"],
        ['name' => "Крупа гречана", "price" => 52.50, "image" => "buckwheat.png"],
        ['name' => "Йогурт фруктовий", "price" => 25.50, "image" => "yogurt.png"]
    ];

    $stmt = $db->prepare("INSERT INTO products (name, price, image) VALUES (?, ?, ?)");
    foreach ($products as $product) {
        $stmt->execute([$product['name'], $product['price'], $product['image']]);
    }

    echo "Database created and products inserted successfully at {$dbFile}\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?> 