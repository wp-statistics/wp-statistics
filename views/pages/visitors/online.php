<?php

use WP_Statistics\Components\View;

?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <?php
                $args = [
                    'data'       => ['test', 'test'],
                    'pagination' => isset($pagination) ? $pagination : null
                ];
                View::load("components/tables/visitors", $args);
                ?>
            </div>
        </div>
    </div>
</div>