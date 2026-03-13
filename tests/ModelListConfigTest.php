<?php
declare(strict_types=1);

use App\Models\ActiviteitModel;
use App\Models\FractieModel;
use App\Models\PersonModel;
use App\Models\ZaakModel;
use App\Models\ZaalModel;
use PHPUnit\Framework\TestCase;

final class ModelListConfigTest extends TestCase
{
    public static function listConfigProvider(): array
    {
        return [
            'activiteit' => [ActiviteitModel::class, 'datum', 'desc'],
            'fractie' => [FractieModel::class, 'naam_nl', 'asc'],
            'person' => [PersonModel::class, 'achternaam', 'asc'],
            'zaak' => [ZaakModel::class, 'gestart_op', 'desc'],
            'zaal' => [ZaalModel::class, 'naam', 'asc'],
        ];
    }

    /**
     * @dataProvider listConfigProvider
     */
    public function testListDefaultsMatchAllowedFilters(
        string $modelClass,
        string $expectedSort,
        string $expectedDirection
    ): void {
        $defaults = $modelClass::getListDefaults();
        $allowedFilters = $modelClass::getAllowedFilters();

        $this->assertSame($expectedSort, $defaults['sort']);
        $this->assertSame($expectedDirection, $defaults['direction']);
        $this->assertSame(1, $defaults['page']);
        $this->assertSame($allowedFilters, array_keys($defaults['filters']));
        $this->assertSame(
            array_fill_keys($allowedFilters, ''),
            $defaults['filters']
        );
    }

    /**
     * @dataProvider listConfigProvider
     */
    public function testAllowedFiltersContainUniqueFieldNames(
        string $modelClass
    ): void {
        $allowedFilters = $modelClass::getAllowedFilters();

        $this->assertNotEmpty($allowedFilters);
        $this->assertSame($allowedFilters, array_values($allowedFilters));
        $this->assertSame($allowedFilters, array_unique($allowedFilters));

        foreach ($allowedFilters as $filterName) {
            $this->assertIsString($filterName);
            $this->assertNotSame('', $filterName);
        }
    }

    public function testPersonModelExposesPersonSpecificFilters(): void
    {
        $allowedFilters = PersonModel::getAllowedFilters();
        $defaults = PersonModel::getListDefaults();

        $this->assertContains('active_only', $allowedFilters);
        $this->assertContains('geboortedatum_tot', $allowedFilters);
        $this->assertArrayHasKey('active_only', $defaults['filters']);
        $this->assertArrayHasKey('geboortedatum_tot', $defaults['filters']);
        $this->assertSame('', $defaults['filters']['active_only']);
        $this->assertSame('', $defaults['filters']['geboortedatum_tot']);
    }
}
