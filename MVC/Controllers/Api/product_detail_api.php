<?php
class product_detail_api extends controllers_customer {
    private $product_model;

    function __construct() {
        $this->product_model = $this->model("product_detail_m");
    }

    // API GET: /web_qlsp/api/product_detail_api/get_detail?slug=ten-san-pham
    function get_detail() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: GET');

        $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
        
        if(empty($slug)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu đường dẫn (slug) sản phẩm']);
            return;
        }

        $product_result = $this->product_model->product_selectBySlug($slug);
        if(!$product_result || mysqli_num_rows($product_result) == 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc đã bị ẩn']);
            return;
        }
        $product_data = mysqli_fetch_assoc($product_result);

        $colors = [];
        $variant_map = [];
        
        $variants_result = $this->product_model->get_variants_by_product($product_data['id']);
        if($variants_result) {
            while($v = mysqli_fetch_assoc($variants_result)) {
                $color = $v['color'];
                
                if(!in_array($color, $colors)) {
                    $colors[] = $color;
                }

                $imgs_res = $this->product_model->get_images_by_variant($v['id']);
                $imgs = [];
                if($imgs_res) {
                    while($img = mysqli_fetch_assoc($imgs_res)) {
                        $imgs[] = $img['image_url'];
                    }
                }
                if(empty($imgs)) {
                    $imgs[] = $product_data['thumbnail'];
                }

                if (!isset($variant_map[$color])) {
                    $variant_map[$color] = [];
                }
                
                $variant_map[$color][] = [
                    'variant_id' => $v['id'],
                    'size' => $v['size'],
                    'stock' => (int)$v['stock'],
                    'images' => $imgs
                ];
            }
        }

        $related = [];
        $related_res = $this->product_model->products_selectRelated($product_data['category_id'], $product_data['id'], 8);
        if($related_res) {
            while($r = mysqli_fetch_assoc($related_res)) {
                $related[] = $r;
            }
        }

        $this->product_model->product_increaseViews($product_data['id']);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => [
                'product_info' => $product_data,
                'available_colors' => $colors,
                'variants' => $variant_map,
                'related_products' => $related
            ]
        ]);
    }
}
?>