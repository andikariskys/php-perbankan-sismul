<?php
/**
 * Render Bootstrap 5 pagination.
 *
 * @param int    $total_rows   Total baris dari query
 * @param int    $per_page     Jumlah item per halaman
 * @param int    $current_page Halaman aktif saat ini
 * @param string $base_url     URL dasar tanpa parameter page (mis: "index.php?search=x&")
 */
function renderPagination(int $total_rows, int $per_page, int $current_page, string $base_url): void
{
    $total_pages = (int) ceil($total_rows / $per_page);
    if ($total_pages <= 1) return;

    echo '<nav aria-label="Pagination"><ul class="pagination pagination-sm justify-content-end mb-0">';

    // Prev
    $prev = $current_page - 1;
    $disabled = $current_page <= 1 ? 'disabled' : '';
    echo "<li class=\"page-item $disabled\"><a class=\"page-link\" href=\"{$base_url}page=$prev\">‹</a></li>";

    for ($i = 1; $i <= $total_pages; $i++) {
        $active = $i === $current_page ? 'active' : '';
        echo "<li class=\"page-item $active\"><a class=\"page-link\" href=\"{$base_url}page=$i\">$i</a></li>";
    }

    // Next
    $next = $current_page + 1;
    $disabled = $current_page >= $total_pages ? 'disabled' : '';
    echo "<li class=\"page-item $disabled\"><a class=\"page-link\" href=\"{$base_url}page=$next\">›</a></li>";

    echo '</ul></nav>';
}
