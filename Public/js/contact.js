document.addEventListener('DOMContentLoaded', function() {
    var contactWrapper = document.getElementById('contactWrapper');
    var toggleBtn = document.getElementById('toggleBtn');
    if (toggleBtn && contactWrapper) {
        toggleBtn.addEventListener('click', function() {
            contactWrapper.classList.toggle('active');
        });
    }
});

function showLoginAlert() {
    // Nếu bạn đã dùng hàm showAlert ở câu hỏi trước thì gọi nó ở đây
    if (typeof showAlert === "function") {
        showAlert('Vui lòng đăng nhập để xem giỏ hàng của bạn!', 'error');
    } else {
        console.log("Yêu cầu đăng nhập để xem giỏ hàng");
    }
}