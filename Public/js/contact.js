document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.getElementById('contactWrapper');
    const toggleBtn = document.getElementById('toggleBtn');

    // 1. Bật/tắt menu khi click vào nút chính
    toggleBtn.addEventListener('click', (e) => {
        e.stopPropagation(); 
        wrapper.classList.toggle('active');
    });

    // 2. Tự động đóng menu nếu người dùng bấm ra ngoài khoảng trống
    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target) && wrapper.classList.contains('active')) {
            wrapper.classList.remove('active');
        }
    });
});

function showLoginAlert() {
    // Nếu bạn đã dùng hàm showAlert ở câu hỏi trước thì gọi nó ở đây
    if (typeof showAlert === "function") {
        showAlert('Vui lòng đăng nhập để xem giỏ hàng của bạn!', 'error');
    } else {
        console.log("Yêu cầu đăng nhập để xem giỏ hàng");
    }
}