<?php
/**
 * Plugin Name: WC Loop Variations Block
 * Description: Display WooCommerce variations inside product loops (FSE / Query Loop compatible).
 * Version: 1.0.0
 * Author: Toni Q
 * Text Domain: wc-loop-variations-block
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/*
|--------------------------------------------------------------------------
| Register Block
|--------------------------------------------------------------------------
*/

add_action( 'init', function() {

    register_block_type( __DIR__, array(
        'render_callback' => 'wc_loop_variations_render_block'
    ) );

});

/*
|--------------------------------------------------------------------------
| Render Variations
|--------------------------------------------------------------------------
*/

function wc_loop_variations_render_block() {

    if ( ! function_exists( 'wc_get_product' ) ) return '';

    global $post;
    if ( ! $post ) return '';

    $product = wc_get_product( $post->ID );

    if ( ! $product || ! $product->is_type( 'variable' ) ) return '';

    $variation_ids = $product->get_children();

    if ( empty( $variation_ids ) ) return '';

    $grouped = array();

    foreach ( $variation_ids as $variation_id ) {

        $variation = wc_get_product( $variation_id );
        if ( ! $variation ) continue;

        foreach ( $variation->get_attributes() as $taxonomy => $term_slug ) {

            $taxonomy_clean = str_replace( 'attribute_', '', $taxonomy );

            if ( taxonomy_exists( $taxonomy_clean ) ) {
                $term = get_term_by( 'slug', $term_slug, $taxonomy_clean );
                $label = $term ? $term->name : $term_slug;
                $attr_label = wc_attribute_label( $taxonomy_clean );
            } else {
                $label = $term_slug;
                $attr_label = ucfirst( $taxonomy_clean );
            }

            if ( ! isset( $grouped[ $attr_label ] ) ) {
                $grouped[ $attr_label ] = array();
            }

            if ( ! isset( $grouped[ $attr_label ][ $label ] ) ) {

                $grouped[ $attr_label ][ $label ] = array(
                    'price' => $variation->get_price_html(),
                    'stock' => $variation->is_in_stock() ? 'In stock' : 'Out of stock'
                );

            }
        }
    }

    if ( empty( $grouped ) ) return '';

    ob_start();
    ?>

    <div class="wc-loop-variations">
        <?php foreach ( $grouped as $attr => $values ) : ?>
            <div class="wc-loop-variation-group">
                <strong><?php echo esc_html( $attr ); ?>:</strong>
                <?php
                $items = array();
                foreach ( $values as $name => $info ) {

                    $text = esc_html( $name );

                    if ( ! empty( $info['price'] ) ) {
                        $text .= ' (' . wp_kses_post( $info['price'] ) . ')';
                    }

                    $text .= ' [' . esc_html( $info['stock'] ) . ']';

                    $items[] = $text;
                }

                echo implode( ', ', $items );
                ?>
            </div>
        <?php endforeach; ?>
    </div>

    <style>
    .wc-loop-variations {
        font-size:13px;
        margin-top:8px;
        line-height:1.4;
    }
    .wc-loop-variation-group {
        margin-bottom:4px;
    }
    @media (max-width:768px){
        .wc-loop-variations { font-size:12px; }
    }
    </style>

    <?php

    return ob_get_clean();
}
