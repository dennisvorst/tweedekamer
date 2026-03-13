<?php
declare(strict_types=1);

use App\Models\PersonModel;
use PHPUnit\Framework\TestCase;

final class PersonModelTest extends TestCase
{
    private PDO $pdo;
    private PersonModel $model;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->pdo->exec('
            CREATE TABLE persoon (
                id TEXT PRIMARY KEY,
                nummer TEXT,
                roepnaam TEXT,
                achternaam TEXT,
                geboortedatum TEXT,
                geslacht TEXT,
                is_verwijderd INTEGER
            )
        ');

        $this->pdo->exec('
            CREATE TABLE fractie_zetel_persoon (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                persoon_id TEXT NOT NULL,
                fractie_zetel_id TEXT,
                functie TEXT,
                van TEXT,
                tot_en_met TEXT,
                is_verwijderd INTEGER
            )
        ');

        $this->seedPersons();
        $this->seedFractieZetelPersonen();

        $this->model = new PersonModel($this->pdo);
    }

    public function testGetPersonsExcludesSoftDeletedPersons(): void
    {
        $persons = $this->model->getPersons([], 'achternaam', 'asc', 1, 20);
        $ids = array_column($persons, 'id');

        $this->assertSame(['p1', 'p2', 'p4'], $ids);
    }

    public function testCountPersonsExcludesSoftDeletedPersons(): void
    {
        $this->assertSame(3, $this->model->countPersons([]));
    }

    public function testGetPersonsCanFilterActivePersonsOnly(): void
    {
        $persons = $this->model->getPersons(['active_only' => '1'], 'achternaam', 'asc', 1, 20);
        $ids = array_column($persons, 'id');

        $this->assertSame(['p1'], $ids);
    }

    public function testCountPersonsCanFilterActivePersonsOnly(): void
    {
        $this->assertSame(1, $this->model->countPersons(['active_only' => '1']));
    }

    public function testGetPersonsCanFilterBirthDateAfterOrEqual(): void
    {
        $persons = $this->model->getPersons(['geboortedatum' => '1981-01-01'], 'achternaam', 'asc', 1, 20);
        $ids = array_column($persons, 'id');

        $this->assertSame(['p2', 'p4'], $ids);
    }

    public function testGetPersonsCanFilterBirthDateBeforeOrEqual(): void
    {
        $persons = $this->model->getPersons(['geboortedatum_tot' => '1981-01-01'], 'achternaam', 'asc', 1, 20);
        $ids = array_column($persons, 'id');

        $this->assertSame(['p1', 'p2'], $ids);
    }

    public function testGetPersonsCanFilterBirthDateBetween(): void
    {
        $persons = $this->model->getPersons(
            ['geboortedatum' => '1980-01-15', 'geboortedatum_tot' => '1983-01-01'],
            'achternaam',
            'asc',
            1,
            20
        );
        $ids = array_column($persons, 'id');

        $this->assertSame(['p2', 'p4'], $ids);
    }

    private function seedPersons(): void
    {
        $statement = $this->pdo->prepare('
            INSERT INTO persoon (id, nummer, roepnaam, achternaam, geboortedatum, geslacht, is_verwijderd)
            VALUES (:id, :nummer, :roepnaam, :achternaam, :geboortedatum, :geslacht, :is_verwijderd)
        ');

        $rows = [
            [
                'id' => 'p1',
                'nummer' => '1',
                'roepnaam' => 'Anna',
                'achternaam' => 'Actief',
                'geboortedatum' => '1980-01-01',
                'geslacht' => 'Vrouw',
                'is_verwijderd' => null,
            ],
            [
                'id' => 'p2',
                'nummer' => '2',
                'roepnaam' => 'Bert',
                'achternaam' => 'Beeindigd',
                'geboortedatum' => '1981-01-01',
                'geslacht' => 'Man',
                'is_verwijderd' => 0,
            ],
            [
                'id' => 'p3',
                'nummer' => '3',
                'roepnaam' => 'Chris',
                'achternaam' => 'Verwijderd',
                'geboortedatum' => '1982-01-01',
                'geslacht' => 'Man',
                'is_verwijderd' => 1,
            ],
            [
                'id' => 'p4',
                'nummer' => '4',
                'roepnaam' => 'Dina',
                'achternaam' => 'GeenFractie',
                'geboortedatum' => '1983-01-01',
                'geslacht' => 'Vrouw',
                'is_verwijderd' => 0,
            ],
        ];

        foreach ($rows as $row) {
            $statement->execute($row);
        }
    }

    private function seedFractieZetelPersonen(): void
    {
        $statement = $this->pdo->prepare('
            INSERT INTO fractie_zetel_persoon (persoon_id, fractie_zetel_id, functie, van, tot_en_met, is_verwijderd)
            VALUES (:persoon_id, :fractie_zetel_id, :functie, :van, :tot_en_met, :is_verwijderd)
        ');

        $rows = [
            [
                'persoon_id' => 'p1',
                'fractie_zetel_id' => 'fz1',
                'functie' => 'Lid',
                'van' => '2024-01-01',
                'tot_en_met' => null,
                'is_verwijderd' => 0,
            ],
            [
                'persoon_id' => 'p2',
                'fractie_zetel_id' => 'fz2',
                'functie' => 'Lid',
                'van' => '2023-01-01',
                'tot_en_met' => '2024-12-31',
                'is_verwijderd' => 0,
            ],
            [
                'persoon_id' => 'p4',
                'fractie_zetel_id' => 'fz4',
                'functie' => 'Lid',
                'van' => '2022-01-01',
                'tot_en_met' => null,
                'is_verwijderd' => 1,
            ],
        ];

        foreach ($rows as $row) {
            $statement->execute($row);
        }
    }
}
