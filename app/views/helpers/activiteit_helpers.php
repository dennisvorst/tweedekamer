<?php
declare(strict_types=1);

if (!function_exists('activiteitHasValue')) {
    function activiteitHasValue(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }
}

if (!function_exists('activiteitFormatDate')) {
    function activiteitFormatDate(?string $date): string
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

if (!function_exists('activiteitFormatTime')) {
    function activiteitFormatTime(?string $time): string
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

if (!function_exists('activiteitFormatNumber')) {
    function activiteitFormatNumber(int|float|string|null $number): string
    {
        if ($number === null || $number === '') {
            return '';
        }

        if (!is_numeric($number)) {
            return (string)$number;
        }

        return number_format((float)$number, 0, ',', '.');
    }
}

if (!function_exists('renderActiviteitValueRow')) {
    function renderActiviteitValueRow(string $label, mixed $value): void
    {
        if (!activiteitHasValue($value)) {
            return;
        }
        ?>
        <div class="row py-2 border-bottom">
            <div class="col-sm-4 fw-semibold"><?= htmlspecialchars($label) ?></div>
            <div class="col-sm-8"><?= nl2br(htmlspecialchars((string)$value)) ?></div>
        </div>
        <?php
    }
}

if (!function_exists('renderActiviteitDetails')) {
    function renderActiviteitDetails(
        array $activiteit,
        array $actorRows = [],
        array $agendapuntRows = [],
        array $inhoudRows = []
    ): void
    {
        $displayTitle = (string)($activiteit['onderwerp'] ?? $activiteit['nummer'] ?? 'Activiteit');
        ?>
        <div class="card shadow-sm">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h2 class="mb-1"><?= htmlspecialchars($displayTitle) ?></h2>
                        <?php if (activiteitHasValue($activiteit['soort'] ?? null)): ?>
                            <div class="text-muted"><?= htmlspecialchars((string)$activiteit['soort']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <a href="index.php?tab=activiteit" class="btn btn-outline-primary">
                            <i class="fa-solid fa-arrow-left"></i> Terug naar overzicht
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="card border-0 bg-light">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">Activiteitgegevens</h5>
                    </div>
                    <div class="card-body">
                        <?php renderActiviteitValueRow('Soort', $activiteit['soort'] ?? null); ?>
                        <?php renderActiviteitValueRow('Nummer', $activiteit['nummer'] ?? null); ?>
                        <?php renderActiviteitValueRow('Onderwerp', $activiteit['onderwerp'] ?? null); ?>
                        <?php renderActiviteitValueRow('Datumsoort', $activiteit['datumsoort'] ?? null); ?>
                        <?php renderActiviteitValueRow('Datum', activiteitFormatDate($activiteit['datum'] ?? null)); ?>
                        <?php renderActiviteitValueRow('Aanvangstijd', activiteitFormatTime($activiteit['aanvangstijd'] ?? null)); ?>
                        <?php renderActiviteitValueRow('Eindtijd', activiteitFormatTime($activiteit['eindtijd'] ?? null)); ?>
                        <?php renderActiviteitValueRow('Locatie', $activiteit['locatie'] ?? null); ?>
                        <?php renderActiviteitValueRow('Besloten', isset($activiteit['besloten']) ? ((int)$activiteit['besloten'] === 1 ? 'Ja' : 'Nee') : null); ?>
                        <?php renderActiviteitValueRow('Status', $activiteit['status'] ?? null); ?>
                        <?php renderActiviteitValueRow('Vergaderjaar', $activiteit['vergaderjaar'] ?? null); ?>
                        <?php renderActiviteitValueRow('Kamer', $activiteit['kamer'] ?? null); ?>
                        <?php renderActiviteitValueRow('Noot', $activiteit['noot'] ?? null); ?>
                        <?php renderActiviteitValueRow('VRS-nummer', $activiteit['vrsnummer'] ?? null); ?>
                        <?php renderActiviteitValueRow('SID voortouw', $activiteit['sidvoortouw'] ?? null); ?>
                        <?php renderActiviteitValueRow('Voortouw naam', $activiteit['voortouwnaam'] ?? null); ?>
                        <?php renderActiviteitValueRow('Voortouw afkorting', $activiteit['voortouwafkorting'] ?? null); ?>
                        <?php renderActiviteitValueRow('Voortouw korte naam', $activiteit['voortouwkortenaam'] ?? null); ?>
                        <?php 
                        /** todo generate commissie list and detail page */
                        renderActiviteitCommissieRow($activiteit); ?>
                        <?php renderActiviteitValueRow('Aanvraagdatum', activiteitFormatDate($activiteit['aanvraagdatum'] ?? null)); ?>
                        <?php renderActiviteitValueRow('Datum verzoek eerste verlenging', activiteitFormatDate($activiteit['datumverzoekeersteverlenging'] ?? null)); ?>
                        <?php renderActiviteitValueRow('Datum mededeling eerste verlenging', activiteitFormatDate($activiteit['datummededelingeersteverlenging'] ?? null)); ?>
                        <?php renderActiviteitValueRow('Datum verzoek tweede verlenging', activiteitFormatDate($activiteit['datumverzoektweedeverlenging'] ?? null)); ?>
                        <?php renderActiviteitValueRow('Datum mededeling tweede verlenging', activiteitFormatDate($activiteit['datummededelingtweedeverlenging'] ?? null)); ?>
                        <?php renderActiviteitValueRow('Vervaldatum', activiteitFormatDate($activiteit['vervaldatum'] ?? null)); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php renderActiviteitActorSection($actorRows); ?>
        <?php renderActiviteitAgendapuntSection($agendapuntRows); ?>
        <?php renderActiviteitMixedContentSection($inhoudRows ?? []); ?>
        <?php
    }

 
    if (!function_exists('renderActiviteitActorSection')) {
        function renderActiviteitActorSection(array $actorRows = []): void
        {
            if (empty($actorRows)) {
                return;
            }
            ?>
            <div class="card border-0 bg-light mt-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">Actoren</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Naam</th>
                                    <th>Fractie</th>
                                    <th>Relatie</th>
                                    <th>Functie</th>
                                    <th class="text-end">Spreektijd</th>
                                    <th class="text-center">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($actorRows as $actor): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string)($actor['actor_naam'] ?? '')) ?></td>
                                        <td><?= htmlspecialchars((string)($actor['actor_fractie'] ?? '')) ?></td>
                                        <td><?= htmlspecialchars((string)($actor['relatie'] ?? '')) ?></td>
                                        <td><?= htmlspecialchars((string)($actor['functie'] ?? '')) ?></td>
                                        <td class="text-end text-nowrap">
                                            <?= htmlspecialchars(activiteitFormatTime($actor['spreektijd'] ?? null)) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($actor['persoon_id'])): ?>
                                                <a
                                                    href="persondetails.php?id=<?= urlencode((string)$actor['persoon_id']) ?>"
                                                    class="btn btn-sm btn-outline-primary me-1"
                                                    title="Bekijk persoon"
                                                >
                                                    <i class="fa-solid fa-user"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (!empty($actor['fractie_id'])): ?>
                                                <a
                                                    href="fractiedetails.php?id=<?= urlencode((string)$actor['fractie_id']) ?>"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    title="Bekijk fractie"
                                                >
                                                    <i class="fa-solid fa-users"></i>
                                                </a>
                                            <?php endif; ?>
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

    if (!function_exists('activiteitPickCommissieNaam')) {
        function activiteitPickCommissieNaam(array $activiteit): ?string
        {
            $naamNl = $activiteit['commissie_naam_nl'] ?? null;
            $naamEn = $activiteit['commissie_naam_en'] ?? null;

            if ($naamNl !== null && $naamNl !== '') {
                return (string)$naamNl;
            }

            if ($naamEn !== null && $naamEn !== '') {
                return (string)$naamEn;
            }

            return null;
        }
    }

    if (!function_exists('renderActiviteitCommissieRow')) {
        function renderActiviteitCommissieRow(array $activiteit): void
        {
            $commissieNaam = activiteitPickCommissieNaam($activiteit);
            $commissieId = $activiteit['commissie_id'] ?? $activiteit['voortouwcommissie_id'] ?? null;

            if (($commissieNaam === null || $commissieNaam === '') && ($commissieId === null || $commissieId === '')) {
                return;
            }
            ?>
            <div class="row py-2 border-bottom">
                <div class="col-sm-4 fw-semibold">Voortouwcommissie</div>
                <div class="col-sm-8">
                    <?php if ($commissieId !== null && $commissieId !== '' && $commissieNaam !== null && $commissieNaam !== ''): ?>
                        <a href="commissiedetails.php?id=<?= urlencode((string)$commissieId) ?>">
                            <?= htmlspecialchars($commissieNaam) ?>
                        </a>
                    <?php elseif ($commissieNaam !== null && $commissieNaam !== ''): ?>
                        <?= htmlspecialchars($commissieNaam) ?>
                    <?php else: ?>
                        <?= htmlspecialchars((string)$commissieId) ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    }    

    if (!function_exists('renderActiviteitAgendapuntSection')) {
        function renderActiviteitAgendapuntSection(array $agendapuntRows = []): void
        {
            if (empty($agendapuntRows)) {
                return;
            }
            ?>
            <div class="card border-0 bg-light mt-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">Agendapunten</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th class="text-end">Nr.</th>
                                    <th>Onderwerp</th>
                                    <th class="text-end">Aanvang</th>
                                    <th class="text-end">Einde</th>
                                    <th>Rubriek</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agendapuntRows as $agendapunt): ?>
                                    <tr>
                                        <td class="text-end text-nowrap">
                                            <?= htmlspecialchars((string)($agendapunt['nummer'] ?? '')) ?>
                                        </td>
                                        <td>
                                            <div><?= htmlspecialchars((string)($agendapunt['onderwerp'] ?? '')) ?></div>
                                            <?php if (!empty($agendapunt['noot'])): ?>
                                                <div class="small text-muted mt-1">
                                                    <?= htmlspecialchars((string)$agendapunt['noot']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <?= htmlspecialchars(activiteitFormatTime($agendapunt['aanvangstijd'] ?? null)) ?>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <?= htmlspecialchars(activiteitFormatTime($agendapunt['eindtijd'] ?? null)) ?>
                                        </td>
                                        <td><?= htmlspecialchars((string)($agendapunt['rubriek'] ?? '')) ?></td>
                                        <td><?= htmlspecialchars((string)($agendapunt['status'] ?? '')) ?></td>
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






    if (!function_exists('activiteitContentHasValue')) {
        function activiteitContentHasValue(mixed $value): bool
        {
            return $value !== null && trim((string)$value) !== '';
        }
    }

    if (!function_exists('activiteitIsHtmlContent')) {
        function activiteitIsHtmlContent(string $value): bool
        {
            return $value !== strip_tags($value);
        }
    }

    if (!function_exists('activiteitShouldRenderAsDocument')) {
        function activiteitShouldRenderAsDocument(string $value, int $textThreshold = 200): bool
        {
            if (activiteitIsHtmlContent($value)) {
                return true;
            }

            $plainText = trim(strip_tags($value));

            return mb_strlen($plainText) > $textThreshold;
        }
    }

    if (!function_exists('renderActiviteitContentTextBlock')) {
        function renderActiviteitContentTextBlock(string $title, string $content): void
        {
            if (!activiteitContentHasValue($content)) {
                return;
            }
            ?>
            <div class="row py-2 border-bottom">
                <div class="col-sm-4 fw-semibold"><?= htmlspecialchars($title) ?></div>
                <div class="col-sm-8"><?= nl2br(htmlspecialchars(trim($content))) ?></div>
            </div>
            <?php
        }
    }

    if (!function_exists('renderActiviteitContentDocumentBlock')) {
        function renderActiviteitContentDocumentBlock(string $title, string $content): void
        {
            if (!activiteitContentHasValue($content)) {
                return;
            }

            $isHtml = activiteitIsHtmlContent($content);
            ?>
            <div class="card border-0 bg-light mt-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><?= htmlspecialchars($title) ?></h5>
                </div>
                <div class="card-body">
                    <div class="border rounded bg-white p-3 activiteit-content-document">
                        <?php if ($isHtml): ?>
                            <?= $content ?>
                        <?php else: ?>
                            <?= nl2br(htmlspecialchars(trim($content))) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    if (!function_exists('renderActiviteitMixedContentSection')) {
        /**
         * Expected rows:
         * [
         *   ['soort' => 'Toelichting', 'content' => 'Korte tekst'],
         *   ['soort' => 'Agenda', 'content' => '<p>Volledige html...</p>'],
         * ]
         */
        function renderActiviteitMixedContentSection(array $contentRows = []): void
        {
            if (empty($contentRows)) {
                return;
            }

            $shortRows = [];
            $documentRows = [];

            foreach ($contentRows as $row) {
                $title = trim((string)($row['soort'] ?? ''));
                $content = (string)($row['content'] ?? '');

                if ($title === '' || !activiteitContentHasValue($content)) {
                    continue;
                }

                if (activiteitShouldRenderAsDocument($content)) {
                    $documentRows[] = [
                        'title' => $title,
                        'content' => $content,
                    ];
                } else {
                    $shortRows[] = [
                        'title' => $title,
                        'content' => $content,
                    ];
                }
            }

            if (empty($shortRows) && empty($documentRows)) {
                return;
            }
            ?>
            <div class="card border-0 bg-light mt-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">Inhoud</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($shortRows)): ?>
                        <div class="card border-0 bg-white mb-4">
                            <div class="card-body">
                                <?php foreach ($shortRows as $row): ?>
                                    <?php renderActiviteitContentTextBlock($row['title'], $row['content']); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($documentRows as $row): ?>
                        <?php renderActiviteitContentDocumentBlock($row['title'], $row['content']); ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
        }
    }
   
}