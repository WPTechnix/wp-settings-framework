<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Text Field Class
 */
final class TextField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        $defaultAttributes = ['class' => 'regular-text'];

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
