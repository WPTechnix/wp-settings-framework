<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings;

use InvalidArgumentException;
use WPTechnix\WPSettings\Interfaces\FieldInterface;
use WPTechnix\WPSettings\Interfaces\ConfigInterface;

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
            'email'       => Fields\EmailField::class,
            'number'      => Fields\NumberField::class,
            'password'    => Fields\PasswordField::class,
            'url'         => Fields\UrlField::class,
            'checkbox'    => Fields\CheckboxField::class,
            'toggle'      => Fields\ToggleField::class,
            'select'      => Fields\SelectField::class,
            'multiselect' => Fields\MultiSelectField::class,
            'radio'       => Fields\RadioField::class,
            'buttongroup' => Fields\ButtonGroupField::class,
            'textarea'    => Fields\TextareaField::class,
            'code'        => Fields\CodeField::class,
            'color'       => Fields\ColorField::class,
            'date'        => Fields\DateField::class,
            'datetime'    => Fields\DateTimeField::class,
            'time'        => Fields\TimeField::class,
            'range'       => Fields\RangeField::class,
            'media'       => Fields\MediaField::class,
            'description' => Fields\DescriptionField::class,
        ];
    }

    /**
     * Creates a field object based on its type.
     *
     * @param string $type The field type identifier (e.g., 'text', 'toggle').
     * @param array<string, mixed>|ConfigInterface $config The configuration for the field.
     *
     * @return FieldInterface The instantiated field object.
     * @throws InvalidArgumentException If the requested field type is not supported.
     */
    public function create(string $type, array|ConfigInterface $config): FieldInterface
    {
        if (! isset($this->fieldMap[$type])) {
            throw new InvalidArgumentException("Unsupported field type: {$type}");
        }

        $className = $this->fieldMap[$type];

        return new $className(new Config($config));
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
