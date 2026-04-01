<?php
class app
{       
    protected $controller = "Home";
    protected $action = "Get_data";
    protected $params = [];

    function __construct()
    {
        $arr = $this->processURL();
        $isApi = false;
        $controllerFound = false;

        if ($arr != null) {
            // 1. Nhận diện luồng API
            // ===============================================
            if (strtolower($arr[0]) == 'api') {
                
                // Trạng thái 1: Có thư mục con "customer" (/api/customer/cart_api/checkout)
                if (isset($arr[1]) && strtolower($arr[1]) == 'customer' && isset($arr[2]) && file_exists('./MVC/Controllers/api/customer/' . $arr[2] . '.php')) {
                    include_once './MVC/Core/controllers_customer.php'; 
                    include_once './MVC/Controllers/api/customer/' . $arr[2] . '.php';
                    $this->controller = $arr[2]; // VD: cart_api
                    unset($arr[0]); // xóa 'api'
                    unset($arr[1]); // xóa 'customer'
                    unset($arr[2]); // xóa 'cart_api'
                    $arr = array_values($arr); // Đẩy 'checkout' lên vị trí số 0
                    $isApi = true;
                    $controllerFound = true;
                }
                // Trạng thái 2: Không có thư mục con (/api/cart_api/checkout) - Giữ lại phòng hờ
                else if (isset($arr[1]) && file_exists('./MVC/Controllers/api/' . $arr[1] . '.php')) {
                    include_once './MVC/Core/controllers_customer.php'; 
                    include_once './MVC/Controllers/api/' . $arr[1] . '.php';
                    $this->controller = $arr[1];
                    unset($arr[0]);
                    unset($arr[1]);
                    $arr = array_values($arr);
                    $isApi = true;
                    $controllerFound = true;
                } 
                // Nếu gọi link API sai
                else {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(404);
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Lỗi: Không tìm thấy API này! Vui lòng kiểm tra lại URL.'
                    ]);
                    exit;
                }
            }
            // 2. Nhận diện luồng Customer (Web)
            elseif (file_exists('./MVC/Controllers/Customer/' . $arr[0] . '.php')) {
                include_once './MVC/Core/controllers_customer.php';
                include_once './MVC/Controllers/Customer/' . $arr[0] . '.php';
                $this->controller = $arr[0];
                unset($arr[0]);
                $controllerFound = true;
            }
            // 3. Nhận diện luồng Admin (Web)
            elseif (file_exists('./MVC/Controllers/' . $arr[0] . '.php')) {
                include_once './MVC/Core/controllers.php';
                include_once './MVC/Controllers/' . $arr[0] . '.php';
                $this->controller = $arr[0];
                unset($arr[0]);
                $controllerFound = true;
            }
        } 
        
        // 4. Nếu vào trang chủ (Không có URL) hoặc không tìm thấy controller Web
        if (!$controllerFound && !$isApi) {
            if (file_exists('./MVC/Controllers/' . $this->controller . '.php')) {
                include_once './MVC/Controllers/' . $this->controller . '.php';
            } elseif (file_exists('./MVC/Controllers/Customer/home.php')) {
                include_once './MVC/Core/controllers_customer.php';
                include_once './MVC/Controllers/Customer/home.php';
                $this->controller = "home";
            }
        }
        
        $this->controller = new $this->controller;

        $action_index = $isApi ? 0 : 1; 
        
        if (isset($arr[$action_index])) {
            if (method_exists($this->controller, $arr[$action_index])) {
                $this->action = $arr[$action_index];
                unset($arr[$action_index]);
            }
        }

        $this->params = $arr ? array_values($arr) : [];
        call_user_func_array([$this->controller, $this->action], $this->params);
    }

    function processURL()
    {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(trim($_GET['url']), FILTER_DEFAULT));
        }
        return null;
    }
}
?>