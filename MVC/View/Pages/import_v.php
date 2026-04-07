<div class="row">
    <div class="col-12 mb-4">
        <h4 class="fw-bold">Nhập dữ liệu từ Excel</h4>
        <p class="text-muted">
            Lưu ý: Hệ thống hỗ trợ định dạng <b>.xlsx</b> hoặc <b>.xls</b> (PHPExcel)
        </p>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white fw-bold">
                1. NHẬP DANH MỤC
            </div>
            <div class="card-body">
                <form id="formImportCategories">
                    <div class="mb-3">
                        <label class="form-label">Chọn file Danh mục</label>
                        <input type="file" name="txtfile" class="form-control" accept=".xlsx, .xls" required>
                    </div>

                    <div class="alert alert-light border">
                        <small>
                            <strong>Cấu trúc file mẫu:</strong><br>
                            Cột A: Tên danh mục | Cột B: Slug | Cột C: Ảnh
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-upload me-1"></i> Upload Danh mục
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white fw-bold">
                2. NHẬP SẢN PHẨM
            </div>
            <div class="card-body">
                <form id="formImportProducts">
                    <div class="mb-3">
                        <label class="form-label">Chọn file Sản phẩm</label>
                        <input type="file" name="txtfile" class="form-control" accept=".xlsx, .xls" required>
                    </div>

                    <div class="alert alert-light border">
                        <small>
                            <strong>Cấu trúc cột:</strong><br>
                            A: Tên | B: Giá | C: ID Danh mục | D: Mô tả
                        </small>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-upload me-1"></i> Upload Sản phẩm
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const API_BASE = '/web_qlsp/import';

    // 1. Xử lý Form Upload Danh Mục
    document.getElementById('formImportCategories').addEventListener('submit', function(e) {
        e.preventDefault(); // Chặn load trang

        // Bật popup xoay vòng chờ đợi
        Swal.fire({
            title: 'Đang xử lý dữ liệu...',
            text: 'Vui lòng không đóng trình duyệt!',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        // Gửi ngầm dữ liệu lên API
        fetch(`${API_BASE}/api_upload_categories`, {
            method: 'POST',
            body: new FormData(this) // Gói file Excel vào
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Hoàn tất!', text: data.message });
                this.reset(); // Xóa trắng ô input file
            } else {
                Swal.fire({ icon: 'error', title: 'Lỗi', text: data.message });
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Lỗi máy chủ', text: 'Không thể kết nối đến hệ thống!' });
        });
    });

    // 2. Xử lý Form Upload Sản Phẩm
    document.getElementById('formImportProducts').addEventListener('submit', function(e) {
        e.preventDefault(); // Chặn load trang

        // Bật popup xoay vòng chờ đợi
        Swal.fire({
            title: 'Đang xử lý dữ liệu...',
            text: 'Tiến trình có thể mất vài phút nếu file lớn.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        // Gửi ngầm dữ liệu lên API
        fetch(`${API_BASE}/api_upload_products`, {
            method: 'POST',
            body: new FormData(this)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Hoàn tất!', text: data.message });
                this.reset(); // Xóa trắng ô input file
            } else {
                Swal.fire({ icon: 'error', title: 'Lỗi', text: data.message });
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Lỗi máy chủ', text: 'Không thể kết nối đến hệ thống!' });
        });
    });
</script>