<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Danh sách Khách hàng</h4>
        <p class="text-muted small mb-0">Quản lý thông tin người dùng của hệ thống</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <form class="d-flex align-items-center" action="/web_qlsp/users/search" method="get">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Tìm theo tên..." value="<?php echo isset($data['search_q']) ? htmlspecialchars($data['search_q']) : ''; ?>" />
            </div>
            <button type="submit" class="btn btn-primary ms-2">Tìm</button>
        </form>
        
        <button class="btn btn-primary shadow-sm ms-2" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus me-2"></i> Thêm mới
        </button>

        <a href="/web_qlsp/users/export_excel" class="btn btn-success shadow-sm ms-2">
            <i class="fas fa-file-excel me-2"></i> Xuất Excel
        </a>
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
                <?php for ($i = 0; $i < 5; $i++): // Hiển thị giả lập 5 dòng ?>
                <tr>
                    <td><div class="skeleton" style="width: 30px; height: 15px;"></div></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="skeleton rounded-circle me-3" style="width: 40px; height: 40px;"></div>
                            <div>
                                <div class="skeleton" style="width: 120px; height: 15px; display: block; margin-bottom: 5px;"></div>
                                <div class="skeleton" style="width: 60px; height: 12px; display: block;"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="skeleton" style="width: 150px; height: 12px; display: block; margin-bottom: 5px;"></div>
                        <div class="skeleton" style="width: 100px; height: 12px; display: block;"></div>
                    </td>
                    <td><div class="skeleton" style="width: 80px; height: 20px;"></div></td>
                    <td><div class="skeleton" style="width: 80px; height: 15px;"></div></td>
                    <td>
                        <div class="skeleton" style="width: 30px; height: 30px; margin-right: 5px;"></div>
                        <div class="skeleton" style="width: 30px; height: 30px;"></div>
                    </td>
                </tr>
                <?php endfor; ?>
            </tbody>




            <tbody id="actual-content" style="display: none;">
                <?php
                if (isset($data['users_list']) && mysqli_num_rows($data['users_list']) > 0) {
                    foreach ($data['users_list'] as $u) {
                ?>
                        <tr>
                            <td>#<?php echo $u['id']; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php
                                    // Trường hợp 1: Có avatar trong DB -> lấy từ thư mục users
                                    // Trường hợp 2: Không có avatar -> tự động tạo từ API ui-avatars
                                    $avatar = !empty($u['avatar'])
                                        ? '/web_qlsp/Public/Picture/users/' . $u['avatar']
                                        : 'https://ui-avatars.com/api/?name=' . urlencode($u['full_name']) . '&background=0D8ABC&color=fff';
                                    ?>
                                    <img src="<?php echo $avatar; ?>" class="rounded-circle me-3" width="40" height="40">
                                    <div>
                                        <div class="fw-bold"><?php echo $u['full_name']; ?></div>
                                        <?php if (!empty($u['google_id'])) { ?>
                                            <span class="badge bg-danger" style="font-size: 10px">
                                                <i class="fab fa-google"></i> Google
                                            </span>
                                        <?php } ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><i class="fas fa-envelope text-muted me-2"></i> <?php echo $u['email']; ?></div>
                                <?php if (!empty($u['phone'])) { ?>
                                    <div><i class="fas fa-phone text-muted me-2"></i> <?php echo $u['phone']; ?></div>
                                <?php } ?>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark fs-6">
                                    <?php echo number_format($u['points']); ?> điểm
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info me-1 btn-detail"
                                    data-bs-toggle="modal"
                                    data-bs-target="#detailUserModal"
                                    data-id="<?php echo $u['id']; ?>"
                                    data-fullname="<?php echo $u['full_name']; ?>"
                                    data-email="<?php echo $u['email']; ?>"
                                    data-phone="<?php echo $u['phone']; ?>"
                                    data-points="<?php echo $u['points']; ?>"
                                    data-role="<?php echo $u['role']; ?>"
                                    data-province="<?php echo $u['province_code']; ?>"
                                    data-district="<?php echo $u['district_code']; ?>"
                                    data-ward="<?php echo $u['ward_code']; ?>"
                                    data-address="<?php echo htmlspecialchars($u['address_detail']); ?>"
                                    data-googleid="<?php echo $u['google_id']; ?>"
                                    data-avatar="<?php echo $avatar; ?>"
                                    data-created="<?php echo date('d/m/Y H:i', strtotime($u['created_at'])); ?>"
                                    data-password="<?php echo $u['password']; ?>"
                                    title="Chi tiết">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="confirmDelete('/web_qlsp/users/delete/<?php echo $u['id']; ?>')" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Không có khách hàng nào.
                        </td>
                    </tr>
                <?php } ?>

            </tbody>
        </table>
    </div>
</div>

<!-- DETAIL USER MODAL -->
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
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-selected="true">
                            <i class="fas fa-user text-primary me-1"></i> Thông tin cá nhân
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab" aria-selected="false">
                            <i class="fas fa-shopping-cart text-success me-1"></i> Lịch sử mua hàng
                        </button>
                    </li>
                </ul>

                <div class="tab-content p-4" id="userTabContent">
                    
                    <div class="tab-pane fade show active" id="info" role="tabpanel" tabindex="0">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small"><i class="fas fa-envelope me-1"></i> Email</label>
                                <div class="fw-bold" id="detail_email"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small"><i class="fas fa-phone me-1"></i> Số điện thoại</label>
                                <div class="fw-bold" id="detail_phone"></div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small"><i class="fas fa-star text-warning me-1"></i> Điểm tích lũy</label>
                                <div><span class="badge bg-warning text-dark fs-6" id="detail_points"></span></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small"><i class="fas fa-calendar-alt me-1"></i> Ngày tham gia</label>
                                <div class="fw-bold" id="detail_created"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small"><i class="fas fa-key me-1"></i> Mật khẩu (Hash)</label>
                            <div class="text-break small" id="detail_password" style="font-family: monospace; background: #f8f9fa; padding: 10px; border-radius: 6px; border: 1px solid #dee2e6;"></div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="fw-bold mb-3"><i class="fas fa-map-marker-alt text-danger me-1"></i> Địa chỉ giao hàng</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small">Mã Tỉnh/TP</label>
                                <div class="fw-bold" id="detail_province"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small">Mã Quận/Huyện</label>
                                <div class="fw-bold" id="detail_district"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small">Mã Phường/Xã</label>
                                <div class="fw-bold" id="detail_ward"></div>
                            </div>
                        </div>
                        <div>
                            <label class="form-label text-muted small">Địa chỉ chi tiết</label>
                            <div class="fw-bold" id="detail_address"></div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="history" role="tabpanel" tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã ĐH</th>
                                        <th>Ngày đặt</th>
                                        <th>Tổng tiền</th>
                                        <th>Thanh toán</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody id="order_history_body">
                                    </tbody>
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

<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="/web_qlsp/users/add" method="POST">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-plus text-primary me-2"></i>Thêm Khách hàng mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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


<link rel="stylesheet" href="/web_qlsp/Public/css/loading.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="/web_qlsp/Public/js/user.js"></script>
<script src="/web_qlsp/Public/js/result.js"></script>
<script src="/web_qlsp/Public/js/loading.js"></script>
<script src="/web_qlsp/Public/js/user_details.js"></script>
<?php if(isset($_SESSION['status_msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let msg = "<?php echo $_SESSION['status_msg']; ?>";
            
            // Xử lý các mã lỗi riêng biệt từ Model trả về
            if (msg === 'email_existed') {
                Swal.fire('Thất bại', 'Email này đã được sử dụng cho tài khoản khác!', 'warning');
            } else if (msg === 'phone_existed') {
                Swal.fire('Thất bại', 'Số điện thoại này đã được sử dụng!', 'warning');
            } else if (msg === 'success') {
                Swal.fire('Thành công', 'Đã thêm khách hàng mới!', 'success');
            } else {
                checkFlashMessage(msg); // Hàm mặc định của bạn
            }
        });
    </script>
    <?php unset($_SESSION['status_msg']); ?>
<?php endif; ?>