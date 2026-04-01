<?php
class cart_api extends controllers_customer
{
    private $menu_categories;
    private $provinces_model;
    private $cart_model;
    private $product_detail_model;

    public function __construct()
    {
        // Khởi tạo các model cần thiết cho giỏ hàng và thanh toán
        $this->menu_categories      = $this->model('master_customer_m');
        $this->provinces_model      = $this->model('provinces_m');
        $this->cart_model           = $this->model('cart_m');
        $this->product_detail_model = $this->model('product_detail_m');
    }

    // ============================================================
    // 1. GIAO DIỆN GIỎ HÀNG (VIEW)
    // ============================================================

    public function Get_data()
    {
        $list_provinces = $this->provinces_model->provinces_selectAll();
        $list_vouchers  = $this->cart_model->vouchers_selectActive();

        // Xử lý thông tin người dùng và địa chỉ mặc định
        $user_info = null; $user_districts = null; $user_wards = null;
        if (isset($_SESSION['user_id'])) {
            $user_info = $this->cart_model->user_getById($_SESSION['user_id']);
            if ($user_info && !empty($user_info['province_code'])) {
                $user_districts = $this->provinces_model->districts_selectByProvince($user_info['province_code']);
            }
            if ($user_info && !empty($user_info['district_code'])) {
                $user_wards = $this->provinces_model->wards_selectByDistrict($user_info['district_code']);
            }
        }

        $subtotal = $this->cart_model->calculateCartTotal();
        $shipping = 30000;
        $total    = $subtotal + $shipping;

        $this->view('Master_customer', [
            'Page'            => 'cart_v',
            'menu_categories' => $this->menu_categories->categories_selectAll(),
            'provinces'       => $list_provinces,
            'vouchers'        => $list_vouchers,
            'user_info'       => $user_info,
            'user_districts'  => $user_districts,
            'user_wards'      => $user_wards,
            'cart_items'      => $_SESSION['cart'] ?? [],
            'subtotal'        => $subtotal,
            'shipping'        => $shipping,
            'total'           => $total,
        ]);
    }

    // ============================================================
    // 2. THAO TÁC GIỎ HÀNG (AJAX/API)
    // ============================================================

