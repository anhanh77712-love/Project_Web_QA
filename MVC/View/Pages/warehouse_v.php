<div class="d-flex justify-content-between align-items-center mb-4">
	<div>
		<h3 class="fw-bold mb-1">Quản lý Kho hàng</h3>
		<p class="text-muted small mb-0">Theo dõi tồn kho, cảnh báo thiếu hàng và điều chỉnh nhanh</p>
	</div>
</div>

<div class="row g-3 mb-3">
	<div class="col-md-3">
		<div class="card border-0 shadow-sm p-3">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<p class="text-muted small mb-1">Tổng tồn kho</p>
					<h5 class="fw-bold mb-0" id="lbl_sum_stock">...</h5>
				</div>
				<span class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary"><i class="fas fa-boxes"></i></span>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card border-0 shadow-sm p-3">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<p class="text-muted small mb-1">Sắp hết hàng</p>
					<h5 class="fw-bold mb-0 text-warning" id="lbl_low_stock">...</h5>
				</div>
				<span class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning"><i class="fas fa-exclamation-triangle"></i></span>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card border-0 shadow-sm p-3">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<p class="text-muted small mb-1">Hết hàng</p>
					<h5 class="fw-bold mb-0 text-danger" id="lbl_out_stock">...</h5>
				</div>
				<span class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger"><i class="fas fa-times-circle"></i></span>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card border-0 shadow-sm p-3">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<p class="text-muted small mb-1">Giá trị tồn (ước tính)</p>
					<h5 class="fw-bold mb-0 text-success" id="lbl_stock_value">...</h5>
				</div>
				<span class="bg-success bg-opacity-10 p-3 rounded-circle text-success"><i class="fas fa-coins"></i></span>
			</div>
		</div>
	</div>
</div>

<form id="formSearch" class="d-flex align-items-center gap-2 mb-3">
	<div class="input-group" style="max-width: 360px;">
		<span class="input-group-text"><i class="fas fa-search"></i></span>
		<input type="text" id="q" class="form-control" placeholder="Tìm theo tên sản phẩm...">
	</div>
	<select id="category" class="form-select" style="max-width: 220px;">
		<option value="">Tất cả danh mục</option>
		</select>
	<select id="stockStatus" class="form-select" style="max-width: 220px;">
		<option value="">Tất cả trạng thái</option>
		<option value="ok">Bình thường</option>
		<option value="low">Sắp hết</option>
		<option value="out">Hết hàng</option>
	</select>
    
	<button class="btn btn-dark" type="submit"><i class="fas fa-filter me-2"></i>Lọc</button>
	<button class="btn btn-outline-secondary" type="button" onclick="resetFilters()"><i class="fas fa-undo me-2"></i>Đặt lại</button>

    <div class="ms-auto d-flex gap-2">
    </div>
</form>

<div class="card border-0 shadow-sm">
	<div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light text-nowrap">
                    <tr>
                        <th>Ảnh</th>
                        <th>Sản phẩm</th>
                        <th>Biến thể</th>
                        <th>Giá nhập</th>
                        <th>Tồn kho</th>
                        <th>Sẵn có</th>
                        <th>Ngưỡng</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody id="loading-skeleton">
                    <?php for ($i=0;$i<6;$i++): ?>
                    <tr>
                        <td><div class="skeleton" style="width:60px;height:60px;border-radius:8px;"></div></td>
                        <td><div class="skeleton" style="width:140px;height:16px;margin-bottom:6px;"></div><div class="skeleton" style="width:90px;height:12px;"></div></td>
                        <td><div class="skeleton" style="width:90px;height:14px;"></div></td>
                        <td><div class="skeleton" style="width:80px;height:14px;"></div></td>
                        <td><div class="skeleton" style="width:50px;height:18px;"></div></td>
                        <td><div class="skeleton" style="width:60px;height:18px;"></div></td>
                        <td><div class="skeleton" style="width:80px;height:14px;"></div></td>
                        <td class="text-end"><div class="skeleton ms-auto" style="width:110px;height:32px;border-radius:6px;"></div></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
                <tbody id="actual-content" style="display:none;"></tbody>
            </table>
        </div>
	</div>
