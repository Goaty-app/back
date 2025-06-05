<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class PaginationService
{
    private const DEFAULT_PAGE_SIZE = 10;
    private const MAX_PAGE_SIZE = 50;

    private ?string $baseUrl = null;

    /**
     * Creates a paginated data structure.
     */
    public function paginate(string $jsonData, Request $request, ?string $baseUrl = null): array
    {
        $this->baseUrl = $request->getSchemeAndHttpHost().$request->getPathInfo();

        $data = json_decode($jsonData, true);

        if (!\is_array($data) || empty($data)) {
            return $this->emptyPaginatedResponse($request);
        }

        [$pageNumber, $pageSize] = $this->getPaginationParameters($request);

        $totalRecordCount = \count($data);
        $totalPages = (int) ceil($totalRecordCount / $pageSize);

        if ($pageNumber > $totalPages && $totalPages > 0) {
            $pageNumber = $totalPages;
        }

        $offset = ($pageNumber - 1) * $pageSize;
        $paginatedData = \array_slice($data, $offset, $pageSize);

        $response = [
            'page_number'        => $pageNumber,
            'page_size'          => $pageSize,
            'total_record_count' => $totalRecordCount,
            'total_pages'        => $totalPages,
            'records'            => $paginatedData,
            'links'              => $this->generateLinks($pageNumber, $pageSize, $totalPages, $request),
        ];

        return $response;
    }

    /**
     * Retrieves pagination parameters from the request with validation.
     */
    private function getPaginationParameters(Request $request): array
    {
        $pageNumber = max(
            1,
            (int) $request->query->get('page', 1),
        );
        $pageSize = max(
            1,
            min(
                self::MAX_PAGE_SIZE,
                (int) $request->query->get('page_size', self::DEFAULT_PAGE_SIZE),
            ),
        );

        return [$pageNumber, $pageSize];
    }

    /**
     * Generates HATEOAS pagination links.
     */
    private function generateLinks(int $currentPage, int $pageSize, int $totalPages, Request $request): array
    {
        return [
            'first' => $this->buildLink(1, $pageSize, $request),
            'last'  => $totalPages > 0 ? $this->buildLink($totalPages, $pageSize, $request) : null,
            'prev'  => $currentPage > 1 ? $this->buildLink($currentPage - 1, $pageSize, $request) : null,
            'next'  => $currentPage < $totalPages ? $this->buildLink($currentPage + 1, $pageSize, $request) : null,
        ];
    }

    /**
     * Builds a complete pagination link URL.
     */
    private function buildLink(int $page, int $pageSize, Request $request): string
    {
        $queryString = http_build_query([
            ...$request->query->all(),
            'page'      => $page,
            'page_size' => $pageSize,
        ]);

        return "{$this->baseUrl}?{$queryString}";
    }

    /**
     * Returns an empty paginated data structure.
     */
    private function emptyPaginatedResponse(Request $request): array
    {
        return [
            'page_number'        => 1,
            'page_size'          => self::DEFAULT_PAGE_SIZE,
            'total_record_count' => 0,
            'total_pages'        => 0,
            'records'            => [],
            'links'              => $this->generateLinks(1, self::DEFAULT_PAGE_SIZE, 0, $request),
        ];
    }
}
