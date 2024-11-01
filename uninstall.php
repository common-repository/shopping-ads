<?php

global $wpdb;

$generated_key_id = get_option('shopping_ADS_generated_key_id', -1);

if ($generated_key_id > 0) {

    $wpdb->delete(
        $wpdb->prefix . 'woocommerce_api_keys',
        array( 'key_id' => $generated_key_id ),
        array( '%d' )
    );

}

delete_option('shopping_ADS_generated_key_id');

delete_option('shopping_ADS_generated_consumer_key');