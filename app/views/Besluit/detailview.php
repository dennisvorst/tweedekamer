<?php
declare(strict_types=1);

/** @var array $besluit */
/** @var array $stemmingRows */
/** @var array $fractieStemSamenvattingRows */

renderBesluitDetails(
    $besluit,
    $stemmingRows ?? [],
    $fractieStemSamenvattingRows ?? []
);