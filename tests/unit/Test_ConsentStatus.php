<?php

use WP_Statistics\Service\Consent\ConsentStatus;

/**
 * @group consent
 */
class Test_ConsentStatus extends WP_UnitTestCase
{
    public function test_full_factory_returns_full_value()
    {
        $status = ConsentStatus::full();
        $this->assertEquals('full', $status->value());
    }

    public function test_anonymous_factory_returns_anonymous_value()
    {
        $status = ConsentStatus::anonymous();
        $this->assertEquals('anonymous', $status->value());
    }

    public function test_none_factory_returns_none_value()
    {
        $status = ConsentStatus::none();
        $this->assertEquals('none', $status->value());
    }

    public function test_from_string_creates_full()
    {
        $status = ConsentStatus::fromString('full');
        $this->assertEquals('full', $status->value());
    }

    public function test_from_string_creates_anonymous()
    {
        $status = ConsentStatus::fromString('anonymous');
        $this->assertEquals('anonymous', $status->value());
    }

    public function test_from_string_creates_none()
    {
        $status = ConsentStatus::fromString('none');
        $this->assertEquals('none', $status->value());
    }

    public function test_from_string_throws_on_invalid_value()
    {
        $this->expectException(\InvalidArgumentException::class);
        ConsentStatus::fromString('invalid');
    }

    public function test_full_should_track()
    {
        $this->assertTrue(ConsentStatus::full()->shouldTrack());
    }

    public function test_full_should_not_anonymize()
    {
        $this->assertFalse(ConsentStatus::full()->shouldAnonymize());
    }

    public function test_anonymous_should_track()
    {
        $this->assertTrue(ConsentStatus::anonymous()->shouldTrack());
    }

    public function test_anonymous_should_anonymize()
    {
        $this->assertTrue(ConsentStatus::anonymous()->shouldAnonymize());
    }

    public function test_none_should_not_track()
    {
        $this->assertFalse(ConsentStatus::none()->shouldTrack());
    }

    public function test_none_should_not_anonymize()
    {
        $this->assertFalse(ConsentStatus::none()->shouldAnonymize());
    }

    public function test_equals_same_status()
    {
        $this->assertTrue(ConsentStatus::full()->equals(ConsentStatus::full()));
    }

    public function test_equals_different_status()
    {
        $this->assertFalse(ConsentStatus::full()->equals(ConsentStatus::none()));
    }

    public function test_from_string_equals_factory()
    {
        $this->assertTrue(ConsentStatus::fromString('anonymous')->equals(ConsentStatus::anonymous()));
    }
}
