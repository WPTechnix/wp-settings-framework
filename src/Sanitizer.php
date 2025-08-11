<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings;

use InvalidArgumentException;

/**
 * Sanitizes the settings array before it is saved to the database.
 *
 * This class ensures that all data conforms to the expected format and is
 * safe for storage. It iterates through all registered fields and applies
 * the appropriate sanitization logic.
 */
final class Sanitizer
{
    /**
     * The full settings configuration array.
     *
     * @var array<string, mixed>
     */
    private array $config;

    /**
     * The FieldFactory instance for creating field objects.
     *
     * @var FieldFactory
     */
    private FieldFactory $fieldFactory;

    /**
     * Sanitizer constructor.
     *
     * @param array<string, mixed> $config       The settings configuration.
     * @param FieldFactory         $fieldFactory The factory for creating field objects.
     */
    public function __construct(array $config, FieldFactory $fieldFactory)
    {
        $this->config = $config;
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * Sanitizes the entire settings array.
     *
     * This is the main callback for the 'sanitize_callback' argument in
     * `register_setting`. It processes the raw input from the $_POST array.
     *
     * @param mixed $input The raw input from the form submission.
     * @return array<string, mixed> The sanitized settings array ready for saving.
     */
    public function sanitize(mixed $input): array
    {
        if (!is_array($input)) {
            return [];
        }

        $sanitized = [];
        $fields = $this->config['fields'] ?? [];

        foreach ($fields as $fieldId => $fieldConfig) {
            // Description fields have no value and should be skipped.
            if ('description' === $fieldConfig['type']) {
                continue;
            }

            $rawValue = $input[$fieldId] ?? null;

            try {
                $field = $this->fieldFactory->create($fieldConfig['type'], $fieldConfig);
                $defaultValue = $field->getDefaultValue();

                // If value is not submitted, use default.
                if (null === $rawValue) {
                    $sanitized[$fieldId] = $defaultValue;
                    continue;
                }

                // Apply the field's specific sanitization method.
                $sanitizedValue = $field->sanitize($rawValue);

                // Apply custom validation callback if it exists.
                if (isset($fieldConfig['validate_callback']) && is_callable($fieldConfig['validate_callback'])) {
                    if (!call_user_func($fieldConfig['validate_callback'], $sanitizedValue)) {
                        // If validation fails, revert to the default value.
                        $sanitizedValue = $defaultValue;
                        add_settings_error(
                            $this->config['optionGroup'],
                            'validation_error_' . $fieldId,
                            'Invalid value provided for ' . $fieldConfig['label'] . '. Reverted to default.',
                            'error'
                        );
                    }
                }

                $sanitized[$fieldId] = $sanitizedValue;
            } catch (InvalidArgumentException) {
                // This should not happen if types are validated on creation.
                // For safety, we use the default value.
                $sanitized[$fieldId] = $fieldConfig['default'] ?? '';
            }
        }

        return $sanitized;
    }
}
