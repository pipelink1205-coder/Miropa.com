<?php

namespace Tests\Unit;

use App\Support\VerificationDevMode;
use Tests\TestCase;

class VerificationDevModeTest extends TestCase
{
    public function test_exposes_sms_codes_when_driver_is_log_in_production(): void
    {
        config(['sms.driver' => 'log', 'sms.expose_code' => null]);
        app()->detectEnvironment(fn () => 'production');

        $this->assertTrue(VerificationDevMode::exposesSmsCodes());
    }

    public function test_exposes_sms_codes_when_driver_is_empty_string(): void
    {
        config(['sms.driver' => '', 'sms.expose_code' => null]);
        app()->detectEnvironment(fn () => 'production');

        $this->assertTrue(VerificationDevMode::exposesSmsCodes());
    }

    public function test_exposes_sms_codes_when_explicitly_enabled(): void
    {
        config(['sms.driver' => 'twilio', 'sms.expose_code' => 'true']);

        $this->assertTrue(VerificationDevMode::exposesSmsCodes());
    }

    public function test_hides_sms_codes_when_using_twilio_without_override(): void
    {
        config(['sms.driver' => 'twilio', 'sms.expose_code' => null]);

        $this->assertFalse(VerificationDevMode::exposesSmsCodes());
    }
}
