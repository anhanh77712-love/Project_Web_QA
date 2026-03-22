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
            if (strtolower($arr[0]) == 'api') {
                // Kiểm tra xem tên file API có tồn tại không
                if (isset($arr[1]) && file_exists('./MVC/Controllers/Api/' . $arr[1] . '.php')) {
                    include_once './MVC/Core/controllers_customer.php'; 
                    include_once './MVC/Controllers/Api/' . $arr[1] . '.php';
                    $this->controller = $arr[1];
                    unset($arr[0]);
                    unset($arr[1]);
                    $arr = array_values($arr);
                    $isApi = true;
                    $controllerFound = true;
                } else {
                    // Nếu gọi link API sai, trả về lỗi JSON thay vì sập web
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(404);
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Lỗi: Không tìm thấy API này! Vui lòng kiểm tra lại URL hoặc tên file.'
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