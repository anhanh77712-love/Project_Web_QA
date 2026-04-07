<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Bộ sưu tập (Collections)</h4>
        <p class="text-muted small mb-0">
            Quản lý các nhóm sản phẩm theo chủ đề (VD: Mùa hè, Chạy bộ...)
        </p>
    </div>
</div>

<div class="card-table">
    <div class="p-3 border-bottom bg-white">
        <form id="formSearch" class="d-flex align-items-center w-100">
            <div class="search-wrapper position-relative me-auto">
                <div class="search-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                        <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 1 0 0 13.5 6.75 6.75 0 0 0 0-13.5ZM2.25 10.5a8.25 8.25 0 1 1 14.59 5.28l4.69 4.69a.75.75 0 1 1-1.06 1.06l-4.69-4.69A8.25 8.25 0 0 1 2.25 10.5Z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="text" id="txtSearch" class="form-control form-search" placeholder="Tìm kiếm bộ sưu tập...">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-dark-blue"><i class="fas fa-search"></i> Tìm</button>
                <button type="button" class="btn btn-light-gray" onclick="resetSearch()"><i class="fas fa-undo-alt"></i> Làm mới</button>
                <button type="button" class="btn btn-green" onclick="exportExcel()"><i class="fas fa-file-excel"></i> Xuất Excel</button>
                <button type="button" class="btn btn-blue" data-bs-toggle="modal" data-bs-target="#importCollectionModal"><i class="fas fa-file-import"></i> Nhập Excel</button>
                <button type="button" class="btn btn-dark-blue" data-bs-toggle="modal" data-bs-target="#addCollectionModal"><i class="fas fa-plus"></i> Thêm bộ sưu tập</button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th width="10%">ID</th>
                    <th width="10%">Ảnh</th>
                    <th width="30%">Tên Bộ sưu tập</th>
                    <th width="15%" class="text-center">Số lượng</th> 
                    <th width="25%">Slug</th>
                    <th width="10%" class="text-end">Hành động</th>
                </tr>
            </thead>
            
            <tbody id="loading-skeleton"> 
                <?php for ($i = 0; $i < 5; $i++): ?> 
                <tr> 
                    <td><div class="skeleton" style="width: 30px; height: 18px;"></div></td> 
                    <td><div class="skeleton rounded border" style="width: 50px; height: 50px;"></div></td> 
                    <td><div class="skeleton" style="width: 180px; height: 20px;"></div></td> 
                    <td><div class="skeleton" style="width: 40px; height: 20px; margin: 0 auto;"></div></td> 
                    <td><div class="skeleton" style="width: 120px; height: 22px; border-radius: 12px;"></div></td> 
                    <td class="text-end"> 
                        <div class="skeleton" style="width: 24px; height: 24px; display: inline-block;"></div> 
                        <div class="skeleton" style="width: 24px; height: 24px; display: inline-block;"></div> 
                    </td> 
                </tr> 
                <?php endfor; ?> 
            </tbody>

            <tbody id="actual-content" style="display: none;"></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="editCollectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Chỉnh sửa Bộ sưu tập</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeEditModal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditCollection">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="old_image" id="edit_old_image">

                    <div class="mb-3">
                        <label class="form-label">Tên Bộ sưu tập</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required onkeyup="generateSlug(this.value, 'edit_slug_modal')">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ảnh bìa</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div id="preview_area" class="mt-2" style="display:none;">
                            <small class="text-muted d-block mb-1">Ảnh hiện tại:</small>
                            <img id="edit_preview_img" src="" class="rounded border" width="80">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" id="edit_slug_modal" class="form-control bg-light" readonly>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Lưu thay đổi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCollectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Thêm Bộ sưu tập mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeAddModal"></button>
            </div>
            <div class="modal-body">
                <form id="formAddCollection">
                    <div class="mb-3">
                        <label class="form-label">Tên Bộ sưu tập</label>
                        <input type="text" class="form-control" placeholder="VD: Mùa hè 2025" name="name" required onkeyup="generateSlug(this.value, 'add_slug')">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control bg-light" readonly name="slug" id="add_slug">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ảnh bìa (Thumbnail)</label>
                        <input type="file" class="form-control" accept="image/*" name="image">
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Thêm ngay</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importCollectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Nhập Bộ Sưu Tập từ Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeImportModal"></button>
            </div>
            <form id="formImportExcel">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Chọn file Excel (.xlsx, .xls)</label>
                        <input class="form-control" type="file" name="import_file_collection" accept=".xlsx, .xls" required>
                    </div>
                    <div class="alert alert-warning">
                        <small><strong>Cấu trúc cột Excel:</strong><br>- Cột A: Tên Bộ Sưu Tập<br>- Cột B: Link ảnh (Online) hoặc Tên ảnh (có sẵn trong folder)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success">Xác nhận nhập</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/web_qlsp/Public/css/loading.css">
