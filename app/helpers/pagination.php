
                        <div class="text-center">
                            <div class="pagination">
<?php
    $param = '';
    if (isset($_REQUEST['id'])) {
        $param .= '&id=' . htmlspecialchars($_REQUEST['id']);
    }
    if (isset($_REQUEST['name'])) {
        $param .= '&name=' . htmlspecialchars($_REQUEST['name']);
    }
    if (isset($_REQUEST['ip'])) {
        $param .= '&ip=' . htmlspecialchars($_REQUEST['ip']);
    }
    if (isset($_REQUEST['event'])) {
        $param .= '&event=' . htmlspecialchars($_REQUEST['event']);
    }
    if (isset($_REQUEST['from_time'])) {
        $param .= '&from_time=' . htmlspecialchars($from_time);
    }
    if (isset($_REQUEST['until_time'])) {
        $param .= '&until_time=' . htmlspecialchars($until_time);
    }

    $max_visible_pages = 10;
    $step_pages = 10;

    if ($browse_page > 1) {
        echo '<span><a href="' . htmlspecialchars($url) . '&p=1">first</a></span>';
    } else {
        echo '<span>first</span>';
    }

    for ($i = 1; $i <= $page_count; $i++) {
        // always show the first, last, step pages (10, 20, 30, etc.),
        // and the pages close to the current one
        if (
            $i === 1 || // first page
            $i === $page_count || // last page
            $i === $browse_page || // current page
            $i === $browse_page -1 ||
            $i === $browse_page +1 ||
            $i === $browse_page -2 ||
            $i === $browse_page +2 ||
            ($i % $step_pages === 0 && $i > $max_visible_pages) // the step pages - 10, 20, etc.
        ) {
            if ($i === $browse_page) {
                // current page, no link
                if ($browse_page > 1) {
                    echo '<span><a href="' . htmlspecialchars($app_root) . '?platform=' . htmlspecialchars($platform_id) . '&page=' . htmlspecialchars($page) . $param . '&p=' . (htmlspecialchars($browse_page) -1) . '"><<</a></span>';
                } else {
                    echo '<span><<</span>';
                }
                echo '[' . htmlspecialchars($i) . ']';

                if ($browse_page < $page_count) {
                    echo '<span><a href="' . htmlspecialchars($app_root) . '?platform=' . htmlspecialchars($platform_id) . '&page=' . htmlspecialchars($page) . $param . '&p=' . (htmlspecialchars($browse_page) +1) . '">>></a></span>';
                } else {
                    echo '<span>>></span>';
                }
            } else {
                // other pages
                echo '<span><a href="' . htmlspecialchars($app_root) . '?platform=' . htmlspecialchars($platform_id) . '&page=' . htmlspecialchars($page) . $param . '&p=' . htmlspecialchars($i) . '">[' . htmlspecialchars($i) . ']</a></span>';
            }
        // show ellipses between distant pages
        } elseif (
            $i === $browse_page -3 ||
            $i === $browse_page +3
        ) {
            echo '<span>...</span>';
        }
    }

    if ($browse_page < $page_count) {
        echo '<span><a href="' . htmlspecialchars($app_root) . '?platform=' . htmlspecialchars($platform_id) . '&page=' . htmlspecialchars($page) . $param . '&p=' . (htmlspecialchars($page_count)) . '">last</a></span>';
    } else {
        echo '<span>last</span>';
    }
?>
                            </div>
                        </div>
