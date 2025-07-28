<?php

namespace Smsbox\Tests\Options\SMS;

use PHPUnit\Framework\TestCase;
use Smsbox\Enum\SMS\Charset;
use Smsbox\Enum\SMS\Day;
use Smsbox\Enum\SMS\Encoding;
use Smsbox\Enum\SMS\Mode;
use Smsbox\Enum\SMS\Strategy;
use Smsbox\Enum\SMS\Udh;
use Smsbox\Exception\SmsboxException;
use Smsbox\Options\SMS\SmsOptions;
use Symfony\Component\Intl\Countries;

/**
 * @internal
 */
class SmsOptionsTest extends TestCase
{
    public function testBasicOptions()
    {
        $options = (new SmsOptions())
            ->mode(Mode::EXPERT)
            ->strategy(Strategy::MARKETING)
            ->charset(Charset::UTF8)
            ->udh(Udh::DISABLED_CONCAT)
            ->maxParts(2)
            ->validity(100)
            ->sender('SENDER')
            ->coding(Encoding::AUTO)
            ->destIso('FR')
            ->allowVocal(true)
            ->callback(false);

        self::assertSame([
            'mode'        => Mode::EXPERT,
            'strategy'    => Strategy::MARKETING,
            'charset'     => Charset::UTF8,
            'udh'         => Udh::DISABLED_CONCAT,
            'max_parts'   => 2,
            'validity'    => 100,
            'sender'      => 'SENDER',
            'coding'      => Encoding::AUTO,
            'dest_iso'    => 'FR',
            'allow_vocal' => true,
            'callback'    => false,
        ], $options->toArray());
    }

    public function testInvalidIsoCountry()
    {
        if (!class_exists(Countries::class)) {
            $this->markTestSkipped('symfony/intl is required for this test.');
        }

        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('The country code "XX" is not valid.');

        (new SmsOptions())->destIso('XX');
    }

    public function testInvalidMode()
    {
        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('Invalid mode value');

        (new SmsOptions())->mode('UNKNOWN');
    }

    public function testInvalidStrategy()
    {
        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('Invalid strategy value');

        (new SmsOptions())->strategy(99);
    }

    public function testInvalidDateFormat()
    {
        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('The date must be in DD/MM/YYYY format.');

        (new SmsOptions())->date('2024-01-01');
    }

    public function testInvalidHourFormat()
    {
        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('Hour must be in HH:MM format.');

        (new SmsOptions())->hour('23h');
    }

    public function testDateTimeConflictsWithDate()
    {
        $this->expectException(SmsboxException::class);

        (new SmsOptions())
            ->dateTime(new \DateTime('+1 day'))
            ->date('01/01/2026');
    }

    public function testDateTimeInPast()
    {
        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('The given DateTime must be greater than the current date.');

        $yesterday = (new \DateTime('now', new \DateTimeZone('Europe/Paris')))->modify('-1 day');
        (new SmsOptions())->dateTime($yesterday);
    }

    public function testInvalidMaxParts()
    {
        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('The "max_parts" option must be an integer between 1 and 8');

        (new SmsOptions())->maxParts(10);
    }

    public function testInvalidValidity()
    {
        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('The "validity" option must be an integer between 5 and 1440');

        (new SmsOptions())->validity(3);
    }

    public function testInvalidDaysMinMax()
    {
        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('The minimum day must be before the maximum day or the same.');

        (new SmsOptions())->daysMinMax(Day::SUNDAY, Day::FRIDAY);
    }

    public function testInvalidHoursMinMax()
    {
        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('The minimum hour must be greater than 0 and lower than the maximum hour.');

        (new SmsOptions())->hoursMinMax(13, 10);
    }

    public function testMaxHourOutOfBounds()
    {
        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('The maximum hour must be lower or equal to 23.');

        (new SmsOptions())->hoursMinMax(0, 25);
    }
}
