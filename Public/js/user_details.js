
// Hàm hỗ trợ format tiền tệ trong JS
    function formatMoney(amount) {
        return parseInt(amount).toLocaleString('vi-VN') + ' VNĐ';
    }

    // Lắng nghe sự kiện click trên toàn bộ body cho các nút Xem đơn hàng (vì nút này được render bằng JS)
    document.addEventListener('click', function(e) {
        // Tìm element gần nhất có class btn-view-order
        const btn = e.target.closest('.btn-view-order');
        if (!btn) return;

        const orderId = btn.getAttribute('data-order-id');
        
        // Mở modal (Chưa có data, hiện loading hoặc mở luôn cũng được)
        const modalUserEl = document.getElementById('detailUserModal');
        const modalOrderEl = document.getElementById('orderDetailModal');

        const modalUser = bootstrap.Modal.getInstance(modalUserEl);
        const modalOrder = bootstrap.Modal.getInstance(modalOrderEl) || new bootstrap.Modal(modalOrderEl);

        // 1. Tạm ẩn Modal Khách hàng đi cho gọn
        if (modalUser) {
            modalUser.hide();
        }

        // 2. Hiện Modal Chi tiết đơn hàng lên
        modalOrder.show();

        // 3. Lắng nghe sự kiện: Khi Modal đơn hàng đóng lại -> Mở lại Modal Khách hàng
        modalOrderEl.addEventListener('hidden.bs.modal', function handler() {
            if (modalUser) {
                modalUser.show();
            }
            // Xóa sự kiện để không bị lặp lại ở những lần click sau
            modalOrderEl.removeEventListener('hidden.bs.modal', handler);
        });

        // Gắn ID tạm thời lên header để người dùng biết đang tải
        document.getElementById('modal_order_id').textContent = '#' + orderId + ' (Đang tải...)';
        document.getElementById('modal_order_items_body').innerHTML = '<tr><td colspan="5" class="text-center py-3"><div class="spinner-border text-primary spinner-border-sm"></div> Đang tải dữ liệu...</td></tr>';

        // Gọi API lấy dữ liệu
        fetch(`/web_qlsp/users/get_order_details/${orderId}`)
            .then(response => response.json())
            .then(data => {
                const info = data.info;
                const items = data.items;

                if(!info) {
                    Swal.fire('Lỗi', 'Không tìm thấy thông tin đơn hàng', 'error');
                    orderModal.hide();
                    return;
                }

                // 1. Điền thông tin giao hàng
                document.getElementById('modal_order_id').textContent = '#' + info.id;
                document.getElementById('modal_order_name').textContent = info.customer_name;
                document.getElementById('modal_order_phone').textContent = info.customer_phone;
                
                // Nối chuỗi địa chỉ
                const fullAddress = `${info.shipping_address_detail}, ${info.shipping_ward}, ${info.shipping_district}, ${info.shipping_province}`;
                document.getElementById('modal_order_address').textContent = fullAddress;
                document.getElementById('modal_order_note').textContent = info.note || 'Không có ghi chú';

                // 2. Điền tóm tắt thanh toán
                document.getElementById('modal_order_subtotal').textContent = formatMoney(info.subtotal);
                document.getElementById('modal_order_fee').textContent = formatMoney(info.shipping_fee);
                document.getElementById('modal_order_discount').textContent = '-' + formatMoney(info.discount_amount);
                document.getElementById('modal_order_points').textContent = '-' + formatMoney(info.points_discount);
                document.getElementById('modal_order_total').textContent = formatMoney(info.total_money);

                // 3. Render danh sách sản phẩm
                let itemsHtml = '';
                if(items.length > 0) {
                    items.forEach(item => {
                        // Hiển thị size/color nếu có
                        let variantText = '';
                        if(item.size || item.color) {
                            variantText = `<div class="text-muted small">Phân loại: ${item.color || ''} ${item.size ? '- ' + item.size : ''}</div>`;
                        }

                        // Xử lý ảnh (mặc định nếu không có ảnh)
                        let imgUrl = item.product_image ? `/web_qlsp/Public/Picture/${item.product_image}` : '/web_qlsp/Public/Picture/default-product.png';

                        itemsHtml += `
                            <tr>
                                <td class="text-center"><img src="${imgUrl}" alt="img" width="40" height="40" class="rounded object-fit-cover"></td>
                                <td>
                                    <div class="fw-bold text-dark">${item.product_name}</div>
                                    ${variantText}
                                </td>
                                <td class="text-center">${formatMoney(item.price)}</td>
                                <td class="text-center">${item.quantity}</td>
                                <td class="text-end fw-bold text-danger">${formatMoney(item.total)}</td>
                            </tr>
                        `;
                    });
                } else {
                    itemsHtml = '<tr><td colspan="5" class="text-center text-muted">Không có dữ liệu sản phẩm</td></tr>';
                }
                
                document.getElementById('modal_order_items_body').innerHTML = itemsHtml;
            })
            .catch(error => {
                console.error('Error fetching details:', error);
                document.getElementById('modal_order_items_body').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Lỗi kết nối dữ liệu.</td></tr>';
                document.getElementById('modal_order_id').textContent = '#' + orderId;
            });
    });



   
