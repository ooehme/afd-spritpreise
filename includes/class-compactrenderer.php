<?php

namespace AFDSP;

defined('ABSPATH') || exit;

final class CompactRenderer
{
    private const LABELS = [
        'diesel' => 'Diesel',
        'e5' => 'Super E5',
        'e10' => 'Super E10',
    ];

    public function __construct(private readonly FuelPriceService $service)
    {
    }

    public function render(array $config): string
    {
        $id = wp_unique_id('afdsp-compact-');
        $results = [];

        foreach (array_keys(self::LABELS) as $fuel) {
            try {
                $results[$fuel] = $this->service->get($config['box'], $fuel);
            } catch (\Throwable $error) {
                $results[$fuel] = null;
                error_log('AfD Spritpreise compact (' . $fuel . '): ' . $error->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            }
        }

        if (!array_filter($results)) {
            return '<section class="afdsp afdsp--compact afdsp--compact-site afdsp--error" role="status">'
                . esc_html__('Aktuelle Kraftstoffpreise sind derzeit nicht verfügbar.', 'afd-spritpreise')
                . '</section>';
        }

        $label = $config['areaLabel'] . ' · ' . self::LABELS[$config['defaultFuel']];

        ob_start();
        ?>
        <section id="<?php echo esc_attr($id); ?>" class="afdsp afdsp--compact afdsp--compact-site" aria-label="<?php echo esc_attr($label); ?>" data-afdsp-tabs>
            <?php if ($config['showTitle']) : ?>
                <h2 class="afdsp-compact-site-title"><?php esc_html_e('Kraftstoffpreise mit der AfD', 'afd-spritpreise'); ?></h2>
            <?php endif; ?>

            <div class="afdsp-compact-site-card">
                <div class="afdsp-compact-site-tabs wp-block-buttons" role="tablist" aria-label="<?php esc_attr_e('Kraftstoffart', 'afd-spritpreise'); ?>">
                    <?php foreach (self::LABELS as $fuel => $fuelLabel) : ?>
                        <?php $active = $fuel === $config['defaultFuel']; ?>
                        <div class="wp-block-button afdsp-compact-site-tab-wrap">
                            <button
                                class="wp-block-button__link wp-element-button afdsp-tab afdsp-compact-site-tab<?php echo $active ? ' is-active' : ''; ?>"
                                type="button"
                                role="tab"
                                id="<?php echo esc_attr($id . '-tab-' . $fuel); ?>"
                                aria-controls="<?php echo esc_attr($id . '-panel-' . $fuel); ?>"
                                aria-selected="<?php echo $active ? 'true' : 'false'; ?>"
                                aria-pressed="<?php echo $active ? 'true' : 'false'; ?>"
                                tabindex="<?php echo $active ? '0' : '-1'; ?>"
                                data-afdsp-tab="<?php echo esc_attr($fuel); ?>"
                            ><?php echo esc_html($fuelLabel); ?></button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php foreach (self::LABELS as $fuel => $fuelLabel) : ?>
                    <div
                        class="afdsp-compact-site-panel"
                        id="<?php echo esc_attr($id . '-panel-' . $fuel); ?>"
                        role="tabpanel"
                        aria-labelledby="<?php echo esc_attr($id . '-tab-' . $fuel); ?>"
                        data-afdsp-panel="<?php echo esc_attr($fuel); ?>"
                        <?php echo $fuel !== $config['defaultFuel'] ? ' hidden' : ''; ?>
                    >
                        <?php if (!$results[$fuel]) : ?>
                            <p class="afdsp-unavailable" role="status"><?php esc_html_e('Aktuelle Kraftstoffpreise sind derzeit nicht verfügbar.', 'afd-spritpreise'); ?></p>
                        <?php else : ?>
                            <?php echo $this->price_panel($results[$fuel], $config); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
        return (string) ob_get_clean();
    }

    private function price_panel(array $result, array $config): string
    {
        $calc = $result['calculation'];
        $intro = sprintf(
            __('Median aus %1$d Tankstellen im Gebiet %2$s.', 'afd-spritpreise'),
            $result['station_count'],
            $config['areaLabel']
        );

        ob_start();
        ?>
        <?php if ($result['stale']) : ?>
            <p class="afdsp-stale" role="status"><?php esc_html_e('Zuletzt verfügbare Preise', 'afd-spritpreise'); ?></p>
        <?php endif; ?>

        <div class="afdsp-compact-site-prices">
            <div class="afdsp-compact-site-price afdsp-compact-site-price--current">
                <p class="afdsp-compact-site-label afdsp-compact-site-label--current"><?php esc_html_e('aktueller Preis', 'afd-spritpreise'); ?></p>
                <p class="afdsp-compact-site-value afdsp-compact-site-value--current"><?php echo esc_html($this->price($calc['current_gross'])); ?></p>
            </div>

            <div class="afdsp-compact-site-price afdsp-compact-site-price--scenario">
                <p class="afdsp-compact-site-label"><?php esc_html_e('AfD-Preis*', 'afd-spritpreise'); ?></p>
                <p class="afdsp-compact-site-value afdsp-compact-site-value--scenario"><?php echo esc_html($this->price($calc['scenario_gross'])); ?></p>
            </div>

            <div class="afdsp-compact-site-price afdsp-compact-site-price--saving">
                <p class="afdsp-compact-site-saving-percent"><?php echo esc_html($this->number($calc['saving_percent'], 1) . ' % weniger'); ?></p>
                <p class="afdsp-compact-site-value afdsp-compact-site-value--saving"><?php echo esc_html($this->number($calc['saving_cents'], 1) . ' ct/l'); ?></p>
                <?php if ($config['showTankSaving']) : ?>
                    <p class="afdsp-compact-site-tank-saving">
                        <span><?php esc_html_e('bei 50 Litern ', 'afd-spritpreise'); ?></span>
                        <span><?php echo esc_html($this->money($calc['saving_50l']) . ' weniger'); ?></span>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="afdsp-compact-site-source">
            <span><?php echo esc_html($intro); ?></span>
            <span><?php esc_html_e('Preisdaten: TankPuls · MTS-K', 'afd-spritpreise'); ?></span>
        </div>
        <?php
        return (string) ob_get_clean();
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
}
