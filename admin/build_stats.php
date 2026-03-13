<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/models/PersoonStatsModel.php';
require_once __DIR__ . '/../app/models/FractieStatsModel.php';
require_once __DIR__ . '/../app/models/BesluitStatsModel.php';
require_once __DIR__ . '/../app/models/FractieBesluitStatsModel.php';

use App\Config\Database;
use App\Models\BesluitStatsModel;
use App\Models\FractieBesluitStatsModel;
use App\Models\FractieStatsModel;
use App\Models\PersoonStatsModel;

set_time_limit(0);

$conn = Database::createConnection();

$type = $_GET['type'] ?? '';
$action = $_GET['action'] ?? '';
$offset = max(0, (int) ($_GET['offset'] ?? 0));
$limit = max(1, (int) ($_GET['limit'] ?? 100));

function getModelConfig(string $type, PDO $conn): array
{
    return match ($type) {
        'persoon' => [
            'title' => 'Persoon stats',
            'table_name' => 'persoon_stats',
            'model' => new PersoonStatsModel($conn),
            'supports_percentages' => true,
        ],
        'fractie' => [
            'title' => 'Fractie stats',
            'table_name' => 'fractie_stats',
            'model' => new FractieStatsModel($conn),
            'supports_percentages' => true,
        ],
        'besluit' => [
            'title' => 'Besluit stats',
            'table_name' => 'besluit_stats',
            'model' => new BesluitStatsModel($conn),
            'supports_percentages' => false,
        ],
        'fractie_besluit' => [
            'title' => 'Fractie besluit stats',
            'table_name' => 'fractie_besluit_stats',
            'model' => new FractieBesluitStatsModel($conn),
            'supports_percentages' => false,
        ],
        default => throw new RuntimeException('Onbekend type: ' . $type),
    };
}

function renderPageStart(string $title): void
{
    echo '<!DOCTYPE html>';
    echo '<html lang="nl">';
    echo '<head>';
    echo '    <meta charset="UTF-8">';
    echo '    <meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '    <title>' . htmlspecialchars($title) . '</title>';
    echo '    <style>';
    echo '        body { font-family: Arial, sans-serif; padding: 16px; background: #fff; color: #212529; }';
    echo '        h2 { margin-top: 0; font-size: 22px; }';
    echo '        .log { font-family: Consolas, monospace; white-space: pre-wrap; background: #f8f9fa; border: 1px solid #dee2e6; padding: 12px; border-radius: 6px; }';
    echo '        .success { color: #0d6efd; font-weight: bold; }';
    echo '        .muted { color: #6c757d; }';
    echo '        .actions { margin-top: 16px; }';
    echo '        .btn { display: inline-block; padding: 8px 12px; border: 1px solid #0d6efd; border-radius: 6px; text-decoration: none; color: #0d6efd; background: #fff; margin-right: 8px; }';
    echo '        .btn-primary { background: #0d6efd; color: #fff; }';
    echo '    </style>';
    echo '</head>';
    echo '<body>';
    echo '<h2>' . htmlspecialchars($title) . '</h2>';
}

function renderPageEnd(): void
{
    echo '</body>';
    echo '</html>';
}

function renderMessage(string $message): void
{
    echo '<div class="log">' . nl2br(htmlspecialchars($message)) . '</div>';
}

function renderAutoContinue(string $url, int $delayMs = 500): void
{
    echo '<p class="muted">Volgende batch wordt automatisch gestart...</p>';
    echo '<script>';
    echo 'setTimeout(function () { window.location.href = ' . json_encode($url) . '; }, ' . $delayMs . ');';
    echo '</script>';
}

function renderBackLink(): void
{
    echo '<div class="actions">';
    echo '    <a class="btn" href="index.php" target="_top">Terug naar admin</a>';
    echo '</div>';
}

try {
    $config = getModelConfig($type, $conn);
    $titlePrefix = $config['title'];
    $tableName = $config['table_name'];
    $model = $config['model'];
    $supportsPercentages = (bool)$config['supports_percentages'];

    switch ($action) {
        case 'create':
            renderPageStart($titlePrefix . ' - Create table');
            $model->createTable();
            echo '<p class="success">Tabel aangemaakt.</p>';
            renderBackLink();
            renderPageEnd();
            break;

        case 'drop':
            renderPageStart($titlePrefix . ' - Drop table');
            $model->dropTable();
            echo '<p class="success">Tabel verwijderd.</p>';
            renderBackLink();
            renderPageEnd();
            break;

        case 'clear':
            renderPageStart($titlePrefix . ' - Clear table');
            $model->clearTable();
            echo '<p class="success">Tabel geleegd.</p>';
            renderBackLink();
            renderPageEnd();
            break;

        case 'build_sums':
            renderPageStart($titlePrefix . ' - Build sums');
            $total = $model->countForBuild();
            $processed = $model->buildSumsBatch($offset, $limit);
            $nextOffset = $offset + $processed;
            $current = min($nextOffset, $total);

            renderMessage(
                "Actie: build_sums\n" .
                "Type: {$type}\n" .
                "Batch grootte: {$limit}\n" .
                "Offset: {$offset}\n" .
                "Verwerkt in deze batch: {$processed}\n" .
                "Voortgang: {$current} / {$total}"
            );

            if ($current < $total && $processed > 0) {
                $nextUrl = 'build_stats.php?type=' . urlencode($type) . '&action=build_sums&offset=' . $nextOffset . '&limit=' . $limit;
                renderAutoContinue($nextUrl);
            } else {
                echo '<p class="success">Build sums voltooid.</p>';
                if ($supportsPercentages) {
                    echo '<p class="muted">Percentages zijn tijdens of na de build beschikbaar voor dit type.</p>';
                }
                renderBackLink();
            }

            renderPageEnd();
            break;

        case 'build_percentages':
            renderPageStart($titlePrefix . ' - Build percentages');
            if (!$supportsPercentages || !method_exists($model, 'buildPercentagesBatch')) {
                renderMessage("Dit type ondersteunt geen aparte percentages-build.");
                renderBackLink();
                renderPageEnd();
                break;
            }

            $total = $model->countForBuild();
            $processed = $model->buildPercentagesBatch($offset, $limit);
            $nextOffset = $offset + $processed;
            $current = min($nextOffset, $total);

            renderMessage(
                "Actie: build_percentages\n" .
                "Type: {$type}\n" .
                "Batch grootte: {$limit}\n" .
                "Offset: {$offset}\n" .
                "Verwerkt in deze batch: {$processed}\n" .
                "Voortgang: {$current} / {$total}"
            );

            if ($current < $total && $processed > 0) {
                $nextUrl = 'build_stats.php?type=' . urlencode($type) . '&action=build_percentages&offset=' . $nextOffset . '&limit=' . $limit;
                renderAutoContinue($nextUrl);
            } else {
                echo '<p class="success">Build percentages voltooid.</p>';
                renderBackLink();
            }
            renderPageEnd();
            break;

        default:
            renderPageStart('Stats builder');
            renderMessage(
                "Ongeldige of ontbrekende actie.\n\n" .
                "Type: {$type}\n" .
                "Gebruik een van de volgende acties:\n" .
                "- create\n" .
                "- drop\n" .
                "- clear\n" .
                "- build_sums\n" .
                "- build_percentages (alleen voor persoon/fractie)"
            );
            renderBackLink();
            renderPageEnd();
            break;
    }
} catch (Throwable $e) {
    renderPageStart('Stats builder - Fout');
    renderMessage("Er is een fout opgetreden:\n" . $e->getMessage());
    renderBackLink();
    renderPageEnd();
}
