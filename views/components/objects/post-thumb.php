<?php
/**
 * One post picture, the same everywhere (Top Contents lists, Top Pages table, the single content header):
 * the featured image in WordPress's 'thumbnail' size, or a colour tile with two initials when the post
 * has no image (or its file is gone). Only the size and corners change per place, through `class`.
 *
 * Args (either a post, or the prepared data from LeaderboardPresenter::thumb() / contentItems()):
 *  - post_id  (int)    Post ID; the picture data is looked up when `thumb` is not given.
 *  - title    (string) Post title (initials come from it).
 *  - thumb    (string) Image URL, '' for the tile. Optional.
 *  - initials (string) Tile text. Optional.
 *  - color    (string) Tile colour. Optional.
 *  - size     (int)    width/height attributes of the image. Default 40.
 *  - class    (string) Extra classes for the place's size and corners.
 *  - alt      (string) Image alt text. Default '' (decorative: the title sits next to it).
 */

use WP_Statistics\Service\Admin\Leaderboard\LeaderboardPresenter;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!isset($thumb)) {
    $data     = LeaderboardPresenter::thumb($post_id ?? 0, $title ?? '');
    $thumb    = $data['thumb'];
    $initials = $initials ?? $data['initials'];
    $color    = $color ?? $data['color'];
}

$size  = !empty($size) ? (int) $size : 40;
$class = trim('wps-post-thumb ' . ($class ?? ''));
$alt   = isset($alt) ? (string) $alt : '';
// Colour, white text and display inline with !important: table link rules force dark text and inline-block on spans inside links.

if (!empty($thumb)) : ?>
    <img class="<?php echo esc_attr($class); ?>" src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($alt); ?>" width="<?php echo esc_attr($size); ?>" height="<?php echo esc_attr($size); ?>" loading="lazy" decoding="async">
<?php else : ?>
    <span class="<?php echo esc_attr($class . ' wps-post-thumb--tile'); ?>" style="background-color:<?php echo esc_attr($color ?? '#404BF2'); ?>;color:#fff !important;display:inline-flex !important" aria-hidden="true"><?php echo esc_html($initials ?? '#'); ?></span>
<?php endif;
