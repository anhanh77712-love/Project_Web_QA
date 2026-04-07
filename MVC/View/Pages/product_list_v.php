<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Quản lý sản phẩm</h3>
</div>

<div class="toolbar-container d-flex align-items-center">
    <form id="formSearch" class="d-flex align-items-center w-100">
        <div class="search-wrapper me-auto">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="txtSearch" class="form-control form-search" placeholder="Tìm kiếm sản phẩm...">
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-dark-blue"><i class="fas fa-search"></i> Tìm</button>
            <button type="button" class="btn btn-light-gray" onclick="resetSearch()"><i class="fas fa-undo-alt"></i> Làm mới</button>
            <button type="button" class="btn btn-green" onclick="exportExcel()"><i class="fas fa-file-excel"></i> Xuất Excel</button>
            <button type="button" class="btn btn-blue" data-bs-toggle="modal" data-bs-target="#importModal"><i class="fas fa-file-import"></i> Nhập Excel</button>
            <button type="button" class="btn btn-dark-blue" onclick="window.location.href='/web_qlsp/product_add'"><i class="fas fa-plus"></i> Thêm Sản Phẩm</button>
        </div>
    </form>
</div>


<div class="card-table">
    <table class="table-modern">
        <thead>
            <tr>
                <th width="6%">ID</th>
                <th width="8%">Ảnh</th>
                <th width="18%">Tên sản phẩm</th>
                <th width="10%">Giá bán</th>
                <th width="12%">Danh mục</th>
                <th width="8%">Lượt xem</th>
                <th width="12%">Trạng thái</th>
                <th width="12%">Màu sắc</th>
                <th width="10%">Kích thước</th>
                <th width="8%" class="text-end">Hành động</th>
            </tr>
        </thead>
        <tbody id="loading-skeleton">
            <?php for ($i = 0; $i < 5; $i++): ?>
            <tr>
                <td><div class="skeleton" style="width:20px; height:15px;"></div></td>
                <td><div class="skeleton" style="width:60px; height:60px; border-radius: 8px;"></div></td>
                <td>
                    <div class="skeleton" style="width:90%; height:15px; margin-bottom: 5px;"></div>
                    <div class="skeleton" style="width:50%; height:10px;"></div>
                </td>
                <td><div class="skeleton" style="width:80px; height:15px;"></div></td>
                <td><div class="skeleton" style="width:100px; height:25px; border-radius: 4px;"></div></td>
                <td><div class="skeleton" style="width:40px; height:15px;"></div></td>
                <td><div class="skeleton" style="width:80px; height:25px; border-radius: 4px;"></div></td>
                <td><div class="skeleton" style="width:60px; height:20px; border-radius: 4px;"></div></td>
                <td><div class="skeleton" style="width:30px; height:20px; border-radius: 4px;"></div></td>
                <td class="text-end">
                    <div class="d-flex justify-content-end gap-2">
                        <div class="skeleton" style="width:20px; height:20px;"></div>
                        <div class="skeleton" style="width:20px; height:20px;"></div>
                    </div>
                </td>
            </tr>
            <?php endfor; ?>
        </tbody>

        <tbody id="actual-content" style="display: none;"></tbody>
    </table>
</div>


