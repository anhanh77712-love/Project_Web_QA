<?php
class categories_api extends controllers_customer {
    private $categories_model;

    function __construct() {
        $this->categories_model = $this->model('categories_m');
    }

    // API GET: /web_qlsp/api/categories_api/get_all
    function get_all() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: GET');

        $result = $this->categories_model->categories_selectAll();
        $list = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $list]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không có danh mục nào']);
        }
    }
}
?>