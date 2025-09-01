<?php
function fetchFirstArticle($keyword) {
    // Step 1: Fetch Google News RSS Feed based on keyword
    $encodedKeyword = urlencode($keyword);
    $rssFeedUrl = "https://news.google.com/rss/search?q={$encodedKeyword}";
    
    // Fetch the RSS feed
    $rssFeed = file_get_contents($rssFeedUrl);
    
    if ($rssFeed === false) {
        return "Failed to retrieve the RSS feed.";
    }
    
    // Parse the RSS feed using SimpleXML
    $rssData = simplexml_load_string($rssFeed);
    
    if ($rssData === false) {
        return "Failed to parse the RSS feed.";
    }
    
    // Step 2: Get the first article in the RSS feed
    $firstItem = $rssData->channel->item[0];
    if (!$firstItem) {
        return "No articles found.";
    }
    
    // Extract the real URL from the Google News link by using Python script
    $realUrl = getRealUrlUsingPython((string) $firstItem->link);
    
    // Return article details
    $article = [
        'title' => (string) $firstItem->title,
        'link' => $firstItem->link,
        'pubDate' => (string) $firstItem->pubDate,
        'description' => (string) $firstItem->description
    ];
    
    return $article;
}
function getRealUrlUsingPython($rssLink) {
    $url = 'http://localhost:5001/get-url';

    // Prepare the data for the POST request
    $data = json_encode(['url' => $rssLink]);

    // Initialize cURL session
    $ch = curl_init($url);

    // Set the cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    // Execute the request and capture the response
    $response = curl_exec($ch);

    // Close the cURL session
    curl_close($ch);

    // Decode the JSON response
    $result = json_decode($response, true);

    // Return the final URL or an error
    if (isset($result['final_url'])) {
        return $result['final_url'];
    } else {
        return isset($result['error']) ? $result['error'] : 'Unknown error';
    }
}


// Example usage
$keyword = "Ai technology in industral sector"; // Adjusted typo
$article = fetchFirstArticle($keyword);
echo "the url is  " . $article['link'];
if (is_array($article)) {
    echo "Title: " . $article['title'] . "\n";
    echo "Link: " . getRealUrlUsingPython($article['link']) . "\n";
    echo "Published Date: " . $article['pubDate'] . "\n";
    echo "Description: " . $article['description'] . "\n";
} else {
    echo $article; // Print error message
}
?>
