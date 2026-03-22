<?php
$user = $data['user_info'] ?? null;
?>

<div id="toast-container"></div>

<script src="/web_qlsp/Public/js/send.js"></script>

<div class="profile">
    
    <div class="header_info">
        <div class="info_top">
            <div class="user_section">
                <h2 class="user_name">Chào <?= htmlspecialchars($user['full_name'] ?? '') ?></h2>
            </div>
            
            <div class="coolcash_section">
                Bạn đang có
                <div class="cash_value badge bg-warning text-dark fs-6">
                    <?= number_format($user['points'] ?? 0) ?> Điểm
                </div>
            </div>
            <footer class="review_date">
                Hạng thành viên được tạo vào ngày <?= isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : '' ?>
            </footer>
        </div>
        <div class="info_img">
            <img src="/web_qlsp/Public/Picture/users/<?= htmlspecialchars($user['avatar'] ?? 'default-avatar.png') ?>" alt="avatar">
        </div>
    </div>

    <div class="main_info">
        <form class="section_box_1">
            <h3 class="section_title">Thông tin tài khoản</h3>
            <div class="info_grid">
                <div class="info_item">
                    <label class="label">Họ và tên</label>
                    <input type="text" name="full_name" class="input_value" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                </div>
                <div class="info_item">
                    <label class="label">Số điện thoại</label>
                    <input type="text" name="phone" class="input_value" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                <div class="info_item">
                    <label class="label">Địa chỉ</label>
                    <?php
                        $province = '';
                        $district = '';
                        $ward = '';
                        // Tìm tên tỉnh/thành
                        if (isset($data['provinces']) && $data['provinces']) {
                            mysqli_data_seek($data['provinces'], 0);
                            $user_province = $user['province_code'] ?? '';
                            while ($row = mysqli_fetch_assoc($data['provinces'])) {
                                if ($row['code'] == $user_province) {
                                    $province = $row['name'];
                                    break;
                                }
                            }
                        }
                        // Tìm tên quận/huyện
                        if (isset($data['user_districts']) && $data['user_districts']) {
                            mysqli_data_seek($data['user_districts'], 0);
                            $user_district = $user['district_code'] ?? '';
                            while ($row = mysqli_fetch_assoc($data['user_districts'])) {
                                if ($row['code'] == $user_district) {
                                    $district = $row['name'];
                                    break;
                                }
                            }
                        }
                        // Tìm tên phường/xã
                        if (isset($data['user_wards']) && $data['user_wards']) {
                            mysqli_data_seek($data['user_wards'], 0);
                            $user_ward = $user['ward_code'] ?? '';
                            while ($row = mysqli_fetch_assoc($data['user_wards'])) {
                                if ($row['code'] == $user_ward) {
                                    $ward = $row['name'];
                                    break;
                                }
                            }
                        }
                        $full_address = htmlspecialchars($user['address_detail'] ?? '');
                        if ($ward) $full_address .= ', ' . $ward;
                        if ($district) $full_address .= ', ' . $district;
                        if ($province) $full_address .= ', ' . $province;
                    ?>
                    <input type="text" name="address_detail" class="input_value" value="<?= $full_address ?>" readonly>
                </div>
            </div>
            <a href="#" type="submit" class="btn_update"  data-bs-toggle="modal" data-bs-target="#updateUserModal">CẬP NHẬT</a>
        </form>

        <form class="section_box_2">
            <h3 class="section_title">Thông tin đăng nhập</h3>
            <div class="info_grid">
                <div class="info_item">
                    <label class="label">Email</label>
                    <input type="email" name="email" class="input_value" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                </div>
                <div class="info_item">
                    <label class="label">Mật khẩu</label>
                    <input type="password" name="password" class="input_value" value="12345678" placeholder="••••••••••••••">
                </div>
            </div>
            <a  href="#" type="submit" class="btn_chagepass" data-bs-toggle="modal" data-bs-target="#changePasswordModal"> <i class="fas fa-key me-2"></i>ĐỔI MẬT KHẨU</a>
        </form>
    </div>
</div>

