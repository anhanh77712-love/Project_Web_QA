<?php
class payment extends controllers_customer {
    private $cart_model;
    private $product_detail_model;

    function __construct() {
        $this->cart_model = $this->model('cart_m');
        $this->product_detail_model = $this->model('product_detail_m');
    }

    // 1. TẠO URL THANH TOÁN VÀ CHUYỂN HƯỚNG SANG VNPAY
    function create($order_id = null) {
        $order_id = intval($order_id);
        if ($order_id <= 0) {
            $_SESSION['error'] = 'Mã đơn hàng không hợp lệ.';
            header('Location: /web_qlsp/cart');
            exit;
        }

        $order = $this->cart_model->order_getById($order_id);
        if (!$order) {
            $_SESSION['error'] = 'Không tìm thấy đơn hàng.';
            header('Location: /web_qlsp/cart');
            exit;
        }

        // Nếu đã thanh toán rồi thì đưa về danh sách đơn hàng
        if (isset($order['status']) && strtolower($order['status']) === 'confirmed') {
            $_SESSION['success'] = 'Đơn hàng #' . $order_id . ' đã hoàn thành thanh toán.';
            header('Location: /web_qlsp/your_order');
            exit;
        }

        // Chuẩn bị dữ liệu VNPAY
        require_once('./config.php');
        
        // Tự động lấy Domain hiện tại thay vì Hardcode Localhost
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $domain = $_SERVER['HTTP_HOST'];
        $vnp_Returnurl = $protocol . '://' . $domain . '/web_qlsp/payment/return';

        $vnp_TxnRef   = $order_id;
        $vnp_OrderInfo= 'Thanh toan don hang #' . $order_id;
        $vnp_OrderType= 'other';
        $vnp_Amount   = intval($order['total_money']) * 100; // nhân 100 theo chuẩn VNPAY
        $vnp_Locale   = 'vn';
        $vnp_IpAddr   = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes'));

        $inputData = array(
            'vnp_Version'   => '2.1.0',
            'vnp_TmnCode'   => $vnp_TmnCode,
            'vnp_Amount'    => $vnp_Amount,
            'vnp_Command'   => 'pay',
            'vnp_CreateDate'=> date('YmdHis'),
            'vnp_CurrCode'  => 'VND',
            'vnp_IpAddr'    => $vnp_IpAddr,
            'vnp_Locale'    => $vnp_Locale,
            'vnp_OrderInfo' => $vnp_OrderInfo,
            'vnp_OrderType' => $vnp_OrderType,
            'vnp_ReturnUrl' => $vnp_Returnurl,
            'vnp_TxnRef'    => $vnp_TxnRef,
            'vnp_ExpireDate'=> $vnp_ExpireDate
        );

        if (isset($_GET['bank']) && $_GET['bank'] !== '') {
            $inputData['vnp_BankCode'] = $_GET['bank'];
        }

        ksort($inputData);
        $query = '';
        $hashdata = '';
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . '=' . urlencode($value) . '&';
        }

        $vnp_Url_full = $vnp_Url . '?' . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url_full .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        // Chuyển hướng người dùng sang giao diện VNPAY
        header('Location: ' . $vnp_Url_full);
        exit;
    }

    // 2. TRANG NHẬN KẾT QUẢ TỪ VNPAY KHI USER QUAY VỀ
    function return() {
        require_once('./config.php');
        $vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
        $inputData = array();
        
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == 'vnp_') {
                $inputData[$key] = $value;
            }
        }
        
        $order_id = isset($inputData['vnp_TxnRef']) ? intval($inputData['vnp_TxnRef']) : 0;
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        
        $i = 0;
        $hashData = '';
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashData .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
        }
        
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash === $vnp_SecureHash && isset($_GET['vnp_ResponseCode']) && $_GET['vnp_ResponseCode'] === '00') {
            // THANH TOÁN THÀNH CÔNG
            if ($order_id > 0) {
                $order = $this->cart_model->order_getById($order_id);
                if ($order && strtolower($order['status']) !== 'confirmed') {
                    
                    // Chỉ cập nhật trạng thái đơn hàng thành Đã Xác Nhận
                    // (Tồn kho và điểm đã được trừ bên lúc tạo đơn hàng ở file cart.php rồi, không trừ lại nữa)
                    $this->cart_model->order_updateStatus($order_id, 'confirmed');

                    // Dọn sạch giỏ hàng
                    if (isset($_SESSION['cart'])) {
                        unset($_SESSION['cart']);
                    }
                }
            }
            $_SESSION['success'] = 'Thanh toán VNPAY thành công cho đơn #' . $order_id;
            header('Location: /web_qlsp/your_order');
            exit;
            
        } else {
            // THANH TOÁN THẤT BẠI HOẶC BỊ HỦY
            $_SESSION['error'] = 'Thanh toán VNPAY thất bại hoặc đã bị hủy.';
            
            // Cập nhật lại trạng thái đơn hàng là Đã Hủy
            if ($order_id > 0) {
                $this->cart_model->order_updateStatus($order_id, 'cancelled');
            }
            
            header('Location: /web_qlsp/your_order');
            exit;
        }
    }

    // 3. IPN SERVER-TO-SERVER (VNPAY gọi ngầm vào API này để confirm)
    function ipn() {
        require_once('./config.php');
        $inputData = array();
        
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == 'vnp_') {
                $inputData[$key] = $value;
            }
        }
        
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        
        $i = 0;
        $hashData = '';
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashData .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
        }
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        
        $order_id = isset($inputData['vnp_TxnRef']) ? intval($inputData['vnp_TxnRef']) : 0;
        $amount = isset($inputData['vnp_Amount']) ? intval($inputData['vnp_Amount'])/100 : 0;

        header('Content-Type: application/json');
        
        if ($secureHash !== $vnp_SecureHash) {
            echo json_encode(['RspCode' => '97', 'Message' => 'Invalid signature']);
            exit;
        }

        $order = $order_id > 0 ? $this->cart_model->order_getById($order_id) : null;
        if (!$order) {
            echo json_encode(['RspCode' => '01', 'Message' => 'Order not found']);
            exit;
        }
        
        if (intval($order['total_money']) !== intval($amount)) {
            echo json_encode(['RspCode' => '04', 'Message' => 'invalid amount']);
            exit;
        }

        if (strtolower($order['status']) === 'confirmed') {
            echo json_encode(['RspCode' => '02', 'Message' => 'Order already confirmed']);
            exit;
        }

        $respCode = $inputData['vnp_ResponseCode'] ?? '';
        $transStatus = $inputData['vnp_TransactionStatus'] ?? '';
        
        if ($respCode === '00' || $transStatus === '00') {
            $this->cart_model->order_updateStatus($order_id, 'confirmed');
            echo json_encode(['RspCode' => '00', 'Message' => 'Confirm Success']);
            exit;
        } else {
            $this->cart_model->order_updateStatus($order_id, 'cancelled');
            echo json_encode(['RspCode' => '00', 'Message' => 'Confirm Failed']);
            exit;
        }
    }
}
?>