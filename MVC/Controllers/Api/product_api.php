<?php
class product_api extends controllers_customer {
    private $pdlist;

    function __construct() {
        $this->pdlist = $this->model("product_list_m"); 
    }

    // API GET: /web_qlsp/api/product_api/get_all
    function get_all() {
        header('Access-Control-Allow-Origin: *'); 
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: GET');

        $products_result = $this->pdlist->products_selectAll('default');
        
        $products_list = [];
        if ($products_result && mysqli_num_rows($products_result) > 0) {
            while ($product = mysqli_fetch_assoc($products_result)) {
                $products_list[] = $product;
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Thành công',
                'data' => $products_list
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false, 
                'message' => 'Không có sản phẩm nào', 
                'data' => []
            ]);
        }
    }
}
?>