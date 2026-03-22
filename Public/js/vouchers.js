function confirmDelete(deleteUrl) {
    Swal.fire({
        title: 'Bạn có chắc chắn?',
        text: "Dữ liệu vouchers sẽ bị mất vĩnh viễn!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Đồng ý, xóa nó!',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            // Nếu người dùng đồng ý, chuyển hướng đến URL xóa
            window.location.href = deleteUrl;
        }
    });
}


document.addEventListener("DOMContentLoaded", function() {
    var editModalElement = document.getElementById('editVoucherModal');
    
    // Kiểm tra xem Modal có tồn tại không
    if (editModalElement) {
        editModalElement.addEventListener('show.bs.modal', function (event) {
            // Button đã kích hoạt modal
            var button = event.relatedTarget; 
            
            // Debug: Xem button có dữ liệu không (Bật F12 -> Console để xem)
            console.log("Đang lấy dữ liệu từ nút:", button);

            // 1. Lấy dữ liệu từ data-attributes
            var id = button.getAttribute('data-id');
            var code = button.getAttribute('data-code');
            var usageLimit = button.getAttribute('data-usage-limit');
            var startDate = button.getAttribute('data-start-date');
            var endDate = button.getAttribute('data-end-date');
            var value = button.getAttribute('data-value');
            var minOrder = button.getAttribute('data-min-order');
            var maxDiscount = button.getAttribute('data-max-discount');
            var status = button.getAttribute('data-status');

            // 2. Điền dữ liệu vào Form (Dựa trên ID trong HTML bạn gửi)
            var modal = this;
            modal.querySelector('#edit_id').value = id;
            modal.querySelector('#edit_code').value = code;
            modal.querySelector('#edit_usage_limit').value = usageLimit;
            
            // Ngày tháng: Bắt buộc phải đúng định dạng 2024-01-01T12:00
            modal.querySelector('#edit_start_date').value = startDate;
            modal.querySelector('#edit_end_date').value = endDate;

            modal.querySelector('#edit_value').value = value;
            modal.querySelector('#edit_min_order').value = minOrder;
            modal.querySelector('#edit_max_discount').value = maxDiscount;
            
            // Xử lý status (nếu null thì mặc định là 1)
            modal.querySelector('#edit_status').value = status ? status : 1;
        });
    }
});