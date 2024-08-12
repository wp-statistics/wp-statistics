<?php

use WP_Statistics\Components\View;

?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <?php
                $args = [
                    'data'       => $data['authors'],
                    'pagination' => $pagination
                ];
                View::load("components/tables/author-pages", $args);
                ?>
            </div>
        </div>
    </div>
</div>