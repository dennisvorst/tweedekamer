<?php
declare(strict_types=1);

set_time_limit(300);

require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/models/StatsAdminModel.php';

use App\Config\Database;
use App\Models\StatsAdminModel;

$pdo = Database::createConnection();
$model = new StatsAdminModel($pdo);

$action = $_GET['action'] ?? '';
$offset = max(0, (int)($_GET['offset'] ?? 0));
$chunkSize = 100;

if ($action === 'create') {
    $model->createFractieStatsTable();
    echo 'Fractie stats table created.';
    exit;
}

if ($action !== 'rebuild') {
    exit('Invalid action.');
}

$ids = $model->getAllFractieIds();
$total = count($ids);
$batch = array_slice($ids, $offset, $chunkSize);

foreach ($batch as $fractieId) {
    $model->rebuildFractieStat((string)$fractieId);
}

$nextOffset = $offset + $chunkSize;

echo "<p>Processed " . min($nextOffset, $total) . " of {$total} fractie records.</p>";

if ($nextOffset < $total) {
    $nextUrl = "rebuild_fractie_stats.php?action=rebuild&offset={$nextOffset}";
    echo "<p><a href=\"{$nextUrl}\">Continue</a></p>";
    echo "<script>setTimeout(function(){ window.location.href = '{$nextUrl}'; }, 500);</script>";
} else {
    echo "<p>Fractie stats rebuild complete.</p>";
    echo '<p><a href="stats.php">Back to admin</a></p>';
}