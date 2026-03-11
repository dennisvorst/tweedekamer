<?php
declare(strict_types=1);

if (!function_exists('zaakHasValue')) {
    function zaakHasValue(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }
}

if (!function_exists('zaakFormatDate')) {
    function zaakFormatDate(?string $date): string
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

if (!function_exists('zaakRenderValueRow')) {
    function zaakRenderValueRow(string $label, mixed $value): void
    {
        if (!zaakHasValue($value)) {
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

if (!function_exists('zaakActorPersoonNaam')) {
    function zaakActorPersoonNaam(array $actor): ?string
    {
        $parts = array_filter([
            $actor['roepnaam'] ?? null,
            $actor['achternaam'] ?? null,
        ]);

        if (empty($parts)) {
            return null;
        }

        return implode(' ', $parts);
    }
}

if (!function_exists('zaakActorFractieNaam')) {
    function zaakActorFractieNaam(array $actor): ?string
    {
        if (zaakHasValue($actor['fractie_naam_nl'] ?? null)) {
            return (string)$actor['fractie_naam_nl'];
        }

        if (zaakHasValue($actor['fractie_naam_en'] ?? null)) {
            return (string)$actor['fractie_naam_en'];
        }

        if (zaakHasValue($actor['actor_fractie'] ?? null)) {
            return (string)$actor['actor_fractie'];
        }

        if (zaakHasValue($actor['fractie_afkorting'] ?? null)) {
            return (string)$actor['fractie_afkorting'];
        }

        if (zaakHasValue($actor['actor_afkorting'] ?? null)) {
            return (string)$actor['actor_afkorting'];
        }

        return null;
    }
}

if (!function_exists('zaakActorCommissieNaam')) {
    function zaakActorCommissieNaam(array $actor): ?string
    {
        if (zaakHasValue($actor['commissie_naam_nl'] ?? null)) {
            return (string)$actor['commissie_naam_nl'];
        }

        if (zaakHasValue($actor['commissie_naam_en'] ?? null)) {
            return (string)$actor['commissie_naam_en'];
        }

        if (zaakHasValue($actor['commissie_afkorting'] ?? null)) {
            return (string)$actor['commissie_afkorting'];
        }

        return null;
    }
}

if (!function_exists('renderZaakActorRelatedLinks')) {
    function renderZaakActorRelatedLinks(array $actor): void
    {
        $hasAny = false;
        ?>
        <div class="d-flex flex-wrap gap-2">
            <?php if (!empty($actor['persoon_id'])): ?>
                <?php $hasAny = true; ?>
                <a
                    href="persondetails.php?id=<?= urlencode((string)$actor['persoon_id']) ?>"
                    class="btn btn-sm btn-outline-primary"
                    title="Bekijk persoon"
                >
                    <i class="fa-solid fa-user me-1"></i>
                    <?= htmlspecialchars(zaakActorPersoonNaam($actor) ?? 'Persoon') ?>
                </a>
            <?php endif; ?>

            <?php if (!empty($actor['fractie_id'])): ?>
                <?php $hasAny = true; ?>
                <a
                    href="fractiedetails.php?id=<?= urlencode((string)$actor['fractie_id']) ?>"
                    class="btn btn-sm btn-outline-secondary"
                    title="Bekijk fractie"
                >
                    <i class="fa-solid fa-users me-1"></i>
                    <?= htmlspecialchars(zaakActorFractieNaam($actor) ?? 'Fractie') ?>
                </a>
            <?php endif; ?>

            <?php if (!empty($actor['commissie_id'])): ?>
                <?php $hasAny = true; ?>
                <a
                    href="commissiedetails.php?id=<?= urlencode((string)$actor['commissie_id']) ?>"
                    class="btn btn-sm btn-outline-dark"
                    title="Bekijk commissie"
                >
                    <i class="fa-solid fa-building-columns me-1"></i>
                    <?= htmlspecialchars(zaakActorCommissieNaam($actor) ?? 'Commissie') ?>
                </a>
            <?php endif; ?>

            <?php if (!$hasAny): ?>
                <span class="text-muted">—</span>
            <?php endif; ?>
        </div>
        <?php
    }
}

if (!function_exists('renderZaakActorSection')) {
    function renderZaakActorSection(array $actorRows = []): void
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
                                <th>Functie</th>
                                <th>Relatie</th>
                                <th>Gerelateerd</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($actorRows as $actor): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string)($actor['actor_naam'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($actor['actor_fractie'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($actor['functie'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($actor['relatie'] ?? '')) ?></td>
                                    <td>
                                        <?php renderZaakActorRelatedLinks($actor); ?>
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

if (!function_exists('renderZaakDetails')) {
    function renderZaakDetails(array $zaak, array $actorRows = []): void
    {
        $displayTitle = (string)($zaak['titel'] ?? $zaak['nummer'] ?? 'Zaak');
        ?>
        <div class="card shadow-sm">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h2 class="mb-1"><?= htmlspecialchars($displayTitle) ?></h2>
                        <?php if (zaakHasValue($zaak['nummer'] ?? null)): ?>
                            <div class="text-muted"><?= htmlspecialchars((string)$zaak['nummer']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <a href="index.php?tab=zaak" class="btn btn-outline-primary">
                            <i class="fa-solid fa-arrow-left"></i> Terug naar overzicht
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="card border-0 bg-light">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">Zaakgegevens</h5>
                    </div>
                    <div class="card-body">
                        <?php zaakRenderValueRow('Nummer', $zaak['nummer'] ?? null); ?>
                        <?php zaakRenderValueRow('Soort', $zaak['soort'] ?? null); ?>
                        <?php zaakRenderValueRow('Titel', $zaak['titel'] ?? null); ?>
                        <?php zaakRenderValueRow('Citeertitel', $zaak['citeertitel'] ?? null); ?>
                        <?php zaakRenderValueRow('Alias', $zaak['alias'] ?? null); ?>
                        <?php zaakRenderValueRow('Status', $zaak['status'] ?? null); ?>
                        <?php zaakRenderValueRow('Onderwerp', $zaak['onderwerp'] ?? null); ?>
                        <?php zaakRenderValueRow('Gestart op', zaakFormatDate($zaak['gestart_op'] ?? null)); ?>
                        <?php zaakRenderValueRow('Organisatie', $zaak['organisatie'] ?? null); ?>
                        <?php zaakRenderValueRow('Grondslag voorhang', $zaak['grondslag_voorhang'] ?? null); ?>
                        <?php zaakRenderValueRow('Termijn', $zaak['termijn'] ?? null); ?>
                        <?php zaakRenderValueRow('Vergaderjaar', $zaak['vergaderjaar'] ?? null); ?>
                        <?php zaakRenderValueRow('Volgnummer', $zaak['volgnummer'] ?? null); ?>
                        <?php zaakRenderValueRow('Huidige behandelstatus', $zaak['huidige_behandelstatus'] ?? null); ?>
                        <?php zaakRenderValueRow('Afgedaan', isset($zaak['afgedaan']) ? ((int)$zaak['afgedaan'] === 1 ? 'Ja' : 'Nee') : null); ?>
                        <?php zaakRenderValueRow('Groot project', isset($zaak['groot_project']) ? ((int)$zaak['groot_project'] === 1 ? 'Ja' : 'Nee') : null); ?>
                    </div>
                </div>

                <?php renderZaakActorSection($actorRows); ?>
            </div>
        </div>
        <?php
    }
}