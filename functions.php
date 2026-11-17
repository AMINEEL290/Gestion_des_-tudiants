<?php
session_start();

const FICHIER = __DIR__ . '/data/etudiants.txt';
const FILIERES = ['GI', 'IDIA', 'GE', 'GC'];
const UTILISATEURS = [
    'admin' => 'admin123',
    'prof' => 'prof123',
];

function h(string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function nettoyer(string $s): string
{
    $s = str_replace(['\r', '\n', ';'], ' ', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
}

function lire_etudiants(): array
{
    if (!file_exists(FICHIER)) {
        return [];
    }

    $lignes = file(FICHIER, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lignes === false) {
        return [];
    }

    $liste = [];
    foreach ($lignes as $ligne) {
        $parts = array_map('trim', explode(';', $ligne));
        if (count($parts) < 6) {
            continue;
        }

        $id = (int) ($parts[0] ?? 0);
        if ($id <= 0) {
            continue;
        }

        $etudiant = [
            'id' => $id,
            'nom' => $parts[1] ?? '',
            'prenom' => $parts[2] ?? '',
            'filiere' => $parts[3] ?? '',
            'note' => (float) ($parts[4] ?? 0),
            'photo' => $parts[5] ?? '',
        ];

        if (isset($parts[6]) && trim($parts[6]) !== '') {
            $etudiant['email'] = $parts[6];
        }

        $liste[$id] = $etudiant;
    }

    ksort($liste);
    return $liste;
}

function sauver_etudiants(array $liste): void
{
    $lignes = [];
    ksort($liste);

    foreach ($liste as $etudiant) {
        $ligne = [
            (string) ($etudiant['id'] ?? 0),
            $etudiant['nom'] ?? '',
            $etudiant['prenom'] ?? '',
            $etudiant['filiere'] ?? '',
            (string) ($etudiant['note'] ?? 0),
            $etudiant['photo'] ?? '',
        ];

        if (isset($etudiant['email'])) {
            $ligne[] = $etudiant['email'];
        }

        $lignes[] = implode(';', $ligne);
    }

    $contenu = implode(PHP_EOL, $lignes);
    file_put_contents(FICHIER, $contenu, LOCK_EX);
}

function connecte(): bool
{
    return isset($_SESSION['user']) && $_SESSION['user'] !== '';
}

function exiger_connexion(): void
{
    if (!connecte()) {
        header('Location: index.php');
        exit;
    }
}

function ajouter_message(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function afficher_message(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }

    $message = $_SESSION['flash'];
    echo '<div class="message ' . h($message['type']) . '">' . h($message['message']) . '</div>';
    unset($_SESSION['flash']);
}
