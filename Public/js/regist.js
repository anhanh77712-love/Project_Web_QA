document.addEventListener("DOMContentLoaded", function() {
    const phoneInput = document.getElementById('phoneInput');

    // Lắng nghe sự kiện khi người dùng gõ phím
    phoneInput.addEventListener('input', function() {
        // 1. Chỉ giữ lại số (xóa chữ và ký tự đặc biệt)
        this.value = this.value.replace(/[^0-9]/g, '');

        // 2. Kiểm tra độ dài
        // Nếu đã nhập (length > 0) nhưng KHÁC 10 ký tự thì báo lỗi
        if (this.value.length > 0 && this.value.length !== 10) {
            this.classList.add('is-invalid'); // Thêm class báo lỗi
            this.classList.remove('is-valid');
        } 
        // Nếu ĐÚNG 10 ký tự
        else if (this.value.length === 10) {
            this.classList.remove('is-invalid'); // Bỏ lỗi
            this.classList.add('is-valid');      // Thêm viền xanh
        } 
        // Nếu ô trống (người dùng xóa hết)
        else {
            this.classList.remove('is-invalid');
            this.classList.remove('is-valid');
        }
    });

    // Chặn submit form nếu SĐT chưa đúng 10 số
    // Lưu ý: Bạn cần selector chính xác form của bạn
    const registerForm = document.querySelector('form[action="/web_qlsp/register/do_register"]');
    
    if (registerForm) { // Kiểm tra xem form có tồn tại không để tránh lỗi console
        registerForm.addEventListener('submit', function(e) {
            // Kiểm tra nếu độ dài KHÁC 10
            if (phoneInput.value.length !== 10) {
                e.preventDefault(); // Ngừng gửi form
                phoneInput.classList.add('is-invalid'); // Hiện lỗi
                phoneInput.focus(); // Trỏ chuột vào ô sđt
                alert('Số điện thoại phải đúng 10 số!');
            }
        });
    }
});