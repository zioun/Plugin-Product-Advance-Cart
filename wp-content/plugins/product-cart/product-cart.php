<?php
/**
 * Plugin Name: Product Cart
 * Description: WooCommerce Product Loop widget with gallery slider and lightbox.
 * Version: 1.1.0
 * Requires Plugins: elementor, woocommerce
 * Text Domain: product-cart
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PC_LOOP_PATH', plugin_dir_path( __FILE__ ) );
define( 'PC_LOOP_URL', plugin_dir_url( __FILE__ ) );

function pc_loop_register_assets() {
    wp_register_style(
        'product-cart-loop',
        PC_LOOP_URL . 'assets/product-cart-loop.css',
        array(),
        '1.1.0'
    );

    wp_register_script(
        'product-cart-loop',
        PC_LOOP_URL . 'assets/product-cart-loop.js',
        array(),
        '1.1.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'pc_loop_register_assets' );

function pc_loop_register_widget( $widgets_manager ) {
    if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
        return;
    }

    $file = PC_LOOP_PATH . 'widgets/product-cart-loop-widget.php';

    if ( ! file_exists( $file ) ) {
        return;
    }

    require_once $file;

    if ( class_exists( '\Product_Cart_Loop_Widget' ) ) {
        $widgets_manager->register(
            new \Product_Cart_Loop_Widget()
        );
    }
}
add_action(
    'elementor/widgets/register',
    'pc_loop_register_widget'
);
