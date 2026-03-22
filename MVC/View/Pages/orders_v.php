<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Danh sách Đơn hàng</h4>
    <div class="d-flex gap-2 align-items-center">
        <input type="date" class="form-control shadow-sm" id="fromDate" style="width: 170px;" value="<?php echo isset($_GET['from']) ? htmlspecialchars($_GET['from']) : (isset($data['filter_from']) ? htmlspecialchars($data['filter_from']) : ''); ?>"/>
        <span class="text-muted fw-bold">-</span>
        <input type="date" class="form-control shadow-sm" id="toDate" style="width: 170px;" value="<?php echo isset($_GET['to']) ? htmlspecialchars($_GET['to']) : (isset($data['filter_to']) ? htmlspecialchars($data['filter_to']) : ''); ?>"/>
        
        <select class="form-select shadow-sm" id="statusFilter" onchange="applyFilters()" style="width: 200px;">
            <option value="all" <?php echo (!isset($_GET['status']) || $_GET['status'] == 'all') ? 'selected' : ''; ?>>Tất cả trạng thái</option>
            <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Chờ xử lý</option>
            <option value="confirmed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'confirmed') ? 'selected' : ''; ?>>Đã xác nhận</option>
            <option value="shipping" <?php echo (isset($_GET['status']) && $_GET['status'] == 'shipping') ? 'selected' : ''; ?>>Đang giao hàng</option>
            <option value="completed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'completed') ? 'selected' : ''; ?>>Hoàn thành</option>
            <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
        </select>
        
        <button class="btn btn-dark shadow-sm" onclick="applyFilters()">
            <i class="fas fa-filter me-1"></i> Lọc
        </button>
    </div>
</div>

