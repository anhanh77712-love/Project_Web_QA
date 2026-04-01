<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* ... copy y nguyên CSS cũ vào đây ... */
:root { --ink: #0f172a; --muted: #6b7280; --line: #e5e7eb; --primary: #111827; --accent: #2563eb; --bg: #f5f7fb; --card: #ffffff; }
.cart-page { background: radial-gradient(circle at 20% 20%, #f0f4ff, #f7f7f7 40%, #f5f7fb 80%); padding: 36px 0 100px; display: flex; justify-content: center; }
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
.cart-form input:focus, .cart-form textarea:focus, .cart-form select:focus { outline: none; border-color: rgba(37,99,235,.5); box-shadow: 0 0 0 3px rgba(37,99,235,.12); background: #fff; }
.cart-form textarea { grid-column: 1 / -1; resize: none; min-height: 90px; }
.pay-options { display: grid; gap: 12px; }
.cart-pay-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1px solid var(--line); border-radius: 12px; cursor: pointer; background: #f9fafb; transition: all .2s ease; }
.cart-pay-item input { accent-color: var(--accent); }
.cart-pay-item:hover { border-color: rgba(37,99,235,.4); background: #fff; box-shadow: 0 8px 20px rgba(37,99,235,0.06); }
.cart-right { position: sticky; top: 90px; }
.cart-items { border: 1px solid var(--line); border-radius: 12px; overflow: hidden; background: #f9fafb; }
.cart-item { display: grid; grid-template-columns: auto 70px 1fr auto; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--line); align-items: center; background: #fff; }
.cart-item:last-child { border-bottom: none; }
.cart-check { accent-color: var(--accent); }
.cart-item img { width: 70px; height: 90px; object-fit: cover; border-radius: 10px; border: 1px solid var(--line); }
.cart-item-info { display: flex; flex-direction: column; gap: 4px; }
.cart-item-title { font-weight: 700; color: var(--ink); }
.cart-item-meta { font-size: 13px; color: var(--muted); }
.cart-item-actions { display: flex; gap: 10px; align-items: center; }
.cart-qty { display: inline-flex; align-items: center; background: #f1f5f9; border-radius: 999px; border: 1px solid var(--line); }
.cart-qty button { border: none; background: transparent; padding: 6px 12px; font-size: 16px; cursor: pointer; color: var(--ink); }
.cart-qty button:hover { background: #e2e8f0; }
.cart-qty input { width: 34px; text-align: center; border: none; background: transparent; font-weight: 700; }
.cart-price { font-weight: 800; color: var(--ink); }
.cart-voucher { display: flex; gap: 10px; margin: 14px 0 6px; }
.cart-voucher select { flex: 1; padding: 10px 12px; border-radius: 12px; border: 1px solid var(--line); background: #f9fafb; }
.cart-voucher button { padding: 10px 14px; border-radius: 12px; border: none; background: var(--accent); color: #fff; font-weight: 700; cursor: pointer; transition: opacity .15s ease; }
.cart-voucher button:hover { opacity: .92; }
.empty-box { text-align: center; padding: 36px 16px; color: var(--muted); }
.cart-fixed-footer { position: fixed; bottom: 0; left: 0; right: 0; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-top: 1px solid var(--line); box-shadow: 0 -8px 30px rgba(15,23,42,0.1); padding: 16px 20px; z-index: 999; display: flex; align-items: center; justify-content: center; }
.cart-fixed-content { width: 100%; max-width: 1280px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
.cart-fixed-summary { display: flex; align-items: center; gap: 24px; font-size: 14px; }
.cart-fixed-row { display: flex; flex-direction: column; gap: 2px; }
.cart-fixed-label { color: var(--muted); font-size: 12px; }
.cart-fixed-value { font-weight: 700; color: var(--ink); font-size: 15px; }
.cart-fixed-total { display: flex; flex-direction: column; gap: 2px; padding-left: 24px; border-left: 2px solid var(--line); }
.cart-fixed-total .cart-fixed-value { font-size: 24px; font-weight: 900; color: var(--accent); }
.cart-fixed-order-btn { padding: 14px 40px; border-radius: 12px; border: none; background: linear-gradient(135deg, #111827, #1f2937); color: #fff; font-size: 16px; font-weight: 800; cursor: pointer; letter-spacing: 0.3px; box-shadow: 0 8px 24px rgba(17,24,39,0.2); transition: all 0.2s ease; white-space: nowrap; }
.cart-fixed-order-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(17,24,39,0.3); }
.cart-fixed-order-btn:disabled { opacity: 0.5; cursor: not-allowed; }
@media (max-width: 992px) { .cart-grid { grid-template-columns: 1fr; } .cart-right { position: relative; top: 0; } .cart-fixed-summary { display: none; } }
@media (max-width: 640px) { .cart-form { grid-template-columns: 1fr; } .cart-item { grid-template-columns: auto 60px 1fr; } }
</style>

<div class="cart-page">
    <div class="cart-shell">
        <div class="cart-hero">
            <div>
                <h2>Giỏ hàng của bạn</h2>
                <p>Hoàn tất thông tin và chọn phương thức thanh toán để đặt hàng.</p>
            </div>
            <div class="pill"><i class="fas fa-shield-alt"></i> Thanh toán an toàn</div>
        </div>

        <form action="/web_qlsp/api/customer/cart_api/checkout" method="POST" id="checkoutForm">
            <input type="hidden" name="voucher_id" id="selectedVoucherId" value="">
            <input type="hidden" name="use_points" id="usePointsField" value="0">
            <input type="hidden" name="points_to_use" id="pointsToUseField" value="0">

            <div class="cart-grid">
                <div class="card-panel">
                    <div class="cart-section">
                        <h3><span class="icon"><i class="fas fa-truck"></i></span>Thông tin vận chuyển</h3>
                        <div class="cart-form">
                            <input type="text" name="customer_name" placeholder="Họ tên" required value="<?= $data['user_info']['full_name'] ?? '' ?>">
                            <input type="text" name="customer_phone" placeholder="Số điện thoại" required value="<?= $data['user_info']['phone'] ?? '' ?>">
                            <input type="email" name="customer_email" placeholder="Email" required value="<?= $data['user_info']['email'] ?? '' ?>">
                            
                            <select name="province_code" class="form-select" id="province" required>
                                <option value="">Chọn Tỉnh/Thành</option>
                                <?php
                                if (isset($data['provinces']) && $data['provinces'] !== false) {
                                    mysqli_data_seek($data['provinces'], 0);
                                    $user_province = $data['user_info']['province_code'] ?? '';
                                    while ($row = mysqli_fetch_assoc($data['provinces'])) {
                                        $selected = ($user_province == $row['code']) ? 'selected' : '';
                                        echo '<option value="' . $row['code'] . '" ' . $selected . '>' . $row['name'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            
                            <select name="district_code" class="form-select" id="district" required>
                                <option value="">Chọn Quận/Huyện</option>
                                <?php
                                if (isset($data['user_districts']) && $data['user_districts']) {
                                    mysqli_data_seek($data['user_districts'], 0);
                                    $user_district = $data['user_info']['district_code'] ?? '';
                                    while ($row = mysqli_fetch_assoc($data['user_districts'])) {
                                        $selected = ($user_district == $row['code']) ? 'selected' : '';
                                        echo '<option value="' . $row['code'] . '" ' . $selected . '>' . $row['name'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            
                            <select name="ward_code" class="form-select" id="ward" required>
                                <option value="">Chọn Phường/Xã</option>
                                <?php
                                if (isset($data['user_wards']) && $data['user_wards']) {
                                    mysqli_data_seek($data['user_wards'], 0);
                                    $user_ward = $data['user_info']['ward_code'] ?? '';
                                    while ($row = mysqli_fetch_assoc($data['user_wards'])) {
                                        $selected = ($user_ward == $row['code']) ? 'selected' : '';
                                        echo '<option value="' . $row['code'] . '" ' . $selected . '>' . $row['name'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            
                            <input type="text" name="address_detail" placeholder="Số nhà, tên đường..." required value="<?= $data['user_info']['address_detail'] ?? '' ?>">
                            <textarea rows="3" name="note" placeholder="Ghi chú"></textarea>
                        </div>
                    </div>

                    <div class="cart-section">
                        <h3><span class="icon"><i class="fas fa-credit-card"></i></span>Hình thức thanh toán</h3>
                        <div class="pay-options">
                            <label class="cart-pay-item"><input type="radio" name="payment_method" value="vnpay"> VNPAY</label>
                            <label class="cart-pay-item"><input type="radio" name="payment_method" value="cod" checked> Thanh toán khi nhận hàng (COD)</label>
                        </div>
                    </div>
                </div>

                <div class="card-panel cart-right">
                    <h3><span class="icon"><i class="fas fa-shopping-bag"></i></span>Giỏ hàng (<?= isset($data['cart_items']) ? count($data['cart_items']) : 0 ?>)</h3>
                    
                    <div class="cart-items">
                    <?php 
                    if (isset($data['cart_items']) && count($data['cart_items']) > 0):
                        foreach ($data['cart_items'] as $key => $item): 
                    ?>
                        <div class="cart-item" data-key="<?= $key ?>">
                            <input type="checkbox" class="cart-check" checked>
                            <img src="/web_qlsp/Public/Picture/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            <div class="cart-item-info">
                                <div class="cart-item-title"><?= htmlspecialchars($item['name']) ?></div>
                                <?php
                                $sizeText = isset($item['size']) && $item['size'] !== '' ? htmlspecialchars($item['size']) : null;
                                $colorText = isset($item['color']) && $item['color'] !== '' ? htmlspecialchars($item['color']) : null;
                                if ($sizeText || $colorText): ?>
                                    <div class="cart-item-meta">
                                        <?php if ($sizeText): ?><span>Size: <?= $sizeText ?></span><?php endif; ?>
                                        <?php if ($colorText): ?><span style="margin-left:10px;">Màu: <?= $colorText ?></span><?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="cart-item-actions">
                                    <div class="cart-qty">
                                        <button type="button" class="qty-minus" onclick="updateQty('<?= $key ?>', -1)">-</button>
                                        <input type="text" value="<?= $item['quantity'] ?>" class="qty-input" data-key="<?= $key ?>" readonly>
                                        <button type="button" class="qty-plus" onclick="updateQty('<?= $key ?>', 1)">+</button>
                                    </div>
                                    <div class="cart-price" data-price="<?= $item['price'] ?>"><?= number_format($item['price'], 0, ',', '.') ?>đ</div>
                                    <button type="button" class="btn btn-sm text-danger" onclick="removeItem('<?= $key ?>')"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="empty-box">
                            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                            <p>Giỏ hàng của bạn đang trống</p>
                            <a href="/web_qlsp/api/customer/product_list_api" class="btn btn-dark">Tiếp tục mua sắm</a>
                        </div>
                    <?php endif; ?>
                    </div>

                    <div class="cart-voucher">
                        <select id="voucherSelect">
                            <option value="">-- Chọn voucher --</option>
                            <?php
                            if (isset($data['vouchers']) && mysqli_num_rows($data['vouchers']) > 0) {
                                while ($voucher = mysqli_fetch_assoc($data['vouchers'])) {
                                    $discount_text = ($voucher['discount_type'] == 'fixed') ? 'Giảm ' . number_format($voucher['discount_value'], 0, ',', '.') . 'đ' : 'Giảm ' . $voucher['discount_value'] . '%';
                                    if ($voucher['status'] == 1 && $voucher['used_count'] < $voucher['usage_limit']) {
                                        echo '<option value="' . $voucher['id'] . '" data-type="' . $voucher['discount_type'] . '" data-value="' . $voucher['discount_value'] . '" data-min-order="' . $voucher['min_order_value'] . '">' . htmlspecialchars($voucher['code']) . ' - ' . $discount_text;
                                        if ($voucher['min_order_value'] > 0) echo ' (Đơn tối thiểu ' . number_format($voucher['min_order_value'], 0, ',', '.') . 'đ)';
                                        echo '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                        <button type="button" id="applyVoucher">ÁP DỤNG</button>
                    </div>

                    <?php $availablePoints = isset($data['user_info']['points']) ? intval($data['user_info']['points']) : 0; ?>
                    <div class="card-panel" style="padding:14px; margin-top:10px;">
                        <h3 style="margin-bottom:10px;"><span class="icon"><i class="fas fa-coins"></i></span>Đổi điểm</h3>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div style="font-size:14px; color: var(--muted); margin-bottom:8px;">Điểm hiện có: <strong id="availablePointsDisp"><?= number_format($availablePoints, 0, ',', '.') ?></strong></div>
                            <div class="pay-options">
                                <label class="cart-pay-item"><input type="radio" name="use_points_choice" value="0" checked> Không dùng điểm</label>
                                <label class="cart-pay-item">
                                    <input type="radio" name="use_points_choice" value="1"> Dùng điểm
                                    <div style="margin-left:10px; display:flex; align-items:center; gap:8px;">
                                        <input type="number" id="pointsInput" min="0" step="1" value="0" style="width:120px;" disabled>
                                        <span style="font-size:13px; color:var(--muted);">1 điểm = 1đ</span>
                                    </div>
                                </label>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light" style="margin:0;">Đăng nhập để sử dụng điểm tích lũy.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>

        <div class="cart-fixed-footer">
            <div class="cart-fixed-content">
                <div class="cart-fixed-summary">
                    <div class="cart-fixed-row"><span class="cart-fixed-label">Thanh toán</span><span class="cart-fixed-value" id="paymentMethodFixed" style="color: #2563eb;">COD</span></div>
                    <div class="cart-fixed-row"><span class="cart-fixed-label">Tạm tính</span><span class="cart-fixed-value" id="subTotalFixed"><?= number_format($data['subtotal'] ?? 0, 0, ',', '.') ?>đ</span></div>
                    <div class="cart-fixed-row"><span class="cart-fixed-label">Phí ship</span><span class="cart-fixed-value"><?= number_format($data['shipping'] ?? 0, 0, ',', '.') ?>đ</span></div>
                    <div class="cart-fixed-row"><span class="cart-fixed-label">Voucher</span><span class="cart-fixed-value" id="voucherValueFixed" style="color: #dc3545;">-<?= number_format($data['discount'] ?? 0, 0, ',', '.') ?>đ</span></div>
                    <div class="cart-fixed-row"><span class="cart-fixed-label">Điểm đổi</span><span class="cart-fixed-value" id="pointsValueFixed" style="color: #dc3545;">-0đ</span></div>
                    <div class="cart-fixed-total"><span class="cart-fixed-label">Thành tiền</span><span class="cart-fixed-value" id="totalPriceFixed"><?= number_format($data['total'] ?? 0, 0, ',', '.') ?>đ</span></div>
                </div>
                <button type="button" class="cart-fixed-order-btn" id="fixedOrderBtn" <?= (isset($data['cart_items']) && count($data['cart_items']) > 0) ? '' : 'disabled' ?>>ĐẶT HÀNG</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // ======================================================
    // 1. AJAX CHỌN ĐỊA CHỈ (Tỉnh -> Huyện -> Xã)
    // ======================================================
    $('#province').change(function() {
        var p_code = $(this).val();
        if(p_code != "") {
            $.ajax({
                url: "/web_qlsp/api/customer/cart_api/get_districts/" + p_code,
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
                url: "/web_qlsp/api/customer/cart_api/get_wards/" + d_code,
                method: "GET",
                success: function(data) {
                    $('#ward').html(data);
                }
            });
        }
    });

    // Helper format tiền
    function formatPrice(n){
        return parseInt(n).toLocaleString('vi-VN') + 'đ';
    }

    // ======================================================
    // 2. LOGIC VOUCHER & ĐIỂM
    // ======================================================
    let currentVoucher = null; 
    // Lấy số điểm hiện có từ PHP (nếu chưa đăng nhập thì = 0)
    const availablePoints = <?= isset($data['user_info']['points']) ? intval($data['user_info']['points']) : 0 ?>;

    const voucherSelect = $('#voucherSelect');
    const applyBtn = $('#applyVoucher');

    // Xử lý nút áp dụng Voucher
    applyBtn.on('click', function(){
        const selectedOption = voucherSelect.find('option:selected');
        const val = voucherSelect.val();
        
        if(val) {
            const type = selectedOption.data('type');
            const value = parseFloat(selectedOption.data('value'));
            const minOrder = parseFloat(selectedOption.data('min-order'));
            
            // Tính tạm tính để check điều kiện
            let tempSubtotal = 0;
            $('.cart-item').each(function(){
                const check = $(this).find('.cart-check');
                if(check.is(':checked')) {
                    const price = parseInt($(this).find('.cart-price').data('price'));
                    const qty = parseInt($(this).find('.qty-input').val()) || 1;
                    tempSubtotal += price * qty;
                }
            });
            
            if(minOrder > 0 && tempSubtotal < minOrder) {
                Swal.fire({ icon: 'error', title: 'Chưa đủ điều kiện', text: 'Đơn hàng tối thiểu phải từ ' + formatPrice(minOrder) });
                voucherSelect.val('');
                currentVoucher = null;
                updateTotal(); 
                return;
            }
            
            currentVoucher = { id: val, type: type, value: value, minOrder: minOrder };
            Swal.fire({ icon: 'success', title: 'Thành công', text: 'Áp dụng voucher thành công!', timer: 1500, showConfirmButton: false });
        } else {
            Swal.fire({ icon: 'warning', title: 'Chưa chọn voucher', text: 'Vui lòng chọn voucher!' });
            currentVoucher = null;
        }
        updateTotal();
    });

    // ======================================================
    // 3. HÀM TÍNH TỔNG TIỀN (Update Total)
    // ======================================================
    function updateTotal(){
        let subtotal = 0;
        $('.cart-item').each(function(){
            const check = $(this).find('.cart-check');
            if(check.is(':checked')) {
                const price = parseInt($(this).find('.cart-price').data('price'));
                const qty = parseInt($(this).find('.qty-input').val()) || 1;
                subtotal += price * qty;
            }
        });

        const shipping = 30000;
        let voucherDiscount = 0;

        // Tính Voucher
        if (currentVoucher) {
            if (currentVoucher.minOrder > 0 && subtotal < currentVoucher.minOrder) {
                voucherDiscount = 0; 
            } else {
                if(currentVoucher.type === 'fixed') {
                    voucherDiscount = currentVoucher.value;
                } else if(currentVoucher.type === 'percent') {
                    voucherDiscount = Math.round(subtotal * currentVoucher.value / 100);
                }
            }
            $('#selectedVoucherId').val(currentVoucher.id);
        } else {
            $('#selectedVoucherId').val('');
        }

        // Tính Điểm
        let pointsInputVal = parseInt($('#pointsInput').val()) || 0;
        const isUsingPoints = $('input[name="use_points_choice"]:checked').val() === '1';
        let pointsDiscount = 0;
        
        if (isUsingPoints) {
            // Không được trừ quá số tiền cần thanh toán
            const maxAllowedDeduction = Math.max(subtotal + shipping - voucherDiscount, 0);
            pointsDiscount = Math.min(pointsInputVal, availablePoints, maxAllowedDeduction);
        }

        // Cập nhật giao diện Footer Fixed
        $('#subTotalFixed').text(formatPrice(subtotal));
        $('#voucherValueFixed').text(voucherDiscount ? '-' + formatPrice(voucherDiscount) : '0đ');
        $('#pointsValueFixed').text(pointsDiscount ? '-' + formatPrice(pointsDiscount) : '0đ');
        
        // Cập nhật Input Hidden gửi đi
        $('#usePointsField').val(pointsDiscount > 0 ? '1' : '0');
        $('#pointsToUseField').val(pointsDiscount);

        // Tổng cuối
        const total = Math.max(subtotal + shipping - voucherDiscount - pointsDiscount, 0);
        $('#totalPriceFixed').text(formatPrice(total));
        
        // Disable nút đặt hàng nếu giỏ hàng trống hoặc subtotal = 0 (tuỳ logic)
        if(subtotal === 0) {
             $('#fixedOrderBtn').prop('disabled', true);
        } else {
             $('#fixedOrderBtn').prop('disabled', false);
        }
    }

    // ======================================================
    // 4. XỬ LÝ SỐ LƯỢNG & XÓA & UPDATE CART
    // ======================================================
    window.updateQty = function(cartKey, change) {
        const input = $('input[data-key="'+cartKey+'"]');
        let qty = parseInt(input.val()) || 1;
        qty = Math.max(1, Math.min(5, qty + change));
        
        $.ajax({
            url: '/web_qlsp/api/customer/cart_api/update_quantity',
            method: 'POST',
            data: { cart_key: cartKey, quantity: qty },
            dataType: 'json',
            success: function(res) {
                if (res && res.success) { 
                    input.val(res.quantity); // Cập nhật lại input value
                    updateTotal(); 
                    updateCartCount(); 
                } else { 
                    Swal.fire('Lỗi', res.message || 'Không đủ số lượng', 'error'); 
                    if (res && typeof res.currentQty !== 'undefined') input.val(res.currentQty);
                    updateTotal(); 
                }
            },
            error: function() { updateTotal(); }
        });
    }

    window.removeItem = function(cartKey) {
        Swal.fire({
            title: 'Xóa sản phẩm?', text: "Bạn chắc chắn muốn xóa?", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/web_qlsp/api/customer/cart_api/remove_item', method: 'POST', data: { cart_key: cartKey },
                    success: function() { location.reload(); }
                });
            }
        });
    }

    function updateCartCount() {
        $.ajax({
            url: '/web_qlsp/api/customer/cart_api/get_cart_count', method: 'GET',
            success: function(response) {
                const data = JSON.parse(response);
                $('#cart-count').text(data.count);
            }
        });
    }

    // Sự kiện checkbox chọn sản phẩm
    $(document).on('change', '.cart-check', function() {
        updateTotal();
    });

    // Chạy lần đầu
    if ($('.cart-item').length > 0) updateTotal();

    // Logic input điểm
    $('input[name="use_points_choice"]').on('change', function() {
        const use = $(this).val() === '1';
        const input = $('#pointsInput');
        if (use) {
            input.prop('disabled', false);
        } else {
            input.prop('disabled', true);
            input.val(0);
        }
        updateTotal();
    });

    $('#pointsInput').on('input', function() {
        let val = parseInt($(this).val()) || 0;
        if (val < 0) val = 0;
        if (val > availablePoints) val = availablePoints;
        $(this).val(val); 
        updateTotal();
    });

    // Hiển thị phương thức thanh toán
    $('input[name="payment_method"]').on('change', function() {
        const method = $(this).val();
        $('#paymentMethodFixed').text(method === 'vnpay' ? 'VNPAY' : 'COD');
    });

    // ======================================================
    // 5. QUAN TRỌNG: SỬA NÚT ĐẶT HÀNG FOOTER & SUBMIT FORM
    // ======================================================
    
    // Khi bấm nút ở Footer, ta kích hoạt sự kiện submit của form
   // ======================================================
    // 5. QUAN TRỌNG: SỬA NÚT ĐẶT HÀNG FOOTER & SUBMIT FORM
    // ======================================================
    
    // Khi bấm nút ở Footer, ta kích hoạt sự kiện submit của form
    $('#fixedOrderBtn').on('click', function(e) {
        e.preventDefault(); // Ngăn hành vi mặc định của button type=button (dù không cần thiết lắm)
        if(!$(this).is(':disabled')){
            $('#checkoutForm').submit(); // Dùng .submit() thay vì .trigger('submit')
        }
    });

    // Bắt sự kiện submit form để gửi AJAX
    $('#checkoutForm').on('submit', function(e) {
        e.preventDefault(); // CHẶN Load lại trang

        var formActionUrl = $(this).attr('action');
        var formData = new FormData(this);

        // DEBUG: Kiểm tra xem Data đã vào Form chưa
        console.log("Đang gửi dữ liệu đến URL:", formActionUrl);
        for (var pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]); 
        }

        Swal.fire({
            title: 'Đang xử lý...',
            text: 'Vui lòng chờ trong giây lát',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: formActionUrl, // Hãy chắc chắn URL này trỏ đúng vào method checkout() trong cart_api.php
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                console.log("Server trả về:", response); // Log kết quả từ server

                if (response && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Đặt hàng thành công!',
                        text: 'Đang chuyển hướng...',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Chuyển hướng theo URL do server trả về
                        if(response.redirect_url) {
                            window.location.href = response.redirect_url;
                        } else {
                            // Fallback nếu server quên trả về redirect_url
                            window.location.href = '/web_qlsp/your_order';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Thất bại',
                        text: response ? (response.message || 'Có lỗi xảy ra.') : 'Lỗi không xác định.'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Lỗi AJAX:", status, error);
                console.error("Chi tiết response:", xhr.responseText); 
                
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi kết nối',
                    text: 'Không thể kết nối đến máy chủ. Nhấn F12 mở Console để xem chi tiết.'
                });
            }
        });
    });

}); // End document.ready
</script>
<link rel="stylesheet" href="/web_qlsp/Public/Css/cart_details.css">
