<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Toggle Field Class
 */
final class ToggleField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        $htmlPrefix = $this->config->get('htmlPrefix', 'wptechnix-settings');

        printf(
            '<label class="%s-toggle">
				<input type="checkbox" id="%s" name="%s" value="1" %s %s />
				<span class="%s-toggle-slider"></span>
			</label>',
            esc_attr($htmlPrefix),
            esc_attr($this->config->get('id')),
            esc_attr($this->config->get('name')),
            checked($value, true, false),
            $this->buildAttributesString($attributes),
            esc_attr($htmlPrefix)
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
        return (bool)($this->config->get('default', false));
    }
}
