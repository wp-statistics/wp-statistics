<?php
namespace WP_Statistics\Service\ThirdParty;

use WP_Statistics\Service\ThirdParty\RankMath\RankMath;

/**
 * ThirdPartyServices is a factory class that creates instances of third-party services.
*/
class ThirdPartyServices
{
    public static function rankMath()
    {
        return new RankMath();
    }
}