<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titre) ? h($titre) : 'Gestion des étudiants' ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?= (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'theme-dark' : 'theme-light' ?>">
    <header class="topbar">
        <div class="container header-inner">
            <h1>Gestion des étudiants</h1>
            <?php if (connecte()): ?>
                <nav class="menu">
                    <a href="liste.php">Liste</a>
                    <a href="ajouter.php">Ajouter</a>
                    <a href="logout.php">Déconnexion</a>
                </nav>
            <?php endif; ?>
        </div>
    </header>
    <main class="container">