<div class="modal fade" id="updateUserModal" tabindex="-1" aria-labelledby="updateUserModalLabel" aria-hidden="true" style="border-radius: 0.5rem;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h4 class="modal-title fw-bold" id="updateUserModalLabel">Chỉnh sửa thông tin tài khoản</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0"> 
                <form action="/web_qlsp/profile/update" method="POST" enctype="multipart/form-data">
                    <div class="row g-0">
                        
                        <div class="col-md-4 avatar_img text-center border-end">
                            <div class="avatar-preview-container">
                                <img src="<?= !empty($user['avatar']) ? '/web_qlsp/Public/Picture/users/'.$user['avatar'] : '/web_qlsp/Public/Picture/users/default-avatar.png' ?>" 
                                     id="previewAvatar" class="rounded-circle" width="160" height="160">
                                <label for="upload-avatar" class="upload-label">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" id="upload-avatar" name="avatar" hidden accept="image/*" onchange="previewImage(this)">
                            </div>
                            <h6 class="fw-bold text-dark mt-2">Ảnh đại diện</h6>
                            <p class="small text-muted px-3">Bấm vào camera để thay đổi ảnh</p>
                        </div>

                        <div class="col-md-8 info_user">
                            <div class="mb-3">
                                <label class="form-label fw-bold">HỌ VÀ TÊN</label>
                                <input type="text" name="full_name" class="form-control" 
                                    value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">SỐ ĐIỆN THOẠI</label>
                                <input type="tel" name="phone" class="form-control" 
                                    value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                            </div>

                            <div class="section-title fw-bold">ĐỊA CHỈ GIAO HÀNG</div>
                            
                            <div class="row g-2">
                                <div class="col-md-4 mb-2">
                                    <label class="mini-label d-block">Tỉnh / Thành</label>
                                    <select name="province_code" class="form-select" id="province" required>
                                        <option value="">Chọn Tỉnh</option>
                                        <?php
                                        if (isset($data['provinces']) && $data['provinces'] !== false) {
                                            mysqli_data_seek($data['provinces'], 0);
                                            $u_province = $user['province_code'] ?? '';
                                            while ($row = mysqli_fetch_assoc($data['provinces'])) {
                                                $sel = ($u_province == $row['code']) ? 'selected' : '';
                                                echo '<option value="' . $row['code'] . '" '.$sel.'>' . $row['name'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="mini-label d-block">Quận / Huyện</label>
                                    <select name="district_code" class="form-select" id="district" required>
                                       <option value="">Chọn Quận</option>
                                       <?php
                                       if (isset($data['user_districts']) && $data['user_districts']) {
                                            mysqli_data_seek($data['user_districts'], 0);
                                            $u_district = $user['district_code'] ?? '';
                                            while ($row = mysqli_fetch_assoc($data['user_districts'])) {
                                                $sel = ($u_district == $row['code']) ? 'selected' : '';
                                                echo '<option value="' . $row['code'] . '" '.$sel.'>' . $row['name'] . '</option>';
                                            }
                                       }
                                       ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="mini-label d-block">Phường / Xã</label>
                                    <select name="ward_code" class="form-select" id="ward" required>
                                       <option value="">Chọn Xã</option>
                                       <?php
                                       if (isset($data['user_wards']) && $data['user_wards']) {
                                            mysqli_data_seek($data['user_wards'], 0);
                                            $u_ward = $user['ward_code'] ?? '';
                                            while ($row = mysqli_fetch_assoc($data['user_wards'])) {
                                                $sel = ($u_ward == $row['code']) ? 'selected' : '';
                                                echo '<option value="' . $row['code'] . '" '.$sel.'>' . $row['name'] . '</option>';
                                            }
                                       }
                                       ?>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="mini-label d-block">Địa chỉ chi tiết</label>
                                    <input type="text" name="address_detail" class="form-control" 
                                        placeholder="Số nhà, tên đường..." value="<?= htmlspecialchars($user['address_detail'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="text-center mt-5 mb-4">
                                <button type="submit" name="btn_update" class="btn-save-change fw-bold">
                                    LƯU THAY ĐỔI
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 0.5rem; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <button type="button" class="btn-close custom-close-btn" data-bs-dismiss="modal" aria-label="Close" style="position: absolute; right: 15px; top: 15px; z-index: 10;"></button>
            
            <div class="modal-body p-5">
                <form method="POST" action="/web_qlsp/profile/change_password" autocomplete="off">
                    
                    <h5 class="fw-bold mb-4 text-center" style="letter-spacing: 1px; color: #000;">ĐỔI MẬT KHẨU</h5>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">MẬT KHẨU HIỆN TẠI</label>
                        <div class="position-relative">
                            <input type="password" name="current_password" id="curr_pass" class="form-control pe-5" placeholder="Nhập mật khẩu cũ" required>
                            <span class="password-toggle-icon" onclick="togglePassword('curr_pass', this)">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">MẬT KHẨU MỚI</label>
                        <div class="position-relative">
                            <input type="password" name="new_password" id="new_pass" class="form-control pe-5" placeholder="Tối thiểu 6 ký tự" required>
                            <span class="password-toggle-icon" onclick="togglePassword('new_pass', this)">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">XÁC NHẬN MẬT KHẨU MỚI</label>
                        <div class="position-relative">
                            <input type="password" name="confirm_password" id="conf_pass" class="form-control pe-5" placeholder="Nhập lại mật khẩu mới" required>
                            <span class="password-toggle-icon" onclick="togglePassword('conf_pass', this)">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    <small class="text-muted d-block mb-4 text-center" style="font-size: 12px; background: #f8f9fa; padding: 10px; border-radius: 5px;">
                        <i class="fas fa-info-circle me-1"></i> Lưu ý: Mật khẩu mới nên bao gồm chữ và số để bảo mật hơn.
                    </small>

                    <div class="text-center">
                        <button type="submit" class="btn-save-custom w-100 py-2 fw-bold" style="background: #000; color: #fff; border: none; border-radius: 5px; transition: 0.3s;">
                            LƯU THAY ĐỔI
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/web_qlsp/Public/Css/profile.css?v=1.1">
<link rel="stylesheet" href="/web_qlsp/Public/Css/profile_edit.css">
<script src="/web_qlsp/Public/Js/province.js"></script>
<script src="/web_qlsp/Public/Js/image.js"></script>
<script src="/web_qlsp/Public/Js/result.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    /* CSS cho Toast Container giống bên Add to Cart */
    #toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    /* Nếu bạn chưa có CSS cho custom-toast trong file profile.css thì thêm vào đây */
    .custom-toast {
        min-width: 300px;
        background: #fff;
        border-radius: 8px;
        padding: 16px 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideInLeft 0.3s ease forwards, fadeOut 0.3s ease 3s forwards;
        border-left: 5px solid #333;
    }
    .custom-toast.success { border-left-color: #2ecc71; }
    .custom-toast.error { border-left-color: #e74c3c; }
    
    .toast-icon { font-size: 20px; }
    .custom-toast.success .toast-icon { color: #2ecc71; }
    .custom-toast.error .toast-icon { color: #e74c3c; }
    
    .toast-message { font-size: 14px; font-weight: 500; color: #333; }
    
    @keyframes slideInLeft {
        from { opacity: 0; transform: translateX(100%); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeOut {
        to { opacity: 0; visibility: hidden; }
    }
</style>