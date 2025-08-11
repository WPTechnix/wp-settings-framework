<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Radio Field Class
 */
final class RadioField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        $options = $this->config['options'] ?? [];
        foreach ($options as $optionValue => $optionLabel) {
            $radioId = $this->config['id'] . '_' . $optionValue;

            printf(
                '<label for="%s">
                           <input type="radio" id="%s" name="%s" value="%s" %s %s />
                           %s
                       </label><br />',
                esc_attr($radioId),
                esc_attr($radioId),
                esc_attr($this->config['name']),
                esc_attr((string) $optionValue),
                checked((string) $value, (string) $optionValue, false),
                $this->buildAttributesString($attributes),
                esc_html($optionLabel)
            );
        }
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
