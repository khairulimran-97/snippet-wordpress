<?php

add_filter( 'affwp_calc_referral_amount', 'my_custom_referral_amount_calculation', 10, 6 );
function my_custom_referral_amount_calculation( $referral_amount, $affiliate_id, $amount, $reference, $product_id, $context ) {
    $subtotal = $amount; // Use the checkout subtotal as the amount
    $quantity = 1; // Default to 1 if quantity cannot be detected
    if ( function_exists( 'WC' ) ) { // Check if WooCommerce is active
        $cart = WC()->cart;
        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            if ( $cart_item['product_id'] == $product_id ) {
                $quantity = $cart_item['quantity']; // Retrieve the quantity from the cart
                break;
            }
        }
    }
    $rate_type = affwp_get_affiliate_rate_type( $affiliate_id, $reference );
    if ( $rate_type == 'percentage' ) {
        // Set the custom referral rate based on the quantity of the product in the cart
        if ( $quantity == 1 ) {
            $custom_rate = 0.28; // 1 product = 28%
        } elseif ( $quantity >= 10 && $quantity < 20 ) {
            $custom_rate = 0.18; // 10-19 products = 18%
        } elseif ( $quantity >= 20 && $quantity < 35 ) {
            $custom_rate = 0.13; // 20-34 products = 13%
        } elseif ( $quantity >= 35 ) {
            $custom_rate = 0.08; // 35 or more products = 8%
        } else {
            $custom_rate = 0; // No commission if quantity is 0 or negative
        }
        $referral_amount = $subtotal * $custom_rate; // Calculate the referral amount based on the custom rate and subtotal
    }
    return (string) $referral_amount;
}
