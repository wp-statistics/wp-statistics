<?php

namespace WP_Statistics\Tests\Notification;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Decorators\NotificationDecorator;
use WP_Statistics\Service\Admin\Notification\NotificationFactory;
use WP_Statistics\Service\Admin\Notification\NotificationProcessor;
use WP_UnitTestCase;

class Test_NotificationPromo extends WP_UnitTestCase
{
    private function decorate(array $extra, array $overrides = [])
    {
        return new NotificationDecorator((object)array_merge([
            'id'               => 1,
            'title'            => 'Sale',
            'background_color' => 'promo',
            'extra_data'       => $extra,
        ], $overrides));
    }

    public function test_promo_cards_come_first_and_keep_the_order_of_the_rest()
    {
        $items = [
            ['id' => 1, 'background_color' => 'info'],
            ['id' => 2, 'background_color' => 'promo'],
            ['id' => 3, 'background_color' => 'warning'],
            ['id' => 4, 'background_color' => 'promo'],
        ];
        $local = ['id' => 'license-alert', 'background_color' => 'warning'];

        $ids = array_column(NotificationFactory::promoFirst($items, $local), 'id');

        $this->assertSame([2, 4, 'license-alert', 1, 3], $ids);
        $this->assertSame([1, 3], array_column(NotificationFactory::promoFirst([$items[0], $items[2]]), 'id'));
    }

    public function test_countdown_is_off_unless_extra_data_turns_it_on()
    {
        $this->assertFalse($this->decorate([])->showCountdown());
        $this->assertFalse($this->decorate(['countdown' => '0'])->showCountdown());
        $this->assertFalse($this->decorate(['countdown' => ''])->showCountdown());
        $this->assertFalse($this->decorate(['countdown' => 'off'])->showCountdown());
        $this->assertTrue($this->decorate(['countdown' => '1'])->showCountdown());
        $this->assertTrue($this->decorate(['countdown' => 'on'])->showCountdown());
        $this->assertTrue($this->decorate(['countdown' => true])->showCountdown());
    }

    public function test_highlight_and_eyebrow_are_optional()
    {
        $this->assertNull($this->decorate([])->getHighlight());
        $this->assertNull($this->decorate([])->getEyebrow());
        $this->assertSame('30% OFF', $this->decorate(['highlight' => '30% OFF'])->getHighlight());
        $this->assertSame('Black Friday', $this->decorate(['eyebrow' => 'Black Friday'])->getEyebrow());
    }

    public function test_an_ended_promo_is_still_filtered_out_by_its_dates()
    {
        $items = [
            ['id' => 1, 'background_color' => 'promo', 'ends_at' => gmdate('Y-m-d\TH:i:s\Z', time() - HOUR_IN_SECONDS), 'extra_data' => ['countdown' => '1']],
            ['id' => 2, 'background_color' => 'promo', 'ends_at' => gmdate('Y-m-d\TH:i:s\Z', time() + HOUR_IN_SECONDS)],
        ];

        $this->assertSame([2], array_column(NotificationProcessor::filterNotificationsByTags($items), 'id'));
    }
}
