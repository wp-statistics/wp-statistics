<?php
namespace WP_Statistics\Service\Admin\ExportImport\Reports;

use WP_STATISTICS\Country;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Admin\Posts\WordCountService;

class ReportsExportDataTransformer
{
    public static function transformVisitorsData($visitors)
    {
        $result = [];

        foreach ($visitors as $visitor) {
            /** @var \WP_Statistics\Decorators\VisitorDecorator $visitor */
            $row = [];

            $row['ID']             = $visitor->getId();
            $row['hash_ip']        = $visitor->isHashedIP() ? $visitor->getRawIP() : '';
            $row['ip']             = !$visitor->isHashedIP() ? $visitor->getRawIP() : '';
            $row['user_id']        = $visitor->getUserId();
            $row['country']        = $visitor->getLocation()->getCountryName();
            $row['region']         = $visitor->getLocation()->getRegion();
            $row['city']           = $visitor->getLocation()->getCity();
            $row['os']             = $visitor->getOs()->getName();
            $row['device_type']    = $visitor->getDevice()->getType();
            $row['browser']        = $visitor->getBrowser()->getName();
            $row['referrer']       = $visitor->getReferral()->getRawReferrer();
            $row['source_channel'] = $visitor->getReferral()->getSourceChannel();
            $row['first_page']     = $visitor->getFirstPage()['link'] ?? '';
            $row['first_view']     = $visitor->getFirstView(true);
            $row['last_page']      = $visitor->getLastPage()['link'] ?? '';
            $row['last_view']      = $visitor->getLastView(true);
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

            $row['ID']             = $visitor->getId();
            $row['view_time']      = $visitor->getLastView(true);
            $row['hash_ip']        = $visitor->isHashedIP() ? $visitor->getRawIP() : '';
            $row['ip']             = !$visitor->isHashedIP() ? $visitor->getRawIP() : '';
            $row['user_id']        = $visitor->getUserId();
            $row['country']        = $visitor->getLocation()->getCountryName();
            $row['region']         = $visitor->getLocation()->getRegion();
            $row['city']           = $visitor->getLocation()->getCity();
            $row['os']             = $visitor->getOs()->getName();
            $row['device_type']    = $visitor->getDevice()->getType();
            $row['browser']        = $visitor->getBrowser()->getName();
            $row['referrer']       = $visitor->getReferral()->getRawReferrer();
            $row['source_channel'] = $visitor->getReferral()->getSourceChannel();
            $row['page']           = $visitor->getLastPage()['link'] ?? '';
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

        foreach ($categories as $term) {
            $row = [];

            $term = (array) $term;

            $row['term_id']     = $term['term_id'];
            $row['term_title']  = $term['term_name'];
            $row['total_posts'] = $term['posts_count'] ?? $term['posts'] ?? '';
            $row['views']       = $term['views'];

            if (isset($term['avg_views'])) {
                $row['views_avg'] = intval($term['avg_views']);
            }

            if (isset($term['words'])) {
                $row['words'] = $term['words'];
            }

            if (isset($term['avg_words'])) {
                $row['words_avg'] = intval($term['avg_words']);
            }

            $row['url'] = get_term_link(intval($term['term_id']));

            $result[] = $row;
        }

        return $result;
    }

    public static function transformAuthorsData($authors)
    {
        $result = [];

        foreach ($authors as $author) {
            $row = [];

            $row['author_id']      = $author->id;
            $row['author_name']    = $author->name;
            $row['total_contents'] = $author->total_posts;

            if (isset($author->page_views)) {
                $row['page_views'] = $author->page_views;
            }

            if (isset($author->total_views)) {
                $row['content_views'] = $author->total_views;
            }

            if (isset($author->total_words)) {
                $row['words'] = $author->total_words;
            }

            if (isset($author->total_comments)) {
                $row['comments'] = $author->total_comments;
            }

            if (isset($author->average_comments)) {
                $row['comments_avg'] = intval($author->average_comments);
            }

            if (isset($author->average_views)) {
                $row['views_avg'] = intval($author->average_views);
            }

            if (isset($author->average_words)) {
                $row['words_avg'] = intval($author->average_words);
            }

            $row['url'] = get_author_posts_url($author->id);

            $result[] = $row;
        }

        return $result;
    }

    public static function transformReferrersData($referrers)
    {
        $result = [];

        foreach ($referrers as $referrer) {
            /** @var \WP_Statistics\Decorators\ReferralDecorator $referrer */

            $row = [];

            $row['domain']      = $referrer->getRawReferrer();
            $row['source_name'] = $referrer->getSourceName();
            $row['referrals']   = $referrer->getTotalReferrals();

            $result[] = $row;
        }

        return $result;
    }

    public static function transformSourceCategoriesData($data)
    {
        $result = [];

        foreach ($data['categories'] as $referrer) {
            /** @var \WP_Statistics\Decorators\ReferralDecorator $referrer */

            $row = [];

            $row['source_channel'] = $referrer->getSourceChannel();
            $row['referrals']      = $referrer->getTotalReferrals();
            $row['share_pct']      = Helper::calculatePercentage($referrer->getTotalReferrals(), $data['total']);

            $result[] = $row;
        }

        return $result;
    }

    public static function transformGeoData($type, $data)
    {
        $result = [];

        foreach ($data as $item) {

            $row = [];

            if (in_array($type, ['country', 'city'])) {
                $row['country_code'] = $item->country;
                $row['country_name'] = $item->country ? Country::getName($item->country) : esc_html__('not set', 'wp-statistics');
            }

            if (in_array($type, ['region', 'city'])) {
                $row['region'] = $item->region;
            }

            if ($type == 'city') {
                $row['city']   = $item->city;
            }

            $row['visitors'] = $item->visitors;
            $row['views']    = $item->views;

            $result[] = $row;
        }

        return $result;
    }

    public static function transformDeviceData($type, $data)
    {
        $result = [];

        foreach ($data['visitors'] as $item) {

            $row = [];

            if ($type == 'browser') {
                $row['browser'] = $item->agent;
            }

            if ($type == 'single-browser') {
                $row['version'] = $item->casted_version;
            }

            if ($type == 'os') {
                $row['os'] = $item->platform;
            }

            if ($type == 'model') {
                $row['os'] = $item->model;
            }

            if ($type == 'device') {
                $row['os'] = ucfirst($item->device);
            }

            $row['visitors'] = $item->visitors;
            $row['share_pct'] = Helper::calculatePercentage($item->visitors, $data['visits']);

            $result[] = $row;
        }

        return $result;
    }

    public static function transformExclusionsData($data, $total)
    {
        $result = [];

        foreach ($data as $item) {
            $row = [];

            $row['type']  = $item->reason;
            $row['count'] = $item->count;

            $row['share_pct'] = Helper::calculatePercentage($item->count, $total);

            $result[] = $row;
        }

        return $result;
    }
}