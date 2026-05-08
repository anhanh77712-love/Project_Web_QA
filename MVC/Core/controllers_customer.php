<?php
    class controllers_customer{
        public function __construct()
        {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $current_uri = $_SERVER['REQUEST_URI'];
            if (strpos($current_uri, '/login') !== false) {
                return; 
            }

            if (!isset($_SESSION['user_id'])) {
                if (strpos($current_uri, 'api_') !== false) {
                    header('Content-Type: application/json');
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.']);
                    exit;
                }

                header("Location: /web_qlsp/home?require_login=true");
                exit;
            }
        }
        public function model($model){
            include_once './MVC/Model/Customer/'.$model.'.php';
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