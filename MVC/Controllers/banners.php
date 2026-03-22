<?php
class banners extends controllers
{
    private $banner;
    public function __construct()
    {
        $this->banner = $this->model('banners_m');
    }
    public function Get_data()
    {
        $this->view('Master', [
            'Page'         => 'banners_v',
            'banners_list' => $this->banner->banners_selectAll(),
        ]);
    }
    public function add()
    {
        if (isset($_POST['btnLuu'])) {
            $title         = $_POST['title'];
            $link_url      = $_POST['link_url'];
            $display_order = $_POST['display_order'];

            // Validate
            if (empty($title)) {
                echo "<script>alert('Tiêu đề banner không được để trống'); window.location.href='/web_qlsp/banners';</script>";
                return;
            } else {
                $image_url = '';
                if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
                    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/banners/";
                    $file_name  = time() . '_' . basename($_FILES["image_url"]["name"]);
                    if (move_uploaded_file($_FILES["image_url"]["tmp_name"], $target_dir . $file_name)) {
                        $image_url = $file_name;
                    }
                }
                // Insert DB
                $kq = $this->banner->banners_insert($title, $image_url, $link_url, $display_order);

                // Trong Controller
                if ($kq) {
                    $_SESSION['status'] = 'success';
                } else {
                    $_SESSION['status'] = 'error';
                }
                header("Location: http://localhost/web_qlsp/banners");
                exit;
            }
        }
    }

    // Toggle status (ẩn/hiện)
    public function toggle($id)
    {
        $kq = $this->banner->banners_toggleStatus($id);
        if ($kq) {
            header("Location: /web_qlsp/banners");
            exit;
        }
    }

    // Update banner
    public function update()
    {
        if (isset($_POST['btnUpdate'])) {
            $id            = $_POST['id'];
            $title         = $_POST['title'];
            $link_url      = $_POST['link_url'];
            $display_order = $_POST['display_order'];
            $old_image     = $_POST['old_image'];
            $image_url     = $old_image; // Mặc định giữ ảnh cũ

            // Kiểm tra có upload ảnh mới không
            if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/banners/";
                $file_name  = time() . '_' . basename($_FILES["image_url"]["name"]);
                if (move_uploaded_file($_FILES["image_url"]["tmp_name"], $target_dir . $file_name)) {
                    $image_url = $file_name;

                    // Xóa ảnh cũ
                    if (file_exists($target_dir . $old_image) && $old_image != '') {
                        unlink($target_dir . $old_image);
                    }
                }
            }

            $kq = $this->banner->banners_update($id, $title, $image_url, $link_url, $display_order);

            // Trong Controller
            if ($kq) {
                $_SESSION['status'] = 'success';
            } else {
                $_SESSION['status'] = 'error';
            }
            header("Location: http://localhost/web_qlsp/banners");
            exit;
        }
    }

    // Delete banner
    public function delete($id)
    {
        // Lấy thông tin ảnh trước khi xóa
        $result = $this->banner->banners_selectById($id);
        $banner = mysqli_fetch_assoc($result);

        if ($banner) {
            // Xóa file ảnh
            $file_path = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/banners/" . $banner['image_url'];
            if (file_exists($file_path) && $banner['image_url'] != '') {
                unlink($file_path);
            }

            // Xóa record trong DB
            $kq = $this->banner->banners_delete($id);

            // Trong Controller
            if ($kq) {
                $_SESSION['status'] = 'success';
            } else {
                $_SESSION['status'] = 'error';
            }
            header("Location: http://localhost/web_qlsp/banners");
            exit;
        }
    }
}
