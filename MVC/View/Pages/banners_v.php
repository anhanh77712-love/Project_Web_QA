<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid px-4">

    <h4 class="mt-4 fw-bold">Quản Lý Banner Trang Chủ</h4>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
        <li class="breadcrumb-item active">Banners</li>
    </ol>

    <div class="row">

        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-plus-circle me-1"></i> Thêm Banner Mới
                </div>
                <div class="card-body">
                    <form action="/web_qlsp/api/banners_api/insert" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tiêu đề (Ghi chú)</label>
                            <input type="text" class="form-control" placeholder="Ví dụ: Khuyến mãi Hè" name="title" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Hình ảnh</label>
                            <input type="file" class="form-control" accept="image/*" name="image_url" required>
                            <div class="form-text text-muted">Kích thước đẹp: 1920x600px</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Link liên kết</label>
                            <input type="text" class="form-control" placeholder="Nhập link hoặc để #" name="link_url">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Thứ tự hiển thị</label>
                            <input type="number" class="form-control" value="0" name="display_order">
                            <div class="form-text">Số nhỏ hiển thị trước</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" name="btnLuu">
                            <i class="fas fa-upload me-1"></i> Tải Lên
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4 shadow-sm">
                <div class="card-header fw-bold">
                    <i class="fas fa-list me-1"></i> Danh sách Banner hiện tại
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="40">TT</th>
                                    <th width="180">Hình ảnh</th>
                                    <th>Thông tin</th>
                                    <th width="100">Trạng thái</th>
                                    <th width="100">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($data['banners_list']) && mysqli_num_rows($data['banners_list']) > 0) {
                                    $stt = 1;
                                    foreach ($data['banners_list'] as $b) {
                                        $imagePath = '/web_qlsp/Public/Picture/banners/' . $b['image_url'];
                                ?>
                                        <tr>
                                            <td class="text-center fw-bold text-muted"><?php echo $stt++; ?></td>
                                            <td>
                                                <img src="<?php echo $imagePath; ?>" 
                                                     class="img-fluid rounded border" 
                                                     style="height:60px;width:100%;object-fit:cover;" 
                                                     alt="<?php echo htmlspecialchars($b['title']); ?>">
                                            </td>
                                            <td>
                                                <div class="fw-bold text-primary"><?php echo htmlspecialchars($b['title']); ?></div>
                                                <small class="text-muted d-block text-truncate" style="max-width:200px;">
                                                    <?php echo htmlspecialchars($b['link_url']); ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($b['status'] == 1) { ?>
                                                    <a href="/web_qlsp/banners/toggle/<?php echo $b['id']; ?>" class="badge bg-success text-decoration-none" title="Click để ẩn">
                                                        Hiển thị
                                                    </a>
                                                <?php } else { ?>
                                                    <a href="/web_qlsp/banners/toggle/<?php echo $b['id']; ?>" class="badge bg-secondary text-decoration-none" title="Click để hiện">
                                                        Ẩn
                                                    </a>
                                                <?php } ?>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-warning btn-sm me-1 btn-edit" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editBannerModal" 
                                                        title="Chỉnh sửa" 
                                                        data-id="<?php echo $b['id']; ?>" 
                                                        data-title="<?php echo htmlspecialchars($b['title']); ?>" 
                                                        data-link="<?php echo htmlspecialchars($b['link_url']); ?>" 
                                                        data-order="<?php echo $b['display_order']; ?>" 
                                                        data-image="<?php echo $b['image_url']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <a href="/web_qlsp/api/banners_api/delete/<?php echo $b['id']; ?>" 
                                                   class="btn btn-danger btn-sm btn-delete" 
                                                   title="Xóa">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            Chưa có banner nào.
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="editBannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-edit me-1"></i> Cập Nhật Banner
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="/web_qlsp/api/banners_api/update" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="old_image" id="edit_old_image">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tiêu đề (Ghi chú)</label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ảnh hiện tại</label>
                        <div>
                            <img id="edit_image_preview" src="" width="100%" class="rounded border" style="max-height: 150px; object-fit: cover;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Hình ảnh mới (Chọn nếu muốn thay)</label>
                        <input type="file" name="image_url" class="form-control" accept="image/*">
                        <div class="form-text text-muted">Kích thước đẹp: 1920x600px</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Link liên kết</label>
                        <input type="text" name="link_url" id="edit_link" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Thứ tự hiển thị</label>
                        <input type="number" name="display_order" id="edit_order" class="form-control">
                        <div class="form-text">Số nhỏ hiển thị trước</div>
                    </div>

                    <button type="submit" name="btnUpdate" class="btn btn-warning w-100 fw-bold">
                        <i class="fas fa-save me-1"></i> Lưu Cập Nhật
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Hàm hiển thị thông báo SweetAlert2
    function checkFlashMessage(status) {
        if (!status) return;

        const config = {
            success: {
                title: 'Thành công!',
                text: 'Thao tác đã được thực hiện thành công.',
                icon: 'success'
            },
            error: {
                title: 'Lỗi!',
                text: 'Có lỗi xảy ra trong quá trình xử lý.',
                icon: 'error'
            },
            warning: {
                title: 'Cảnh báo!',
                text: 'Dữ liệu không hợp lệ.',
                icon: 'warning'
            }
        };

        const currentConfig = config[status];
        if (currentConfig) {
            Swal.fire({
                title: currentConfig.title,
                text: currentConfig.text,
                icon: currentConfig.icon,
                timer: 2000,
                showConfirmButton: false,
                position: 'center'
            });
        }
    }

    // 2. Tự động kiểm tra Session PHP để hiện thông báo sau khi reload trang
    // Lưu ý: Trong Controller PHP (Add, Update, Delete) bạn cần set $_SESSION['status'] = 'success' hoặc 'error'
    <?php if (isset($_SESSION['status'])): ?>
        checkFlashMessage('<?php echo $_SESSION['status']; ?>');
        <?php unset($_SESSION['status']); // Xóa session để không hiện lại khi F5 ?>
    <?php endif; ?>


    // 3. Xử lý sự kiện khi nhấn nút "Chỉnh sửa"
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            // Lấy dữ liệu từ data-attribute
            const id = this.dataset.id;
            const title = this.dataset.title;
            const link = this.dataset.link;
            const order = this.dataset.order;
            const image = this.dataset.image;

            // Đổ dữ liệu vào Modal
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_link').value = link;
            document.getElementById('edit_order').value = order;
            document.getElementById('edit_old_image').value = image;

            // Xử lý ảnh preview trong modal
            const imgPath = image ? '/web_qlsp/Public/Picture/banners/' + image : '';
            document.getElementById('edit_image_preview').src = imgPath;
        });
    });

    // 4. Xử lý sự kiện khi nhấn nút "Xóa" (Hiệu ứng Confirm)
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault(); // Ngăn chặn việc chuyển trang ngay lập tức
            const href = this.getAttribute('href'); // Lấy đường dẫn xóa

            Swal.fire({
                title: 'Bạn chắc chắn muốn xóa?',
                text: "Dữ liệu banner này sẽ bị xóa vĩnh viễn!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Vâng, xóa nó!',
                cancelButtonText: 'Hủy bỏ'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Nếu người dùng chọn "Vâng", chuyển hướng trang để thực hiện xóa
                    window.location.href = href;
                }
            });
        });
    });
</script>