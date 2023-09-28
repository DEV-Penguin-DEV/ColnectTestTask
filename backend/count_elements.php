<?php
// Create a MySQL connection here
// ...

// Function to fetch the HTML content of a URL
function fetchUrl($url)
{
    $ch = curl_init();

    // Set cURL options, including the user agent to mimic a real browser
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');

    $htmlContent = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        return false;
    }

    curl_close($ch);

    return $htmlContent; // HTML content as a string
}

// Function to count the specified HTML element
function countElement($html, $element)
{
    $dom = new DOMDocument();
    @$dom->loadHTML($html); // Use '@' to suppress warnings

    $elementCount = $dom->getElementsByTagName($element)->length;
    return $elementCount;
}

function generateResponseMessage($response)
{
    return "URL {$response['url']} Fetched on {$response['date']}, took {$response['response_time']}.\nElement <{$response['element']}> appeared {$response['element_count']} times on the page.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = $_POST['url'];
    $element = $_POST['element'];

    // Implement logic to check if the same request for the same URL and element
    // was made within the last 5 minutes. If so, return the previous response.

    $startTime = microtime(true);

    // Fetch the HTML content of the URL
    $htmlContent = fetchUrl($url);

    $endTime = microtime(true);
    $duration = round(($endTime - $startTime) * 1000); // Response time in msec

    if ($htmlContent) {
        // Count the specified HTML element
        $elementCount = countElement($htmlContent, $element);

        // Implement database update logic here
        // ...

        // Calculate statistics (i, ii, iii, iv)
        // ...

        // Prepare the response
        $response = [
            'url' => $url,
            'date' => date('d/m/Y H:i'),
            'response_time' => $duration . 'msec',
            'element_count' => $elementCount,
            'element' => htmlentities($element),
        ];

        // Return the response as JSON
        header('Content-Type: application/json');
        echo json_encode(['message' => generateResponseMessage($response)]);
    } else {
        echo 'Invalid URL or HTML content.';
    }
} else {
    echo 'Invalid request method.';
}
?>