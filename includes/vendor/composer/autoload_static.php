<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2ac516f03a394ec3d1721352b7f43359
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WhichBrowser\\' => 13,
            'WP_Statistics\\' => 14,
        ),
        'P' => 
        array (
            'Psr\\Cache\\' => 10,
        ),
        'M' => 
        array (
            'MaxMind\\WebService\\' => 19,
            'MaxMind\\Exception\\' => 18,
            'MaxMind\\Db\\' => 11,
        ),
        'J' => 
        array (
            'Jaybizzle\\CrawlerDetect\\' => 24,
        ),
        'I' => 
        array (
            'IPTools\\' => 8,
        ),
        'G' => 
        array (
            'GeoIp2\\' => 7,
        ),
        'C' => 
        array (
            'Composer\\CaBundle\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WhichBrowser\\' => 
        array (
            0 => __DIR__ . '/..' . '/whichbrowser/parser/src',
            1 => __DIR__ . '/..' . '/whichbrowser/parser/tests/src',
        ),
        'WP_Statistics\\' => 
        array (
            0 => __DIR__ . '/../../..' . '/src',
        ),
        'Psr\\Cache\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/cache/src',
        ),
        'MaxMind\\WebService\\' => 
        array (
            0 => __DIR__ . '/..' . '/maxmind/web-service-common/src/WebService',
        ),
        'MaxMind\\Exception\\' => 
        array (
            0 => __DIR__ . '/..' . '/maxmind/web-service-common/src/Exception',
        ),
        'MaxMind\\Db\\' => 
        array (
            0 => __DIR__ . '/..' . '/maxmind-db/reader/src/MaxMind/Db',
        ),
        'Jaybizzle\\CrawlerDetect\\' => 
        array (
            0 => __DIR__ . '/..' . '/jaybizzle/crawler-detect/src',
        ),
        'IPTools\\' => 
        array (
            0 => __DIR__ . '/..' . '/s1lentium/iptools/src',
        ),
        'GeoIp2\\' => 
        array (
            0 => __DIR__ . '/..' . '/geoip2/geoip2/src',
        ),
        'Composer\\CaBundle\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/ca-bundle/src',
        ),
    );

    public static $classMap = array (
        'Composer\\CaBundle\\CaBundle' => __DIR__ . '/..' . '/composer/ca-bundle/src/CaBundle.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'GeoIp2\\Database\\Reader' => __DIR__ . '/..' . '/geoip2/geoip2/src/Database/Reader.php',
        'GeoIp2\\Exception\\AddressNotFoundException' => __DIR__ . '/..' . '/geoip2/geoip2/src/Exception/AddressNotFoundException.php',
        'GeoIp2\\Exception\\AuthenticationException' => __DIR__ . '/..' . '/geoip2/geoip2/src/Exception/AuthenticationException.php',
        'GeoIp2\\Exception\\GeoIp2Exception' => __DIR__ . '/..' . '/geoip2/geoip2/src/Exception/GeoIp2Exception.php',
        'GeoIp2\\Exception\\HttpException' => __DIR__ . '/..' . '/geoip2/geoip2/src/Exception/HttpException.php',
        'GeoIp2\\Exception\\InvalidRequestException' => __DIR__ . '/..' . '/geoip2/geoip2/src/Exception/InvalidRequestException.php',
        'GeoIp2\\Exception\\OutOfQueriesException' => __DIR__ . '/..' . '/geoip2/geoip2/src/Exception/OutOfQueriesException.php',
        'GeoIp2\\Model\\AbstractModel' => __DIR__ . '/..' . '/geoip2/geoip2/src/Model/AbstractModel.php',
        'GeoIp2\\Model\\AnonymousIp' => __DIR__ . '/..' . '/geoip2/geoip2/src/Model/AnonymousIp.php',
        'GeoIp2\\Model\\Asn' => __DIR__ . '/..' . '/geoip2/geoip2/src/Model/Asn.php',
        'GeoIp2\\Model\\City' => __DIR__ . '/..' . '/geoip2/geoip2/src/Model/City.php',
        'GeoIp2\\Model\\ConnectionType' => __DIR__ . '/..' . '/geoip2/geoip2/src/Model/ConnectionType.php',
        'GeoIp2\\Model\\Country' => __DIR__ . '/..' . '/geoip2/geoip2/src/Model/Country.php',
        'GeoIp2\\Model\\Domain' => __DIR__ . '/..' . '/geoip2/geoip2/src/Model/Domain.php',
        'GeoIp2\\Model\\Enterprise' => __DIR__ . '/..' . '/geoip2/geoip2/src/Model/Enterprise.php',
        'GeoIp2\\Model\\Insights' => __DIR__ . '/..' . '/geoip2/geoip2/src/Model/Insights.php',
        'GeoIp2\\Model\\Isp' => __DIR__ . '/..' . '/geoip2/geoip2/src/Model/Isp.php',
        'GeoIp2\\ProviderInterface' => __DIR__ . '/..' . '/geoip2/geoip2/src/ProviderInterface.php',
        'GeoIp2\\Record\\AbstractPlaceRecord' => __DIR__ . '/..' . '/geoip2/geoip2/src/Record/AbstractPlaceRecord.php',
        'GeoIp2\\Record\\AbstractRecord' => __DIR__ . '/..' . '/geoip2/geoip2/src/Record/AbstractRecord.php',
        'GeoIp2\\Record\\City' => __DIR__ . '/..' . '/geoip2/geoip2/src/Record/City.php',
        'GeoIp2\\Record\\Continent' => __DIR__ . '/..' . '/geoip2/geoip2/src/Record/Continent.php',
        'GeoIp2\\Record\\Country' => __DIR__ . '/..' . '/geoip2/geoip2/src/Record/Country.php',
        'GeoIp2\\Record\\Location' => __DIR__ . '/..' . '/geoip2/geoip2/src/Record/Location.php',
        'GeoIp2\\Record\\MaxMind' => __DIR__ . '/..' . '/geoip2/geoip2/src/Record/MaxMind.php',
        'GeoIp2\\Record\\Postal' => __DIR__ . '/..' . '/geoip2/geoip2/src/Record/Postal.php',
        'GeoIp2\\Record\\RepresentedCountry' => __DIR__ . '/..' . '/geoip2/geoip2/src/Record/RepresentedCountry.php',
        'GeoIp2\\Record\\Subdivision' => __DIR__ . '/..' . '/geoip2/geoip2/src/Record/Subdivision.php',
        'GeoIp2\\Record\\Traits' => __DIR__ . '/..' . '/geoip2/geoip2/src/Record/Traits.php',
        'GeoIp2\\Util' => __DIR__ . '/..' . '/geoip2/geoip2/src/Util.php',
        'GeoIp2\\WebService\\Client' => __DIR__ . '/..' . '/geoip2/geoip2/src/WebService/Client.php',
        'IPTools\\Exception\\IpException' => __DIR__ . '/..' . '/s1lentium/iptools/src/Exception/IpException.php',
        'IPTools\\Exception\\IpToolsException' => __DIR__ . '/..' . '/s1lentium/iptools/src/Exception/IpToolsException.php',
        'IPTools\\Exception\\NetworkException' => __DIR__ . '/..' . '/s1lentium/iptools/src/Exception/NetworkException.php',
        'IPTools\\Exception\\RangeException' => __DIR__ . '/..' . '/s1lentium/iptools/src/Exception/RangeException.php',
        'IPTools\\IP' => __DIR__ . '/..' . '/s1lentium/iptools/src/IP.php',
        'IPTools\\Network' => __DIR__ . '/..' . '/s1lentium/iptools/src/Network.php',
        'IPTools\\PropertyTrait' => __DIR__ . '/..' . '/s1lentium/iptools/src/PropertyTrait.php',
        'IPTools\\Range' => __DIR__ . '/..' . '/s1lentium/iptools/src/Range.php',
        'Jaybizzle\\CrawlerDetect\\CrawlerDetect' => __DIR__ . '/..' . '/jaybizzle/crawler-detect/src/CrawlerDetect.php',
        'Jaybizzle\\CrawlerDetect\\Fixtures\\AbstractProvider' => __DIR__ . '/..' . '/jaybizzle/crawler-detect/src/Fixtures/AbstractProvider.php',
        'Jaybizzle\\CrawlerDetect\\Fixtures\\Crawlers' => __DIR__ . '/..' . '/jaybizzle/crawler-detect/src/Fixtures/Crawlers.php',
        'Jaybizzle\\CrawlerDetect\\Fixtures\\Exclusions' => __DIR__ . '/..' . '/jaybizzle/crawler-detect/src/Fixtures/Exclusions.php',
        'Jaybizzle\\CrawlerDetect\\Fixtures\\Headers' => __DIR__ . '/..' . '/jaybizzle/crawler-detect/src/Fixtures/Headers.php',
        'MaxMind\\Db\\Reader' => __DIR__ . '/..' . '/maxmind-db/reader/src/MaxMind/Db/Reader.php',
        'MaxMind\\Db\\Reader\\Decoder' => __DIR__ . '/..' . '/maxmind-db/reader/src/MaxMind/Db/Reader/Decoder.php',
        'MaxMind\\Db\\Reader\\InvalidDatabaseException' => __DIR__ . '/..' . '/maxmind-db/reader/src/MaxMind/Db/Reader/InvalidDatabaseException.php',
        'MaxMind\\Db\\Reader\\Metadata' => __DIR__ . '/..' . '/maxmind-db/reader/src/MaxMind/Db/Reader/Metadata.php',
        'MaxMind\\Db\\Reader\\Util' => __DIR__ . '/..' . '/maxmind-db/reader/src/MaxMind/Db/Reader/Util.php',
        'MaxMind\\Exception\\AuthenticationException' => __DIR__ . '/..' . '/maxmind/web-service-common/src/Exception/AuthenticationException.php',
        'MaxMind\\Exception\\HttpException' => __DIR__ . '/..' . '/maxmind/web-service-common/src/Exception/HttpException.php',
        'MaxMind\\Exception\\InsufficientFundsException' => __DIR__ . '/..' . '/maxmind/web-service-common/src/Exception/InsufficientFundsException.php',
        'MaxMind\\Exception\\InvalidInputException' => __DIR__ . '/..' . '/maxmind/web-service-common/src/Exception/InvalidInputException.php',
        'MaxMind\\Exception\\InvalidRequestException' => __DIR__ . '/..' . '/maxmind/web-service-common/src/Exception/InvalidRequestException.php',
        'MaxMind\\Exception\\IpAddressNotFoundException' => __DIR__ . '/..' . '/maxmind/web-service-common/src/Exception/IpAddressNotFoundException.php',
        'MaxMind\\Exception\\PermissionRequiredException' => __DIR__ . '/..' . '/maxmind/web-service-common/src/Exception/PermissionRequiredException.php',
        'MaxMind\\Exception\\WebServiceException' => __DIR__ . '/..' . '/maxmind/web-service-common/src/Exception/WebServiceException.php',
        'MaxMind\\WebService\\Client' => __DIR__ . '/..' . '/maxmind/web-service-common/src/WebService/Client.php',
        'MaxMind\\WebService\\Http\\CurlRequest' => __DIR__ . '/..' . '/maxmind/web-service-common/src/WebService/Http/CurlRequest.php',
        'MaxMind\\WebService\\Http\\Request' => __DIR__ . '/..' . '/maxmind/web-service-common/src/WebService/Http/Request.php',
        'MaxMind\\WebService\\Http\\RequestFactory' => __DIR__ . '/..' . '/maxmind/web-service-common/src/WebService/Http/RequestFactory.php',
        'Psr\\Cache\\CacheException' => __DIR__ . '/..' . '/psr/cache/src/CacheException.php',
        'Psr\\Cache\\CacheItemInterface' => __DIR__ . '/..' . '/psr/cache/src/CacheItemInterface.php',
        'Psr\\Cache\\CacheItemPoolInterface' => __DIR__ . '/..' . '/psr/cache/src/CacheItemPoolInterface.php',
        'Psr\\Cache\\InvalidArgumentException' => __DIR__ . '/..' . '/psr/cache/src/InvalidArgumentException.php',
        'WP_Statistics\\Abstracts\\BaseModel' => __DIR__ . '/../../..' . '/src/Abstracts/BaseModel.php',
        'WP_Statistics\\Abstracts\\BasePage' => __DIR__ . '/../../..' . '/src/Abstracts/BasePage.php',
        'WP_Statistics\\Abstracts\\BaseTabView' => __DIR__ . '/../../..' . '/src/Abstracts/BaseTabView.php',
        'WP_Statistics\\Abstracts\\BaseView' => __DIR__ . '/../../..' . '/src/Abstracts/BaseView.php',
        'WP_Statistics\\Abstracts\\MultiViewPage' => __DIR__ . '/../../..' . '/src/Abstracts/MultiViewPage.php',
        'WP_Statistics\\Async\\CalculatePostWordsCount' => __DIR__ . '/../../..' . '/src/Async/CalculatePostWordsCount.php',
        'WP_Statistics\\Components\\AssetNameObfuscator' => __DIR__ . '/../../..' . '/src/Components/AssetNameObfuscator.php',
        'WP_Statistics\\Components\\Assets' => __DIR__ . '/../../..' . '/src/Components/Assets.php',
        'WP_Statistics\\Components\\Singleton' => __DIR__ . '/../../..' . '/src/Components/Singleton.php',
        'WP_Statistics\\Exception\\SystemErrorException' => __DIR__ . '/../../..' . '/src/Exception/SystemErrorException.php',
        'WP_Statistics\\Models\\AuthorsModel' => __DIR__ . '/../../..' . '/src/Models/AuthorsModel.php',
        'WP_Statistics\\Models\\PagesModel' => __DIR__ . '/../../..' . '/src/Models/PagesModel.php',
        'WP_Statistics\\Models\\PostsModel' => __DIR__ . '/../../..' . '/src/Models/PostsModel.php',
        'WP_Statistics\\Models\\TaxonomyModel' => __DIR__ . '/../../..' . '/src/Models/TaxonomyModel.php',
        'WP_Statistics\\Models\\VisitorsModel' => __DIR__ . '/../../..' . '/src/Models/VisitorsModel.php',
        'WP_Statistics\\Service\\Admin\\AddOnDecorator' => __DIR__ . '/../../..' . '/src/Service/Admin/AddOnDecorator.php',
        'WP_Statistics\\Service\\Admin\\AddOnsFactory' => __DIR__ . '/../../..' . '/src/Service/Admin/AddOnsFactory.php',
        'WP_Statistics\\Service\\Admin\\AdminManager' => __DIR__ . '/../../..' . '/src/Service/Admin/AdminManager.php',
        'WP_Statistics\\Service\\Admin\\AuthorAnalytics\\AuthorAnalyticsDataProvider' => __DIR__ . '/../../..' . '/src/Service/Admin/AuthorAnalytics/AuthorAnalyticsDataProvider.php',
        'WP_Statistics\\Service\\Admin\\AuthorAnalytics\\AuthorAnalyticsManager' => __DIR__ . '/../../..' . '/src/Service/Admin/AuthorAnalytics/AuthorAnalyticsManager.php',
        'WP_Statistics\\Service\\Admin\\AuthorAnalytics\\AuthorAnalyticsPage' => __DIR__ . '/../../..' . '/src/Service/Admin/AuthorAnalytics/AuthorAnalyticsPage.php',
        'WP_Statistics\\Service\\Admin\\AuthorAnalytics\\Views\\AuthorsView' => __DIR__ . '/../../..' . '/src/Service/Admin/AuthorAnalytics/Views/AuthorsView.php',
        'WP_Statistics\\Service\\Admin\\AuthorAnalytics\\Views\\SingleAuthorView' => __DIR__ . '/../../..' . '/src/Service/Admin/AuthorAnalytics/Views/SingleAuthorView.php',
        'WP_Statistics\\Service\\Admin\\AuthorAnalytics\\Views\\TabsView' => __DIR__ . '/../../..' . '/src/Service/Admin/AuthorAnalytics/Views/TabsView.php',
        'WP_Statistics\\Service\\Admin\\Geographic\\GeographicManager' => __DIR__ . '/../../..' . '/src/Service/Admin/Geographic/GeographicManager.php',
        'WP_Statistics\\Service\\Admin\\Geographic\\GeographicPage' => __DIR__ . '/../../..' . '/src/Service/Admin/Geographic/GeographicPage.php',
        'WP_Statistics\\Service\\Admin\\Geographic\\Views\\SingleView' => __DIR__ . '/../../..' . '/src/Service/Admin/Geographic/Views/SingleView.php',
        'WP_Statistics\\Service\\Admin\\Geographic\\Views\\TabsView' => __DIR__ . '/../../..' . '/src/Service/Admin/Geographic/Views/TabsView.php',
        'WP_Statistics\\Service\\Admin\\NoticeHandler\\GeneralNotices' => __DIR__ . '/../../..' . '/src/Service/Admin/NoticeHandler/GeneralNotices.php',
        'WP_Statistics\\Service\\Admin\\NoticeHandler\\Notice' => __DIR__ . '/../../..' . '/src/Service/Admin/NoticeHandler/Notice.php',
        'WP_Statistics\\Service\\Admin\\Posts\\PostsManager' => __DIR__ . '/../../..' . '/src/Service/Admin/Posts/PostsManager.php',
        'WP_Statistics\\Service\\Admin\\Posts\\WordCount' => __DIR__ . '/../../..' . '/src/Service/Admin/Posts/WordCount.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\Abstracts\\BaseAudit' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/Audits/Abstracts/BaseAudit.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\Abstracts\\ResolvableAudit' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/Audits/Abstracts/ResolvableAudit.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\AnonymizeIpAddress' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/Audits/AnonymizeIpAddress.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\HashIpAddress' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/Audits/HashIpAddress.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\RecordUserPageVisits' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/Audits/RecordUserPageVisits.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\StoreUserAgentString' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/Audits/StoreUserAgentString.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\StoredUserAgentStringData' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/Audits/StoredUserAgentStringData.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\StoredUserIdData' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/Audits/StoredUserIdData.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\UnhashedIpAddress' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/Audits/UnhashedIpAddress.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Faqs\\AbstractFaq' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/Faqs/AbstractFaq.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Faqs\\RequireConsent' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/Faqs/RequireConsent.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Faqs\\RequireCookieBanner' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/Faqs/RequireCookieBanner.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Faqs\\RequireMention' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/Faqs/RequireMention.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Faqs\\TransferData' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/Faqs/TransferData.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\PrivacyAuditCheck' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/PrivacyAuditCheck.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\PrivacyAuditController' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/PrivacyAuditController.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\PrivacyAuditManager' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/PrivacyAuditManager.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\PrivacyAuditPage' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/PrivacyAuditPage.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\PrivacyStatusOption' => __DIR__ . '/../../..' . '/src/Service/Admin/PrivacyAudit/PrivacyStatusOption.php',
        'WP_Statistics\\Service\\Analytics\\AnalyticsController' => __DIR__ . '/../../..' . '/src/Service/Analytics/AnalyticsController.php',
        'WP_Statistics\\Service\\Analytics\\AnalyticsManager' => __DIR__ . '/../../..' . '/src/Service/Analytics/AnalyticsManager.php',
        'WP_Statistics\\Service\\Analytics\\VisitorProfile' => __DIR__ . '/../../..' . '/src/Service/Analytics/VisitorProfile.php',
        'WP_Statistics\\Traits\\Cacheable' => __DIR__ . '/../../..' . '/src/Traits/Cacheable.php',
        'WP_Statistics\\Utils\\Query' => __DIR__ . '/../../..' . '/src/Utils/Query.php',
        'WP_Statistics\\Utils\\Request' => __DIR__ . '/../../..' . '/src/Utils/Request.php',
        'WhichBrowser\\Analyser' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser.php',
        'WhichBrowser\\Analyser\\Camouflage' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Camouflage.php',
        'WhichBrowser\\Analyser\\Corrections' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Corrections.php',
        'WhichBrowser\\Analyser\\Derive' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Derive.php',
        'WhichBrowser\\Analyser\\Header' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header.php',
        'WhichBrowser\\Analyser\\Header\\Baidu' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Baidu.php',
        'WhichBrowser\\Analyser\\Header\\BrowserId' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/BrowserId.php',
        'WhichBrowser\\Analyser\\Header\\OperaMini' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/OperaMini.php',
        'WhichBrowser\\Analyser\\Header\\Puffin' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Puffin.php',
        'WhichBrowser\\Analyser\\Header\\UCBrowserNew' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/UCBrowserNew.php',
        'WhichBrowser\\Analyser\\Header\\UCBrowserOld' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/UCBrowserOld.php',
        'WhichBrowser\\Analyser\\Header\\Useragent' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Application' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Application.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Bot' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Bot.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Browser' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Browser.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Device' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Device.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Device\\Appliance' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Device/Appliance.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Device\\Cars' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Device/Cars.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Device\\Ereader' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Device/Ereader.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Device\\Gaming' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Device/Gaming.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Device\\Gps' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Device/Gps.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Device\\Media' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Device/Media.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Device\\Mobile' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Device/Mobile.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Device\\Pda' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Device/Pda.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Device\\Phone' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Device/Phone.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Device\\Printer' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Device/Printer.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Device\\Signage' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Device/Signage.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Device\\Tablet' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Device/Tablet.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Device\\Television' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Device/Television.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Engine' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Engine.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Os' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Os.php',
        'WhichBrowser\\Analyser\\Header\\Useragent\\Using' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Useragent/Using.php',
        'WhichBrowser\\Analyser\\Header\\Wap' => __DIR__ . '/..' . '/whichbrowser/parser/src/Analyser/Header/Wap.php',
        'WhichBrowser\\Cache' => __DIR__ . '/..' . '/whichbrowser/parser/src/Cache.php',
        'WhichBrowser\\Constants\\BrowserType' => __DIR__ . '/..' . '/whichbrowser/parser/src/Constants/BrowserType.php',
        'WhichBrowser\\Constants\\DeviceSubType' => __DIR__ . '/..' . '/whichbrowser/parser/src/Constants/DeviceSubType.php',
        'WhichBrowser\\Constants\\DeviceType' => __DIR__ . '/..' . '/whichbrowser/parser/src/Constants/DeviceType.php',
        'WhichBrowser\\Constants\\EngineType' => __DIR__ . '/..' . '/whichbrowser/parser/src/Constants/EngineType.php',
        'WhichBrowser\\Constants\\Feature' => __DIR__ . '/..' . '/whichbrowser/parser/src/Constants/Feature.php',
        'WhichBrowser\\Constants\\Flag' => __DIR__ . '/..' . '/whichbrowser/parser/src/Constants/Flag.php',
        'WhichBrowser\\Constants\\Id' => __DIR__ . '/..' . '/whichbrowser/parser/src/Constants/Id.php',
        'WhichBrowser\\Data\\Applications' => __DIR__ . '/..' . '/whichbrowser/parser/src/Data/Applications.php',
        'WhichBrowser\\Data\\BrowserIds' => __DIR__ . '/..' . '/whichbrowser/parser/src/Data/BrowserIds.php',
        'WhichBrowser\\Data\\BuildIds' => __DIR__ . '/..' . '/whichbrowser/parser/src/Data/BuildIds.php',
        'WhichBrowser\\Data\\CFNetwork' => __DIR__ . '/..' . '/whichbrowser/parser/src/Data/CFNetwork.php',
        'WhichBrowser\\Data\\Chrome' => __DIR__ . '/..' . '/whichbrowser/parser/src/Data/Chrome.php',
        'WhichBrowser\\Data\\Darwin' => __DIR__ . '/..' . '/whichbrowser/parser/src/Data/Darwin.php',
        'WhichBrowser\\Data\\DeviceModels' => __DIR__ . '/..' . '/whichbrowser/parser/src/Data/DeviceModels.php',
        'WhichBrowser\\Data\\DeviceProfiles' => __DIR__ . '/..' . '/whichbrowser/parser/src/Data/DeviceProfiles.php',
        'WhichBrowser\\Data\\Manufacturers' => __DIR__ . '/..' . '/whichbrowser/parser/src/Data/Manufacturers.php',
        'WhichBrowser\\Model\\Browser' => __DIR__ . '/..' . '/whichbrowser/parser/src/Model/Browser.php',
        'WhichBrowser\\Model\\Device' => __DIR__ . '/..' . '/whichbrowser/parser/src/Model/Device.php',
        'WhichBrowser\\Model\\Engine' => __DIR__ . '/..' . '/whichbrowser/parser/src/Model/Engine.php',
        'WhichBrowser\\Model\\Family' => __DIR__ . '/..' . '/whichbrowser/parser/src/Model/Family.php',
        'WhichBrowser\\Model\\Main' => __DIR__ . '/..' . '/whichbrowser/parser/src/Model/Main.php',
        'WhichBrowser\\Model\\Os' => __DIR__ . '/..' . '/whichbrowser/parser/src/Model/Os.php',
        'WhichBrowser\\Model\\Primitive\\Base' => __DIR__ . '/..' . '/whichbrowser/parser/src/Model/Primitive/Base.php',
        'WhichBrowser\\Model\\Primitive\\NameVersion' => __DIR__ . '/..' . '/whichbrowser/parser/src/Model/Primitive/NameVersion.php',
        'WhichBrowser\\Model\\Using' => __DIR__ . '/..' . '/whichbrowser/parser/src/Model/Using.php',
        'WhichBrowser\\Model\\Version' => __DIR__ . '/..' . '/whichbrowser/parser/src/Model/Version.php',
        'WhichBrowser\\Parser' => __DIR__ . '/..' . '/whichbrowser/parser/src/Parser.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2ac516f03a394ec3d1721352b7f43359::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2ac516f03a394ec3d1721352b7f43359::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2ac516f03a394ec3d1721352b7f43359::$classMap;

        }, null, ClassLoader::class);
    }
}
