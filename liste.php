<?php
require_once __DIR__ . '/functions.php';

exiger_connexion();

$titre = 'Liste des étudiants';
$liste = lire_etudiants();
$q = trim($_GET['q'] ?? '');
$tri = $_GET['tri'] ?? 'ajout';

if ($q !== '') {
    $liste = array_filter($liste, function ($etudiant) use ($q) {
        return stripos($etudiant['nom'], $q) !== false || stripos($etudiant['prenom'], $q) !== false;
    });
}

if ($tri === 'nom') {
    uasort($liste, function ($a, $b) {
        return strcasecmp($a['nom'], $b['nom']) ?: strcasecmp($a['prenom'], $b['prenom']);
    });
} elseif ($tri === 'filiere') {
    uasort($liste, function ($a, $b) {
        return strcasecmp($a['filiere'], $b['filiere']) ?: strcasecmp($a['nom'], $b['nom']);
    });
} elseif ($tri === 'note') {
    uasort($liste, function ($a, $b) {
        return (float) $b['note'] <=> (float) $a['note'];
    });
}

$notes = array_map(function ($etudiant) {
    return (float) $etudiant['note'];
}, $liste);

$nbEtudiants = count($liste);
$moyenne = $nbEtudiants > 0 ? array_sum($notes) / $nbEtudiants : 0;
$meilleureNote = $nbEtudiants > 0 ? max($notes) : 0;

include __DIR__ . '/header.php';
?>
<div class="search-bar">
    <form method="get" action="liste.php" class="search-bar">
        <input id="search-input" type="text" name="q" value="<?= h($q) ?>" placeholder="Rechercher un nom ou prénom">
        <select name="tri">
            <option value="ajout" <?= $tri === 'ajout' ? 'selected' : '' ?>>Ordre d'ajout</option>
            <option value="nom" <?= $tri === 'nom' ? 'selected' : '' ?>>Nom</option>
            <option value="filiere" <?= $tri === 'filiere' ? 'selected' : '' ?>>Filière</option>
            <option value="note" <?= $tri === 'note' ? 'selected' : '' ?>>Note</option>
        </select>
        <button type="submit">Valider</button>
    </form>
</div>

<div class="card">
    <?php afficher_message(); ?>

    <p><strong><?= $nbEtudiants ?></strong> étudiant(s) affiché(s) | Moyenne : <strong><?= number_format($moyenne, 2, ',', ' ') ?></strong> | Meilleure note : <strong><?= number_format($meilleureNote, 2, ',', ' ') ?></strong></p>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Filière</th>
                    <th>Note</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($nbEtudiants === 0): ?>
                    <tr>
                        <td colspan="7" class="empty">Aucun étudiant</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($liste as $etudiant): ?>
                        <tr class="student-row" data-search="<?= h(strtolower(($etudiant['nom'] ?? '') . ' ' . ($etudiant['prenom'] ?? ''))) ?>">
                            <td>
                                <?php if (!empty($etudiant['photo']) && file_exists(__DIR__ . '/uploads/' . $etudiant['photo'])): ?>
                                    <img class="photo" src="uploads/<?= h($etudiant['photo']) ?>" alt="Photo étudiant">
                                <?php else: ?>
                                    <span>—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= h(strtoupper($etudiant['nom'])) ?></td>
                            <td><?= h(ucfirst(strtolower($etudiant['prenom']))) ?></td>
                            <td><?= h($etudiant['filiere']) ?></td>
                            <td class="note <?= (float) $etudiant['note'] >= 10 ? 'good' : 'bad' ?>"><?= number_format((float) $etudiant['note'], 1, ',', ' ') ?></td>
                            <td><?= h($etudiant['email'] ?? '—') ?></td>
                            <td>
                                <div class="actions">
                                    <a class="link-btn edit" href="ajouter.php?id=<?= (int) $etudiant['id'] ?>">Modifier</a>
                                    <a class="link-btn delete delete-link" href="supprimer.php?id=<?= (int) $etudiant['id'] ?>">Supprimer</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
