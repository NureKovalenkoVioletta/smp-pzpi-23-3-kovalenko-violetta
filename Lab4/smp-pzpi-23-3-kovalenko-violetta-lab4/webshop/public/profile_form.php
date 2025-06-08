<?php
require_once 'includes/header.php';
if (empty($_SESSION['user'])) {
    header('Location: /page404.php');
    exit;
}

$user = include 'profile.php';

function getAge($dob) {
    $today = new DateTime();
    $birthdate = new DateTime($dob);
    $age = $today->diff($birthdate)->y;
    return $age;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $about = trim($_POST['about'] ?? '');
    $photo = $user['photo'];

    if (!$name || strlen($name) < 2) $errors[] = "Ім'я має містити більше 1 символу.";
    if (!$surname || strlen($surname) < 2) $errors[] = "Прізвище має містити більше 1 символу.";
    if (!$dob || getAge($dob) < 16) $errors[] = "Вам має бути не менше 16 років.";
    if (!$about || strlen($about) < 50) $errors[] = "Стисла інформація має містити не менше 50 символів.";

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['photo']['type'], $allowed)) {
            $errors[] = "Фото має бути у форматі JPG, PNG або GIF.";
        } else {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = 'assets/images/profile_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__ . '/' . $filename)) {
                $photo = '/' . $filename;
            } else {
                $errors[] = "Не вдалося завантажити фото.";
            }
        }
    }

    if (!$errors) {
        $data = [
            'name' => $name,
            'surname' => $surname,
            'dob' => $dob,
            'about' => $about,
            'photo' => $photo
        ];
        file_put_contents('profile.php', "<?php\nreturn " . var_export($data, true) . ";\n");
        $user = $data;
        $success = "Дані збережено!";
    } 
}

?>

<h2 style="text-align: center;">Профіль користувача</h2>

<?php if ($errors): ?>
    <ul class="error">
        <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
    </ul>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success" style="color:green; margin-bottom:1em; padding: 10px; background-color: #e8f5e9; border-radius: 4px; border: 1px solid #c8e6c9;">
        <strong>✓</strong> <?= $success ?>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" style="max-width:600px; margin:auto; background:#fff; padding:2em; border-radius:8px; box-shadow:0 2px 8px #ccc;">
    <div style="display:flex; gap:2em; align-items:flex-start;">
        <div>
            <img src="<?= htmlspecialchars($user['photo']) ?>" alt="Фото профілю" style="width:150px; height:150px; object-fit:cover; border-radius:8px; border:1px solid #ccc; background:#eee;">
            <br><br>
            <input type="file" name="photo" accept="image/*">
        </div>
        <div style="flex:1;">
            <label>Ім'я:<br><input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required style="width:100%;"></label><br><br>
            <label>Прізвище:<br><input type="text" name="surname" value="<?= htmlspecialchars($user['surname']) ?>" required style="width:100%;"></label><br><br>
            <label>Дата народження:<br><input type="date" name="dob" value="<?= htmlspecialchars($user['dob']) ?>" required style="width:100%;"></label><br><br>
            <label>Стисла інформація:<br><textarea name="about" required style="width:100%;height:80px;"><?= htmlspecialchars($user['about']) ?></textarea></label><br><br>
            <button type="submit">Зберегти</button>
        </div>
    </div>
</form>

<?php require_once 'includes/footer.php'; ?> 