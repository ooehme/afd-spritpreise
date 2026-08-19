<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class Admin
{
    private const PAGE = 'afd-spritpreise';

    public function __construct(private readonly Plugin $plugin)
    {
    }

    public function register_hooks(): void
    {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_init', [$this, 'settings']);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
        add_action('admin_post_afdsp_clear_cache', [$this, 'clear_cache']);
        add_action('admin_post_afdsp_refresh_data', [$this, 'refresh_data']);
        add_action('admin_post_afdsp_reset_settings', [$this, 'reset_settings']);
    }

    public function menu(): void
    {
        add_options_page(__('AfD Spritpreise', 'afd-spritpreise'), __('AfD Spritpreise', 'afd-spritpreise'), 'manage_options', self::PAGE, [$this, 'page']);
    }

    public function settings(): void
    {
        register_setting('afdsp_settings_group', Options::OPTION, ['type' => 'array', 'sanitize_callback' => [Options::class, 'sanitize'], 'default' => Options::defaults()]);
    }

    public function assets(string $hook): void
    {
        if ('settings_page_' . self::PAGE !== $hook) {
            return;
        }
        $options = Options::get();
        wp_enqueue_style('afdsp-admin');
        wp_enqueue_script('afdsp-area-picker');
        wp_localize_script('afdsp-area-picker', 'afdspAreaPickerConfig', [
            'endpoint' => esc_url_raw(rest_url('afd-spritpreise/v1/photon')),
            'nonce' => wp_create_nonce('wp_rest'),
            'area' => $options['area'],
            'strings' => ['search' => __('Ort oder PLZ suchen', 'afd-spritpreise'), 'noResults' => __('Keine Orte gefunden.', 'afd-spritpreise'), 'error' => __('Ortssuche derzeit nicht verfügbar.', 'afd-spritpreise')],
        ]);
    }

    public function page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $options = Options::get();
        $cache = Cache::status();
        ?>
        <div class="wrap afdsp-admin-wrap">
            <h1><?php esc_html_e('AfD Spritpreise', 'afd-spritpreise'); ?></h1>
            <?php settings_errors(); ?>
            <?php if (isset($_GET['afdsp_notice'])) : ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html(sanitize_text_field(wp_unslash($_GET['afdsp_notice']))); ?></p></div><?php endif; ?>
            <form action="options.php" method="post">
                <?php settings_fields('afdsp_settings_group'); ?>
                <section class="afdsp-settings-card">
                    <h2><?php esc_html_e('Standardgebiet', 'afd-spritpreise'); ?></h2>
                    <p><?php esc_html_e('Default für neue Blöcke und Shortcodes ohne eigene Koordinaten.', 'afd-spritpreise'); ?></p>
                    <?php $this->area_picker($options['area']); ?>
                </section>
                <section class="afdsp-settings-card">
                    <h2><?php esc_html_e('TankPuls', 'afd-spritpreise'); ?></h2>
                    <div class="afdsp-settings-grid">
                        <?php $this->text('tankpuls', 'base_url', __('API-Basis-URL', 'afd-spritpreise'), $options['tankpuls']['base_url'], 'url'); ?>
                        <?php $this->text('tankpuls', 'timeout', __('Timeout (Sekunden)', 'afd-spritpreise'), $options['tankpuls']['timeout'], 'number', '3', '120'); ?>
                        <?php $this->text('tankpuls', 'cache_ttl', __('Cache-TTL (Sekunden)', 'afd-spritpreise'), $options['tankpuls']['cache_ttl'], 'number', '60', (string) DAY_IN_SECONDS); ?>
                        <?php $this->text('tankpuls', 'limit', __('Maximale Tankstellen', 'afd-spritpreise'), $options['tankpuls']['limit'], 'number', '1', '500'); ?>
                    </div>
                </section>
                <section class="afdsp-settings-card">
                    <h2><?php esc_html_e('Photon', 'afd-spritpreise'); ?></h2>
                    <?php $this->text('photon', 'endpoint', __('Photon-Endpunkt', 'afd-spritpreise'), $options['photon']['endpoint'], 'url'); ?>
                    <p class="description"><?php esc_html_e('Photon wird ausschließlich bei der Gebietskonfiguration im Backend und Block-Editor aufgerufen.', 'afd-spritpreise'); ?></p>
                </section>
                <section class="afdsp-settings-card">
                    <h2><?php esc_html_e('Berechnung', 'afd-spritpreise'); ?></h2>
                    <div class="afdsp-settings-grid">
                        <?php foreach ($this->calculation_labels() as $key => $label) : $this->text('calculation', $key, $label, $options['calculation'][$key], 'number', '0', '1000', '0.0001'); endforeach; ?>
                    </div>
                </section>
                <section class="afdsp-settings-card">
                    <h2><?php esc_html_e('Darstellung', 'afd-spritpreise'); ?></h2>
                    <div class="afdsp-settings-grid">
                        <label><?php esc_html_e('Standard-Kraftstoff', 'afd-spritpreise'); ?><select name="afdsp_settings[display][defaultFuel]"><?php foreach (['diesel' => 'Diesel', 'e5' => 'Super E5', 'e10' => 'Super E10'] as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($options['display']['defaultFuel'], $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></label>
                        <label><?php esc_html_e('Standard-Modus', 'afd-spritpreise'); ?><select name="afdsp_settings[display][displayMode]"><option value="full" <?php selected($options['display']['displayMode'], 'full'); ?>><?php esc_html_e('Vollständig', 'afd-spritpreise'); ?></option><option value="compact" <?php selected($options['display']['displayMode'], 'compact'); ?>><?php esc_html_e('Kompakt', 'afd-spritpreise'); ?></option></select></label>
                    </div>
                    <div class="afdsp-checkboxes">
                        <?php foreach ($this->display_labels() as $key => $label) : ?><label><input type="checkbox" name="afdsp_settings[display][<?php echo esc_attr($key); ?>]" value="1" <?php checked($options['display'][$key]); ?>> <?php echo esc_html($label); ?></label><?php endforeach; ?>
                    </div>
                </section>
                <?php submit_button(__('Einstellungen speichern', 'afd-spritpreise')); ?>
            </form>
            <section class="afdsp-settings-card">
                <h2><?php esc_html_e('Cache', 'afd-spritpreise'); ?></h2>
                <dl class="afdsp-diagnostics"><dt><?php esc_html_e('Einträge', 'afd-spritpreise'); ?></dt><dd><?php echo esc_html((string) $cache['entries']); ?></dd><dt><?php esc_html_e('Letzter erfolgreicher Abruf', 'afd-spritpreise'); ?></dt><dd><?php echo esc_html($cache['last_success'] ?: '–'); ?></dd><dt><?php esc_html_e('Letzter Fehler', 'afd-spritpreise'); ?></dt><dd><?php echo esc_html($cache['last_error']['message'] ?? '–'); ?></dd></dl>
                <div class="afdsp-actions">
                    <?php $this->action_form('afdsp_clear_cache', __('Cache leeren', 'afd-spritpreise')); ?>
                    <?php $this->action_form('afdsp_refresh_data', __('Daten aktualisieren', 'afd-spritpreise'), 'button button-primary'); ?>
                </div>
            </section>
            <section class="afdsp-settings-card afdsp-settings-card--danger"><h2><?php esc_html_e('Standardwerte', 'afd-spritpreise'); ?></h2><?php $this->action_form('afdsp_reset_settings', __('Standardwerte wiederherstellen', 'afd-spritpreise')); ?></section>
        </div>
        <?php
    }

    public function clear_cache(): void
    {
        $this->authorize('afdsp_clear_cache');
        Cache::clear_all();
        $this->redirect(__('Cache wurde geleert.', 'afd-spritpreise'));
    }

    public function refresh_data(): void
    {
        $this->authorize('afdsp_refresh_data');
        $box = BoundingBox::from_array(Options::get()['area']);
        $errors = [];
        foreach (['diesel', 'e5', 'e10'] as $fuel) {
            try {
                $this->plugin->service()->get($box, $fuel, true);
            } catch (\Throwable $error) {
                $errors[] = strtoupper($fuel) . ': ' . $error->getMessage();
            }
        }
        $this->redirect($errors ? __('Aktualisierung teilweise fehlgeschlagen. Details stehen im Cache-Status.', 'afd-spritpreise') : __('Preisdaten wurden aktualisiert.', 'afd-spritpreise'));
    }

    public function reset_settings(): void
    {
        $this->authorize('afdsp_reset_settings');
        Options::reset();
        Cache::clear_all();
        $this->redirect(__('Standardwerte wurden wiederhergestellt.', 'afd-spritpreise'));
    }

    private function area_picker(array $area): void
    {
        ?><div class="afdsp-area-picker" data-afdsp-area-picker>
            <label class="afdsp-search-label"><?php esc_html_e('Ort oder PLZ suchen', 'afd-spritpreise'); ?><input type="search" data-afdsp-search autocomplete="off" placeholder="<?php esc_attr_e('Mindestens 3 Zeichen', 'afd-spritpreise'); ?>"></label>
            <p class="description"><?php echo wp_kses_post(__('Geocoding: <a href="https://photon.komoot.io/" target="_blank" rel="external noopener">Photon</a> · © OpenStreetMap-Mitwirkende', 'afd-spritpreise')); ?></p>
            <div class="afdsp-search-results" data-afdsp-results role="listbox"></div>
            <label><?php esc_html_e('Gebietsname', 'afd-spritpreise'); ?><input type="text" class="regular-text" name="afdsp_settings[area][areaLabel]" data-afdsp-field="areaLabel" value="<?php echo esc_attr($area['areaLabel']); ?>"></label>
            <details class="afdsp-bbox-details"><summary><?php esc_html_e('Gespeicherte Bounding Box', 'afd-spritpreise'); ?></summary><div class="afdsp-coordinates"><?php foreach (['minLat', 'minLng', 'maxLat', 'maxLng'] as $key) : ?><label><?php echo esc_html($key); ?><input type="number" step="any" name="afdsp_settings[area][<?php echo esc_attr($key); ?>]" data-afdsp-field="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr((string) $area[$key]); ?>"></label><?php endforeach; ?></div></details>
        </div><?php
    }

    private function text(string $section, string $key, string $label, mixed $value, string $type = 'text', ?string $min = null, ?string $max = null, ?string $step = null): void
    {
        ?><label><?php echo esc_html($label); ?><input class="regular-text" type="<?php echo esc_attr($type); ?>" name="afdsp_settings[<?php echo esc_attr($section); ?>][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr((string) $value); ?>"<?php if ($min) : ?> min="<?php echo esc_attr($min); ?>"<?php endif; ?><?php if ($max) : ?> max="<?php echo esc_attr($max); ?>"<?php endif; ?><?php if ($step) : ?> step="<?php echo esc_attr($step); ?>"<?php endif; ?>></label><?php
    }

    private function action_form(string $action, string $label, string $class = 'button'): void
    {
        ?><form class="afdsp-inline-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="<?php echo esc_attr($action); ?>"><?php wp_nonce_field($action); ?><button type="submit" class="<?php echo esc_attr($class); ?>"><?php echo esc_html($label); ?></button></form><?php
    }

    private function authorize(string $action): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Keine Berechtigung.', 'afd-spritpreise'));
        }
        check_admin_referer($action);
    }

    private function redirect(string $notice): never
    {
        wp_safe_redirect(add_query_arg(['page' => self::PAGE, 'afdsp_notice' => $notice], admin_url('options-general.php')));
        exit;
    }

    private function calculation_labels(): array
    {
        return [
            'energy_petrol_current' => __('Energiesteuer Benzin aktuell (€/l)', 'afd-spritpreise'), 'energy_diesel_current' => __('Energiesteuer Diesel aktuell (€/l)', 'afd-spritpreise'),
            'energy_petrol_scenario' => __('AfD Benzin (€/l)', 'afd-spritpreise'), 'energy_diesel_scenario' => __('AfD Diesel (€/l)', 'afd-spritpreise'),
            'vat_current' => __('Mehrwertsteuer aktuell (%)', 'afd-spritpreise'), 'vat_scenario' => __('AfD Mehrwertsteuer (%)', 'afd-spritpreise'),
            'co2_petrol_kg' => __('CO₂ Benzin (kg/l)', 'afd-spritpreise'), 'co2_diesel_kg' => __('CO₂ Diesel (kg/l)', 'afd-spritpreise'),
            'co2_price_per_tonne' => __('CO₂-Preis aktuell (€/t)', 'afd-spritpreise'), 'co2_scenario_per_tonne' => __('AfD CO₂-Preis (€/t)', 'afd-spritpreise'),
        ];
    }

    private function display_labels(): array
    {
        return ['showTitle' => __('Titel anzeigen', 'afd-spritpreise'), 'showDemands' => __('Forderungen anzeigen', 'afd-spritpreise'), 'showMethod' => __('Methodik anzeigen', 'afd-spritpreise'), 'showCheapestStation' => __('Günstigste Tankstelle anzeigen', 'afd-spritpreise'), 'showTankSaving' => __('50-Liter-Ersparnis anzeigen', 'afd-spritpreise'), 'showDetailsLink' => __('Detail-/Quellenlink anzeigen', 'afd-spritpreise')];
    }
}
