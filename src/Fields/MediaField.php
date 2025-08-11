<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings\Fields;

/**
 * Media Field Class
 */
final class MediaField extends AbstractField
{
    /**
     * {@inheritDoc}
     */
    public function render(mixed $value, array $attributes): void
    {
        $htmlPrefix = $this->config['htmlPrefix'] ?? 'wptechnix-settings';
        $mediaUrl = '';
        $mediaId = absint($value);
        if ($mediaId > 0) {
            $mediaUrl = wp_get_attachment_url($mediaId);
        }

        printf(
            '<div class="%s-media-field-container">',
            esc_attr($htmlPrefix)
        );

        printf(
            '<input type="hidden" id="%s" name="%s" value="%s" %s />',
            esc_attr($this->config['id']),
            esc_attr($this->config['name']),
            esc_attr((string) $value),
            $this->buildAttributesString($attributes)
        );
        printf(
            '<button type="button" class="button %s-media-upload-button" data-field="%s">%s</button>',
            esc_attr($htmlPrefix),
            esc_attr($this->config['id']),
            esc_html__('Select Media', 'default')
        );

        if (!empty($mediaUrl)) {
            printf(
                ' <button type="button" class="button %s-media-remove-button" data-field="%s">%s</button>',
                esc_attr($htmlPrefix),
                esc_attr($this->config['id']),
                esc_html__('Remove', 'default')
            );
        }

        printf('<div class="%s-media-preview">', esc_attr($htmlPrefix));
        if (!empty($mediaUrl) && wp_attachment_is_image($mediaId)) {
            printf(
                '<img src="%s" alt="" style="max-width: 150px; height: auto; margin-top: 10px;" />',
                esc_url($mediaUrl)
            );
        } elseif (!empty($mediaUrl)) {
            $file = (string)get_attached_file($mediaId);
            $fileName = ! empty($file) ? basename($file) : '';
            printf('<p style="margin-top:10px;"><strong>File:</strong> %s</p>', esc_html($fileName));
        }
        echo '</div></div>';
    }

    /**
     * {@inheritDoc}
     */
    public function sanitize(mixed $value): int
    {
        return absint($value);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultValue(): int
    {
        return (int) ($this->config['default'] ?? 0);
    }
}
