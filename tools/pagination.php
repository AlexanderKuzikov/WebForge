<?php
/**
 * tools/pagination.php — порт usePagination из Storefront UI
 * (внутри — логика jw-paginate) в чистый PHP для data-driven
 * страниц WebForge (programmatic SEO). Ноль зависимостей.
 *
 * Возвращает те же поля: totalPages/selectedPage/startPage/endPage/pages.
 */
function webforge_paginate(int $totalItems, int $currentPage = 1, int $pageSize = 24, int $maxVisible = 5): array
{
    $totalPages = max(1, (int)ceil($totalItems / max(1, $pageSize)));
    $currentPage = min(max(1, $currentPage), $totalPages);
    if ($totalPages <= $maxVisible) {
        $start = 1; $end = $totalPages;
    } elseif ($currentPage <= (int)ceil($maxVisible / 2)) {
        $start = 1; $end = $maxVisible;
    } elseif ($currentPage + (int)floor($maxVisible / 2) >= $totalPages) {
        $start = $totalPages - $maxVisible + 1; $end = $totalPages;
    } else {
        $start = $currentPage - (int)floor($maxVisible / 2);
        $end = $start + $maxVisible - 1;
    }
    return [
        'totalPages' => $totalPages,
        'selectedPage' => $currentPage,
        'startPage' => $start,
        'endPage' => $end,
        'pages' => range($start, $end),
    ];
}

// self-check: 150 товаров, стр. 2 → 7 страниц, окно 1–5
if (PHP_SAPI === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
    $r = webforge_paginate(150, 2, 24, 5);
    assert($r['totalPages'] === 7 && $r['pages'] === [1, 2, 3, 4, 5]);
    echo "pagination OK: " . json_encode($r, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