document.addEventListener('DOMContentLoaded', function() {
    // Lắng nghe sự kiện click trên các nút "Chi tiết"
    const detailButtons = document.querySelectorAll('.btn-detail');
    
    detailButtons.forEach(button => {
        button.addEventListener('click', function() {
            // 1. Gán dữ liệu vào Tab Thông tin cá nhân (Code cũ của bạn, tôi viết lại cho gọn)
            const userId = this.getAttribute('data-id');
            document.getElementById('detail_fullname').textContent = this.getAttribute('data-fullname');
            document.getElementById('detail_email').textContent = this.getAttribute('data-email');
            document.getElementById('detail_phone').textContent = this.getAttribute('data-phone') || 'Chưa cập nhật';
            document.getElementById('detail_points').textContent = this.getAttribute('data-points') + ' điểm';
            document.getElementById('detail_created').textContent = this.getAttribute('data-created');
            document.getElementById('detail_address').textContent = this.getAttribute('data-address') || 'Chưa cập nhật';
            document.getElementById('detail_province').textContent = this.getAttribute('data-province') || 'N/A';
            document.getElementById('detail_district').textContent = this.getAttribute('data-district') || 'N/A';
            document.getElementById('detail_ward').textContent = this.getAttribute('data-ward') || 'N/A';
            document.getElementById('detail_password').textContent = this.getAttribute('data-password');
            document.getElementById('detail_avatar').src = this.getAttribute('data-avatar');

            // Hiển thị badge Google nếu có
            if (this.getAttribute('data-googleid')) {
                document.getElementById('detail_google_badge').style.display = 'inline-block';
            } else {
                document.getElementById('detail_google_badge').style.display = 'none';
            }

            // 2. Gọi AJAX để lấy lịch sử mua hàng
            const historyBody = document.getElementById('order_history_body');
            // Hiển thị trạng thái đang tải
            historyBody.innerHTML = `<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm" role="status"></div> Đang tải dữ liệu...</td></tr>`;
            
            // Chuyển Tab mặc định về Thông tin cá nhân mỗi khi mở Modal
            var firstTab = new bootstrap.Tab(document.querySelector('#userTab button[data-bs-target="#info"]'));
            firstTab.show();

            // Thực hiện fetch API lấy lịch sử mua hàng
            fetch(`/web_qlsp/users/get_order_history/${userId}`) // (Hoặc ?id=${userId} nếu bạn đang dùng cách đó)
                .then(response => response.json())
                .then(data => {
                    historyBody.innerHTML = ''; // Xóa chữ Đang tải
                    
                    if (data.length === 0) {
                        historyBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">Khách hàng này chưa có đơn hàng nào.</td></tr>`;
                        return;
                    }

                    // Lặp qua mảng dữ liệu và tạo các hàng HTML
                    let html = '';
                    data.forEach(order => {
                        // 1. Logic trạng thái
                        let statusColor = 'bg-secondary';
                        let statusText = order.status;
                        
                        switch(order.status) {
                            case 'pending': statusText = 'Chờ xác nhận'; statusColor = 'bg-warning text-dark'; break;
                            case 'confirmed': statusText = 'Đã xác nhận'; statusColor = 'bg-info text-dark'; break;
                            case 'shipping': statusText = 'Đang giao'; statusColor = 'bg-primary'; break;
                            case 'completed': statusText = 'Hoàn thành'; statusColor = 'bg-success'; break;
                            case 'cancelled': statusText = 'Đã hủy'; statusColor = 'bg-danger'; break;
                        }

                        // 2. Render HTML (Đã thay thế cột Mã ĐH thành Nút bấm)
                        html += `
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary fw-bold btn-view-order" data-order-id="${order.id}">
                                        <i class="fas fa-search me-1"></i>#${order.id}
                                    </button>
                                </td>
                                <td>${order.created_at_format}</td>
                                <td class="text-danger fw-bold">${order.total_money_format}</td>
                                <td><span class="badge border border-secondary text-secondary">${order.payment_method || 'N/A'}</span></td>
                                <td><span class="badge ${statusColor}">${statusText}</span></td>
                            </tr>
                        `;
                    });
                    historyBody.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error fetching order history:', error);
                    historyBody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Có lỗi xảy ra khi tải dữ liệu.</td></tr>`;
                });
        });
    });
});