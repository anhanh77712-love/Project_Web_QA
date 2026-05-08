<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Quản lý Mã giảm giá</h4>
</div>

<div class="toolbar-container d-flex align-items-center gap-2"> 
    <form id="formSearch" class="d-flex align-items-center flex-grow-1"> 
        <div class="search-wrapper me-auto">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="txtSearch" class="form-control form-search" placeholder="Tìm kiếm vouchers...">
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-dark-blue"><i class="fas fa-search"></i> Tìm</button>
            <button type="button" class="btn btn-light-gray" onclick="resetSearch()"><i class="fas fa-undo-alt"></i> Làm mới</button>
            <button type="button" class="btn btn-green" onclick="exportExcel()"><i class="fas fa-file-excel"></i> Xuất Excel</button>
            <button type="button" class="btn btn-dark-blue" data-bs-toggle="modal" data-bs-target="#addVoucherModal"><i class="fas fa-plus"></i> Thêm Voucher</button>
        </div>
    </form>

    <div class="input-group" style="width: auto;">
        <span class="input-group-text bg-light fw-bold text-secondary"><i class="fas fa-filter me-1"></i> Trạng thái</span>
        <select id="statusFilter" class="form-select focus-ring-none" onchange="loadData()" style="min-width: 130px;">
            <option value="all">Tất cả</option>
            <option value="running">Đang chạy</option>
            <option value="upcoming">Sắp diễn ra</option>
            <option value="expired">Đã kết thúc</option>
            <option value="empty">Hết lượt</option>
            <option value="hidden">Đang ẩn (DB)</option>
        </select>
    </div>
</div>

<div class="bg-white rounded-3 shadow-sm overflow-auto">
    <table class="table table-hover align-middle mb-0 text-nowrap">
        <thead class="bg-light">
            <tr>
                <th class="fw-bold ps-4">Mã Code</th>
                <th class="fw-bold text-center">Trạng thái</th> 
                <th class="fw-bold">Thời gian</th>
                <th class="fw-bold">Loại giảm</th>
                <th class="fw-bold">Giá trị</th>
                <th class="fw-bold">Điều kiện</th>
                <th class="fw-bold text-center">Đã dùng</th>
                <th class="fw-bold text-center">Còn lại</th>
                <th class="fw-bold text-end pe-4">Hành động</th>
            </tr>
        </thead>
        
        <tbody id="loading-skeleton">
            <?php for($i=0; $i<5; $i++): ?>
            <tr>
                <td class="ps-4"><div class="skeleton" style="width: 80px; height: 26px;"></div></td>
                <td class="text-center"><div class="skeleton" style="width: 100px; height: 24px; border-radius: 12px; margin: 0 auto;"></div></td>
                <td><div class="skeleton mb-1" style="width: 120px; height: 12px;"></div><div class="skeleton" style="width: 120px; height: 12px;"></div></td>
                <td><div class="skeleton" style="width: 70px; height: 20px;"></div></td>
                <td><div class="skeleton" style="width: 50px; height: 20px;"></div></td>
                <td><div class="skeleton mb-1" style="width: 90px; height: 12px;"></div><div class="skeleton" style="width: 60px; height: 12px;"></div></td>
                <td class="text-center"><div class="skeleton mx-auto" style="width: 30px; height: 20px;"></div></td>
                <td class="text-center"><div class="skeleton mx-auto" style="width: 30px; height: 20px;"></div></td>
                <td class="text-end pe-4"><div class="skeleton d-inline-block me-1" style="width: 30px; height: 30px;"></div><div class="skeleton d-inline-block" style="width: 30px; height: 30px;"></div></td>
            </tr>
            <?php endfor; ?>
        </tbody>

        <tbody id="actual-content" style="display: none;"></tbody>
    </table>
</div>