<link rel="stylesheet" href="/web_qlsp/Public/css/collections.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const API_BASE = '/web_qlsp/collections';

    function showAlert(icon, message) {
        Swal.fire({ icon: icon, text: message, timer: 2000, showConfirmButton: false });
    }

    // 1. Tải danh sách
    function loadData(query = '') {
        document.getElementById('loading-skeleton').style.display = 'table-row-group';
        document.getElementById('actual-content').style.display = 'none';

        fetch(`${API_BASE}/api_get_data?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(res => {
                const tbody = document.getElementById('actual-content');
                let html = '';

                if (res.success && res.data.length > 0) {
                    res.data.forEach(c => {
                        let imgTag = c.thumbnail && c.thumbnail !== 'no-image.jpg'
                            ? `<img src="/web_qlsp/Public/Picture/collections/${c.thumbnail}" class="rounded border" style="width:50px; height:50px; object-fit:cover;">`
                            : `<span class="badge bg-light text-secondary border">No Image</span>`;

                        html += `
                            <tr>
                                <td class="text-muted">#${c.id}</td>
                                <td>${imgTag}</td>
                                <td class="fw-bold text-dark">${c.name}</td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark rounded-pill px-3">${c.product_count} sản phẩm</span>
                                </td>
                                <td><span class="badge-slug">${c.slug}</span></td>
                                <td class="text-end">
                                    <button class="btn-icon text-primary me-1" onclick="openEditModal(${c.id}, '${c.name}', '${c.slug}', '${c.thumbnail}')"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg></button>
                                    <button class="btn-icon text-danger" onclick="deleteCollection(${c.id})"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="6" class="text-center py-5 text-muted">Chưa có Bộ sưu tập nào.</td></tr>';
                }

                tbody.innerHTML = html;
                document.getElementById('loading-skeleton').style.display = 'none';
                document.getElementById('actual-content').style.display = 'table-row-group';
            })
            .catch(err => showAlert('error', 'Lỗi kết nối API'));
    }

    document.addEventListener("DOMContentLoaded", () => loadData());

    // 2. Tìm kiếm
    document.getElementById('formSearch').addEventListener('submit', function(e) {
        e.preventDefault();
        loadData(document.getElementById('txtSearch').value);
    });

    function resetSearch() {
        document.getElementById('txtSearch').value = '';
        loadData();
    }

    // 3. Xuất Excel
    function exportExcel() {
        window.location.href = `${API_BASE}/export?q=${encodeURIComponent(document.getElementById('txtSearch').value)}`;
    }

    // 4. Thêm Bộ sưu tập
    document.getElementById('formAddCollection').addEventListener('submit', function(e) {
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

    // 5. Mở form Sửa
    function openEditModal(id, name, slug, thumbnail) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_slug_modal').value = slug;
        document.getElementById('edit_old_image').value = thumbnail;
        
        const imgPath = (thumbnail && thumbnail !== 'no-image.jpg') ? `/web_qlsp/Public/Picture/collections/${thumbnail}` : '';
        document.getElementById('edit_preview_img').src = imgPath;
        document.getElementById('preview_area').style.display = imgPath ? 'block' : 'none';

        new bootstrap.Modal(document.getElementById('editCollectionModal')).show();
    }

    // 6. Lưu thay đổi
    document.getElementById('formEditCollection').addEventListener('submit', function(e) {
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

    // 7. Xóa
    function deleteCollection(id) {
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

    // 8. Import Excel
    document.getElementById('formImportExcel').addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({ title: 'Đang xử lý...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch(`${API_BASE}/importExcelCollections`, { method: 'POST', body: new FormData(this) })
            .then(res => res.json()).then(data => {
                if(data.success) {
                    Swal.fire({ icon: 'success', title: 'Hoàn tất', html: data.message });
                    this.reset();
                    document.getElementById('closeImportModal').click();
                    loadData();
                } else {
                    Swal.fire({ icon: 'error', title: 'Lỗi', text: data.message });
                }
            });
    });

    // Hàm tạo Slug
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