<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-file-upload"></i> Nhập dữ liệu Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeImportModal"></button>
            </div>

            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#product-import" type="button"><i class="fas fa-box"></i> Import Sản Phẩm</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#variant-import" type="button"><i class="fas fa-palette"></i> Import Biến Thể</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="product-import" role="tabpanel">
                        <form id="formImportProduct">
                            <input type="hidden" name="importType" value="product">
                            <div class="alert alert-info">
                                <small><i class="fas fa-info-circle"></i> Chưa có file mẫu? <a href="/web_qlsp/product_list/downloadProductTemplate" class="fw-bold">Tải file mẫu Sản Phẩm</a></small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Chọn file Excel (.xlsx, .xls)</label>
                                <input class="form-control" type="file" name="txtfile" accept=".xlsx, .xls" required>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-cloud-upload-alt"></i> Import Sản Phẩm</button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="variant-import" role="tabpanel">
                        <form id="formImportVariant">
                            <input type="hidden" name="importType" value="variant">
                            <div class="alert alert-info">
                                <small><i class="fas fa-info-circle"></i> Chưa có file mẫu? <a href="/web_qlsp/product_list/downloadVariantTemplate" class="fw-bold">Tải file mẫu Biến Thể</a></small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Chọn file Excel (.xlsx, .xls)</label>
                                <input class="form-control" type="file" name="txtfile" accept=".xlsx, .xls" required>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                                <button type="submit" class="btn btn-success"><i class="fas fa-cloud-upload-alt"></i> Import Biến Thể</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/web_qlsp/Public/css/loading.css">
