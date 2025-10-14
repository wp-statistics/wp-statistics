<?php
namespace WP_Statistics\Service\Admin\ExportImport\Reports;

use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_STATISTICS\Visitor;

class ReportsExportDataTransformer
{
    public static function transformVisitorsData($visitors)
    {
        $result = [];

        foreach ($visitors as $visitor) {
            /** @var \WP_Statistics\Decorators\VisitorDecorator $visitor */
            $row = [];

            $row['last_view']      = $visitor->getLastView(true);
            $row['country']        = $visitor->getLocation()->getCountryName();
            $row['region']         = $visitor->getLocation()->getRegion();
            $row['city']           = $visitor->getLocation()->getCity();
            $row['os']             = $visitor->getOs()->getName();
            $row['device_type']    = $visitor->getDevice()->getType();
            $row['browser']        = $visitor->getBrowser()->getName();
            $row['ip']             = !$visitor->isHashedIP() ? $visitor->getRawIP() : '';
            $row['hashed_ip']      = $visitor->isHashedIP() ? $visitor->getRawIP() : '';
            $row['user_id']        = $visitor->getUserId();
            $row['referrer']       = $visitor->getReferral()->getRawReferrer();
            $row['source_channel'] = $visitor->getReferral()->getSourceChannel();
            $row['first_page']     = $visitor->getFirstPage()['full_url'] ?? '';
            $row['last_page']      = $visitor->getLastPage()['full_url'] ?? '';
            $row['total_views']    = $visitor->getHits(true);

            $result[] = $row;
        }

        return $result;
    }

    public static function transformViewsData($visitors)
    {
        $result = [];

        foreach ($visitors as $visitor) {
            /** @var \WP_Statistics\Decorators\VisitorDecorator $visitor */
            $row = [];

            $row['view_time']      = $visitor->getLastView(true);
            $row['country']        = $visitor->getLocation()->getCountryName();
            $row['region']         = $visitor->getLocation()->getRegion();
            $row['city']           = $visitor->getLocation()->getCity();
            $row['os']             = $visitor->getOs()->getName();
            $row['device_type']    = $visitor->getDevice()->getType();
            $row['browser']        = $visitor->getBrowser()->getName();
            $row['ip']             = !$visitor->isHashedIP() ? $visitor->getRawIP() : '';
            $row['hashed_ip']      = $visitor->isHashedIP() ? $visitor->getRawIP() : '';
            $row['user_id']        = $visitor->getUserId();
            $row['referrer']       = $visitor->getReferral()->getRawReferrer();
            $row['source_channel'] = $visitor->getReferral()->getSourceChannel();
            $row['exit_page']      = $visitor->getLastPage()['full_url'] ?? '';
            $row['total_views']    = $visitor->getHits(true);

            $result[] = $row;
        }

        return $result;
    }

    public static function transformPostsData($posts)
    {
        $result = [];

        foreach ($posts as $post) {
            $row = [];

            $row['post_id']  = $post->post_id;
            $row['title']    = $post->title;
            $row['visitors'] = $post->visitors;
            $row['views']    = $post->views;

            if (WordCountService::isActive()) {
                $row['words'] = $post->words;
            }

            $row['published_data'] = $post->date;
            $row['url']            = get_permalink($post->post_id);

            $result[] = $row;
        }

        return $result;
    }

    public static function transformCategoriesData($categories)
    {
        $result = [];

        $categories = array_values($categories)[0] ?? [];

        foreach ($categories as $term) {
            $row = [];

            $row['term_id']     = $term['term_id'];
            $row['term_title']  = $term['term_name'];
            $row['views']       = $term['views'];
            $row['total_posts'] = $term['posts_count'];
            $row['page']        = get_term_link(intval($term['term_id']));

            $result[] = $row;
        }

        return $result;
    }

    public static function transformAuthorsData($authors)
    {
        $result = [];

        foreach ($authors as $author) {
            $row = [];

            $row['author_id']   = $author->id;
            $row['author_name'] = $author->name;
            $row['views']       = $author->page_views;
            $row['total_posts'] = $author->total_posts;
            $row['page']        = get_author_posts_url($author->id);

            $result[] = $row;
        }

        return $result;
    }
}