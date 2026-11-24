<?php
/**
 * Leaderboard table: rank, name, Views (default ranking), Posts (published in the range), Per post
 * (views / posts published in the range) and the optional Comments column. Same numbers as the
 * Authors and Categories reports.
 *
 * Ranked on the server: with `rankings` (one list per sortable metric and direction, LeaderboardDataProvider),
 * a click on the Views, Posts, Per post or Comments header shows that metric's own top rows, a second click
 * its bottom rows (one <tbody data-ranking data-direction> each, assets/dev/javascript/components/leaderboard.js).
 * Without `rankings` (older callers) the header re-orders the shown rows in the browser, both ways.
 * Under ~520px the table scrolls inside its card.
 *
 * Args:
 *  - rows       (array[]) Leaderboard::buildRows() rows, ranked by views. Optional per row:
 *                         'url' (report link; no link when empty), 'avatar' (image URL),
 *                         'title' (hover text for the name).
 *  - rankings   (array[]) Optional. 'descending' and 'ascending' => metric (Leaderboard::SORTABLE) => rows
 *                         ranked by that metric (LeaderboardPresenter::authorRankings()). A plain
 *                         metric => rows array is read as the descending lists.
 *  - columns    (bool[])  Leaderboard::columns() flags.
 *  - name_label (string)  Header of the name column, e.g. "Author".
 *  - caption    (string)  Accessible table name, e.g. the card title.
 *  - media      (string)  'avatar' (initial on a colour circle, avatar image on top when it loads) or 'none'.
 *  - note       (string)  Optional line under the table, e.g. "0 posts published in this range".
 *  - empty      (array)   Args for components/objects/empty-state when there are no rows.
 *
 * Example:
 *  View::load('components/leaderboard', [
 *      'rows'       => $data['rows'],
 *      'rankings'   => LeaderboardPresenter::authorRankings($data),
 *      'columns'    => $data['columns'],
 *      'name_label' => __('Author', 'wp-statistics'),
 *      'caption'    => __('Top Authors', 'wp-statistics'),
 *      'media'      => 'avatar',
 *  ]);
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Helper;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\Leaderboard\Leaderboard;

$rows     = isset($rows) && is_array($rows) ? $rows : [];
$rankings = isset($rankings) && is_array($rankings) ? $rankings : [];
if (!empty($rankings) && !isset($rankings['descending']) && !isset($rankings['ascending'])) {
    $rankings = ['descending' => $rankings];
}

// direction => metric => rows, known metrics only.
$lists = [];
foreach (Leaderboard::DIRECTIONS as $direction) {
    if (!empty($rankings[$direction]) && is_array($rankings[$direction])) {
        $lists[$direction] = array_intersect_key($rankings[$direction], array_flip(Leaderboard::SORTABLE));
    }
}

$serverSort = !empty($lists['descending']);
if ($serverSort) {
    $rows = isset($lists['descending']['views']) ? $lists['descending']['views'] : reset($lists['descending']);
} else {
    $lists = ['descending' => ['views' => $rows]];
}
$columns    = isset($columns) && is_array($columns) ? $columns : Leaderboard::columns($rows);
$name_label = !empty($name_label) ? $name_label : __('Name', 'wp-statistics');
$caption    = !empty($caption) ? $caption : '';
$media      = isset($media) && $media === 'avatar' ? 'avatar' : 'none';
$note       = isset($note) ? (string) $note : '';
$empty      = isset($empty) && is_array($empty) ? $empty : [];

if (empty($rows)) {
    View::load('components/objects/empty-state', $empty);

    if ($note !== '') {
        echo '<p class="wps-leaderboard__note">' . esc_html($note) . '</p>';
    }

    return;
}

// Short header on screen (one line in a narrow card), the full meaning in the tooltip and for screen readers.
$numberColumns = [
    'views'            => __('Views', 'wp-statistics'),
    /* translators: Short leaderboard column header; the full meaning is "Posts published in this range". */
    'published'        => _x('Posts', 'leaderboard column: posts published in the range', 'wp-statistics'),
    /* translators: Short leaderboard column header; the full meaning is "Posts with views". */
    'posts_with_views' => _x('Posts', 'leaderboard column: posts with views', 'wp-statistics'),
    /* translators: Short leaderboard column header; the full meaning is "Views per post published in this range". */
    'views_per_post'   => _x('Per post', 'leaderboard column: views per post', 'wp-statistics'),
    'comments'         => __('Comments', 'wp-statistics'),
];

