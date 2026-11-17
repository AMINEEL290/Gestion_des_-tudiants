<?php
require_once __DIR__ . '/functions.php';

exiger_connexion();

$id = (int) ($_GET['id'] ?? 0);
$liste = lire_etudiants();

if ($id > 0 && isset($liste[$id])) {
    $photo = $liste[$id]['photo'] ?? '';
    if ($photo !== '' && file_exists(__DIR__ . '/uploads/' . $photo)) {
        unlink(__DIR__ . '/uploads/' . $photo);
    }

    unset($liste[$id]);
    sauver_etudiants($liste);
    ajouter_message('success', 'Étudiant supprimé avec succès.');
}

header('Location: liste.php');
exit;
