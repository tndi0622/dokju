<?php
// Naver Shopping API Configuration
$naver_client_id = getenv('NAVER_CLIENT_ID');
$naver_client_secret = getenv('NAVER_CLIENT_SECRET');

/**
 * Search Naver Shopping API for product image
 * @param string $product_name Product name to search
 * @return string|null Image URL or null if not found
 */
function searchNaverProductImage($product_name, $client_id, $client_secret) {
    // Clean product name for search
    $search_query = urlencode($product_name . ' 사케');
    
    $url = "https://openapi.naver.com/v1/search/shop.json?query=" . $search_query . "&display=5&sort=sim";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "X-Naver-Client-Id: " . $client_id,
        "X-Naver-Client-Secret: " . $client_secret
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    // Debug output
    if ($http_code != 200) {
        echo "<span style='color:red;'>API Error (HTTP {$http_code}): {$product_name}</span><br>";
        if ($curl_error) {
            echo "<span style='color:red;'>CURL Error: {$curl_error}</span><br>";
        }
        if ($response) {
            $error_data = json_decode($response, true);
            if (isset($error_data['errorMessage'])) {
                echo "<span style='color:red;'>Naver API Error: {$error_data['errorMessage']}</span><br>";
            }
        }
        return null;
    }
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        
        if (isset($data['items']) && count($data['items']) > 0) {
            // Return the first result's image
            return $data['items'][0]['image'];
        } else {
            echo "<span style='color:orange;'>No results found for: {$product_name}</span><br>";
        }
    }
    
    return null;
}

/**
 * Update all products with images from Naver Shopping API
 */
function updateProductImages($conn, $client_id, $client_secret) {
    $result = $conn->query("SELECT id, product_name, image FROM products");
    $updated = 0;
    $skipped = 0;
    
    while ($product = $result->fetch_assoc()) {
        // Skip if already has image
        if (!empty($product['image'])) {
            $skipped++;
            echo "Skipped: {$product['product_name']} (already has image)<br>";
            continue;
        }
        
        // Search for image
        $image_url = searchNaverProductImage($product['product_name'], $client_id, $client_secret);
        
        if ($image_url) {
            // Update database
            $stmt = $conn->prepare("UPDATE products SET image = ? WHERE id = ?");
            $stmt->bind_param("si", $image_url, $product['id']);
            $stmt->execute();
            
            $updated++;
            echo "Updated: {$product['product_name']} → {$image_url}<br>";
            
            // Sleep to avoid API rate limit
            usleep(100000); // 0.1 second delay
        } else {
            echo "Not found: {$product['product_name']}<br>";
        }
    }
    
    echo "<br><strong>Summary:</strong><br>";
    echo "Updated: {$updated}<br>";
    echo "Skipped: {$skipped}<br>";
}
?>
