<?php

namespace DataQualityLogApiV2\Api\Resources;

use Plenty\Log\Search\Contracts\LogRepositoryContract;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

class LogResource extends Controller
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var LogRepositoryContract
     */
    private $logRepository;

    public function __construct(
        Request $request,
        Response $response,
        LogRepositoryContract $logRepository
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->logRepository = $logRepository;
    }

    /**
     * GET /rest/dq-v2/logs
     */
    public function index(): Response
    {
        try {
            $page = $this->normalizePage(
                $this->request->get('page')
            );

            $itemsPerPage = $this->normalizeItemsPerPage(
                $this->request->get('itemsPerPage')
            );

            $filters = $this->normalizeFilters(
                $this->request->get('filters')
            );

            $simpleFilters = $this->getSimpleRequestFilters();

            foreach ($simpleFilters as $key => $value) {
                $filters[$key] = $value;
            }

            $sortBy = $this->normalizeSortBy(
                $this->request->get('sortBy')
            );

            $sortOrder = $this->normalizeSortOrder(
                $this->request->get('sortOrder')
            );

            return $this->executeSearch(
                $page,
                $itemsPerPage,
                $filters,
                $sortBy,
                $sortOrder
            );
        } catch (\Throwable $exception) {
            return $this->errorResponse(
                $exception
            );
        }
    }

    /**
     * POST /rest/dq-v2/logs/search
     */
    public function search(): Response
    {
        try {
            $payload = $this->request->all();

            if (!is_array($payload)) {
                $payload = [];
            }

            $page = $this->normalizePage(
                $payload['page'] ?? null
            );

            $itemsPerPage = $this->normalizeItemsPerPage(
                $payload['itemsPerPage'] ?? null
            );

            $filters = $this->normalizeFilters(
                $payload['filters'] ?? []
            );

            $simpleFilters = $this->getSimplePayloadFilters(
                $payload
            );

            foreach ($simpleFilters as $key => $value) {
                $filters[$key] = $value;
            }

            $sortBy = $this->normalizeSortBy(
                $payload['sortBy'] ?? null
            );

            $sortOrder = $this->normalizeSortOrder(
                $payload['sortOrder'] ?? null
            );

            return $this->executeSearch(
                $page,
                $itemsPerPage,
                $filters,
                $sortBy,
                $sortOrder
            );
        } catch (\Throwable $exception) {
            return $this->errorResponse(
                $exception
            );
        }
    }

    /**
     * GET /rest/dq-v2/logs/{id}
     */
    public function show($id): Response
    {
        try {
            $id = trim(
                (string) $id
            );

            if ($id === '') {
                return $this->jsonResponse(
                    [
                        'ok' => false,
                        'error' => 'Missing log ID.'
                    ],
                    400
                );
            }

            $log = $this->logRepository->get(
                $id
            );

            if ($log === null) {
                return $this->jsonResponse(
                    [
                        'ok' => false,
                        'error' => 'Log entry not found.',
                        'id' => $id
                    ],
                    404
                );
            }

            return $this->jsonResponse(
                [
                    'ok' => true,
                    'entry' => $log->toArray()
                ],
                200
            );
        } catch (\Throwable $exception) {
            return $this->errorResponse(
                $exception
            );
        }
    }

    private function executeSearch(
        int $page,
        int $itemsPerPage,
        array $filters,
        string $sortBy,
        string $sortOrder
    ): Response {
        $result = $this->logRepository->search(
            $page,
            $itemsPerPage,
            $filters,
            $sortBy,
            $sortOrder,
            []
        );

        return $this->jsonResponse(
            [
                'ok' => true,

                'request' => [
                    'page' => $page,
                    'itemsPerPage' => $itemsPerPage,
                    'filters' => $filters,
                    'sortBy' => $sortBy,
                    'sortOrder' => $sortOrder
                ],

                'result' => $result->toArray()
            ],
            200
        );
    }

    private function getSimpleRequestFilters(): array
    {
        $filters = [];

        $identifier = $this->request->get(
            'identifier'
        );

        $identifierValues = $this->normalizeArrayFilter(
            $identifier
        );

        if (count($identifierValues) > 0) {
            $filters['identifier'] = $identifierValues;
        }

        $level = $this->normalizeStringFilter(
            $this->request->get(
                'level'
            )
        );

        if ($level !== null) {
            $filters['level'] = $level;
        }

        return $filters;
    }

    private function getSimplePayloadFilters(
        array $payload
    ): array {
        $filters = [];

        if (
            array_key_exists(
                'identifier',
                $payload
            )
        ) {
            $identifierValues = $this->normalizeArrayFilter(
                $payload['identifier']
            );

            if (count($identifierValues) > 0) {
                $filters['identifier'] = $identifierValues;
            }
        }

        if (
            array_key_exists(
                'level',
                $payload
            )
        ) {
            $level = $this->normalizeStringFilter(
                $payload['level']
            );

            if ($level !== null) {
                $filters['level'] = $level;
            }
        }

        return $filters;
    }

    private function normalizeFilters(
        $value
    ): array {
        if (
            $value === null ||
            $value === ''
        ) {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode(
                $value,
                true
            );

            if (!is_array($decoded)) {
                throw new \InvalidArgumentException(
                    'filters must be a valid JSON object or array.'
                );
            }

            $value = $decoded;
        }

        if (!is_array($value)) {
            throw new \InvalidArgumentException(
                'filters must be an array or JSON string.'
            );
        }

        $normalized = [];

        foreach (
            $value
            as $key => $filterValue
        ) {
            $key = trim(
                (string) $key
            );

            if ($key === '') {
                continue;
            }

            if ($key === 'identifier') {
                $values = $this->normalizeArrayFilter(
                    $filterValue
                );

                if (count($values) > 0) {
                    $normalized[$key] = $values;
                }

                continue;
            }

            if ($key === 'level') {
                $stringValue = $this->normalizeStringFilter(
                    $filterValue
                );

                if ($stringValue !== null) {
                    $normalized[$key] = $stringValue;
                }

                continue;
            }

            $normalized[$key] = $filterValue;
        }

        return $normalized;
    }

    private function normalizeArrayFilter(
        $value
    ): array {
        if (
            $value === null ||
            $value === ''
        ) {
            return [];
        }

        if (!is_array($value)) {
            $value = [
                $value
            ];
        }

        $normalized = [];

        foreach (
            $value
            as $entry
        ) {
            if (
                is_array($entry) ||
                is_object($entry)
            ) {
                continue;
            }

            $entry = trim(
                (string) $entry
            );

            if ($entry === '') {
                continue;
            }

            if (
                !in_array(
                    $entry,
                    $normalized,
                    true
                )
            ) {
                $normalized[] = $entry;
            }
        }

        return $normalized;
    }

    private function normalizeStringFilter(
        $value
    ) {
        if (
            $value === null ||
            $value === ''
        ) {
            return null;
        }

        if (is_array($value)) {
            if (count($value) !== 1) {
                throw new \InvalidArgumentException(
                    'String filter must contain exactly one value.'
                );
            }

            $value = reset(
                $value
            );
        }

        if (is_object($value)) {
            throw new \InvalidArgumentException(
                'String filter must be a scalar value.'
            );
        }

        $value = trim(
            (string) $value
        );

        if ($value === '') {
            return null;
        }

        return $value;
    }

    private function normalizePage(
        $value
    ): int {
        $value = (int) $value;

        if ($value > 0) {
            return $value;
        }

        return 1;
    }

    private function normalizeItemsPerPage(
        $value
    ): int {
        $value = (int) $value;

        if ($value <= 0) {
            return 50;
        }

        if ($value > 100) {
            return 100;
        }

        return $value;
    }

    private function normalizeSortBy(
        $value
    ): string {
        $allowed = [
            'createdAt',
            'id',
            'integration',
            'identifier',
            'level',
            'code'
        ];

        $value = trim(
            (string) $value
        );

        if (
            in_array(
                $value,
                $allowed,
                true
            )
        ) {
            return $value;
        }

        return 'createdAt';
    }

    private function normalizeSortOrder(
        $value
    ): string {
        $value = strtolower(
            trim(
                (string) $value
            )
        );

        if ($value === 'asc') {
            return 'asc';
        }

        return 'desc';
    }

    private function jsonResponse(
        array $data,
        int $status = 200
    ): Response {
        return $this->response->make(
            json_encode(
                $data
            ),
            $status,
            [
                'Content-Type' =>
                    'application/json; charset=UTF-8'
            ]
        );
    }

    private function errorResponse(
        \Throwable $exception
    ): Response {
        return $this->jsonResponse(
            [
                'ok' => false,
                'error' => get_class(
                    $exception
                ),
                'message' => $exception->getMessage()
            ],
            500
        );
    }
}
