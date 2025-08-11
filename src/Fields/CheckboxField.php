<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Checkbox Field Class
 */
final class CheckboxField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        printf(
            '<input type="checkbox" id="%s" name="%s" value="1" %s %s />',
            esc_attr($this->config['id']),
            esc_attr($this->config['name']),
            checked($value, true, false),
            $this->buildAttributesString($attributes)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function sanitize(mixed $value): bool
    {
        return in_array($value, [true, 'true', 1, '1', 'on'], true);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultValue(): bool
    {
        return (bool) ($this->config['default'] ?? false);
    }
}
