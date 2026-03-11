<?php
declare(strict_types=1);

require_once __DIR__ . '/app/config/Database.php';
require_once __DIR__ . '/app/api/ActiviteitApi.php';
require_once __DIR__ . '/app/models/ActiviteitModel.php';
require_once __DIR__ . '/app/views/helpers/activiteit_helpers.php';

use App\Config\Database;
use App\Api\ActiviteitApi;
use App\Models\ActiviteitModel;

$id = trim((string)($_GET['id'] ?? ''));

if ($id === '') {
    http_response_code(400);
    exit('Missing activiteit id.');
}

$pdo = Database::createConnection();
$activiteitModel = new ActiviteitModel($pdo);
$activiteitApi = new ActiviteitApi($activiteitModel);

$activiteit = $activiteitApi->getActiviteitDetails($id);

if ($activiteit === null) {
    http_response_code(404);
    exit('Activiteit not found.');
}

$actorRows = $activiteitApi->getActiviteitActorRows($id);
$agendapuntRows = $activiteitApi->getActiviteitAgendapuntRows($id);

/** for soort */
$inhoudRows = array_map(
    static fn(array $row): array => [
        'soort' => (string)($row['soort'] ?? ''),
        'content' => (string)($row['content'] ?? ''),
    ],
    $agendapuntRows
);

$pageTitle = 'Activiteitdetails';



include __DIR__ . '/app/views/layout/header.php';
include __DIR__ . '/app/views/activiteit/detailview.php';
include __DIR__ . '/app/views/layout/footer.php';


