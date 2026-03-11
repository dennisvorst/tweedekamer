<?php
declare(strict_types=1);

require_once __DIR__ . '/app/config/Database.php';
require_once __DIR__ . '/app/api/BesluitApi.php';
require_once __DIR__ . '/app/models/BesluitModel.php';
require_once __DIR__ . '/app/views/helpers/besluit_helpers.php';

use App\Config\Database;
use App\Api\BesluitApi;
use App\Models\BesluitModel;

$id = trim((string)($_GET['id'] ?? ''));

if ($id === '') {
    http_response_code(400);
    exit('Missing besluit id.');
}

$pdo = Database::createConnection();
$besluitModel = new BesluitModel($pdo);
$besluitApi = new BesluitApi($besluitModel);

$besluit = $besluitApi->getBesluitDetails($id);

if ($besluit === null) {
    http_response_code(404);
    exit('Besluit not found.');
}

$stemmingRows = $besluitApi->getBesluitStemmingRows($id);
$fractieStemSamenvattingRows = $besluitApi->getBesluitStemmingFractieSamenvatting($id);

$pageTitle = 'Besluitdetails';

include __DIR__ . '/app/views/layout/header.php';
include __DIR__ . '/app/views/besluit/detailview.php';
include __DIR__ . '/app/views/layout/footer.php';