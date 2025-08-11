<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings;

use InvalidArgumentException;
use WPTechnix\WPSettings\Interfaces\FieldInterface;

/**
 * Creates instances of field objects based on their type.
 */
final class FieldFactory
{
    /**
     * A map of field types to their corresponding class names.
     *
     * @var array<string, class-string<FieldInterface>>
     */
    private array $fieldMap;

    /**
     * FieldFactory constructor.
     *
     * Initializes the map of supported field types. This can be extended
     * programmatically if needed.
     */
    public function __construct()
    {
        $this->fieldMap = [
            'text'        => Fields\TextField::class,
        ];
    }

    /**
     * Creates a field object based on its type.
     *
     * @param string               $type   The field type identifier (e.g., 'text', 'toggle').
     * @param array<string, mixed> $config The configuration for the field.
     * @return FieldInterface The instantiated field object.
     * @throws InvalidArgumentException If the requested field type is not supported.
     */
    public function create(string $type, array $config): FieldInterface
    {
        if (!isset($this->fieldMap[$type])) {
            throw new InvalidArgumentException("Unsupported field type: {$type}");
        }

        $className = $this->fieldMap[$type];

        return new $className($config);
    }

    /**
     * Get the list of all supported field type keys.
     *
     * @return string[] An array of supported type identifiers.
     */
    public function getSupportedTypes(): array
    {
        return array_keys($this->fieldMap);
    }
}
