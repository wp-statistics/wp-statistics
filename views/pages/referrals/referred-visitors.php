<?php

use WP_Statistics\Components\View;
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <?php
                    $args = [
                        'visitors'   => $data['visitors'],
                        'pagination' => $pagination ?? null
                    ];
                    View::load("components/tables/referred-visitors", $args);
                ?>
            </div>
        </div>
    </div>
</div>