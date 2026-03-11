<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/persoon_helpers.php';

/** @var array $person */
/** @var array $contactRows */
/** @var array $onderwijsRows */
/** @var array $loopbaanRows */
/** @var array $nevenfunctieRows */
/** @var array $nevenfunctieInkomstenRows */
/** @var array $besluitStemRows */
/** @var array $fractieRows */

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