<?php declare(strict_types=1);

namespace MyCLabs\Enum\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use MyCLabs\Enum\Enum;

/**
 * Base class to map enums to a VARCHAR column.
 */
abstract class StringEnumType extends Type
{
    /**
     * Implement this method and return the class name of your enum.
     */
    abstract public function getClassName(): string;

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        // Enum values are stored as VARCHAR
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?object
    {
        if ($value === null) {
            return null;
        }

        $className = $this->getClassName();

        return new $className($value);
    }

    /**
     * @param Enum|null $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return (string) $value->getValue();
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
