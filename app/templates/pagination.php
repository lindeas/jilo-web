<?php
/**
 * Reusable pagination view/template component
 * Required variables:
 * $currentPage - Current page number
 * $totalPages - Total number of pages
 */

// Validate required pagination variables
if (!isset($currentPage) || !isset($totalPages)) {
    return;
}

// Ensure valid values
$currentPage = max(1, min($currentPage, $totalPages));

// Number of page links to show before and after current page
$range = 2;
?>

<?php if ($totalPages > 1): ?>
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center d-flex flex-row gap-1">
        <!-- First page -->
<?php if ($currentPage > 1): ?>
        <li class="page-item">
            <a class="page-link" href="<?= htmlspecialchars($app_root . '?page=' . $page . $params) ?>">First</a>
        </li>
        <li class="page-item">
            <a class="page-link" href="<?= htmlspecialchars($app_root . '?page=' . $page . ($currentPage > 1 ? '&page_num=' . ($currentPage - 1) : '') . $params) ?>">«</a>
        </li>
<?php else: ?>
        <li class="page-item disabled">
            <span class="page-link">First</span>
        </li>
        <li class="page-item disabled">
            <span class="page-link">«</span>
        </li>
<?php endif; ?>
        <!-- Page numbers -->
<?php
        for ($i = 1; $i <= $totalPages; $i++) {
            // Show first, last, current page, 2 pages before and after current, and step pages (10, 20, etc.)
            if ($i === 1 ||
                $i === $totalPages ||
                $i === $currentPage ||
                $i === $currentPage - 1 ||
                $i === $currentPage + 1 ||
                $i === $currentPage - 2 ||
                $i === $currentPage + 2 ||
                ($i % 10 === 0 && $i > 10)
                ) { ?>
        <li class="page-item <?= $i === (int)$currentPage ? 'active' : '' ?>">
            <a class="page-link" href="<?= htmlspecialchars($app_root . '?page=' . $page . ($i > 1 ? '&page_num=' . $i : '') . $params) ?>"><?= $i ?></a>
        </li>
<?php  } elseif ($i === $currentPage - 3 || $i === $currentPage + 3) { ?>
        <li class="page-item disabled">
            <span class="page-link">...</span>
        </li>
<?php  } ?>
<?php } ?>
        <!-- Last page -->
<?php if ($currentPage < $totalPages): ?>
        <li class="page-item">
            <a class="page-link" href="<?= htmlspecialchars($app_root . '?page=' . $page . '&page_num=' . ($currentPage + 1) . $params) ?>">»</a>
        </li>
        <li class="page-item">
            <a class="page-link" href="<?= htmlspecialchars($app_root . '?page=' . $page . '&page_num=' . $totalPages . $params) ?>">Last</a>
        </li>
<?php else: ?>
        <li class="page-item disabled">
            <span class="page-link">»</span>
        </li>
        <li class="page-item disabled">
            <span class="page-link">Last</span>
        </li>
<?php endif; ?>
    </ul>
</nav>

<?php endif; ?>
