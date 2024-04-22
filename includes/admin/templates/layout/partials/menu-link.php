<?php
$class = '';
if (isset($_GET['page']) && $_GET['page'] === $slug) {
    $class = 'active';
}

$href = esc_url(admin_url('admin.php?page=' . $slug));

$badge_count_html = '';
if ($badge_count !== null) {
    $badge_count_html = '<span class="badge"><span>' . esc_html($badge_count) . '</span></span>';
}
?>

<a class="<?php echo esc_attr($icon_class) . ' ' . esc_attr($class); ?> " href="<?php echo esc_url($href); ?>">
    <span class="icon"></span><span><?php echo esc_html($link_text) ?></span><?php echo $badge_count_html; ?>
</a>
