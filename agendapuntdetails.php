<?php
declare(strict_types=1);

require_once __DIR__ . '/app/config/Database.php';
require_once __DIR__ . '/app/api/ActiviteitApi.php';
require_once __DIR__ . '/app/models/ActiviteitModel.php';
require_once __DIR__ . '/app/views/helpers/agendapunt_helpers.php';

use App\Api\ActiviteitApi;
use App\Config\Database;
use App\Models\ActiviteitModel;

$id = trim((string)($_GET['id'] ?? ''));

if ($id === '') {
    http_response_code(400);
    exit('Missing agendapunt id.');
}

$pdo = Database::createConnection();
$activiteitModel = new ActiviteitModel($pdo);
$activiteitApi = new ActiviteitApi($activiteitModel);

$agendapunt = $activiteitApi->getAgendapuntDetails($id);
$besluitRows = $activiteitApi->getAgendapuntBesluitRows($id);

if ($agendapunt === null) {
    http_response_code(404);
    exit('Agendapunt not found.');
}

$pageTitle = 'Agendapuntdetails';

include __DIR__ . '/app/views/layout/header.php';
include __DIR__ . '/app/views/Agendapunt/detailview.php';
include __DIR__ . '/app/views/layout/footer.php';
