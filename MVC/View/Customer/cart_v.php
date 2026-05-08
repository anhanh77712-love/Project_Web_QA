<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root { --ink: #0f172a; --muted: #6b7280; --line: #e5e7eb; --primary: #111827; --accent: #2563eb; --bg: #f5f7fb; --card: #ffffff; }
    .cart-page { background: radial-gradient(circle at 20% 20%, #f0f4ff, #f7f7f7 40%, #f5f7fb 80%); padding: 36px 0 100px; display: flex; justify-content: center; min-height: 80vh; }
    .cart-page * { box-sizing: border-box; }
    .cart-shell { width: 100%; max-width: 1280px; padding: 0 20px; }
    .cart-hero { background: var(--card); border: 1px solid var(--line); border-radius: 18px; padding: 18px 20px; display: flex; justify-content: space-between; gap: 14px; align-items: center; margin-bottom: 22px; box-shadow: 0 12px 30px rgba(15,23,42,0.06); }
    .cart-hero h2 { margin: 0; font-size: 22px; color: var(--ink); font-weight: 800; }
    .cart-hero p { margin: 4px 0 0; color: var(--muted); font-size: 14px; }
    .cart-hero .pill { background: rgba(37,99,235,.1); color: var(--accent); padding: 8px 14px; border-radius: 999px; font-weight: 700; font-size: 13px; display: inline-flex; gap: 8px; align-items: center; }
    .cart-grid { display: grid; grid-template-columns: 2.1fr 1fr; gap: 20px; align-items: start; }
    .card-panel { background: var(--card); border: 1px solid var(--line); border-radius: 16px; padding: 22px; box-shadow: 0 12px 30px rgba(15,23,42,0.05); }
    .card-panel h3 { font-size: 18px; font-weight: 800; margin: 0 0 16px; display: flex; align-items: center; gap: 10px; color: var(--ink); }
    .card-panel h3 .icon { width: 36px; height: 36px; border-radius: 10px; display: grid; place-items: center; background: rgba(37,99,235,.12); color: var(--accent); }
    .cart-section { margin-bottom: 26px; }
    .cart-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 14px; }
    .cart-form input, .cart-form textarea, .cart-form select { width: 100%; padding: 12px 14px; border-radius: 12px; border: 1px solid var(--line); font-size: 14px; background: #f8fafc; }
    .cart-form textarea { grid-column: 1 / -1; resize: none; min-height: 90px; }
    .pay-options { display: grid; gap: 12px; }
    .cart-pay-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1px solid var(--line); border-radius: 12px; cursor: pointer; background: #f9fafb; transition: all .2s ease; }
    .cart-pay-item input { accent-color: var(--accent); }
    .cart-right { position: sticky; top: 90px; }
    .cart-items { border: 1px solid var(--line); border-radius: 12px; overflow: hidden; background: #f9fafb; }
    .cart-item { display: grid; grid-template-columns: auto 70px 1fr auto; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--line); align-items: center; background: #fff; }
    .cart-item img { width: 70px; height: 90px; object-fit: cover; border-radius: 10px; border: 1px solid #eee; }
    .cart-item-title { font-weight: 700; color: var(--ink); font-size: 15px; }
    .cart-item-meta { font-size: 13px; color: var(--muted); margin-top: 2px; }
    .cart-qty { display: inline-flex; align-items: center; background: #f1f5f9; border-radius: 999px; border: 1px solid var(--line); }
    .cart-qty button { border: none; background: transparent; padding: 4px 10px; font-size: 16px; cursor: pointer; }
    .cart-qty input { width: 30px; text-align: center; border: none; background: transparent; font-weight: 700; }
    .cart-price { font-weight: 800; color: var(--ink); }
    .cart-voucher { display: flex; gap: 10px; margin: 14px 0 6px; }
    .cart-voucher select { flex: 1; padding: 10px 12px; border-radius: 12px; border: 1px solid var(--line); background: #f9fafb; }
    .cart-voucher button { padding: 10px 14px; border-radius: 12px; border: none; background: var(--accent); color: #fff; font-weight: 700; cursor: pointer; }
    .empty-box { text-align: center; padding: 40px 16px; color: var(--muted); background: #fff; border-radius: 12px; }

    /* Footer cố định tính tiền */
    .cart-fixed-footer { position: fixed; bottom: 0; left: 0; right: 0; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-top: 1px solid var(--line); box-shadow: 0 -8px 30px rgba(15,23,42,0.1); padding: 16px 20px; z-index: 999; display: flex; align-items: center; justify-content: center; }
    .cart-fixed-content { width: 100%; max-width: 1280px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
    .cart-fixed-summary { display: flex; align-items: center; gap: 24px; }
    .cart-fixed-row { display: flex; flex-direction: column; gap: 2px; }
    .cart-fixed-label { color: var(--muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
    .cart-fixed-value { font-weight: 700; color: var(--ink); font-size: 15px; }
    .cart-fixed-total { border-left: 2px solid var(--line); padding-left: 24px; }
    .cart-fixed-total .cart-fixed-value { font-size: 24px; font-weight: 900; color: var(--accent); }
    .cart-fixed-order-btn { padding: 14px 45px; border-radius: 12px; border: none; background: linear-gradient(135deg, #111827, #1f2937); color: #fff; font-size: 16px; font-weight: 800; cursor: pointer; transition: 0.2s; }
    .cart-fixed-order-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
    .cart-fixed-order-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

    @media (max-width: 992px) { .cart-grid { grid-template-columns: 1fr; } .cart-fixed-summary { display: none; } }
</style>

<div class="cart-page">
    <div class="cart-shell">
        <div class="cart-hero">
            <div>
                <h2>Giỏ hàng của bạn</h2>
                <p>Kiểm tra lại sản phẩm và hoàn tất đặt hàng.</p>
            </div>
            <div class="pill"><i class="fas fa-shield-alt"></i> Thanh toán an toàn</div>
        </div>

        <form action="/web_qlsp/cart/checkout" method="POST" id="checkoutForm">
            <input type="hidden" name="voucher_id" id="selectedVoucherId" value="">
            <input type="hidden" name="use_points" id="usePointsField" value="0">
            <input type="hidden" name="points_to_use" id="pointsToUseField" value="0">
            <input type="hidden" name="selected_items" id="selectedItems" value="">

            <div class="cart-grid">
                <div class="card-panel">
                    <div class="cart-section">
                        <h3><span class="icon"><i class="fas fa-truck"></i></span>Thông tin vận chuyển</h3>
                        <div class="cart-form">
                            <input type="text" name="customer_name" placeholder="Họ tên người nhận" required value="<?= $data['user_info']['full_name'] ?? '' ?>">
                            <input type="text" name="customer_phone" placeholder="Số điện thoại" required value="<?= $data['user_info']['phone'] ?? '' ?>">
                            <input type="email" name="customer_email" placeholder="Email nhận thông báo" required value="<?= $data['user_info']['email'] ?? '' ?>">
                            
                            <select name="province_code" id="province" required>
                                <option value="">Chọn Tỉnh/Thành</option>
                                <?php
                                if (isset($data['provinces'])) {
                                    mysqli_data_seek($data['provinces'], 0);
                                    $u_province = $data['user_info']['province_code'] ?? '';
                                    while ($row = mysqli_fetch_assoc($data['provinces'])) {
                                        $sel = ($u_province == $row['code']) ? 'selected' : '';
                                        echo '<option value="'.$row['code'].'" '.$sel.'>'.$row['name'].'</option>';
                                    }
                                }
                                ?>
                            </select>
                            
                            <select name="district_code" id="district" required>
                                <option value="">Chọn Quận/Huyện</option>
                                <?php
                                if (isset($data['user_districts'])) {
                                    mysqli_data_seek($data['user_districts'], 0);
                                    $u_district = $data['user_info']['district_code'] ?? '';
                                    while ($row = mysqli_fetch_assoc($data['user_districts'])) {
                                        $sel = ($u_district == $row['code']) ? 'selected' : '';
                                        echo '<option value="'.$row['code'].'" '.$sel.'>'.$row['name'].'</option>';
                                    }
                                }
                                ?>
                            </select>
                            
                            <select name="ward_code" id="ward" required>
                                <option value="">Chọn Phường/Xã</option>
                                <?php
                                if (isset($data['user_wards'])) {
                                    mysqli_data_seek($data['user_wards'], 0);
                                    $u_ward = $data['user_info']['ward_code'] ?? '';
                                    while ($row = mysqli_fetch_assoc($data['user_wards'])) {
                                        $sel = ($u_ward == $row['code']) ? 'selected' : '';
                                        echo '<option value="'.$row['code'].'" '.$sel.'>'.$row['name'].'</option>';
                                    }
                                }
                                ?>
                            </select>
                            
                            <input type="text" name="address_detail" placeholder="Số nhà, tên đường..." required value="<?= $data['user_info']['address_detail'] ?? '' ?>">
                            <textarea rows="3" name="note" placeholder="Ghi chú thêm về đơn hàng..."></textarea>
                        </div>
                    </div>

                    <div class="cart-section">
                        <h3><span class="icon"><i class="fas fa-credit-card"></i></span>Hình thức thanh toán</h3>
                        <div class="pay-options">
                            <label class="cart-pay-item"><input type="radio" name="payment_method" value="cod" checked> Thanh toán khi nhận hàng (COD)</label>
                            <label class="cart-pay-item"><input type="radio" name="payment_method" value="vnpay"> Thanh toán qua VNPAY</label>
                        </div>
                    </div>
                </div>

                <div class="card-panel cart-right">
                    <h3><span class="icon"><i class="fas fa-shopping-bag"></i></span>Sản phẩm (<span id="cart-qty-title"><?= isset($data['cart_items']) ? count($data['cart_items']) : 0 ?></span>)</h3>
                    
                    <div class="cart-items" id="cart-container">
                        <?php if (isset($data['cart_items']) && count($data['cart_items']) > 0): ?>
                            <?php foreach ($data['cart_items'] as $key => $item): ?>
                                <div class="cart-item" data-key="<?= $key ?>">
                                    <input type="checkbox" class="cart-check" checked>
                                    <img src="/web_qlsp/Public/Picture/<?= htmlspecialchars($item['image']) ?>" alt="thumb">
                                    <div class="cart-item-info">
                                        <div class="cart-item-title"><?= htmlspecialchars($item['name']) ?></div>
                                        <div class="cart-item-meta">
                                            <?= $item['size'] ? 'Size: '.$item['size'] : '' ?>
                                            <?= $item['color'] ? ' | Màu: '.$item['color'] : '' ?>
                                        </div>
                                        <div class="d-flex align-items-center gap-3 mt-2">
                                            <div class="cart-qty">
                                                <button type="button" onclick="updateQty('<?= $key ?>', -1)">-</button>
                                                <input type="text" value="<?= $item['quantity'] ?>" data-key="<?= $key ?>" readonly>
                                                <button type="button" onclick="updateQty('<?= $key ?>', 1)">+</button>
                                            </div>
                                            <div class="cart-price" data-price="<?= $item['price'] ?>"><?= number_format($item['price'], 0, ',', '.') ?>đ</div>
                                            <button type="button" class="btn btn-sm text-danger" onclick="removeItem('<?= $key ?>')"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-box">
                                <i class="fas fa-shopping-cart fa-3x mb-3 opacity-25"></i>
                                <p>Giỏ hàng của bạn đang trống.</p>
                                <a href="/web_qlsp/product_list_customer" class="btn btn-primary mt-3 rounded-pill px-4">Mua sắm ngay</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="cart-voucher">
                        <select id="voucherSelect">
                            <option value="">-- Chọn Voucher --</option>
                            <?php
                            if (isset($data['vouchers'])) {
                                while ($v = mysqli_fetch_assoc($data['vouchers'])) {
                                    if ($v['status'] == 1) {
                                        $d_text = ($v['discount_type'] == 'fixed') ? number_format($v['discount_value']).'đ' : $v['discount_value'].'%';
                                        echo '<option value="'.$v['id'].'" data-type="'.$v['discount_type'].'" data-value="'.$v['discount_value'].'" data-min="'.$v['min_order_value'].'">'.$v['code'].' - Giảm '.$d_text.'</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                        <button type="button" id="applyVoucher">ÁP DỤNG</button>
                    </div>

                    <div class="mt-4 border-top pt-3">
                        <h6 class="fw-bold mb-2">Đổi điểm tích lũy</h6>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php $points = intval($data['user_info']['points'] ?? 0); ?>
                            <div class="small text-muted mb-2">Bạn đang có: <strong><?= number_format($points) ?> điểm</strong></div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="checkUsePoints">
                                <label class="form-check-label" for="checkUsePoints">Sử dụng điểm (1 điểm = 1đ)</label>
                            </div>
                            <input type="number" id="pointsInput" class="form-control" placeholder="Nhập số điểm..." min="0" max="<?= $points ?>" disabled>
                        <?php else: ?>
                            <div class="alert alert-light small py-2">Đăng nhập để sử dụng điểm.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>

        <div class="cart-fixed-footer">
            <div class="cart-fixed-content">
                <div class="cart-fixed-summary">
                    <div class="cart-fixed-row"><span class="cart-fixed-label">Tạm tính</span><span class="cart-fixed-value" id="subTotalFixed">0đ</span></div>
                    <div class="cart-fixed-row"><span class="cart-fixed-label">Tiền ship</span><span class="cart-fixed-value" id="shippingFixed">0đ</span></div>
                    <div class="cart-fixed-row"><span class="cart-fixed-label">Voucher</span><span class="cart-fixed-value text-danger" id="voucherFixed">-0đ</span></div>
                    <div class="cart-fixed-row"><span class="cart-fixed-label">Điểm đổi</span><span class="cart-fixed-value text-danger" id="pointsFixed">-0đ</span></div>
                    <div class="cart-fixed-total"><span class="cart-fixed-label">Tổng cộng</span><span class="cart-fixed-value" id="totalPriceFixed">0đ</span></div>
                </div>
                <button type="button" class="cart-fixed-order-btn" id="btnOrder" <?= (empty($data['cart_items'])) ? 'disabled' : '' ?>>ĐẶT HÀNG NGAY</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const availablePoints = <?= isset($data['user_info']['points']) ? intval($data['user_info']['points']) : 0 ?>;
    let currentVoucher = null;

    // 1. AJAX LOAD ĐỊA CHỈ
    $('#province').change(function() {
        if ($(this).val()) {
            $.ajax({ url: "/web_qlsp/cart/get_districts/" + $(this).val(), method: "GET", success: function(d) { $('#district').html(d); $('#ward').html('<option value="">Chọn Phường/Xã</option>'); } });
        }
    });
    $('#district').change(function() {
        if ($(this).val()) { $.ajax({ url: "/web_qlsp/cart/get_wards/" + $(this).val(), method: "GET", success: function(d) { $('#ward').html(d); } }); }
    });

    // 2. CẬP NHẬT TỔNG TIỀN (UI)
    function updateTotal() {
        let subtotal = 0;
        $('.cart-item').each(function() {
            if ($(this).find('.cart-check').is(':checked')) {
                subtotal += parseInt($(this).find('.cart-price').data('price')) * parseInt($(this).find('.cart-qty input').val());
            }
        });

        let voucherDiscount = 0;
        if (currentVoucher) {
            if (subtotal < currentVoucher.min) {
                currentVoucher = null; $('#voucherSelect').val('');
            } else {
                voucherDiscount = (currentVoucher.type === 'fixed') ? currentVoucher.value : Math.round(subtotal * currentVoucher.value / 100);
            }
        }

        let pointsUsed = 0;
        if ($('#checkUsePoints').is(':checked')) {
            pointsUsed = Math.min(parseInt($('#pointsInput').val()) || 0, availablePoints, Math.max(0, subtotal - voucherDiscount));
        }

        const shipping = subtotal > 0 ? 30000 : 0;
        const total = Math.max(0, subtotal + shipping - voucherDiscount - pointsUsed);

        $('#subTotalFixed').text(subtotal.toLocaleString() + 'đ');
        $('#voucherFixed').text('-' + voucherDiscount.toLocaleString() + 'đ');
        $('#pointsFixed').text('-' + pointsUsed.toLocaleString() + 'đ');
        $('#shippingFixed').text(shipping.toLocaleString() + 'đ');
        $('#totalPriceFixed').text(total.toLocaleString() + 'đ');

        $('#pointsToUseField').val(pointsUsed);
        $('#usePointsField').val($('#checkUsePoints').is(':checked') ? 1 : 0);
        $('#selectedVoucherId').val(currentVoucher ? currentVoucher.id : '');

        $('#btnOrder').prop('disabled', subtotal === 0);
    }

    // 3. AJAX CẬP NHẬT SỐ LƯỢNG
    window.updateQty = function(cartKey, delta) {
        const input = $('input[data-key="'+cartKey+'"]');
        let newQty = parseInt(input.val()) + delta;
        if (newQty < 1 || newQty > 2) return;

        $.ajax({
            url: "/web_qlsp/cart/update_quantity",
            method: "POST",
            data: { cart_key: cartKey, quantity: newQty },
            dataType: 'json',
            success: function(res) {
                if (res.success) { input.val(res.quantity); updateTotal(); }
                else Swal.fire('Lỗi', res.message, 'warning');
            }
        });
    }

    // 4. AJAX XÓA SẢN PHẨM (KHÔNG F5)
    window.removeItem = function(cartKey) {
        Swal.fire({ title: 'Xóa sản phẩm?', text: "Bạn chắc chắn muốn bỏ sản phẩm này?", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Đồng ý' }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    url: "/web_qlsp/cart/remove_item",
                    method: "POST",
                    data: { cart_key: cartKey },
                    success: function() { 
                        $(`.cart-item[data-key="${cartKey}"]`).fadeOut(300, function() { $(this).remove(); refreshCartUI(); });
                    }
                });
            }
        });
    }

    function refreshCartUI() {
        $.ajax({
            url: "/web_qlsp/cart/api_get_cart_items",
            method: "GET",
            success: function(res) {
                if (res.success) {
                    const keys = Object.keys(res.cart_items);
                    $('#cart-qty-title').text(keys.length);
                    $('#cart-count').text(keys.length); // Navbar count
                    if (keys.length === 0) {
                        $('#cart-container').html('<div class="empty-box"><i class="fas fa-shopping-cart fa-3x mb-3 opacity-25"></i><p>Giỏ hàng rỗng.</p><a href="/web_qlsp/product_list_customer" class="btn btn-primary mt-3 rounded-pill px-4">Mua sắm</a></div>');
                    }
                    updateTotal();
                }
            }
        });
    }

    // 5. VOUCHER & POINTS EVENTS
    $('#applyVoucher').click(function() {
        const sel = $('#voucherSelect option:selected');
        if (!sel.val()) { currentVoucher = null; updateTotal(); return; }
        currentVoucher = { id: sel.val(), type: sel.data('type'), value: sel.data('value'), min: sel.data('min') };
        updateTotal();
        Swal.fire({ icon: 'success', title: 'Đã áp dụng Voucher', timer: 1000, showConfirmButton: false });
    });

    $('#checkUsePoints').change(function() { $('#pointsInput').prop('disabled', !this.checked); if(!this.checked) $('#pointsInput').val(0); updateTotal(); });
    $('#pointsInput').on('input', updateTotal);
    $(document).on('change', '.cart-check', updateTotal);

    // 6. ĐẶT HÀNG AJAX
    $('#btnOrder').click(function() { 
        const userLoggedIn = <?= isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? 'true' : 'false' ?>;
        if (!userLoggedIn) {
            Swal.fire({
                title: 'Yêu cầu đăng nhập',
                text: 'Vui lòng đăng nhập để tiếp tục đặt hàng!',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }
        $('#checkoutForm').submit(); 
    });

    $('#checkoutForm').on('submit', function(e) {
        e.preventDefault();
        
        let selectedKeys = [];
        $('.cart-item').each(function() { if ($(this).find('.cart-check').is(':checked')) selectedKeys.push($(this).data('key')); });
        if (selectedKeys.length === 0) return Swal.fire('Thông báo', 'Chọn ít nhất 1 sản phẩm', 'info');
        $('#selectedItems').val(selectedKeys.join(','));

        Swal.fire({ title: 'Đang xử lý...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url: $(this).attr('action'),
            method: "POST",
            data: new FormData(this),
            processData: false, contentType: false, dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Đặt hàng thành công!', text: 'Đang chuyển hướng...', timer: 2000, showConfirmButton: false }).then(() => {
                        window.location.href = res.redirect_url;
                    });
                } else if (res.require_login) {
                    Swal.fire({
                        title: 'Yêu cầu đăng nhập',
                        text: res.message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Đăng nhập ngay',
                        cancelButtonText: 'Hủy'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '/web_qlsp/login';
                        }
                    });
                } else Swal.fire('Lỗi', res.message, 'error');
            }
        });
    });

    // Chạy lần đầu
    updateTotal();
});
</script>