<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Danh sách Khách hàng</h4>
        <p class="text-muted small mb-0">Quản lý thông tin người dùng của hệ thống</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <form class="d-flex align-items-center" id="formSearch">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="txtSearch" class="form-control" placeholder="Tìm theo tên..." />
            </div>
            <button type="submit" class="btn btn-primary ms-2">Tìm</button>
            <button type="button" class="btn btn-light border ms-2" onclick="resetSearch()">
                <i class="fas fa-undo-alt"></i>
            </button>
        </form>
        
        <button class="btn btn-primary shadow-sm ms-2" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus me-2"></i> Thêm mới
        </button>

        <button type="button" onclick="exportExcel()" class="btn btn-success shadow-sm ms-2">
            <i class="fas fa-file-excel me-2"></i> Xuất Excel
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="bg-light">
                <tr>
                    <th>ID</th>
                    <th>Thông tin cá nhân</th>
                    <th>Liên hệ</th>
                    <th>Điểm tích lũy</th>
                    <th>Ngày tham gia</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            
            <tbody id="loading-skeleton">
                <?php for ($i = 0; $i < 5; $i++): ?>
                <tr>
                    <td><div class="skeleton" style="width: 30px; height: 15px;"></div></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="skeleton rounded-circle me-3" style="width: 40px; height: 40px;"></div>
                            <div>
                                <div class="skeleton" style="width: 120px; height: 15px; margin-bottom: 5px;"></div>
                                <div class="skeleton" style="width: 60px; height: 12px;"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="skeleton" style="width: 150px; height: 12px; margin-bottom: 5px;"></div>
                        <div class="skeleton" style="width: 100px; height: 12px;"></div>
                    </td>
                    <td><div class="skeleton" style="width: 80px; height: 20px;"></div></td>
                    <td><div class="skeleton" style="width: 80px; height: 15px;"></div></td>
                    <td>
                        <div class="skeleton d-inline-block" style="width: 30px; height: 30px; margin-right: 5px;"></div>
                        <div class="skeleton d-inline-block" style="width: 30px; height: 30px;"></div>
                    </td>
                </tr>
                <?php endfor; ?>
            </tbody>

            <tbody id="actual-content" style="display: none;"></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formAddUser">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-plus text-primary me-2"></i>Thêm Khách hàng mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeAddModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Họ và tên <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="full_name" class="form-control" required placeholder="Nhập họ và tên">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" required placeholder="Nhập địa chỉ email">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Số điện thoại <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" name="phone" class="form-control" required pattern="[0-9]{9,11}" title="Vui lòng nhập số điện thoại hợp lệ" placeholder="Nhập số điện thoại">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Mật khẩu <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" required minlength="6" placeholder="Tạo mật khẩu (Ít nhất 6 ký tự)">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Lưu khách hàng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="detailUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Chi tiết Khách hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="text-center p-4 bg-light border-bottom">
                    <img id="detail_avatar" src="" class="rounded-circle mb-3 shadow-sm" width="100" height="100" style="object-fit: cover;">
                    <h5 id="detail_fullname" class="fw-bold mb-1"></h5>
                    <span id="detail_role_badge" class="badge"></span>
                    <span id="detail_google_badge" class="badge bg-danger ms-1" style="display: none;">
                        <i class="fab fa-google"></i> Google
                    </span>
                </div>

                <ul class="nav nav-tabs px-3 pt-3" id="userTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button"><i class="fas fa-user text-primary me-1"></i> Thông tin cá nhân</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button"><i class="fas fa-shopping-cart text-success me-1"></i> Lịch sử mua hàng</button>
                    </li>
                </ul>

                <div class="tab-content p-4">
                    <div class="tab-pane fade show active" id="info" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label text-muted small">Email</label><div class="fw-bold" id="detail_email"></div></div>
                            <div class="col-md-6 mb-3"><label class="form-label text-muted small">Số điện thoại</label><div class="fw-bold" id="detail_phone"></div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label text-muted small">Điểm tích lũy</label><div><span class="badge bg-warning text-dark fs-6" id="detail_points"></span></div></div>
                            <div class="col-md-6 mb-3"><label class="form-label text-muted small">Ngày tham gia</label><div class="fw-bold" id="detail_created"></div></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Mật khẩu (Hash)</label>
                            <div class="text-break small" id="detail_password" style="font-family: monospace; background: #f8f9fa; padding: 10px; border-radius: 6px; border: 1px solid #dee2e6;"></div>
                        </div>
                        <hr>
                        <h6 class="fw-bold mb-3"><i class="fas fa-map-marker-alt text-danger me-1"></i> Địa chỉ giao hàng</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3"><label class="form-label text-muted small">Mã Tỉnh/TP</label><div class="fw-bold" id="detail_province"></div></div>
                            <div class="col-md-4 mb-3"><label class="form-label text-muted small">Mã Quận/Huyện</label><div class="fw-bold" id="detail_district"></div></div>
                            <div class="col-md-4 mb-3"><label class="form-label text-muted small">Mã Phường/Xã</label><div class="fw-bold" id="detail_ward"></div></div>
                        </div>
                        <div><label class="form-label text-muted small">Địa chỉ chi tiết</label><div class="fw-bold" id="detail_address"></div></div>
                    </div>

                    <div class="tab-pane fade" id="history" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light"><tr><th>Mã ĐH</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th><th>Hành động</th></tr></thead>
                                <tbody id="order_history_body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-file-invoice-dollar text-primary me-2"></i>Chi tiết đơn hàng <span id="modal_order_id" class="text-danger"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="bg-light p-3 rounded mb-4 border">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">Thông tin giao hàng</h6>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <span class="text-muted small">Người nhận:</span> 
                            <strong id="modal_order_name"></strong>
                        </div>
                        <div class="col-md-6 mb-2">
                            <span class="text-muted small">Số điện thoại:</span> 
                            <strong id="modal_order_phone"></strong>
                        </div>
                        <div class="col-md-12 mb-2">
                            <span class="text-muted small">Địa chỉ:</span> 
                            <strong id="modal_order_address"></strong>
                        </div>
                        <div class="col-md-12">
                            <span class="text-muted small">Ghi chú:</span> 
                            <span id="modal_order_note" class="fst-italic"></span>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mb-3">Sản phẩm đã mua</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center small">
                            <tr>
                                <th width="60">Ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th width="100">Đơn giá</th>
                                <th width="80">SL</th>
                                <th width="120">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="modal_order_items_body">
                        </tbody>
                    </table>
                </div>

                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">Tạm tính:</td>
                                <td class="text-end fw-bold" id="modal_order_subtotal"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Phí vận chuyển:</td>
                                <td class="text-end fw-bold" id="modal_order_fee"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Voucher giảm:</td>
                                <td class="text-end fw-bold text-danger" id="modal_order_discount"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Điểm trừ:</td>
                                <td class="text-end fw-bold text-danger" id="modal_order_points"></td>
                            </tr>
                            <tr class="border-top">
                                <td class="fw-bold fs-6">Tổng cộng:</td>
                                <td class="text-end fw-bold fs-5 text-primary" id="modal_order_total"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/web_qlsp/Public/css/loading.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const API_BASE = '/web_qlsp/users';
    
    // BIẾN TOÀN CỤC ĐỂ LƯU DỮ LIỆU KHÁCH HÀNG (Dùng cho Modal Chi tiết)
    let globalUsers = [];

    // ==========================================
    // 1. TẢI VÀ VẼ DANH SÁCH KHÁCH HÀNG
    // ==========================================
    function loadData(query = '') {
        document.getElementById('loading-skeleton').style.display = 'table-row-group';
        document.getElementById('actual-content').style.display = 'none';

        fetch(`${API_BASE}/api_get_data?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(res => {
                const tbody = document.getElementById('actual-content');
                let html = '';

                if (res.success && res.data.length > 0) {
                    globalUsers = res.data; // Lưu lại dữ liệu
                    
                    res.data.forEach(u => {
                        const avatar = u.avatar ? `/web_qlsp/Public/Picture/users/${u.avatar}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(u.full_name)}&background=0D8ABC&color=fff`;
                        const googleBadge = u.google_id ? `<span class="badge bg-danger" style="font-size: 10px"><i class="fab fa-google"></i> Google</span>` : '';
                        const phone = u.phone ? `<div><i class="fas fa-phone text-muted me-2"></i> ${u.phone}</div>` : '';
                        
                        const dateParts = u.created_at.split(/[- :]/);
                        const createdDate = dateParts.length >= 3 ? `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}` : u.created_at;

                        html += `
                            <tr>
                                <td>#${u.id}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="${avatar}" class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;">
                                        <div>
                                            <div class="fw-bold">${u.full_name}</div>
                                            ${googleBadge}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><i class="fas fa-envelope text-muted me-2"></i> ${u.email}</div>
                                    ${phone}
                                </td>
                                <td><span class="badge bg-warning text-dark fs-6">${new Intl.NumberFormat('vi-VN').format(u.points || 0)} điểm</span></td>
                                <td>${createdDate}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info me-1" onclick="viewUserDetails(${u.id})" title="Chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(${u.id})" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = `<tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-users-slash fs-1 mb-3 d-block opacity-25"></i>Không có khách hàng nào.</td></tr>`;
                }

                tbody.innerHTML = html;
                document.getElementById('loading-skeleton').style.display = 'none';
                document.getElementById('actual-content').style.display = 'table-row-group';
            })
            .catch(err => console.error('Lỗi kết nối', err));
    }

    document.addEventListener("DOMContentLoaded", () => loadData());

    // ==========================================
    // 2. XEM CHI TIẾT & LỊCH SỬ MUA HÀNG
    // ==========================================
    function viewUserDetails(id) {
        const u = globalUsers.find(user => user.id == id);
        if (!u) return;

        const avatar = u.avatar ? `/web_qlsp/Public/Picture/users/${u.avatar}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(u.full_name)}&background=0D8ABC&color=fff`;
        const dateParts = u.created_at.split(/[- :]/);
        const createdFull = dateParts.length >= 5 ? `${dateParts[2]}/${dateParts[1]}/${dateParts[0]} ${dateParts[3]}:${dateParts[4]}` : u.created_at;

        document.getElementById('detail_avatar').src = avatar;
        document.getElementById('detail_fullname').textContent = u.full_name;
        document.getElementById('detail_email').textContent = u.email;
        document.getElementById('detail_phone').textContent = u.phone || 'Chưa cập nhật';
        document.getElementById('detail_points').textContent = new Intl.NumberFormat('vi-VN').format(u.points || 0) + ' điểm';
        document.getElementById('detail_created').textContent = createdFull;
        
        document.getElementById('detail_province').textContent = u.province_code || '--';
        document.getElementById('detail_district').textContent = u.district_code || '--';
        document.getElementById('detail_ward').textContent = u.ward_code || '--';
        document.getElementById('detail_address').textContent = u.address_detail || 'Chưa cập nhật địa chỉ chi tiết';
        document.getElementById('detail_password').textContent = u.password || 'Không có';

        const roleBadge = document.getElementById('detail_role_badge');
        if (u.role == 1) {
            roleBadge.className = 'badge bg-primary';
            roleBadge.textContent = 'Admin';
        } else {
            roleBadge.className = 'badge bg-secondary';
            roleBadge.textContent = 'Khách hàng';
        }

        document.getElementById('detail_google_badge').style.display = u.google_id ? 'inline-block' : 'none';

        new bootstrap.Modal(document.getElementById('detailUserModal')).show();

        loadOrderHistory(u.id);
    }

    function loadOrderHistory(userId) {
        const tbody = document.getElementById('order_history_body');
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm"></div> Đang tải lịch sử...</td></tr>';

        fetch(`${API_BASE}/get_order_history/${userId}`)
            .then(res => res.json())
            .then(data => {
                let html = '';
                if (data && data.length > 0) {
                    data.forEach(order => {
                        let statusBadge = '';
                        switch(order.status) {
                            case 'pending': statusBadge = '<span class="badge bg-warning text-dark">Chờ xử lý</span>'; break;
                            case 'confirmed': statusBadge = '<span class="badge bg-info text-dark">Đã xác nhận</span>'; break;
                            case 'shipping': statusBadge = '<span class="badge bg-primary">Đang giao</span>'; break;
                            case 'completed': statusBadge = '<span class="badge bg-success">Hoàn thành</span>'; break;
                            case 'cancelled': statusBadge = '<span class="badge bg-danger">Đã hủy</span>'; break;
                            default: statusBadge = `<span class="badge bg-secondary">${order.status}</span>`;
                        }

                        html += `
                            <tr>
                                <td class="fw-bold text-primary">#${order.id}</td>
                                <td>${order.created_at_format}</td>
                                <td class="fw-bold text-danger">${order.total_money_format}</td>
                                <td>${statusBadge}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails(${order.id})">
                                        <i class="fas fa-eye"></i> Xem
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="5" class="text-center py-4 text-muted"><i class="fas fa-box-open mb-2 d-block fs-3"></i>Khách hàng này chưa có đơn hàng nào.</td></tr>';
                }
                tbody.innerHTML = html;
            })
            .catch(err => {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Lỗi kết nối khi tải lịch sử.</td></tr>';
            });
    }

    // ==========================================
    // 3. XEM CHI TIẾT 1 ĐƠN HÀNG TỪ LỊCH SỬ
    // ==========================================
    function viewOrderDetails(orderId) {
        Swal.fire({
            title: 'Đang tải...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch(`${API_BASE}/get_order_details/${orderId}`)
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                if (data.info) {
                    const info = data.info;
                    document.getElementById('modal_order_id').textContent = info.id;
                    document.getElementById('modal_order_name').textContent = info.customer_name;
                    document.getElementById('modal_order_phone').textContent = info.customer_phone;
                    document.getElementById('modal_order_address').textContent = `${info.shipping_address_detail || ''}, ${info.shipping_ward || ''}, ${info.shipping_district || ''}, ${info.shipping_province || ''}`;
                    document.getElementById('modal_order_note').textContent = info.note || 'Không có';
                    
                    const formatMoney = (amount) => new Intl.NumberFormat('vi-VN').format(amount || 0) + 'đ';
                    
                    document.getElementById('modal_order_subtotal').textContent = formatMoney(info.subtotal);
                    document.getElementById('modal_order_fee').textContent = formatMoney(info.shipping_fee);
                    document.getElementById('modal_order_discount').textContent = '-' + formatMoney(info.discount_amount);
                    document.getElementById('modal_order_points').textContent = '-' + formatMoney(info.points_used);
                    document.getElementById('modal_order_total').textContent = formatMoney(info.total_money);
                }

                if (data.items && data.items.length > 0) {
                    let itemsHtml = '';
                    data.items.forEach(item => {
                        const imgUrl = item.product_image ? `/web_qlsp/Public/Picture/${item.product_image}` : 'https://via.placeholder.com/50';
                        const formatMoney = (amount) => new Intl.NumberFormat('vi-VN').format(amount || 0) + 'đ';
                        
                        itemsHtml += `
                            <tr>
                                <td class="text-center">
                                    <img src="${imgUrl}" width="50" height="50" style="object-fit:cover; border-radius:4px;">
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">${item.product_name}</div>
                                    <div class="text-muted small">Màu: ${item.color || 'N/A'} | Size: ${item.size || 'N/A'}</div>
                                </td>
                                <td class="text-center">${formatMoney(item.price)}</td>
                                <td class="text-center">${item.quantity}</td>
                                <td class="text-center text-danger fw-bold">${formatMoney(item.total)}</td>
                            </tr>
                        `;
                    });
                    document.getElementById('modal_order_items_body').innerHTML = itemsHtml;
                } else {
                    document.getElementById('modal_order_items_body').innerHTML = '<tr><td colspan="5" class="text-center">Không có sản phẩm nào</td></tr>';
                }

                // Hiện Modal (Lưu ý: nó sẽ nằm đè lên Modal chi tiết user)
                new bootstrap.Modal(document.getElementById('orderDetailModal')).show();
            })
            .catch(error => {
                Swal.fire('Lỗi', 'Không thể tải chi tiết đơn hàng', 'error');
            });
    }

    // ==========================================
    // 4. TÌM KIẾM, XUẤT EXCEL, THÊM, XÓA
    // ==========================================
    document.getElementById('formSearch').addEventListener('submit', function(e) {
        e.preventDefault();
        loadData(document.getElementById('txtSearch').value);
    });

    function resetSearch() {
        document.getElementById('txtSearch').value = '';
        loadData();
    }

    function exportExcel() {
        const q = encodeURIComponent(document.getElementById('txtSearch').value);
        window.location.href = `${API_BASE}/export_excel?q=${q}`;
    }

    document.getElementById('formAddUser').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(`${API_BASE}/api_add`, { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Thành công', text: data.message, timer: 1500 });
                    this.reset();
                    document.getElementById('closeAddModal').click();
                    loadData(document.getElementById('txtSearch').value);
                } else {
                    Swal.fire('Lỗi', data.message, 'warning');
                }
            })
            .catch(() => Swal.fire('Lỗi', 'Không thể kết nối máy chủ', 'error'));
    });

    function deleteUser(id) {
        Swal.fire({
            title: 'Xóa khách hàng?', text: "Dữ liệu không thể khôi phục!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Xóa ngay', cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${API_BASE}/api_delete/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Đã xóa', timer: 1500, showConfirmButton: false });
                            loadData(document.getElementById('txtSearch').value);
                        } else {
                            Swal.fire('Lỗi', data.message, 'error');
                        }
                    });
            }
        });
    }
</script>