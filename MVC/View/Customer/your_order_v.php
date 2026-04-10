<style>
    /* Kế thừa toàn bộ CSS của bạn */
    .order-card { border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 20px; transition: box-shadow 0.3s; background: #fff; }
    .order-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .order-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; border-bottom: 1px solid #e0e0e0; margin-bottom: 15px; }
    .status-badge { padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-confirmed { background: #d1ecf1; color: #0c5460; }
    .status-shipping { background: #cce5ff; color: #004085; }
    .status-completed { background: #d4edda; color: #155724; }
    .status-cancelled { background: #f8d7da; color: #721c24; }
    
    .order-tabs { border-bottom: 2px solid #e0e0e0; margin-bottom: 30px; display: flex; overflow-x: auto; white-space: nowrap; scrollbar-width: none;}
    .order-tabs::-webkit-scrollbar { display: none; }
    .order-tab { padding: 12px 24px; border: none; background: none; color: #666; font-weight: 500; cursor: pointer; border-bottom: 3px solid transparent; transition: all 0.3s; }
    .order-tab.active { color: #2f5acf; border-bottom-color: #2f5acf; }
    .order-tab:hover { color: #2f5acf; }
    
    .order-product-item { display: flex; gap: 15px; padding: 10px 0; }
    .order-product-img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #e0e0e0; }
    .btn-view-detail { background: #2f5acf; color: white; border: none; padding: 8px 20px; border-radius: 6px; font-size: 14px; font-weight: 500; transition: all 0.3s; }
    .btn-view-detail:hover { background: #1e3a8a; color: white; }
    .btn-cancel-order { background: white; color: #dc3545; border: 1px solid #dc3545; padding: 8px 20px; border-radius: 6px; font-size: 14px; font-weight: 500; transition: all 0.3s; }
    .btn-cancel-order:hover { background: #dc3545; color: white; }
    .btn-confirm-order { background: white; color: #28a745; border: 1px solid #28a745; padding: 8px 20px; border-radius: 6px; font-size: 14px; font-weight: 500; transition: all 0.3s; }
    .btn-confirm-order:hover { background: #28a745; color: white; }

    .skeleton { background: #e2e8f0; animation: pulse 1.5s infinite; border-radius: 8px; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }
</style>

<div class="my-5" style="max-width: 1000px; margin: 0 auto;">
    <h2 class="fw-bold mb-4">Đơn hàng của bạn</h2>
    
    <div class="order-tabs gap-2" id="order-tabs">
        <button class="order-tab active" data-status="">Tất cả (<span id="count-all">0</span>)</button>
        <button class="order-tab" data-status="pending">Chờ xác nhận (<span id="count-pending">0</span>)</button>
        <button class="order-tab" data-status="confirmed">Đã xác nhận (<span id="count-confirmed">0</span>)</button>
        <button class="order-tab" data-status="shipping">Đang giao (<span id="count-shipping">0</span>)</button>
        <button class="order-tab" data-status="completed">Hoàn thành (<span id="count-completed">0</span>)</button>
        <button class="order-tab" data-status="cancelled">Đã hủy (<span id="count-cancelled">0</span>)</button>
    </div>
    
    <div id="loading-skeleton">
        <?php for($i=0; $i<3; $i++): ?>
        <div class="order-card">
            <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                <div class="skeleton" style="width: 150px; height: 20px;"></div>
                <div class="skeleton" style="width: 100px; height: 25px; border-radius: 15px;"></div>
            </div>
            <div class="d-flex gap-3">
                <div class="skeleton" style="width: 80px; height: 80px;"></div>
                <div class="flex-grow-1">
                    <div class="skeleton mb-2" style="width: 40%; height: 16px;"></div>
                    <div class="skeleton mb-2" style="width: 20%; height: 14px;"></div>
                    <div class="skeleton" style="width: 30%; height: 18px;"></div>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <div id="orders-container" style="display: none;"></div>

</div>

<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-file-invoice-dollar text-primary me-2"></i>Chi tiết đơn hàng #<span id="modal-order-id" class="text-danger"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="order-detail-content">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const statusTextMap = {
    'pending': 'Chờ xác nhận',
    'confirmed': 'Đã xác nhận',
    'shipping': 'Đang giao hàng',
    'completed': 'Hoàn thành',
    'cancelled': 'Đã hủy'
};

// =============================
// LOAD ORDERS
// =============================
function loadOrders() {
    const params = new URLSearchParams(window.location.search);
    const currentStatus = params.get('status') || '';

    document.querySelectorAll('.order-tab').forEach(tab => {
        tab.classList.remove('active');
        if (tab.getAttribute('data-status') === currentStatus) tab.classList.add('active');
    });

    document.getElementById('loading-skeleton').style.display = 'block';
    document.getElementById('orders-container').style.display = 'none';

    fetch(`/web_qlsp/your_order/api_get_orders?status=${currentStatus}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('count-all').textContent = data.counts.all || 0;
                document.getElementById('count-pending').textContent = data.counts.pending || 0;
                document.getElementById('count-confirmed').textContent = data.counts.confirmed || 0;
                document.getElementById('count-shipping').textContent = data.counts.shipping || 0;
                document.getElementById('count-completed').textContent = data.counts.completed || 0;
                document.getElementById('count-cancelled').textContent = data.counts.cancelled || 0;

                renderOrders(data.orders);
            } else {
                Swal.fire('Lỗi', data.message, 'error');
            }
        })
        .catch(() => {
            document.getElementById('orders-container').innerHTML = 
                '<div class="text-center py-5 text-danger">Lỗi kết nối máy chủ</div>';
            document.getElementById('loading-skeleton').style.display = 'none';
            document.getElementById('orders-container').style.display = 'block';
        });
}

// =============================
// RENDER ORDERS
// =============================
function renderOrders(orders) {
    const container = document.getElementById('orders-container');
    let html = '';

    if (orders && orders.length > 0) {
        orders.forEach(order => {
            const firstItem = order.items?.[0];
            const totalItems = order.total_items;
            const dateStr = new Date(order.created_at).toLocaleString('vi-VN');

            let productHtml = '';
            if (firstItem) {
                const img = firstItem.variant_image || firstItem.product_image;
                const moreStr = totalItems > 1 
                    ? `<p class="text-muted mt-2">+ ${totalItems - 1} sản phẩm khác</p>` 
                    : '';

                productHtml = `
                <div class="order-product-item">
                    <img src="/web_qlsp/Public/Picture/${img}" class="order-product-img">
                    <div class="flex-grow-1">
                        <h6>${firstItem.product_name}</h6>
                        <p class="text-muted">
                            SL: ${firstItem.quantity}
                            ${firstItem.size ? ` | Size: ${firstItem.size}` : ''}
                            ${firstItem.color ? ` | Màu: ${firstItem.color}` : ''}
                        </p>
                        <p class="fw-bold">${parseInt(firstItem.price).toLocaleString()}đ</p>
                    </div>
                </div>
                ${moreStr}`;
            }

            let btnHtml = `
                <button class="btn-view-detail" onclick="viewOrderDetail(${order.id})">
                    Xem chi tiết
                </button>
            `;

            // =========================
            // 🔥 THANH TOÁN LẠI
            // =========================
            if (order.status === 'pending') {
                const paymentMethod = (order.payment_method || 'cod').toLowerCase();

                if (paymentMethod !== 'cod') {
                    btnHtml += `
                    <button class="btn-view-detail" 
                        style="background:#ff9800;border:none"
                        onclick="retryPayment(${order.id})">
                        <i class="fas fa-credit-card me-1"></i> Thanh toán lại
                    </button>`;
                }

                btnHtml += `
                <button class="btn-cancel-order" onclick="cancelOrder(${order.id})">
                    Hủy đơn
                </button>`;
            }

            if (order.status === 'shipping') {
                btnHtml += `
                <button class="btn-confirm-order" onclick="confirmOrder(${order.id})">
                    Đã nhận hàng
                </button>`;
            }

            html += `
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <h5>Đơn hàng #${order.id}</h5>
                        <small>${dateStr}</small>
                    </div>
                    <span class="status-badge status-${order.status}">
                        ${statusTextMap[order.status]}
                    </span>
                </div>

                <div class="row">
                    <div class="col-md-8">${productHtml}</div>
                    <div class="col-md-4 text-end">
                        <p class="text-muted">Tổng tiền</p>
                        <h4>${parseInt(order.total_money || 0).toLocaleString()}đ</h4>
                        <div class="d-flex gap-2 justify-content-end flex-wrap">
                            ${btnHtml}
                        </div>
                    </div>
                </div>
            </div>`;
        });
    } else {
        html = `
        <div class="text-center py-5">
            <h4>Chưa có đơn hàng</h4>
        </div>`;
    }

    container.innerHTML = html;
    document.getElementById('loading-skeleton').style.display = 'none';
    container.style.display = 'block';
}

// =============================
// 🔥 THANH TOÁN LẠI
// =============================
function retryPayment(orderId) {
    Swal.fire({
        title: 'Thanh toán lại?',
        text: "Bạn sẽ được chuyển tới trang thanh toán VNPay",
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Tiếp tục',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/web_qlsp/payment/create/' + orderId;
        }
    });
}

// =============================
// VIEW DETAIL
// =============================
function viewOrderDetail(orderId) {
        document.getElementById('modal-order-id').textContent = orderId;
        document.getElementById('order-detail-content').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
        new bootstrap.Modal(document.getElementById('orderDetailModal')).show();
        
        // ĐÃ XÓA CHỮ /Customer/ TẠI ĐÂY
        fetch('/web_qlsp/your_order/detail?id=' + orderId)
            .then(res => res.json())
            .then(data => {
                if (data.success) displayOrderDetail(data.order, data.items); 
                else document.getElementById('order-detail-content').innerHTML = `<p class="text-danger text-center mt-3">${data.message}</p>`;
            })
            .catch(() => document.getElementById('order-detail-content').innerHTML = `<p class="text-danger text-center mt-3">Lỗi tải dữ liệu.</p>`);
    }

    function displayOrderDetail(order, items) {
        let productsHtml = '';
        if (items && items.length > 0) {
            items.forEach(item => {
                const img = item.variant_image ? item.variant_image : item.product_image;
                productsHtml += `
                    <div class="d-flex gap-3 py-3 border-bottom">
                        <img src="/web_qlsp/Public/Picture/${img}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border:1px solid #eee;">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${item.product_name}</h6>
                            <p class="text-muted mb-1" style="font-size: 13px;">SL: ${item.quantity}${item.size ? ` | Size: ${item.size}` : ''}${item.color ? ` | Màu: ${item.color}` : ''}</p>
                            <p class="fw-bold mb-0" style="color: #2f5acf;">${parseInt(item.price).toLocaleString('vi-VN')}đ</p>
                        </div>
                        <div class="text-end fw-bold d-flex align-items-center">${parseInt(item.total).toLocaleString('vi-VN')}đ</div>
                    </div>`;
            });
        }
        
const addressStr = `${order.shipping_address_detail || ''}, ${order.ward_name || order.shipping_ward || ''}, ${order.district_name || order.shipping_district || ''}, ${order.province_name || order.shipping_province || ''}`;
        document.getElementById('order-detail-content').innerHTML = `
            <div class="mb-4 p-3 bg-light rounded-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold">Mã vận đơn</h6>
                    <span class="status-badge status-${order.status}">${statusTextMap[order.status]}</span>
                </div>
                <p class="text-muted mb-0 small">Ngày đặt: ${new Date(order.created_at).toLocaleString('vi-VN')}</p>
            </div>
            
            <div class="mb-4">
                <h6 class="mb-3 fw-bold"><i class="fas fa-map-marker-alt text-danger me-2"></i>Thông tin người nhận</h6>
                <div class="ms-4 small text-muted">
                    <p class="mb-1"><strong>Họ tên:</strong> <span class="text-dark">${order.customer_name || order.full_name}</span></p>
                    <p class="mb-1"><strong>SĐT:</strong> <span class="text-dark">${order.customer_phone || order.phone}</span></p>
                    <p class="mb-0"><strong>Địa chỉ:</strong> <span class="text-dark">${addressStr}</span></p>
                </div>
            </div>
            
            <div class="mb-4 border rounded-3 p-3">
                <h6 class="mb-2 fw-bold"><i class="fas fa-box-open text-primary me-2"></i>Sản phẩm</h6>
                ${productsHtml}
            </div>
            
            <div class="mb-2 p-3 bg-light rounded-3">
                <div class="d-flex justify-content-between mb-2 small"><span>Tạm tính:</span><strong>${parseInt(order.subtotal || 0).toLocaleString('vi-VN')}đ</strong></div>
                <div class="d-flex justify-content-between mb-2 small"><span>Phí vận chuyển:</span><strong>${parseInt(order.shipping_fee || 0).toLocaleString('vi-VN')}đ</strong></div>
                ${order.discount_amount > 0 ? `<div class="d-flex justify-content-between mb-2 small"><span>Giảm giá (Voucher):</span><strong class="text-danger">-${parseInt(order.discount_amount).toLocaleString('vi-VN')}đ</strong></div>` : ''}
                ${order.points_discount > 0 ? `<div class="d-flex justify-content-between mb-2 small"><span>Giảm giá (Điểm):</span><strong class="text-danger">-${parseInt(order.points_discount).toLocaleString('vi-VN')}đ</strong></div>` : ''}
                <div class="d-flex justify-content-between pt-2 border-top mt-2">
                    <strong>Tổng thanh toán:</strong><strong style="color: #e74c3c; font-size: 18px;">${parseInt(order.total_money || order.grand_total || 0).toLocaleString('vi-VN')}đ</strong>
                </div>
            </div>
        `;
    }

    // ĐÃ CHỈNH SỬA AJAX ĐỂ KHÔNG LOAD LẠI TRANG KHI HỦY
    function cancelOrder(orderId) {
        Swal.fire({
            title: 'Hủy đơn hàng?', text: "Hành động này không thể hoàn tác.", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Đồng ý hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Đang xử lý...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                const fd = new FormData(); fd.append('order_id', orderId);
                
                // ĐÃ XÓA CHỮ /Customer/ TẠI ĐÂY
                fetch('/web_qlsp/your_order/cancel', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Thành công!', timer: 1500, showConfirmButton: false });
                        loadOrders(); // Chỉ load lại danh sách ngầm (Khỏi F5 trang)
                    } else Swal.fire('Lỗi', data.message, 'error');
                });
            }
        });
    }

// =============================
// CONFIRM ORDER
// =============================
function confirmOrder(orderId) {
    const fd = new FormData();
    fd.append('order_id', orderId);

    fetch('/web_qlsp/your_order/confirm', {
        method: 'POST',
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) loadOrders();
        else Swal.fire('Lỗi', data.message, 'error');
    });
}

// =============================
// TAB CLICK
// =============================
document.querySelectorAll('.order-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const status = this.getAttribute('data-status');
        const newUrl = window.location.pathname + (status ? '?status=' + status : '');
        window.history.pushState({}, '', newUrl);
        loadOrders();
    });
});

window.addEventListener('popstate', loadOrders);
document.addEventListener("DOMContentLoaded", loadOrders);

</script>