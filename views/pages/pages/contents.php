<?php

use WP_Statistics\Components\View;

?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <?php
                $args = [
                    'data'       => $data['posts'],
                    'pagination' => $pagination
                ];
                View::load("components/tables/content-report", $args);
                ?>
            </div>
        </div>
    </div>
</div>
