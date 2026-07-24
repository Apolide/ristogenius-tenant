<?php

namespace Tests\Unit;

use App\Rules\ValidPhoneNumber;
use App\Services\PhoneNumberService;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Tests\TestCase;

class PhoneNumberServiceTest extends TestCase
{
    public function test_it_normalizes_national_numbers_to_e164_using_the_selected_country(): void
    {
        $service = app(PhoneNumberService::class);

        $this->assertSame('+393331234567', $service->normalize('333 123 4567', 'IT'));
        $this->assertSame('+4930901820', $service->normalize('030 901820', 'DE'));
    }

    public function test_it_rejects_letters_and_invalid_lengths(): void
    {
        $service = app(PhoneNumberService::class);

        foreach (['phone ABC', '123'] as $phone) {
            try {
                $service->normalize($phone, 'IT');
                $this->fail("The invalid phone number {$phone} was accepted.");
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_validation_rule_uses_the_selected_country(): void
    {
        $valid = Validator::make(['phone' => '030 901820'], ['phone' => [new ValidPhoneNumber('DE')]]);
        $invalid = Validator::make(['phone' => '030 901820'], ['phone' => [new ValidPhoneNumber('US')]]);

        $this->assertFalse($valid->fails());
        $this->assertTrue($invalid->fails());
    }

    public function test_it_splits_an_e164_number_for_country_and_national_inputs(): void
    {
        $phone = app(PhoneNumberService::class)->split('+4930901820');

        $this->assertSame('DE', $phone['region']);
        $this->assertSame('030 901820', $phone['national']);
    }
}
