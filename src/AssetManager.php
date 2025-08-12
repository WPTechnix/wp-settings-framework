<?php

declare(strict_types=1);

namespace WPTechnix\WPSettings;

/**
 * Handles the enqueueing and rendering of all static assets for the settings page.
 *
 * @noinspection JSUnusedLocalSymbols, CssUnusedSymbol
 *
 * @phpstan-import-type SettingsConfig from Settings
 */
final class AssetManager
{
    /**
     * Defines the external libraries that can be loaded.
     *
     * @var array<string, array{
     *     handle: string,
     *     script?: array{src: string, deps: string[], version: string, in_footer: bool},
     *     style?: array{src: string, deps: string[], version: string}
     * }>
     */
    private array $libraryPackages;

    /**
     * Maps field types to the library packages they require.
     *
     * @var array<string, string>
     */
    private array $fieldTypeToPackageMap;

    /**
     * AssetManager constructor.
     *
     * @param array $config The complete setting configuration array.
     * @phpstan-param SettingsConfig $config
     */
    public function __construct(protected array $config)
    {
        $customPackages = $this->config['assetPackages'] ?? [];

        $defaultPackages = [
            'select2' => [
                'handle' => 'select2',
                'script' => [
                    'src' => 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
                    'deps' => ['jquery'],
                    'version' => '4.0.13',
                    'in_footer' => true,
                ],
                'style'  => [
                    'src' => 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css',
                    'deps' => [],
                    'version' => '4.0.13',
                ],
            ],
            'flatpickr' => [
                'handle' => 'flatpickr',
                'script' => [
                    'src' => 'https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js',
                    'deps' => [],
                    'version' => '4.6.13',
                    'in_footer' => true,
                ],
                'style'  => [
                    'src' => 'https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css',
                    'deps' => [],
                    'version' => '4.6.13',
                ],
            ],
        ];

        $this->libraryPackages = array_replace_recursive($defaultPackages, $customPackages);

        $this->fieldTypeToPackageMap = [
            'select'      => 'select2',
            'multiselect' => 'select2',
            'date'        => 'flatpickr',
            'datetime'    => 'flatpickr',
            'time'        => 'flatpickr',
        ];
    }

