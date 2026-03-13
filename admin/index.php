<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/config/Database.php';

use App\Config\Database;

$conn = Database::createConnection();

function tableExists($conn, string $tableName): bool
{
    $stmt = $conn->prepare('SHOW TABLES LIKE :table_name');
    $stmt->bindValue(':table_name', $tableName);
    $stmt->execute();

    return (bool) $stmt->fetchColumn();
}

$persoonStatsExists = tableExists($conn, 'persoon_stats');
$fractieStatsExists = tableExists($conn, 'fractie_stats');
$besluitStatsExists = tableExists($conn, 'besluit_stats');
$fractieBesluitStatsExists = tableExists($conn, 'fractie_besluit_stats');
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Statistieken</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        .transcript-frame {
            width: 100%;
            height: 650px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            background: #fff;
        }

        .button-group {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
    </style>

    <script>
        function renderTranscriptSelection(title, action) {
            var frame = document.querySelector('iframe[name="transcriptFrame"]');

            if (!frame) {
                return true;
            }

            frame.srcdoc =
                "<html><body style='font-family: Arial, sans-serif; padding: 15px;'>" +
                "<h3>Transcript</h3>" +
                "<p><strong>Geselecteerd:</strong> " + title + "</p>" +
                "<p><strong>Actie:</strong> " + action + "</p>" +
                "<p>Bezig met laden...</p>" +
                "</body></html>";

            return true;
        }

        function confirmAndRenderTranscript(title, action) {
            if (!confirm('Weet u het zeker?')) {
                return false;
            }

            return renderTranscriptSelection(title, action);
        }
    </script>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Admin - Statistieken</h1>
            <a class="btn btn-light border" href="../index.php">Terug</a>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Persoon stats</h5>

                        <div class="button-group">
                            <?php if (!$persoonStatsExists): ?>
                                <a
                                    class="btn btn-primary"
                                    href="build_stats.php?type=persoon&action=create"
                                    target="transcriptFrame"
                                    onclick="return renderTranscriptSelection('Persoon stats', 'Create table');"
                                >
                                    Create table
                                </a>
                            <?php endif; ?>

                            <?php if ($persoonStatsExists): ?>
                                <a
                                    class="btn btn-light border"
                                    href="build_stats.php?type=persoon&action=drop"
                                    target="transcriptFrame"
                                    onclick="return confirmAndRenderTranscript('Persoon stats', 'Drop table');"
                                >
                                    Drop table
                                </a>

                                <a
                                    class="btn btn-light border"
                                    href="build_stats.php?type=persoon&action=clear"
                                    target="transcriptFrame"
                                    onclick="return confirmAndRenderTranscript('Persoon stats', 'Clear table');"
                                >
                                    Clear table
                                </a>

                                <a
                                    class="btn btn-primary"
                                    href="build_stats.php?type=persoon&action=build_sums"
                                    target="transcriptFrame"
                                    onclick="return renderTranscriptSelection('Persoon stats', 'Build sums');"
                                >
                                    Build sums
                                </a>

                                <a
                                    class="btn btn-primary"
                                    href="build_stats.php?type=persoon&action=build_percentages"
                                    target="transcriptFrame"
                                    onclick="return renderTranscriptSelection('Persoon stats', 'Build percentages');"
                                >
                                    Build percentages
                                </a>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Fractie stats</h5>

                        <div class="button-group">
                            <?php if (!$fractieStatsExists): ?>
                                <a
                                    class="btn btn-primary"
                                    href="build_stats.php?type=fractie&action=create"
                                    target="transcriptFrame"
                                    onclick="return renderTranscriptSelection('Fractie stats', 'Create table');"
                                >
                                    Create table
                                </a>
                            <?php endif; ?>

                            <?php if ($fractieStatsExists): ?>
                                <a
                                    class="btn btn-light border"
                                    href="build_stats.php?type=fractie&action=drop"
                                    target="transcriptFrame"
                                    onclick="return confirmAndRenderTranscript('Fractie stats', 'Drop table');"
                                >
                                    Drop table
                                </a>

                                <a
                                    class="btn btn-light border"
                                    href="build_stats.php?type=fractie&action=clear"
                                    target="transcriptFrame"
                                    onclick="return confirmAndRenderTranscript('Fractie stats', 'Clear table');"
                                >
                                    Clear table
                                </a>

                                <a
                                    class="btn btn-primary"
                                    href="build_stats.php?type=fractie&action=build_sums"
                                    target="transcriptFrame"
                                    onclick="return renderTranscriptSelection('Fractie stats', 'Build sums');"
                                >
                                    Build sums
                                </a>

                                <a
                                    class="btn btn-primary"
                                    href="build_stats.php?type=fractie&action=build_percentages"
                                    target="transcriptFrame"
                                    onclick="return renderTranscriptSelection('Fractie stats', 'Build percentages');"
                                >
                                    Build percentages
                                </a>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Besluit stats</h5>

                        <div class="button-group">
                            <?php if (!$besluitStatsExists): ?>
                                <a
                                    class="btn btn-primary"
                                    href="build_stats.php?type=besluit&action=create"
                                    target="transcriptFrame"
                                    onclick="return renderTranscriptSelection('Besluit stats', 'Create table');"
                                >
                                    Create table
                                </a>
                            <?php endif; ?>

                            <?php if ($besluitStatsExists): ?>
                                <a
                                    class="btn btn-light border"
                                    href="build_stats.php?type=besluit&action=drop"
                                    target="transcriptFrame"
                                    onclick="return confirmAndRenderTranscript('Besluit stats', 'Drop table');"
                                >
                                    Drop table
                                </a>

                                <a
                                    class="btn btn-light border"
                                    href="build_stats.php?type=besluit&action=clear"
                                    target="transcriptFrame"
                                    onclick="return confirmAndRenderTranscript('Besluit stats', 'Clear table');"
                                >
                                    Clear table
                                </a>

                                <a
                                    class="btn btn-primary"
                                    href="build_stats.php?type=besluit&action=build_sums"
                                    target="transcriptFrame"
                                    onclick="return renderTranscriptSelection('Besluit stats', 'Build sums');"
                                >
                                    Build sums
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Fractie besluit stats</h5>

                        <div class="button-group">
                            <?php if (!$fractieBesluitStatsExists): ?>
                                <a
                                    class="btn btn-primary"
                                    href="build_stats.php?type=fractie_besluit&action=create"
                                    target="transcriptFrame"
                                    onclick="return renderTranscriptSelection('Fractie besluit stats', 'Create table');"
                                >
                                    Create table
                                </a>
                            <?php endif; ?>

                            <?php if ($fractieBesluitStatsExists): ?>
                                <a
                                    class="btn btn-light border"
                                    href="build_stats.php?type=fractie_besluit&action=drop"
                                    target="transcriptFrame"
                                    onclick="return confirmAndRenderTranscript('Fractie besluit stats', 'Drop table');"
                                >
                                    Drop table
                                </a>

                                <a
                                    class="btn btn-light border"
                                    href="build_stats.php?type=fractie_besluit&action=clear"
                                    target="transcriptFrame"
                                    onclick="return confirmAndRenderTranscript('Fractie besluit stats', 'Clear table');"
                                >
                                    Clear table
                                </a>

                                <a
                                    class="btn btn-primary"
                                    href="build_stats.php?type=fractie_besluit&action=build_sums"
                                    target="transcriptFrame"
                                    onclick="return renderTranscriptSelection('Fractie besluit stats', 'Build sums');"
                                >
                                    Build sums
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <strong>Transcript / voortgang</strong>
                    </div>

                    <div class="card-body p-2">
                        <iframe
                            name="transcriptFrame"
                            class="transcript-frame"
                            srcdoc="
                                <html>
                                <body style='font-family: Arial, sans-serif; padding: 15px;'>
                                    <h3>Transcript</h3>
                                    <p>Klik op een knop links om de voortgang te starten.</p>
                                </body>
                                </html>
                            "
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
