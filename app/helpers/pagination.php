
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
    if ($browse_page > 1) {
        echo '<span><a href="' . $url . '&p=1">first</a></span>';
    } else {
        echo '<span>first</span>';
    }

    for ($i = 1; $i <= $page_count; $i++) {
        if ($i === $browse_page) {
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
            echo '<span><a href="' . $app_root . '?platform=' . $platform_id . '&page=' . $page . $param . '&p=' . $i . '">[' . $i . ']</a></span>';
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
