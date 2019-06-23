<?php

namespace App\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

/**
 * A DateTimeType used to always convert \DateTime objects into UTC versions.
 *
 * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/cookbook/working-with-datetime.html
 */
class UTCDateTimeType extends DateTimeType
{
    static private $utc;

    /**
     * @param $value
     * @param AbstractPlatform $platform
     *
     * @throws ConversionException
     *
     * @return mixed|string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof \DateTime) {
            $value->setTimezone(new \DateTimeZone(\DateTimeZone::UTC));
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     *
     * @throws ConversionException
     *
     * @return bool|\DateTime|false|mixed
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value || $value instanceof \DateTime) {
            return $value;
        }

        $converted = \DateTime::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            self::$utc ? self::$utc : self::$utc = new \DateTimeZone('UTC')
        );

        if (! $converted) {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                $platform->getDateTimeFormatString()
            );
        }

        return $converted;
    }
}
