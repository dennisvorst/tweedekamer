<?php
declare(strict_types=1);

/** @var string $pageTitle */
$pageTitle = $pageTitle ?? 'Derde Kamer';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet"
    >

    <style>
    .activiteit-content-document {
        line-height: 1.6;
        overflow-x: auto;
    }

    .activiteit-content-document p:last-child {
        margin-bottom: 0;
    }

    .activiteit-content-document img {
        max-width: 100%;
        height: auto;
    }

    .activiteit-content-document table {
        width: 100%;
    }

    .activiteit-content-document iframe {
        max-width: 100%;
    }
    </style>    
</head>
<body class="bg-light">
<div class="container py-4">