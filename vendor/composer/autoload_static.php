<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0c58b92c1859559503021091c91f6640
{
    public static $files = array (
        '04c6c5c2f7095ccf6c481d3e53e1776f' => __DIR__ . '/..' . '/mustangostang/spyc/Spyc.php',
    );

    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WP_Statistics\\' => 14,
        ),
        'M' => 
        array (
            'MaxMind\\WebService\\' => 19,
            'MaxMind\\Exception\\' => 18,
            'MaxMind\\Db\\' => 11,
        ),
        'G' => 
        array (
            'GeoIp2\\' => 7,
        ),
        'D' => 
        array (
            'DeviceDetector\\' => 15,
        ),
        'C' => 
        array (
            'Composer\\CaBundle\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WP_Statistics\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
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
        'GeoIp2\\' => 
        array (
            0 => __DIR__ . '/..' . '/geoip2/geoip2/src',
        ),
        'DeviceDetector\\' => 
        array (
            0 => __DIR__ . '/..' . '/matomo/device-detector',
        ),
        'Composer\\CaBundle\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/ca-bundle/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'WP_Statistics\\Abstracts\\BaseModel' => __DIR__ . '/../..' . '/src/Abstracts/BaseModel.php',
        'WP_Statistics\\Abstracts\\BasePage' => __DIR__ . '/../..' . '/src/Abstracts/BasePage.php',
        'WP_Statistics\\Abstracts\\BaseTabView' => __DIR__ . '/../..' . '/src/Abstracts/BaseTabView.php',
        'WP_Statistics\\Abstracts\\BaseView' => __DIR__ . '/../..' . '/src/Abstracts/BaseView.php',
        'WP_Statistics\\Abstracts\\MultiViewPage' => __DIR__ . '/../..' . '/src/Abstracts/MultiViewPage.php',
        'WP_Statistics\\Async\\BackgroundProcessFactory' => __DIR__ . '/../..' . '/src/Async/BackgroundProcessFactory.php',
        'WP_Statistics\\Async\\CalculatePostWordsCount' => __DIR__ . '/../..' . '/src/Async/CalculatePostWordsCount.php',
        'WP_Statistics\\Async\\GeolocationDatabaseDownloadProcess' => __DIR__ . '/../..' . '/src/Async/GeolocationDatabaseDownloadProcess.php',
        'WP_Statistics\\Async\\IncompleteGeoIpUpdater' => __DIR__ . '/../..' . '/src/Async/IncompleteGeoIpUpdater.php',
        'WP_Statistics\\Async\\SourceChannelUpdater' => __DIR__ . '/../..' . '/src/Async/SourceChannelUpdater.php',
        'WP_Statistics\\Components\\AssetNameObfuscator' => __DIR__ . '/../..' . '/src/Components/AssetNameObfuscator.php',
        'WP_Statistics\\Components\\Assets' => __DIR__ . '/../..' . '/src/Components/Assets.php',
        'WP_Statistics\\Components\\DateRange' => __DIR__ . '/../..' . '/src/Components/DateRange.php',
        'WP_Statistics\\Components\\DateTime' => __DIR__ . '/../..' . '/src/Components/DateTime.php',
        'WP_Statistics\\Components\\Event' => __DIR__ . '/../..' . '/src/Components/Event.php',
        'WP_Statistics\\Components\\RemoteRequest' => __DIR__ . '/../..' . '/src/Components/RemoteRequest.php',
        'WP_Statistics\\Components\\Singleton' => __DIR__ . '/../..' . '/src/Components/Singleton.php',
        'WP_Statistics\\Components\\View' => __DIR__ . '/../..' . '/src/Components/View.php',
        'WP_Statistics\\Decorators\\BrowserDecorator' => __DIR__ . '/../..' . '/src/Decorators/BrowserDecorator.php',
        'WP_Statistics\\Decorators\\DeviceDecorator' => __DIR__ . '/../..' . '/src/Decorators/DeviceDecorator.php',
        'WP_Statistics\\Decorators\\LocationDecorator' => __DIR__ . '/../..' . '/src/Decorators/LocationDecorator.php',
        'WP_Statistics\\Decorators\\OsDecorator' => __DIR__ . '/../..' . '/src/Decorators/OsDecorator.php',
        'WP_Statistics\\Decorators\\ReferralDecorator' => __DIR__ . '/../..' . '/src/Decorators/ReferralDecorator.php',
        'WP_Statistics\\Decorators\\UserDecorator' => __DIR__ . '/../..' . '/src/Decorators/UserDecorator.php',
        'WP_Statistics\\Decorators\\VisitorDecorator' => __DIR__ . '/../..' . '/src/Decorators/VisitorDecorator.php',
        'WP_Statistics\\Dependencies\\Composer\\CaBundle\\CaBundle' => __DIR__ . '/../..' . '/src/Dependencies/Composer/CaBundle/CaBundle.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Cache\\CacheInterface' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Cache/CacheInterface.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Cache\\DoctrineBridge' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Cache/DoctrineBridge.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Cache\\LaravelCache' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Cache/LaravelCache.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Cache\\PSR16Bridge' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Cache/PSR16Bridge.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Cache\\PSR6Bridge' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Cache/PSR6Bridge.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Cache\\StaticCache' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Cache/StaticCache.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\ClientHints' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/ClientHints.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\DeviceDetector' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/DeviceDetector.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\AbstractBotParser' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/AbstractBotParser.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\AbstractParser' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/AbstractParser.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Bot' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Bot.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Client\\AbstractClientParser' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Client/AbstractClientParser.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Client\\Browser' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Client/Browser.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Client\\Browser\\Engine' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Client/Browser/Engine.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Client\\Browser\\Engine\\Version' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Client/Browser/Engine/Version.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Client\\FeedReader' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Client/FeedReader.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Client\\Hints\\AppHints' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Client/Hints/AppHints.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Client\\Hints\\BrowserHints' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Client/Hints/BrowserHints.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Client\\Library' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Client/Library.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Client\\MediaPlayer' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Client/MediaPlayer.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Client\\MobileApp' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Client/MobileApp.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Client\\PIM' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Client/PIM.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Device\\AbstractDeviceParser' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Device/AbstractDeviceParser.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Device\\Camera' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Device/Camera.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Device\\CarBrowser' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Device/CarBrowser.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Device\\Console' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Device/Console.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Device\\HbbTv' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Device/HbbTv.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Device\\Mobile' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Device/Mobile.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Device\\Notebook' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Device/Notebook.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Device\\PortableMediaPlayer' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Device/PortableMediaPlayer.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\Device\\ShellTv' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/Device/ShellTv.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\OperatingSystem' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/OperatingSystem.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Parser\\VendorFragment' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Parser/VendorFragment.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Yaml\\ParserInterface' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Yaml/ParserInterface.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Yaml\\Pecl' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Yaml/Pecl.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Yaml\\Spyc' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Yaml/Spyc.php',
        'WP_Statistics\\Dependencies\\DeviceDetector\\Yaml\\Symfony' => __DIR__ . '/../..' . '/src/Dependencies/DeviceDetector/Yaml/Symfony.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Database\\Reader' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Database/Reader.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Exception\\AddressNotFoundException' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Exception/AddressNotFoundException.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Exception\\AuthenticationException' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Exception/AuthenticationException.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Exception\\GeoIp2Exception' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Exception/GeoIp2Exception.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Exception\\HttpException' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Exception/HttpException.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Exception\\InvalidRequestException' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Exception/InvalidRequestException.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Exception\\OutOfQueriesException' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Exception/OutOfQueriesException.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Model\\AbstractModel' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Model/AbstractModel.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Model\\AnonymousIp' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Model/AnonymousIp.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Model\\Asn' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Model/Asn.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Model\\City' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Model/City.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Model\\ConnectionType' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Model/ConnectionType.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Model\\Country' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Model/Country.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Model\\Domain' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Model/Domain.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Model\\Enterprise' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Model/Enterprise.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Model\\Insights' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Model/Insights.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Model\\Isp' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Model/Isp.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\ProviderInterface' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/ProviderInterface.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Record\\AbstractPlaceRecord' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Record/AbstractPlaceRecord.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Record\\AbstractRecord' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Record/AbstractRecord.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Record\\City' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Record/City.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Record\\Continent' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Record/Continent.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Record\\Country' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Record/Country.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Record\\Location' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Record/Location.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Record\\MaxMind' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Record/MaxMind.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Record\\Postal' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Record/Postal.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Record\\RepresentedCountry' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Record/RepresentedCountry.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Record\\Subdivision' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Record/Subdivision.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Record\\Traits' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Record/Traits.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\Util' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/Util.php',
        'WP_Statistics\\Dependencies\\GeoIp2\\WebService\\Client' => __DIR__ . '/../..' . '/src/Dependencies/GeoIp2/WebService/Client.php',
        'WP_Statistics\\Dependencies\\MaxMind\\Db\\Reader' => __DIR__ . '/../..' . '/src/Dependencies/MaxMind/Db/Reader.php',
        'WP_Statistics\\Dependencies\\MaxMind\\Db\\Reader\\Decoder' => __DIR__ . '/../..' . '/src/Dependencies/MaxMind/Db/Reader/Decoder.php',
        'WP_Statistics\\Dependencies\\MaxMind\\Db\\Reader\\InvalidDatabaseException' => __DIR__ . '/../..' . '/src/Dependencies/MaxMind/Db/Reader/InvalidDatabaseException.php',
        'WP_Statistics\\Dependencies\\MaxMind\\Db\\Reader\\Metadata' => __DIR__ . '/../..' . '/src/Dependencies/MaxMind/Db/Reader/Metadata.php',
        'WP_Statistics\\Dependencies\\MaxMind\\Db\\Reader\\Util' => __DIR__ . '/../..' . '/src/Dependencies/MaxMind/Db/Reader/Util.php',
        'WP_Statistics\\Dependencies\\MaxMind\\WebService\\Client' => __DIR__ . '/../..' . '/src/Dependencies/MaxMind/WebService/Client.php',
        'WP_Statistics\\Dependencies\\MaxMind\\WebService\\Http\\CurlRequest' => __DIR__ . '/../..' . '/src/Dependencies/MaxMind/WebService/Http/CurlRequest.php',
        'WP_Statistics\\Dependencies\\MaxMind\\WebService\\Http\\Request' => __DIR__ . '/../..' . '/src/Dependencies/MaxMind/WebService/Http/Request.php',
        'WP_Statistics\\Dependencies\\MaxMind\\WebService\\Http\\RequestFactory' => __DIR__ . '/../..' . '/src/Dependencies/MaxMind/WebService/Http/RequestFactory.php',
        'WP_Statistics\\Dependencies\\Psr\\Cache\\CacheException' => __DIR__ . '/../..' . '/src/Dependencies/Psr/Cache/CacheException.php',
        'WP_Statistics\\Dependencies\\Psr\\Cache\\CacheItemInterface' => __DIR__ . '/../..' . '/src/Dependencies/Psr/Cache/CacheItemInterface.php',
        'WP_Statistics\\Dependencies\\Psr\\Cache\\CacheItemPoolInterface' => __DIR__ . '/../..' . '/src/Dependencies/Psr/Cache/CacheItemPoolInterface.php',
        'WP_Statistics\\Dependencies\\Psr\\Cache\\InvalidArgumentException' => __DIR__ . '/../..' . '/src/Dependencies/Psr/Cache/InvalidArgumentException.php',
        'WP_Statistics\\Exception\\LicenseException' => __DIR__ . '/../..' . '/src/Exception/LicenseException.php',
        'WP_Statistics\\Exception\\LogException' => __DIR__ . '/../..' . '/src/Exception/LogException.php',
        'WP_Statistics\\Exception\\SystemErrorException' => __DIR__ . '/../..' . '/src/Exception/SystemErrorException.php',
        'WP_Statistics\\Models\\AuthorsModel' => __DIR__ . '/../..' . '/src/Models/AuthorsModel.php',
        'WP_Statistics\\Models\\HistoricalModel' => __DIR__ . '/../..' . '/src/Models/HistoricalModel.php',
        'WP_Statistics\\Models\\OnlineModel' => __DIR__ . '/../..' . '/src/Models/OnlineModel.php',
        'WP_Statistics\\Models\\PostsModel' => __DIR__ . '/../..' . '/src/Models/PostsModel.php',
        'WP_Statistics\\Models\\TaxonomyModel' => __DIR__ . '/../..' . '/src/Models/TaxonomyModel.php',
        'WP_Statistics\\Models\\ViewsModel' => __DIR__ . '/../..' . '/src/Models/ViewsModel.php',
        'WP_Statistics\\Models\\VisitorsModel' => __DIR__ . '/../..' . '/src/Models/VisitorsModel.php',
        'WP_Statistics\\Service\\Admin\\AdminManager' => __DIR__ . '/../..' . '/src/Service/Admin/AdminManager.php',
        'WP_Statistics\\Service\\Admin\\AuthorAnalytics\\AuthorAnalyticsDataProvider' => __DIR__ . '/../..' . '/src/Service/Admin/AuthorAnalytics/AuthorAnalyticsDataProvider.php',
        'WP_Statistics\\Service\\Admin\\AuthorAnalytics\\AuthorAnalyticsManager' => __DIR__ . '/../..' . '/src/Service/Admin/AuthorAnalytics/AuthorAnalyticsManager.php',
        'WP_Statistics\\Service\\Admin\\AuthorAnalytics\\AuthorAnalyticsPage' => __DIR__ . '/../..' . '/src/Service/Admin/AuthorAnalytics/AuthorAnalyticsPage.php',
        'WP_Statistics\\Service\\Admin\\AuthorAnalytics\\Views\\AuthorsView' => __DIR__ . '/../..' . '/src/Service/Admin/AuthorAnalytics/Views/AuthorsView.php',
        'WP_Statistics\\Service\\Admin\\AuthorAnalytics\\Views\\PerformanceView' => __DIR__ . '/../..' . '/src/Service/Admin/AuthorAnalytics/Views/PerformanceView.php',
        'WP_Statistics\\Service\\Admin\\AuthorAnalytics\\Views\\SingleAuthorView' => __DIR__ . '/../..' . '/src/Service/Admin/AuthorAnalytics/Views/SingleAuthorView.php',
        'WP_Statistics\\Service\\Admin\\CategoryAnalytics\\CategoryAnalyticsDataProvider' => __DIR__ . '/../..' . '/src/Service/Admin/CategoryAnalytics/CategoryAnalyticsDataProvider.php',
        'WP_Statistics\\Service\\Admin\\CategoryAnalytics\\CategoryAnalyticsManager' => __DIR__ . '/../..' . '/src/Service/Admin/CategoryAnalytics/CategoryAnalyticsManager.php',
        'WP_Statistics\\Service\\Admin\\CategoryAnalytics\\CategoryAnalyticsPage' => __DIR__ . '/../..' . '/src/Service/Admin/CategoryAnalytics/CategoryAnalyticsPage.php',
        'WP_Statistics\\Service\\Admin\\CategoryAnalytics\\Views\\CategoryReportView' => __DIR__ . '/../..' . '/src/Service/Admin/CategoryAnalytics/Views/CategoryReportView.php',
        'WP_Statistics\\Service\\Admin\\CategoryAnalytics\\Views\\SingleView' => __DIR__ . '/../..' . '/src/Service/Admin/CategoryAnalytics/Views/SingleView.php',
        'WP_Statistics\\Service\\Admin\\CategoryAnalytics\\Views\\TabsView' => __DIR__ . '/../..' . '/src/Service/Admin/CategoryAnalytics/Views/TabsView.php',
        'WP_Statistics\\Service\\Admin\\ContentAnalytics\\ContentAnalyticsDataProvider' => __DIR__ . '/../..' . '/src/Service/Admin/ContentAnalytics/ContentAnalyticsDataProvider.php',
        'WP_Statistics\\Service\\Admin\\ContentAnalytics\\ContentAnalyticsManager' => __DIR__ . '/../..' . '/src/Service/Admin/ContentAnalytics/ContentAnalyticsManager.php',
        'WP_Statistics\\Service\\Admin\\ContentAnalytics\\ContentAnalyticsPage' => __DIR__ . '/../..' . '/src/Service/Admin/ContentAnalytics/ContentAnalyticsPage.php',
        'WP_Statistics\\Service\\Admin\\ContentAnalytics\\Views\\SingleView' => __DIR__ . '/../..' . '/src/Service/Admin/ContentAnalytics/Views/SingleView.php',
        'WP_Statistics\\Service\\Admin\\ContentAnalytics\\Views\\TabsView' => __DIR__ . '/../..' . '/src/Service/Admin/ContentAnalytics/Views/TabsView.php',
        'WP_Statistics\\Service\\Admin\\Devices\\DevicesDataProvider' => __DIR__ . '/../..' . '/src/Service/Admin/Devices/DevicesDataProvider.php',
        'WP_Statistics\\Service\\Admin\\Devices\\DevicesManager' => __DIR__ . '/../..' . '/src/Service/Admin/Devices/DevicesManager.php',
        'WP_Statistics\\Service\\Admin\\Devices\\DevicesPage' => __DIR__ . '/../..' . '/src/Service/Admin/Devices/DevicesPage.php',
        'WP_Statistics\\Service\\Admin\\Devices\\Views\\SingleBrowserView' => __DIR__ . '/../..' . '/src/Service/Admin/Devices/Views/SingleBrowserView.php',
        'WP_Statistics\\Service\\Admin\\Devices\\Views\\TabsView' => __DIR__ . '/../..' . '/src/Service/Admin/Devices/Views/TabsView.php',
        'WP_Statistics\\Service\\Admin\\Geographic\\GeographicDataProvider' => __DIR__ . '/../..' . '/src/Service/Admin/Geographic/GeographicDataProvider.php',
        'WP_Statistics\\Service\\Admin\\Geographic\\GeographicManager' => __DIR__ . '/../..' . '/src/Service/Admin/Geographic/GeographicManager.php',
        'WP_Statistics\\Service\\Admin\\Geographic\\GeographicPage' => __DIR__ . '/../..' . '/src/Service/Admin/Geographic/GeographicPage.php',
        'WP_Statistics\\Service\\Admin\\Geographic\\Views\\SingleCountryView' => __DIR__ . '/../..' . '/src/Service/Admin/Geographic/Views/SingleCountryView.php',
        'WP_Statistics\\Service\\Admin\\Geographic\\Views\\TabsView' => __DIR__ . '/../..' . '/src/Service/Admin/Geographic/Views/TabsView.php',
        'WP_Statistics\\Service\\Admin\\LicenseManagement\\ApiCommunicator' => __DIR__ . '/../..' . '/src/Service/Admin/LicenseManagement/ApiCommunicator.php',
        'WP_Statistics\\Service\\Admin\\LicenseManagement\\LicenseHelper' => __DIR__ . '/../..' . '/src/Service/Admin/LicenseManagement/LicenseHelper.php',
        'WP_Statistics\\Service\\Admin\\LicenseManagement\\LicenseManagementManager' => __DIR__ . '/../..' . '/src/Service/Admin/LicenseManagement/LicenseManagementManager.php',
        'WP_Statistics\\Service\\Admin\\LicenseManagement\\LicenseManagerDataProvider' => __DIR__ . '/../..' . '/src/Service/Admin/LicenseManagement/LicenseManagerDataProvider.php',
        'WP_Statistics\\Service\\Admin\\LicenseManagement\\LicenseManagerPage' => __DIR__ . '/../..' . '/src/Service/Admin/LicenseManagement/LicenseManagerPage.php',
        'WP_Statistics\\Service\\Admin\\LicenseManagement\\LicenseMigration' => __DIR__ . '/../..' . '/src/Service/Admin/LicenseManagement/LicenseMigration.php',
        'WP_Statistics\\Service\\Admin\\LicenseManagement\\Plugin\\PluginActions' => __DIR__ . '/../..' . '/src/Service/Admin/LicenseManagement/Plugin/PluginActions.php',
        'WP_Statistics\\Service\\Admin\\LicenseManagement\\Plugin\\PluginDecorator' => __DIR__ . '/../..' . '/src/Service/Admin/LicenseManagement/Plugin/PluginDecorator.php',
        'WP_Statistics\\Service\\Admin\\LicenseManagement\\Plugin\\PluginHandler' => __DIR__ . '/../..' . '/src/Service/Admin/LicenseManagement/Plugin/PluginHandler.php',
        'WP_Statistics\\Service\\Admin\\LicenseManagement\\Plugin\\PluginHelper' => __DIR__ . '/../..' . '/src/Service/Admin/LicenseManagement/Plugin/PluginHelper.php',
        'WP_Statistics\\Service\\Admin\\LicenseManagement\\Plugin\\PluginUpdater' => __DIR__ . '/../..' . '/src/Service/Admin/LicenseManagement/Plugin/PluginUpdater.php',
        'WP_Statistics\\Service\\Admin\\LicenseManagement\\Views\\LockedMiniChartView' => __DIR__ . '/../..' . '/src/Service/Admin/LicenseManagement/Views/LockedMiniChartView.php',
        'WP_Statistics\\Service\\Admin\\LicenseManagement\\Views\\LockedRealTimeStatView' => __DIR__ . '/../..' . '/src/Service/Admin/LicenseManagement/Views/LockedRealTimeStatView.php',
        'WP_Statistics\\Service\\Admin\\LicenseManagement\\Views\\TabsView' => __DIR__ . '/../..' . '/src/Service/Admin/LicenseManagement/Views/TabsView.php',
        'WP_Statistics\\Service\\Admin\\MiniChart\\MiniChartHelper' => __DIR__ . '/../..' . '/src/Service/Admin/MiniChart/MiniChartHelper.php',
        'WP_Statistics\\Service\\Admin\\ModalHandler\\Modal' => __DIR__ . '/../..' . '/src/Service/Admin/ModalHandler/Modal.php',
        'WP_Statistics\\Service\\Admin\\NoticeHandler\\GeneralNotices' => __DIR__ . '/../..' . '/src/Service/Admin/NoticeHandler/GeneralNotices.php',
        'WP_Statistics\\Service\\Admin\\NoticeHandler\\Notice' => __DIR__ . '/../..' . '/src/Service/Admin/NoticeHandler/Notice.php',
        'WP_Statistics\\Service\\Admin\\PageInsights\\PageInsightsDataProvider' => __DIR__ . '/../..' . '/src/Service/Admin/PageInsights/PageInsightsDataProvider.php',
        'WP_Statistics\\Service\\Admin\\PageInsights\\PageInsightsManager' => __DIR__ . '/../..' . '/src/Service/Admin/PageInsights/PageInsightsManager.php',
        'WP_Statistics\\Service\\Admin\\PageInsights\\PageInsightsPage' => __DIR__ . '/../..' . '/src/Service/Admin/PageInsights/PageInsightsPage.php',
        'WP_Statistics\\Service\\Admin\\PageInsights\\Views\\TabsView' => __DIR__ . '/../..' . '/src/Service/Admin/PageInsights/Views/TabsView.php',
        'WP_Statistics\\Service\\Admin\\Posts\\HitColumnHandler' => __DIR__ . '/../..' . '/src/Service/Admin/Posts/HitColumnHandler.php',
        'WP_Statistics\\Service\\Admin\\Posts\\PostSummaryDataProvider' => __DIR__ . '/../..' . '/src/Service/Admin/Posts/PostSummaryDataProvider.php',
        'WP_Statistics\\Service\\Admin\\Posts\\PostsManager' => __DIR__ . '/../..' . '/src/Service/Admin/Posts/PostsManager.php',
        'WP_Statistics\\Service\\Admin\\Posts\\WordCountService' => __DIR__ . '/../..' . '/src/Service/Admin/Posts/WordCountService.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\Abstracts\\BaseAudit' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/Audits/Abstracts/BaseAudit.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\Abstracts\\ResolvableAudit' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/Audits/Abstracts/ResolvableAudit.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\AnonymizeIpAddress' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/Audits/AnonymizeIpAddress.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\HashIpAddress' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/Audits/HashIpAddress.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\RecordUserPageVisits' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/Audits/RecordUserPageVisits.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\StoreUserAgentString' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/Audits/StoreUserAgentString.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\StoredUserAgentStringData' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/Audits/StoredUserAgentStringData.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\StoredUserIdData' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/Audits/StoredUserIdData.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Audits\\UnhashedIpAddress' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/Audits/UnhashedIpAddress.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Faqs\\AbstractFaq' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/Faqs/AbstractFaq.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Faqs\\RequireConsent' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/Faqs/RequireConsent.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Faqs\\RequireCookieBanner' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/Faqs/RequireCookieBanner.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Faqs\\RequireMention' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/Faqs/RequireMention.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\Faqs\\TransferData' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/Faqs/TransferData.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\PrivacyAuditController' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/PrivacyAuditController.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\PrivacyAuditDataProvider' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/PrivacyAuditDataProvider.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\PrivacyAuditManager' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/PrivacyAuditManager.php',
        'WP_Statistics\\Service\\Admin\\PrivacyAudit\\PrivacyAuditPage' => __DIR__ . '/../..' . '/src/Service/Admin/PrivacyAudit/PrivacyAuditPage.php',
        'WP_Statistics\\Service\\Admin\\Referrals\\ReferralsDataProvider' => __DIR__ . '/../..' . '/src/Service/Admin/Referrals/ReferralsDataProvider.php',
        'WP_Statistics\\Service\\Admin\\Referrals\\ReferralsManager' => __DIR__ . '/../..' . '/src/Service/Admin/Referrals/ReferralsManager.php',
        'WP_Statistics\\Service\\Admin\\Referrals\\ReferralsPage' => __DIR__ . '/../..' . '/src/Service/Admin/Referrals/ReferralsPage.php',
        'WP_Statistics\\Service\\Admin\\Referrals\\Views\\TabsView' => __DIR__ . '/../..' . '/src/Service/Admin/Referrals/Views/TabsView.php',
        'WP_Statistics\\Service\\Admin\\SiteHealthInfo' => __DIR__ . '/../..' . '/src/Service/Admin/SiteHealthInfo.php',
        'WP_Statistics\\Service\\Admin\\VisitorInsights\\Views\\SingleVisitorView' => __DIR__ . '/../..' . '/src/Service/Admin/VisitorInsights/Views/SingleVisitorView.php',
        'WP_Statistics\\Service\\Admin\\VisitorInsights\\Views\\TabsView' => __DIR__ . '/../..' . '/src/Service/Admin/VisitorInsights/Views/TabsView.php',
        'WP_Statistics\\Service\\Admin\\VisitorInsights\\VisitorInsightsDataProvider' => __DIR__ . '/../..' . '/src/Service/Admin/VisitorInsights/VisitorInsightsDataProvider.php',
        'WP_Statistics\\Service\\Admin\\VisitorInsights\\VisitorInsightsManager' => __DIR__ . '/../..' . '/src/Service/Admin/VisitorInsights/VisitorInsightsManager.php',
        'WP_Statistics\\Service\\Admin\\VisitorInsights\\VisitorInsightsPage' => __DIR__ . '/../..' . '/src/Service/Admin/VisitorInsights/VisitorInsightsPage.php',
        'WP_Statistics\\Service\\Admin\\WebsitePerformance\\WebsitePerformanceDataProvider' => __DIR__ . '/../..' . '/src/Service/Admin/WebsitePerformance/WebsitePerformanceDataProvider.php',
        'WP_Statistics\\Service\\Analytics\\AnalyticsController' => __DIR__ . '/../..' . '/src/Service/Analytics/AnalyticsController.php',
        'WP_Statistics\\Service\\Analytics\\AnalyticsManager' => __DIR__ . '/../..' . '/src/Service/Analytics/AnalyticsManager.php',
        'WP_Statistics\\Service\\Analytics\\DeviceDetection\\DeviceHelper' => __DIR__ . '/../..' . '/src/Service/Analytics/DeviceDetection/DeviceHelper.php',
        'WP_Statistics\\Service\\Analytics\\DeviceDetection\\UserAgent' => __DIR__ . '/../..' . '/src/Service/Analytics/DeviceDetection/UserAgent.php',
        'WP_Statistics\\Service\\Analytics\\DeviceDetection\\UserAgentService' => __DIR__ . '/../..' . '/src/Service/Analytics/DeviceDetection/UserAgentService.php',
        'WP_Statistics\\Service\\Analytics\\Referrals\\Referrals' => __DIR__ . '/../..' . '/src/Service/Analytics/Referrals/Referrals.php',
        'WP_Statistics\\Service\\Analytics\\Referrals\\ReferralsDatabase' => __DIR__ . '/../..' . '/src/Service/Analytics/Referrals/ReferralsDatabase.php',
        'WP_Statistics\\Service\\Analytics\\Referrals\\ReferralsParser' => __DIR__ . '/../..' . '/src/Service/Analytics/Referrals/ReferralsParser.php',
        'WP_Statistics\\Service\\Analytics\\Referrals\\SourceChannels' => __DIR__ . '/../..' . '/src/Service/Analytics/Referrals/SourceChannels.php',
        'WP_Statistics\\Service\\Analytics\\Referrals\\SourceDetector' => __DIR__ . '/../..' . '/src/Service/Analytics/Referrals/SourceDetector.php',
        'WP_Statistics\\Service\\Analytics\\VisitorProfile' => __DIR__ . '/../..' . '/src/Service/Analytics/VisitorProfile.php',
        'WP_Statistics\\Service\\Charts\\AbstractChartDataProvider' => __DIR__ . '/../..' . '/src/Service/Charts/AbstractChartDataProvider.php',
        'WP_Statistics\\Service\\Charts\\ChartDataProviderFactory' => __DIR__ . '/../..' . '/src/Service/Charts/ChartDataProviderFactory.php',
        'WP_Statistics\\Service\\Charts\\DataProvider\\AuthorsPostViewsChartDataProvider' => __DIR__ . '/../..' . '/src/Service/Charts/DataProvider/AuthorsPostViewsChartDataProvider.php',
        'WP_Statistics\\Service\\Charts\\DataProvider\\PerformanceChartDataProvider' => __DIR__ . '/../..' . '/src/Service/Charts/DataProvider/PerformanceChartDataProvider.php',
        'WP_Statistics\\Service\\Charts\\DataProvider\\PlatformChartDataProvider' => __DIR__ . '/../..' . '/src/Service/Charts/DataProvider/PlatformChartDataProvider.php',
        'WP_Statistics\\Service\\Charts\\DataProvider\\PublishOverviewChartDataProvider' => __DIR__ . '/../..' . '/src/Service/Charts/DataProvider/PublishOverviewChartDataProvider.php',
        'WP_Statistics\\Service\\Charts\\DataProvider\\SearchEngineChartDataProvider' => __DIR__ . '/../..' . '/src/Service/Charts/DataProvider/SearchEngineChartDataProvider.php',
        'WP_Statistics\\Service\\Charts\\DataProvider\\TrafficChartDataProvider' => __DIR__ . '/../..' . '/src/Service/Charts/DataProvider/TrafficChartDataProvider.php',
        'WP_Statistics\\Service\\Charts\\Traits\\BarChartResponseTrait' => __DIR__ . '/../..' . '/src/Service/Charts/Traits/BarChartResponseTrait.php',
        'WP_Statistics\\Service\\Charts\\Traits\\BaseChartResponseTrait' => __DIR__ . '/../..' . '/src/Service/Charts/Traits/BaseChartResponseTrait.php',
        'WP_Statistics\\Service\\Charts\\Traits\\LineChartResponseTrait' => __DIR__ . '/../..' . '/src/Service/Charts/Traits/LineChartResponseTrait.php',
        'WP_Statistics\\Service\\Geolocation\\AbstractGeoIPProvider' => __DIR__ . '/../..' . '/src/Service/Geolocation/AbstractGeoIPProvider.php',
        'WP_Statistics\\Service\\Geolocation\\GeoServiceProviderInterface' => __DIR__ . '/../..' . '/src/Service/Geolocation/GeoServiceProviderInterface.php',
        'WP_Statistics\\Service\\Geolocation\\GeolocationFactory' => __DIR__ . '/../..' . '/src/Service/Geolocation/GeolocationFactory.php',
        'WP_Statistics\\Service\\Geolocation\\GeolocationService' => __DIR__ . '/../..' . '/src/Service/Geolocation/GeolocationService.php',
        'WP_Statistics\\Service\\Geolocation\\Provider\\DbIpProvider' => __DIR__ . '/../..' . '/src/Service/Geolocation/Provider/DbIpProvider.php',
        'WP_Statistics\\Service\\Geolocation\\Provider\\MaxmindGeoIPProvider' => __DIR__ . '/../..' . '/src/Service/Geolocation/Provider/MaxmindGeoIPProvider.php',
        'WP_Statistics\\Service\\HooksManager' => __DIR__ . '/../..' . '/src/Service/HooksManager.php',
        'WP_Statistics\\Service\\Integrations\\IntegrationHelper' => __DIR__ . '/../..' . '/src/Service/Integrations/IntegrationHelper.php',
        'WP_Statistics\\Service\\Integrations\\IntegrationsManager' => __DIR__ . '/../..' . '/src/Service/Integrations/IntegrationsManager.php',
        'WP_Statistics\\Service\\Integrations\\Plugins\\AbstractIntegration' => __DIR__ . '/../..' . '/src/Service/Integrations/Plugins/AbstractIntegration.php',
        'WP_Statistics\\Service\\Integrations\\Plugins\\RealCookieBanner' => __DIR__ . '/../..' . '/src/Service/Integrations/Plugins/RealCookieBanner.php',
        'WP_Statistics\\Service\\Integrations\\Plugins\\WpConsentApi' => __DIR__ . '/../..' . '/src/Service/Integrations/Plugins/WpConsentApi.php',
        'WP_Statistics\\Traits\\ObjectCacheTrait' => __DIR__ . '/../..' . '/src/Traits/ObjectCacheTrait.php',
        'WP_Statistics\\Traits\\TransientCacheTrait' => __DIR__ . '/../..' . '/src/Traits/TransientCacheTrait.php',
        'WP_Statistics\\Utils\\Query' => __DIR__ . '/../..' . '/src/Utils/Query.php',
        'WP_Statistics\\Utils\\Request' => __DIR__ . '/../..' . '/src/Utils/Request.php',
        'WP_Statistics\\Utils\\Signature' => __DIR__ . '/../..' . '/src/Utils/Signature.php',
        'WP_Statistics\\Utils\\Url' => __DIR__ . '/../..' . '/src/Utils/Url.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0c58b92c1859559503021091c91f6640::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0c58b92c1859559503021091c91f6640::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit0c58b92c1859559503021091c91f6640::$classMap;

        }, null, ClassLoader::class);
    }
}
