<?php
    class controllers{
         public function __construct()
            {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                $current_uri = $_SERVER['REQUEST_URI'];
                if (strpos($current_uri, '/login') !== false) {
                    return; 
                }

                // Kiểm tra: Nếu CHƯA đăng nhập HOẶC quyền KHÔNG PHẢI admin
                if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                    
                    // Nếu là gọi API admin trái phép
                    if (strpos($current_uri, 'api_') !== false) {
                        header('Content-Type: application/json');
                        http_response_code(403); // Lỗi Forbidden - Cấm truy cập
                        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền truy cập khu vực này.']);
                        exit;
                    }

                    // Nếu cố tình vào giao diện quản trị, đá về trang chủ của khách
                    header("Location: /web_qlsp/home?require_login=true");
                    exit;
                }
            }
        public function model($model){
            include_once './MVC/Model/'.$model.'.php';
            return new $model;
        }
        public function view($view,$data=[]){
            if (is_array($data) && count($data) > 0) {
                extract($data);
            }
            include_once './MVC/View/'.$view.'.php';
        }
    }
?>