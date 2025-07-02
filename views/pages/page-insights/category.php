<?php

use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;

$taxonomy = Request::get('tx', 'category');

if ($showLockedPage) :
    View::load("components/locked-page", [
        'campaign' => 'pages',
        'src'      => 'assets/images/locked/category-pages.jpg',
    ]);
else: ?>
    <div class="postbox-container wps-postbox-full">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
                    <?php
                        View::load("components/tables/category-pages", [
                            'data'       => $data['categories'][$taxonomy],
                            'pagination' => $pagination
                        ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

