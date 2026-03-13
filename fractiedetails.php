<?php
declare(strict_types=1);

require_once __DIR__ . '/app/config/Database.php';
require_once __DIR__ . '/app/api/FractieApi.php';
require_once __DIR__ . '/app/models/FractieModel.php';
require_once __DIR__ . '/app/views/helpers/fractie_helpers.php';

use App\Config\Database;
use App\Api\FractieApi;
use App\Models\FractieModel;

$id = trim((string)($_GET['id'] ?? ''));

if ($id === '') {
    http_response_code(400);
    exit('Missing fractie id.');
}

$pdo = Database::createConnection();
$fractieModel = new FractieModel($pdo);
$fractieApi = new FractieApi($fractieModel);

$fractie = $fractieApi->getFractieDetails($id);

if ($fractie === null) {
    http_response_code(404);
    exit('Fractie not found.');
}

$zetelPersoonRows = $fractieApi->getFractieZetelPersonen($id);

$pageTitle = 'Fractiedetails';

include __DIR__ . '/app/views/layout/header.php';
include __DIR__ . '/app/views/fractie/detailview.php';
include __DIR__ . '/app/views/layout/footer.php';
