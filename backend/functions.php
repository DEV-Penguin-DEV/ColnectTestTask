<?php
/**
 * Fetches HTML content from a given URL using cURL.
 *
 * @param string $url The URL to fetch content from.
 *
 * @return string|false The fetched HTML content or false on error.
 */
function fetchUrl($url)
{
    $ch = curl_init(); // Initialize cURL

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');

    $htmlContent = curl_exec($ch); // Execute the cURL request and get the HTML content

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        return false;
    }

    curl_close($ch); // Close the cURL session

    return $htmlContent;
}

/**
 * Counts the occurrences of a specified HTML element in the fetched HTML content.
 *
 * @param string $html    The HTML content to search in.
 * @param string $element The HTML element to count.
 *
 * @return int The count of the specified HTML element.
 */
function countElement($html, $element)
{
    // Create a DOMDocument to parse the HTML content
    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    // Return the count of the specified HTML element
    return $dom->getElementsByTagName($element)->length;
}

/**
 * Executes a prepared SQL query with parameters.
 *
 * @param mysqli  $conn      The database connection.
 * @param string  $sql       The SQL query with placeholders.
 * @param string  $types     A string specifying the types of parameters (e.g., "s" for string, "i" for integer).
 * @param mixed[] $params    An array of parameters to bind to the placeholders in the query.
 *
 * @return mysqli_result|bool The result set from the query or false on failure.
 */
function executeQuery($conn, $sql, $types, $params)
{
    $stmt = $conn->prepare($sql);

    // Check if the statement was prepared successfully
    if (!$stmt) {
        return false;
    }

    // Bind the parameters to the statement
    $stmt->bind_param($types, ...$params);

    // Execute the query
    $stmt->execute();

    // Get the result set
    $result = $stmt->get_result();

    return $result;
}

/**
 * Retrieves or creates a domain ID based on the URL.
 *
 * @param string  $url  The URL to extract the domain from.
 * @param mysqli  $conn The database connection.
 *
 * @return int The domain ID.
 */
function getDomainId($url, $conn)
{
    $domain = parse_url($url, PHP_URL_HOST); // Extract the domain from the URL

    // Check if the domain already exists in the database
    $sql = "SELECT id FROM domain WHERE name = ?";
    $result = executeQuery($conn, $sql, "s", [$domain]);

    if ($result->num_rows > 0) {
        // Domain exists, return its ID
        $row = $result->fetch_assoc();
        return $row["id"];
    } else {
        // Domain doesn't exist, insert it into the database
        $sql = "INSERT INTO domain (name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $domain);
        if ($stmt->execute()) {
            // Return the newly inserted domain's ID
            return $conn->insert_id;
        } else {
            return -1; // Return -1 on failure
        }
    }
}

/**
 * Creates or updates a unique URL entry in the database.
 *
 * @param int    $domainId The ID of the domain.
 * @param string $url      The URL to insert or update.
 * @param mysqli $conn     The database connection.
 */
function createOrUpdateUniqueUrl($domainId, $url, $conn)
{
    // Check if the URL already exists for the domain
    $sql = "SELECT id FROM unique_urls WHERE domain_id = ? AND url = ?";
    $result = executeQuery($conn, $sql, "is", [$domainId, $url]);

    if ($result->num_rows > 0) {
        // URL exists, increment its count
        incrementUrlCount($domainId, $conn);
    } else {
        // URL doesn't exist, insert it into the database
        $sql = "INSERT INTO unique_urls (domain_id, url) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $domainId, $url);
        $stmt->execute();
        incrementUrlCount($domainId, $conn);
    }
}

/**
 * Retrieves the count of unique URLs for a domain.
 *
 * @param int    $domainId The ID of the domain.
 * @param mysqli $conn     The database connection.
 *
 * @return int The count of unique URLs for the domain.
 */
function getUniqueUrlCountForDomain($domainId, $conn)
{
    // Query the database to get the count of unique URLs for the domain
    $sql = "SELECT COUNT(id) AS unique_url_count FROM unique_urls WHERE domain_id = ?";
    $result = executeQuery($conn, $sql, "i", [$domainId]);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["unique_url_count"];
    } else {
        return 0;
    }
}

