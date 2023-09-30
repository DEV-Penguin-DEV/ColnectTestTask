<?php
require_once 'config.php'; // Include the database configuration file
require_once 'functions.php'; // Include function file

// Initialize a response array with isError flag
$response = ['isError' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve URL and HTML element from the POST request
    $url = $_POST['url'];
    $element = $_POST['element'];

    $startTime = microtime(true); // Measure the start time for performance tracking

    $htmlContent = fetchUrl($url); // Fetch HTML content from the specified URL

    // Measure the end time and calculate the duration of the request
    $endTime = microtime(true);
    $duration = round(($endTime - $startTime) * 1000);

    // Check if HTML content was successfully fetched
    if ($htmlContent) {
        $elementCount = countElement($htmlContent, $element); // Count the occurrences of the specified HTML element

        $domainId = getDomainId($url, $conn); // Retrieve or create a domain ID based on the URL

        createOrUpdateUniqueUrl($domainId, $url, $conn); // Create or update a unique URL in the database

        logPageLoadTime($domainId, $duration, $conn); // Log the page load time in the database

        addOrUpdateElement($element, $elementCount, $domainId, $conn); // Add or update the element in the database

        // Prepare the response data
        $response += [
            // Searching url
            'url' => $url,

            // Searching date and time
            'date' => date('d/m/Y H:i'),

            // Searching duration 
            'time' => $duration . 'msec',

            // Searching result message
            'message' => 'Element <' . htmlentities($element) . '> appeared ' . $elementCount . ' times in the page',

            // Total uniq urls with one domain
            'total_domain_urls' => getUniqueUrlCountForDomain($domainId, $conn),

            // The average load time for the last 24 hours
            'average_load_time' => getAverageLoadTimeLast24Hours($domainId, $conn) . 'msec',

            // Domain name
            'domain_name' => getDomainName($domainId, $conn),

            // Total count of element with all domains during all time
            'total_element_counts' => getElementCount($element, $conn),

            // Total count of element with one domain during all time
            'total_domain_element_counts' => getElementCount($element, $conn, $domainId),
        ];
    } else {
        // Handle the case where fetching HTML content failed
        $response = [
            'isError' => true,
            'error_message' => 'Invalid URL or HTML content.',
        ];
    }
} else {
    // Handle the case of an invalid request method
    $response = [
        'isError' => true,
        'error_message' => 'Invalid request method.',
    ];
}

header('Content-Type: application/json'); // Put Content header
echo json_encode($response); // Encode response

$conn->close(); // Close the database connection
?>