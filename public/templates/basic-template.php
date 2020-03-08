<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_GPFA
 * @subpackage WP_GPFA/public/templates
 */
?>

<div class="gpfa-container">
  <div class="gpfa-container__img-container">
    <a href="<?php echo $product['staticLink']; ?>" target="_blank" rel="nofollow">
      <img class="gpfa-container__img-container__img" src="<?php echo $product['img']; ?>" alt="<?php echo $product['title']; ?>">
    </a>
  </div>
  <?php if( $product['title'] ): ?>
    <p class="gpfa-container__title">
      <a href="<?php echo $product['staticLink']; ?>" class="gpfa-container__title__link" target="_blank" rel="nofollow">
        <?php echo $product['title']; ?>
      </a>
    </p>
  <?php endif; ?>
  <?php if( is_user_logged_in() && current_user_can( 'manage_options' ) && $product['error']): ?>
    <?php echo 'Error: ' . $product['error'] ?>
  <?php endif; ?>
</div>
