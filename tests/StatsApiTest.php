<?php
declare(strict_types=1);

use App\Api\StatsApi;
use App\Models\StatsModel;
use PHPUnit\Framework\TestCase;

final class StatsApiTest extends TestCase
{
    public function testGetPersonRankingsDelegatesToModel(): void
    {
        $expected = ['Most votes' => [['id' => 'p1']]];

        $model = $this->createMock(StatsModel::class);
        $model->expects($this->once())
            ->method('getPersonRankings')
            ->willReturn($expected);

        $api = new StatsApi($model);

        $this->assertSame($expected, $api->getPersonRankings());
    }

    public function testGetFractieRankingsDelegatesToModel(): void
    {
        $expected = ['Most votes' => [['id' => 'f1']]];

        $model = $this->createMock(StatsModel::class);
        $model->expects($this->once())
            ->method('getFractieRankings')
            ->willReturn($expected);

        $api = new StatsApi($model);

        $this->assertSame($expected, $api->getFractieRankings());
    }

    public function testGetActivePersonStatsDelegatesToModel(): void
    {
        $expected = [['id' => 'p1', 'jaren_ervaring' => 12.5]];

        $model = $this->createMock(StatsModel::class);
        $model->expects($this->once())
            ->method('getActivePersonStats')
            ->with('jaren_ervaring', 'desc')
            ->willReturn($expected);

        $api = new StatsApi($model);

        $this->assertSame($expected, $api->getActivePersonStats('jaren_ervaring', 'desc'));
    }

    public function testGetPersonStatsListDelegatesToModel(): void
    {
        $expected = [['id' => 'p1', 'totaal_stemmen' => 100]];

        $model = $this->createMock(StatsModel::class);
        $model->expects($this->once())
            ->method('getPersonStatsList')
            ->with('totaal_stemmen', 'desc')
            ->willReturn($expected);

        $api = new StatsApi($model);

        $this->assertSame($expected, $api->getPersonStatsList('totaal_stemmen', 'desc'));
    }

    public function testGetFractieStatsListDelegatesToModel(): void
    {
        $expected = [['id' => 'f1', 'totaal_stemmen' => 40]];

        $model = $this->createMock(StatsModel::class);
        $model->expects($this->once())
            ->method('getFractieStatsList')
            ->with('totaal_stemmen', 'desc')
            ->willReturn($expected);

        $api = new StatsApi($model);

        $this->assertSame($expected, $api->getFractieStatsList('totaal_stemmen', 'desc'));
    }

    public function testGetBesluitStatsListDelegatesToModel(): void
    {
        $expected = [['id' => 'b1', 'totaal_stemmen' => 200]];

        $model = $this->createMock(StatsModel::class);
        $model->expects($this->once())
            ->method('getBesluitStatsList')
            ->with('totaal_stemmen', 'desc')
            ->willReturn($expected);

        $api = new StatsApi($model);

        $this->assertSame($expected, $api->getBesluitStatsList('totaal_stemmen', 'desc'));
    }
}
