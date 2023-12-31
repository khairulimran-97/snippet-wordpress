// Hook to add a custom admin menu
add_action('admin_menu', 'custom_shipping_admin_menu');

function custom_shipping_admin_menu() {
    add_menu_page(
        'Shipping Methods',
        'Shipping Methods',
        'manage_options',
        'custom-shipping-admin',
        'custom_shipping_admin_page'
    );
}
function enqueue_datepicker() {
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-datepicker-style', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
	wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);
    wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '4.0.13');
}

add_action('admin_enqueue_scripts', 'enqueue_datepicker');


function custom_shipping_admin_page() {
    ?>
    <div class="wrap">
        <h2>Shipping Methods</h2>
		<form method="post" action="">
        <table class="widefat">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Shipping Method</th>
                    <th>Method Type</th>
                    <th>Normal Price</th>
					<th>Modify Price</th>
					<th>Start Date</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $zone_ids = array_keys(WC_Shipping_Zones::get_zones());

                foreach ($zone_ids as $zone_id) {
                    $shipping_zone = new WC_Shipping_Zone($zone_id);
                    $zone_name = $shipping_zone->get_zone_name();

                    $shipping_methods = $shipping_zone->get_shipping_methods(false, 'values');

                    foreach ($shipping_methods as $instance_id => $shipping_method) {
						 $saved_value = get_option('custom_shipping_modified_price_' . $instance_id);
						 $start_date = get_option('custom_shipping_start_date_' . $instance_id);
                         $end_date = get_option('custom_shipping_end_date_' . $instance_id);
                        ?>
                        <tr>
                            <td><?php echo $instance_id; ?></td>
                            <td><?php echo $shipping_method->get_title() . ' - ' . $zone_name; ?></td>
                            <td><?php echo get_shipping_method_type($shipping_method); ?></td>
                            <td>
							<?php
							$method_type = get_shipping_method_type($shipping_method);
							if ($method_type === 'Table Rate') {
								$normal_price = '-';
							} elseif ($method_type === 'Free Shipping') {
								$normal_price = 'Free';
							} else {
								$normal_price = wc_price($shipping_method->get_option('cost'));
							}
							echo $normal_price;
							?>
						    </td>
							<td><input type="text" name="modify_price[<?php echo $instance_id; ?>]" value="<?php echo esc_attr($saved_value); ?>" /></td>
							<td><input type="text" class="datepicker" name="start_date[<?php echo $instance_id; ?>]" value="<?php echo esc_attr($start_date); ?>" /></td>
                            <td><input type="text" class="datepicker" name="end_date[<?php echo $instance_id; ?>]" value="<?php echo esc_attr($end_date); ?>" /></td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
		    <p class="submit">
                <input type="submit" name="save_prices" class="button-primary" value="Save Prices"/>
            </p>
        </form>

		<div>
		<div class="product-list-container">	
		<!-- New form for disabling shipping methods with Select2 -->
        <form method="post" action="">
            <h2>Disable Shipping Methods</h2>
            <label for="disabled_methods">Select shipping methods to disable:</label>
            <select name="disabled_methods[]" multiple="multiple" class="custom-select2">
                <?php
                foreach ($zone_ids as $zone_id) {
                    $shipping_zone = new WC_Shipping_Zone($zone_id);
                    $shipping_methods = $shipping_zone->get_shipping_methods(false, 'values');

                    foreach ($shipping_methods as $instance_id => $shipping_method) {
                        $method_type = get_shipping_method_type($shipping_method);
                        $method_type_sanitized = str_replace(' ', '_', strtolower($method_type));
                        $option_value = $method_type_sanitized . ':' . $instance_id;
                        $selected = in_array($option_value, get_option('custom_shipping_disabled_methods', array())) ? 'selected="selected"' : '';
                        echo '<option value="' . esc_attr($option_value) . '" ' . $selected . '>' . esc_html($shipping_method->get_title()) . '</option>';
                    }
                }
                ?>
            </select>

            <div style="display: flex; align-items: center; margin-top: 10px; width: 50%;">
                <label for="start_date" style="margin-right: 10px;">Start Date:</label>
                <input type="text" class="datepicker" name="start_date" style="flex: 1;" value="<?php echo esc_attr(get_option('disable_shipping_start_date', '')); ?>" />
                <label for="end_date" style="margin-left: 10px; margin-right: 10px;">End Date:</label>
                <input type="text" class="datepicker" name="end_date" style="flex: 1;" value="<?php echo esc_attr(get_option('disable_shipping_end_date', '')); ?>" />
            </div>
			
            <div class="save-status">
                <input type="submit" name="disable_shipping" class="button-secondary" value="Disable Selected Shipping Methods"/>
            </div>
        </form></div></div>
		
        <script>
            jQuery(document).ready(function ($) {
                // Add datepicker to the date fields
                $('.datepicker').datepicker({
                    dateFormat: 'yy-mm-dd'
                });
				 $('.custom-select2').select2();
            });
        </script>
		<style>
        /* Style the product list container */
        .product-list-container {
            max-width: 100%;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        /* Style the multi-select dropdown */
        .select2 {
            width: 100%!important;
        }

        .save-status {
            margin-top: 20px;
        }

        .save-button {
            padding: 12px;
        }
    </style>
    </div>
    <?php
	
// Handle saving and deleting modified prices, start dates, and end dates
    if (isset($_POST['save_prices'])) {
        $modified_prices = $_POST['modify_price'];
        $start_dates = $_POST['start_date'];
        $end_dates = $_POST['end_date'];

        foreach ($modified_prices as $instance_id => $modified_price) {
            // Validate and sanitize the input as needed
            $modified_price = floatval($modified_price);
            $start_date = sanitize_text_field($start_dates[$instance_id]);
            $end_date = sanitize_text_field($end_dates[$instance_id]);

            // Save the modified price if > 0, delete if 0 or empty
            if ($modified_price > 0) {
                update_option('custom_shipping_modified_price_' . $instance_id, $modified_price);
                update_option('custom_shipping_start_date_' . $instance_id, $start_date);
            	update_option('custom_shipping_end_date_' . $instance_id, $end_date);
            } else {
                delete_option('custom_shipping_modified_price_' . $instance_id);
				delete_option('custom_shipping_start_date_' . $instance_id, $start_date);
            	delete_option('custom_shipping_end_date_' . $instance_id, $end_date);
            }
        }

        // Redirect back to the same page after saving
        wp_redirect(admin_url('admin.php?page=custom-shipping-admin'));
        exit;
    }
	

    // Handle disabling shipping methods
    if (isset($_POST['disable_shipping'])) {
        $disabled_methods = isset($_POST['disabled_methods']) ? $_POST['disabled_methods'] : array();

        // Convert method type to lowercase and replace spaces with underscores before saving
        $disabled_methods_sanitized = array_map(function ($value) {
            list($method_type, $instance_id) = explode(':', $value);
            $method_type_sanitized = str_replace(' ', '_', strtolower($method_type));
            return $method_type_sanitized . ':' . $instance_id;
        }, $disabled_methods);

        // Save disabled methods as a single option entry
        update_option('custom_shipping_disabled_methods', $disabled_methods_sanitized);
        update_option('disable_shipping_start_date', sanitize_text_field($_POST['start_date']));
        update_option('disable_shipping_end_date', sanitize_text_field($_POST['end_date']));

        // Redirect back to the same page after saving
        wp_redirect(admin_url('admin.php?page=custom-shipping-admin'));
        exit;
    }
	
}

function get_shipping_method_type($shipping_method) {
	$class_name = get_class($shipping_method);
	
    if (strpos($class_name, 'WC_Shipping_Flat_Rate') !== false) {
        return 'Flat Rate';
    } elseif (strpos($class_name, 'WC_Shipping_Free_Shipping') !== false) {
        return 'Free Shipping';
    } elseif (strpos($class_name, 'WC_Shipping_Local_Pickup') !== false) {
        return 'Local Pickup';
    } elseif (strpos($class_name, 'WC_Shipping_Table_Rate') !== false) {
        return 'Table Rate';
    } else {
        return 'Unknown';
    }
}

add_filter('woocommerce_package_rates', 'custom_modify_shipping_rates', 100, 2);

function custom_modify_shipping_rates($rates, $package) {
    // Set timezone to Asia/Kuala_Lumpur
    date_default_timezone_set('Asia/Kuala_Lumpur');

    // Get current date
    $current_date = date('Y-m-d');

    // Iterate through each shipping method
    foreach ($rates as $rate_id => $rate) {
        // Extract shipping method instance ID from rate ID
        $instance_id = '';

        // Check if the shipping method ID contains ':'
        if (strpos($rate->id, ':') !== false) {
            list(, $instance_id) = explode(':', $rate->id);
        }

        // Check if the shipping method has a modified price and is within the date range
        $modified_price = get_option('custom_shipping_modified_price_' . $instance_id);
        $start_date = get_option('custom_shipping_start_date_' . $instance_id);
        $end_date = get_option('custom_shipping_end_date_' . $instance_id);

        if ($modified_price > 0 && $current_date >= $start_date && $current_date <= $end_date) {
            // Set the modified price
            $rates[$rate_id]->cost = $modified_price;

            // Update tax based on the modified price
            $rates[$rate_id]->taxes[1] = $modified_price * 0.2;
        }
    }

    return $rates;
}

function filter_woocommerce_package_rates( $rates, $package ) {
    // Get the list of disabled shipping methods from wp_options
    $disabled_methods = get_option( 'custom_shipping_disabled_methods', array() );

    // Set the time zone to Asia/Kuala_Lumpur
    date_default_timezone_set( 'Asia/Kuala_Lumpur' );

    // Get the current date in the specified time zone
   $current_date = date('Y-m-d');

    // Get the start and end dates for disabling shipping methods
    $disable_shipping_start_date = get_option( 'disable_shipping_start_date', '' );
    $disable_shipping_end_date = get_option( 'disable_shipping_end_date', '' );

    // Check if today's date is within the specified range
    if ( $current_date >= $disable_shipping_start_date && $current_date <= $disable_shipping_end_date ) {
        // Loop through rates
        foreach ( $rates as $rate_id => $rate ) {
            // Check if the rate ID is in the list of disabled methods
            if ( in_array( $rate_id, $disabled_methods ) ) {
                // Remove disabled methods
                unset( $rates[ $rate_id ] );
            } elseif ( strpos( $rate_id, 'table_rate:' ) === 0 && in_array( 'table_rate:' . explode( ':', $rate_id )[1], $disabled_methods ) ) {
                // Additional condition: Only remove 'table_rate:id:*' if 'table_rate:id' exists in the array
                unset( $rates[ $rate_id ] );
            }
        }
    }

    return $rates;
}

add_filter( 'woocommerce_package_rates', 'filter_woocommerce_package_rates', 10, 2 );

