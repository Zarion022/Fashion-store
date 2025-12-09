<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');



$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($data['product_id'])) {
        
        
        $response = [
            'success' => true,
            'message' => 'Produkt dodany do koszyka',
            'cart_count' => rand(1, 10) 
        ];
        
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'error' => 'Brak ID produktu']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Nieprawidłowa metoda']);
}
?>