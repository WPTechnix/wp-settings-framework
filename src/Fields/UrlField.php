<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * URL Field Class
 */
final class UrlField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        $defaultAttributes = ['class' => 'regular-text'];

        $mergedAttributes = array_merge(
            $defaultAttributes,
            $attributes
        );

        printf(
            '<input type="url" id="%s" name="%s" value="%s" %s />',
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
        return esc_url_raw((string) $value);
    }
}
