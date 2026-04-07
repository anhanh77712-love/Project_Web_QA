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
                    <form id="formAddBanner">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tiêu đề (Ghi chú)</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Hình ảnh</label>
                            <input type="file" class="form-control" accept="image/*" name="image_url" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Link liên kết</label>
                            <input type="text" class="form-control" name="link_url">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Thứ tự hiển thị</label>
                            <input type="number" class="form-control" value="0" name="display_order">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
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
                            <tbody id="banner-table-body">
                                <tr><td colspan="5" class="text-center py-4">Đang tải dữ liệu...</td></tr>
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
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-1"></i> Cập Nhật Banner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeEditModal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditBanner">
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
                        <label class="form-label fw-bold">Hình ảnh mới (Tùy chọn)</label>
                        <input type="file" name="image_url" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Link liên kết</label>
                        <input type="text" name="link_url" id="edit_link" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Thứ tự hiển thị</label>
                        <input type="number" name="display_order" id="edit_order" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-warning w-100 fw-bold">
                        <i class="fas fa-save me-1"></i> Lưu Cập Nhật
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // URL gốc của API (Tùy chỉnh theo router của bạn)
    const API_BASE = '/web_qlsp/banners';

    // 1. Hàm Alert chung
    function showAlert(icon, message) {
        Swal.fire({ icon: icon, text: message, timer: 2000, showConfirmButton: false });
    }

    // 2. Hàm Tải danh sách Banner và vẽ lên HTML
    function loadBanners() {
        fetch(`${API_BASE}/api_get_data`) // <-- Đổi thành tên hàm API mới
            .then(res => res.json())
            .then(response => {
                const tbody = document.getElementById('banner-table-body');
                let html = '';

                if (response.success && response.data.length > 0) {
                    let stt = 1;
                    response.data.forEach(b => {
                        const imgPath = `/web_qlsp/Public/Picture/banners/${b.image_url}`;
                        // Badge trạng thái
                        const statusBadge = b.status == 1 
                            ? `<button onclick="toggleBanner(${b.id})" class="badge bg-success border-0">Hiển thị</button>`
                            : `<button onclick="toggleBanner(${b.id})" class="badge bg-secondary border-0">Ẩn</button>`;

                        html += `
                            <tr>
                                <td class="text-center fw-bold text-muted">${stt++}</td>
                                <td>
                                    <img src="${imgPath}" class="img-fluid rounded border" style="height:60px;width:100%;object-fit:cover;">
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">${b.title}</div>
                                    <small class="text-muted">${b.link_url}</small>
                                </td>
                                <td class="text-center">${statusBadge}</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm me-1" 
                                        onclick="openEditModal(${b.id}, '${b.title}', '${b.link_url}', ${b.display_order}, '${b.image_url}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteBanner(${b.id})">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="5" class="text-center py-4 text-muted">Chưa có banner nào.</td></tr>';
                }
                tbody.innerHTML = html;
            })
            .catch(err => showAlert('error', 'Lỗi kết nối đến máy chủ!'));
    }

    // Chạy tải dữ liệu ngay khi vừa vào trang
    document.addEventListener("DOMContentLoaded", loadBanners);

    // 3. Xử lý Thêm Banner (Gửi Form ngầm)
    document.getElementById('formAddBanner').addEventListener('submit', function(e) {
        e.preventDefault(); // Chặn hành động load lại trang
        
        let formData = new FormData(this); // Gói toàn bộ ảnh và text lại

        fetch(`${API_BASE}/add`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                this.reset(); // Xóa trắng form
                loadBanners(); // Tải lại bảng ngay lập tức
            } else {
                showAlert('error', data.message);
            }
        });
    });

    // 4. Xử lý Ẩn/Hiện
    function toggleBanner(id) {
        fetch(`${API_BASE}/toggle/${id}`)
            .then(res => res.json())
            .then(data => {
                if(data.success) loadBanners();
            });
    }

    // 5. Xử lý Xóa
    function deleteBanner(id) {
        Swal.fire({
            title: 'Chắc chắn xóa?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${API_BASE}/delete/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('success', data.message);
                            loadBanners();
                        } else {
                            showAlert('error', data.message);
                        }
                    });
            }
        });
    }

    // 6. Xử lý mở Modal Sửa và Nạp dữ liệu
    function openEditModal(id, title, link, order, image) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_title').value = title;
        document.getElementById('edit_link').value = link;
        document.getElementById('edit_order').value = order;
        document.getElementById('edit_old_image').value = image;
        
        const imgPath = image ? '/web_qlsp/Public/Picture/banners/' + image : '';
        document.getElementById('edit_image_preview').src = imgPath;

        // Kích hoạt mở modal (Bootstrap)
        new bootstrap.Modal(document.getElementById('editBannerModal')).show();
    }

    // 7. Xử lý Submit Sửa Banner
    document.getElementById('formEditBanner').addEventListener('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);

        fetch(`${API_BASE}/update`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                document.getElementById('closeEditModal').click(); // Đóng modal
                this.reset(); // Xóa form cũ
                loadBanners(); // Cập nhật lại bảng
            } else {
                showAlert('error', data.message);
            }
        });
    });
</script>