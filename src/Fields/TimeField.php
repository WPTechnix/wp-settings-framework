<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Time Field Class
 */
final class TimeField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        $defaultAttributes = [
            'class' => 'regular-text flatpickr-time',
            'readonly' => 'readonly'
        ];

        $mergedAttributes = array_merge($defaultAttributes, $attributes);

        printf(
            '<input type="text" id="%s" name="%s" value="%s" %s />',
            esc_attr($this->config['id']),
            esc_attr($this->config['name']),
            esc_attr((string) $value),
            $this->buildAttributesString($mergedAttributes)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function sanitize(mixed $value): string
    {
        return sanitize_text_field((string) $value);
    }
}
