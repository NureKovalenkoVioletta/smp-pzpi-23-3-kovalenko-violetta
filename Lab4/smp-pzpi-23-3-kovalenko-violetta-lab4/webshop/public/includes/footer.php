    </main>
    <footer>
        <div class="footer-nav">
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
        </div>
    </footer>
</body>
</html> 