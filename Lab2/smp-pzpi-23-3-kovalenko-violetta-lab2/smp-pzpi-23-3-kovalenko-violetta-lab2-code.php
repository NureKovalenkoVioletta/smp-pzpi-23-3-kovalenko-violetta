<?php

$productsIndexed = require 'products.php';

$products = [];
foreach ($productsIndexed as $product) {
    $products[$product['id']] = $product;
}

$cart = [];
$profile = [
    'name' => '',
    'age' => 0
];

function utf8_strlen($str) {
    return preg_match_all('/./us', $str);
}

function utf8_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT) {
    $input_length = utf8_strlen($input);
    $diff = $pad_length - $input_length;
    if ($diff > 0) {
        if ($pad_type === STR_PAD_RIGHT) {
            return $input . str_repeat($pad_string, $diff);
        } elseif ($pad_type === STR_PAD_LEFT) {
            return str_repeat($pad_string, $diff) . $input;
        } elseif ($pad_type === STR_PAD_BOTH) {
            $left = floor($diff / 2);
            $right = $diff - $left;
            return str_repeat($pad_string, $left) . $input . str_repeat($pad_string, $right);
        }
    }
    return $input;
}

function getMaxLengths($products) {
    $maxId = utf8_strlen('№');
    $maxName = utf8_strlen('НАЗВА');
    $maxPrice = utf8_strlen('ЦІНА');

    foreach ($products as $p) {
        $idLen = utf8_strlen((string)$p['id']);
        $nameLen = utf8_strlen($p['name']);
        $priceLen = utf8_strlen((string)$p['price']);

        if ($idLen > $maxId) $maxId = $idLen;
        if ($nameLen > $maxName) $maxName = $nameLen;
        if ($priceLen > $maxPrice) $maxPrice = $priceLen;
    }
    return ['id' => $maxId, 'name' => $maxName, 'price' => $maxPrice];
}

function displayMainMenu() {
    echo "################################\n";
    echo "# ПРОДОВОЛЬЧИЙ МАГАЗИН \"ВЕСНА\" #\n";
    echo "################################\n";
    echo "1 Вибрати товари\n";
    echo "2 Отримати підсумковий рахунок\n";
    echo "3 Налаштувати свій профіль\n";
    echo "0 Вийти з програми\n";
    echo "Введіть команду: ";
}

function displayProducts($products) {
    $maxLengths = getMaxLengths($products);
    $idWidth = $maxLengths['id'] + 2;
    $nameWidth = $maxLengths['name'] + 2;
    $priceWidth = $maxLengths['price'] + 2;

    echo utf8_str_pad('№', $idWidth);
    echo utf8_str_pad('НАЗВА', $nameWidth);
    echo utf8_str_pad('ЦІНА', $priceWidth);
    echo "\n";

    foreach ($products as $product) {
        echo utf8_str_pad((string)$product['id'], $idWidth);
        echo utf8_str_pad($product['name'], $nameWidth);
        echo utf8_str_pad((string)$product['price'], $priceWidth);
        echo "\n";
    }
    echo str_repeat('-', $idWidth + $nameWidth + $priceWidth) . "\n";
    echo "0  ПОВЕРНУТИСЯ\n";
    echo "Виберіть товар: ";
}

function displayCart($cart, $products) {
    if (empty($cart)) {
        echo "КОШИК ПОРОЖНІЙ\n";
        return;
    }

    echo "У КОШИКУ:\n";
    echo utf8_str_pad("НАЗВА", 15);
    echo "КІЛЬКІСТЬ\n";

    foreach ($cart as $productId => $quantity) {
        if (!isset($products[$productId])) continue;

        $product = $products[$productId];
        echo utf8_str_pad($product['name'], 15);
        echo $quantity . "\n";
    }
}

