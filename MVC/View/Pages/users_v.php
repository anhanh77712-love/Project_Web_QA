<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Danh sách Khách hàng</h4>
        <p class="text-muted small mb-0">Quản lý thông tin người dùng của hệ thống</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <form class="d-flex align-items-center" action="/web_qlsp/users/search" method="get">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Tìm theo tên khách hàng" value="<?php echo isset($data['search_q']) ? htmlspecialchars($data['search_q']) : ''; ?>" />
            </div>
            <button type="submit" class="btn btn-primary ms-2">Tìm</button>
        </form>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Chi tiết Khách hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <img id="detail_avatar" src="" class="rounded-circle mb-3" width="120" height="120">
                    <h5 id="detail_fullname" class="fw-bold mb-1"></h5>
                    <span id="detail_role_badge" class="badge"></span>
                    <span id="detail_google_badge" class="badge bg-danger ms-1" style="display: none;">
                        <i class="fab fa-google"></i> Google Account
                    </span>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Email</label>
                        <div class="fw-bold" id="detail_email"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Số điện thoại</label>
                        <div class="fw-bold" id="detail_phone"></div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label text-muted small">Mật khẩu (Hash)</label>
                        <div class="text-break small" id="detail_password" style="font-family: monospace; background: #f5f5f5; padding: 8px; border-radius: 4px;"></div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Điểm tích lũy</label>
                        <div>
                            <span class="badge bg-warning text-dark fs-6" id="detail_points"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Ngày tham gia</label>
                        <div class="fw-bold" id="detail_created"></div>
                    </div>
                </div>
                
                <hr>
                
                <h6 class="fw-bold mb-3">Địa chỉ</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small">Mã Tỉnh/TP</label>
                        <div id="detail_province"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small">Mã Quận/Huyện</label>
                        <div id="detail_district"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small">Mã Phường/Xã</label>
                        <div id="detail_ward"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Địa chỉ chi tiết</label>
                    <div id="detail_address"></div>
                </div>
            </div>
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
<?php if(isset($_SESSION['status_msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            checkFlashMessage("<?php echo $_SESSION['status_msg']; ?>");
        });
    </script>
    <?php unset($_SESSION['status_msg']); ?>
<?php endif; ?>