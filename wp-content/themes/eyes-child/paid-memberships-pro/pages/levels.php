<?php
$levels = pmpro_getAllLevels(false, true);
?>

<div class="pricing-grid">
    <?php foreach($levels as $level) { ?>
        <div class="pricing-card">
            <h3><?php echo esc_html($level->name); ?></h3>

            <div class="price">
                <?php echo pmpro_formatPrice($level->initial_payment); ?>
            </div>

            <div class="description">
                <?php echo wp_kses_post($level->description); ?>
            </div>

            <a
              href="<?php echo pmpro_url('checkout', '?level=' . $level->id); ?>"
              class="btn-plan">
                Choose Plan
            </a>
        </div>
    <?php } ?>
</div>