/**
 * Logs page load time in the database.
 *
 * @param int    $domainId   The ID of the domain.
 * @param int    $loadTime   The page load time in milliseconds.
 * @param mysqli $conn       The database connection.
 */
function logPageLoadTime($domainId, $loadTime, $conn)
{
    // Get the current datetime
    $currentDatetime = date('Y-m-d H:i:s');

    // Insert page load time into the database
    $sql = "INSERT INTO page_load_time (domain_id, load_time, load_datetime) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ids", $domainId, $loadTime, $currentDatetime);
    $stmt->execute();
}

/**
 * Increments the URL count for a domain.
 *
 * @param int    $domainId The ID of the domain.
 * @param mysqli $conn     The database connection.
 */
function incrementUrlCount($domainId, $conn)
{
    // Increment the URL count for the domain
    $sql = "UPDATE url_count SET url_count = url_count + 1 WHERE domain_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $domainId);
    $stmt->execute();
}

/**
 * Adds or updates an element in the database.
 *
 * @param string $name      The name of the element.
 * @param int    $count     The count of the element.
 * @param int    $domain_id The ID of the domain.
 * @param mysqli $conn      The database connection.
 */
function addOrUpdateElement($name, $count, $domain_id, $conn)
{
    // Check if an element with the given name and domain exists
    $sql = "SELECT id FROM elements WHERE name = ? AND domain_id = ?";
    $result = executeQuery($conn, $sql, "si", [$name, $domain_id]);

    if ($result->num_rows > 0) {
        // If the element exists, update its count
        $row = $result->fetch_assoc();
        $elementId = $row["id"];
        $sql = "UPDATE elements SET count = count + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $count, $elementId);
        $stmt->execute();
    } else {
        // If the element doesn't exist, create a new element
        $sql = "INSERT INTO elements (name, count, domain_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $name, $count, $domain_id);
        $stmt->execute();
    }
}

/**
 * Retrieves the count of elements by name.
 *
 * @param string   $name     The name of the element.
 * @param mysqli   $conn     The database connection.
 * @param int|null $domainId The ID of the domain (optional).
 *
 * @return int The count of elements with the given name.
 */
function getElementCount($name, $conn, $domainId = null)
{
    if ($domainId != null) {
        // Get the sum for a specific domain
        $sql = "SELECT SUM(count) AS total_count FROM elements WHERE name = ? AND domain_id = ?";
    } else {
        // Get the sum for all domains
        $sql = "SELECT SUM(count) AS total_count FROM elements WHERE name = ?";
    }

    $stmt = $conn->prepare($sql);

    if ($domainId != null) {
        $stmt->bind_param("si", $name, $domainId);
    } else {
        $stmt->bind_param("s", $name);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["total_count"];
    } else {
        return 0; // Return 0 if no records with the given name exist
    }
}

/**
 * Retrieves the average load time for the last 24 hours.
 *
 * @param int    $domainId The ID of the domain.
 * @param mysqli $conn     The database connection.
 *
 * @return int The average load time in milliseconds.
 */
function getAverageLoadTimeLast24Hours($domainId, $conn)
{
    // Calculate the datetime for 24 hours ago
    $twentyFourHoursAgo = date('Y-m-d H:i:s', strtotime('-24 hours'));

    // Query the database to calculate the average load time
    $sql = "SELECT AVG(load_time) AS average_load_time
            FROM page_load_time
            WHERE domain_id = ?
            AND load_datetime >= ?";
    $result = executeQuery($conn, $sql, "is", [$domainId, $twentyFourHoursAgo]);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return intval($row["average_load_time"]);
    } else {
        return 0; // If no data is available, return 0 as the average load time
    }
}

/**
 * Retrieve the domain name
 *
 * @param int    $domainId The ID of the domain.
 * @param mysqli $conn     The database connection.
 *
 * @return string Domain name.
 */
function getDomainName($domainId, $conn)
{
    // Query the database to retrieve the domain name
    $sql = "SELECT name FROM domain WHERE id = ?";
    $result = executeQuery($conn, $sql, "i", [$domainId]);

    // Fetch and return the domain name
    $row = $result->fetch_assoc();
    return $row["name"];
}
?>