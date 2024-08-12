<?php

use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;

$taxonomy = Request::get('tx', 'category');

if ($showLockedPage) :
    $locked_args = [
        'campaign' => 'pages',
        'src'      => 'assets/images/locked/category-pages.jpg',
    ];
    View::load("components/locked-page", $locked_args);
else: ?>
    <div class="postbox-container wps-postbox-full">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <?php
                    $args = [
                        'data'       => $data['categories'][$taxonomy],
                        'pagination' => $pagination
                    ];
                    View::load("components/tables/category-pages", $args);
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

