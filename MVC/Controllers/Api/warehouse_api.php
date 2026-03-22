<?php
class warehouse_api extends controllers_customer {
    private $warehouse_model;

    function __construct() {
        $this->warehouse_model = $this->model('warehouse_m');
    }

    // API GET: /web_qlsp/api/warehouse_api/get_all
    function get_all() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: GET');

        $result = $this->warehouse_model->warehouse_selectAll();
        $list = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $list]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không có kho nào']);
        }
    }
}
?>