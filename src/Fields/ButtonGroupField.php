<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Button Group Field Class
 */
final class ButtonGroupField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        $htmlPrefix = $this->config['htmlPrefix'] ?? 'wptechnix-settings';

        printf(
            '<input type="hidden" name="%s" id="%s" value="%s" %s />',
            esc_attr($this->config['name']),
            esc_attr($this->config['id']),
            esc_attr((string) $value),
            $this->buildAttributesString($attributes)
        );

        printf('<div class="%s-buttongroup-container">', esc_attr($htmlPrefix));

        $options = $this->config['options'] ?? [];
        foreach ($options as $optionValue => $optionLabel) {
            $activeClass = ((string) $value === (string) $optionValue) ? ' active' : '';
            printf(
                '<button type="button" class="%s-buttongroup-option%s" data-value="%s">%s</button>',
                esc_attr($htmlPrefix),
                esc_attr($activeClass),
                esc_attr((string) $optionValue),
                esc_html($optionLabel)
            );
        }
        echo '</div>';
    }

    /**
     * {@inheritDoc}
     */
    public function sanitize(mixed $value): string
    {
        $allowedValues = array_keys($this->config['options'] ?? []);
        if (in_array((string) $value, $allowedValues, true)) {
            return (string) $value;
        }
        return '';
    }
}
