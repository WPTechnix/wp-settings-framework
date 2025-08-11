<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Range Field Class
 */
final class RangeField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        $htmlPrefix = $this->config['htmlPrefix'] ?? 'wptechnix-settings';
        $defaultAttributes = ['min' => 0, 'max' => 100, 'step' => 1, 'class' => "{$htmlPrefix}-enhanced-range-slider"];
        $mergedAttributes = array_merge($defaultAttributes, $attributes);
        $min = (int) ($mergedAttributes['min'] ?? 0);
        $max = (int) ($mergedAttributes['max'] ?? 100);
        $currentValue = $value ?? $min;

        printf('<div class="%s-enhanced-range-container">', esc_attr($htmlPrefix));
        printf(
            '<input type="range" id="%s" name="%s" value="%s" %s />',
            esc_attr($this->config['id']),
            esc_attr($this->config['name']),
            esc_attr((string) $currentValue),
            $this->buildAttributesString($mergedAttributes)
        );
        printf(
            '<input type="number" class="%s-range-value-input" value="%s" min="%d" max="%d" readonly />',
            esc_attr($htmlPrefix),
            esc_attr((string) $currentValue),
            $min,
            $max
        );
        echo '</div>';
    }

    /**
     * {@inheritDoc}
     */
    public function sanitize(mixed $value): int|float
    {
        if (!is_numeric($value)) {
            return 0;
        }
        $numericValue = $value + 0;
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
