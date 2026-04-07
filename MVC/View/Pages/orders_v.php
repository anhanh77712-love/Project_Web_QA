<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Danh sách Đơn hàng</h4>
    <div class="d-flex gap-2 align-items-center">
        <input type="date" class="form-control shadow-sm" id="fromDate" style="width: 170px;"/>
        <span class="text-muted fw-bold">-</span>
        <input type="date" class="form-control shadow-sm" id="toDate" style="width: 170px;"/>
        
        <select class="form-select shadow-sm" id="statusFilter" onchange="applyFilters()" style="width: 200px;">
            <option value="all">Tất cả trạng thái</option>
            <option value="pending">Chờ xử lý</option>
            <option value="confirmed">Đã xác nhận</option>
            <option value="shipping">Đang giao hàng</option>
            <option value="completed">Hoàn thành</option>
            <option value="cancelled">Đã hủy</option>
        </select>
        
        <button class="btn btn-dark shadow-sm" onclick="applyFilters()">
            <i class="fas fa-filter me-1"></i> Lọc
        </button>
    </div>
</div>

<style>
    .product-list .border-bottom:last-child { border-bottom: none !important; }
    .skeleton { background: #e0e0e0; animation: pulse 1.5s infinite; border-radius: 4px; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
    .table-hover tbody tr:hover { background-color: #f8f9fa; }
    .modal-body::-webkit-scrollbar { width: 8px; }
    .modal-body::-webkit-scrollbar-track { background: #f1f1f1; }
    .modal-body::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
    .modal-body::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
</style>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4">Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th class="text-end pe-4">Hành động</th>
                    </tr>
                </thead>
                
                <tbody id="loading-skeleton"> 
                    <?php for ($i = 0; $i < 6; $i++): ?> 
                    <tr>
                        <td class="ps-4"><div class="skeleton" style="width: 40px; height: 20px;"></div></td>
                        <td>
                            <div class="skeleton mb-1" style="width: 140px; height: 18px;"></div>
                            <div class="skeleton" style="width: 100px; height: 14px;"></div>
                        </td>
                        <td><div class="skeleton" style="width: 120px; height: 18px;"></div></td>
                        <td><div class="skeleton" style="width: 90px; height: 18px;"></div></td>
                        <td><div class="skeleton" style="width: 110px; height: 24px; border-radius: 50px;"></div></td>
                        <td class="text-end pe-4"><div class="skeleton ms-auto" style="width: 90px; height: 32px; border-radius: 5px;"></div></td>
                    </tr> 
                    <?php endfor; ?> 
                </tbody>

                <tbody id="actual-content" style="display: none;">
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header py-3 bg-white border-bottom-0">
                <h5 class="modal-title fw-bold text-dark">
                    Đơn hàng <span class="text-primary">#<span id="order_id"></span></span>
                </h5>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-outline-danger btn-sm fw-bold" onclick="deleteOrder(currentOrderId)">
                        <i class="fas fa-trash me-1"></i> Xóa
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeDetailModal"></button>
                </div>
            </div>

            <div class="modal-body p-4" style="background-color: #f8f9fa;">
                <div class="row g-4">
                    <div class="col-lg-4 d-flex flex-column gap-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center p-4">
                                <div class="mb-3 d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 70px; height: 70px;">
                                    <i class="fas fa-user fs-2"></i>
                                </div>
                                <h5 class="fw-bold mb-1" id="customer_name">Loading...</h5>
                                <p class="text-muted small mb-3">Khách hàng</p>

                                <div class="text-start bg-light p-3 rounded-3 mb-2 d-flex align-items-center">
                                    <i class="fas fa-phone-alt text-success me-3 fs-5"></i>
                                    <div>
                                        <small class="text-muted d-block" style="font-size:11px;">SỐ ĐIỆN THOẠI</small>
                                        <span class="fw-bold text-dark" id="customer_phone">...</span>
                                    </div>
                                </div>

                                <div class="text-start bg-light p-3 rounded-3 d-flex align-items-center">
                                    <i class="fas fa-envelope text-danger me-3 fs-5"></i>
                                    <div class="overflow-hidden">
                                        <small class="text-muted d-block" style="font-size:11px;">EMAIL</small>
                                        <span class="fw-bold text-dark text-truncate d-block" id="customer_email">...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size: 12px; letter-spacing: 0.5px;">Cập nhật trạng thái</h6>
                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Hiện tại:</span>
                                    <div id="current_status_badge"></div>
                                </div>
                                <div class="input-group">
                                    <select class="form-select border-primary fw-bold text-dark" id="new_status">
                                        <option value="">-- Chọn hành động --</option>
                                        <option value="pending">⏳ Chờ xử lý</option>
                                        <option value="confirmed">✅ Đã xác nhận</option>
                                        <option value="shipping">🚚 Đang giao hàng</option>
                                        <option value="completed">🎉 Hoàn thành</option>
                                        <option value="cancelled">❌ Hủy đơn</option>
                                    </select>
                                    <button id="btnUpdateStatus" class="btn btn-primary fw-bold" onclick="updateStatusFromModal()">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 d-flex flex-column gap-3">
                         <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white fw-bold py-3">
                                <i class="fas fa-map-marker-alt text-danger me-2"></i>Địa chỉ nhận hàng
                            </div>
                            <div class="card-body bg-white">
                                <p id="shipping_address" class="mb-0 text-secondary" style="line-height: 1.6;"></p>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm flex-grow-1">
                            <div class="card-header bg-white fw-bold py-3">
                                <i class="fas fa-receipt text-success me-2"></i>Chi tiết thanh toán
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Tạm tính:</span>
                                    <span class="fw-bold" id="subtotal">0đ</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Phí vận chuyển:</span>
                                    <span class="fw-bold" id="shipping_fee">0đ</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-danger">
                                    <span class="fw-bold">Giảm giá:</span>
                                    <span class="fw-bold" id="discount_amount">-0đ</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3 text-primary">
                                    <span class="small"><i class="fas fa-ticket-alt me-1"></i>Voucher:</span>
                                    <span class="fw-bold" id="voucher_code">--</span>
                                </div>
                                <div class="border-top pt-3 d-flex justify-content-between align-items-center bg-light p-2 rounded">
                                    <span class="fw-bold text-uppercase small">Tổng thanh toán</span>
                                    <span class="text-danger fw-bold fs-5" id="total_money">0đ</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white fw-bold py-3 ps-4 border-bottom">
                                <i class="fas fa-box text-warning me-2"></i>Sản phẩm đơn hàng
                            </div>
                            <div class="card-body p-0 d-flex flex-column">
                                <div id="order_products" class="flex-grow-1 overflow-auto" style="max-height: 400px;"></div>
                                <div class="p-3 bg-light border-top mt-auto">
                                    <small class="fw-bold text-muted text-uppercase d-block mb-1">
                                        <i class="fas fa-pen me-1"></i>Ghi chú khách hàng:
                                    </small>
                                    <div id="note" class="fst-italic text-dark small bg-white p-2 rounded border">Không có ghi chú.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const API_BASE = '/web_qlsp/orders';
    let currentOrderId = null;

    // --- Các hàm Format ---
    const formatMoney = (amount) => new Intl.NumberFormat('vi-VN').format(amount || 0) + 'đ';
    
    function formatDate(dateStr) {
        if (!dateStr) return '';
        // Date format từ DB: YYYY-MM-DD HH:mm:ss
        const p = dateStr.split(/[- :]/);
        if(p.length >= 5) return `${p[2]}/${p[1]}/${p[0]} ${p[3]}:${p[4]}`;
        return dateStr;
    }

    function getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge bg-warning text-dark border border-warning bg-opacity-25">⏳ Chờ xử lý</span>',
            'confirmed': '<span class="badge bg-info text-dark border border-info bg-opacity-25">✅ Đã xác nhận</span>',
            'shipping': '<span class="badge bg-primary border border-primary bg-opacity-75">🚚 Đang giao hàng</span>',
            'completed': '<span class="badge bg-success border border-success bg-opacity-75">🎉 Hoàn thành</span>',
            'cancelled': '<span class="badge bg-danger border border-danger bg-opacity-75">❌ Đã hủy</span>'
        };
        return badges[status] || '<span class="badge bg-secondary">' + status + '</span>';
    }

    // --- 1. TẢI DANH SÁCH ĐƠN HÀNG (AJAX) ---
    function loadData() {
        document.getElementById('loading-skeleton').style.display = 'table-row-group';
        document.getElementById('actual-content').style.display = 'none';

        const status = document.getElementById('statusFilter').value;
        const from = document.getElementById('fromDate').value;
        const to = document.getElementById('toDate').value;

        const params = new URLSearchParams();
        if (status !== 'all') params.append('status', status);
        if (from) params.append('from', from);
        if (to) params.append('to', to);

        fetch(`${API_BASE}/api_get_data?${params.toString()}`)
            .then(res => res.json())
            .then(res => {
                const tbody = document.getElementById('actual-content');
                let html = '';

                if (res.success && res.data.length > 0) {
                    res.data.forEach(order => {
                        html += `
                            <tr>
                                <td class="ps-4 fw-bold text-primary">#${order.id}</td>
                                <td>
                                    <div class="fw-bold text-dark">${order.customer_name}</div>
                                    <small class="text-muted"><i class="fas fa-phone-alt me-1" style="font-size:10px;"></i>${order.customer_phone}</small>
                                </td>
                                <td>${formatDate(order.created_at)}</td>
                                <td class="fw-bold text-danger">${formatMoney(order.total_money)}</td>
                                <td>${getStatusBadge(order.status)}</td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary fw-bold" onclick="viewOrderDetail(${order.id})">
                                        Chi tiết <i class="bi bi-arrow-right-circle ms-1"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = `<tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-box-open fs-1 mb-3 d-block opacity-25"></i>
                                    Không tìm thấy đơn hàng nào.
                                </td>
                            </tr>`;
                }

                tbody.innerHTML = html;
                document.getElementById('loading-skeleton').style.display = 'none';
                document.getElementById('actual-content').style.display = 'table-row-group';
            })
            .catch(err => console.error('Lỗi tải dữ liệu', err));
    }

    // Load lần đầu khi mở trang
    document.addEventListener("DOMContentLoaded", loadData);

    // Xử lý Lọc dữ liệu (Không reload trang)
    function applyFilters() {
        const from = document.getElementById('fromDate').value;
        const to = document.getElementById('toDate').value;
        if (from && to && new Date(from) > new Date(to)) {
            Swal.fire('Ngày không hợp lệ', 'Ngày bắt đầu phải nhỏ hơn ngày kết thúc', 'warning');
            return;
        }
        loadData();
    }

    // --- 2. XEM CHI TIẾT ĐƠN HÀNG ---
    function viewOrderDetail(orderId) {
        currentOrderId = orderId;
        Swal.fire({ title: 'Đang tải...', didOpen: () => { Swal.showLoading() }, allowOutsideClick: false, showConfirmButton: false });

        fetch(`${API_BASE}/view_detail?id=${orderId}`)
            .then(res => res.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    const order = data.order;
                    const items = data.items || [];

                    document.getElementById('order_id').textContent = order.id;
                    document.getElementById('customer_name').textContent = order.customer_name;
                    document.getElementById('customer_phone').textContent = order.customer_phone;
                    document.getElementById('customer_email').textContent = order.customer_email || 'Không có';
                    
                    const addressParts = [order.shipping_address_detail, order.shipping_ward, order.shipping_district, order.shipping_province];
                    document.getElementById('shipping_address').textContent = addressParts.filter(Boolean).join(', ');

                    document.getElementById('subtotal').textContent = formatMoney(order.subtotal);
                    document.getElementById('shipping_fee').textContent = formatMoney(order.shipping_fee);
                    document.getElementById('discount_amount').textContent = '-' + formatMoney(order.discount_amount);
                    document.getElementById('voucher_code').textContent = order.voucher_code || '--';
                    document.getElementById('total_money').textContent = formatMoney(order.total_money);
                    document.getElementById('note').textContent = order.note || 'Không có ghi chú';

                    const productsContainer = document.getElementById('order_products');
                    productsContainer.innerHTML = '';

                    if (items.length > 0) {
                        items.forEach((item, index) => {
                            const borderClass = index === items.length - 1 ? '' : 'border-bottom';
                            productsContainer.innerHTML += `
                            <div class="d-flex align-items-center ${borderClass} py-3 px-4">
                                <div class="position-relative">
                                    <img src="/web_qlsp/Public/Picture/${item.image}" alt="${item.name}" class="rounded border shadow-sm bg-white" style="width: 60px; height: 60px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/60'">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary" style="font-size: 0.65rem;">x${item.quantity}</span>
                                </div>
                                <div class="ms-3 flex-grow-1" style="min-width: 0;">
                                    <h6 class="mb-1 fw-bold text-dark text-truncate" title="${item.name}" style="font-size: 0.9rem;">${item.name}</h6>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-light text-secondary border fw-normal">Size: ${item.size || 'F'}</span>
                                        <span class="badge bg-light text-secondary border fw-normal">Màu: ${item.color || 'F'}</span>
                                    </div>
                                </div>
                                <div class="text-end ps-2">
                                    <div class="fw-bold text-danger" style="font-size: 0.95rem;">${formatMoney(item.subtotal)}</div>
                                </div>
                            </div>`;
                        });
                    }

                    document.getElementById('current_status_badge').innerHTML = getStatusBadge(order.status);
                    document.getElementById('new_status').value = order.status;
                    
                    const statusSelect = document.getElementById('new_status');
                    const updateBtn = document.getElementById('btnUpdateStatus');

                    if (order.status === 'completed' || order.status === 'cancelled') {
                        statusSelect.disabled = true; updateBtn.disabled = true; updateBtn.classList.add('opacity-50');
                    } else {
                        statusSelect.disabled = false; updateBtn.disabled = false; updateBtn.classList.remove('opacity-50');
                    }

                    new bootstrap.Modal(document.getElementById('orderDetailModal')).show();
                } else {
                    Swal.fire('Lỗi!', data.message || 'Lỗi hệ thống', 'error');
                }
            });
    }

    // --- 3. CẬP NHẬT TRẠNG THÁI ---
    function updateStatusFromModal() {
        const newStatus = document.getElementById('new_status').value;
        if (!newStatus) return Swal.fire('Chú ý', 'Vui lòng chọn trạng thái mới!', 'warning');

        Swal.fire({
            title: 'Cập nhật trạng thái?', text: "Xác nhận thay đổi trạng thái?", icon: 'question',
            showCancelButton: true, confirmButtonText: 'Cập nhật', cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('order_id', currentOrderId);
                formData.append('status', newStatus);

                fetch(`${API_BASE}/update_status`, { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ title: 'Thành công!', text: 'Đã cập nhật.', icon: 'success', timer: 1500, showConfirmButton: false });
                            document.getElementById('closeDetailModal').click();
                            loadData(); // Tải lại bảng mà không reload trang
                        } else {
                            Swal.fire('Thất bại', data.message, 'error');
                        }
                    });
            }
        });
    }

    // --- 4. XÓA ĐƠN HÀNG ---
    function deleteOrder(orderId) {
        Swal.fire({
            title: 'Xóa vĩnh viễn?', text: "Không thể hoàn tác!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Xóa ngay', cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('order_id', orderId);

                fetch(`${API_BASE}/delete_order`, { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ title: 'Đã xóa!', text: 'Đơn hàng đã bay màu.', icon: 'success', timer: 1500, showConfirmButton: false });
                            document.getElementById('closeDetailModal').click();
                            loadData(); // Tải lại bảng
                        } else {
                            Swal.fire('Lỗi', data.message, 'error');
                        }
                    });
            }
        });
    }

    // Đồng bộ lịch
    const fromInput = document.getElementById('fromDate');
    const toInput = document.getElementById('toDate');
    if(fromInput && toInput){
        const syncDate = () => {
            if (fromInput.value) toInput.min = fromInput.value;
            if (toInput.value) fromInput.max = toInput.value;
        };
        fromInput.addEventListener('change', syncDate);
        toInput.addEventListener('change', syncDate);
    }
</script>