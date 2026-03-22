// Hàm hiển thị xác nhận trước khi xóa
function confirmDelete(deleteUrl) {
    Swal.fire({
        title: 'Bạn có chắc chắn?',
        text: "Dữ liệu đơn hàng sẽ bị mất vĩnh viễn!",
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
