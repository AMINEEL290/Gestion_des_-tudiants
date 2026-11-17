<?php
require_once __DIR__ . '/functions.php';

exiger_connexion();

$titre = 'Ajouter un étudiant';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$liste = lire_etudiants();
$etudiantActuel = $id > 0 && isset($liste[$id]) ? $liste[$id] : null;

if ($etudiantActuel) {
    $titre = 'Modifier un étudiant';
}

$form = [
    'nom' => $etudiantActuel['nom'] ?? '',
    'prenom' => $etudiantActuel['prenom'] ?? '',
    'filiere' => $etudiantActuel['filiere'] ?? 'GI',
    'note' => $etudiantActuel['note'] ?? '',
    'email' => $etudiantActuel['email'] ?? '',
    'photo' => $etudiantActuel['photo'] ?? '',
];

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['nom'] = nettoyer($_POST['nom'] ?? '');
    $form['prenom'] = nettoyer($_POST['prenom'] ?? '');
    $form['filiere'] = $_POST['filiere'] ?? 'GI';
    $form['note'] = $_POST['note'] ?? '';
    $form['email'] = nettoyer($_POST['email'] ?? '');

    if ($form['nom'] === '') {
        $erreurs[] = 'Le nom est obligatoire.';
    }

    if ($form['prenom'] === '') {
        $erreurs[] = 'Le prénom est obligatoire.';
    }

    if (!in_array($form['filiere'], FILIERES, true)) {
        $erreurs[] = 'La filière sélectionnée est invalide.';
    }

    if (!is_numeric($form['note']) || (float) $form['note'] < 0 || (float) $form['note'] > 20) {
        $erreurs[] = 'La note doit être un nombre entre 0 et 20.';
    }

    if ($form['email'] !== '' && !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'L’adresse email est invalide.';
    }

    $upload = $_FILES['photo'] ?? ['error' => UPLOAD_ERR_NO_FILE];
    $nomPhoto = $form['photo'];

    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
        $extensionsAutorisees = ['jpg', 'jpeg', 'png'];

        if (!in_array($extension, $extensionsAutorisees, true)) {
            $erreurs[] = 'L’extension de la photo n’est pas autorisée (jpg, jpeg, png).';
        } elseif ($upload['size'] > 1048576) {
            $erreurs[] = 'La photo ne doit pas dépasser 1 Mo.';
        } else {
            $nomPhoto = uniqid('etu_', true) . '.' . $extension;
            $destination = __DIR__ . '/uploads/' . $nomPhoto;
            if (!move_uploaded_file($upload['tmp_name'], $destination)) {
                $erreurs[] = 'Le téléchargement de la photo a échoué.';
            }
        }
    } elseif (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $erreurs[] = 'Erreur lors du téléchargement de la photo.';
    }

    if (empty($erreurs)) {
        $liste = lire_etudiants();

        if ($id > 0 && isset($liste[$id])) {
            $ancienPhoto = $liste[$id]['photo'] ?? '';
            $liste[$id]['nom'] = $form['nom'];
            $liste[$id]['prenom'] = $form['prenom'];
            $liste[$id]['filiere'] = $form['filiere'];
            $liste[$id]['note'] = (float) $form['note'];
            $liste[$id]['photo'] = $nomPhoto;

            if ($form['email'] !== '') {
                $liste[$id]['email'] = $form['email'];
            } elseif (isset($liste[$id]['email'])) {
                unset($liste[$id]['email']);
            }

            if ($nomPhoto !== $ancienPhoto && $ancienPhoto !== '' && file_exists(__DIR__ . '/uploads/' . $ancienPhoto)) {
                unlink(__DIR__ . '/uploads/' . $ancienPhoto);
            }
        } else {
            $nouvelId = 1;
            if (!empty($liste)) {
                $nouvelId = max(array_keys($liste)) + 1;
            }

            $liste[$nouvelId] = [
                'id' => $nouvelId,
                'nom' => $form['nom'],
                'prenom' => $form['prenom'],
                'filiere' => $form['filiere'],
                'note' => (float) $form['note'],
                'photo' => $nomPhoto,
            ];

            if ($form['email'] !== '') {
                $liste[$nouvelId]['email'] = $form['email'];
            }
        }

        sauver_etudiants($liste);
        ajouter_message('success', $id > 0 ? 'Étudiant modifié avec succès.' : 'Étudiant ajouté avec succès.');
        header('Location: liste.php');
        exit;
    }
}

include __DIR__ . '/header.php';
?>
<div class="card form-box">
    <?php if (!empty($erreurs)): ?>
        <ul class="error-list">
            <?php foreach ($erreurs as $erreur): ?>
                <li><?= h($erreur) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="ajouter.php<?= $id > 0 ? '?id=' . $id : '' ?>" enctype="multipart/form-data">
        <label>
            Nom
            <input type="text" name="nom" value="<?= h($form['nom']) ?>" required>
        </label>

        <label>
            Prénom
            <input type="text" name="prenom" value="<?= h($form['prenom']) ?>" required>
        </label>

        <label>
            Filière
            <select name="filiere">
                <?php foreach (FILIERES as $filiere): ?>
                    <option value="<?= h($filiere) ?>" <?= $form['filiere'] === $filiere ? 'selected' : '' ?>><?= h($filiere) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Note
            <input type="number" step="0.1" min="0" max="20" name="note" value="<?= h((string) $form['note']) ?>" required>
        </label>

        <label>
            Email (optionnel)
            <input type="email" name="email" value="<?= h($form['email']) ?>">
        </label>

        <label>
            Photo (optionnelle)
            <input type="file" name="photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
        </label>

        <button type="submit"><?= $id > 0 ? 'Modifier' : 'Ajouter' ?></button>
    </form>
</div>
<?php include __DIR__ . '/footer.php'; ?>
