<?php
class home extends controllers_customer {
    private $home;
    private $menu_categories;
    private $provinces_model;
    
    function __construct() {
        $this->home = $this->model('home_m');
        $this->menu_categories = $this->model('master_customer_m');
        $this->provinces_model = $this->model('provinces_m');
       
    }

    // 1. CHỈ TẢI GIAO DIỆN (Trống rỗng, tải cực nhanh)
    function Get_data() {
        $list_provinces = $this->provinces_model->provinces_selectAll();
        $user_info = null;
        if (isset($_SESSION['user_id'])) {
            $user_info = $this->model('profile_m')->user_getById($_SESSION['user_id']);
        }

        $this->view('Master_customer', [
            'Page' => 'home_v',
            'menu_categories' => $this->menu_categories->categories_selectAll(),
            'provinces' => $list_provinces,
            'user_info' => $user_info
        ]);
    }

    // 2. API: LẤY TOÀN BỘ DỮ LIỆU TRANG CHỦ (Banners, Sections, Products...)
    function api_get_home_data() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        // Lấy Banners
        $banners_rs = $this->home->banners_getActive();
        $banners = [];
        if ($banners_rs && mysqli_num_rows($banners_rs) > 0) {
            while ($b = mysqli_fetch_assoc($banners_rs)) {
                $banners[] = $b;
            }
        }

        // Lấy Sections & Kèm theo dữ liệu Sản phẩm được đóng gói sẵn
        $sections_rs = $this->home->sections_getActive();
        $sections = [];
        
        if ($sections_rs && mysqli_num_rows($sections_rs) > 0) {
            while ($sec = mysqli_fetch_assoc($sections_rs)) {
                
                // Xử lý Section chứa Sản phẩm (Collection / Flash sale)
                if (in_array($sec['section_type'], ['collection', 'flash_sale']) && !empty($sec['collection_id'])) {
                    $prods_rs = $this->home->products_getByCollection($sec['collection_id'], 4);
                    $prods = [];
                    
                    if ($prods_rs && mysqli_num_rows($prods_rs) > 0) {
                        while ($p = mysqli_fetch_assoc($prods_rs)) {
                            // Gom nhóm Variants và Ảnh NGAY TẠI CONTROLLER
                            $variants_rs = $this->home->get_variants_by_product($p['id']);
                            $colors = [];
                            $variant_map = [];
                            
                            if ($variants_rs && mysqli_num_rows($variants_rs) > 0) {
                                while ($v = mysqli_fetch_assoc($variants_rs)) {
                                    if (!in_array($v['color'], $colors)) {
                                        $colors[] = $v['color'];
                                        
                                        $images_rs = $this->home->get_images_by_variant($v['id']);
                                        $images = [];
                                        if ($images_rs && mysqli_num_rows($images_rs) > 0) {
                                            while ($img = mysqli_fetch_assoc($images_rs)) {
                                                $images[] = $img['image_url'];
                                            }
                                        }
                                        if (empty($images)) $images[] = $p['thumbnail'];
                                        
                                        $variant_map[$v['color']] = [
                                            'variant_id' => $v['id'],
                                            'images' => $images,
                                            'hex' => method_exists($this->home, 'get_color_hex') ? $this->home->get_color_hex($v['color']) : '#ccc'
                                        ];
                                    }
                                }
                            }
                            $p['colors'] = $colors;
                            $p['variant_map'] = $variant_map;
                            $prods[] = $p;
                        }
                    }
                    $sec['products'] = $prods;
                } 
                // Xử lý Section chứa Category Grid
                elseif ($sec['section_type'] == 'category_grid') {
                    $cats_rs = $this->home->categories_selectAll();
                    $cats = [];
                    if ($cats_rs && mysqli_num_rows($cats_rs) > 0) {
                        $count = 0;
                        while ($c = mysqli_fetch_assoc($cats_rs)) {
                            if ($count >= 6) break;
                            $cats[] = $c;
                            $count++;
                        }
                    }
                    $sec['categories'] = $cats;
                }

                $sections[] = $sec;
            }
        }

        echo json_encode([
            'success' => true,
            'banners' => $banners,
            'sections' => $sections
        ]);
        exit;
    }

    // Các hàm Ajax phụ trợ (Giữ nguyên)
    function get_districts($p_code) {
        $districts = $this->provinces_model->districts_selectByProvince($p_code);
        echo '<option value="">Chọn Quận/Huyện</option>';
        while ($row = mysqli_fetch_assoc($districts)) {
            echo "<option value='".$row['code']."'>".$row['name']."</option>";
        }
        exit;
    }

    function get_wards($d_code) {
        $wards = $this->provinces_model->wards_selectByDistrict($d_code);
        echo '<option value="">Chọn Phường/Xã</option>';
        while ($row = mysqli_fetch_assoc($wards)) {
            echo "<option value='".$row['code']."'>".$row['name']."</option>";
        }
        exit;
    }
}
?>