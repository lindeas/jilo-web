<?php
/**
 * Pagination helper
 * @param string $url Base URL for pagination links
 * @param int $browse_page Current page number
 * @param int $page_count Total number of pages
 */
function renderPagination($url, $browse_page = 1, $page_count = 1) {
    $param = '';
    // calls
    if (isset($_REQUEST['name'])) {
        $param .= '&name=' . htmlspecialchars($_REQUEST['name']);
    }
    if (isset($_REQUEST['invitees'])) {
        $param .= '&invitees=' . htmlspecialchars($_REQUEST['invitees']);
    }
    if (isset($_REQUEST['description'])) {
        $param .= '&description=' . htmlspecialchars($_REQUEST['description']);
    }
    if (isset($_REQUEST['filter'])) {
        $param .= '&filter=' . htmlspecialchars($_REQUEST['filter']);
    }
    // contacts
    if (isset($_REQUEST['name'])) {
        $param .= '&name=' . htmlspecialchars($_REQUEST['name']);
    }
    if (isset($_REQUEST['phone'])) {
        $param .= '&phone=' . htmlspecialchars($_REQUEST['phone']);
    }
    if (isset($_REQUEST['email'])) {
        $param .= '&email=' . htmlspecialchars($_REQUEST['email']);
    }
    // messages
    if (isset($_REQUEST['from'])) {
        $param .= '&from=' . htmlspecialchars($_REQUEST['from']);
    }
    if (isset($_REQUEST['to'])) {
        $param .= '&to=' . htmlspecialchars($_REQUEST['to']);
    }
    if (isset($_REQUEST['subject'])) {
        $param .= '&subject=' . htmlspecialchars($_REQUEST['subject']);
    }
    // notifications
    if (isset($_REQUEST['message'])) {
        $param .= '&message=' . htmlspecialchars($_REQUEST['message']);
    }
    // time period
    if (isset($_REQUEST['from_time'])) {
        $param .= '&from_time=' . htmlspecialchars($_REQUEST['from_time']);
    }
    if (isset($_REQUEST['until_time'])) {
        $param .= '&until_time=' . htmlspecialchars($_REQUEST['until_time']);
    }

    $max_visible_pages = 10;
    $step_pages = 10;

    echo '<div class="tm-pagination text-center"><div class="pagination">';

    if ($browse_page > 1) {
        echo '<a class="pagination-link" href="' . htmlspecialchars($url) . '&p=1' . $param . '">first</a>';
        echo '<a class="pagination-link" href="' . htmlspecialchars($url) . '&p=' . ($browse_page - 1) . $param . '">&laquo;</a>';
    } else {
        echo '<span class="pagination-link disabled">first</span>';
        echo '<span class="pagination-link disabled">&laquo;</span>';
    }

    for ($i = 1; $i <= $page_count; $i++) {
        // always show the first, last, step pages (10, 20, 30, etc.),
        // and pages around current page
        if ($i == 1 || $i == $page_count ||
            $i % $step_pages == 0 ||
            abs($i - $browse_page) < $max_visible_pages / 2) {

            if ($i == $browse_page) {
                echo '<span class="pagination-link active">' . $i . '</span>';
            } else {
                echo '<a class="pagination-link" href="' . htmlspecialchars($url) . '&p=' . $i . $param . '">' . $i . '</a>';
            }
        } elseif ($i == 2 || $i == $page_count - 1 ||
                  ($i > $browse_page + $max_visible_pages / 2 && $i % $step_pages == 1) ||
                  ($i < $browse_page - $max_visible_pages / 2 && $i % $step_pages == $step_pages - 1)) {
            echo '<span class="pagination-link pagination-ellipsis disabled">...</span>';
        }
    }

    if ($browse_page < $page_count) {
        echo '<a class="pagination-link" href="' . htmlspecialchars($url) . '&p=' . ($browse_page + 1) . $param . '">&raquo;</a>';
        echo '<a class="pagination-link" href="' . htmlspecialchars($url) . '&p=' . $page_count . $param . '">last</a>';
    } else {
        echo '<span class="pagination-link disabled">&raquo;</span>';
        echo '<span class="pagination-link disabled">last</span>';
    }

    echo '</div></div>';
}
