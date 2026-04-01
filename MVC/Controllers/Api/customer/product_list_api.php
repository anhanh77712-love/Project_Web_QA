<?php
class product_list_api extends controllers_customer {
    private $prd_list;
    private $menu_categories;
    private $home_model;
    private $profile_model;

    function __construct() {
        // Khởi tạo các Model cần thiết
        $this->prd_list = $this->model("product_list_m");
        $this->menu_categories = $this->model('master_customer_m');
        $this->home_model = $this->model('home_m');
        $this->profile_model = $this->model('profile_m');
    }

    function Get_data() {
        // Thu thập các tham số lọc từ URL
        $params = $this->get_filter_params();
        
        // Gọi hàm xử lý logic lọc chung
        $products = $this->build_product_query($params);

        $user_info = null;
        if (isset($_SESSION['user_id'])) {
            $user_info = $this->profile_model->user_getById($_SESSION['user_id']);
        }

        // Truyền dữ liệu sang View Master_customer
        $this->view('Master_customer', [
            'Page' => 'product_list_v',
            'menu_categories' => $this->menu_categories->categories_selectAll(),
            'products' => $products,
            'categories' => $this->prd_list->categories_selectAll(),
            'current_category' => $params['category'],
            'current_price' => $params['price_range'],
            'current_sort' => $params['sort'],
            'search_keyword' => $params['search'],
            'current_filter' => $params['sale'] === 1 ? 'sale' : $params['filter'],
            'home_model' => $this->home_model,
            'user_info' => $user_info
        ]);
    }

    // ============================================================
    // 2. CÁC PHƯƠNG THỨC API (JSON)
    // ============================================================

    // API lấy danh sách sản phẩm đã lọc dưới dạng JSON
    // URL: /web_qlsp/product_list_api/get_products_json?category=1&price=0-500000
    function get_products_json() {
        header('Content-Type: application/json; charset=utf-8');
        
        $params = $this->get_filter_params();
        $products_query = $this->build_product_query($params);
        
        $data = [];
        if ($products_query) {
            while ($row = mysqli_fetch_assoc($products_query)) {
                $data[] = $row;
            }
        }

        echo json_encode([
            'success' => true,
            'count' => count($data),
            'data' => $data
        ]);
        exit;
    }

    // ============================================================
    // 3. LOGIC LỌC SẢN PHẨM TRUNG TÂM (HELPERS)
    // ============================================================

    private function get_filter_params() {
        return [
            'category'    => isset($_GET['category']) ? trim($_GET['category']) : '',
            'collection'  => isset($_GET['collection']) ? trim($_GET['collection']) : '',
            'price_range' => isset($_GET['price']) ? trim($_GET['price']) : '',
            'sort'        => isset($_GET['sort']) ? trim($_GET['sort']) : 'default',
            'search'      => isset($_GET['search']) ? trim($_GET['search']) : '',
            'filter'      => isset($_GET['filter']) ? trim($_GET['filter']) : '',
            'sale'        => isset($_GET['sale']) ? intval($_GET['sale']) : 0
        ];
    }

    private function build_product_query($p) {
        // Xử lý khoảng giá
        $min = 0; $max = 999999999;
        if($p['price_range']) {
            $parts = explode('-', $p['price_range']);
            if(count($parts) == 2) {
                $min = intval($parts[0]); $max = intval($parts[1]);
            }
        }

        // Ưu tiên 1: Xử lý logic SALE (Flash Sale)
        if($p['sale'] === 1) {
            $flashSlug = '';
            $sections = $this->home_model->sections_getActive();
            if($sections) {
                while($sec = mysqli_fetch_assoc($sections)) {
                    if(strtolower($sec['section_type']) === 'flash_sale' && intval($sec['status']) === 1) {
                        $flashSlug = $this->home_model->collection_getSlug(intval($sec['collection_id']));
                        break;
                    }
                }
            }
            if($flashSlug) {
                if($p['category'] && $p['price_range']) 
                    return $this->prd_list->products_selectByCollectionCategoryAndPrice($flashSlug, $p['category'], $min, $max, $p['sort']);
                if($p['category']) 
                    return $this->prd_list->products_selectByCollectionAndCategory($flashSlug, $p['category'], $p['sort']);
                return $this->prd_list->products_selectByCollection($flashSlug, $p['sort']);
            }
        }

        // Ưu tiên 2: Bestseller
        if($p['filter'] === 'bestseller') {
            if($p['category']) return $this->prd_list->products_selectBestsellerByCategory($p['category'], $p['sort']);
            return $this->prd_list->products_selectBestseller($p['sort']);
        }

        // Ưu tiên 3: New
        if($p['filter'] === 'new') {
            if($p['category']) return $this->prd_list->products_selectNewByCategory($p['category'], $p['sort']);
            return $this->prd_list->products_selectNew($p['sort']);
        }

        // Ưu tiên 4: Search
        if($p['search']) {
            if($p['search'] === 'Áo' || $p['search'] === 'Quần') return $this->prd_list->products_searchByKeywordPrefix($p['search'], $p['sort']);
            return $this->prd_list->products_searchByKeyword($p['search'], $p['sort']);
        }

        // Ưu tiên 5: Category & Price
        if($p['category']) return $this->prd_list->products_selectByCategory($p['category'], $p['sort']);
        if($p['collection']) return $this->prd_list->products_selectByCollection($p['collection'], $p['sort']);

        // Mặc định: Lấy tất cả
        return $this->prd_list->products_selectAll($p['sort']);
    }
}