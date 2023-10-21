<?php

function submission_values_shortcode() {
    global $wpdb;
	
	// Enqueue the JavaScript library
    wp_enqueue_script('xlsx-library', 'https://unpkg.com/xlsx/dist/xlsx.full.min.js', array(), '1.0', true);


    // Query the wp_e_submissions_values table
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}e_submissions_values");

    // Organize the data into an associative array
    $data = array();
    foreach ($results as $result) {
        $submission_id = $result->submission_id;
        $key = $result->key;
        $value = $result->value;

        if (!isset($data[$submission_id])) {
            $data[$submission_id] = array();
        }

        $data[$submission_id][$key] = $value;
    }

    // Filter data by $affiliate_username
    $affiliate_username = do_shortcode('[affiliate_username]');
    $filtered_data = array_filter($data, function($submission_data) use ($affiliate_username) {
        $referrer = isset($submission_data['referrer']) ? $submission_data['referrer'] : '';
        return $referrer == $affiliate_username;
    });

    // Pagination setup
    $total_items = count($filtered_data);
    $items_per_page = 2;
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = max(1, get_query_var('cpage'));
    $offset = ($current_page - 1) * $items_per_page;
    $paged_data = array_slice($filtered_data, $offset, $items_per_page, true);

    // Generate the HTML table with CSS styles
    $output = '<style>
        .submission-table {
            width: 100%;
            border-collapse: collapse;
        }
        .submission-table th, .submission-table td {
            padding: 10px;
            border: 1px solid #ccc;
        }
        .submission-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .submission-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
		
		@media (max-width: 768px) {
    .submission-table th, .submission-table td {
        padding: 5px;
    }
    .submission-table th {
        font-size: 12px;
    }
    .submission-table td {
        font-size: 12px;
    }
}
        .submission-pagination {
            text-align: right;
            margin-top: 10px;
			margin-bottom:30px;
        }
        .submission-pagination span {
			padding-left: 10px;
			padding-right: 10px;
        }
        .submission-pagination a {
            display: inline-block;
            padding: 5px 10px;
            background-color: #f2f2f2;
            border: 1px solid #ccc;
            text-decoration: none;
            color: #333;
			border-width: 1px;
    		border-style: solid;
    		-webkit-appearance: none;
    		border-radius: 3px;
    		white-space: nowrap;
    		box-sizing: border-box;
			text-decoration: none!important;
        }
        .submission-pagination a:hover {
            background-color: #e0e0e0;
        }
		
		.disable{
		color: #a7aaad!important;
        border-color: #dcdcde!important;
    	background: #f6f7f7!important;
    	box-shadow: none!important;
    	cursor: default;
    	transform: none!important;
		padding: 10px;
		border-width: 1px;
    	border-style: solid;
    	-webkit-appearance: none;
    	border-radius: 3px;
   		white-space: nowrap;
   		box-sizing: border-box;
		
		}
		
		.export{
		padding: 4px;
		padding-left:10px;
		padding-right:10px;
		margin-left: 10px;
		background: #00b4ff !important;
		color: #fff!important;
		border-width: 1px;
    	border-style: solid;
		border-color: #dcdcde!important;
		}
		
    </style>';
	
	

	$output .= '<div class="submission-pagination">';
	if ($current_page > 1) {
    $output .= '<a href="'.add_query_arg('cpage', ($current_page - 1)).'">&laquo; Previous</a>';
	} else {
    $output .= '<span class="disable">&laquo; Previous</span>';
	}

	$output .= '<span>Page '.$current_page.' of '.$total_pages.'</span>';

	if ($current_page < $total_pages) {
    $output .= '<a href="'.add_query_arg('cpage', ($current_page + 1)).'">Next &raquo;</a>';
	} else {
    $output .= '<span class="disable" >Next &raquo;</span>';
	}
	
	 // Add export button
    $output .= '<button class="export" onclick="exportToExcel()">Export to Excel</button>';

	$output .= '</div>';


    $output .= '<table class="submission-table">';
    $output .= '<tr><th>Name</th><th>Email</th><th>Phone</th><th>Referrer By</th></tr>';

    foreach ($paged_data as $submission_data) {
        $name = isset($submission_data['name']) ? $submission_data['name'] : '';
        $email = isset($submission_data['email']) ? $submission_data['email'] : '';
        $phone = isset($submission_data['phone']) ? $submission_data['phone'] : '';
        $referrer = isset($submission_data['referrer']) ? $submission_data['referrer'] : '';

        $output .= "<tr><td style='width: 300px;'>$name</td><td style='width: 300px;'>$email</td><td>$phone</td><td>$referrer</td></tr>";
    }

    $output .= '</table>';
	
	// JavaScript function to handle export to Excel
    $output .= '<script>
        function exportToExcel() {
            // Create a new Excel workbook
            let wb = XLSX.utils.book_new();

            // Convert the filtered data to an array of arrays
            let data = Object.entries(' . json_encode($filtered_data) . ');
            let sheetData = data.map(([_, rowData]) => Object.values(rowData));

            // Create a worksheet from the data
            let ws = XLSX.utils.aoa_to_sheet(sheetData);

            // Add the worksheet to the workbook
            XLSX.utils.book_append_sheet(wb, ws, "Submissions");

            // Save the workbook as a file
            XLSX.writeFile(wb, "submissions.xlsx");
        }
    </script>';


    return $output;
}


add_shortcode('submission_values', 'submission_values_shortcode');
