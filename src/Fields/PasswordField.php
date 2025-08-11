<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Password Field Class
 */
final class PasswordField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        $defaultAttributes = ['class' => 'regular-text'];
        $mergedAttributes = array_merge($defaultAttributes, $attributes);
        printf(
            '<input type="password" id="%s" name="%s" value="%s" %s />',
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
        // Passwords should not be altered during sanitization beyond
        // basic string conversion.
        return (string) $value;
    }
}
