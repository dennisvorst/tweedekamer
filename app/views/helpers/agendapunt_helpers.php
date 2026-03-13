<?php
declare(strict_types=1);

if (!function_exists('agendapuntHasValue')) {
    function agendapuntHasValue(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }
}

if (!function_exists('agendapuntFormatTime')) {
    function agendapuntFormatTime(?string $time): string
    {
        if (!$time) {
            return '';
        }

        try {
            $dt = new DateTime($time);
            return $dt->format('H:i');
        } catch (Exception) {
            return $time;
        }
    }
}

if (!function_exists('agendapuntFormatDate')) {
    function agendapuntFormatDate(?string $date): string
    {
        if (!$date) {
            return '';
        }

        try {
            $dt = new DateTime($date);
            return $dt->format('d-m-Y');
        } catch (Exception) {
            return $date;
        }
    }
}

if (!function_exists('renderAgendapuntValueRow')) {
    function renderAgendapuntValueRow(string $label, mixed $value, bool $showWhenEmpty = false): void
    {
        if (!agendapuntHasValue($value) && !$showWhenEmpty) {
            return;
        }

        $displayValue = agendapuntHasValue($value) ? (string)$value : '-';
        ?>
        <div class="row py-2 border-bottom">
            <div class="col-sm-4 fw-semibold"><?= htmlspecialchars($label) ?></div>
            <div class="col-sm-8"><?= nl2br(htmlspecialchars($displayValue)) ?></div>
        </div>
        <?php
    }
}

if (!function_exists('agendapuntLooksLikeHtmlFragment')) {
    function agendapuntLooksLikeHtmlFragment(string $value): bool
    {
        return preg_match('/<(ul|ol|li|div|p|strong|em|a|table|tr|td|br)\b/i', $value) === 1;
    }
}

if (!function_exists('agendapuntBuildIframeDocument')) {
    function agendapuntBuildIframeDocument(string $html): string
    {
        $safeHtml = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $safeHtml = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $safeHtml) ?? $safeHtml;
        $safeHtml = preg_replace('/\son\w+="[^"]*"/i', '', $safeHtml) ?? $safeHtml;
        $safeHtml = preg_replace("/\son\w+='[^']*'/i", '', $safeHtml) ?? $safeHtml;
        $safeHtml = preg_replace('/\sstyle="[^"]*"/i', '', $safeHtml) ?? $safeHtml;
        $safeHtml = preg_replace("/\sstyle='[^']*'/i", '', $safeHtml) ?? $safeHtml;
        $safeHtml = preg_replace('/<a\b(?![^>]*\btarget=)/i', '<a target="_blank" rel="noopener noreferrer"', $safeHtml) ?? $safeHtml;

        return '<!doctype html><html lang="nl"><head><meta charset="utf-8"><base target="_blank"><style>'
            . 'body{font-family:Segoe UI,Tahoma,Arial,sans-serif;font-size:14px;line-height:1.5;color:#212529;margin:0;padding:12px;background:#fff;}'
            . 'ul,ol{padding-left:1.25rem;margin:0 0 1rem;}'
            . 'li{margin-bottom:.35rem;}'
            . 'p,div{margin:0 0 .75rem;}'
            . 'strong{font-weight:600;}'
            . 'a{color:#0d6efd;text-decoration:none;}'
            . 'a:hover{text-decoration:underline;}'
            . '</style></head><body>' . $safeHtml . '</body></html>';
    }
}