function displayBill($cart, $products) {
    if (empty($cart)) {
        echo "КОШИК ПОРОЖНІЙ\n";
        return;
    }

    $maxId = utf8_strlen('№');
    $maxName = utf8_strlen('НАЗВА');
    $maxPrice = utf8_strlen('ЦІНА');
    $maxQty = utf8_strlen('КІЛЬКІСТЬ');
    $maxCost = utf8_strlen('ВАРТІСТЬ');

    foreach ($cart as $productId => $qty) {
        if (!isset($products[$productId])) continue;

        $p = $products[$productId];
        $maxId = max($maxId, utf8_strlen((string)$p['id']));
        $maxName = max($maxName, utf8_strlen($p['name']));
        $maxPrice = max($maxPrice, utf8_strlen((string)$p['price']));
        $maxQty = max($maxQty, utf8_strlen((string)$qty));
        $cost = $p['price'] * $qty;
        $maxCost = max($maxCost, utf8_strlen((string)$cost));
    }

    $maxId += 2; $maxName += 2; $maxPrice += 2; $maxQty += 2; $maxCost += 2;

    echo utf8_str_pad('№', $maxId);
    echo utf8_str_pad('НАЗВА', $maxName);
    echo utf8_str_pad('ЦІНА', $maxPrice);
    echo utf8_str_pad('КІЛЬКІСТЬ', $maxQty);
    echo utf8_str_pad('ВАРТІСТЬ', $maxCost);
    echo "\n";

    $total = 0;
    $i = 1;
    foreach ($cart as $productId => $qty) {
        if (!isset($products[$productId])) continue;

        $p = $products[$productId];
        $cost = $p['price'] * $qty;
        $total += $cost;

        echo utf8_str_pad((string)$i++, $maxId);
        echo utf8_str_pad($p['name'], $maxName);
        echo utf8_str_pad((string)$p['price'], $maxPrice);
        echo utf8_str_pad((string)$qty, $maxQty);
        echo utf8_str_pad((string)$cost, $maxCost);
        echo "\n";
    }
    echo "РАЗОМ ДО CПЛАТИ: $total\n";
}

function validateName($name) {
    return preg_match('/[a-zA-Zа-яА-Я]/u', $name);
}

function validateAge($age) {
    return is_numeric($age) && $age >= 7 && $age <= 150;
}


while (true) {
    displayMainMenu();
    $choice = trim(fgets(STDIN));

    switch ($choice) {
        case '0':
            exit("Дякуємо за використання програми!\n");

        case '1':
            while (true) {
                displayProducts($products);
                $productChoice = trim(fgets(STDIN));

                if ($productChoice === '0') {
                    break;
                }

                if (!is_numeric($productChoice) || !isset($products[(int)$productChoice])) {
                    echo "ПОМИЛКА! ВКАЗАНО НЕПРАВИЛЬНИЙ НОМЕР ТОВАРУ\n";
                    continue;
                }

                $selectedProduct = $products[(int)$productChoice];
                echo "Вибрано: {$selectedProduct['name']}\n";
                echo "Введіть кількість, штук: ";
                $quantity = trim(fgets(STDIN));

                if (!is_numeric($quantity) || $quantity < 0 || $quantity >= 100) {
                    echo "ПОМИЛКА! Введіть кількість від 0 до 99\n";
                    continue;
                }

                if ($quantity === '0') {
                    if (isset($cart[$selectedProduct['id']])) {
                        unset($cart[$selectedProduct['id']]);
                        echo "ВИДАЛЯЮ З КОШИКА\n";
                    }
                } else {
                    $cart[$selectedProduct['id']] = (int)$quantity;
                }

                displayCart($cart, $products);
            }
            break;

        case '2':
            if (empty($cart)) {
                echo "КОШИК ПОРОЖНІЙ\n";
            } else {
                displayBill($cart, $products);
            }
            break;

        case '3':
            echo "Ваше імʼя: ";
            $name = trim(fgets(STDIN));
            while (!validateName($name)) {
                echo "ПОМИЛКА! Імʼя повинно містити хоча б одну літеру\n";
                echo "Ваше імʼя: ";
                $name = trim(fgets(STDIN));
            }

            echo "Ваш вік: ";
            $age = trim(fgets(STDIN));
            while (!validateAge($age)) {
                echo "ПОМИЛКА! Вік повинен бути від 7 до 150 років\n";
                echo "Ваш вік: ";
                $age = trim(fgets(STDIN));
            }

            $profile['name'] = $name;
            $profile['age'] = (int)$age;
            echo "Профіль успішно оновлено!\n";
            break;

        default:
            echo "ПОМИЛКА! Введіть правильну команду\n";
    }
}
