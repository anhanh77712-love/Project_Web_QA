<?php
class product_detail extends controllers_customer {
    private $product_model;
    private $menu_categories;
    
    function __construct() {
        
        $this->product_model = $this->model("product_detail_m");
        $this->menu_categories = $this->model('master_customer_m');
    }
    
    function Get_data() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
        
        // Validate Slug
        if(empty($slug)) {
            header('Location: /web_qlsp/product_list_customer');
            exit;
        }
        
        $user_info = null;
        if (isset($_SESSION['user_id'])) {
            $user_info = $this->model('profile_m')->user_getById($_SESSION['user_id']);
        }

        // Lấy sản phẩm chính
        $product_rs = $this->product_model->product_selectBySlug($slug);
        
        if(!$product_rs || mysqli_num_rows($product_rs) == 0) {
            $_SESSION['error'] = 'Sản phẩm không tồn tại hoặc đã bị ẩn.';
            header('Location: /web_qlsp/product_list_customer');
            exit;
        }
        
        $product = mysqli_fetch_assoc($product_rs);
        $product_id = $product['id'];

        // ==========================================
        // 1. LẤY BIẾN THỂ & ẢNH CHO SẢN PHẨM CHÍNH
        // ==========================================
        $colors = [];
        $variant_map = [];
        $color_images = [];
        $all_images = [];

        $variants_result = $this->product_model->get_variants_by_product($product_id);
        if($variants_result && mysqli_num_rows($variants_result) > 0){
            while($v = mysqli_fetch_assoc($variants_result)){
                $color = $v['color'];
                if(!in_array($color, $colors)) $colors[] = $color;
                
                $imgs_res = $this->product_model->get_images_by_variant($v['id']);
                $imgs = [];
                if($imgs_res && mysqli_num_rows($imgs_res) > 0){
                    while($img = mysqli_fetch_assoc($imgs_res)){
                        $imgs[] = $img['image_url'];
                    }
                }
                if(empty($imgs)) $imgs[] = $product['thumbnail'];
                
                $variant_map[$color][] = [ 
                    'size' => $v['size'], 
                    'variant_id' => $v['id'], 
                    'images' => $imgs, 
                    'stock' => (int)($v['stock'] ?? 0) 
                ];

                if(!isset($color_images[$color])) $color_images[$color] = [];
                foreach($imgs as $iu){
                    if(!in_array($iu, $color_images[$color])) $color_images[$color][] = $iu;
                    if(!in_array($iu, $all_images)) $all_images[] = $iu;
                }
            }
        }

        // ==========================================
        // 2. LẤY SẢN PHẨM LIÊN QUAN & BIẾN THỂ CỦA CHÚNG
        // ==========================================
        $related_products = [];
        $related_rs = $this->product_model->products_selectRelated($product['category_id'], $product_id);
        
        if($related_rs && mysqli_num_rows($related_rs) > 0){
            while($rp = mysqli_fetch_assoc($related_rs)){
                $rp_colors = [];
                $rp_variant_map = [];
                
                $rp_vars = $this->product_model->get_variants_by_product($rp['id']);
                if($rp_vars && mysqli_num_rows($rp_vars) > 0){
                    while($rv = mysqli_fetch_assoc($rp_vars)){
                        $c = $rv['color'];
                        if(!in_array($c, $rp_colors)){
                            $rp_colors[] = $c;
                            $images = [];
                            $imgs_res = $this->product_model->get_images_by_variant($rv['id']);
                            if($imgs_res && mysqli_num_rows($imgs_res) > 0){
                                while($img = mysqli_fetch_assoc($imgs_res)){
                                    $images[] = $img['image_url'];
                                }
                            }
                            if(empty($images)) $images[] = $rp['thumbnail'];
                            
                            $rp_variant_map[$c] = [
                                'variant_id' => $rv['id'], 
                                'images' => $images,
                                'hex' => method_exists($this->product_model, 'get_color_hex') ? $this->product_model->get_color_hex($c) : '#ccc'
                            ];
                        }
                    }
                }
                $rp['colors'] = $rp_colors;
                $rp['variant_map'] = $rp_variant_map;
                $related_products[] = $rp;
            }
        }

        // Tăng view
        $this->product_model->product_increaseViews($product_id);
        
        // Trả dữ liệu sạch sẽ về cho View
        $this->view('Master_customer', [
            'Page' => 'product_details_v',
            'menu_categories' => $this->menu_categories->categories_selectAll(),
            'product' => $product,
            'colors' => $colors,
            'variant_map' => $variant_map,
            'color_images' => $color_images,
            'all_images' => $all_images,
            'related_products' => $related_products,
            'product_model' => $this->product_model,
            'user_info' => $user_info
        ]);
    }
}
?>