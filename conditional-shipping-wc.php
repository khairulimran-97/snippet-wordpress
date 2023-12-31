// Step 1: Create a Custom Admin Menu
function add_shipping_list_menu() {
    add_menu_page(
        'Shipping List Woocommerce',
        'Condition Shipping',
        'manage_options',
        'shipping_list_page',
        'display_shipping_list_page',
		'dashicons-cart'
    );
}
add_action('admin_menu', 'add_shipping_list_menu');

function enqueue_datepicker2() {
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-datepicker-style', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
}

add_action('admin_enqueue_scripts', 'enqueue_datepicker2');

// Step 2: Displaying the Custom Admin Page
function display_shipping_list_page() {
    // Step 3: Fetch and Display Data
    global $wpdb;

    $table_name = $wpdb->prefix . 'woocommerce_shipping_zone_methods';
    $data = $wpdb->get_results("SELECT * FROM $table_name ORDER BY zone_id, method_order");

    echo '<div class="wrap">';
    echo '<h1>Conditional Shipping Woocommerce</h1>';

    // Step 4: Display Tables
    $current_zone_id = null;

	echo '<form method="post" action="">';
    foreach ($data as $row) {
    $shipping_name = bbloomer_get_shipping_name_by_instance_id($row->instance_id);
	$shipping_status_option = get_option('shipping_status_' . $row->instance_id, false);
	$default_value = ($shipping_status_option === false) ? $row->is_enabled : $shipping_status_option;
	$shipping_promo_option = get_option('shipping_promo_status_' . $row->instance_id, 0); // Set default to 0
    $promoStatusData = json_decode($shipping_promo_option, true);
    $promoStatus = isset($promoStatusData['promo_status']) ? $promoStatusData['promo_status'] : 0;
	$start_date = isset($promoStatusData['promo_start_date']) ? esc_attr($promoStatusData['promo_start_date']) : '';
    $end_date = isset($promoStatusData['promo_end_date']) ? esc_attr($promoStatusData['promo_end_date']) : '';
		
        if ($row->zone_id !== $current_zone_id) {
            if ($current_zone_id !== null) {
                echo '</tbody></table>';
            }

            $zone_name = bbloomer_get_zone_name_by_id($row->zone_id);
            echo '<h2>Zone ID ' . $row->zone_id . ' : ' . $zone_name . '</h2>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>
			<tr>
			<th>Instance ID</th>
			<th>Shipping Name</th>
			<th>Current Status</th>
			<th>Status Default</th>
			<th>Promo Start Date</th>
            <th>Promo End Date</th>
			<th>Status For Campaign</th>
			</tr>
			</thead>';
            echo '<tbody>';
            
            $current_zone_id = $row->zone_id;}

			echo '<tr>';
			echo '<td>' . $row->instance_id .' - '. $row->method_id . '</td>';
			echo '<td>' . $shipping_name . '</td>';
			$status_class = $row->is_enabled == 1 ? 'enabled' : 'disabled';
			echo '<td class="' . $status_class . '">' . ($row->is_enabled == 1 ? 'Enable' : 'Disable') . '</td>';
			echo '<td class="toggle-container">';
			echo '<div class="toggle-switch">
			<input type="checkbox" id="shipping_status_' . $row->instance_id . '" name="shipping_status[' . $row->instance_id . ']" value="1" ' . checked($default_value, 1, false) . '>
			<label for="shipping_status_' . $row->instance_id . '"></label></div>';
			echo '</td>';
			echo '<td><input type="text" class="datepicker" name="promo_start_date[' . $row->instance_id . ']" value="' . esc_attr($start_date) . '" /></td>';
			echo '<td><input type="text" class="datepicker" name="promo_end_date[' . $row->instance_id . ']" value="' . esc_attr($end_date) . '" /></td>';
			echo '<td class="toggle-container">';
			echo '<select id="shipping_promo_status_' . $row->instance_id . '" name="shipping_promo_status[' . $row->instance_id . ']">';
			echo '<option value="1" ' . selected($promoStatus, 1, false) . '>Enabled</option>';
			echo '<option value="0" ' . selected($promoStatus, 0, false) . '>Disabled</option>';
			echo '</select>';
			echo '</td>';
			echo '</tr>';}

			echo '</tbody></table>';
			echo '<button class="button-primary" type="submit" name="save_shipping_status">Save</button>';
			echo '</form>'; 
			echo '</div>';

			echo '<style>';
			echo '.enabled { background-color: #aaffaa; }';
			echo '.disabled { background-color: #ffaaaa; }';
			echo '</style>';
	
	    	?>
			<style>
			/* Add this to your CSS */
			.toggle-container {
				text-align: center;
			}

			.toggle-switch {
				position: relative;
				margin-left:20px;
				width: 60px;
				height: 34px;
			}

			.toggle-switch input {
				display: none;
			}

			.toggle-switch label {
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				background-color: #ccc;
				border-radius: 34px;
				cursor: pointer;
			}

			.toggle-switch label:before {
				position: absolute;
				content: '';
				height: 26px;
				width: 26px;
				left: 4px;
				bottom: 4px;
				background-color: white;
				border-radius: 50%;
				transition: 0.3s;
			}

			.toggle-switch input:checked + label {
				background-color: #4CAF50;
			}

			.toggle-switch input:checked + label:before {
				transform: translateX(26px);
			}
			.button-primary{
				margin-top:20px!important;
				padding-left:40px!important;
				padding-right:40px!important;
				font-size:14px!important;
			}
		</style>
		<script>
			jQuery(document).ready(function ($) {
					// Add datepicker to the date fields
					$('.datepicker').datepicker({
						dateFormat: 'yy-mm-dd'
					});
				});	
		</script>
    <?php

}