    public function add_to_cart()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Lỗi phương thức']); exit;
        }

        $p_id = intval($_POST['product_id'] ?? 0);
        $qty  = max(1, min(5, intval($_POST['quantity'] ?? 1)));
        $variant_id = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : null;

        // Kiểm tra tồn kho
        $stock_ok = $variant_id 
            ? $this->product_detail_model->variant_checkStock($variant_id, $qty)
            : $this->cart_model->product_checkStock($p_id, $qty);

        if (!$stock_ok) {
            echo json_encode(['success' => false, 'message' => 'Không đủ hàng trong kho']); exit;
        }

        $product = $this->cart_model->product_getById($p_id);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']); exit;
        }

        // Tạo key duy nhất cho sản phẩm trong giỏ
        $cart_key = $p_id . ($variant_id ? '_' . $variant_id : '') . ($_POST['size'] ? '_' . $_POST['size'] : '') . ($_POST['color'] ? '_' . $_POST['color'] : '');

        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        if (isset($_SESSION['cart'][$cart_key])) {
            if ($_SESSION['cart'][$cart_key]['quantity'] >= 5) {
                echo json_encode(['success' => false, 'message' => 'Giới hạn 5 sản phẩm/loại']); exit;
            }
            $_SESSION['cart'][$cart_key]['quantity'] = min(5, $_SESSION['cart'][$cart_key]['quantity'] + $qty);
        } else {
            $_SESSION['cart'][$cart_key] = [
                'id' => $p_id, 'name' => $product['name'], 'price' => $product['base_price'],
                'image' => $product['thumbnail'], 'quantity' => $qty,
                'size' => $_POST['size'] ?? '', 'color' => $_POST['color'] ?? '', 'variant_id' => $variant_id
            ];
        }

        echo json_encode(['success' => true, 'message' => 'Đã thêm vào giỏ', 'count' => $this->cart_model->getCartCount()]);
        exit;
    }

    // ĐÃ FIX: Bổ sung check tồn kho và trả về biến quantity
    public function update_quantity()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cart_key = $_POST['cart_key'] ?? '';
            $quantity = max(1, min(5, intval($_POST['quantity'] ?? 1)));

            if (!isset($_SESSION['cart'][$cart_key])) {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
                exit;
            }

            $item       = $_SESSION['cart'][$cart_key];
            $product_id = intval($item['id']);
            $variant_id = isset($item['variant_id']) ? intval($item['variant_id']) : null;

            // Kiểm tra tồn kho trước khi cập nhật
            $inStock  = true;
            $maxStock = null;
            if (!empty($variant_id)) {
                $variant  = $this->product_detail_model->variant_getById($variant_id);
                $maxStock = $variant ? intval($variant['stock']) : 0;
                $inStock  = $maxStock >= $quantity;
            } else {
                $product  = $this->cart_model->product_getById($product_id);
                $maxStock = $product ? intval($product['stock_quantity']) : 0;
                $inStock  = $maxStock >= $quantity;
            }

            if (!$inStock) {
                echo json_encode([
                    'success'    => false,
                    'message'    => 'Không đủ số lượng trong kho',
                    'currentQty' => $_SESSION['cart'][$cart_key]['quantity'],
                    'stock'      => $maxStock,
                ]);
                exit;
            }

            // Hợp lệ: cập nhật số lượng
            $_SESSION['cart'][$cart_key]['quantity'] = $quantity;
            echo json_encode([
                'success' => true, 
                'quantity' => $quantity, // Biến này giúp JS không bị ẩn số
                'new_total' => $this->cart_model->calculateCartTotal()
            ]);
            exit;
        }
    }

    public function remove_item()
    {
        header('Content-Type: application/json');
        $cart_key = $_POST['cart_key'] ?? '';
        unset($_SESSION['cart'][$cart_key]);
        echo json_encode(['success' => true, 'count' => $this->cart_model->getCartCount()]);
        exit;
    }

    // Lấy số lượng sản phẩm trong giỏ
    public function get_cart_count()
    {
        header('Content-Type: application/json');
        $count = $this->cart_model->getCartCount();
        echo json_encode(['count' => $count]);
        exit;
    }

    // ============================================================
    // 3. THANH TOÁN (CHECKOUT API) - ĐÃ BỔ SUNG ĐẦY ĐỦ LOGIC
    // ============================================================

    public function checkout()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            exit;
        }

        // 1. Kiểm tra giỏ hàng
        if (empty($_SESSION['cart'])) {
            echo json_encode(['success' => false, 'message' => 'Giỏ hàng của bạn đang trống!']); 
            exit;
        }

        // 2. Lấy thông tin từ form
        $customer_name  = trim($_POST['customer_name'] ?? '');
        $customer_phone = trim($_POST['customer_phone'] ?? '');
        $customer_email = trim($_POST['customer_email'] ?? '');
        $province_code  = trim($_POST['province_code'] ?? '');
        $district_code  = trim($_POST['district_code'] ?? '');
        $ward_code      = trim($_POST['ward_code'] ?? '');
        $address_detail = trim($_POST['address_detail'] ?? '');
        $note           = trim($_POST['note'] ?? '');
        $payment_method = trim($_POST['payment_method'] ?? 'COD');
        $voucher_id     = isset($_POST['voucher_id']) && $_POST['voucher_id'] !== '' ? intval($_POST['voucher_id']) : null;

        // Validate cơ bản
        if (empty($customer_name) || empty($customer_phone) || empty($province_code) || empty($district_code) || empty($ward_code) || empty($address_detail)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin giao hàng!']);
            exit;
        }

        // Cờ dùng điểm và số điểm muốn dùng
        $use_points_flag  = isset($_POST['use_points']) ? intval($_POST['use_points']) : 0;
        $points_requested = isset($_POST['points_to_use']) ? intval($_POST['points_to_use']) : 0;

        // 3. Tính toán giá trị đơn hàng
        $subtotal        = $this->cart_model->calculateCartTotal();
        $shipping_fee    = 30000;
        $discount_amount = 0;
        $voucher_code    = null;
        $points_used     = 0;
        $points_discount = 0;
        $points_earned   = 0;

        // --- Xử lý Voucher ---
        if ($voucher_id) {
            $voucher = $this->cart_model->voucher_getById($voucher_id);
            if ($voucher && $voucher['status'] == 1) {
                $voucher_code = $voucher['code'];
                if ($subtotal >= $voucher['min_order_value']) {
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
        }

        // --- Xử lý Điểm tích lũy ---
        if (isset($_SESSION['user_id']) && $use_points_flag === 1) {
            $user_info2       = $this->cart_model->user_getById($_SESSION['user_id']);
            $available_points = $user_info2 && isset($user_info2['points']) ? intval($user_info2['points']) : 0;
            $base_total_before_points = max($subtotal + $shipping_fee - $discount_amount, 0);
            $points_used     = max(0, min(intval($points_requested), $available_points, $base_total_before_points));
            $points_discount = $points_used; 
        }

        if (isset($_SESSION['user_id'])) {
            $points_earned = floor($subtotal / 100000);
        }

        $total_money = $subtotal + $shipping_fee - $discount_amount - $points_discount;

        // 4. Chuẩn bị dữ liệu
        $order_data = [
            'user_id'                 => $_SESSION['user_id'] ?? null,
            'customer_name'           => $customer_name,
            'customer_phone'          => $customer_phone,
            'customer_email'          => $customer_email,
            'shipping_province'       => $province_code,
            'shipping_district'       => $district_code,
            'shipping_ward'           => $ward_code,
            'shipping_address_detail' => $address_detail,
            'subtotal'                => $subtotal,
            'shipping_fee'            => $shipping_fee,
            'voucher_code'            => $voucher_code,
            'discount_amount'         => $discount_amount,
            'points_used'             => $points_used,
            'points_discount'         => $points_discount,
            'total_money'             => $total_money,
            'points_earned'           => $points_earned,
            'payment_method'          => $payment_method,
            'status'                  => 'pending',
            'note'                    => $note,
        ];

        // 5. Lưu đơn hàng
        $order_id = $this->cart_model->order_create($order_data);

        if ($order_id) {
            // 6. Lưu chi tiết đơn hàng và cập nhật tồn kho
            foreach ($_SESSION['cart'] as $item) {
                $order_item_data = [
                    'order_id'   => $order_id,
                    'product_id' => $item['id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'size'       => $item['size'] ?? null,
                    'color'      => $item['color'] ?? null,
                ];
                $this->cart_model->orderItem_insert($order_item_data);
                
                // Cập nhật tồn kho
                $this->update_stock_helper($item);
            }

            // Trừ điểm user
            if (isset($_SESSION['user_id']) && $points_used > 0) {
                if (method_exists($this->cart_model, 'user_decreasePoints')) {
                    $this->cart_model->user_decreasePoints($_SESSION['user_id'], $points_used);
                }
            }

            // Cập nhật lượt dùng voucher
            if ($voucher_id) {
                $this->cart_model->voucher_updateUsedCount($voucher_id);
            }

            // Cập nhật địa chỉ user làm mặc định cho lần sau
            if (isset($_SESSION['user_id'])) {
                $this->cart_model->user_updateAddress($_SESSION['user_id'], $province_code, $district_code, $ward_code, $address_detail);
            }

            // Xóa giỏ hàng
            $this->cart_model->clearCart();

            // Xác định URL chuyển hướng về Controller tĩnh (chứ không phải trỏ sang API)
            $redirect_url = '/web_qlsp/your_order'; 
            if (strtolower($payment_method) === 'vnpay') {
                $redirect_url = '/web_qlsp/payment/create/' . $order_id;
            }

            echo json_encode([
                'success'      => true,
                'message'      => 'Đặt hàng thành công!',
                'order_id'     => $order_id,
                'redirect_url' => $redirect_url
            ]);
            exit;

        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi server: Không thể tạo đơn hàng.']);
            exit;
        }
    }

    // ============================================================
    // 4. ĐỊA CHÍ & TIỆN ÍCH
    // ============================================================

    public function get_districts($p_code)
    {
        $res = $this->provinces_model->districts_selectByProvince($p_code);
        echo '<option value="">Chọn Quận/Huyện</option>';
        while ($r = mysqli_fetch_assoc($res)) echo "<option value='{$r['code']}'>{$r['name']}</option>";
        exit;
    }

    public function get_wards($d_code)
    {
        $res = $this->provinces_model->wards_selectByDistrict($d_code);
        echo '<option value="">Chọn Phường/Xã</option>';
        while ($r = mysqli_fetch_assoc($res)) echo "<option value='{$r['code']}'>{$r['name']}</option>";
        exit;
    }

    private function update_stock_helper($item)
    {
        if (!empty($item['variant_id'])) {
            if (method_exists($this->cart_model, 'variant_updateStock')) {
                $this->cart_model->variant_updateStock($item['variant_id'], $item['quantity']);
            } elseif (isset($this->product_detail_model) && method_exists($this->product_detail_model, 'variant_updateStock')) {
                $this->product_detail_model->variant_updateStock($item['variant_id'], $item['quantity']);
            }
        } else {
            $this->cart_model->product_updateStock($item['id'], $item['quantity']);
        }
    }
}