    /**
     * Hooks the asset enqueueing method into WordPress.
     */
    public function init(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Enqueues all necessary scripts and styles for the settings page.
     *
     * This method is hooked into 'admin_enqueue_scripts' and only loads assets
     * on the relevant admin page.
     */
    public function enqueueAssets(): void
    {
        $pageSlug = $this->config['pageSlug'] ?? '';
        $screen = get_current_screen();
        if (empty($pageSlug) || null === $screen || !str_contains($screen->id, $pageSlug)) {
            return;
        }

        wp_enqueue_script('jquery');
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_media();

        $this->enqueueRequiredLibraries();
        $this->enqueueCodeEditorAssets();

        wp_add_inline_script('jquery-core', $this->getInlineScripts());
        wp_add_inline_style('wp-admin', $this->getInlineStyles());
    }

    /**
     * Scans the configured fields and enqueues the necessary third-party libraries.
     */
    private function enqueueRequiredLibraries(): void
    {
        if (empty($this->config['fields'])) {
            return;
        }

        $requiredPackages = [];
        foreach ($this->config['fields'] as $field) {
            $fieldType = $field['type'] ?? '';
            if (isset($this->fieldTypeToPackageMap[$fieldType])) {
                $packageName = $this->fieldTypeToPackageMap[$fieldType];
                $requiredPackages[$packageName] = true;
            }
        }

        foreach (array_keys($requiredPackages) as $packageName) {
            if (isset($this->libraryPackages[$packageName])) {
                $this->enqueuePackage($this->libraryPackages[$packageName]);
            }
        }
    }

    /**
     * Enqueues a single library package (style and/or script).
     *
     * It checks if the asset is already registered to avoid conflicts with other plugins.
     *
     * @param array{
     *     handle: string,
     *     script?: array{src: string, deps: string[], version: string, in_footer: bool},
     *     style?: array{src: string, deps: string[], version: string}
     * } $package The package definition.
     */
    private function enqueuePackage(array $package): void
    {
        $handle = $package['handle'];
        if (isset($package['style'])) {
            if (!wp_style_is($handle, 'registered')) {
                wp_register_style($handle, ...array_values($package['style']));
            }
            wp_enqueue_style($handle);
        }
        if (isset($package['script'])) {
            if (!wp_script_is($handle, 'registered')) {
                wp_register_script($handle, ...array_values($package['script']));
            }
            wp_enqueue_script($handle);
        }
    }

    /**
     * Enqueues the WordPress Code Editor assets for the required languages.
     */
    private function enqueueCodeEditorAssets(): void
    {
        if (!function_exists('wp_enqueue_code_editor') || empty($this->config['fields'])) {
            return;
        }
        $requiredLanguages = [];
        foreach ($this->config['fields'] as $field) {
            if (($field['type'] ?? '') === 'code') {
                $requiredLanguages[$field['language'] ?? 'css'] = true;
            }
        }
        if (empty($requiredLanguages)) {
            return;
        }
        $mimeTypes = [
            'css'        => 'text/css',
            'js'         => 'text/javascript',
            'javascript' => 'text/javascript',
            'html'       => 'text/html',
            'xml'        => 'application/xml',
        ];
        foreach (array_keys($requiredLanguages) as $lang) {
            wp_enqueue_code_editor(['type' => $mimeTypes[$lang] ?? 'text/plain']);
        }
    }

    /**
     * Gets the complete inline JavaScript for the settings page.
     *
     * @return string The inline JavaScript code.
     */
    private function getInlineScripts(): string
    {
        $htmlPrefix = $this->config['htmlPrefix'] ?? 'wptechnix-settings';

        $addMediaTitle = esc_js(
            $this->config['labels']['addMediaTitle'] ??
            __('Select', 'default')
        );
        $selectMediaText = esc_js(
            $this->config['labels']['selectMediaText'] ??
            __('Select', 'default')
        );
        $removeMediaText = esc_js(
            $this->config['labels']['removeMediaText'] ??
                __('Remove', 'default')
        );

        // phpcs:disable Generic.Files.LineLength
        return <<<JS
		jQuery(function($) {
			// Initialize WordPress color picker
			if ($.fn.wpColorPicker) {
				$('.{$htmlPrefix}-color-picker').wpColorPicker({
					change: function(event, ui) { $(event.target).trigger('change'); }
				});
			}

			// Initialize Select2
			if ($.fn.select2) {
				try {
					$('.{$htmlPrefix}-select2-field').not('.select2-hidden-accessible').select2({ width: '100%', allowClear: true });
				} catch (e) {
					console.error('Settings Framework: Select2 Error:', e);
				}
			}

			// Initialize Flatpickr
			if (typeof flatpickr !== 'undefined') {
				$('.{$htmlPrefix}-flatpickr-date').flatpickr({ dateFormat: 'Y-m-d', altInput: true, altFormat: 'F j, Y' });
				$('.{$htmlPrefix}-flatpickr-datetime').flatpickr({ enableTime: true, dateFormat: 'Y-m-d H:i', altInput: true, altFormat: 'F j, Y at h:i K' });
				$('.{$htmlPrefix}-flatpickr-time').flatpickr({ enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true });
			}

			// Initialize WordPress media uploader
			$(document).on('click', '.{$htmlPrefix}-media-upload-button', function(e) {
				e.preventDefault();
				var button = $(this);
				var fieldId = button.data('field');
				var mediaUploader = wp.media({
					title: '{$addMediaTitle}',
					button: { text: '{$selectMediaText}' },
					multiple: false
				});
				mediaUploader.on('select', function() {
					var attachment = mediaUploader.state().get('selection').first().toJSON();
					$('#' + fieldId).val(attachment.id).trigger('change');
					var previewContainer = button.siblings('.{$htmlPrefix}-media-preview');
					var previewHTML = '';
					if (attachment.type === 'image') {
						previewHTML = '<img src="' + attachment.url + '" alt="" class="{$htmlPrefix}-media-preview-image" />';
					} else {
						previewHTML = '<div class="{$htmlPrefix}-media-preview-file"><span class="dashicons dashicons-media-default"></span> ' + attachment.filename + '</div>';
					}
					previewContainer.html(previewHTML);
					if (!button.siblings('.{$htmlPrefix}-media-remove-button').length) {
						button.after(' <button type="button" class="button {$htmlPrefix}-media-remove-button" data-field="' + fieldId + '">{$removeMediaText}</button>');
					}
				});
				mediaUploader.open();
			});
			$(document).on('click', '.{$htmlPrefix}-media-remove-button', function(e) {
				e.preventDefault();
				var button = $(this);
				var fieldId = button.data('field');
				$('#' + fieldId).val('').trigger('change');
				button.siblings('.{$htmlPrefix}-media-preview').empty();
				button.remove();
			});

			// Initialize button groups
			$('.{$htmlPrefix}-buttongroup-container').on('click', '.{$htmlPrefix}-buttongroup-option', function(e) {
				e.preventDefault();
				var button = $(this);
				var container = button.parent();
				var hiddenInput = container.siblings('input[type="hidden"]');
				container.find('.{$htmlPrefix}-buttongroup-option').removeClass('active');
				button.addClass('active');
				hiddenInput.val(button.data('value')).trigger('change');
			});

			// Range sliders
			function updateRangeSlider(slider) {
				var value = slider.val();
				slider.closest('.{$htmlPrefix}-enhanced-range-container').find('.{$htmlPrefix}-range-value-input').val(value);
			}

            $('.{$htmlPrefix}-enhanced-range-slider').on('input', function() {
                updateRangeSlider($(this));
            });
			$('.{$htmlPrefix}-enhanced-range-slider').each(function() {
                updateRangeSlider($(this));
            });

			// Initialize CodeMirror
			if (wp.codeEditor) {
				$('.{$htmlPrefix}-code-editor').each(function() {
					var textarea = $(this);
					if (textarea.data('codemirror-initialized')) { return; }
					var language = textarea.data('language') || 'css';
					var mimeType = 'text/' + language;
					if (language === 'javascript' || language === 'js') mimeType = 'text/javascript';
					if (language === 'html') mimeType = 'text/html';

					var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
					editorSettings.codemirror = _.extend({}, editorSettings.codemirror, { mode: mimeType, lineNumbers: true, lineWrapping: true, styleActiveLine: true });

					var editor = wp.codeEditor.initialize(textarea, editorSettings);
					editor.codemirror.on('change', function() { editor.codemirror.save(); textarea.trigger('change'); });
					textarea.data('codemirror-initialized', true);
				});
			}

			// Conditional fields logic
			function toggleConditionalFields() {
				$('[data-conditional]').each(function() {
					var field = $(this);
					var condField = field.data('conditional');
					var condValue = field.data('conditional-value').toString();
					var operator = field.data('conditional-operator') || '==';
					var target = $('[name*="[' + condField + ']"]');
					var currentVal;

					if (target.is(':checkbox')) {
						currentVal = target.is(':checked') ? '1' : '0';
					} else if (target.is('input[type=radio]')) {
						currentVal = $('[name*="[' + condField + ']"]:checked').val();
					} else {
						currentVal = target.val();
					}

					var show = false;
					switch (operator) {
						case '==': show = (currentVal == condValue); break;
						case '!=': show = (currentVal != condValue); break;
						case 'in': show = Array.isArray(currentVal) ? currentVal.includes(condValue) : condValue.split(',').includes(currentVal); break;
						case 'not in': show = Array.isArray(currentVal) ? !currentVal.includes(condValue) : !condValue.split(',').includes(currentVal); break;
					}

					var container = field.closest('tr');
					show ? container.show() : container.hide();
				});
			}
			$('body').on('change', 'input, select, textarea', toggleConditionalFields);
			toggleConditionalFields();
		});
JS;
        // phpcs:enable
    }

    /**
     * Gets the complete inline CSS styles for the settings page.
     *
     * @return string The inline CSS code.
     */
    private function getInlineStyles(): string
    {
        $htmlPrefix = $this->config['htmlPrefix'] ?? 'wptechnix-settings';

        // phpcs:disable Generic.Files.LineLength
        return <<<CSS
			nav.nav-tab-wrapper .dashicons {
                line-height: 1.5;
                vertical-align: middle;
                margin-right: 0.3em;
			}
			.nav-tab {
                display: flex;
                align-items: center;
			}

			.{$htmlPrefix}-toggle {
			  position: relative;
			  display: inline-block;
			  width: 50px;
			  height: 24px;
			  vertical-align: middle;
			}

			.{$htmlPrefix}-toggle input {
			  opacity: 0;
			  width: 0;
			  height: 0;
			}

			.{$htmlPrefix}-toggle-slider {
			  position: absolute;
			  cursor: pointer;
			  top: 0;
			  left: 0;
			  right: 0;
			  bottom: 0;
			  background-color: #ccc;
			  transition: .4s;
			  border-radius: 24px;
			}

			.{$htmlPrefix}-toggle-slider:before {
              position: absolute;
              content: '';
              height: 18px;
              width: 18px;
              left: 3px;
              bottom: 3px;
              background-color: white;
              transition: .4s;
              border-radius: 50%;
			}

			input:checked + .{$htmlPrefix}-toggle-slider {
			  background-color: #2271b1;
			}

			input:checked + .{$htmlPrefix}-toggle-slider:before {
			  transform: translateX(26px);
			}

			.{$htmlPrefix}-buttongroup-container {
			  display: inline-flex;
			  border: 1px solid #ccd0d4;
			  border-radius: 4px;
			  overflow: hidden;
			}
			.{$htmlPrefix}-buttongroup-option {
                 padding: 0 14px;
                 height: 30px;
                 line-height: 28px;
                 background: #f6f7f7;
                 border: none;
                 cursor: pointer;
                 border-right: 1px solid #ccd0d4;
                 color: #2c3338;
			}

			.{$htmlPrefix}-buttongroup-option:last-child {
			   border-right: none;
			}

			.{$htmlPrefix}-buttongroup-option.active {
              background: #2271b1;
              color: white;
              box-shadow: inset 0 0 5px rgba(0,0,0,0.2);
			}

			.{$htmlPrefix}-media-field-container {
			  display: flex;
			  align-items: center;
			  gap: 10px;
			  flex-wrap: wrap;
			}

			.{$htmlPrefix}-media-preview-image {
			  max-width: 150px;
			  height: auto;
			  margin-top: 10px;
			  border: 1px solid #ddd;
			  box-shadow: 0 1px 2px rgba(0,0,0,0.07);
			}
			.{$htmlPrefix}-media-preview-file {
			   margin-top: 10px;
			}

			.{$htmlPrefix}-media-preview-file .dashicons {
			  vertical-align: text-bottom;
			}

			.{$htmlPrefix}-enhanced-range-container {
			  display: flex;
			  align-items: center;
			  gap: 15px;
			  max-width: 400px;
			}

			.{$htmlPrefix}-enhanced-range-slider {
			  flex: 1;
			}

			.{$htmlPrefix}-range-value-input {
			  width: 70px;
			  text-align: center;
			}
CSS;
        // phpcs:enable
    }
}
