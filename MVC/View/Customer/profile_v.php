<?php
$user = $data['user_info'] ?? null;
?>

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
        <div class="section_box_1">
            <h3 class="section_title">Thông tin tài khoản</h3>
            <div class="info_grid">
                <div class="info_item">
                    <label class="label">Họ và tên</label>
                    <input type="text" class="input_value" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" readonly>
                </div>
                <div class="info_item">
                    <label class="label">Số điện thoại</label>
                    <input type="text" class="input_value" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" readonly>
                </div>
                <div class="info_item">
                    <label class="label">Địa chỉ</label>
                    <?php
                        $province = ''; $district = ''; $ward = '';
                        if (isset($data['provinces']) && $data['provinces']) {
                            mysqli_data_seek($data['provinces'], 0);
                            $user_province = $user['province_code'] ?? '';
                            while ($row = mysqli_fetch_assoc($data['provinces'])) {
                                if ($row['code'] == $user_province) { $province = $row['name']; break; }
                            }
                        }
                        if (isset($data['user_districts']) && $data['user_districts']) {
                            mysqli_data_seek($data['user_districts'], 0);
                            $user_district = $user['district_code'] ?? '';
                            while ($row = mysqli_fetch_assoc($data['user_districts'])) {
                                if ($row['code'] == $user_district) { $district = $row['name']; break; }
                            }
                        }
                        if (isset($data['user_wards']) && $data['user_wards']) {
                            mysqli_data_seek($data['user_wards'], 0);
                            $user_ward = $user['ward_code'] ?? '';
                            while ($row = mysqli_fetch_assoc($data['user_wards'])) {
                                if ($row['code'] == $user_ward) { $ward = $row['name']; break; }
                            }
                        }
                        $full_address = htmlspecialchars($user['address_detail'] ?? '');
                        if ($ward) $full_address .= ', ' . $ward;
                        if ($district) $full_address .= ', ' . $district;
                        if ($province) $full_address .= ', ' . $province;
                    ?>
                    <input type="text" class="input_value" value="<?= $full_address ?>" readonly>
                </div>
            </div>
            <a href="#" class="btn_update" data-bs-toggle="modal" data-bs-target="#updateUserModal">CẬP NHẬT</a>
        </div>

        <div class="section_box_2">
            <h3 class="section_title">Thông tin đăng nhập</h3>
            <div class="info_grid">
                <div class="info_item">
                    <label class="label">Email</label>
                    <input type="email" class="input_value" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                </div>
                <div class="info_item">
                    <label class="label">Mật khẩu</label>
                    <input type="password" class="input_value" value="12345678" readonly placeholder="••••••••••••••">
                </div>
            </div>
            <a href="#" class="btn_chagepass" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                <i class="fas fa-key me-2"></i>ĐỔI MẬT KHẨU
            </a>
        </div>
    </div>
</div>

