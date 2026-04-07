<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="/web_qlsp/Public/css/loading.css">
<link rel="stylesheet" href="/web_qlsp/Public/Css/categories.css">

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Danh mục sản phẩm</h4>
    <p class="text-muted small mb-0">Quản lý các nhóm sản phẩm hiển thị trên website</p>
  </div>
</div>

<div class="card-table">
  <div class="p-3 border-bottom bg-white">
        <form id="formSearch" class="d-flex align-items-center w-100">
            <div class="search-wrapper position-relative me-auto">
                <div class="search-icon"><i class="fas fa-search"></i></div>
                <input type="text" id="txtSearch" class="form-control form-search" placeholder="Tìm kiếm danh mục...">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-dark-blue"><i class="fas fa-search"></i> Tìm</button>
                <button type="button" class="btn btn-light-gray" onclick="resetSearch()"><i class="fas fa-undo-alt"></i> Làm mới</button>
                <button type="button" class="btn btn-green" onclick="exportExcel()"><i class="fas fa-file-excel"></i> Xuất Excel</button>
                <button type="button" class="btn btn-blue" data-bs-toggle="modal" data-bs-target="#importModal"><i class="fas fa-file-import"></i> Nhập Excel</button>
                <button type="button" class="btn btn-dark-blue" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="fas fa-plus"></i> Thêm Danh Mục</button>
            </div>
        </form>
    </div>

  <div class="table-responsive">
    <table class="table-modern">
      <thead>
        <tr>
          <th width="10%">ID</th>
          <th width="15%">Hình ảnh</th>
          <th width="35%">Tên danh mục</th>
          <th width="25%">Đường dẫn (Slug)</th>
          <th width="15%" class="text-end">Hành động</th>
        </tr>
      </thead>
      
      <tbody id="loading-skeleton"> 
        <?php for ($i = 0; $i < 5; $i++): ?> 
          <tr>
            <td><div class="skeleton" style="width: 30px; height: 15px;"></div></td>
            <td><div class="skeleton rounded border" style="width: 50px; height: 50px;"></div></td>
            <td><div class="skeleton" style="width: 150px; height: 18px;"></div></td>
            <td><div class="skeleton" style="width: 100px; height: 20px; border-radius: 10px;"></div></td>
            <td class="text-end">
              <div class="skeleton" style="width: 32px; height: 32px; display: inline-block;"></div>
              <div class="skeleton" style="width: 32px; height: 32px; display: inline-block;"></div>
            </td>
          </tr> 
        <?php endfor; ?> 
      </tbody>

      <tbody id="actual-content" style="display: none;">
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Thêm danh mục mới</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeAddModal"></button>
      </div>
      <div class="modal-body">
        <form id="formAddCategory">
          <div class="mb-3">
            <label class="form-label">Tên danh mục</label>
            <input type="text" name="name" class="form-control" required onkeyup="generateSlug(this.value, 'add_slug')">
          </div>
          <div class="mb-3">
            <label class="form-label">Ảnh đại diện</label>
            <input type="file" name="image" class="form-control" accept="image/*">
          </div>
          <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" id="add_slug" class="form-control bg-light" readonly>
          </div>
          <button type="submit" class="btn btn-dark w-100">Thêm ngay</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Chỉnh sửa danh mục</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeEditModal"></button>
      </div>
      <div class="modal-body">
        <form id="formEditCategory">
          <input type="hidden" name="id" id="edit_id">
          <input type="hidden" name="old_image" id="edit_old_image">

          <div class="mb-3">
            <label class="form-label">Tên danh mục</label>
            <input type="text" name="name" id="edit_name" class="form-control" required onkeyup="generateSlug(this.value, 'edit_slug')">
          </div>
          <div class="mb-3">
            <label class="form-label">Ảnh đại diện</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <div id="preview_area" class="mt-2">
              <small class="text-muted d-block mb-1">Ảnh hiện tại:</small>
              <img id="edit_preview_img" src="" class="rounded border" width="80">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" id="edit_slug" class="form-control bg-light" readonly>
          </div>
          <button type="submit" class="btn btn-primary w-100">Lưu thay đổi</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Nhập Danh Mục từ Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeImportModal"></button>
            </div>
            <form id="formImportExcel">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Chọn file Excel (.xlsx, .xls)</label>
                        <input class="form-control" type="file" name="import_file" accept=".xlsx, .xls, .csv" required>
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <strong>Cấu trúc file Excel:</strong><br>
                            - Cột A: Tên danh mục<br>
                            - Cột B: Đường dẫn ảnh (Thumbnail)
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Xác nhận nhập</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const API_BASE = '/web_qlsp/categories_list';

    function showAlert(icon, message) {
        Swal.fire({ icon: icon, text: message, timer: 2000, showConfirmButton: false });
    }

    // 1. Tải dữ liệu API (Có hỗ trợ tìm kiếm)
    function loadData(query = '') {
        // Hiện Skeleton, ẩn Bảng
        document.getElementById('loading-skeleton').style.display = 'table-row-group';
        document.getElementById('actual-content').style.display = 'none';

        fetch(`${API_BASE}/api_get_data?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(res => {
                const tbody = document.getElementById('actual-content');
                let html = '';

                if (res.success && res.data.length > 0) {
                    res.data.forEach(c => {
                        let imgTag = c.thumbnail 
                            ? `<img src="/web_qlsp/Public/Picture/categories/${c.thumbnail}" class="rounded border" style="width:50px; height:50px; object-fit:cover;">`
                            : `<span class="badge bg-light text-secondary border">No Image</span>`;

                        html += `
                            <tr>
                                <td class="text-muted">#${c.id}</td>
                                <td>${imgTag}</td>
                                <td class="fw-bold text-dark">${c.name}</td>
                                <td><span class="badge-slug">${c.slug}</span></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary me-1" onclick="openEditModal(${c.id}, '${c.name}', '${c.slug}', '${c.thumbnail}')"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(${c.id})"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="5" class="text-center py-5 text-muted">Không tìm thấy danh mục nào.</td></tr>';
                }

                tbody.innerHTML = html;
                // Ẩn Skeleton, hiện Bảng
                document.getElementById('loading-skeleton').style.display = 'none';
                document.getElementById('actual-content').style.display = 'table-row-group';
            })
            .catch(err => showAlert('error', 'Lỗi tải dữ liệu'));
    }

    document.addEventListener("DOMContentLoaded", () => loadData());

    // 2. Tìm kiếm (Không F5)
    document.getElementById('formSearch').addEventListener('submit', function(e) {
        e.preventDefault();
        const keyword = document.getElementById('txtSearch').value;
        loadData(keyword);
    });

    // Reset tìm kiếm
    function resetSearch() {
        document.getElementById('txtSearch').value = '';
        loadData();
    }

    // 3. Xuất Excel
    function exportExcel() {
        const keyword = document.getElementById('txtSearch').value;
        // Chuyển hướng trình duyệt để tải file thay vì dùng fetch AJAX
        window.location.href = `${API_BASE}/export?q=${encodeURIComponent(keyword)}`;
    }

    // 4. Thêm Mới
    document.getElementById('formAddCategory').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(`${API_BASE}/add`, { method: 'POST', body: new FormData(this) })
            .then(res => res.json()).then(data => {
                showAlert(data.success ? 'success' : 'error', data.message);
                if(data.success) {
                    this.reset();
                    document.getElementById('closeAddModal').click();
                    loadData(document.getElementById('txtSearch').value);
                }
            });
    });

    // 5. Xóa
    function deleteCategory(id) {
        Swal.fire({
            title: 'Xác nhận xóa?', text: 'Dữ liệu không thể khôi phục!', icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Xóa', cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${API_BASE}/delete/${id}`)
                    .then(res => res.json()).then(data => {
                        showAlert(data.success ? 'success' : 'error', data.message);
                        if(data.success) loadData(document.getElementById('txtSearch').value);
                    });
            }
        });
    }

    // 6. Mở Modal Sửa
    function openEditModal(id, name, slug, thumbnail) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_slug').value = slug;
        document.getElementById('edit_old_image').value = thumbnail;
        
        const imgPath = thumbnail ? `/web_qlsp/Public/Picture/categories/${thumbnail}` : '';
        document.getElementById('edit_preview_img').src = imgPath;
        document.getElementById('edit_preview_img').style.display = thumbnail ? 'block' : 'none';

        new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
    }

    // 7. Lưu Sửa
    document.getElementById('formEditCategory').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(`${API_BASE}/update`, { method: 'POST', body: new FormData(this) })
            .then(res => res.json()).then(data => {
                showAlert(data.success ? 'success' : 'error', data.message);
                if(data.success) {
                    document.getElementById('closeEditModal').click();
                    loadData(document.getElementById('txtSearch').value);
                }
            });
    });

    // 8. Import Excel
    document.getElementById('formImportExcel').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Hiện trạng thái đang xử lý (vì import có thể mất chút thời gian do tải ảnh)
        Swal.fire({ title: 'Đang xử lý...', text: 'Vui lòng đợi', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        fetch(`${API_BASE}/importExcelCat`, { method: 'POST', body: new FormData(this) })
            .then(res => res.json()).then(data => {
                if(data.success) {
                    Swal.fire({ icon: 'success', title: 'Thành công', text: data.message });
                    this.reset();
                    document.getElementById('closeImportModal').click();
                    loadData(); // Tải lại bảng
                } else {
                    Swal.fire({ icon: 'error', title: 'Lỗi', text: data.message });
                }
            }).catch(err => {
                Swal.fire({ icon: 'error', title: 'Lỗi máy chủ', text: 'Không thể kết nối API' });
            });
    });

    // Hàm tạo Slug của bạn (giữ nguyên logic)
    function generateSlug(text, targetId) {
        let slug = text.toLowerCase();
        slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
        slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
        slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
        slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
        slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
        slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
        slug = slug.replace(/đ/gi, 'd');
        slug = slug.replace(/[^a-z0-9 -]/g, '');
        slug = slug.replace(/\s+/g, '-');
        slug = slug.replace(/-+/g, '-');
        document.getElementById(targetId).value = slug;
    }
</script>