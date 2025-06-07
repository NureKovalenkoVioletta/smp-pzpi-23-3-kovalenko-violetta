<?php
session_start();
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Інтернет-магазин</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="/home.php">Головна</a></li>
                <li><a href="/index.php">Товари</a></li>
                <?php if (empty($_SESSION['user'])): ?>
                    <li><a href="/login.php">Login</a></li>
                <?php else: ?>
                    <li><a href="/basket.php">Кошик</a></li>
                    <li><a href="/profile_form.php">Профіль</a></li>
                    <li><a href="/logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main> 