<style>
    /* CSS Tùy chỉnh cho trang đơn hàng */
    .product-list .border-bottom:last-child { border-bottom: none !important; }
    .skeleton { background: #e0e0e0; animation: pulse 1.5s infinite; border-radius: 4px; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
    
    /* Hover effect cho dòng bảng */
    .table-hover tbody tr:hover { background-color: #f8f9fa; }
    
    /* Scrollbar đẹp cho modal nếu dài */
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
                    <?php
                    if (isset($data['orders_list']) && $data['orders_list'] && mysqli_num_rows($data['orders_list']) > 0) {
                        while ($row = mysqli_fetch_array($data['orders_list'])) {
                            // Định dạng dữ liệu
                            $total_money = number_format($row['total_money'], 0, ',', '.');
                            $created_date = date('d/m/Y H:i', strtotime($row['created_at']));
                            
                            // Xử lý Badge trạng thái
                            $status = trim($row['status'] ?? 'pending');
                            $status_badge = match($status) {
                                'pending'   => '<span class="badge bg-warning text-dark bg-opacity-75">⏳ Chờ xử lý</span>',
                                'confirmed' => '<span class="badge bg-info bg-opacity-75 text-dark">✅ Đã xác nhận</span>',
                                'shipping'  => '<span class="badge bg-primary bg-opacity-75">🚚 Đang giao hàng</span>',
                                'completed' => '<span class="badge bg-success bg-opacity-75">🎉 Hoàn thành</span>',
                                'cancelled' => '<span class="badge bg-danger bg-opacity-75">❌ Đã hủy</span>',
                                default     => '<span class="badge bg-secondary">Không xác định</span>'
                            };
                    ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary">#<?php echo $row['id']; ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo $row['customer_name']; ?></div>
                                    <small class="text-muted"><i class="fas fa-phone-alt me-1" style="font-size:10px;"></i><?php echo $row['customer_phone']; ?></small>
                                </td>
                                <td><?php echo $created_date; ?></td>
                                <td class="fw-bold text-danger"><?php echo $total_money; ?>đ</td>
                                <td><?php echo $status_badge; ?></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary fw-bold" onclick="viewOrderDetail(<?php echo $row['id']; ?>)">
                                        Chi tiết <i class="bi bi-arrow-right-circle ms-1"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fs-1 mb-3 d-block opacity-25"></i>
                                Không tìm thấy đơn hàng nào.
                            </td>
                        </tr>
                    <?php } ?>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                                <div id="order_products" class="flex-grow-1 overflow-auto" style="max-height: 400px;">
                                    </div>
                                
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

<link rel="stylesheet" href="/web_qlsp/Public/css/loading.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/web_qlsp/Public/js/loading.js"></script> 

<script>
    let currentOrderId = null;

    // --- 1. XEM CHI TIẾT ĐƠN HÀNG ---
    function viewOrderDetail(orderId) {
        currentOrderId = orderId;

        // Show loading state
        Swal.fire({
            title: 'Đang tải...',
            didOpen: () => { Swal.showLoading() },
            allowOutsideClick: false,
            background: 'transparent',
            backdrop: 'rgba(0,0,0,0.3)',
            showConfirmButton: false
        });

        fetch('/web_qlsp/orders/view_detail?id=' + orderId)
            .then(response => {
                if (!response.ok) throw new Error('Lỗi kết nối server');
                return response.json();
            })
            .then(data => {
                Swal.close(); // Tắt loading

                if (data.success) {
                    const order = data.order;
                    const items = data.items || [];
                    const formatMoney = (amount) => new Intl.NumberFormat('vi-VN').format(amount || 0) + 'đ';

                    // --- ĐIỀN DỮ LIỆU ---
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

                    // --- RENDER SẢN PHẨM ---
                    const productsContainer = document.getElementById('order_products');
                    productsContainer.innerHTML = '';

                    if (items && items.length > 0) {
                        items.forEach((item, index) => {
                            const borderClass = index === items.length - 1 ? '' : 'border-bottom';

                            const productHtml = `
                            <div class="d-flex align-items-center ${borderClass} py-3 px-4">
                                <div class="position-relative">
                                    <img src="/web_qlsp/Public/Picture/${item.image}" 
                                         alt="${item.name}" 
                                         class="rounded border shadow-sm bg-white"
                                         style="width: 60px; height: 60px; object-fit: cover;"
                                         onerror="this.src='https://via.placeholder.com/60'">
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
                            productsContainer.innerHTML += productHtml;
                        });
                    } else {
                        productsContainer.innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-box-open fs-3 mb-2 d-block"></i>Không có sản phẩm</div>';
                    }

                    // --- TRẠNG THÁI ---
                    document.getElementById('current_status_badge').innerHTML = getStatusBadge(order.status);
                    document.getElementById('new_status').value = order.status;
                    
                    // --- [LOGIC MỚI] KIỂM TRA ĐỂ KHÓA NÚT ---
                    const statusSelect = document.getElementById('new_status');
                    const updateBtn = document.getElementById('btnUpdateStatus'); // Chọn theo ID đã thêm

                    if (order.status === 'completed' || order.status === 'cancelled') {
                        statusSelect.disabled = true;
                        updateBtn.disabled = true;
                        updateBtn.classList.add('opacity-50'); // Làm mờ nút
                    } else {
                        statusSelect.disabled = false;
                        updateBtn.disabled = false;
                        updateBtn.classList.remove('opacity-50');
                    }
                    // ----------------------------------------

                    // Hiện Modal
                    new bootstrap.Modal(document.getElementById('orderDetailModal')).show();

                } else {
                    Swal.fire('Lỗi!', 'Không thể tải dữ liệu: ' + (data.message || 'Lỗi lạ'), 'error');
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Error:', error);
                Swal.fire('Lỗi!', 'Có lỗi kết nối xảy ra.', 'error');
            });
    }

    // --- 2. HELPER: TRẠNG THÁI ---
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

    // --- 3. CẬP NHẬT TRẠNG THÁI ---
    function updateStatusFromModal() {
        const newStatus = document.getElementById('new_status').value;
        if (!newStatus) {
            Swal.fire('Chú ý', 'Vui lòng chọn trạng thái mới!', 'warning');
            return;
        }

        Swal.fire({
            title: 'Cập nhật trạng thái?',
            text: "Xác nhận thay đổi trạng thái đơn hàng này?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Cập nhật',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('order_id', currentOrderId);
                formData.append('status', newStatus);

                fetch('/web_qlsp/orders/update_status', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Thành công!',
                                text: 'Trạng thái đã được cập nhật.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Thất bại', data.message || 'Lỗi server', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Lỗi', 'Không thể kết nối server', 'error'));
            }
        });
    }

    // --- 4. XÓA ĐƠN HÀNG ---
    function deleteOrder(orderId) {
        Swal.fire({
            title: 'Xóa vĩnh viễn?',
            text: "Hành động này KHÔNG THỂ hoàn tác! Bạn có chắc?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Xóa ngay',
            cancelButtonText: 'Suy nghĩ lại'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('order_id', orderId);

                fetch('/web_qlsp/orders/delete_order', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Đã xóa!',
                                text: 'Đơn hàng đã bay màu.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Lỗi', data.message, 'error');
                        }
                    })
                    .catch(() => Swal.fire('Lỗi', 'Không thể kết nối server', 'error'));
            }
        });
    }

    // --- 5. BỘ LỌC NGÀY ---
    function applyFilters() {
        const status = document.getElementById('statusFilter').value || 'all';
        const from = document.getElementById('fromDate').value;
        const to = document.getElementById('toDate').value;
        
        if (from && to && new Date(from) > new Date(to)) {
            Swal.fire('Ngày không hợp lệ', 'Ngày bắt đầu phải nhỏ hơn ngày kết thúc', 'warning');
            return;
        }
        
        const params = new URLSearchParams();
        if (status) params.set('status', status);
        if (from) params.set('from', from);
        if (to) params.set('to', to);
        window.location.href = '/web_qlsp/orders' + (params.toString() ? '?' + params.toString() : '');
    }

    // Auto sync min/max date
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