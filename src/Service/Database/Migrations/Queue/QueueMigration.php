<?php

namespace WP_Statistics\Service\Database\Migrations\Queue;

use WP_Statistics\Abstracts\BaseMigrationOperation;
use WP_STATISTICS\Option;
use WP_STATISTICS\Helper;

/**
 * Queue migration class for handling database migration steps.
 *
 * This class extends BaseMigrationOperation to provide specific migration
 * functionality for queued operations. It handles various setting updates
 * and data transformations during the migration process.
 */
class QueueMigration extends BaseMigrationOperation
{
    /**
     * Array of migration steps with their corresponding method names.
     *
     * Each key represents a migration step identifier, and the value
     * is the corresponding method name to execute for that step.
     * The methods are called sequentially during the migration process.
     *
     * @var array<string, string> Array mapping step names to method names
     */
    protected $migrationSteps = [
        'updateRobotListSetting' => 'updateRobotListSetting'
    ];

    /**
     * Updates the 'robotlist' option by removing all default known bots.
     *
     * This method retrieves the current list of robots from the database (newline-separated),
     * compares it against a predefined list of default bots, and removes any matching items.
     * The result is saved back to the database, preserving only custom/non-default entries.
     *
     * @return void
     */
    public function updateRobotListSetting()
    {
        $defaultRobots = [
            '007ac9', '5bot', 'A6-Indexer', 'AbachoBOT', 'accoona', 'AcoiRobot', 'AddThis.com', 'ADmantX', 'AdsBot-Google', 'advbot', 'AhrefsBot', 'aiHitBot', 'alexa', 'alphabot', 'AltaVista',
            'AntivirusPro', 'anyevent', 'appie', 'Applebot', 'archive.org_bot', 'Ask Jeeves', 'ASPSeek', 'Baiduspider', 'Benjojo', 'BeetleBot', 'bingbot', 'Blekkobot', 'blexbot', 'BOT for JCE', 'bubing',
            'Butterfly', 'cbot', 'clamantivirus', 'cliqzbot', 'clumboot', 'coccoc', 'crawler', 'CrocCrawler', 'crowsnest.tv', 'dbot', 'dl2bot', 'dotbot', 'downloadbot', 'duckduckgo', 'Dumbot',
            'EasouSpider', 'eStyle', 'EveryoneSocialBot', 'Exabot', 'ezooms', 'facebook.com', 'facebookexternalhit', 'FAST', 'Feedfetcher-Google', 'feedzirra', 'findxbot', 'Firfly', 'FriendFeedBot', 'froogle', 'GeonaBot',
            'Gigabot', 'girafabot', 'gimme60bot', 'glbot', 'Googlebot', 'GroupHigh', 'ia_archiver', 'IDBot', 'InfoSeek', 'inktomi', 'IstellaBot', 'jetmon', 'Kraken', 'Leikibot', 'linkapediabot',
            'linkdexbot', 'LinkpadBot', 'LoadTimeBot', 'looksmart', 'ltx71', 'Lycos', 'Mail.RU_Bot', 'Me.dium', 'meanpathbot', 'mediabot', 'medialbot', 'Mediapartners-Google', 'MJ12bot', 'msnbot', 'MojeekBot',
            'monobot', 'moreover', 'MRBOT', 'NationalDirectory', 'NerdyBot', 'NetcraftSurveyAgent', 'niki-bot', 'nutch', 'Openbot', 'OrangeBot', 'owler', 'p4Bot', 'PaperLiBot', 'pageanalyzer', 'PagesInventory',
            'Pimonster', 'porkbun', 'pr-cy', 'proximic', 'pwbot', 'r4bot', 'rabaz', 'Rambler', 'Rankivabot', 'revip', 'riddler', 'rogerbot', 'Scooter', 'Scrubby', 'scrapy.org',
            'SearchmetricsBot', 'sees.co', 'SemanticBot', 'SemrushBot', 'SeznamBot', 'sfFeedReader', 'shareaholic-bot', 'sistrix', 'SiteExplorer', 'Slurp', 'Socialradarbot', 'SocialSearch', 'Sogou web spider', 'Spade', 'spbot',
            'SpiderLing', 'SputnikBot', 'Superfeedr', 'SurveyBot', 'TechnoratiSnoop', 'TECNOSEEK', 'Teoma', 'trendictionbot', 'TweetmemeBot', 'Twiceler', 'Twitterbot', 'Twitturls', 'u2bot', 'uMBot-LN', 'uni5download',
            'unrulymedia', 'UptimeRobot', 'URL_Spider_SQL', 'Vagabondo', 'vBSEO', 'WASALive-Bot', 'WebAlta Crawler', 'WebBug', 'WebFindBot', 'WebMasterAid', 'WeSEE', 'Wotbox', 'wsowner', 'wsr-agent', 'www.galaxy.com',
            'x100bot', 'XoviBot', 'xzybot', 'yandex', 'Yahoo', 'Yammybot', 'YoudaoBot', 'ZyBorg', 'ZemlyaCrawl'
        ];

        $robotlist = Option::get('robotlist');

        if (!empty($robotlist)) {
            $robotArray      = explode("\n", $robotlist);
            $robotArray      = array_map('trim', $robotArray);
            $customRobotList = array_diff($robotArray, $defaultRobots);

            Option::update('robotlist', implode("\n", $customRobotList));
        }
    }
}