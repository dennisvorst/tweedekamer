<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Stemstatistieken</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4">Admin - Stemstatistieken</h1>

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">Persoon stats</h5>
        </div>
        <div class="card-body d-flex gap-2">
            <a href="rebuild_person_stats.php?action=create" class="btn btn-outline-primary">
                Create table
            </a>
            <a href="rebuild_person_stats.php?action=rebuild" class="btn btn-primary">
                Insert / update values
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Fractie stats</h5>
        </div>
        <div class="card-body d-flex gap-2">
            <a href="rebuild_fractie_stats.php?action=create" class="btn btn-outline-primary">
                Create table
            </a>
            <a href="rebuild_fractie_stats.php?action=rebuild" class="btn btn-primary">
                Insert / update values
            </a>
        </div>
    </div>
</div>
</body>
</html>