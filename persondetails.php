<?php
declare(strict_types=1);

require_once __DIR__ . '/app/config/Database.php';
require_once __DIR__ . '/app/api/PersonApi.php';
require_once __DIR__ . '/app/models/PersonModel.php';
require_once __DIR__ . '/app/views/helpers/person_helpers.php';

use App\Config\Database;
use App\Api\PersonApi;
use App\Models\PersonModel;

$id = trim((string)($_GET['id'] ?? ''));

if ($id === '') {
    http_response_code(400);
    exit('Missing person id.');
}

$pdo = Database::createConnection();
$personModel = new PersonModel($pdo);
$personApi = new PersonApi($personModel);

$person = $personApi->getPersonDetails($id);

if ($person === null) {
    http_response_code(404);
    exit('Person not found.');
}

/*
 * Replace this with your real API/model call when available.
 * The renderer already supports a list of onderwijs rows.
 */
$contactRows = $personApi->getPersonContactInformation($id);
$onderwijsRows = $personApi->getPersonOnderwijs($id);
$loopbaanRows = $personApi->getPersonLoopbaan($id);
$nevenfunctieRows = $personApi->getPersonNevenfuncties($id);
$nevenfunctieInkomstenRows = $personApi->getPersonNevenfunctieInkomsten($id);
$besluitStemRows = $personApi->getPersonBesluitStemRows($id);
$fractieRows = $personApi->getPersonFractieRows($id);

$pageTitle = 'Persoondetails';
include __DIR__ . '/app/views/layout/header.php';

renderPersonDetails(
    $person,
    $contactRows ?? [],
    $onderwijsRows ?? [],
    $loopbaanRows ?? [],
    $nevenfunctieRows ?? [],
    $nevenfunctieInkomstenRows ?? [],
    $besluitStemRows ?? [],
    $fractieRows ?? []
);

include __DIR__ . '/app/views/layout/footer.php';
