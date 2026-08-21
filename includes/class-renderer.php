<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class Renderer
{
    private const LABELS = ['diesel' => 'Diesel', 'e5' => 'Super E5', 'e10' => 'Super E10'];

    public function __construct(private readonly FuelPriceService $service)
    {
    }

    public function render(array $config): string
    {
        return 'compact' === $config['displayMode'] ? $this->render_compact($config) : $this->render_full($config);
    }

    private function render_full(array $config): string
    {
        $id = wp_unique_id('afdsp-');
        $results = [];
        foreach (array_keys(self::LABELS) as $fuel) {
            try {
                $results[$fuel] = $this->service->get($config['box'], $fuel);
            } catch (\Throwable $error) {
                $results[$fuel] = null;
                error_log('AfD Spritpreise (' . $fuel . '): ' . $error->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
        }

        ob_start();
        ?>
        <section id="<?php echo esc_attr($id); ?>" class="afdsp afdsp--full afdsp--full-site" data-afdsp-tabs>
            <?php if ($config['showTitle']) : ?>
                <h2 class="afdsp-full-title"><?php esc_html_e('Das kostet Kraftstoff mit der AfD', 'afd-spritpreise'); ?></h2>
            <?php endif; ?>

            <div class="afdsp-full-card">
                <div class="afdsp-tabs" role="tablist" aria-label="<?php esc_attr_e('Kraftstoffart', 'afd-spritpreise'); ?>">
                    <?php foreach (self::LABELS as $fuel => $label) : ?>
                        <button class="afdsp-tab<?php echo $fuel === $config['defaultFuel'] ? ' is-active' : ''; ?>" type="button" role="tab" id="<?php echo esc_attr($id . '-tab-' . $fuel); ?>" aria-controls="<?php echo esc_attr($id . '-panel-' . $fuel); ?>" aria-selected="<?php echo $fuel === $config['defaultFuel'] ? 'true' : 'false'; ?>" aria-pressed="<?php echo $fuel === $config['defaultFuel'] ? 'true' : 'false'; ?>" tabindex="<?php echo $fuel === $config['defaultFuel'] ? '0' : '-1'; ?>" data-afdsp-tab="<?php echo esc_attr($fuel); ?>"><?php echo esc_html($label); ?></button>
                    <?php endforeach; ?>
                </div>

                <?php foreach (self::LABELS as $fuel => $label) : ?>
                    <div class="afdsp-price-panel" id="<?php echo esc_attr($id . '-panel-' . $fuel); ?>" role="tabpanel" aria-labelledby="<?php echo esc_attr($id . '-tab-' . $fuel); ?>" data-afdsp-panel="<?php echo esc_attr($fuel); ?>"<?php echo $fuel !== $config['defaultFuel'] ? ' hidden' : ''; ?>>
                        <?php if (!$results[$fuel]) : ?>
                            <p class="afdsp-unavailable" role="status"><?php esc_html_e('Aktuelle Kraftstoffpreise sind derzeit nicht verfügbar.', 'afd-spritpreise'); ?></p>
                        <?php else : ?>
                            <?php echo $this->price_board($results[$fuel], $config); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php foreach (self::LABELS as $fuel => $label) : ?>
                <div class="afdsp-detail-panel" data-afdsp-panel="<?php echo esc_attr($fuel); ?>"<?php echo $fuel !== $config['defaultFuel'] ? ' hidden' : ''; ?>>
                    <?php if ($results[$fuel]) : ?>
                        <?php if ($config['showCheapestStation']) : ?>
                            <?php echo $this->station_row($results[$fuel]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php endif; ?>
                        <?php if ($config['showDemands']) : ?>
                            <?php echo $this->demands($results[$fuel]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php endif; ?>
                        <?php if ($config['showMethod']) : ?>
                            <?php echo $this->method($results[$fuel], $config['areaLabel']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>
        <?php
        return (string) ob_get_clean();
    }

    private function render_compact(array $config): string
    {
        try {
            $result = $this->service->get($config['box'], $config['defaultFuel']);
        } catch (\Throwable $error) {
            error_log('AfD Spritpreise compact: ' . $error->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            return '<section class="afdsp afdsp--compact afdsp--error" role="status">' . esc_html__('Aktuelle Kraftstoffpreise sind derzeit nicht verfügbar.', 'afd-spritpreise') . '</section>';
        }
        $calc = $result['calculation'];
        ob_start();
        ?>
        <section class="afdsp afdsp--compact">
            <header class="afdsp-compact-heading">
                <p><?php echo esc_html($config['areaLabel'] . ' · ' . self::LABELS[$config['defaultFuel']]); ?></p>
                <?php if ($result['stale']) : ?><span class="afdsp-stale"><?php esc_html_e('Zuletzt verfügbare Preise', 'afd-spritpreise'); ?></span><?php endif; ?>
            </header>
            <div class="afdsp-compact-prices">
                <div><span><?php esc_html_e('Aktuell', 'afd-spritpreise'); ?></span><strong><?php echo esc_html($this->price($calc['current_gross'])); ?></strong></div>
                <div class="afdsp-compact-scenario"><span><?php esc_html_e('Mit AfD-Forderungen', 'afd-spritpreise'); ?></span><strong><?php echo esc_html($this->price($calc['scenario_gross'])); ?></strong></div>
                <div class="afdsp-compact-saving"><strong><?php echo esc_html($this->number($calc['saving_cents'], 1) . ' ct/l'); ?></strong><span><?php esc_html_e('weniger', 'afd-spritpreise'); ?></span><?php if ($config['showTankSaving']) : ?><small><?php echo esc_html($this->money($calc['saving_50l']) . ' weniger bei 50 Litern'); ?></small><?php endif; ?></div>
            </div>
            <?php if ($config['showCheapestStation']) : ?>
                <p class="afdsp-compact-station"><span><?php esc_html_e('Günstigste Tankstelle', 'afd-spritpreise'); ?></span><strong><?php echo esc_html($result['cheapest']['name'] . ' · ' . $this->price($result['cheapest']['price'])); ?></strong></p>
            <?php endif; ?>
            <?php if ($config['showDetailsLink']) : ?>
                <details class="afdsp-compact-details"><summary><?php esc_html_e('Berechnung & Quellen', 'afd-spritpreise'); ?></summary><?php echo $this->compact_details(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></details>
            <?php endif; ?>
            <p class="afdsp-source"><?php echo wp_kses_post(sprintf(__('Preisdaten: %s · MTS-K', 'afd-spritpreise'), '<a href="https://tankpuls.de/" rel="external nofollow">TankPuls</a>')); ?></p>
        </section>
        <?php
        return (string) ob_get_clean();
    }

    private function price_board(array $result, array $config): string
    {
        $calc = $result['calculation'];
        ob_start();
        ?>
        <div class="afdsp-board">
            <?php if ($result['stale']) : ?><p class="afdsp-stale" role="status"><?php esc_html_e('Die API ist derzeit nicht erreichbar. Angezeigt werden die zuletzt erfolgreich geladenen Preise.', 'afd-spritpreise'); ?></p><?php endif; ?>
            <div class="afdsp-price-grid">
                <div class="afdsp-price afdsp-price--current"><span><?php esc_html_e('aktueller Preis', 'afd-spritpreise'); ?></span><strong><?php echo esc_html($this->price($calc['current_gross'])); ?></strong></div>
                <div class="afdsp-price afdsp-price--scenario"><span><?php esc_html_e('Nach AfD-Forderungen', 'afd-spritpreise'); ?></span><strong><?php echo esc_html($this->price($calc['scenario_gross'])); ?></strong><small><?php esc_html_e('wenn die Entlastungen vollständig ankommen', 'afd-spritpreise'); ?></small></div>
                <div class="afdsp-price afdsp-price--saving"><span><?php esc_html_e('Mögliche Ersparnis', 'afd-spritpreise'); ?></span><strong><?php echo esc_html($this->number($calc['saving_cents'], 1) . ' ct/l'); ?></strong><small><?php echo esc_html($this->number($calc['saving_percent'], 1) . ' % weniger'); ?></small></div>
            </div>
            <div class="afdsp-price-meta">
                <p><?php echo esc_html(sprintf(__('Median aus %1$d Tankstellen im Gebiet %2$s.', 'afd-spritpreise'), $result['station_count'], $config['areaLabel'])); ?></p>
                <?php if ($config['showTankSaving']) : ?>
                    <p class="afdsp-price-meta-saving"><span><?php esc_html_e('bei 50 Litern', 'afd-spritpreise'); ?></span><strong><?php echo esc_html($this->money($calc['saving_50l']) . ' weniger'); ?></strong></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    private function station_row(array $result): string
    {
        $station = $result['cheapest'];
        $label = sprintf(__('aktuell günstigste Tankstelle für %s', 'afd-spritpreise'), self::LABELS[$result['fuel']]);
        $address = trim($station['street'] . ', ' . $station['postcode'] . ' ' . $station['city'], ' ,');
        return '<div class="afdsp-station-row"><span class="afdsp-station-label">' . esc_html($label) . '</span><strong class="afdsp-station-name">' . esc_html($station['name']) . '</strong><small class="afdsp-station-address">' . esc_html($address) . '</small><b class="afdsp-station-price">' . esc_html($this->price($station['price'])) . '</b></div>';
    }

    private function demands(array $result): string
    {
        $c = $result['calculation'];
        ob_start();
        ?>
        <h2 class="afdsp-receipt-title"><?php esc_html_e('In der Rechnung', 'afd-spritpreise'); ?></h2>
        <section class="afdsp-receipt" aria-label="<?php esc_attr_e('Forderungen und Quellen', 'afd-spritpreise'); ?>">
            <?php echo $this->demand_row(__('Energiesteuer auf das EU-Mindestmaß senken', 'afd-spritpreise'), $this->number($c['energy_current'] * 100, 1) . ' ct → ' . $this->number($c['energy_scenario'] * 100, 1) . ' ct', $this->number(($c['energy_current'] - $c['energy_scenario']) * 100, 1) . ' ct weniger je Liter', 'BT-Drs. 21/6332', 'https://dserver.bundestag.de/btd/21/063/2106332.pdf'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php echo $this->demand_row(__('CO₂-Bepreisung abschaffen', 'afd-spritpreise'), $this->number($c['co2_current'] * 100, 1) . ' ct → ' . $this->number($c['co2_scenario'] * 100, 1) . ' ct', $this->number(($c['co2_current'] - $c['co2_scenario']) * 100, 1) . ' ct weniger je Liter', 'BT-Drs. 21/6334', 'https://dserver.bundestag.de/btd/21/063/2106334.pdf'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php echo $this->demand_row(__('Mehrwertsteuer auf Kraftstoffe auf 7 % senken', 'afd-spritpreise'), '', '', 'BT-Drs. 21/5326', 'https://dserver.bundestag.de/btd/21/053/2105326.pdf'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <div class="afdsp-receipt-note"><strong><?php esc_html_e('Weitere Abgaben nicht eingerechnet', 'afd-spritpreise'); ?></strong><p><?php esc_html_e('Erdölbevorratungsbeitrag und THG-Quote werden ohne konkrete konfigurierte Zielgröße nicht zusätzlich abgezogen.', 'afd-spritpreise'); ?></p></div>
        </section>
        <?php
        return (string) ob_get_clean();
    }

    private function demand_row(string $title, string $change, string $effect, string $source, string $url): string
    {
        $values = '';
        if ('' !== $change || '' !== $effect) {
            $values = '<p>' . ('' !== $change ? '<strong>' . esc_html($change) . '</strong>' : '') . ('' !== $effect ? '<span>' . esc_html($effect) . '</span>' : '') . '</p>';
        }
        return '<article class="afdsp-demand"><h4>' . esc_html($title) . '</h4>' . $values . '<a class="afdsp-demand-link" href="' . esc_url($url) . '" rel="external noopener" target="_blank">' . esc_html($source) . '</a></article>';
    }

    private function method(array $result, string $areaLabel): string
    {
        $text = sprintf(
            __('Median aus %1$d gültigen, aktiven Tankstellen im Gebiet %2$s. Der Bruttopreis wird um Mehrwertsteuer, Energiesteuer und CO₂-Kosten bereinigt und anschließend mit den konfigurierten Szenariowerten neu berechnet. Intern wird nicht vorzeitig gerundet.', 'afd-spritpreise'),
            $result['station_count'],
            $areaLabel
        );
        return '<section class="afdsp-method"><h3>' . esc_html__('Methodik:', 'afd-spritpreise') . '</h3><p>' . esc_html($text) . '</p></section><p class="afdsp-source">' . wp_kses_post(sprintf(__('Preisdaten: %s · MTS-K', 'afd-spritpreise'), '<a href="https://tankpuls.de/" rel="external nofollow">TankPuls</a>')) . '</p>';
    }

    private function compact_details(): string
    {
        return '<p>' . esc_html__('Szenario auf Basis des regionalen Medians: Energie- und CO₂-Kosten werden durch die konfigurierten Zielwerte ersetzt; anschließend wird die Szenario-Mehrwertsteuer berechnet.', 'afd-spritpreise') . '</p><p><a href="https://dserver.bundestag.de/btd/21/063/2106332.pdf" target="_blank" rel="external noopener">BT-Drs. 21/6332</a> · <a href="https://dserver.bundestag.de/btd/21/063/2106334.pdf" target="_blank" rel="external noopener">BT-Drs. 21/6334</a> · <a href="https://dserver.bundestag.de/btd/21/053/2105326.pdf" target="_blank" rel="external noopener">BT-Drs. 21/5326</a></p>';
    }

    private function number(float $value, int $decimals): string
    {
        return number_format_i18n($value, $decimals);
    }

    private function price(float $value): string
    {
        return $this->number($value, 3) . ' €/l';
    }

    private function money(float $value): string
    {
        return $this->number($value, 2) . ' €';
    }

    private function date(string $iso): string
    {
        $timestamp = strtotime($iso);
        return $timestamp ? wp_date(get_option('date_format') . ' · ' . get_option('time_format'), $timestamp) : $iso;
    }
}