<div class="modal fade custom-modal-style" id="addVoucherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tạo mã giảm giá mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeAddModal"></button>
            </div>
            <div class="modal-body">
                <form id="formAddVoucher">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Mã Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control uppercase-input" placeholder="VD: SALE50" name="code" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới hạn lượt dùng</label>
                            <input type="number" class="form-control" value="100" name="usage_limit">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả chương trình</label>
                        <textarea class="form-control" rows="2" placeholder="VD: Giảm giá ngày đôi..." name="description"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Ngày bắt đầu</label>
                            <input type="datetime-local" class="form-control" name="start_date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày kết thúc</label>
                            <input type="datetime-local" class="form-control" name="end_date">
                        </div>
                    </div>
                    <hr> 
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Loại giảm giá</label>
                            <select class="form-select" name="type" id="discountType">
                                <option value="fixed">Trừ tiền mặt (VNĐ)</option>
                                <option value="percent">Trừ theo %</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giá trị giảm</label>
                            <input type="number" class="form-control" placeholder="VD: 50000 hoặc 10" name="value" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Đơn tối thiểu (VNĐ)</label>
                            <input type="number" class="form-control" value="0" name="min_order">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giảm tối đa (VNĐ)</label>
                            <input type="number" class="form-control" name="max_discount" placeholder="Bỏ trống nếu không giới hạn">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="status">
                            <option value="1" selected>Kích hoạt ngay</option>
                            <option value="0">Tạm ẩn (Chưa kích hoạt)</option>
                        </select>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2"><i class="fas fa-save me-2"></i> Lưu Mã Giảm Giá</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editVoucherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title fw-bold text-white">Cập nhật Voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeEditModal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditVoucher">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="mb-3">
                        <label>Mã Code (Chỉ đọc)</label>
                        <input type="text" class="form-control" id="edit_code" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Tổng giới hạn lượt dùng</label>
                        <input type="number" class="form-control" id="edit_usage_limit" name="usage_limit">
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label>Ngày bắt đầu</label>
                            <input type="datetime-local" class="form-control" id="edit_start_date" name="start_date">
                        </div>
                        <div class="col-6">
                            <label>Ngày kết thúc</label>
                            <input type="datetime-local" class="form-control" id="edit_end_date" name="end_date">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label>Giá trị giảm (Chỉ đọc)</label>
                            <input type="number" class="form-control" id="edit_value" name="value" readonly>
                        </div>
                        <div class="col-4">
                            <label>Đơn tối thiểu</label>
                            <input type="number" class="form-control" id="edit_min_order" name="min_order">
                        </div>
                        <div class="col-4">
                            <label>Giảm tối đa</label>
                            <input type="number" class="form-control" id="edit_max_discount" name="max_discount">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Trạng thái</label>
                        <select class="form-select" id="edit_status" name="status">
                            <option value="1">Đang hoạt động (Active)</option>
                            <option value="0">Tạm ẩn</option>
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-warning fw-bold">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importExcelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title fw-bold text-white"><i class="fas fa-file-upload me-2"></i>Nhập Vouchers từ Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeImportModal"></button>
            </div>
            <form id="formImportExcel">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn file Excel (.xlsx)</label>
                        <input type="file" name="fileImport" class="form-control" accept=".xlsx, .xls" required>
                        <small class="text-muted">* Sử dụng định dạng giống file Xuất Excel.<br>* Hệ thống sẽ bỏ qua dòng tiêu đề.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary-custom fw-bold"><i class="fas fa-upload me-1"></i> Tiến hành Nhập</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/web_qlsp/Public/css/loading.css">
