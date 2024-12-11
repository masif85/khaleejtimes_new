<?php
echo "<table border='1'>";

// Initialize a multi cURL handle
$multiCurl = curl_multi_init();

// Define an array to store individual curl handles
$curlHandles = [];

// Define an array of full request objects (example structure)
$requests = [
    [
        "url" => "https://loki.boxx.ai/",
        "client_id" => "x9vk",
        "access_token" => "478b112d-8bd1-49ad-ad97-1f44b023705c",
        "channel_id" => "DyMq",
        "rec_type" => "trending",
        "transaction_window" => "24",
        "boxx_token_id" => "17639b83-daa6-49b1-ad35-9fdef09f4434",
        "num" => 3,
        "offset" => 25
    ],
    [
        "url" => "https://loki.boxx.ai/",
        "client_id" => "x9vk",
        "access_token" => "478b112d-8bd1-49ad-ad97-1f44b023705c",
        "channel_id" => "DyMq",
        "rec_type" => "recommended",
        "transaction_window" => "48",
        "boxx_token_id" => "17639b83-daa6-49b1-ad35-9fdef09f4434",
        "num" => 5,
        "offset" => 30
    ],
    [
        "url" => "https://loki.boxx.ai/",
        "client_id" => "x9vk",
        "access_token" => "478b112d-8bd1-49ad-ad97-1f44b023705c",
        "channel_id" => "DyMq",
        "rec_type" => "new_arrivals",
        "transaction_window" => "72",
        "boxx_token_id" => "17639b83-daa6-49b1-ad35-9fdef09f4434",
        "num" => 4,
        "offset" => 15
    ]
];

// Loop through each request object and initialize a curl handle for each
foreach ($requests as $request) {
    $ch = curl_init();

    // Set options for each curl handle, passing the full object data
    curl_setopt_array($ch, array(
        CURLOPT_URL => $request['url'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode([
            "client_id" => $request['client_id'],
            "access_token" => $request['access_token'],
            "channel_id" => $request['channel_id'],
            "is_internal" => false,
            "is_boxx_internal" => false,
            "rec_type" => $request['rec_type'],
            "no_cache" => true,
            "related_action_as_view" => true,
            "related_action_type" => "view",
            "transaction_window" => $request['transaction_window'],
            "query" => [
                "userid" => "",
                "boxx_token_id" => $request['boxx_token_id'],
                "item_filters" => ["n_days_old" => ["\$lte" => 3]],
                "related_products" => [],
                "exclude" => [],
                "num" => $request['num'],
                "offset" => $request['offset'],
                "get_product_properties" => ["price", "brand"]
            ]
        ]),
        CURLOPT_HTTPHEADER => array('Content-Type: application/json')
    ));

    // Add the handle to the multi handle
    curl_multi_add_handle($multiCurl, $ch);
    
    // Store individual curl handles for later reference
    $curlHandles[] = $ch;
}

// Execute the multi handle
$running = null;
do {
    curl_multi_exec($multiCurl, $running);
} while ($running);

// Collect the responses
foreach ($curlHandles as $ch) {
    $response = curl_multi_getcontent($ch);  // Get the content from each handle
    echo "<tr><td>$response</td></tr>";      // Output the response in table format
    curl_multi_remove_handle($multiCurl, $ch); // Remove the handle from the multi handle
}

// Close the multi handle
curl_multi_close($multiCurl);

echo "</table>";
?>