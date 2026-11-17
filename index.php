<?php
require_once __DIR__ . '/functions.php';

$titre = 'Connexion';

if (connecte()) {
    header('Location: liste.php');
    exit;
}

$erreurs = [];
$login = $_POST['login'] ?? ($_COOKIE['dernier_user'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = nettoyer($_POST['login'] ?? '');
    $mdp = $_POST['mdp'] ?? '';

    if ($login === '' || !isset(UTILISATEURS[$login]) || UTILISATEURS[$login] !== $mdp) {
        $erreurs[] = 'Identifiants incorrects.';
    } else {
        $_SESSION['user'] = $login;
        setcookie('dernier_user', $login, time() + 7 * 24 * 3600, '/');
        header('Location: liste.php');
        exit;
    }
}

include __DIR__ . '/header.php';
?>
<div class="card login-box">
    <?php if (!empty($erreurs)): ?>
        <ul class="error-list">
            <?php foreach ($erreurs as $erreur): ?>
                <li><?= h($erreur) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="index.php">
        <label>
            Login
            <input type="text" name="login" value="<?= h($login) ?>" placeholder="admin" required>
        </label>

        <label>
            Mot de passe
            <input type="password" name="mdp" placeholder="Votre mot de passe" required>
        </label>

        <button type="submit">Se connecter</button>
    </form>
</div>
<?php include __DIR__ . '/footer.php'; ?>
