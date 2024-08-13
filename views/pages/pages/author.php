<?php
use WP_Statistics\Components\View;
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <?php
                    View::load("components/tables/author-pages", [
                        'data'       => $data['authors'],
                        'pagination' => $pagination
                    ]);
                ?>
            </div>
        </div>
    </div>
</div>