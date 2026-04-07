<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid px-4">
    <h1 class="mt-4 fw-bold">Quản Lý Giao Diện Trang Chủ</h1>

    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-dark text-white fw-bold">Thêm Section Mới</div>
                <div class="card-body bg-light">
                    <form id="formAddCampaign">
                        <div class="mb-3">
                            <label class="fw-bold">Loại hiển thị (*)</label>
                            <select class="form-select border-primary" id="sType" name="type" onchange="toggleFormBlocks('add')">
                                <option value="category_grid">1. Danh sách Danh mục</option>
                                <option value="overlay_banner">2. Banner Chữ Đè Ảnh</option>
                                <option value="collection">3. Danh sách Sản phẩm</option>
                                <option value="flash_sale">4. Flash Sale</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold">Tiêu đề Section (*)</label>
                            <input type="text" class="form-control" name="title" placeholder="VD: Hàng Mới Về" required>
                        </div>

                        <div id="add_block_banner" class="mb-3 p-3 border rounded bg-white shadow-sm" style="display:none;">
                            <h6 class="text-primary fw-bold border-bottom pb-2">Cấu hình Banner</h6>
                            <label>Ảnh nền (*)</label>
                            <input type="file" class="form-control mb-2" name="banner_image" accept="image/*">
                            <div class="row mb-2">
                                <div class="col-6">
                                    <label>Chữ trên nút</label>
                                    <input type="text" class="form-control" name="button_text" value="XEM NGAY">
                                </div>
                                <div class="col-6">
                                    <label>Vị trí chữ</label>
                                    <select class="form-select" name="text_position">
                                        <option value="left">Trái</option>
                                        <option value="center">Giữa</option>
                                        <option value="right">Phải</option>
                                    </select>
                                </div>
                            </div>
                            <label>Link khi bấm vào</label>
                            <input type="text" class="form-control" name="link_url" placeholder="#">
                        </div>

                        <div id="add_block_collection" class="mb-3 p-3 border rounded bg-white shadow-sm" style="display:none;">
                            <h6 class="text-success fw-bold border-bottom pb-2">Chọn Nguồn Sản Phẩm</h6>
                            <label>Lấy từ Bộ Sưu Tập nào?</label>
                            <select class="form-select" name="collection_id" id="add_collection_select">
                                <option value="">-- Chọn Collection --</option>
                                </select>
                        </div>

                        <div id="add_block_flash" class="mb-3 p-3 border rounded bg-white shadow-sm" style="display:none;">
                            <h6 class="text-danger fw-bold border-bottom pb-2">Thời gian Flash Sale</h6>
                            <label>Kết thúc lúc:</label>
                            <input type="datetime-local" class="form-control" name="end_time">
                        </div>

                        <div class="p-3 border rounded bg-white shadow-sm mb-3">
                            <h6 class="fw-bold border-bottom pb-2">Giao diện & Thứ tự</h6>
                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="fw-bold small">Màu nền</label>
                                    <input type="color" class="form-control form-control-color w-100" name="bg_color" value="#ffffff">
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold small">Màu chữ tiêu đề</label>
                                    <input type="color" class="form-control form-control-color w-100" name="text_color" value="#000000">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="fw-bold small">Thứ tự hiển thị</label>
                                <input type="number" class="form-control" name="display_order" value="0">
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" checked>
                                <label class="form-check-label">Hiển thị ngay</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="fas fa-save me-1"></i> LƯU CẤU HÌNH</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Danh sách các mục trên trang chủ</div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>TT</th>
                                <th>Tiêu đề</th>
                                <th>Loại</th>
                                <th>Màu sắc</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="campaign-table-body">
                            <tr><td colspan="5" class="text-center py-4">Đang tải dữ liệu...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEditCampaign">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold">Chỉnh Sửa Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeEditModal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="old_banner_image" id="edit_old_image">
                    
                    <div class="mb-3">
                        <label class="fw-bold">Loại hiển thị (*)</label>
                        <select class="form-select" id="edit_type" name="type" onchange="toggleFormBlocks('edit')">
                            <option value="category_grid">1. Danh sách Danh mục</option>
                            <option value="overlay_banner">2. Banner Chữ Đè Ảnh</option>
                            <option value="collection">3. Danh sách Sản phẩm</option>
                            <option value="flash_sale">4. Flash Sale</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Tiêu đề Section (*)</label>
                        <input type="text" class="form-control" name="title" id="edit_title" required>
                    </div>

                    <div id="edit_block_banner" class="mb-3 p-3 border rounded bg-light" style="display:none;">
                        <h6 class="text-primary fw-bold">Cấu hình Banner</h6>
                        <label>Ảnh nền mới (để trống nếu không đổi)</label>
                        <input type="file" class="form-control mb-2" name="banner_image" accept="image/*">
                        <div class="row mb-2">
                            <div class="col-6">
                                <label>Chữ trên nút</label>
                                <input type="text" class="form-control" name="button_text" id="edit_button_text">
                            </div>
                            <div class="col-6">
                                <label>Vị trí chữ</label>
                                <select class="form-select" name="text_position" id="edit_text_position">
                                    <option value="left">Trái</option>
                                    <option value="center">Giữa</option>
                                    <option value="right">Phải</option>
                                </select>
                            </div>
                        </div>
                        <label>Link khi bấm vào</label>
                        <input type="text" class="form-control" name="link_url" id="edit_link_url">
                    </div>

                    <div id="edit_block_collection" class="mb-3 p-3 border rounded bg-light" style="display:none;">
                        <h6 class="text-success fw-bold">Chọn Nguồn Sản Phẩm</h6>
                        <label>Lấy từ Bộ Sưu Tập nào?</label>
                        <select class="form-select" name="collection_id" id="edit_collection_select">
                            <option value="">-- Chọn Collection --</option>
                        </select>
                    </div>

                    <div id="edit_block_flash" class="mb-3 p-3 border rounded bg-light" style="display:none;">
                        <h6 class="text-danger fw-bold">Thời gian Flash Sale</h6>
                        <label>Kết thúc lúc:</label>
                        <input type="datetime-local" class="form-control" name="end_time" id="edit_end_time">
                    </div>

                    <div class="p-3 border rounded bg-light mb-3">
                        <h6 class="fw-bold">Giao diện & Thứ tự</h6>
                        <div class="row mb-2">
                            <div class="col-6">
                                <label class="fw-bold small">Màu nền</label>
                                <input type="color" class="form-control form-control-color w-100" name="bg_color" id="edit_bg_color">
                            </div>
                            <div class="col-6">
                                <label class="fw-bold small">Màu chữ</label>
                                <input type="color" class="form-control form-control-color w-100" name="text_color" id="edit_text_color">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="fw-bold small">Thứ tự hiển thị</label>
                            <input type="number" class="form-control" name="display_order" id="edit_display_order">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
                            <label class="form-check-label">Hiển thị ngay</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning fw-bold"><i class="fas fa-save me-1"></i> CẬP NHẬT</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const API_BASE = '/web_qlsp/campaigns';
    let globalCampaigns = []; // Lưu danh sách để JS tái sử dụng lúc mở Modal Sửa

    function showAlert(icon, message) {
        Swal.fire({ icon: icon, text: message, timer: 2000, showConfirmButton: false });
    }

    // 1. Quản lý việc Ẩn/Hiện các block chức năng khi đổi "Loại hiển thị"
    function toggleFormBlocks(mode) {
        // mode = 'add' hoặc 'edit'
        const typeSelectId = mode === 'add' ? 'sType' : 'edit_type';
        const type = document.getElementById(typeSelectId).value;

        // Reset tất cả về ẩn
        document.getElementById(`${mode}_block_banner`).style.display = 'none';
        document.getElementById(`${mode}_block_collection`).style.display = 'none';
        document.getElementById(`${mode}_block_flash`).style.display = 'none';

        if (type === 'overlay_banner') {
            document.getElementById(`${mode}_block_banner`).style.display = 'block';
        } else if (type === 'collection') {
            document.getElementById(`${mode}_block_collection`).style.display = 'block';
        } else if (type === 'flash_sale') {
            document.getElementById(`${mode}_block_collection`).style.display = 'block';
            document.getElementById(`${mode}_block_flash`).style.display = 'block';
        }
    }

    // Chạy mặc định lúc load trang để giấu form
    toggleFormBlocks('add');

    // 2. Tải dữ liệu API: Campaigns & Collections
    function loadData() {
        fetch(`${API_BASE}/api_get_data`)
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    globalCampaigns = res.data.campaigns;
                    renderCampaignTable(res.data.campaigns);
                    renderCollectionDropdowns(res.data.collections);
                }
            })
            .catch(err => showAlert('error', 'Lỗi tải dữ liệu máy chủ'));
    }

    function renderCampaignTable(campaigns) {
        const tbody = document.getElementById('campaign-table-body');
        let html = '';

        if (campaigns.length > 0) {
            let stt = 1;
            campaigns.forEach(c => {
                let type_label = '';
                let badge_class = '';
                
                if (c.section_type === 'category_grid') { type_label = 'Danh mục'; badge_class = 'bg-primary'; }
                else if (c.section_type === 'overlay_banner') { type_label = 'Banner'; badge_class = 'bg-warning text-dark'; }
                else if (c.section_type === 'collection') { type_label = 'DS Sản phẩm'; badge_class = 'bg-info text-dark'; }
                else if (c.section_type === 'flash_sale') { type_label = 'Flash Sale'; badge_class = 'bg-danger'; }

                const statusBtn = c.status == 1 
                    ? `<button onclick="toggleCampaign(${c.id})" class="btn btn-sm btn-success me-1" title="Ẩn"><i class="fas fa-eye"></i></button>`
                    : `<button onclick="toggleCampaign(${c.id})" class="btn btn-sm btn-secondary me-1" title="Hiện"><i class="fas fa-eye-slash"></i></button>`;

                html += `
                    <tr style="border-left: 5px solid ${c.bg_color};">
                        <td class="text-center fw-bold">${stt++}</td>
                        <td class="fw-bold" style="color:${c.text_color};">${c.title}</td>
                        <td><span class="badge ${badge_class}">${type_label}</span></td>
                        <td>
                            <div class="d-flex gap-1">
                                <div style="width:20px;height:20px;background:${c.bg_color};border:1px solid #ddd;" title="Màu nền"></div>
                                <div style="width:20px;height:20px;background:${c.text_color};border:1px solid #ddd;" title="Màu chữ"></div>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning me-1" onclick="openEditModal(${c.id})" title="Sửa"><i class="fas fa-edit"></i></button>
                            ${statusBtn}
                            <button class="btn btn-sm btn-danger" onclick="deleteCampaign(${c.id})" title="Xóa"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        } else {
            html = '<tr><td colspan="5" class="text-center py-4 text-muted">Chưa có section nào.</td></tr>';
        }
        tbody.innerHTML = html;
    }

    function renderCollectionDropdowns(collections) {
        let options = '<option value="">-- Chọn Collection --</option>';
        collections.forEach(col => {
            options += `<option value="${col.id}">${col.name}</option>`;
        });
        document.getElementById('add_collection_select').innerHTML = options;
        document.getElementById('edit_collection_select').innerHTML = options;
    }

    // Tải bảng khi trang web mở lên
    document.addEventListener("DOMContentLoaded", loadData);

    // 3. Xử lý Thêm mới
    document.getElementById('formAddCampaign').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(`${API_BASE}/add`, {
            method: 'POST',
            body: new FormData(this)
        }).then(res => res.json()).then(data => {
            showAlert(data.success ? 'success' : 'error', data.message);
            if (data.success) {
                this.reset();
                toggleFormBlocks('add'); // Reset lại các khối
                loadData();
            }
        });
    });

    // 4. Xử lý Ẩn/Hiện
    function toggleCampaign(id) {
        fetch(`${API_BASE}/toggle/${id}`).then(res => res.json()).then(data => {
            if(data.success) loadData();
        });
    }

    // 5. Xử lý Xóa
    function deleteCampaign(id) {
        Swal.fire({
            title: 'Chắc chắn xóa?', icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Xóa', cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${API_BASE}/delete/${id}`).then(res => res.json()).then(data => {
                    showAlert(data.success ? 'success' : 'error', data.message);
                    if (data.success) loadData();
                });
            }
        });
    }

    // 6. Mở Modal Sửa
    function openEditModal(id) {
        // Tìm campaign trong mảng global
        const c = globalCampaigns.find(item => item.id == id);
        if(!c) return;

        document.getElementById('edit_id').value = c.id;
        document.getElementById('edit_title').value = c.title;
        document.getElementById('edit_type').value = c.section_type;
        
        document.getElementById('edit_bg_color').value = c.bg_color;
        document.getElementById('edit_text_color').value = c.text_color;
        document.getElementById('edit_display_order').value = c.display_order;
        document.getElementById('edit_is_active').checked = c.status == 1;

        // Dữ liệu tùy chọn
        document.getElementById('edit_old_image').value = c.image_url || '';
        document.getElementById('edit_button_text').value = c.button_text || '';
        document.getElementById('edit_text_position').value = c.text_position || 'left';
        document.getElementById('edit_link_url').value = c.link_url || '';
        document.getElementById('edit_collection_select').value = c.collection_id || '';
        
        // Cắt bỏ phần giây (.000Z hoặc đuôi giây) để gắn vào input datetime-local nếu có end_time
        if (c.end_time) {
            let d = new Date(c.end_time);
            d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
            document.getElementById('edit_end_time').value = d.toISOString().slice(0,16);
        } else {
            document.getElementById('edit_end_time').value = '';
        }

        // Hiện đúng block
        toggleFormBlocks('edit');

        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    // 7. Xử lý Submit Sửa
    document.getElementById('formEditCampaign').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(`${API_BASE}/update`, {
            method: 'POST',
            body: new FormData(this)
        }).then(res => res.json()).then(data => {
            showAlert(data.success ? 'success' : 'error', data.message);
            if (data.success) {
                document.getElementById('closeEditModal').click();
                loadData();
            }
        });
    });
</script>