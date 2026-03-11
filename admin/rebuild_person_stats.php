<?php
declare(strict_types=1);

set_time_limit(300);

require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/models/StatsAdminModel.php';

use App\Config\Database;
use App\Models\StatsAdminModel;

$pdo = Database::createConnection();
$model = new StatsAdminModel($pdo);

$persons = $model->getAllPersoonIds();
$limit = 100;

$batches = (int)(count($persons) / $limit) + 1; 

for ($i = 0; $i <= $batches; $i++) 
{
    echo "Processing batch {$i}<br>\n";
    $model->rebuildPersoonStatsBatch($i, $limit);
}
echo "Done<br>\n";
