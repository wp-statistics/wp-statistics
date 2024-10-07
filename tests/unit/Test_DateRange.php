<?php

use WP_Statistics\Components\DateRange;

class Test_DateRange extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
    }

    public function test_validate() {
        $this->assertTrue(DateRange::validate('30days'));
        $this->assertTrue(DateRange::validate(['from' => '2024-01-01', 'to' => '2024-01-31']));
        $this->assertFalse(DateRange::validate('invalid_period'));
        $this->assertFalse(DateRange::validate(['from' => '2024-01-01']));
        $this->assertFalse(DateRange::validate(''));
    }

    public function test_retrieve_default_range() {
        $retrieved = DateRange::retrieve();

        $this->assertEquals('period', $retrieved['type']);
        $this->assertEquals('30days', $retrieved['value']);
    }

    public function test_getPeriod() {

        $range = [
            'from'  => date('Y-m-d', strtotime('first day of this month')),
            'to'    => date('Y-m-d', strtotime('last day of this month'))
        ];
        $period = DateRange::get('this_month');
        $this->assertEquals($range, $period);
    }

    public function test_getPeriod_excluding_today() {

        $range = [
            'from'  => date('Y-m-d', strtotime('first day of this month')),
            'to'    => date('Y-m-d', strtotime('last day of this month - 1 day'))
        ];
        $period = DateRange::get('this_month', true);
        $this->assertEquals($range, $period);
    }

    public function test_getPrevPeriod() {
        $prevPeriodRange = [
            'from'  => date('Y-m-d', strtotime('first day of last month')),
            'to'    => date('Y-m-d', strtotime('last day of last month'))
        ];
        $prevPeriod = DateRange::getPrevPeriod('this_month');
        $this->assertEquals($prevPeriodRange, $prevPeriod);
    }

    public function test_getPeriodFromRange() {
        $range = [
            'from'  => date('Y-m-d'),
            'to'    => date('Y-m-d')
        ];
        $this->assertEquals('today', DateRange::getPeriodFromRange($range));

        $range = [
            'from'  => date('Y-m-d', strtotime('first day of this month')),
            'to'    => date('Y-m-d', strtotime('last day of this month'))
        ];
        $this->assertEquals('this_month', DateRange::getPeriodFromRange($range));
    }

    public function test_compare() {
        $this->assertTrue(DateRange::compare('2024-10-07', '=', 'today'));
        $this->assertTrue(DateRange::compare('2024-10-15', 'in', 'this_month'));
        $this->assertTrue(DateRange::compare('2024-09-15', '!=', '2024-10-15'));
        $this->assertTrue(DateRange::compare('2024-09-15', '<', '2024-10-15'));
        $this->assertFalse(DateRange::compare('2024-10-15', '<', '2024-09-15'));
    }
}