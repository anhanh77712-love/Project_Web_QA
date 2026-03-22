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
    
    function Get_data() {
        // Lấy filter từ URL
        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        $collection = isset($_GET['collection']) ? trim($_GET['collection']) : '';
        $price_range = isset($_GET['price']) ? trim($_GET['price']) : '';
        $sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'default';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';
        $sale = isset($_GET['sale']) ? intval($_GET['sale']) : 0;
        
        // Debug - Xem giá trị nhận được
        echo "<!-- Debug: Category = " . htmlspecialchars($category) . " -->";
        echo "<!-- Debug: Price = " . htmlspecialchars($price_range) . " -->";
        echo "<!-- Debug: Sort = " . htmlspecialchars($sort) . " -->";
        
        // Xử lý price_range (format: min-max)
        $min_price = 0;
        $max_price = 999999999;
        if($price_range) {
            $price_parts = explode('-', $price_range);
            if(count($price_parts) == 2) {
                $min_price = intval($price_parts[0]);
                $max_price = intval($price_parts[1]);
            }
        }
        
        // Nếu là SALE: lấy cấu hình flash_sale từ home_sections và lọc theo collection
        if($sale === 1) {
            $products = null;
            $flashSlug = '';
            $sections = $this->home_model->sections_getActive();
            if($sections && mysqli_num_rows($sections) > 0) {
                mysqli_data_seek($sections, 0);
                while($sec = mysqli_fetch_assoc($sections)) {
                    if(strtolower($sec['section_type']) === 'flash_sale' && intval($sec['status']) === 1) {
                        $collectionId = intval($sec['collection_id']);
                        if($collectionId > 0) {
                            $flashSlug = $this->home_model->collection_getSlug($collectionId);
                        }
                        break;
                    }
                }
            }

            if($flashSlug) {
                if($category && $price_range) {
                    $products = $this->prd_list->products_selectByCollectionCategoryAndPrice($flashSlug, $category, $min_price, $max_price, $sort);
                    echo "<!-- Debug: SALE + Category + Price -->";
                } elseif($category) {
                    $products = $this->prd_list->products_selectByCollectionAndCategory($flashSlug, $category, $sort);
                    echo "<!-- Debug: SALE + Category -->";
                } elseif($price_range) {
                    $products = $this->prd_list->products_selectByCollectionAndPriceRange($flashSlug, $min_price, $max_price, $sort);
                    echo "<!-- Debug: SALE + Price -->";
                } else {
                    $products = $this->prd_list->products_selectByCollection($flashSlug, $sort);
                    echo "<!-- Debug: SALE only (collection) -->";
                }
            }

            if(!$products) {
                // Fallback nếu không có cấu hình flash_sale
                $products = $this->prd_list->products_selectAll($sort);
                echo "<!-- Debug: SALE fallback to all products -->";
            }
        }

        // Lấy sản phẩm theo filter khác (ưu tiên kết hợp)
        elseif($filter === 'bestseller' && $category && $price_range) {
            // Sản phẩm bán chạy + category + giá
            $products = $this->prd_list->products_selectBestsellerByCategoryAndPrice($category, $min_price, $max_price, $sort);
            echo "<!-- Debug: BESTSELLER + Category + Price -->";
        } elseif($filter === 'bestseller' && $category) {
            // Sản phẩm bán chạy + category
            $products = $this->prd_list->products_selectBestsellerByCategory($category, $sort);
            echo "<!-- Debug: BESTSELLER + Category -->";
        } elseif($filter === 'bestseller' && $price_range) {
            // Sản phẩm bán chạy + giá
            $products = $this->prd_list->products_selectBestsellerByPrice($min_price, $max_price, $sort);
            echo "<!-- Debug: BESTSELLER + Price -->";
        } elseif($filter === 'bestseller') {
            // Chỉ lọc sản phẩm bán chạy
            $products = $this->prd_list->products_selectBestseller($sort);
            echo "<!-- Debug: Filter by BESTSELLER -->";
        } elseif($filter === 'new' && $category && $price_range) {
            // Sản phẩm mới + category + giá
            $products = $this->prd_list->products_selectNewByCategoryAndPrice($category, $min_price, $max_price, $sort);
            echo "<!-- Debug: NEW + Category + Price -->";
        } elseif($filter === 'new' && $category) {
            // Sản phẩm mới + category
            $products = $this->prd_list->products_selectNewByCategory($category, $sort);
            echo "<!-- Debug: NEW + Category -->";
        } elseif($filter === 'new' && $price_range) {
            // Sản phẩm mới + giá
            $products = $this->prd_list->products_selectNewByPrice($min_price, $max_price, $sort);
            echo "<!-- Debug: NEW + Price -->";
        } elseif($filter === 'new') {
            // Chỉ lọc sản phẩm mới
            $products = $this->prd_list->products_selectNew($sort);
            echo "<!-- Debug: Filter by NEW (7 days) -->";
        } elseif($search && $category && $price_range) {
            // Tìm kiếm + lọc category + giá
            $products = $this->prd_list->products_searchByCategoryPriceAndKeyword($category, $min_price, $max_price, $search, $sort);
            echo "<!-- Debug: Search + Category + Price -->";
        } elseif($search && $category) {
            // Tìm kiếm + lọc category
            $products = $this->prd_list->products_searchByCategoryAndKeyword($category, $search, $sort);
            echo "<!-- Debug: Search + Category -->";
        } elseif($search && $price_range) {
            // Tìm kiếm + lọc giá
            $products = $this->prd_list->products_searchByPriceAndKeyword($min_price, $max_price, $search, $sort);
            echo "<!-- Debug: Search + Price -->";
        } elseif($search) {
            // Nếu search = "Áo" hoặc "Quần" thì dùng bắt đầu với, còn lại tìm kiếm bình thường
            if($search === 'Áo' || $search === 'Quần' || $search === 'Ao' || $search === 'Quan') {
                $products = $this->prd_list->products_searchByKeywordPrefix($search, $sort);
                echo "<!-- Debug: Search by PREFIX (Áo/Quần) -->";
            } else {
                $products = $this->prd_list->products_searchByKeyword($search, $sort);
                echo "<!-- Debug: Search only -->";
            }
        } elseif($category && $price_range) {
            // Lọc cả category và giá
            $products = $this->prd_list->products_selectByCategoryAndPrice($category, $min_price, $max_price, $sort);
            echo "<!-- Debug: Filter by category AND price -->";
        } elseif($category) {
            // Chỉ lọc category
            $products = $this->prd_list->products_selectByCategory($category, $sort);
            echo "<!-- Debug: Filter by category only -->";
        } elseif($price_range) {
            // Chỉ lọc giá
            $products = $this->prd_list->products_selectByPriceRange($min_price, $max_price, $sort);
            echo "<!-- Debug: Filter by price only -->";
        } elseif($collection) {
            // Lọc collection
            $products = $this->prd_list->products_selectByCollection($collection, $sort);
            echo "<!-- Debug: Filter by collection -->";
        } else {
            // Không có filter
            $products = $this->prd_list->products_selectAll($sort);
            echo "<!-- Debug: No filter - showing all products -->";
        }
        $user_info = null;
        if (isset($_SESSION['user_id'])) {
            $user_info = $this->model('profile_m')->user_getById($_SESSION['user_id']);
        }
        echo "<!-- Debug: Products count = " . ($products ? mysqli_num_rows($products) : 0) . " -->";
        
        $this->view('Master_customer', [
            'Page' => 'product_list_v',
            'menu_categories' => $this->menu_categories->categories_selectAll(),
            'products' => $products,
            'categories' => $this->prd_list->categories_selectAll(),
            'current_category' => $category,
            'current_price' => $price_range,
            'current_sort' => $sort,
            'search_keyword' => $search,
            'current_filter' => $sale === 1 ? 'sale' : $filter,
            'home_model' => $this->home_model,
            'user_info' => $user_info
        ]);
    }
}