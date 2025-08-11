<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Multi Select Field
 */
final class MultiSelectField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        $htmlPrefix = $this->config['htmlPrefix'] ?? 'wptechnix-settings';
        $defaultAttributes = ['multiple' => 'multiple', 'class' => "{$htmlPrefix}-select2-field"];
        $mergedAttributes = array_merge($defaultAttributes, $attributes);
        $selectedValues = is_array($value) ? array_map('strval', $value) : [];

        printf(
            '<select id="%s" name="%s[]" %s>',
            esc_attr($this->config['id']),
            esc_attr($this->config['name']),
            $this->buildAttributesString($mergedAttributes)
        );

        $options = $this->config['options'] ?? [];
        foreach ($options as $optionValue => $optionLabel) {
            $selected = in_array((string) $optionValue, $selectedValues, true) ? 'selected="selected"' : '';
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr((string) $optionValue),
                $selected,
                esc_html($optionLabel)
            );
        }
        echo '</select>';
    }

    /**
     * {@inheritDoc}
     *
     * @return array<int, string>
     */
    public function sanitize(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        $allowedValues = array_keys($this->config['options'] ?? []);
        $sanitized = [];
        foreach ($value as $item) {
            if (in_array((string) $item, $allowedValues, true)) {
                $sanitized[] = (string) $item;
            }
        }
        return $sanitized;
    }

    /**
     * {@inheritDoc}
     *
     * @return array<int, string>
     */
    public function getDefaultValue(): array
    {
        return $this->config['default'] ?? [];
    }
}