</div>

<div class="modal fade" id="adjustModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
            <form id="formAdjustStock">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit text-primary me-2"></i>Điều chỉnh tồn kho</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <input type="hidden" id="adj_product_id" name="product_id">
                    <input type="hidden" id="adj_variant_id" name="variant_id">
                    
                    <div class="mb-3 text-center border-bottom pb-3">
                        <strong id="adj_title" class="fs-5 text-primary"></strong>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tồn hiện tại</label>
                        <input type="number" id="adj_current" class="form-control fw-bold text-muted" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-danger">Thay đổi số lượng <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">±</span>
                            <input type="number" id="adj_delta" name="delta" class="form-control fw-bold" value="0" step="1" required>
                        </div>
                        <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle"></i> Nhập số <b>dương</b> để cộng thêm kho, số <b>âm</b> để trừ đi.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4"><i class="fas fa-save me-1"></i> Lưu thay đổi</button>
                </div>
            </form>
		</div>
	</div>
</div>



<link rel="stylesheet" href="/web_qlsp/Public/css/loading.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const API_BASE = '/web_qlsp/warehouse';

    const formatNum = (num) => new Intl.NumberFormat('vi-VN').format(num || 0);
    const formatMoney = (amount) => formatNum(amount) + 'đ';
    let isCategoryLoaded = false; 

    // 1. LOAD DỮ LIỆU
    function loadData() {
        document.getElementById('loading-skeleton').style.display = 'table-row-group';
        document.getElementById('actual-content').style.display = 'none';

        const q = document.getElementById('q').value;
        const cat = document.getElementById('category').value;
        const st = document.getElementById('stockStatus').value;

        fetch(`${API_BASE}/api_get_data?q=${encodeURIComponent(q)}&category=${cat}&status=${st}`)
            .then(res => {
                if(!res.ok) throw new Error("Máy chủ trả về lỗi " + res.status);
                return res.json();
            })
            .then(res => {
                if (res.success) {
                    const data = res.data;

                    // Đổ 4 thẻ thống kê
                    document.getElementById('lbl_sum_stock').textContent = formatNum(data.summaries.sum_stock);
                    document.getElementById('lbl_low_stock').textContent = formatNum(data.summaries.low_stock_count);
                    document.getElementById('lbl_out_stock').textContent = formatNum(data.summaries.out_of_stock_count);
                    document.getElementById('lbl_stock_value').textContent = formatMoney(data.summaries.stock_value);

                    // Đổ Select Category
                    if (!isCategoryLoaded) {
                        let catHtml = '<option value="">Tất cả danh mục</option>';
                        if(data.categories) {
                            data.categories.forEach(c => catHtml += `<option value="${c.id}">${c.name}</option>`);
                        }
                        document.getElementById('category').innerHTML = catHtml;
                        isCategoryLoaded = true;
                    }

                    // Đổ Bảng dữ liệu
                    const tbody = document.getElementById('actual-content');
                    let html = '';

                    if (data.items && data.items.length > 0) {
                        data.items.forEach(it => {
                            let badge = '';
                            if (it.available == 0) badge = '<span class="badge bg-danger">Hết hàng</span>';
                            else if (it.is_low) badge = '<span class="badge bg-warning text-dark">Sắp hết</span>';
                            else badge = '<span class="badge bg-success">OK</span>';

                            const variantStr = [it.color, it.size].filter(Boolean).join(' • ');
                            const thumb = it.thumbnail ? it.thumbnail : 'default.png';

                            html += `
                                <tr>
                                    <td><img src="/web_qlsp/Public/Picture/${thumb}" width="60" height="60" style="object-fit:cover;border-radius:8px;border:1px solid #eee;" onerror="this.src='https://via.placeholder.com/60'"></td>
                                    <td>
                                        <div class="fw-bold">${it.product_name || 'Không tên'}</div>
                                        <small class="text-muted"><i class="fas fa-folder"></i> ${it.category_name || '--'}</small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">${variantStr || 'Mặc định'}</span></td>
                                    <td>${formatMoney(it.cost_price)}</td>
                                    <td class="fw-bold">${formatNum(it.stock_quantity)}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">${formatNum(it.available)}</span>
                                        <div class="mt-1">${badge}</div>
                                    </td>
                                    <td>${formatNum(it.threshold || '-')}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary fw-bold" onclick="openAdjustModal(${it.product_id}, ${it.variant_id}, '${it.product_name}', '${it.color || ''}', '${it.size || ''}', ${it.stock_quantity})">
                                            <i class="fas fa-edit"></i> Điều chỉnh
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = `<tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-boxes fa-3x mb-3 d-block opacity-25"></i> Không tìm thấy sản phẩm nào trong kho.</td></tr>`;
                    }

                    tbody.innerHTML = html;
                    document.getElementById('loading-skeleton').style.display = 'none';
                    document.getElementById('actual-content').style.display = 'table-row-group';
                } else {
                    Swal.fire('Lỗi API', res.message || 'Không thể lấy dữ liệu', 'error');
                }
            })
            .catch(err => {
                console.error('Lỗi JS:', err);
                Swal.fire('Lỗi kết nối', 'Đã xảy ra lỗi khi tải dữ liệu từ máy chủ. Vui lòng mở F12 Console để xem chi tiết.', 'error');
                document.getElementById('loading-skeleton').style.display = 'none';
                document.getElementById('actual-content').innerHTML = `<tr><td colspan="8" class="text-center py-5 text-danger"><i class="fas fa-exclamation-triangle fa-3x mb-3 d-block"></i> Máy chủ trả về dữ liệu không hợp lệ.</td></tr>`;
                document.getElementById('actual-content').style.display = 'table-row-group';
            });
    }

    document.addEventListener("DOMContentLoaded", () => loadData());

    // 2. TÌM KIẾM & LỌC
    document.getElementById('formSearch').addEventListener('submit', function(e) {
        e.preventDefault(); loadData();
    });

    function resetFilters() {
        document.getElementById('q').value = '';
        document.getElementById('category').value = '';
        document.getElementById('stockStatus').value = '';
        loadData();
    }

    // 3. ĐIỀU CHỈNH TỒN KHO THỦ CÔNG
    let adjustModalObj;
    function openAdjustModal(productId, variantId, name, color, size, current) {
        document.getElementById('adj_product_id').value = productId;
        document.getElementById('adj_variant_id').value = variantId || '';
        document.getElementById('adj_title').textContent = name + (color ? ` • ${color}` : '') + (size ? ` • ${size}` : '');
        document.getElementById('adj_current').value = current;
        document.getElementById('adj_delta').value = 0;
        
        if(!adjustModalObj) adjustModalObj = new bootstrap.Modal(document.getElementById('adjustModal'));
        adjustModalObj.show();
    }

    document.getElementById('formAdjustStock').addEventListener('submit', function(e) {
        e.preventDefault();
        const delta = parseInt(document.getElementById('adj_delta').value);
        if (delta === 0) return Swal.fire('Chú ý', 'Vui lòng nhập số lượng thay đổi khác 0', 'warning');

        Swal.fire({ title: 'Đang xử lý...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch(`${API_BASE}/api_adjust_stock`, { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Hoàn tất', text: data.message, timer: 1500 });
                    adjustModalObj.hide();
                    loadData(); // Tải lại bảng kho ngầm
                } else {
                    Swal.fire('Lỗi', data.message, 'error');
                }
            });
    });


</script>