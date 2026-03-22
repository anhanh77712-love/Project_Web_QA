<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Coolmate Clone' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/web_qlsp/Public/Css/style.css?v=1.1">
    <link rel="stylesheet" href="/web_qlsp/Public/Css/contact.css?v=1.0">
    <link rel="stylesheet" href="/web_qlsp/Public/Css/nam.css?v=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

</head>
<body>
    



<!-- ===== NAVBAR ===== -->
<nav class="navbar navbar-expand-lg sticky-top bg-white">
    <div class="container-fluid px-lg-5">
        <a class="navbar-brand me-4" href="/web_qlsp/home">
            <span class="fw-bold" style="font-size: 22px; letter-spacing: -1.5px;">COOLMATE</span><span style="color:#2f5acf; font-weight: 900;">.ME</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item"><a class="nav-link fw-bold" href="/web_qlsp/product_list_customer?filter=new">NEW</a></li>
                <li class="nav-item"><a class="nav-link fw-bold " href="/web_qlsp/product_list_customer?filter=bestseller" >BÁN CHẠY</a></li>

                <li class="nav-item has-mega-menu">
                <a class="nav-link" href="/web_qlsp/product_list_customer">
                    NAM <i class="fas fa-chevron-down ms-1" style="font-size: 10px;"></i>
                </a>

                <div class="dropdown-menu mega-menu">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-2">
                                <a href="/web_qlsp/product_list_customer" class="mega-title">
                                    TẤT CẢ SẢN PHẨM <i class="fas fa-arrow-right"></i>
                                </a>
                                <ul class="mega-list mt-3">
                                    <li><a href="/web_qlsp/product_list_customer?filter=new" class="fw-bold text-primary">Sản phẩm mới</a></li>
                                    <li><a href="/web_qlsp/product_list_customer?filter=bestseller" class="fw-bold">Bán chạy nhất</a></li>
                                </ul>
                            </div>

                            <?php
                            // Build two columns: ÁO NAM and QUẦN NAM using category name prefixes
                            $shirtCats = [];
                            $pantCats = [];
                            if (isset($data['menu_categories']) && mysqli_num_rows($data['menu_categories']) > 0) {
                                mysqli_data_seek($data['menu_categories'], 0);
                                while ($cat = mysqli_fetch_assoc($data['menu_categories'])) {
                                    $name = trim($cat['name']);
                                    $slug = $cat['slug'];
                                    // Match Vietnamese prefixes (case-insensitive)
                                    $isShirt = (mb_stripos($name, 'Áo', 0, 'UTF-8') === 0) || (mb_stripos($name, 'Ao', 0, 'UTF-8') === 0);
                                    $isPant  = (mb_stripos($name, 'Quần', 0, 'UTF-8') === 0) || (mb_stripos($name, 'Quan', 0, 'UTF-8') === 0);
                                    if ($isShirt) {
                                        $shirtCats[] = ['name' => $name, 'slug' => $slug];
                                    } elseif ($isPant) {
                                        $pantCats[] = ['name' => $name, 'slug' => $slug];
                                    }
                                }
                            }
                            // Render ÁO NAM column
                            ?>
                            <div class="col-md-2">
                                <a href="/web_qlsp/product_list_customer?search=Áo" class="mega-title">
                                    ÁO NAM <i class="fas fa-arrow-right"></i>
                                </a>
                                <ul class="mega-list">
                                    <?php
                                    $limit = 12;
                                    $count = 0;
                                    foreach ($shirtCats as $c) {
                                        if ($count++ >= $limit) break;
                                        $cname = htmlspecialchars($c['name']);
                                        $cslug = urlencode($c['slug']);
                                        echo "<li><a href=\"/web_qlsp/product_list_customer?category={$cslug}\">{$cname}</a></li>";
                                    }
                                    ?>
                                </ul>
                            </div>

                            <?php // Render QUẦN NAM column ?>
                            <div class="col-md-2">
                                <a href="/web_qlsp/product_list_customer?search=Quần" class="mega-title">
                                    QUẦN NAM <i class="fas fa-arrow-right"></i>
                                </a>
                                <ul class="mega-list">
                                    <?php
                                    $limit = 12;
                                    $count = 0;
                                    foreach ($pantCats as $c) {
                                        if ($count++ >= $limit) break;
                                        $cname = htmlspecialchars($c['name']);
                                        $cslug = urlencode($c['slug']);
                                        echo "<li><a href=\"/web_qlsp/product_list_customer?category={$cslug}\">{$cname}</a></li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                   <div class="mega-menu-footer mt-4 py-3 bg-light"> <div class="container">
        <div class="d-flex align-items-center">
            <span class="footer-label text-secondary fw-bold border-end pe-3 me-3 text-nowrap">
                BỘ SƯU TẬP
            </span>
            
            <div class="footer-links d-flex flex-wrap align-items-center">
                <?php
                // ... (Code PHP của bạn giữ nguyên) ...
                
                // Ở đoạn echo thẻ a, bạn nên thêm class để đẹp hơn:
                // echo "<a class='text-uppercase text-muted text-decoration-none fw-bold me-4' href=\"...\">{$cname}</a>";
                
                // Code PHP gốc của bạn:
                require_once './MVC/Model/product_m.php';
                $col_model = new product_m();
                $collections_rs = $col_model->collections_selectAll();
                
                if ($collections_rs && mysqli_num_rows($collections_rs) > 0) {
                    mysqli_data_seek($collections_rs, 0);
                    $limit = 8;
                    $i = 0;
                    while(($col = mysqli_fetch_assoc($collections_rs)) && $i < $limit) {
                        $i++;
                        $cname = htmlspecialchars($col['name']);
                        $cslug = urlencode($col['slug']);
                        
                        // Sửa dòng echo này để thêm class Bootstrap:
                        echo "<a href=\"/web_qlsp/product_list_customer?collection={$cslug}\" class=\"text-uppercase text-muted text-decoration-none fw-bold me-4\" style=\"font-size: 13px;\">{$cname}</a>";
                    }
                } else {
                    echo '<a href="#" class="text-muted">Chưa có bộ sưu tập</a>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
                </div>
            </li>

                <li class="nav-item position-relative">
                    <a class="nav-link fw-bold text-danger" href="/web_qlsp/product_list_customer?sale=1" style="color: #ff0000 !important;">SALE</a>
                </li>
            </ul>
        </div>

        <div class="d-flex align-items-center nav-right-zone">
            <form class="search-coolmate me-3 d-none d-xl-flex" action="/web_qlsp/product_list_customer" method="GET">
                <input type="text" name="search" placeholder="Tìm kiếm..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>

           <div class="user-auth-area me-2">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="dropdown">
                <a href="#" class="user-logged-link dropdown-toggle no-caret" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php
                        $avatar = '';
                        if (isset($data['user_info']['avatar'])) {
                            $avatar = $data['user_info']['avatar'];
                        } elseif (isset($_SESSION['user_avatar'])) {
                            $avatar = $_SESSION['user_avatar'];
                        }
                        
                        if (!empty($avatar) && $avatar !== 'default-avatar.png') {
                            echo '<img src="/web_qlsp/Public/Picture/users/' . htmlspecialchars($avatar) . '" alt="avatar" class="rounded-circle" style="width:30px;height:30px;object-fit:cover; border: 1px solid #eee;">';
                        } else {
                            echo '<i class="far fa-user fs-5" style="color: #000;"></i>';
                        }
                    ?>
                    <span class="user-name-text d-none d-md-inline"><?php echo $_SESSION['user_name']; ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                        <li><a class="dropdown-item py-2 text-danger fw-bold" href="/web_qlsp/overview">Quản trị hệ thống</a></li>
                        <li><hr class="dropdown-divider"></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item py-2" href="/web_qlsp/profile"><i class="fas fa-user-circle me-2"></i>Hồ sơ cá nhân</a></li>
                    <li><a class="dropdown-item py-2" href="/web_qlsp/your_order"><i class="fas fa-shopping-bag me-2"></i>Đơn hàng của tôi</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 text-muted" href="/web_qlsp/login/logout"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                </ul>
            </div>
        <?php else: ?>
            <div class="auth-buttons small fw-bold">
                <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="btnok">Đăng nhập</a>
                <span class="mx-1 text-muted" style="font-weight: normal;">/</span>
                <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" class="btnok">Đăng ký</a>
            </div>
        <?php endif; ?>
    </div>

            <div class="cart-wrapper ms-2">
                <a href="/web_qlsp/cart" class="cart-icon-btn position-relative">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-badge-new" id="cart-count">
                        <?php
                            $cart_count = 0;
                            if (isset($_SESSION['cart'])) {
                                foreach ($_SESSION['cart'] as $item) {
                                    $cart_count += $item['quantity'];
                                }
                            }
                            echo $cart_count;
                        ?>
                    </span>
                </a>
            </div>
        </div> </div>
</nav>
<!-- ===== CONTENT ===== -->
<main class="content" style=" background: #FFFFFF">
    <div class="main" style="margin-left: 80px; margin-right: 80px;  background-color: none; padding-top: 30px; padding-bottom: 30px;">
        <?php
        // Nội dung view con sẽ được nhúng tại đây
        include_once "./MVC/View/Customer/" . $data['Page'] . ".php";
    ?>
    </div>
</main>
<div class="contact-container" id="contactWrapper">
    <a href="https://chat.zalo.me/" target="_blank" class="contact__item item-zalo">Zalo</a>

    <div class="contact__item item-phone" data-phone="0987.654.321">
        <i class="fa-solid fa-phone"></i>
    </div>

    <div class="contact__item main-btn" id="toggleBtn">
        <i class="fa-solid fa-message icon-msg"></i>
        <span class="icon-close">X</span>
    </div>
</div>

<div style="height: 50px;"></div>

    <footer class="site-footer">
        <div class="container">
            
            <div class="footer-top">
                <div class="footer-cta">
                    <h2 class="footer-heading-lg">COOLMATE lắng nghe bạn!</h2>
                    <p class="footer-desc">Chúng tôi luôn trân trọng và mong đợi nhận được mọi ý kiến đóng góp từ khách hàng để có thể nâng cấp trải nghiệm dịch vụ và sản phẩm tốt hơn nữa.</p>
                    <a href="#" class="btn-feedback">Đóng góp ý kiến <i class="fa-solid fa-arrow-right"></i></a>
                    
                    <div class="social-icons">
                        <a href="#" class="icon-box"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="icon-box"><i class="fa-solid fa-comment-dots"></i></a>
                        <a href="#" class="icon-box"><i class="fa-brands fa-tiktok"></i></a>
                        <a href="#" class="icon-box"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="icon-box"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-contact-info">
                    <div class="contact-item">
                        <i class="fa-solid fa-phone-volume"></i>
                        <div class="contact-text">
                            <span>Hotline</span>
                            <strong>1900.272737 - 028.7777.2737</strong>
                            <span class="sub-text">(8:30 - 22:00)</span>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fa-solid fa-envelope"></i>
                        <div class="contact-text">
                            <span>Email</span>
                            <strong>Cool@coolmate.me</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="footer-middle">
                <div class="footer-col">
                    <h3 class="footer-title">COOLCLUB</h3>
                    <ul class="footer-links">
                        <li><a href="#">Tài khoản CoolClub</a></li>
                        <li><a href="#">Đăng kí thành viên</a></li>
                        <li><a href="#">Ưu đãi & Đặc quyền</a></li>
                    </ul>
                    
                    <h3 class="footer-title mt-20">TÀI LIỆU - TUYỂN DỤNG</h3>
                    <ul class="footer-links">
                        <li><a href="#">Tuyển dụng</a></li>
                        <li><a href="#">Đăng ký bản quyền</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h3 class="footer-title">CHÍNH SÁCH</h3>
                    <ul class="footer-links">
                        <li><a href="#">Chính sách đổi trả tại cửa hàng</a></li>
                        <li><a href="#">Chính sách đổi trả 60 ngày online</a></li>
                        <li><a href="#">Chính sách khuyến mãi</a></li>
                        <li><a href="#">Chính sách bảo mật</a></li>
                        <li><a href="#">Chính sách giao hàng</a></li>
                    </ul>

                    <h3 class="footer-title mt-20">COOLMATE.ME</h3>
                    <ul class="footer-links">
                        <li><a href="#">Lịch sử thay đổi website</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h3 class="footer-title">CHĂM SÓC KHÁCH HÀNG</h3>
                    <ul class="footer-links">
                        <li><a href="#">Trải nghiệm mua sắm 100% hài lòng</a></li>
                        <li><a href="#">Hỏi đáp - FAQs</a></li>
                    </ul>

                    <h3 class="footer-title mt-20">KIẾN THỨC MẶC ĐẸP</h3>
                    <ul class="footer-links">
                        <li><a href="#">Hướng dẫn chọn size</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>

                 <div class="footer-col">
                    <h3 class="footer-title">VỀ COOLMATE</h3>
                    <ul class="footer-links">
                        <li><a href="#">Quy tắc ứng xử của Coolmate</a></li>
                        <li><a href="#">Coolmate 101</a></li>
                        <li><a href="#">DVKH xuất sắc</a></li>
                        <li><a href="#">Câu chuyện về Coolmate</a></li>
                        <li><a href="#">Nhà máy</a></li>
                        <li><a href="#">Care & Share</a></li>
                        <li><a href="#">Cam kết bền vững</a></li>
                        <li><a href="#">Tầm nhìn 2030</a></li>
                    </ul>
                </div>

                <div class="footer-col address-col">
                    <h3 class="footer-title">ĐỊA CHỈ LIÊN HỆ</h3>
                    <p class="address-text">
                        <u>Văn phòng Hà Nội:</u> Tầng 3-4, Tòa nhà BMM, Km2, Đường Phùng Hưng, Phường Hà Đông, Thành phố Hà Nội, Việt Nam
                    </p>
                    <p class="address-text">
                        <u>Trung tâm vận hành Hà Nội:</u> Lô C8, KCN Lại Yên, Xã Lại Yên, Huyện Hoài Đức, Thành phố Hà Nội
                    </p>
                    <p class="address-text">
                        <u>Văn phòng và Trung tâm vận hành TP.HCM:</u> Lô C3, đường D2, KCN Cát Lái, Thạnh Mỹ Lợi, TP. Thủ Đức, TP. Hồ Chí Minh
                    </p>
                    <p class="address-text">
                        <u>Trung tâm R&D:</u> T6-01, The Manhattan Vinhomes Grand Park, Long Bình, TP. Thủ Đức
                    </p>
                </div>
            </div>

            <div class="divider"></div>

            <div class="footer-bottom">
                <div class="company-info">
                    <p class="copyright-title">@ CÔNG TY TNHH FASTECH ASIA</p>
                    <p>Mã số doanh nghiệp: 0108617038. Giấy chứng nhận đăng ký doanh nghiệp do Sở Kế hoạch và Đầu tư TP Hà Nội cấp lần đầu ngày 20/02/2019.</p>
                </div>
                <div class="certifications">
                    <div class="cert-logo dmca">DMCA<br>PROTECTED</div>
                    <div class="cert-logo bct">ĐÃ THÔNG BÁO<br>BỘ CÔNG THƯƠNG</div>
                </div>
            </div>
        </div>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 0.5rem; overflow: hidden; border: none;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">ĐĂNG NHẬP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form action="/web_qlsp/login/login" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-bold">EMAIL</label>
                        <input type="email" name="email" class="form-control" required placeholder="example@gmail.com"
                               value="<?php echo isset($_COOKIE['user_email']) ? $_COOKIE['user_email'] : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">MẬT KHẨU</label>
                        <div class="position-relative">
                            <input type="password" name="password" id="loginPass" class="form-control pe-5" required 
                            placeholder="*****"
                                   value="<?php echo isset($_COOKIE['user_password']) ? $_COOKIE['user_password'] : ''; ?>">
                            <span class="password-toggle-icon" onclick="togglePassword('loginPass', this)">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember"
                               <?php echo isset($_COOKIE['user_email']) ? 'checked' : ''; ?>>
                        <label class="form-check-label small" for="remember">Ghi nhớ đăng nhập</label>
                    </div>

                    <button type="submit" name="btn_login" class="btn btn-dark w-100 mb-3 py-2 fw-bold">ĐĂNG NHẬP</button>
                </form>

                <div class="text-center">
                    <p class="small text-muted">Chưa có tài khoản? <a href="#" class="text-dark fw-bold text-decoration-none" data-bs-toggle="modal" data-bs-target="#registerModal">Đăng ký ngay</a></p>
                    <hr class="my-3">
                    <button type="button" class="btn btn-link text-secondary small text-decoration-none" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left me-1"></i> Tiếp tục mua sắm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 0.5rem; overflow: hidden; border: none;">
            <div class="modal-header border-0 p-4 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 pb-4">
                <div class="text-center mb-4">
                    <h2 class="fw-bold">ĐĂNG KÝ TÀI KHOẢN</h2>
                    <p class="text-muted">Trở thành thành viên để nhận nhiều ưu đãi</p>
                </div>

                <form action="/web_qlsp/register/do_register" method="POST">
                    <input type="hidden" name="role" value="0">
                    <input type="hidden" name="points" value="0">
                    <input type="hidden" name="google_id" value="">
                    <input type="hidden" name="avatar" value="default-avatar.png">
                    

                    <div class="section-title mb-3 pb-2 border-bottom fw-bold" style="font-size: 1.1rem;">Thông tin cá nhân</div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label small fw-bold">HỌ VÀ TÊN</label>
                            <input type="text" name="full_name" class="form-control" placeholder="Nhập họ và tên của bạn" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">EMAIL</label>
                            <input type="email" name="email" class="form-control" placeholder="example@gmail.com" required>
                        </div>
                      <div class="col-md-6 mb-3">
    <label class="form-label small fw-bold">SỐ ĐIỆN THOẠI</label>
    <input 
        type="tel" 
        id="phoneInput"
        name="phone" 
        class="form-control" 
        placeholder="Số điện thoại của bạn" 
        required
        minlength="10"
        maxlength="11"
    >
    <div class="invalid-feedback">
        SĐT phải là số và dài 10 ký tự .
    </div>
</div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label small fw-bold">MẬT KHẨU</label>
                            <div class="position-relative">
                                <input type="password" name="password" id="registerPass" class="form-control pe-5" placeholder="Tối thiểu 6 ký tự" required>
                                <span class="password-toggle-icon" onclick="togglePassword('registerPass', this)">
                                    <i class="far fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="section-title mt-3 mb-3 pb-2 border-bottom fw-bold" style="font-size: 1.1rem;">Địa chỉ nhận hàng</div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">TỈNH / THÀNH</label>
                            <select name="province_code" class="form-select" id="province" required>
                                <option value="">Chọn Tỉnh/Thành</option>
                                <?php
                                    if (isset($data['provinces']) && $data['provinces'] !== false) {
                                        mysqli_data_seek($data['provinces'], 0);
                                        while ($row = mysqli_fetch_assoc($data['provinces'])) {
                                            echo '<option value="' . $row['code'] . '">' . $row['name'] . '</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">QUẬN / HUYỆN</label>
                            <select name="district_code" class="form-select" id="district" required>
                                <option value="">Chọn Quận/Huyện</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">PHƯỜNG / XÃ</label>
                            <select name="ward_code" class="form-select" id="ward" required>
                                <option value="">Chọn Phường/Xã</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label small fw-bold">ĐỊA CHỈ CHI TIẾT</label>
                            <input type="text" name="address_detail" class="form-control" placeholder="Số nhà, tên đường..." required>
                        </div>
                    </div>

                    <div class="form-check mb-4 mt-3">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label small" for="terms">
                            Tôi đồng ý với các <a href="#" class="text-dark fw-bold">điều khoản dịch vụ</a>.
                        </label>
                    </div>

                    <button type="submit" name="btn_register" class="btn btn-dark w-100 py-3 fw-bold">ĐĂNG KÝ NGAY</button>
                </form>

                <div class="text-center mt-4">
                    <p class="small">Đã có tài khoản?
                        <a href="#" class="text-dark fw-bold text-decoration-none" data-bs-toggle="modal" data-bs-target="#loginModal">Đăng nhập</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
    <link rel="stylesheet" href="/web_qlsp/Public/Css/footer.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/web_qlsp/Public/js/province.js"></script>
<script src="/web_qlsp/Public/js/contact.js"></script>
<script src="/web_qlsp/Public/js/result.js"></script>
<script src="/web_qlsp/Public/js/regist.js"></script>