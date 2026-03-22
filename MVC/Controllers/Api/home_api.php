<?php
class home_api extends controllers_customer {
    private $home_model;
    private $prd_list_model;

    function __construct() {
        $this->home_model = $this->model("home_m");
        $this->prd_list_model = $this->model("product_list_m");
    }

    // API: /web_qlsp/api/home_api/get_home_data
    function get_home_data() {
        header('Access-Control-Allow-Origin: *'); 
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: GET');

        $data = [
            'banners' => [],
            'categories' => [],
            'new_products' => []
        ];

        // 1. Lấy Banners
        $banners = $this->home_model->banners_getActive();
        if ($banners && mysqli_num_rows($banners) > 0) {
            while ($row = mysqli_fetch_assoc($banners)) {
                $data['banners'][] = $row;
            }
        }

        // 2. Lấy Danh mục
        $categories = $this->home_model->categories_selectAll();
        if ($categories && mysqli_num_rows($categories) > 0) {
            while ($row = mysqli_fetch_assoc($categories)) {
                $data['categories'][] = $row;
            }
        }

        // 3. Lấy Sản phẩm mới
        $new_products = $this->prd_list_model->products_selectNew('default');
        if ($new_products && mysqli_num_rows($new_products) > 0) {
            $count = 0;
            while ($row = mysqli_fetch_assoc($new_products)) {
                if ($count >= 10) break;
                $data['new_products'][] = $row;
                $count++;
            }
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Lấy dữ liệu trang chủ thành công',
            'data' => $data
        ]);
    }
}
?>