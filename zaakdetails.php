<?php
declare(strict_types=1);

require_once __DIR__ . '/app/config/Database.php';
require_once __DIR__ . '/app/api/ZaakApi.php';
require_once __DIR__ . '/app/models/ZaakModel.php';
require_once __DIR__ . '/app/views/helpers/zaak_helpers.php';

use App\Config\Database;
use App\Api\ZaakApi;
use App\Models\ZaakModel;

$id = trim((string)($_GET['id'] ?? ''));

if ($id === '') {
    http_response_code(400);
    exit('Missing zaak id.');
}

$pdo = Database::createConnection();
$zaakModel = new ZaakModel($pdo);
$zaakApi = new ZaakApi($zaakModel);

$zaak = $zaakApi->getZaakDetails($id);

if ($zaak === null) {
    http_response_code(404);
    exit('Zaak not found.');
}

$actorRows = $zaakApi->getZaakActorRows($id);

$pageTitle = 'Zaakdetails';

include __DIR__ . '/app/views/layout/header.php';
include __DIR__ . '/app/views/zaak/detailview.php';
include __DIR__ . '/app/views/layout/footer.php';