<link rel="stylesheet" href="/web_qlsp/Public/css/product_list.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const API_BASE = '/web_qlsp/product_list';

    // Helpers UI
    const formatMoney = (amount) => new Intl.NumberFormat('vi-VN').format(amount || 0) + 'đ';
    const formatNumber = (num) => new Intl.NumberFormat('vi-VN').format(num || 0);

    function getColorHex(color) {
        const colors = { 'Đen': '#000000', 'Trắng': '#FFFFFF', 'Đỏ': '#DC3545', 'Xanh': '#0D6EFD', 'Vàng': '#FFC107', 'Hồng': '#E83E8C', 'Xám': '#6C757D', 'Nâu': '#8B4513', 'Tím': '#6F42C1', 'Cam': '#FD7E14' };
        return colors[color] || '#6C757D';
    }
    
    function isWhiteColor(color) {
        const normalized = color ? color.trim().toLowerCase() : '';
        return ['trắng', 'trang', 'white'].includes(normalized);
    }

    // 1. TẢI VÀ VẼ BẢNG SẢN PHẨM (Dùng rowspan lồng biến thể)
    function loadData(query = '') {
        document.getElementById('loading-skeleton').style.display = 'table-row-group';
        document.getElementById('actual-content').style.display = 'none';

        fetch(`${API_BASE}/api_get_data?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(res => {
                const tbody = document.getElementById('actual-content');
                let html = '';

                if (res.success && res.data.length > 0) {
                    res.data.forEach(p => {
                        const variants = p.variants || [];
                        const vCount = variants.length > 0 ? variants.length : 1;

                        // Nếu không có variant, vòng lặp này sẽ chạy 1 lần với v = undefined
                        for (let i = 0; i < vCount; i++) {
                            const v = variants[i];
                            html += `<tr class="product-row">`;

                            // Các cột chung của Sản Phẩm (Chỉ vẽ ở dòng đầu tiên của nhóm variant)
                            if (i === 0) {
                                html += `<td rowspan="${vCount}" class="text-muted fw-bold">#${p.id}</td>`;
                                html += `<td rowspan="${vCount}"><img src="/web_qlsp/Public/Picture/${p.thumbnail}" width="60" height="60" style="object-fit: cover; border-radius: 8px; border: 1px solid #eee;" onerror="this.src='https://via.placeholder.com/60'"></td>`;
                                html += `<td rowspan="${vCount}"><div class="fw-bold text-dark">${p.name}</div><small class="text-muted"><i class="fas fa-link"></i> ${p.slug}</small></td>`;
                                html += `<td rowspan="${vCount}" class="fw-bold text-primary">${formatMoney(p.base_price)}</td>`;
                                html += `<td rowspan="${vCount}"><span class="badge bg-light text-dark border"><i class="fas fa-folder"></i> ${p.category_name || ''}</span></td>`;
                                html += `<td rowspan="${vCount}"><div class="d-flex align-items-center text-muted"><i class="fas fa-eye me-1"></i> ${formatNumber(p.views)}</div></td>`;
                                html += `<td rowspan="${vCount}">` + (p.is_sale == 1 ? `<span class="badge bg-danger"><i class="fas fa-tag"></i> Sale</span>` : `<span class="badge bg-success"><i class="fas fa-check"></i> Bình thường</span>`) + `</td>`;
                            }

                            // Cột riêng của Variant (Vẽ trên mọi dòng)
                            if (v) {
                                const bg = getColorHex(v.color);
                                const txt = isWhiteColor(v.color) ? '#000000' : '#FFFFFF';
                                html += `<td><span class="badge" style="background: ${bg}; color: ${txt}; border: 1px solid #e0e0e0;">${v.color}</span></td>`;
                                html += `<td><span class="badge bg-secondary">${v.size}</span></td>`;
                            } else {
                                html += `<td><span class="text-muted">-</span></td><td><span class="text-muted">-</span></td>`;
                            }

                            // Cột Hành động (Chỉ vẽ ở dòng đầu tiên)
                            if (i === 0) {
                                html += `<td rowspan="${vCount}" class="text-end">
                                    <a href="/web_qlsp/product_list/sua/${p.id}" class="btn-icon text-primary me-1" title="Sửa"><i class="fas fa-edit" style="width:18px;"></i></a>
                                    <button class="btn-icon text-danger border-0 bg-transparent p-0" onclick="deleteProduct(${p.id})" title="Xóa"><i class="fas fa-trash" style="width:18px;"></i></button>
                                </td>`;
                            }

                            html += `</tr>`;
                        }
                    });
                } else {
                    html = `<tr><td colspan="10" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3 d-block"></i>Không tìm thấy sản phẩm nào.</td></tr>`;
                }

                tbody.innerHTML = html;
                document.getElementById('loading-skeleton').style.display = 'none';
                document.getElementById('actual-content').style.display = 'table-row-group';
            })
            .catch(err => console.error('Lỗi tải dữ liệu', err));
    }

    document.addEventListener("DOMContentLoaded", () => loadData());

    // 2. TÌM KIẾM
    document.getElementById('formSearch').addEventListener('submit', function(e) {
        e.preventDefault();
        loadData(document.getElementById('txtSearch').value);
    });

    function resetSearch() {
        document.getElementById('txtSearch').value = '';
        loadData();
    }

    // 3. XÓA SẢN PHẨM
    function deleteProduct(id) {
        Swal.fire({
            title: 'Xóa sản phẩm này?', text: "Dữ liệu không thể khôi phục!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Xóa ngay', cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${API_BASE}/api_delete/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Đã xóa!', timer: 1500, showConfirmButton: false });
                            loadData(document.getElementById('txtSearch').value);
                        } else {
                            Swal.fire('Lỗi', data.message, 'error');
                        }
                    });
            }
        });
    }

    // 4. XUẤT EXCEL
    function exportExcel() {
        const query = encodeURIComponent(document.getElementById('txtSearch').value);
        window.location.href = `${API_BASE}/export?q=${query}`;
    }

    // 5. XỬ LÝ IMPORT EXCEL CHUNG
    function handleImport(formElement) {
        Swal.fire({ title: 'Đang đọc dữ liệu...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        fetch(`${API_BASE}/api_import_excel`, { method: 'POST', body: new FormData(formElement) })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Hoàn tất', html: data.message });
                    formElement.reset();
                    document.getElementById('closeImportModal').click();
                    loadData(document.getElementById('txtSearch').value);
                } else {
                    Swal.fire('Lỗi Import', data.message, 'error');
                }
            })
            .catch(() => Swal.fire('Lỗi máy chủ', 'Không thể kết nối API', 'error'));
    }

    document.getElementById('formImportProduct').addEventListener('submit', function(e) {
        e.preventDefault(); handleImport(this);
    });

    document.getElementById('formImportVariant').addEventListener('submit', function(e) {
        e.preventDefault(); handleImport(this);
    });
</script>