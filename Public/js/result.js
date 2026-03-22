
function checkFlashMessage(status) {
    if (!status) return;

    const config = {
        success: {
            title: 'Thành công!',
            text: 'Thao tác đã được thực hiện thành công.',
            icon: 'success'
        },
        error: {
            title: 'Lỗi!',
            text: 'Có lỗi xảy ra trong quá trình xử lý.',
            icon: 'error'
        }
    };

    const currentConfig = config[status];

    if (currentConfig) {
        Swal.fire({
            title: currentConfig.title,
            text: currentConfig.text,
            icon: currentConfig.icon,
            timer: 2000,
            showConfirmButton: false
        });
    }
}

function togglePassword(inputId, iconElement) {
    const input = document.getElementById(inputId);
    const icon = iconElement.querySelector('i');
    
    if (input.type === "password") {
        // 1. Chuyển sang chế độ xem (text)
        input.type = "text";
        // -> Đổi icon thành "Mắt gạch chéo" (ý nghĩa: bấm để ẩn đi)
        icon.className = 'far fa-eye'; 
    } else {
        // 2. Chuyển sang chế độ ẩn (password)
        input.type = "password";
        // -> Đổi icon thành "Mắt mở" (ý nghĩa: bấm để xem)
        icon.className = 'far fa-eye-slash'; 
    }
}