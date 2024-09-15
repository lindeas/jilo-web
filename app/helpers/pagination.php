
                        <div class="text-center">
                            <div class="pagination">
<?php
    $param = '';
    if (isset($_GET['id'])) {
        $param .= '&id=' . $_GET['id'];
    }
    if (isset($_GET['name'])) {
        $param .= '&name=' . $_GET['name'];
    }
    if (isset($_GET['ip'])) {
        $param .= '&ip=' . $_GET['ip'];
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
