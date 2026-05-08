<?php
class cart extends controllers_customer
{
    private $menu_categories;
    private $provinces_model;
    private $cart_model;
    private $product_detail_model;

    public function __construct()
    {
        parent::__construct(); 

       if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' || strpos($_SERVER['REQUEST_URI'], 'add') !== false) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Tài khoản Quản trị viên (Admin) không có quyền này!']);
                exit;
            }

            echo "<script>
                    alert('Lỗi: Bạn đang đăng nhập bằng quyền Admin, không có quyền này!');
                    window.location.href = '/web_qlsp/home';
                  </script>";
            exit;
        }
        $this->menu_categories      = $this->model('master_customer_m');
        $this->provinces_model      = $this->model('provinces_m');
        $this->cart_model           = $this->model('cart_m');
        $this->product_detail_model = $this->model('product_detail_m');
    }

    public function Get_data()
    {
        // 1. Lấy danh sách tỉnh/thành
        $list_provinces = $this->provinces_model->provinces_selectAll();

        // 2. Lấy voucher
        $list_vouchers = $this->cart_model->vouchers_selectActive();

        // 3. Xử lý thông tin User
        $user_info      = null;
        $user_districts = null;
        $user_wards     = null;
        if (isset($_SESSION['user_id'])) {
            $user_info = $this->cart_model->user_getById($_SESSION['user_id']);
            if ($user_info && ! empty($user_info['province_code'])) {
                $user_districts = $this->provinces_model->districts_selectByProvince($user_info['province_code']);
            }
            if ($user_info && ! empty($user_info['district_code'])) {
                $user_wards = $this->provinces_model->wards_selectByDistrict($user_info['district_code']);
            }
        }

        // 4. Tính toán tiền giỏ hàng
        $cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        $subtotal   = $this->cart_model->calculateCartTotal();
        $shipping   = 30000;
        $discount   = 0;
        $total      = $subtotal + $shipping - $discount;

        $this->view('Master_customer', [
            'Page'            => 'cart_v',
            'menu_categories' => $this->menu_categories->categories_selectAll(),
            'provinces'       => $list_provinces,
            'vouchers'        => $list_vouchers, 
            'user_info'       => $user_info,
            'user_districts'  => $user_districts,
            'user_wards'      => $user_wards,
            'cart_items'      => $cart_items,
            'subtotal'        => $subtotal,
            'shipping'        => $shipping,
            'discount'        => $discount,
            'total'           => $total,
        ]);
    }


    public function api_get_cart_items() {
        header('Content-Type: application/json; charset=utf-8');
        $cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        $subtotal = $this->cart_model->calculateCartTotal();
        echo json_encode([
            'success' => true,
            'cart_items' => $cart_items,
            'subtotal' => $subtotal
        ]);
        exit;
    }

    public function add_to_cart()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không hỗ trợ']); exit;
        }

        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity   = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;
        if ($quantity > 2) $quantity = 2;
        $size       = isset($_POST['size']) ? trim($_POST['size']) : '';
        $color      = isset($_POST['color']) ? trim($_POST['color']) : '';
        $variant_id = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : null;

        if ($product_id <= 0) {
            $_SESSION['error'] = 'Thiếu mã sản phẩm.';
            http_response_code(400); echo json_encode(['success' => false, 'message' => 'Thiếu mã sản phẩm']); exit;
        }

        $product = $this->cart_model->product_getById($product_id);
        if (! $product) {
            $_SESSION['error'] = 'Sản phẩm không tồn tại.';
            http_response_code(404); echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']); exit;
        }

        $has_stock = true;
        if (! empty($variant_id)) {
            if (! $this->product_detail_model->variant_checkStock($variant_id, $quantity)) $has_stock = false;
        } else {
            if (! $this->cart_model->product_checkStock($product_id, $quantity)) $has_stock = false;
        }

        if (! $has_stock) {
            $_SESSION['error'] = 'Không đủ số lượng trong kho.';
            http_response_code(409); echo json_encode(['success' => false, 'message' => 'Không đủ số lượng trong kho']); exit;
        }

        $price = floatval($product['base_price']);
        $image = ! empty($product['thumbnail']) ? $product['thumbnail'] : '';
        $name  = $product['name'];

        if (! empty($variant_id)) {
            $variant = $this->product_detail_model->variant_getById($variant_id);
            if (! $variant || intval($variant['product_id']) !== $product_id) {
                echo json_encode(['success' => false, 'message' => 'Biến thể không hợp lệ']); exit;
            }
            if ($size === '' && ! empty($variant['size'])) $size = $variant['size'];
            if ($color === '' && ! empty($variant['color'])) $color = $variant['color'];
        }

        if (! isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        $key_parts = [$product_id];
        if ($variant_id) $key_parts[] = $variant_id;
        if ($size !== '') $key_parts[] = $size;
        if ($color !== '') $key_parts[] = $color;
        $cart_key = implode('_', $key_parts);

        $isLimitReached = false;
        $currentQty = 0;
        
        if (isset($_SESSION['cart'][$cart_key])) {
            $currentQty = $_SESSION['cart'][$cart_key]['quantity'];
            $newTotal = $currentQty + $quantity;
            
            if ($currentQty >= 2) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Đã đạt giới hạn 2 sản phẩm/loại!', 'limit_reached' => true]); exit;
            }
            
            if ($newTotal > 2) {
                $_SESSION['cart'][$cart_key]['quantity'] = 2;
                $isLimitReached = true;
            } else {
                $_SESSION['cart'][$cart_key]['quantity'] = $newTotal;
            }
        } else {
            $_SESSION['cart'][$cart_key] = [
                'id' => $product_id, 'name' => $name, 'price' => $price, 'image' => $image,
                'quantity' => min(2, $quantity), 'size' => $size, 'color' => $color, 'variant_id' => $variant_id,
            ];
        }

        $_SESSION['success'] = 'Đã thêm sản phẩm "' . $name . '" vào giỏ hàng!';
        $count = $this->cart_model->getCartCount();
        $message = $isLimitReached ? 'Đã đạt giới hạn tối đa 5 sản phẩm' : 'Đã thêm sản phẩm vào giỏ hàng';
            
        echo json_encode(['success' => true, 'message' => $message, 'count' => $count, 'item' => $_SESSION['cart'][$cart_key], 'limit_reached' => $isLimitReached]);
        exit;
    }

    public function update_quantity()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cart_key = $_POST['cart_key'] ?? '';
            $quantity = max(1, min(2, intval($_POST['quantity'] ?? 1)));

            if (! isset($_SESSION['cart'][$cart_key])) {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']); exit;
            }

            $item       = $_SESSION['cart'][$cart_key];
            $product_id = intval($item['id']);
            $variant_id = isset($item['variant_id']) ? intval($item['variant_id']) : null;

            $inStock  = true;
            $maxStock = null;
            if (! empty($variant_id)) {
                $variant  = $this->product_detail_model->variant_getById($variant_id);
                $maxStock = $variant ? intval($variant['stock']) : 0;
                $inStock  = $maxStock >= $quantity;
            } else {
                $product  = $this->cart_model->product_getById($product_id);
                $maxStock = $product ? intval($product['stock_quantity']) : 0;
                $inStock  = $maxStock >= $quantity;
            }

            if (! $inStock) {
                echo json_encode(['success' => false, 'message' => 'Không đủ số lượng trong kho', 'currentQty' => $_SESSION['cart'][$cart_key]['quantity'], 'stock' => $maxStock]); exit;
            }

            $_SESSION['cart'][$cart_key]['quantity'] = $quantity;
            echo json_encode(['success' => true, 'quantity' => $quantity]);
            exit;
        }
    }

    public function remove_item()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cart_key = $_POST['cart_key'] ?? '';
            if (isset($_SESSION['cart'][$cart_key])) {
                unset($_SESSION['cart'][$cart_key]);
            }
            echo json_encode(['success' => true]);
            exit;
        }
    }

    public function get_cart_count()
    {
        $count = $this->cart_model->getCartCount();
        echo json_encode(['count' => $count]);
        exit;
    }

    public function get_districts($p_code)
    {
        $districts = $this->provinces_model->districts_selectByProvince($p_code);
        echo '<option value="">Chọn Quận/Huyện</option>';
        while ($row = mysqli_fetch_assoc($districts)) {
            echo "<option value='" . $row['code'] . "'>" . $row['name'] . "</option>";
        }
        exit;
    }

    public function get_wards($d_code)
    {
        $wards = $this->provinces_model->wards_selectByDistrict($d_code);
        echo '<option value="">Chọn Phường/Xã</option>';
        while ($row = mysqli_fetch_assoc($wards)) {
            echo "<option value='" . $row['code'] . "'>" . $row['name'] . "</option>";
        }
        exit;
    }

    public function checkout()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Kiểm tra user đã đăng nhập chưa
            if (! isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục đặt hàng!', 'require_login' => true]); exit;
            }

            if (! isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
                echo json_encode(['success' => false, 'message' => 'Giỏ hàng của bạn đang trống!']); exit;
            }

            $customer_name  = $_POST['customer_name'] ?? '';
            $customer_phone = $_POST['customer_phone'] ?? '';
            $customer_email = $_POST['customer_email'] ?? '';
            $province_code  = $_POST['province_code'] ?? '';
            $district_code  = $_POST['district_code'] ?? '';
            $ward_code      = $_POST['ward_code'] ?? '';
            $address_detail = $_POST['address_detail'] ?? '';
            $note           = $_POST['note'] ?? '';
            $payment_method = $_POST['payment_method'] ?? 'COD';
            $voucher_id     = $_POST['voucher_id'] ?? null;

            if (empty($customer_name) || empty($customer_phone) || empty($province_code)) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin giao hàng!']); exit;
            }

            $use_points_flag  = isset($_POST['use_points']) ? intval($_POST['use_points']) : 0;
            $points_requested = isset($_POST['points_to_use']) ? intval($_POST['points_to_use']) : 0;

            $subtotal        = $this->cart_model->calculateCartTotal();
            $shipping_fee    = 30000;
            $discount_amount = 0;
            $voucher_code    = null;
            $points_used     = 0;
            $points_discount = 0;
            $points_earned   = 0;

            if ($voucher_id) {
                $voucher = $this->cart_model->voucher_getById($voucher_id);
                if ($voucher && $voucher['status'] == 1 && $subtotal >= $voucher['min_order_value']) {
                    $voucher_code = $voucher['code'];
                    if ($voucher['discount_type'] == 'fixed') {
                        $discount_amount = $voucher['discount_value'];
                    } else if ($voucher['discount_type'] == 'percent') {
                        $discount_amount = round($subtotal * $voucher['discount_value'] / 100);
                        if (isset($voucher['max_discount_amount']) && $voucher['max_discount_amount'] > 0) {
                            $discount_amount = min($discount_amount, $voucher['max_discount_amount']);
                        }
                    }
                }
            }

            if (isset($_SESSION['user_id']) && $use_points_flag === 1) {
                $user_info2       = $this->cart_model->user_getById($_SESSION['user_id']);
                $available_points = $user_info2 ? intval($user_info2['points']) : 0;
                $base_total_before_points = max($subtotal + $shipping_fee - $discount_amount, 0);
                $points_used     = max(0, min(intval($points_requested), $available_points, $base_total_before_points));
                $points_discount = $points_used; 
            }

            if (isset($_SESSION['user_id'])) $points_earned = floor($subtotal / 100000);

            $total_money = $subtotal + $shipping_fee - $discount_amount - $points_discount;

            $order_data = [
                'user_id' => $_SESSION['user_id'] ?? null, 'customer_name' => $customer_name, 'customer_phone' => $customer_phone,
                'customer_email' => $customer_email, 'shipping_province' => $province_code, 'shipping_district' => $district_code,
                'shipping_ward' => $ward_code, 'shipping_address_detail' => $address_detail, 'subtotal' => $subtotal,
                'shipping_fee' => $shipping_fee, 'voucher_code' => $voucher_code, 'discount_amount' => $discount_amount,
                'points_used' => $points_used, 'points_discount' => $points_discount, 'total_money' => $total_money,
                'points_earned' => $points_earned, 'payment_method' => $payment_method, 'status' => 'pending', 'note' => $note,
            ];

            $order_id = $this->cart_model->order_create($order_data);

            if ($order_id) {
                foreach ($_SESSION['cart'] as $key => $item) {
                    $this->cart_model->orderItem_insert([
                        'order_id' => $order_id, 'product_id' => $item['id'], 'quantity' => $item['quantity'],
                        'price' => $item['price'], 'variant_id' => $item['variant_id'] ?? null, 'size' => $item['size'] ?? null, 'color' => $item['color'] ?? null,
                    ]);
                }

                if (isset($_SESSION['user_id']) && $points_used > 0 && method_exists($this->cart_model, 'user_decreasePoints')) {
                    $this->cart_model->user_decreasePoints($_SESSION['user_id'], $points_used);
                }

                foreach ($_SESSION['cart'] as $key => $item) {
                    if (! empty($item['variant_id'])) {
                         if (method_exists($this->cart_model, 'variant_updateStock')) $this->cart_model->variant_updateStock($item['variant_id'], $item['quantity']);
                         elseif (isset($this->product_detail_model)) $this->product_detail_model->variant_updateStock($item['variant_id'], $item['quantity']);
                    } else {
                        $this->cart_model->product_updateStock($item['id'], $item['quantity']);
                    }
                }

                if ($voucher_id) $this->cart_model->voucher_updateUsedCount($voucher_id);
                if (isset($_SESSION['user_id'])) $this->cart_model->user_updateAddress($_SESSION['user_id'], $province_code, $district_code, $ward_code, $address_detail);

                $this->cart_model->clearCart();
                
                $redirect_url = (strtolower($payment_method) === 'vnpay') ? '/web_qlsp/payment/create/' . $order_id : '/web_qlsp/your_order';

                echo json_encode(['success' => true, 'message' => 'Đặt hàng thành công!', 'order_id' => $order_id, 'redirect_url' => $redirect_url]);
                exit;

            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi server: Không thể tạo đơn hàng.']); exit;
            }
        }
    }
}
?>