if (!function_exists('renderAgendapuntRichTextValue')) {
    function renderAgendapuntRichTextValue(?string $value): string
    {
        if (!agendapuntHasValue($value)) {
            return '-';
        }

        $rawValue = (string)$value;

        if (agendapuntLooksLikeHtmlFragment($rawValue)) {
            $srcdoc = htmlspecialchars(agendapuntBuildIframeDocument($rawValue), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            return '<iframe '
                . 'class="w-100 border rounded bg-white" '
                . 'style="min-height: 24rem;" '
                . 'sandbox="allow-popups allow-popups-to-escape-sandbox" '
                . 'srcdoc="' . $srcdoc . '"'
                . '></iframe>';
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", $rawValue);
        $normalized = preg_replace('/<br\s*\/?>/i', "\n", $normalized) ?? $normalized;
        $normalized = html_entity_decode($normalized, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $lines = array_values(array_filter(array_map(
            static fn(string $line): string => trim($line),
            explode("\n", $normalized)
        ), static fn(string $line): bool => $line !== ''));

        $bulletLines = [];
        $plainLines = [];

        foreach ($lines as $line) {
            if (preg_match('/^[\x{2022}\-\*]\s*(.+)$/u', $line, $matches) === 1) {
                $bulletLines[] = $matches[1];
                continue;
            }

            $plainLines[] = $line;
        }

        if ($bulletLines !== [] && $plainLines === []) {
            $items = array_map(
                static fn(string $line): string => '<li>' . htmlspecialchars($line) . '</li>',
                $bulletLines
            );

            return '<ul class="mb-0 ps-3">' . implode('', $items) . '</ul>';
        }

        return nl2br(htmlspecialchars(implode("\n", $lines)));
    }
}

if (!function_exists('renderAgendapuntBesluitenSection')) {
    function renderAgendapuntBesluitenSection(array $besluitRows = []): void
    {
        if (empty($besluitRows)) {
            return;
        }
        ?>
        <div class="card border-0 bg-light mt-4">
            <div class="card-header bg-transparent">
                <h5 class="mb-0">Besluiten</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Besluitsoort</th>
                                <th>Stemmingssoort</th>
                                <th>Besluittekst</th>
                                <th>Status</th>
                                <th class="text-end">Volgorde</th>
                                <th class="text-center">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($besluitRows as $besluit): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string)($besluit['besluit_soort'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($besluit['stemmingssoort'] ?? '')) ?></td>
                                    <td>
                                        <?= htmlspecialchars((string)($besluit['besluittekst'] ?? '')) ?>
                                        <?php if (agendapuntHasValue($besluit['opmerking'] ?? null)): ?>
                                            <div class="small text-muted mt-1">
                                                <?= htmlspecialchars((string)$besluit['opmerking']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars((string)($besluit['status'] ?? '')) ?></td>
                                    <td class="text-end"><?= htmlspecialchars((string)($besluit['agendapunt_zaak_besluitvolgorde'] ?? '')) ?></td>
                                    <td class="text-center">
                                        <a
                                            href="besluitdetails.php?id=<?= urlencode((string)($besluit['id'] ?? '')) ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Bekijk besluit"
                                        >
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('renderAgendapuntDetails')) {
    function renderAgendapuntDetails(array $agendapunt, array $besluitRows = []): void
    {
        $title = (string)($agendapunt['onderwerp'] ?? $agendapunt['nummer'] ?? 'Agendapunt');
        $volledigeTekst = (string)($agendapunt['content'] ?? '');
        ?>
        <div class="card shadow-sm">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h2 class="mb-1"><?= htmlspecialchars($title) ?></h2>
                        <?php if (agendapuntHasValue($agendapunt['nummer'] ?? null)): ?>
                            <div class="text-muted">Agendapunt <?= htmlspecialchars((string)$agendapunt['nummer']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if (!empty($agendapunt['activiteit_id'])): ?>
                            <a href="activiteitdetails.php?id=<?= urlencode((string)$agendapunt['activiteit_id']) ?>" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-arrow-left"></i> Terug naar activiteit
                            </a>
                        <?php endif; ?>
                        <a href="index.php?tab=activiteit" class="btn btn-outline-primary">
                            <i class="fa-solid fa-list"></i> Activiteiten
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="card border-0 bg-light">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">Agendapuntgegevens</h5>
                    </div>
                    <div class="card-body">
                        <?php renderAgendapuntValueRow('Nummer', $agendapunt['nummer'] ?? null); ?>
                        <?php renderAgendapuntValueRow('Onderwerp', $agendapunt['onderwerp'] ?? null); ?>
                        <?php renderAgendapuntValueRow('Aanvangstijd', agendapuntFormatTime($agendapunt['aanvangstijd'] ?? null), true); ?>
                        <?php renderAgendapuntValueRow('Eindtijd', agendapuntFormatTime($agendapunt['eindtijd'] ?? null), true); ?>
                        <?php renderAgendapuntValueRow('Volgorde', $agendapunt['volgorde'] ?? null); ?>
                        <?php renderAgendapuntValueRow('Rubriek', $agendapunt['rubriek'] ?? null, true); ?>
                        <div class="row py-2 border-bottom">
                            <div class="col-sm-4 fw-semibold">Noot</div>
                            <div class="col-sm-8"><?= renderAgendapuntRichTextValue($agendapunt['noot'] ?? null) ?></div>
                        </div>
                        <?php renderAgendapuntValueRow('Status', $agendapunt['status'] ?? null); ?>
                        <?php renderAgendapuntValueRow('Activiteit', $agendapunt['activiteit_onderwerp'] ?? null); ?>
                        <?php renderAgendapuntValueRow('Activiteit datum', agendapuntFormatDate($agendapunt['activiteit_datum'] ?? null)); ?>
                    </div>
                </div>

                <?php if (agendapuntHasValue($volledigeTekst)): ?>
                    <div class="card border-0 bg-light mt-4">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0">Volledige teksten</h5>
                        </div>
                        <div class="card-body">
                            <div class="border rounded bg-white p-3 activiteit-content-document">
                                <?= $volledigeTekst !== strip_tags($volledigeTekst)
                                    ? $volledigeTekst
                                    : nl2br(htmlspecialchars(trim($volledigeTekst))) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php renderAgendapuntBesluitenSection($besluitRows); ?>
            </div>
        </div>
        <?php
    }
}
