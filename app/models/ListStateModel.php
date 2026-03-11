<?php
declare(strict_types=1);

namespace App\Models;

class ListStateModel
{
    private string $sessionKey = 'list_state';
    private string $entityKey;

    public function __construct(string $entityKey)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->entityKey = $entityKey;

        if (!isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = [];
        }

        if (!isset($_SESSION[$this->sessionKey][$this->entityKey])) {
            $_SESSION[$this->sessionKey][$this->entityKey] = [
                'sort' => null,
                'direction' => null,
                'page' => 1,
                'filters' => [],
            ];
        }
    }

    public function getState(array $defaults = []): array
    {
        $stored = $_SESSION[$this->sessionKey][$this->entityKey] ?? [];

        return [
            'sort' => $stored['sort'] ?? ($defaults['sort'] ?? null),
            'direction' => $stored['direction'] ?? ($defaults['direction'] ?? null),
            'page' => (int)($stored['page'] ?? ($defaults['page'] ?? 1)),
            'filters' => array_merge(
                $defaults['filters'] ?? [],
                $stored['filters'] ?? []
            ),
        ];
    }

    public function applyRequest(array $request, array $defaults = [], array $allowedFilters = []): array
    {
        $currentState = $this->getState($defaults);

        $sort = isset($request['sort']) && $request['sort'] !== ''
            ? (string)$request['sort']
            : $currentState['sort'];

        $direction = isset($request['direction']) && $request['direction'] !== ''
            ? strtolower((string)$request['direction'])
            : $currentState['direction'];

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = $defaults['direction'] ?? 'asc';
        }

        $page = isset($request['page']) ? max(1, (int)$request['page']) : (int)$currentState['page'];

        $filters = $currentState['filters'];

        foreach ($allowedFilters as $filterKey) {
            if (array_key_exists($filterKey, $request)) {
                $filters[$filterKey] = is_string($request[$filterKey])
                    ? trim($request[$filterKey])
                    : $request[$filterKey];
            } elseif (!array_key_exists($filterKey, $filters)) {
                $filters[$filterKey] = $defaults['filters'][$filterKey] ?? '';
            }
        }

        $newState = [
            'sort' => $sort ?? ($defaults['sort'] ?? null),
            'direction' => $direction ?? ($defaults['direction'] ?? 'asc'),
            'page' => $page,
            'filters' => $filters,
        ];

        $this->saveState($newState);

        return $newState;
    }

    public function saveState(array $state): void
    {
        $_SESSION[$this->sessionKey][$this->entityKey] = [
            'sort' => $state['sort'] ?? null,
            'direction' => $state['direction'] ?? 'asc',
            'page' => max(1, (int)($state['page'] ?? 1)),
            'filters' => $state['filters'] ?? [],
        ];
    }

    public function reset(array $defaults = []): array
    {
        $state = [
            'sort' => $defaults['sort'] ?? null,
            'direction' => $defaults['direction'] ?? 'asc',
            'page' => $defaults['page'] ?? 1,
            'filters' => $defaults['filters'] ?? [],
        ];

        $this->saveState($state);

        return $state;
    }

    public function getEntityKey(): string
    {
        return $this->entityKey;
    }
}