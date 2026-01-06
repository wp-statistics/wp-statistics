<?php

namespace WP_Statistics\Service\Shortcode\Handlers;

use WP_Statistics\Service\Shortcode\Contracts\StatHandlerInterface;
use WP_Statistics\Utils\Comment;
use WP_Statistics\Utils\Post;
use WP_Statistics\Utils\PostType;
use WP_Statistics\Utils\User;

/**
 * Handler for WordPress core statistics.
 *
 * Handles stats that come from WordPress core:
 * post count, page count, comment count, user count, etc.
 *
 * @since 15.0.0
 */
class WordPressStatHandler implements StatHandlerInterface
{
    /**
     * Supported stat types.
     *
     * @var array
     */
    private const SUPPORTED_STATS = [
        'postcount',
        'pagecount',
        'commentcount',
        'spamcount',
        'usercount',
        'postaverage',
        'commentaverage',
        'useraverage',
        'lpd',
    ];

    /**
     * {@inheritdoc}
     */
    public function getSupportedStats(): array
    {
        return self::SUPPORTED_STATS;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $stat): bool
    {
        return in_array($stat, self::SUPPORTED_STATS, true);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(string $stat, array $args = [])
    {
        switch ($stat) {
            case 'postcount':
                return PostType::countPublished('post');

            case 'pagecount':
                return PostType::countPublished('page');

            case 'commentcount':
                return Comment::countAll('approved');

            case 'spamcount':
                return Comment::countAll('spam');

            case 'usercount':
                return User::countAll();

            case 'postaverage':
                return Post::getPublishRate();

            case 'commentaverage':
                return Comment::getPublishRate();

            case 'useraverage':
                return User::getRegisterRate();

            case 'lpd':
                return Post::getLastByDate();

            default:
                return '';
        }
    }
}
