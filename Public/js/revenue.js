function applyFilters() {
    const fromInput = document.getElementById('fromDate');
    const toInput = document.getElementById('toDate');
    const statusSelect = document.getElementById('statusFilter');

    const from = fromInput.value;
    const to = toInput.value;
    const status = statusSelect ? statusSelect.value : 'all';
    
    const today = new Date().toISOString().split('T')[0];

    // ĐIỀU KIỆN 1 & 2: Ngày không được lớn hơn ngày hiện tại
    if (from && from > today) {
        alert('Ngày bắt đầu không được lớn hơn ngày hiện tại!');
        return;
    }
    if (to && to > today) {
        alert('Ngày kết thúc không được lớn hơn ngày hiện tại!');
        return;
    }

    // ĐIỀU KIỆN 3: Ngày bắt đầu phải nhỏ hơn hoặc bằng ngày kết thúc
    if (from && to && from > to) {
        alert('Ngày bắt đầu không được lớn hơn ngày kết thúc!');
        return;
    }

    // Xây dựng URL để render lại trang mà không mất dữ liệu lọc
    const params = new URLSearchParams();
    if (from) params.set('from', from);
    if (to) params.set('to', to);
    if (status && status !== 'all') params.set('status', status);

    // Lưu ý: Thay đổi đường dẫn này theo đúng controller của bạn (ví dụ: /revenue hoặc /orders)
    window.location.href = window.location.pathname + '?' + params.toString();
}

$(document).ready(function() {
        // Kiểm tra xem đã khởi tạo chưa để tránh lỗi duplicate
        if (!$.fn.DataTable.isDataTable('#ordersTable')) {
            $('#ordersTable').DataTable({
                "order": [[ 1, "desc" ]], // Sắp xếp theo cột Ngày đặt (index 1) giảm dần
                "language": {
                    "search": "Tìm kiếm nhanh:",
                    "lengthMenu": "Hiển thị _MENU_ dòng",
                    "info": "Đang xem _START_ đến _END_ trong tổng _TOTAL_ đơn",
                    "infoEmpty": "Không có dữ liệu",
                    "emptyTable": "Không tìm thấy đơn hàng nào phù hợp",
                    "paginate": { 
                        "next": '<i class="fa-solid fa-chevron-right"></i>', 
                        "previous": '<i class="fa-solid fa-chevron-left"></i>' 
                    }
                },
                "pageLength": 10, // Mặc định hiện 10 dòng
                "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>' 
                // (Cấu hình layout của DataTables cho đẹp hơn: l=length, f=filter, r=processing, t=table, i=info, p=pagination)
            });
        }
    });