<?php
// Trang xác nhận kết quả thanh toán
// Dữ liệu được truyền từ controller qua $this->view()
$order_id = $order_id ?? 0;
$status = $status ?? '';
$message = $message ?? '';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php if ($status === 'success'): ?>
                <div class="card border-0 shadow-lg">
                    <div class="card-body text-center p-5">
                        <div style="margin: 0 auto 30px;">
                            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                                <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                            </svg>
                        </div>
                        <h2 class="mb-3" style="color: #2f5acf; font-weight: 700;">Thanh Toán Thành Công!</h2>
                        <p class="text-muted mb-2">Đơn hàng <span class="badge" style="background-color: #2f5acf; font-size: 14px;">#<?php echo htmlspecialchars($order_id); ?></span> đã được xác nhận.</p>
                        <p class="text-muted small">Chúng tôi đang xử lý đơn hàng của bạn.</p>
                        
                        <div class="mt-4 mb-4">
                            <p class="mb-2"><strong>Các bước tiếp theo:</strong></p>
                            <ul class="text-start" style="display: inline-block;">
                                <li><i class="fas fa-check" style="color: #2f5acf;"></i> <small>Kiểm tra email xác nhận đơn hàng</small></li>
                                <li><i class="fas fa-check" style="color: #2f5acf;"></i> <small>Theo dõi trạng thái giao hàng</small></li>
                                <li><i class="fas fa-check" style="color: #2f5acf;"></i> <small>Nhận sản phẩm tại nhà</small></li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="/web_qlsp/your_order" class="btn btn-lg" style="background-color: #2f5acf; color: white; font-weight: 600;">
                                <i class="fas fa-box"></i> Xem Chi Tiết Đơn Hàng
                            </a>
                            <a href="/web_qlsp/product_list_customer" class="btn btn-outline-secondary">
                                <i class="fas fa-shopping-bag"></i> Tiếp Tục Mua Sắm
                            </a>
                        </div>

                        <p class="text-muted mt-4 small">
                            <i class="fas fa-info-circle"></i> Tự động chuyển hướng trong <span id="countdown" style="font-weight: bold;">5</span> giây...
                        </p>
                    </div>
                </div>

                <style>
                    .checkmark {
                        width: 80px;
                        height: 80px;
                        margin: 0 auto;
                    }

                    .checkmark__circle {
                        stroke-dasharray: 166;
                        stroke-dashoffset: 166;
                        stroke-width: 2;
                        stroke-miterlimit: 10;
                        stroke: #4caf50;
                        fill: none;
                        animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
                    }

                    .checkmark__check {
                        stroke-dasharray: 48;
                        stroke-dashoffset: 48;
                        stroke-width: 2;
                        stroke-linecap: round;
                        stroke-linejoin: round;
                        stroke: #4caf50;
                        animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.4s forwards;
                    }

                    @keyframes stroke {
                        100% {
                            stroke-dashoffset: 0;
                        }
                    }
                </style>

                <script>
                    let countdown = 5;
                    const countdownEl = document.getElementById('countdown');
                    
                    const timer = setInterval(() => {
                        countdown--;
                        countdownEl.textContent = countdown;
                        if (countdown <= 0) {
                            clearInterval(timer);
                            window.location.href = '/web_qlsp/your_order';
                        }
                    }, 1000);
                </script>

            <?php elseif ($status === 'failed'): ?>
                <div class="card border-0 shadow-lg">
                    <div class="card-body text-center p-5">
                        <div style="margin: 0 auto 30px;">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 80px; height: 80px; color: #dc3545;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </div>
                        <h2 class="mb-3" style="color: #dc3545; font-weight: 700;">Thanh Toán Thất Bại</h2>
                        <p class="text-muted mb-2">Đơn hàng <span class="badge bg-danger" style="font-size: 14px;">#<?php echo htmlspecialchars($order_id); ?></span> <strong>Không được xác nhận</strong>.</p>
                        
                        <div class="alert alert-warning mt-4 mb-4" role="alert" style="text-align: left;">
                            <div style="margin-bottom: 15px;">
                                <strong style="color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Lý do:</strong>
                                <p style="margin-top: 8px; margin-bottom: 0;" class="small"><?php echo htmlspecialchars($message); ?></p>
                            </div>
                            
                            <!-- Hướng dẫn cho từng trường hợp -->
                            <?php 
                            // Kiểm tra nếu là lỗi timeout/expired
                            if (strpos($message, 'vượt quá thời gian') !== false || strpos($message, 'hết hạn') !== false || strpos($message, 'timeout') !== false):
                            ?>
                            <hr>
                            <div style="background: #fff3cd; padding: 12px; border-radius: 6px; margin-top: 15px;">
                                <p class="mb-2" style="color: #856404;"><strong>💡 Cách xử lý:</strong></p>
                                <ul style="margin-bottom: 0; padding-left: 20px; color: #856404;">
                                    <li><small><strong>Bước 1:</strong> Kiểm tra tài khoản ngân hàng xem tiền có bị trừ không</small></li>
                                    <li><small><strong>Bước 2:</strong> Nếu tiền bị trừ, vui lòng chờ 24-48 giờ để VNPay hoàn tiền</small></li>
                                    <li><small><strong>Bước 3:</strong> Quay lại danh sách đơn hàng và click "Thanh toán lại" để tiếp tục</small></li>
                                    <li><small><strong>Bước 4:</strong> Nếu có vấn đề, liên hệ <a href="mailto:support@coolmate.me">hỗ trợ@coolmate.me</a></small></li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="/web_qlsp/payment/create/<?php echo $order_id; ?>" class="btn btn-lg" style="background-color: #2f5acf; color: white; font-weight: 600;">
                                <i class="fas fa-redo"></i> Thử Lại Thanh Toán
                            </a>
                            <a href="/web_qlsp/your_order" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Quay Lại Đơn Hàng
                            </a>
                        </div>

                        <p class="text-muted mt-4 small">
                            <i class="fas fa-phone"></i> Cần trợ giúp? <a href="/web_qlsp/contact">Liên hệ chúng tôi</a>
                        </p>
                    </div>
                </div>

            <?php else: ?>
                <div class="card border-0 shadow-lg">
                    <div class="card-body text-center p-5">
                        <div class="spinner-border mb-4" role="status" style="color: #2f5acf; width: 60px; height: 60px; border-width: 0.3em;">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <h3 class="mb-3" style="color: #2f5acf; font-weight: 700;">Đang Xử Lý Thanh Toán...</h3>
                        <p class="text-muted">Vui lòng chờ trong khi chúng tôi xác nhận kết quả thanh toán của bạn.</p>
                        <p class="text-muted small mt-3"><strong>Lưu ý:</strong> Không tắt trang này cho đến khi hoàn tất.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
