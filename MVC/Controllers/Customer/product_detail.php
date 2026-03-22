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
        // Lấy sản phẩm
        $product = $this->product_model->product_selectBySlug($slug);
        
        if(!$product || mysqli_num_rows($product) == 0) {
            $_SESSION['error'] = 'Sản phẩm không tồn tại hoặc đã bị ẩn.';
            header('Location: /web_qlsp/product_list_customer');
            exit;
        }
        
        $product_data = mysqli_fetch_assoc($product);
        
        // Lấy sản phẩm liên quan & Tăng view
        $related_products = $this->product_model->products_selectRelated($product_data['category_id'], $product_data['id']);
        $this->product_model->product_increaseViews($product_data['id']);
        
        $this->view('Master_customer', [
            'Page' => 'product_details_v',
            'menu_categories' => $this->menu_categories->categories_selectAll(),
            'product' => $product_data,
            'related_products' => $related_products,
            'detail_model' => $this->product_model,
            'user_info' => $user_info
        ]);
    }
}
?>