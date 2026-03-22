<?php
class cart extends controllers_customer
{
    private $menu_categories;
    private $provinces_model;
    private $cart_model;
    private $product_detail_model;

    public function __construct()
    {
        $this->menu_categories      = $this->model('master_customer_m');
        $this->provinces_model      = $this->model('provinces_m');
        $this->cart_model           = $this->model('cart_m');
        $this->product_detail_model = $this->model('product_detail_m');
    }

    public function Get_data()
    {
        // 1. Lấy danh sách tỉnh/thành
        $list_provinces = $this->provinces_model->provinces_selectAll();

        // 2. Lấy danh sách vouchers hợp lệ (BỎ COMMENT DÒNG NÀY)
        // Hàm này trong Model của bạn đã có điều kiện expiry_date >= NOW()
        $list_vouchers = $this->cart_model->vouchers_selectActive();

        // 3. Xử lý thông tin User (Giữ nguyên của bạn)
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

        // 5. TRUYỀN DỮ LIỆU SANG VIEW (Kiểm tra kỹ tên biến $list_vouchers)
        $this->view('Master_customer', [
            'Page'            => 'cart_v',
            'menu_categories' => $this->menu_categories->categories_selectAll(),
            'provinces'       => $list_provinces,
            'vouchers'        => $list_vouchers, // ĐẢM BẢO DÒNG NÀY CÓ BIẾN $list_vouchers
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

    public function add_to_cart()
    {
        // Đảm bảo session được khởi động
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set header JSON
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không hỗ trợ']);
            exit;
        }

        // Lấy và hợp lệ hóa dữ liệu đầu vào
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity   = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;
        if ($quantity > 5) {$quantity = 5;}
        $size       = isset($_POST['size']) ? trim($_POST['size']) : '';
        $color      = isset($_POST['color']) ? trim($_POST['color']) : '';
        $variant_id = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : null;

        if ($product_id <= 0) {
            // THẤT BẠI: Thiếu ID sản phẩm
            $_SESSION['error'] = 'Thêm sản phẩm thất bại. Thiếu mã sản phẩm.';
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu mã sản phẩm']);
            exit;
        }

        $product = $this->cart_model->product_getById($product_id);
        if (! $product) {
            // THẤT BẠI: Sản phẩm không tồn tại
            $_SESSION['error'] = 'Sản phẩm không tồn tại trong hệ thống.';
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
            exit;
        }

        // Kiểm tra tồn kho
        $has_stock = true;
        if (! empty($variant_id)) {
            if (! $this->product_detail_model->variant_checkStock($variant_id, $quantity)) {$has_stock = false;}
        } else {
            if (! $this->cart_model->product_checkStock($product_id, $quantity)) {$has_stock = false;}
        }

        if (! $has_stock) {
            // THẤT BẠI: Hết hàng
            $_SESSION['error'] = 'Không đủ số lượng sản phẩm trong kho.';
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Không đủ số lượng trong kho']);
            exit;
        }

        // ... (Giữ nguyên phần xử lý biến thể và ảnh của bạn ở đây) ...
        $price = floatval($product['base_price']);
        $image = ! empty($product['thumbnail']) ? $product['thumbnail'] : '';
        $name  = $product['name'];

        if (! empty($variant_id)) {
            $variant = $this->product_detail_model->variant_getById($variant_id);
            if (! $variant || intval($variant['product_id']) !== $product_id) {
                $_SESSION['error'] = 'Biến thể không hợp lệ.';
                echo json_encode(['success' => false, 'message' => 'Biến thể không hợp lệ']);
                exit;
            }
            // ... (Logic đồng bộ size/color/image của bạn) ...
            if ($size === '' && ! empty($variant['size'])) {$size = $variant['size'];}
            if ($color === '' && ! empty($variant['color'])) {$color = $variant['color'];}
        }

        // Khởi tạo giỏ hàng nếu chưa có
        if (! isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Tạo key duy nhất
        $key_parts = [$product_id];
        if ($variant_id) {$key_parts[] = $variant_id;}
        if ($size !== '') {$key_parts[] = $size;}
        if ($color !== '') {$key_parts[] = $color;}
        $cart_key = implode('_', $key_parts);

        // THỰC HIỆN THÊM VÀO GIỎ HÀNG
        $isLimitReached = false;
        $currentQty = 0;
        
        if (isset($_SESSION['cart'][$cart_key])) {
            $currentQty = $_SESSION['cart'][$cart_key]['quantity'];
            $newTotal = $currentQty + $quantity;
            
            // Kiểm tra nếu đã đạt giới hạn 5
            if ($currentQty >= 5) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Sản phẩm này đã đạt giới hạn tối đa 5 sản phẩm trong giỏ hàng!',
                    'limit_reached' => true
                ]);
                exit;
            }
            
            // Nếu thêm vào sẽ vượt quá 5, chỉ thêm đủ đến 5
            if ($newTotal > 5) {
                $_SESSION['cart'][$cart_key]['quantity'] = 5;
                $isLimitReached = true;
            } else {
                $_SESSION['cart'][$cart_key]['quantity'] = $newTotal;
            }
        } else {
            // Sản phẩm mới, giới hạn tối đa 5
            $_SESSION['cart'][$cart_key] = [
                'id'         => $product_id,
                'name'       => $name,
                'price'      => $price,
                'image'      => $image,
                'quantity'   => min(5, $quantity),
                'size'       => $size,
                'color'      => $color,
                'variant_id' => $variant_id,
            ];
        }

        // THÀNH CÔNG: Gán thông báo vào Session
        $_SESSION['success'] = 'Đã thêm sản phẩm "' . $name . '" vào giỏ hàng thành công!';

        // Trả kết quả JSON cho Ajax
        $count = $this->cart_model->getCartCount();
        $message = $isLimitReached 
            ? 'Đã thêm sản phẩm vào giỏ hàng (đã đạt giới hạn tối đa 5 sản phẩm)'
            : 'Đã thêm sản phẩm vào giỏ hàng';
            
        echo json_encode([
            'success' => true,
            'message' => $message,
            'count'   => $count,
            'item'    => $_SESSION['cart'][$cart_key],
            'limit_reached' => $isLimitReached
        ]);
        exit;
    }

