<?php
class product_list_customer extends controllers_customer {
    private $prd_list;
    private $menu_categories;
    private $home_model;
    
    function __construct() {
        
        $this->prd_list = $this->model("product_list_m");
        $this->menu_categories = $this->model('master_customer_m');
        $this->home_model = $this->model('home_m');
    }
    
    // 1. CHỈ TẢI GIAO DIỆN
    function Get_data() {
        $user_info = null;
        if (isset($_SESSION['user_id'])) {
            $user_info = $this->model('profile_m')->user_getById($_SESSION['user_id']);
        }
        
        $this->view('Master_customer', [
            'Page' => 'product_list_v',
            'menu_categories' => $this->menu_categories->categories_selectAll(),
            'categories' => $this->prd_list->categories_selectAll(),
            'user_info' => $user_info
        ]);
    }

    // 2. API LẤY DANH SÁCH SẢN PHẨM THEO BỘ LỌC
    function api_get_data() {
        // Tắt cảnh báo rác, giữ lại lỗi nghiêm trọng để vào try-catch
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $category = isset($_GET['category']) ? trim($_GET['category']) : '';
            $collection = isset($_GET['collection']) ? trim($_GET['collection']) : '';
            $price_range = isset($_GET['price']) ? trim($_GET['price']) : '';
            $sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'default';
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';
            $sale = isset($_GET['sale']) ? intval($_GET['sale']) : 0;
            
            $min_price = 0; $max_price = 999999999;
            if($price_range) {
                $price_parts = explode('-', $price_range);
                if(count($price_parts) == 2) {
                    $min_price = intval($price_parts[0]);
                    $max_price = intval($price_parts[1]);
                }
            }
            
            $products_rs = null;
            
            // --- LOGIC LỌC SẢN PHẨM ---
            if($sale === 1) {
                $flashSlug = '';
                $sections = $this->home_model->sections_getActive();
                if($sections) {
                    // Xử lý an toàn cho object hoặc array
                    $sec_array = [];
                    if (is_object($sections)) {
                        while($s = mysqli_fetch_assoc($sections)) $sec_array[] = $s;
                    } elseif (is_array($sections)) {
                        $sec_array = $sections;
                    }
                    foreach($sec_array as $sec) {
                        if(strtolower($sec['section_type']) === 'flash_sale' && intval($sec['status']) === 1) {
                            $collectionId = intval($sec['collection_id']);
                            if($collectionId > 0) $flashSlug = $this->home_model->collection_getSlug($collectionId);
                            break;
                        }
                    }
                }
                
                if($flashSlug) {
                    if($category && $price_range) $products_rs = $this->prd_list->products_selectByCollectionCategoryAndPrice($flashSlug, $category, $min_price, $max_price, $sort);
                    elseif($category) $products_rs = $this->prd_list->products_selectByCollectionAndCategory($flashSlug, $category, $sort);
                    elseif($price_range) $products_rs = $this->prd_list->products_selectByCollectionAndPriceRange($flashSlug, $min_price, $max_price, $sort);
                    else $products_rs = $this->prd_list->products_selectByCollection($flashSlug, $sort);
                }
                if(!$products_rs) $products_rs = $this->prd_list->products_selectAll($sort);
            }
            elseif($filter === 'bestseller' && $category && $price_range) $products_rs = $this->prd_list->products_selectBestsellerByCategoryAndPrice($category, $min_price, $max_price, $sort);
            elseif($filter === 'bestseller' && $category) $products_rs = $this->prd_list->products_selectBestsellerByCategory($category, $sort);
            elseif($filter === 'bestseller' && $price_range) $products_rs = $this->prd_list->products_selectBestsellerByPrice($min_price, $max_price, $sort);
            elseif($filter === 'bestseller') $products_rs = $this->prd_list->products_selectBestseller($sort);
            elseif($filter === 'new' && $category && $price_range) $products_rs = $this->prd_list->products_selectNewByCategoryAndPrice($category, $min_price, $max_price, $sort);
            elseif($filter === 'new' && $category) $products_rs = $this->prd_list->products_selectNewByCategory($category, $sort);
            elseif($filter === 'new' && $price_range) $products_rs = $this->prd_list->products_selectNewByPrice($min_price, $max_price, $sort);
            elseif($filter === 'new') $products_rs = $this->prd_list->products_selectNew($sort);
            elseif($search && $category && $price_range) $products_rs = $this->prd_list->products_searchByCategoryPriceAndKeyword($category, $min_price, $max_price, $search, $sort);
            elseif($search && $category) $products_rs = $this->prd_list->products_searchByCategoryAndKeyword($category, $search, $sort);
            elseif($search && $price_range) $products_rs = $this->prd_list->products_searchByPriceAndKeyword($min_price, $max_price, $search, $sort);
            elseif($search) {
                if(in_array($search, ['Áo', 'Quần', 'Ao', 'Quan'])) $products_rs = $this->prd_list->products_searchByKeywordPrefix($search, $sort);
                else $products_rs = $this->prd_list->products_searchByKeyword($search, $sort);
            } 
            elseif($category && $price_range) $products_rs = $this->prd_list->products_selectByCategoryAndPrice($category, $min_price, $max_price, $sort);
            elseif($category) $products_rs = $this->prd_list->products_selectByCategory($category, $sort);
            elseif($price_range) $products_rs = $this->prd_list->products_selectByPriceRange($min_price, $max_price, $sort);
            elseif($collection) $products_rs = $this->prd_list->products_selectByCollection($collection, $sort);
            else $products_rs = $this->prd_list->products_selectAll($sort);


            // --- XỬ LÝ AN TOÀN TRÁNH LỖI N+1 QUERIES & OBJECT/ARRAY ---
            $items_array = [];
            if ($products_rs) {
                if (is_object($products_rs)) {
                    while ($p = mysqli_fetch_assoc($products_rs)) $items_array[] = $p;
                } elseif (is_array($products_rs)) {
                    $items_array = $products_rs;
                }
            }

            $count = count($items_array);
            $products = [];

            foreach ($items_array as $p) {
                $colors = [];
                $variant_map = [];
                
                // Lấy biến thể an toàn
                $variants_result = $this->home_model->get_variants_by_product($p['id']);
                $v_array = [];
                if ($variants_result) {
                    if (is_object($variants_result)) {
                        while ($v = mysqli_fetch_assoc($variants_result)) $v_array[] = $v;
                    } elseif (is_array($variants_result)) {
                        $v_array = $variants_result;
                    }
                }

                foreach ($v_array as $v) {
                    if (!in_array($v['color'], $colors)) {
                        $colors[] = $v['color'];
                        
                        // Lấy ảnh an toàn
                        $images_result = $this->home_model->get_images_by_variant($v['id']);
                        $images = [];
                        if ($images_result) {
                            if (is_object($images_result)) {
                                while ($img = mysqli_fetch_assoc($images_result)) $images[] = $img['image_url'];
                            } elseif (is_array($images_result)) {
                                foreach ($images_result as $img) $images[] = $img['image_url'];
                            }
                        }
                        if (empty($images)) $images[] = $p['thumbnail'];
                        
                        $variant_map[$v['color']] = [
                            'variant_id' => $v['id'],
                            'images' => $images,
                            'hex' => method_exists($this->home_model, 'get_color_hex') ? $this->home_model->get_color_hex($v['color']) : '#ccc'
                        ];
                    }
                }
                
                $p['colors'] = $colors;
                $p['variant_map'] = $variant_map;
                
                // Chống lỗi JSON: Ép tất cả các chuỗi thành UTF-8 chuẩn
                array_walk_recursive($p, function(&$item) {
                    if (is_string($item)) {
                        $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
                    }
                });

                $products[] = $p;
            }

            // Gói dữ liệu
            $response = [
                'success' => true,
                'count' => $count,
                'products' => $products,
                'current_filter' => $sale === 1 ? 'sale' : $filter,
                'search_keyword' => $search
            ];

            // Render JSON
            $json_string = json_encode($response);
            if ($json_string === false) {
                echo json_encode(['success' => false, 'message' => 'Lỗi mã hóa JSON: ' . json_last_error_msg()]);
                exit;
            }

            echo $json_string;
            exit;

        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi PHP: ' . $e->getMessage() . ' tại dòng ' . $e->getLine()
            ]);
            exit;
        }
    }
}
?>