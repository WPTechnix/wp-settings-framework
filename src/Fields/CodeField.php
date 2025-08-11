<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Code Field Class
 */
final class CodeField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        $language = $this->config['language'] ?? 'css';

        $htmlPrefix = $this->config['htmlPrefix'] ?? 'wptechnix-settings';

        $defaultAttributes = [
            'rows' => 10,
            'cols' => 50,
            'class' => "large-text {$htmlPrefix}-code-editor",
            'data-language' => $language
        ];

        $mergedAttributes = array_merge($defaultAttributes, $attributes);

        printf(
            '<textarea id="%s" name="%s" %s>%s</textarea>',
            esc_attr($this->config['id']),
            esc_attr($this->config['name']),
            $this->buildAttributesString($mergedAttributes),
            esc_textarea((string) $value)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function sanitize(mixed $value): string
    {
        // Basic sanitization for code to preserve its structure.
        return str_replace(["\x00", "\r\n", "\r"], ['', "\n", "\n"], (string) $value);
    }
}