<div class="modal fade" id="updateUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0" style="border-radius: 0.5rem; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header border-0 pb-0">
                <h4 class="modal-title fw-bold">Chỉnh sửa thông tin tài khoản</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0"> 
                <form id="formUpdateProfile">
                    <div class="row g-0">
                        <div class="col-md-4 avatar_img text-center border-end py-4">
                            <div class="avatar-preview-container" style="position: relative; display: inline-block;">
                                <img src="<?= !empty($user['avatar']) ? '/web_qlsp/Public/Picture/users/'.$user['avatar'] : '/web_qlsp/Public/Picture/users/default-avatar.png' ?>" 
                                     id="previewAvatar" class="rounded-circle shadow-sm" width="160" height="160" style="object-fit: cover;">
                                
                                <label for="upload-avatar" class="upload-label bg-primary text-white rounded-circle shadow" style="position: absolute; bottom: 5px; right: 15px; width: 40px; height: 40px; line-height: 40px; cursor: pointer;">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" id="upload-avatar" name="avatar" hidden accept="image/*" onchange="previewImage(this)">
                            </div>
                            <h6 class="fw-bold text-dark mt-3">Ảnh đại diện</h6>
                            <p class="small text-muted px-3">Bấm vào camera để thay đổi ảnh</p>
                        </div>

                        <div class="col-md-8 info_user p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">HỌ VÀ TÊN</label>
                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">SỐ ĐIỆN THOẠI</label>
                                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                            </div>

                            <div class="section-title fw-bold small mt-4 mb-2">ĐỊA CHỈ GIAO HÀNG</div>
                            <div class="row g-2">
                                <div class="col-md-4 mb-2">
                                    <label class="mini-label d-block text-muted small">Tỉnh / Thành</label>
                                    <select name="province_code" class="form-select" id="province" required>
                                        <option value="">Chọn Tỉnh</option>
                                        <?php
                                        if (isset($data['provinces']) && $data['provinces']) {
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
                                    <label class="mini-label d-block text-muted small">Quận / Huyện</label>
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
                                    <label class="mini-label d-block text-muted small">Phường / Xã</label>
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
                                    <label class="mini-label d-block text-muted small">Địa chỉ chi tiết</label>
                                    <input type="text" name="address_detail" class="form-control" placeholder="Số nhà, tên đường..." value="<?= htmlspecialchars($user['address_detail'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-dark w-100 py-2 fw-bold">LƯU THAY ĐỔI</button>
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
            <button type="button" class="btn-close custom-close-btn" data-bs-dismiss="modal" style="position: absolute; right: 15px; top: 15px; z-index: 10;"></button>
            
            <div class="modal-body p-5">
                <form id="formChangePassword">
                    <h5 class="fw-bold mb-4 text-center" style="letter-spacing: 1px; color: #000;">ĐỔI MẬT KHẨU</h5>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">MẬT KHẨU HIỆN TẠI</label>
                        <div class="position-relative">
                            <input type="password" name="current_password" id="curr_pass" class="form-control pe-5" placeholder="Nhập mật khẩu cũ" required>
                            <span class="password-toggle-icon position-absolute top-50 end-0 translate-middle-y me-3" style="cursor: pointer;" onclick="togglePassword('curr_pass', this)">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">MẬT KHẨU MỚI</label>
                        <div class="position-relative">
                            <input type="password" name="new_password" id="new_pass" class="form-control pe-5" placeholder="Tối thiểu 6 ký tự" required minlength="6">
                            <span class="password-toggle-icon position-absolute top-50 end-0 translate-middle-y me-3" style="cursor: pointer;" onclick="togglePassword('new_pass', this)">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">XÁC NHẬN MẬT KHẨU MỚI</label>
                        <div class="position-relative">
                            <input type="password" name="confirm_password" id="conf_pass" class="form-control pe-5" placeholder="Nhập lại mật khẩu mới" required minlength="6">
                            <span class="password-toggle-icon position-absolute top-50 end-0 translate-middle-y me-3" style="cursor: pointer;" onclick="togglePassword('conf_pass', this)">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    <small class="text-muted d-block mb-4 text-center" style="font-size: 12px; background: #f8f9fa; padding: 10px; border-radius: 5px;">
                        <i class="fas fa-info-circle me-1"></i> Lưu ý: Mật khẩu mới nên bao gồm chữ và số để bảo mật hơn.
                    </small>

                    <div class="text-center">
                        <button type="submit" class="btn btn-dark w-100 py-2 fw-bold">LƯU THAY ĐỔI</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/web_qlsp/Public/Css/profile.css?v=1.1">
<link rel="stylesheet" href="/web_qlsp/Public/Css/profile_edit.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // 1. AJAX LOAD QUẬN / HUYỆN / XÃ
    $('#province').change(function() {
        var p_code = $(this).val();
        if(p_code != "") {
            $.ajax({
                url: "/web_qlsp/profile/get_districts/" + p_code,
                method: "GET",
                success: function(data) {
                    $('#district').html(data);
                    $('#ward').html('<option value="">Chọn Phường/Xã</option>');
                }
            });
        }
    });

    $('#district').change(function() {
        var d_code = $(this).val();
        if(d_code != "") {
            $.ajax({
                url: "/web_qlsp/profile/get_wards/" + d_code,
                method: "GET",
                success: function(data) {
                    $('#ward').html(data);
                }
            });
        }
    });

    // 2. PREVIEW ẢNH ĐẠI DIỆN TRƯỚC KHI LƯU
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewAvatar').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // 3. ẨN/HIỆN MẬT KHẨU
    function togglePassword(inputId, iconElement) {
        var input = document.getElementById(inputId);
        var icon = iconElement.querySelector('i');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // 4. GỬI FORM CẬP NHẬT HỒ SƠ NGẦM BẰNG AJAX
    document.getElementById('formUpdateProfile').addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({ title: 'Đang lưu...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        fetch('/web_qlsp/profile/api_update', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Thành công', text: data.message, timer: 1500, showConfirmButton: false })
                .then(() => {
                    // Load lại trang nhẹ nhàng để Navbar Header cũng được cập nhật ảnh đại diện/tên mới
                    window.location.reload();
                });
            } else {
                Swal.fire('Lỗi', data.message, 'error');
            }
        }).catch(() => Swal.fire('Lỗi', 'Không thể kết nối với máy chủ', 'error'));
    });

    // 5. GỬI FORM ĐỔI MẬT KHẨU NGẦM BẰNG AJAX
    document.getElementById('formChangePassword').addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({ title: 'Đang xử lý...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        fetch('/web_qlsp/profile/api_change_password', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // 1. Đóng Modal cực kỳ an toàn bằng cách "bấm hộ" nút X
                const closeBtn = document.querySelector('#changePasswordModal .btn-close');
                if (closeBtn) closeBtn.click();
                
                // 2. Chống kẹt màn hình đen thủ công (Dự phòng cho Bootstrap)
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style = '';

                // 3. Báo thành công
                Swal.fire({ 
                    icon: 'success', 
                    title: 'Thành công', 
                    text: data.message, 
                    timer: 1500, 
                    showConfirmButton: false 
                }).then(() => {
                    // Tải lại trang để xóa sạch form và làm mới session
                    window.location.reload();
                });
                
            } else {
                Swal.fire('Thất bại', data.message, 'warning');
            }
        }).catch(() => Swal.fire('Lỗi', 'Không thể kết nối với máy chủ', 'error'));
    });
</script>