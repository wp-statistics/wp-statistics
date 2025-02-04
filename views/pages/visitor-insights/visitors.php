<?php
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\ModalHandler\Modal;
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <?php
                $args = [
                    'data'       => $data['data'],
                    'pagination' => isset($pagination) ? $pagination : null
                ];
                View::load("components/tables/visitors", $args);
                ?>
            </div>
        </div>
    </div>
</div>
<?php Modal::render('delete-visitor-record'); ?>
<?php Modal::render('add-to-exclusions-list'); ?>