<style>
    .order-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        transition: box-shadow 0.3s;
    }
    .order-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 15px;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 15px;
    }
    .status-badge {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-confirmed { background: #d1ecf1; color: #0c5460; }
    .status-shipping { background: #cce5ff; color: #004085; }
    .status-completed { background: #d4edda; color: #155724; }
    .status-cancelled { background: #f8d7da; color: #721c24; }
    
    .order-tabs {
        border-bottom: 2px solid #e0e0e0;
        margin-bottom: 30px;
    }
    .order-tab {
        padding: 12px 24px;
        border: none;
        background: none;
        color: #666;
        font-weight: 500;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.3s;
    }
    .order-tab.active {
        color: #2f5acf;
        border-bottom-color: #2f5acf;
    }
    .order-tab:hover {
        color: #2f5acf;
    }
    .order-product-item {
        display: flex;
        gap: 15px;
        padding: 10px 0;
    }
    .order-product-img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
    }
    .btn-view-detail {
        background: #2f5acf;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
    }
    .btn-view-detail:hover {
        background: #1e3a8a;
        color: white;
    }
    .btn-cancel-order {
        background: white;
        color: #dc3545;
        border: 1px solid #dc3545;
        padding: 8px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
    }
    .btn-cancel-order:hover {
        background: #dc3545;
        color: white;
    }
    .btn-confirm-order {
        background: white;
        color: #28a745;
        border: 1px solid #28a745;
        padding: 8px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
    }
    .btn-confirm-order:hover {
        background: #28a745;
        color: white;
    }
</style>

<div class="my-5">
    <h2 class="fw-bold mb-4">Đơn hàng của bạn</h2>
    
    <!-- Tabs lọc trạng thái -->
    <div class="order-tabs d-flex gap-2">
        <button class="order-tab <?= !isset($data['current_status']) ? 'active' : '' ?>" 
                onclick="window.location.href='/web_qlsp/api/customer/your_order_api'">
            Tất cả (<?= isset($data['counts']) ? $data['counts']['all'] : 0 ?>)
        </button>
        <button class="order-tab <?= isset($data['current_status']) && $data['current_status'] == 'pending' ? 'active' : '' ?>" 
                onclick="window.location.href='/web_qlsp/api/customer/your_order_api?status=pending'">
            Chờ xác nhận (<?= isset($data['counts']) ? $data['counts']['pending'] : 0 ?>)
        </button>
        <button class="order-tab <?= isset($data['current_status']) && $data['current_status'] == 'confirmed' ? 'active' : '' ?>" 
                onclick="window.location.href='/web_qlsp/api/customer/your_order_api?status=confirmed'">
            Đã xác nhận (<?= isset($data['counts']) ? $data['counts']['confirmed'] : 0 ?>)
        </button>
        <button class="order-tab <?= isset($data['current_status']) && $data['current_status'] == 'shipping' ? 'active' : '' ?>" 
                onclick="window.location.href='/web_qlsp/api/customer/your_order_api?status=shipping'">
            Đang giao (<?= isset($data['counts']) ? $data['counts']['shipping'] : 0 ?>)
        </button>
        <button class="order-tab <?= isset($data['current_status']) && $data['current_status'] == 'completed' ? 'active' : '' ?>" 
                onclick="window.location.href='/web_qlsp/api/customer/your_order_api?status=completed'">
            Hoàn thành (<?= isset($data['counts']) ? $data['counts']['completed'] : 0 ?>)
        </button>
        <button class="order-tab <?= isset($data['current_status']) && $data['current_status'] == 'cancelled' ? 'active' : '' ?>" 
                onclick="window.location.href='/web_qlsp/api/customer/your_order_api?status=cancelled'">
            Đã hủy (<?= isset($data['counts']) ? $data['counts']['cancelled'] : 0 ?>)
        </button>
    </div>
    
    <!-- Danh sách đơn hàng -->
    <?php 
    // Debug
    echo "<!-- Debug: ";
    echo "orders_data exists: " . (isset($data['orders_data']) ? 'yes' : 'no') . ", ";
    echo "count: " . (isset($data['orders_data']) ? count($data['orders_data']) : '0') . ", ";
    echo "debug_orders_count from DB: " . (isset($data['debug_orders_count']) ? $data['debug_orders_count'] : 'not set') . ", ";
    
    // Debug chi tiết đơn hàng đầu tiên
    if (isset($data['orders_data']) && count($data['orders_data']) > 0) {
        echo "\nFirst Order Data: ";
        $first_order = $data['orders_data'][0];
        echo "ID: " . $first_order['id'] . ", ";
        echo "Status: " . $first_order['status'] . ", ";
        echo "Items count: " . $first_order['total_items'] . ", ";
        if (isset($first_order['items'][0])) {
            echo "\nFirst Item: ";
            echo "product_id: " . $first_order['items'][0]['product_id'] . ", ";
            echo "product_name: " . ($first_order['items'][0]['product_name'] ?? 'NULL') . ", ";
            echo "product_image: " . ($first_order['items'][0]['product_image'] ?? 'NULL') . ", ";
            echo "quantity: " . $first_order['items'][0]['quantity'] . ", ";
            echo "price: " . $first_order['items'][0]['price'];
        } else {
            echo "No items in order";
        }
    }
    
    echo " -->";
    
    if (isset($data['orders_data']) && count($data['orders_data']) > 0) {
        foreach($data['orders_data'] as $order) {
            // Lấy sản phẩm đầu tiên để hiển thị preview
            $first_item = isset($order['items'][0]) ? $order['items'][0] : null;
            $total_items = $order['total_items'];
            
            // Format trạng thái
            $status_text = [
                'pending' => 'Chờ xác nhận',
                'confirmed' => 'Đã xác nhận',
                'shipping' => 'Đang giao hàng',
                'completed' => 'Hoàn thành',
                'cancelled' => 'Đã hủy'
            ];
    ?>
    <div class="order-card">
        <div class="order-header">
            <div>
                <h5 class="mb-1">Đơn hàng #<?= $order['id'] ?></h5>
                <small class="text-muted">Đặt ngày: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></small>
            </div>
            <span class="status-badge status-<?= $order['status'] ?>">
                <?= $status_text[$order['status']] ?>
            </span>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <?php if ($first_item) { ?>
                <div class="order-product-item">
                    <?php $previewImg = !empty($first_item['variant_image']) ? $first_item['variant_image'] : $first_item['product_image']; ?>
                    <img src="/web_qlsp/Public/Picture/<?= $previewImg ?>" 
                         alt="<?= $first_item['product_name'] ?>" 
                         class="order-product-img">
                    <div class="flex-grow-1">
                        <h6 class="mb-1"><?= $first_item['product_name'] ?></h6>
                        <p class="text-muted mb-1" style="font-size: 14px;">
                            Số lượng: <?= $first_item['quantity'] ?>
                            <?php if (!empty($first_item['size'])): ?>
                                | Size: <?= htmlspecialchars($first_item['size']) ?>
                            <?php endif; ?>
                            <?php if (isset($first_item['color']) && $first_item['color'] !== null && $first_item['color'] !== ''): ?>
                                | Màu: <?= htmlspecialchars($first_item['color']) ?>
                            <?php endif; ?>
                        </p>
                        <p class="fw-bold mb-0" style="color: #2f5acf;">
                            <?= number_format($first_item['price']) ?>đ
                        </p>
                    </div>
                </div>
                <?php 
                    if ($total_items > 1) {
                        echo '<p class="text-muted mt-2 mb-0" style="font-size: 14px;">+ ' . ($total_items - 1) . ' sản phẩm khác</p>';
                    }
                } 
                ?>
            </div>
            
            <div class="col-md-4 text-end">
                <p class="text-muted mb-1" style="font-size: 14px;">Tổng tiền:</p>
                <h4 class="mb-3" style="color: #2f5acf;"><?= number_format($order['grand_total']) ?>đ</h4>
                
                <div class="d-flex gap-2 justify-content-end">
                    <button class="btn-view-detail" onclick="viewOrderDetail(<?= $order['id'] ?>)">
                        <i class="fas fa-eye me-1"></i> Xem chi tiết
                    </button>

                    <?php if ($order['status'] == 'shipping') { ?>
                    <button class="btn-confirm-order" onclick="confirmOrder(<?= $order['id'] ?>)">
                        <i class="fas fa-check me-1"></i> Đã được nhận hàng
                    </button>
                    <?php } ?>
                    
                    <?php if ($order['status'] == 'pending') { ?>
                    <button class="btn-cancel-order" onclick="cancelOrder(<?= $order['id'] ?>)">
                        <i class="fas fa-times me-1"></i> Hủy đơn
                    </button>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <?php 
        }
    } else {
    ?>
    <div class="text-center py-5">
        <i class="fas fa-shopping-bag" style="font-size: 80px; color: #ccc;"></i>
        <h4 class="mt-3 text-muted">Chưa có đơn hàng nào</h4>
        <p class="text-muted">Hãy khám phá và mua sắm những sản phẩm yêu thích ngay!</p>
        <a href="/web_qlsp/api/customer/product_list_api" class="btn btn-primary mt-3">Tiếp tục mua sắm</a>
    </div>
    <?php } ?>
</div>

<!-- Modal Chi tiết đơn hàng -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết đơn hàng #<span id="modal-order-id"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="order-detail-content">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewOrderDetail(orderId) {
    // 1. Gắn ID vào modal và hiển thị hiệu ứng tải (Loading)
    document.getElementById('modal-order-id').textContent = orderId;
    document.getElementById('order-detail-content').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // 2. Mở Modal
    var modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
    modal.show();
    
    // 3. Gọi API lấy dữ liệu chi tiết
    // Đã sửa 'order_id' thành 'id' để khớp với $_GET['id'] trong PHP
    // Đã bỏ 'user_id' vì PHP tự động dùng $_SESSION['user_id']
    fetch('/web_qlsp/api/customer/your_order_api/detail?id=' + orderId)
        .then(response => {
            if (!response.ok) {
                throw new Error('Mạng bị lỗi hoặc không thể kết nối API');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Đã sửa lại tham số để khớp với phản hồi từ PHP: data.order và data.items
                displayOrderDetail(data.order, data.items); 
            } else {
                 document.getElementById('order-detail-content').innerHTML = `<p class="text-danger text-center mt-3">${data.message || 'Không tìm thấy đơn hàng'}</p>`;
            }
        })
        .catch(error => {
            document.getElementById('order-detail-content').innerHTML = `<p class="text-danger text-center mt-3">Lỗi tải dữ liệu. Vui lòng thử lại.</p>`;
            console.error('Lỗi Fetch:', error);
        });
}

function displayOrderDetail(order, items) {
    const statusText = {
        'pending': 'Chờ xác nhận',
        'confirmed': 'Đã xác nhận',
        'shipping': 'Đang giao hàng',
        'completed': 'Hoàn thành',
        'cancelled': 'Đã hủy'
    };
    
    let productsHtml = '';
    // Kiểm tra items có tồn tại không
    if (items && items.length > 0) {
        items.forEach(item => {
            const img = item.variant_image ? item.variant_image : item.product_image;
            productsHtml += `
                <div class="d-flex gap-3 py-3 border-bottom">
                    <img src="/web_qlsp/Public/Picture/${img}" 
                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${item.product_name}</h6>
                        <p class="text-muted mb-1" style="font-size: 14px;">
                            Số lượng: ${item.quantity}${item.size ? ` | Size: ${item.size}` : ''}${item.color ? ` | Màu: ${item.color}` : ''}
                        </p>
                        <p class="fw-bold mb-0" style="color: #2f5acf;">
                            ${parseInt(item.price).toLocaleString()}đ × ${item.quantity} = ${parseInt(item.total).toLocaleString()}đ
                        </p>
                    </div>
                </div>
            `;
        });
    } else {
        productsHtml = '<p class="text-muted">Không có sản phẩm nào trong đơn hàng này.</p>';
    }
    
    // Kiểm tra order object có các thuộc tính mong muốn không trước khi in ra
    const orderNotes = order.notes || order.note || ''; // Tùy thuộc DB của bạn lưu notes hay note
    const emailStr = order.email || 'Không có';
    const addressStr = order.address || order.shipping_address_detail || ''; // Tùy thuộc cột trong DB
    const wardStr = order.ward_name || '';
    const districtStr = order.district_name || '';
    const provinceStr = order.province_name || '';

    const content = `
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Trạng thái đơn hàng</h6>
                <span class="status-badge status-${order.status}">
                    ${statusText[order.status]}
                </span>
            </div>
            <p class="text-muted mb-0" style="font-size: 14px;">
                Ngày đặt: ${new Date(order.created_at).toLocaleString('vi-VN')}
            </p>
        </div>
        
        <div class="mb-4">
            <h6 class="mb-3">Thông tin người nhận</h6>
            <p class="mb-1"><strong>Họ tên:</strong> ${order.customer_name || order.full_name}</p>
            <p class="mb-1"><strong>Số điện thoại:</strong> ${order.customer_phone || order.phone}</p>
            <p class="mb-1"><strong>Email:</strong> ${emailStr}</p>
            <p class="mb-0"><strong>Địa chỉ:</strong> ${addressStr}, ${wardStr}, ${districtStr}, ${provinceStr}</p>
        </div>
        
        <div class="mb-4">
            <h6 class="mb-3">Danh sách sản phẩm</h6>
            ${productsHtml}
        </div>
        
        <div class="mb-4">
            <h6 class="mb-3">Thanh toán</h6>
            <div class="d-flex justify-content-between mb-2">
                <span>Tạm tính:</span>
                <span>${parseInt(order.subtotal || 0).toLocaleString()}đ</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Phí vận chuyển:</span>
                <span>${parseInt(order.shipping_fee || 0).toLocaleString()}đ</span>
            </div>
            ${order.discount_amount > 0 ? `
            <div class="d-flex justify-content-between mb-2">
                <span>Giảm giá:</span>
                <span class="text-danger">-${parseInt(order.discount_amount).toLocaleString()}đ</span>
            </div>
            ` : ''}
            <div class="d-flex justify-content-between mb-2 pt-2 border-top">
                <strong>Tổng cộng:</strong>
                <strong style="color: #2f5acf; font-size: 18px;">${parseInt(order.total_money || order.grand_total || 0).toLocaleString()}đ</strong>
            </div>
            <p class="text-muted mb-0 mt-2" style="font-size: 14px;">
                <i class="fas fa-credit-card me-1"></i> ${order.payment_method === 'cod' ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản'}
            </p>
        </div>
        
        ${orderNotes ? `
        <div>
            <h6 class="mb-2">Ghi chú</h6>
            <p class="text-muted mb-0">${orderNotes}</p>
        </div>
        ` : ''}
    `;
    
    document.getElementById('order-detail-content').innerHTML = content;
}
// --- Hàm HỦY ĐƠN HÀNG (Nâng cấp) ---
function cancelOrder(orderId) {
    Swal.fire({
        title: 'Hủy đơn hàng?',
        text: "Bạn có chắc chắn muốn hủy đơn hàng này không? Hành động này không thể hoàn tác.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545', // Màu đỏ cảnh báo
        cancelButtonColor: '#6c757d', // Màu xám nút hủy
        confirmButtonText: 'Đồng ý hủy',
        cancelButtonText: 'Suy nghĩ lại',
        reverseButtons: true // Đảo vị trí nút cho thuận tay
    }).then((result) => {
        if (result.isConfirmed) {
            // Hiển thị loading trong lúc gọi API
            Swal.fire({
                title: 'Đang xử lý...',
                text: 'Vui lòng chờ trong giây lát',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData();
            formData.append('order_id', orderId);
            
            fetch('/web_qlsp/api/customer/your_order_api/cancel', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: 'Đơn hàng đã được hủy.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Không thể hủy',
                        text: data.message || 'Có lỗi xảy ra.'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi hệ thống',
                    text: 'Không thể kết nối đến máy chủ.'
                });
            });
        }
    });
}

// --- Hàm XÁC NHẬN NHẬN HÀNG (Nâng cấp) ---
function confirmOrder(orderId) {
    Swal.fire({
        title: 'Xác nhận đã nhận hàng?',
        text: "Bạn xác nhận rằng đã nhận được đầy đủ hàng và hài lòng với sản phẩm?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981', // Màu xanh lá thành công
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Đúng, tôi đã nhận',
        cancelButtonText: 'Chưa',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Hiển thị loading
            Swal.fire({
                title: 'Đang cập nhật...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData();
            formData.append('order_id', orderId);
            
            fetch('/web_qlsp/api/customer/your_order_api/confirm_order', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cảm ơn bạn!',
                        text: 'Xác nhận nhận hàng thành công.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: data.message || 'Có lỗi xảy ra'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi hệ thống',
                    text: 'Không thể kết nối đến máy chủ.'
                });
            });
        }
    });
}
</script>
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>