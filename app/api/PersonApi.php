<?php
declare(strict_types=1);

namespace App\Api;

use App\Models\PersonModel;

class PersonApi
{
    public function __construct(
        private PersonModel $personModel
    ) {
    }

   

    public function getPersons(
        array $filters = [],
        string $sort = 'achternaam',
        string $direction = 'asc',
        int $page = 1,
        int $perPage = 50
    ): array {
        $allowedSorts = [
            'id',
            'nummer',
            'roepnaam',
            'achternaam',
            'geboortedatum',
            'geslacht',
        ];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'achternaam';
        }

        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        $page = max(1, $page);
        $perPage = max(1, min(200, $perPage));

        return [
            'data' => $this->personModel->getPersons($filters, $sort, $direction, $page, $perPage),
            'total' => $this->personModel->countPersons($filters),
        ];
    }

    public function getPersonDetails(string $id): ?array
    {
        return $this->personModel->getPersonDetails($id);
    }    

    public function getPersonOnderwijs(string $id): ?array
    {
        return $this->personModel->getPersonOnderwijs($id);
    }

    public function getPersonContactInformation(string $id): ?array
    {
        return $this->personModel->getPersonContactInformation($id);
    }    

    public function getPersonLoopbaan(string $persoonId): array
    {
        return $this->personModel->getPersonLoopbaan($persoonId);
    }

    public function getPersonNevenfuncties(string $persoonId): array
    {
        return $this->personModel->getPersonNevenfuncties($persoonId);
    }

    public function getPersonNevenfunctieInkomsten(string $persoonId): array
    {
        return $this->personModel->getPersonNevenfunctieInkomsten($persoonId);
    }    

    public function getPersonBesluitStemRows(string $persoonId): array
    {
        return $this->personModel->getPersonBesluitStemRows($persoonId);
    }

    public function getPersonFractieRows(string $persoonId): array
    {
        return $this->personModel->getPersonFractieRows($persoonId);
    }    
  
}