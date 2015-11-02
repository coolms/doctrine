<?php
/**
 * CoolMS2 Doctrine Module (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform,
    Doctrine\DBAL\Types\ConversionException,
    Doctrine\DBAL\Types\Type,
    Litipk\BigNumbers\Decimal;

class DecimalObject extends Type
{
    const DECIMAL_OBJECT = 'decimal_object';

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::DECIMAL_OBJECT;
    }

    /**
     * {@inheritDoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $typeDeclaration  = $platform->getDecimalTypeDeclarationSQL($fieldDeclaration);
        $typeDeclaration .= " COMMENT '(DC2Type:decimal_object)'";

        return $typeDeclaration;
    }

    /**
     * {@inheritDoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof Decimal) {
            throw new DBALException(sprintf(
                'Value is not an instance of %s',
                DecimalObject::class
            ));
        }

        return (string) $value;
    }

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        try {
            return Decimal::create($value);
        } catch (\Exception $e) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), '0.0');
        }
    }
}