<link rel="stylesheet" href="/web_qlsp/Public/Css/vouchers.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const API_BASE = '/web_qlsp/vouchers';
    const formatMoney = (amount) => new Intl.NumberFormat('vi-VN').format(amount || 0) + 'đ';
    let globalVouchers = [];

    // 1. TẢI VÀ VẼ DANH SÁCH
    function loadData() {
        document.getElementById('loading-skeleton').style.display = 'table-row-group';
        document.getElementById('actual-content').style.display = 'none';

        const q = document.getElementById('txtSearch').value;
        const filter = document.getElementById('statusFilter').value;

        fetch(`${API_BASE}/api_get_data?q=${encodeURIComponent(q)}&filter=${filter}`)
            .then(res => res.json())
            .then(res => {
                const tbody = document.getElementById('actual-content');
                let html = '';

                if (res.success && res.data.length > 0) {
                    globalVouchers = res.data;
                    
                    res.data.forEach(v => {
                        const con_lai = v.usage_limit - v.used_count;
                        
                        let statusBadge = '', statusText = '', rowOpacity = '';
                        switch(v.real_status) {
                            case 'hidden': statusText = 'Đang ẩn'; statusBadge = 'bg-secondary'; rowOpacity = 'opacity-50'; break;
                            case 'empty': statusText = 'Hết lượt'; statusBadge = 'bg-dark'; rowOpacity = 'opacity-75'; break;
                            case 'expired': statusText = 'Đã kết thúc'; statusBadge = 'bg-danger'; rowOpacity = 'opacity-50'; break;
                            case 'upcoming': statusText = 'Sắp diễn ra'; statusBadge = 'bg-warning text-dark'; break;
                            default: statusText = 'Đang chạy'; statusBadge = 'bg-success'; break;
                        }

                        // Format Date
                        const formatDt = (ts) => {
                            const d = new Date(ts * 1000);
                            return `${d.getDate().toString().padStart(2,'0')}/${(d.getMonth()+1).toString().padStart(2,'0')}/${d.getFullYear().toString().slice(-2)} ${d.getHours().toString().padStart(2,'0')}:${d.getMinutes().toString().padStart(2,'0')}`;
                        };
                        const sDt = formatDt(v.start_timestamp);
                        const eDt = formatDt(v.end_timestamp);

                        html += `
                            <tr class="${rowOpacity}">
                                <td class="ps-4"><span class="badge bg-dark fs-6">${v.code}</span></td>
                                <td class="text-center"><span class="badge rounded-pill ${statusBadge}">${statusText}</span></td>
                                <td>
                                    <div class="d-flex flex-column text-muted" style="font-size: 13px;">
                                        <span><i class="fas fa-play me-1 text-success"></i> ${sDt}</span>
                                        <span><i class="fas fa-stop me-1 text-danger"></i> ${eDt}</span>
                                    </div>
                                </td>
                                <td>${v.discount_type == 'percent' ? 'Theo %' : 'Tiền mặt'}</td>
                                <td class="fw-bold text-primary">${v.discount_type == 'percent' ? v.discount_value + '%' : formatMoney(v.discount_value)}</td>
                                <td><small>Min: <b>${formatMoney(v.min_order_value)}</b><br>${v.max_discount_amount > 0 ? 'Max: '+formatMoney(v.max_discount_amount) : ''}</small></td>
                                <td class="text-center">${v.used_count}</td>
                                <td class="text-center"><span class="badge ${con_lai > 0 ? 'bg-info' : 'bg-secondary'}">${con_lai}</span></td>
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-sm btn-outline-primary border-0 me-1" onclick="openEditModal(${v.id})" title="Sửa"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger border-0" onclick="deleteVoucher(${v.id})" title="Xóa"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = `<tr><td colspan="9" class="text-center py-4 text-muted">Chưa có mã giảm giá nào.</td></tr>`;
                }

                tbody.innerHTML = html;
                document.getElementById('loading-skeleton').style.display = 'none';
                document.getElementById('actual-content').style.display = 'table-row-group';
            })
            .catch(err => console.error('Lỗi kết nối', err));
    }

    document.addEventListener("DOMContentLoaded", () => loadData());

    // 2. TÌM KIẾM & XUẤT EXCEL
    document.getElementById('formSearch').addEventListener('submit', function(e) {
        e.preventDefault();
        loadData();
    });

    function resetSearch() {
        document.getElementById('txtSearch').value = '';
        document.getElementById('statusFilter').value = 'all';
        loadData();
    }

    function exportExcel() {
        const q = encodeURIComponent(document.getElementById('txtSearch').value);
        window.location.href = `${API_BASE}/export_excel?q=${q}`;
    }

    // 3. THÊM VOUCHER
    document.getElementById('formAddVoucher').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(`${API_BASE}/api_add`, { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Thành công', text: data.message, timer: 1500 });
                    this.reset();
                    document.getElementById('closeAddModal').click();
                    loadData();
                } else {
                    Swal.fire('Lỗi', data.message, 'warning');
                }
            });
    });

    // 4. MỞ MODAL SỬA & CẬP NHẬT
    function openEditModal(id) {
        const v = globalVouchers.find(item => item.id == id);
        if(!v) return;

        // Chuyển đổi định dạng ngày cho thẻ input type="datetime-local" (YYYY-MM-DDTHH:mm)
        const formatForInput = (dateStr) => {
            if (!dateStr) return '';
            return dateStr.replace(' ', 'T'); 
        };

        document.getElementById('edit_id').value = v.id;
        document.getElementById('edit_code').value = v.code;
        document.getElementById('edit_usage_limit').value = v.usage_limit;
        document.getElementById('edit_start_date').value = formatForInput(v.start_date);
        document.getElementById('edit_end_date').value = formatForInput(v.end_date);
        document.getElementById('edit_value').value = v.discount_value;
        document.getElementById('edit_min_order').value = v.min_order_value;
        document.getElementById('edit_max_discount').value = v.max_discount_amount > 0 ? v.max_discount_amount : '';
        document.getElementById('edit_status').value = v.status;

        new bootstrap.Modal(document.getElementById('editVoucherModal')).show();
    }

    document.getElementById('formEditVoucher').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(`${API_BASE}/api_update`, { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Đã cập nhật', timer: 1500, showConfirmButton: false });
                    document.getElementById('closeEditModal').click();
                    loadData();
                } else {
                    Swal.fire('Lỗi', data.message, 'warning');
                }
            });
    });

    // 5. XÓA VOUCHER
    function deleteVoucher(id) {
        Swal.fire({
            title: 'Xóa mã giảm giá?', text: "Hành động không thể hoàn tác!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${API_BASE}/api_delete/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Đã xóa', timer: 1500, showConfirmButton: false });
                            loadData();
                        } else {
                            Swal.fire('Lỗi', data.message, 'error');
                        }
                    });
            }
        });
    }

    // 6. IMPORT EXCEL
    document.getElementById('formImportExcel').addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({ title: 'Đang xử lý...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        fetch(`${API_BASE}/api_import`, { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Hoàn tất', html: data.message });
                    this.reset();
                    document.getElementById('closeImportModal').click();
                    loadData();
                } else {
                    Swal.fire('Lỗi Import', data.message, 'error');
                }
            });
    });
</script>