    // Cập nhật số lượng sản phẩm
    public function update_quantity()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cart_key = $_POST['cart_key'] ?? '';
            $quantity = $_POST['quantity'] ?? 1;
            $quantity = max(1, min(5, intval($quantity)));

            if (! isset($_SESSION['cart'][$cart_key])) {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
                exit;
            }

            $item       = $_SESSION['cart'][$cart_key];
            $product_id = intval($item['id']);
            $variant_id = isset($item['variant_id']) ? intval($item['variant_id']) : null;

            // Kiểm tra tồn kho trước khi cập nhật
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
            echo json_encode(['success' => true, 'quantity' => $quantity]);
            exit;
        }
    }

    // Xóa sản phẩm khỏi giỏ hàng
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

    // Lấy số lượng sản phẩm trong giỏ
    public function get_cart_count()
    {
        $count = $this->cart_model->getCartCount();
        echo json_encode(['count' => $count]);
        exit;
    }

    // Hàm lấy Quận/Huyện cho Ajax
    public function get_districts($p_code)
    {
        $districts = $this->provinces_model->districts_selectByProvince($p_code);
        echo '<option value="">Chọn Quận/Huyện</option>';
        while ($row = mysqli_fetch_assoc($districts)) {
            echo "<option value='" . $row['code'] . "'>" . $row['name'] . "</option>";
        }
        exit;
    }

    // Hàm lấy Phường/Xã cho Ajax
    public function get_wards($d_code)
    {
        $wards = $this->provinces_model->wards_selectByDistrict($d_code);
        echo '<option value="">Chọn Phường/Xã</option>';
        while ($row = mysqli_fetch_assoc($wards)) {
            echo "<option value='" . $row['code'] . "'>" . $row['name'] . "</option>";
        }
        exit;
    }

    // public function checkout()
    // {
    //     if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //         // 1. Kiểm tra giỏ hàng
    //         if (! isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    //             $_SESSION['error'] = 'Giỏ hàng của bạn đang trống!';
    //             header('Location: /web_qlsp/cart');
    //             exit;
    //         }

    //         // 2. Lấy thông tin từ form
    //         $customer_name  = $_POST['customer_name'] ?? '';
    //         $customer_phone = $_POST['customer_phone'] ?? '';
    //         $customer_email = $_POST['customer_email'] ?? '';
    //         $province_code  = $_POST['province_code'] ?? '';
    //         $district_code  = $_POST['district_code'] ?? '';
    //         $ward_code      = $_POST['ward_code'] ?? '';
    //         $address_detail = $_POST['address_detail'] ?? '';
    //         $note           = $_POST['note'] ?? '';
    //         $payment_method = $_POST['payment_method'] ?? 'COD';
    //         $voucher_id     = $_POST['voucher_id'] ?? null;

    //         // Cờ dùng điểm và số điểm muốn dùng
    //         $use_points_flag  = isset($_POST['use_points']) ? intval($_POST['use_points']) : 0;
    //         $points_requested = isset($_POST['points_to_use']) ? intval($_POST['points_to_use']) : 0;

    //         // 3. Tính toán giá trị đơn hàng
    //         $subtotal        = $this->cart_model->calculateCartTotal();
    //         $shipping_fee    = 30000;
    //         $discount_amount = 0;
    //         $voucher_code    = null;
    //         $points_used     = 0;
    //         $points_discount = 0;
    //         $points_earned   = 0;

    //         // --- Xử lý Voucher ---
    //         if ($voucher_id) {
    //             $voucher = $this->cart_model->voucher_getById($voucher_id);
    //             if ($voucher && $voucher['status'] == 1) {
    //                 $voucher_code = $voucher['code'];

    //                 // Kiểm tra điều kiện đơn hàng tối thiểu
    //                 if ($subtotal >= $voucher['min_order_value']) {
    //                     if ($voucher['discount_type'] == 'fixed') {
    //                         $discount_amount = $voucher['discount_value'];
    //                     } else if ($voucher['discount_type'] == 'percent') {
    //                         $discount_amount = round($subtotal * $voucher['discount_value'] / 100);
    //                         // Giới hạn giảm tối đa (nếu có)
    //                         if (isset($voucher['max_discount_amount']) && $voucher['max_discount_amount'] > 0) {
    //                             $discount_amount = min($discount_amount, $voucher['max_discount_amount']);
    //                         }
    //                     }
    //                 }
    //             }
    //         }

    //         // --- Xử lý Điểm tích lũy ---
    //         if (isset($_SESSION['user_id']) && $use_points_flag === 1) {
    //             $user_info2       = $this->cart_model->user_getById($_SESSION['user_id']);
    //             $available_points = $user_info2 && isset($user_info2['points']) ? intval($user_info2['points']) : 0;

    //             // Số tiền cần thanh toán trước khi trừ điểm
    //             $base_total_before_points = max($subtotal + $shipping_fee - $discount_amount, 0);

    //             // Tính số điểm thực tế sử dụng (không quá số điểm có, không quá số tiền phải trả)
    //             $points_used     = max(0, min(intval($points_requested), $available_points, $base_total_before_points));
    //             $points_discount = $points_used; // Quy đổi: 1 điểm = 1 VNĐ
    //         }

    //         // Tính điểm thưởng (nháp) - Điểm này sẽ được cộng khi Admin xác nhận hoàn thành đơn
    //         if (isset($_SESSION['user_id'])) {
    //             $points_earned = floor($subtotal / 100000);
    //         }

    //         // Tổng tiền cuối cùng
    //         $total_money = $subtotal + $shipping_fee - $discount_amount - $points_discount;

    //         // 4. Chuẩn bị dữ liệu để lưu
    //         $order_data = [
    //             'user_id'                 => $_SESSION['user_id'] ?? null,
    //             'customer_name'           => $customer_name,
    //             'customer_phone'          => $customer_phone,
    //             'customer_email'          => $customer_email,
    //             'shipping_province'       => $province_code,
    //             'shipping_district'       => $district_code,
    //             'shipping_ward'           => $ward_code,
    //             'shipping_address_detail' => $address_detail,
    //             'subtotal'                => $subtotal,
    //             'shipping_fee'            => $shipping_fee,
    //             'voucher_code'            => $voucher_code,
    //             'discount_amount'         => $discount_amount,
    //             'points_used'             => $points_used,
    //             'points_discount'         => $points_discount,
    //             'total_money'             => $total_money,
    //             'points_earned'           => $points_earned,
    //             'payment_method'          => $payment_method,
    //             'status'                  => 'pending',
    //             'note'                    => $note,
    //         ];

    //         // 5. Lưu đơn hàng vào database
    //         $order_id = $this->cart_model->order_create($order_data);

    //         if ($order_id) {
    //             // 6. Lưu chi tiết đơn hàng (Order Items)
    //             foreach ($_SESSION['cart'] as $key => $item) {
    //                 $order_item_data = [
    //                     'order_id'   => $order_id,
    //                     'product_id' => $item['id'],
    //                     'quantity'   => $item['quantity'],
    //                     'price'      => $item['price'],
    //                     'variant_id' => $item['variant_id'] ?? null,
    //                     'size'       => $item['size'] ?? null,
    //                     'color'      => $item['color'] ?? null,
    //                 ];
    //                 $this->cart_model->orderItem_insert($order_item_data);
    //             }

    //             // --- TH1: Thanh toán VNPAY ---
    //             if (strtolower($payment_method) === 'vnpay') {
    //                 // Chuyển hướng sang trang xử lý thanh toán VNPAY
    //                 header('Location: /web_qlsp/payment/create/' . $order_id);
    //                 exit;
    //             }

    //             // --- TH2: Thanh toán COD (Tiền mặt) ---

    //             // Trừ điểm của user (nếu dùng)
    //             if (isset($_SESSION['user_id']) && $points_used > 0) {
    //                 if (method_exists($this->cart_model, 'user_decreasePoints')) {
    //                     $this->cart_model->user_decreasePoints($_SESSION['user_id'], $points_used);
    //                 }
    //             }

    //             // Cập nhật tồn kho
    //             foreach ($_SESSION['cart'] as $key => $item) {
    //                 if (! empty($item['variant_id'])) {
    //                     // Cần đảm bảo model có hàm variant_updateStock (thường nằm ở product_detail_model hoặc cart_model tùy cấu trúc)
    //                     // Ở đây tôi giả sử dùng cart_model hoặc product_detail_model có sẵn
    //                     if (method_exists($this->cart_model, 'variant_updateStock')) {
    //                         $this->cart_model->variant_updateStock($item['variant_id'], $item['quantity']);
    //                     } elseif (isset($this->product_detail_model) && method_exists($this->product_detail_model, 'variant_updateStock')) {
    //                         $this->product_detail_model->variant_updateStock($item['variant_id'], $item['quantity']);
    //                     }
    //                 } else {
    //                     $this->cart_model->product_updateStock($item['id'], $item['quantity']);
    //                 }
    //             }

    //             // Cập nhật lượt dùng Voucher
    //             if ($voucher_id) {
    //                 $this->cart_model->voucher_updateUsedCount($voucher_id);
    //             }

    //             // Cập nhật địa chỉ mặc định cho User (nếu đang đăng nhập) để lần sau tiện mua
    //             if (isset($_SESSION['user_id'])) {
    //                 $this->cart_model->user_updateAddress(
    //                     $_SESSION['user_id'],
    //                     $province_code,
    //                     $district_code,
    //                     $ward_code,
    //                     $address_detail
    //                 );
    //             }

    //             // Xóa giỏ hàng
    //             $this->cart_model->clearCart();

    //             // Thông báo thành công
    //             $_SESSION['success'] = 'Đặt hàng thành công! Mã đơn hàng: #' . $order_id;

    //             // === CHUYỂN HƯỚNG VỀ TRANG YOUR_ORDER ===
    //             header('Location: /web_qlsp/your_order');
    //             exit;

    //         } else {
    //             // Xử lý lỗi khi không tạo được đơn hàng
    //             $error_msg = 'Có lỗi xảy ra khi tạo đơn hàng. Vui lòng kiểm tra lại thông tin!';

    //             // Kiểm tra validate cơ bản để báo lỗi rõ hơn cho người dùng
    //             if (empty($customer_name) || empty($customer_phone) || empty($customer_email)) {
    //                 $error_msg = 'Vui lòng điền đầy đủ thông tin liên hệ!';
    //             } elseif (empty($province_code) || empty($district_code) || empty($ward_code)) {
    //                 $error_msg = 'Vui lòng chọn đầy đủ địa chỉ giao hàng!';
    //             } elseif (empty($address_detail)) {
    //                 $error_msg = 'Vui lòng nhập địa chỉ chi tiết!';
    //             }

    //             $_SESSION['error'] = $error_msg;

    //             // Quay lại trang giỏ hàng
    //             header('Location: /web_qlsp/cart');
    //             exit;
    //         }
    //     }
    // }
    public function checkout()
    {
        // Đặt header JSON để đảm bảo Client nhận đúng định dạng
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Kiểm tra giỏ hàng
            if (! isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
                echo json_encode(['success' => false, 'message' => 'Giỏ hàng của bạn đang trống!']);
                exit;
            }

            // 2. Lấy thông tin từ form
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

            // Validate cơ bản
            if (empty($customer_name) || empty($customer_phone) || empty($province_code)) {
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
                // 6. Lưu chi tiết đơn hàng
                foreach ($_SESSION['cart'] as $key => $item) {
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
                }

                // Trừ điểm user
                if (isset($_SESSION['user_id']) && $points_used > 0) {
                    if (method_exists($this->cart_model, 'user_decreasePoints')) {
                        $this->cart_model->user_decreasePoints($_SESSION['user_id'], $points_used);
                    }
                }

                // Cập nhật tồn kho (Logic rút gọn, giữ nguyên logic của bạn)
                foreach ($_SESSION['cart'] as $key => $item) {
                    if (! empty($item['variant_id'])) {
                         if (method_exists($this->cart_model, 'variant_updateStock')) {
                            $this->cart_model->variant_updateStock($item['variant_id'], $item['quantity']);
                        } elseif (isset($this->product_detail_model)) {
                            $this->product_detail_model->variant_updateStock($item['variant_id'], $item['quantity']);
                        }
                    } else {
                        $this->cart_model->product_updateStock($item['id'], $item['quantity']);
                    }
                }

                // Cập nhật voucher
                if ($voucher_id) {
                    $this->cart_model->voucher_updateUsedCount($voucher_id);
                }

                // Cập nhật địa chỉ user
                if (isset($_SESSION['user_id'])) {
                    $this->cart_model->user_updateAddress($_SESSION['user_id'], $province_code, $district_code, $ward_code, $address_detail);
                }

                // Xóa giỏ hàng
                $this->cart_model->clearCart();

                // === TRẢ VỀ JSON THÀNH CÔNG THAY VÌ REDIRECT ===
                
                // Xác định URL chuyển hướng
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
    }
    
}
