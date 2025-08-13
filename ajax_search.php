// FILENAME: ajax_search.php
// --- CONTENT ---
<?php
include_once 'common/config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'products' => []];

if (isset($_GET['q'])) {
    $search_term = trim($_GET['q']);
    
    if (strlen($search_term) > 1) { // Only search if query is 2+ characters
        $search_query = "%" . $search_term . "%";
        
        $sql = "SELECT id, name, image, price FROM products WHERE name LIKE ? LIMIT 5";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $search_query);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = [
                'id' => $row['id'],
                'name' => htmlspecialchars($row['name']),
                'image' => htmlspecialchars($row['image']),
                'price' => format_price($row['price']) // Use the price formatting function
            ];
        }
        
        $response['success'] = true;
        $response['products'] = $products;
        
        $stmt->close();
    }
}

$conn->close();
echo json_encode($response);
?>