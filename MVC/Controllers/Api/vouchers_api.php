<?php
class vouchers_api extends controllers
{
    private $vouchers_model;

    public function __construct()
    {
        // Khởi tạo model vouchers_m dùng chung
        $this->vouchers_model = $this->model('vouchers_m');
    }

    // ============================================================
    // 1. QUẢN LÝ DANH SÁCH & HIỂN THỊ (VIEW)
    // ============================================================

    public function Get_data()
    {
        $this->view('Master', [
            'Page'          => 'vouchers_v',
            'vouchers_list' => $this->vouchers_model->vouchers_selectAll(),
        ]);
    }

    public function reset()
    {
        $this->Get_data();
    }

    // Kết hợp tìm kiếm và xuất Excel trong cùng một phương thức
    public function search()
    {
        if (isset($_POST['btnTimkiem'])) {
            $search = $_POST['txtSearch'];
            $vouchers_result = $this->vouchers_model->vouchers_select_search($search);
            $this->view('Master', [
                'Page' => 'vouchers_v',
                'vouchers_list' => $vouchers_result,
                'search' => $search
            ]);
        } 
        else if (isset($_POST['btnXuatExcel'])) {
            $this->exportExcel();
        }
    }

    // ============================================================
    // 2. THÊM, SỬA, XÓA (CRUD)
    // ============================================================

    public function add()
    {
        if (isset($_POST['btnAddVoucher'])) {
            $code = $_POST['code'];
            $description = $_POST['description'];
            $discount_type = $_POST['type'];
            $discount_value = $_POST['value'];
            $max_discount_amount = !empty($_POST['max_discount']) ? $_POST['max_discount'] : 'NULL';
            $min_order_value = !empty($_POST['min_order']) ? $_POST['min_order'] : 0;
            $usage_limit = !empty($_POST['usage_limit']) ? $_POST['usage_limit'] : 100;
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $status = $_POST['status'] ?? 1;

            // Validate dữ liệu
            if (empty($code)) {
                echo "<script>alert('Mã voucher không được để trống'); window.history.back();</script>";
                return;
            }
            if (!empty($start_date) && !empty($end_date) && $end_date < $start_date) {
                echo "<script>alert('Ngày kết thúc phải lớn hơn ngày bắt đầu'); window.history.back();</script>";
                return;
            }

            $kq = $this->vouchers_model->vouchers_insert(
                $code, $description, $discount_type, $discount_value,
                $max_discount_amount, $min_order_value, $usage_limit,
                $start_date, $end_date, $status
            );

            $this->thongBao($kq);
        }
    }

    public function update()
    {
        if (isset($_POST['btnUpdateVoucher'])) {
            $kq = $this->vouchers_model->vouchers_update(
                $_POST['id'], $_POST['usage_limit'], $_POST['start_date'],
                $_POST['end_date'], $_POST['value'], $_POST['min_order'],
                $_POST['max_discount'], $_POST['status']
            );
            $this->thongBao($kq);
        }
    }

    public function delete($id)
    {
        $kq = $this->vouchers_model->vouchers_delete($id);
        $this->thongBao($kq);
    }

    // ============================================================
    // 3. XỬ LÝ EXCEL (IMPORT & EXPORT)
    // ============================================================

    public function import()
    {
        if (isset($_POST['btnImport']) && isset($_FILES['fileImport'])) {
            $file = $_FILES['fileImport']['tmp_name'];
            if (empty($file)) {
                echo "<script>alert('Vui lòng chọn file Excel!'); window.history.back();</script>";
                return;
            }

            try {
                require_once __DIR__ . '/../../Public/Classes/PHPExcel/IOFactory.php';
                $objExcel = PHPExcel_IOFactory::load($file);
                $sheetData = $objExcel->getActiveSheet()->toArray(null, true, true, true);

                $countSuccess = 0;
                $countFail = 0;

                for ($i = 2; $i <= count($sheetData); $i++) {
                    $code = trim($sheetData[$i]["B"] ?? '');
                    if (empty($code) || strtolower($code) == 'mã code') continue;

                    // Mapping dữ liệu từ Excel
                    $discount_type = (trim($sheetData[$i]["D"]) == 'Theo %') ? 'percent' : 'fixed';
                    $start_date = is_numeric($sheetData[$i]["J"]) ? date('Y-m-d H:i:s', PHPExcel_Shared_Date::ExcelToPHP($sheetData[$i]["J"])) : date('Y-m-d H:i:s', strtotime($sheetData[$i]["J"]));
                    $end_date = is_numeric($sheetData[$i]["K"]) ? date('Y-m-d H:i:s', PHPExcel_Shared_Date::ExcelToPHP($sheetData[$i]["K"])) : date('Y-m-d H:i:s', strtotime($sheetData[$i]["K"]));

                    $kq = $this->vouchers_model->vouchers_insert(
                        $code, $sheetData[$i]["C"], $discount_type, 
                        str_replace(',', '', $sheetData[$i]["E"]),
                        empty($sheetData[$i]["F"]) ? "NULL" : str_replace(',', '', $sheetData[$i]["F"]),
                        str_replace(',', '', $sheetData[$i]["G"]),
                        str_replace(',', '', $sheetData[$i]["H"]),
                        $start_date, $end_date,
                        (trim($sheetData[$i]["L"]) == 'Hoạt động') ? 1 : 0
                    );

                    $kq ? $countSuccess++ : $countFail++;
                }
                $this->alertResult($countSuccess, $countFail);
            } catch (Exception $e) {
                echo "<script>alert('Lỗi: " . $e->getMessage() . "'); window.history.back();</script>";
            }
        }
    }

    private function exportExcel()
    {
        // Logic xuất file Excel sử dụng PHPExcel
        $objExcel = new PHPExcel();
        $sheet = $objExcel->setActiveSheetIndex(0);
        // ... (Các bước thiết lập header và đổ dữ liệu từ vouchers_select_search như file gốc)
        // Lưu ý: Cần require thư viện PHPExcel trong bridge.php hoặc tại đây.
    }

    // ============================================================
    // 4. API ENDPOINTS (JSON)
    // ============================================================

    // Endpoint: /web_qlsp/vouchers_api/get_all
    public function get_all()
    {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        $result = $this->vouchers_model->vouchers_selectAll();
        $list = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $list]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không có voucher nào']);
        }
    }

    // ============================================================
    // HELPERS
    // ============================================================

    private function thongBao($kq)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['status_msg'] = $kq ? "success" : "error";
        header("Location: /web_qlsp/vouchers");
        exit();
    }

    private function alertResult($s, $f)
    {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
              <script>window.onload = function(){ Swal.fire({ title: 'Hoàn tất!', html: 'Thành công: $s <br> Thất bại: $f', icon: 'info' }).then(() => { window.location.href = '/web_qlsp/vouchers'; }); };</script>";
    }
}