// Handle form submission
function handle_shipping_status_save() {
    if (isset($_POST['save_shipping_status'])) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'woocommerce_shipping_zone_methods';
        $data = $wpdb->get_results("SELECT * FROM $table_name ORDER BY zone_id, method_order");

        foreach ($data as $row) {
            $instanceId = $row->instance_id;

            // Handle is_enabled status (checkbox)
            $status = isset($_POST['shipping_status'][$instanceId]) ? 1 : 0;

            // Handle promo status (dropdown)
            $promoStatus = isset($_POST['shipping_promo_status'][$instanceId]) ? intval($_POST['shipping_promo_status'][$instanceId]) : 0;

            // Update shipping status option
            update_option('shipping_status_' . $instanceId, $status);

            // Update shipping promo status option as an array
            $promoStatusData = array(
                'promo_status' => $promoStatus,
                'shipping_id' => $instanceId,
                'zone' => $row->zone_id,
                'promo_start_date' => sanitize_text_field($_POST['promo_start_date'][$instanceId]),
                'promo_end_date' => sanitize_text_field($_POST['promo_end_date'][$instanceId]),
            );

            update_option('shipping_promo_status_' . $instanceId, json_encode($promoStatusData));
        }

        echo '<div class="updated"><p>Status saved successfully!</p></div>';
    }
}

add_action('admin_init', 'handle_shipping_status_save');

// Function to get shipping name based on instance_id
function bbloomer_get_shipping_name_by_instance_id($instance_id) {
    foreach (bbloomer_get_all_shipping_zones() as $zone) {
        $zone_shipping_methods = $zone->get_shipping_methods();
        foreach ($zone_shipping_methods as $method) {
            if ($method->get_instance_id() == $instance_id) {
                return $method->get_title();
            }
        }
    }

    return 'N/A';
}

// Function to get zone name based on zone_id
function bbloomer_get_zone_name_by_id($zone_id) {
    foreach (bbloomer_get_all_shipping_zones() as $zone) {
        if ($zone->get_id() == $zone_id) {
            return $zone->get_zone_name();
        }
    }

    return 'N/A';
}


function bbloomer_get_all_shipping_zones() {
   $data_store = WC_Data_Store::load( 'shipping-zone' );
   $raw_zones = $data_store->get_zones();
   foreach ( $raw_zones as $raw_zone ) {
      $zones[] = new WC_Shipping_Zone( $raw_zone );
   }
   return $zones;
}

function update_shipping_methods_on_refresh() {
    global $wpdb;

    // Set timezone to Asia/Kuala_Lumpur
    date_default_timezone_set('Asia/Kuala_Lumpur');

    // Get current date
    $current_date = date('Y-m-d');

    // Get all shipping methods instance IDs
    $instance_ids = $wpdb->get_col("SELECT instance_id FROM {$wpdb->prefix}woocommerce_shipping_zone_methods");

    foreach ($instance_ids as $instance_id) {
        // Get the option name for the current instance ID
        $option_name = 'shipping_promo_status_' . $instance_id;

        // Check if the option exists and has a valid JSON format
        if (get_option($option_name) && is_array(json_decode(get_option($option_name), true))) {
            // Get the promo data for the current instance ID
            $promo_data = json_decode(get_option($option_name), true);

            // Check if today is within the promo start and end dates
            if (
                isset($promo_data['promo_start_date']) &&
                isset($promo_data['promo_end_date']) &&
                $current_date >= $promo_data['promo_start_date'] &&
                $current_date <= $promo_data['promo_end_date']
            ) {
                // Get the current is_enabled value
                $current_is_enabled = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT is_enabled FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE instance_id = %d",
                        $instance_id
                    )
                );

                // Update wp_woocommerce_shipping_zone_methods based on promo_status only if needed
                if ($current_is_enabled !== $promo_data['promo_status']) {
                    $wpdb->update(
                        "{$wpdb->prefix}woocommerce_shipping_zone_methods",
                        array('is_enabled' => $promo_data['promo_status']),
                        array('instance_id' => $instance_id)
                    );
                }
            } else {
                // If outside promo date range, update based on 'shipping_status_*'
                $status_option_name = 'shipping_status_' . $instance_id;

                // Check if the option exists and has a valid value
                if (get_option($status_option_name) !== false) {
                    $status_value = get_option($status_option_name);

                    // Get the current is_enabled value
                    $current_is_enabled = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT is_enabled FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE instance_id = %d",
                            $instance_id
                        )
                    );

                    // Update wp_woocommerce_shipping_zone_methods based on shipping_status_* only if needed
                    if ($current_is_enabled !== $status_value) {
                        $wpdb->update(
                            "{$wpdb->prefix}woocommerce_shipping_zone_methods",
                            array('is_enabled' => $status_value),
                            array('instance_id' => $instance_id)
                        );
                    }
                }
            }
        }
    }
}

// Hook the function to the init action
add_action('init', 'update_shipping_methods_on_refresh');
