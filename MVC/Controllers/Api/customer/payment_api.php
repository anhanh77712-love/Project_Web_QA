<?php
class payment_api extends controllers_customer {
    private $cart_model;
    private $product_detail_model;

    function __construct() {
        // Khởi tạo các model cần thiết để xử lý đơn hàng và tồn kho
        $this->cart_model = $this->model('cart_m');
        $this->product_detail_model = $this->model('product_detail_m');
    }

    // ============================================================
    // 1. TẠO GIAO DỊCH VNPAY (CREATE)
    // ============================================================

    function create($order_id = null) {
        $order_id = intval($order_id);
        if ($order_id <= 0) {
            $_SESSION['error'] = 'Mã đơn hàng không hợp lệ.';
            header('Location: /web_qlsp/cart'); exit;
        }

        $order = $this->cart_model->order_getById($order_id);
        if (!$order) {
            $_SESSION['error'] = 'Không tìm thấy đơn hàng.';
            header('Location: /web_qlsp/cart'); exit;
        }

        // Nếu đơn hàng đã được xác nhận thanh toán trước đó
        if (isset($order['status']) && strtolower($order['status']) === 'confirmed') {
            $_SESSION['success'] = 'Đơn hàng #' . $order_id . ' đã hoàn tất thanh toán.';
            header('Location: /web_qlsp/your_order'); exit;
        }

        // Cấu hình thông số VNPAY
        require_once('./config.php'); 
        $vnp_Returnurl = 'http://localhost/web_qlsp/api/customer/payment_api/return'; // URL nhận kết quả

        $vnp_TxnRef    = $order_id;
        $vnp_OrderInfo = 'Thanh toán đơn hàng #' . $order_id;
        $vnp_OrderType = 'other';
        $vnp_Amount    = intval($order['total_money']) * 100; // Định dạng tiền tệ VNPAY
        $vnp_Locale    = 'vn';
        $vnp_IpAddr    = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes'));

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $vnp_ExpireDate
        );

        // Xử lý chọn ngân hàng (nếu có)
        if (isset($_GET['bank']) && $_GET['bank'] !== '') {
            $inputData['vnp_BankCode'] = $_GET['bank'];
        }

        ksort($inputData);
        $query = ""; $hashdata = ""; $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) { $hashdata .= '&' . urlencode($key) . "=" . urlencode($value); } 
            else { $hashdata .= urlencode($key) . "=" . urlencode($value); $i = 1; }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url_full = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret); // Tạo chữ ký bảo mật
            $vnp_Url_full .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        header('Location: ' . $vnp_Url_full);
        exit;
    }

    // ============================================================
    // 2. NHẬN KẾT QUẢ TỪ VNPAY (RETURN URL)
    // ============================================================

    function return() {
        require_once('./config.php');
        $vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") { $inputData[$key] = $value; }
        }
        
        $order_id = isset($inputData['vnp_TxnRef']) ? intval($inputData['vnp_TxnRef']) : 0;
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $hashData = ""; $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) { $hashData .= '&' . urlencode($key) . "=" . urlencode($value); } 
            else { $hashData .= urlencode($key) . "=" . urlencode($value); $i = 1; }
        }
        
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        // Xác thực chữ ký và mã phản hồi thành công (00)
        if ($secureHash === $vnp_SecureHash && isset($_GET['vnp_ResponseCode']) && $_GET['vnp_ResponseCode'] === '00') {
            if ($order_id > 0) {
                $order = $this->cart_model->order_getById($order_id);
                if ($order && strtolower($order['status']) !== 'confirmed') {
                    // Cập nhật trạng thái đơn hàng và xử lý tồn kho
                    $this->cart_model->order_updateStatus($order_id, 'confirmed');
                    $this->process_inventory($order_id);
                    $this->process_user_benefits($order);
                    
                    if (isset($_SESSION['cart'])) { $this->cart_model->clearCart(); }
                }
            }
            $_SESSION['success'] = 'Thanh toán VNPAY thành công cho đơn #' . $order_id;
            header('Location: /web_qlsp/your_order');
        } else {
            $_SESSION['error'] = 'Thanh toán thất bại hoặc chữ ký không hợp lệ.';
            header('Location: /web_qlsp/your_order');
        }
        exit;
    }

    // ============================================================
    // 3. XÁC THỰC SERVER-TO-SERVER (IPN)
    // ============================================================

    function ipn() {
        require_once('./config.php');
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") { $inputData[$key] = $value; }
        }
        
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $hashData = ""; $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) { $hashData .= '&' . urlencode($key) . "=" . urlencode($value); } 
            else { $hashData .= urlencode($key) . "=" . urlencode($value); $i = 1; }
        }
        
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $order_id = isset($inputData['vnp_TxnRef']) ? intval($inputData['vnp_TxnRef']) : 0;
        $amount = isset($inputData['vnp_Amount']) ? intval($inputData['vnp_Amount'])/100 : 0;

        header('Content-Type: application/json');
        if ($secureHash !== $vnp_SecureHash) {
            echo json_encode(['RspCode' => '97', 'Message' => 'Invalid signature']); exit;
        }

        $order = $order_id > 0 ? $this->cart_model->order_getById($order_id) : null;
        if (!$order) {
            echo json_encode(['RspCode' => '01', 'Message' => 'Order not found']); exit;
        }
        if (intval($order['total_money']) !== intval($amount)) {
            echo json_encode(['RspCode' => '04', 'Message' => 'Invalid amount']); exit;
        }

        if (strtolower($order['status']) === 'confirmed') {
            echo json_encode(['RspCode' => '02', 'Message' => 'Order already confirmed']); exit;
        }

        // Xác nhận trạng thái giao dịch từ VNPAY
        if (($inputData['vnp_ResponseCode'] ?? '') === '00') {
            $this->cart_model->order_updateStatus($order_id, 'confirmed');
            echo json_encode(['RspCode' => '00', 'Message' => 'Confirm Success']);
        } else {
            echo json_encode(['RspCode' => '00', 'Message' => 'Confirm Failed']);
        }
        exit;
    }

    // ============================================================
    // HELPERS (HÀM BỔ TRỢ)
    // ============================================================

    private function process_inventory($order_id) {
        $items = $this->cart_model->orderItems_getByOrderId($order_id);
        if ($items) {
            while ($item = mysqli_fetch_assoc($items)) {
                $qty = intval($item['quantity']);
                if (!empty($item['variant_id'])) {
                    $this->product_detail_model->variant_updateStock(intval($item['variant_id']), $qty);
                } else {
                    $this->cart_model->product_updateStock(intval($item['product_id']), $qty);
                }
            }
        }
    }

    private function process_user_benefits($order) {
        // Trừ điểm người dùng
        if (!empty($order['user_id']) && intval($order['points_used']) > 0) {
            if (method_exists($this->cart_model, 'user_decreasePoints')) {
                $this->cart_model->user_decreasePoints(intval($order['user_id']), intval($order['points_used']));
            }
        }
        // Có thể bổ sung thêm logic cập nhật số lần dùng voucher tại đây
    }
}
?>