<?php
$class = '';
if (isset($_GET['page'])) {
    $full_slug = $_GET['page'];
    if (isset($_GET['tab'])) {
        $full_slug .= '&tab=' . $_GET['tab'];
    }

    if ($slug === $full_slug) {
        $class = 'active';
    }
} else {
    if ($_GET['page'] === $slug) {
        $class = 'active';
    }
}

$href = esc_url(admin_url('admin.php?page=' . $slug));

$badge_count_html = '';
if ($badge_count !== null) {
    $badge_count_html = '<span class="badge"><span>' . esc_html($badge_count) . '</span></span>';
}

// Check if there's a submenu
$has_submenu = !empty($sub_menu);
?>

<div class="wps-admin-header__menu-item">
    <a class="<?php echo esc_attr($icon_class) . ' ' . esc_attr($class) . ' ' . ($has_submenu ? 'wps-admin-header__link--has-dropdown' : ''); ?>" href="<?php echo $has_submenu ? '#' : esc_url($href); ?>">
        <span class="icon"></span><span><?php echo esc_html($link_text) ?></span><?php echo $badge_count_html; ?>
    </a>

    <?php if ($has_submenu) : ?>
        <ul class="wps-admin-header__dropdown-menu">
            <?php foreach ($sub_menu as $sub_item) : ?>
                <li>
                    <a class="<?php echo isset($_GET['page']) && $_GET['page'] === $sub_item['slug'] ? 'active' : '' ?>" href="<?php echo esc_url(admin_url('admin.php?page=' . $sub_item['slug'])); ?>">
                        <span class="icon <?php echo esc_attr($sub_item['icon_class']) ?>"></span>
                        <span><?php echo esc_html($sub_item['link_text']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
