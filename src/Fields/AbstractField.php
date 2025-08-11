<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

use WPTechnix\WPSettings\Interfaces\FieldInterface;

/**
 * Provides the basic structure and common functionality for all field types.
 */
abstract class AbstractField implements FieldInterface
{
    /**
     * The field's configuration properties.
     *
     * @var array{
     *     id: string,
     *     name: string,
     *     label: string,
     *     description: string,
     *     default?: mixed,
     *     options?: array<int|string, string>,
     *     attributes?: array<string, scalar>,
     *     sanitize_callback?: callable,
     *     validate_callback?: callable,
     *     conditional?: array{field: string, value: mixed, operator?: string}
     * }
     */
    protected array $config;

    /**
     * AbstractField constructor.
     *
     * @param array{
     *     id: string,
     *     name: string,
     *     label: string,
     *     description: string,
     *     default?: mixed,
     *     options?: array<int|string, string>,
     *     attributes?: array<string, scalar>,
     *     sanitize_callback?: callable,
     *     validate_callback?: callable,
     *     conditional?: array{field: string, value: mixed, operator?: string}
     * } $config The field configuration array.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Build an HTML attributes string from an array.
     *
     * This helper method constructs a valid HTML attribute string from an
     * associative array, with proper escaping.
     *
     * @param array<string, scalar> $attributes The array of attributes (key => value).
     * @return string The generated HTML attributes string.
     */
    protected function buildAttributesString(array $attributes): string
    {
        $attrParts = [];
        foreach ($attributes as $key => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $attrParts[] = esc_attr($key);
                }
            } else {
                $attrParts[] = sprintf('%s="%s"', esc_attr($key), esc_attr((string) $value));
            }
        }

        return implode(' ', $attrParts);
    }

    /**
     * Get the default value for the field.
     *
     * If a 'default' key is present in the field's configuration, it will be returned.
     * Otherwise, it provides a sensible default based on the expected data type.
     *
     * @return mixed The default value.
     */
    public function getDefaultValue(): mixed
    {
        if (array_key_exists('default', $this->config)) {
            return $this->config['default'];
        }

        // Fallback for fields that might be arrays.
        if (in_array(static::class, [MultiSelectField::class], true)) {
            return [];
        }

        return '';
    }
}
