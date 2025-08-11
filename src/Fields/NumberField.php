<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Number Field Class
 */
final class NumberField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        $defaultAttributes = ['class' => 'regular-text'];
        $mergedAttributes = array_merge($defaultAttributes, $attributes);
        printf(
            '<input type="number" id="%s" name="%s" value="%s" %s />',
            esc_attr($this->config['id']),
            esc_attr($this->config['name']),
            esc_attr((string) $value),
            $this->buildAttributesString($mergedAttributes)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function sanitize(mixed $value): int|float
    {
        if (!is_numeric($value)) {
            return 0;
        }
        $numericValue = $value + 0; // Cast to number
        return is_float($numericValue) ? (float) $value : (int) $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultValue(): int
    {
        return (int) ($this->config['default'] ?? 0);
    }
}
