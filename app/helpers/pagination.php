
                        <div class="text-center">
                            <div class="pagination">
<?php
    $param = '';
    if (isset($_REQUEST['id'])) {
        $param .= '&id=' . $_REQUEST['id'];
    }
    if (isset($_REQUEST['name'])) {
        $param .= '&name=' . $_REQUEST['name'];
    }
    if (isset($_REQUEST['ip'])) {
        $param .= '&ip=' . $_REQUEST['ip'];
    }
    if (isset($_REQUEST['event'])) {
        $param .= '&event=' . $_REQUEST['event'];
    }

    $max_visible_pages = 10;
    $step_pages = 10;

    if ($browse_page > 1) {
        echo '<span><a href="' . $url . '&p=1">first</a></span>';
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
                    echo '<span><a href="' . $app_root . '?platform=' . $platform_id . '&page=' . $page . $param . '&p=' . ($browse_page -1) . '"><<</a></span>';
                } else {
                    echo '<span><<</span>';
                }
                echo '[' . $i . ']';

                if ($browse_page < $page_count) {
                    echo '<span><a href="' . $app_root . '?platform=' . $platform_id . '&page=' . $page . $param . '&p=' . ($browse_page +1) . '">>></a></span>';
                } else {
                    echo '<span>>></span>';
                }
            } else {
                // other pages
                echo '<span><a href="' . $app_root . '?platform=' . $platform_id . '&page=' . $page . $param . '&p=' . $i . '">[' . $i . ']</a></span>';
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
        echo '<span><a href="' . $app_root . '?platform=' . $platform_id . '&page=' . $page . $param . '&p=' . ($page_count) . '">last</a></span>';
    } else {
        echo '<span>last</span>';
    }
?>
                            </div>
                        </div>