$columnHints = [
    'published'        => __('Posts published in this range', 'wp-statistics'),
    'posts_with_views' => __('Posts with views', 'wp-statistics'),
    'views_per_post'   => __('Views per post published in this range', 'wp-statistics'),
];

$numberColumns = array_filter($numberColumns, function ($key) use ($columns) {
    return !empty($columns[$key]);
}, ARRAY_FILTER_USE_KEY);

$tagLabels = [
    'lang'   => __('Language', 'wp-statistics'),
    'parent' => __('Parent', 'wp-statistics'),
    'slug'   => __('Slug', 'wp-statistics'),
];
?>
<div class="wps-leaderboard" data-wps-leaderboard>
    <div class="wps-leaderboard__scroll"<?php if ($caption) : ?> role="region" tabindex="0" aria-label="<?php echo esc_attr($caption); ?>"<?php endif; ?>>
        <table class="wps-leaderboard__table<?php echo count($numberColumns) <= 3 ? ' wps-leaderboard__table--compact' : ''; ?>">
            <?php if ($caption) : ?>
                <caption class="screen-reader-text"><?php echo esc_html($caption); ?></caption>
            <?php endif; ?>
            <thead>
            <tr>
                <th scope="col" class="wps-leaderboard__rank"><span aria-hidden="true">#</span><span class="screen-reader-text"><?php esc_html_e('Rank', 'wp-statistics'); ?></span></th>
                <th scope="col" class="wps-leaderboard__name"><?php echo esc_html($name_label); ?></th>
                <?php foreach ($numberColumns as $key => $label) :
                    // Server-ranked tables sort only by the metrics that have their own ranking.
                    $sortable = !$serverSort || isset($lists['descending'][$key]);
                    ?>
                    <th scope="col" class="wps-leaderboard__num"<?php echo $key === 'views' ? ' aria-sort="descending"' : ''; ?>>
                        <?php if ($sortable) : ?><button type="button" class="wps-leaderboard__sort" data-sort-key="<?php echo esc_attr($key); ?>"<?php echo isset($columnHints[$key]) ? ' title="' . esc_attr($columnHints[$key]) . '"' : ''; ?>><?php else : ?><span class="wps-leaderboard__head"<?php echo isset($columnHints[$key]) ? ' title="' . esc_attr($columnHints[$key]) . '"' : ''; ?>><?php endif; ?>
                            <?php if (isset($columnHints[$key])) : ?>
                                <abbr class="wps-leaderboard__label" aria-hidden="true"><?php echo esc_html($label); ?></abbr><span class="screen-reader-text"><?php echo esc_html($columnHints[$key]); ?></span>
                            <?php else : ?>
                                <span class="wps-leaderboard__label"><?php echo esc_html($label); ?></span>
                            <?php endif; ?>
                            <?php if ($sortable) : ?><span class="wps-leaderboard__arrow" aria-hidden="true"></span>
                        </button><?php else : ?></span><?php endif; ?>
                    </th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <?php foreach ($lists as $direction => $directionRankings) : foreach ($directionRankings as $ranking => $rankingRows) : ?>
            <tbody<?php if ($serverSort) : ?> data-ranking="<?php echo esc_attr($ranking); ?>" data-direction="<?php echo esc_attr($direction); ?>"<?php echo ($ranking !== 'views' || $direction !== 'descending') ? ' hidden' : ''; ?><?php endif; ?>>
            <?php foreach ((array) $rankingRows as $index => $row) :
                $name    = $row['name'] !== '' ? $row['name'] : __('(no name)', 'wp-statistics');
                $tag     = !empty($row['tag']['text']) ? $row['tag'] : null;
                $url     = !empty($row['url']) ? $row['url'] : '';
                $avatar  = !empty($row['avatar']) ? $row['avatar'] : '';
                $initial = Leaderboard::initials($name, 1);
                $color   = Leaderboard::color(!empty($row['id']) ? $row['id'] : $name);
                ?>
                <tr data-rank="<?php echo esc_attr($index + 1); ?>"
                    data-views="<?php echo esc_attr((int) $row['views']); ?>"
                    data-posts_with_views="<?php echo esc_attr((int) ($row['posts_with_views'] ?? 0)); ?>"
                    data-views_per_post="<?php echo esc_attr((float) $row['views_per_post']); ?>"
                    data-published="<?php echo esc_attr((int) $row['published']); ?>"
                    data-comments="<?php echo esc_attr((int) $row['comments']); ?>">
                    <td class="wps-leaderboard__rank"><?php echo esc_html(number_format_i18n($index + 1)); ?></td>
                    <th scope="row" class="wps-leaderboard__name">
                        <span class="wps-leaderboard__who">
                            <?php if ($media === 'avatar') : ?>
                                <span class="wps-leaderboard__avatar" style="background-color:<?php echo esc_attr($color); ?>" aria-hidden="true">
                                    <span class="wps-leaderboard__initial"><?php echo esc_html($initial); ?></span>
                                    <?php if ($avatar) : ?>
                                        <img src="<?php echo esc_url($avatar); ?>" alt="" width="24" height="24" loading="lazy" decoding="async">
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($tag && $tag['type'] === 'parent') : ?>
                                <span class="wps-leaderboard__parent" title="<?php echo esc_attr($tagLabels['parent']); ?>"><?php echo esc_html($tag['text'] . ' ›'); ?></span>
                            <?php endif; ?>
                            <?php if ($url) : ?>
                                <a class="wps-leaderboard__link" href="<?php echo esc_url($url); ?>" title="<?php echo esc_attr(!empty($row['title']) ? $row['title'] : $name); ?>"><span class="wps-ellipsis-text"><?php echo esc_html($name); ?></span></a>
                            <?php else : ?>
                                <span class="wps-leaderboard__text" title="<?php echo esc_attr(!empty($row['title']) ? $row['title'] : $name); ?>"><span class="wps-ellipsis-text"><?php echo esc_html($name); ?></span></span>
                            <?php endif; ?>
                            <?php if ($tag && $tag['type'] !== 'parent') : ?>
                                <span class="wps-leaderboard__tag wps-leaderboard__tag--<?php echo esc_attr($tag['type']); ?>" title="<?php echo esc_attr($tagLabels[$tag['type']] . ': ' . $tag['text']); ?>"><span class="screen-reader-text"><?php echo esc_html($tagLabels[$tag['type']] . ': '); ?></span><?php echo esc_html($tag['text']); ?></span>
                            <?php endif; ?>
                        </span>
                    </th>
                    <?php foreach ($numberColumns as $key => $label) :
                        $value = $row[$key] ?? 0;

                        if ($key === 'views_per_post') {
                            $text  = Leaderboard::formatAverage($value);
                            $exact = $text;
                        } else {
                            $text  = Helper::formatNumberWithUnit((int) $value, 1);
                            $exact = number_format_i18n((int) $value);
                        }
                        ?>
                        <td class="wps-leaderboard__num"<?php echo ((string) $text !== $exact) ? ' title="' . esc_attr($exact) . '"' : ''; ?>><?php echo esc_html($text); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <?php endforeach; endforeach; ?>
        </table>
    </div>
    <?php if ($note !== '') : ?>
        <p class="wps-leaderboard__note"><?php echo esc_html($note); ?></p>
    <?php endif; ?>
</div>
