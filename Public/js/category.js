// Hàm hiển thị xác nhận trước khi xóa
function confirmDelete(deleteUrl) {
    Swal.fire({
        title: 'Bạn có chắc chắn?',
        text: "Dữ liệu danh mục sẽ bị mất vĩnh viễn!",
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

/**
 * Chuyển đổi Tiêu đề tiếng Việt sang Slug không dấu
 */
function generateSlug(title, targetId) {
    let slug = title.toLowerCase();
    slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
    slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
    slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
    slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
    slug = slug.replace(/ú|ù|ủ|ũ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
    slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
    slug = slug.replace(/đ/gi, 'd');
    slug = slug.replace(/\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\:|\;|_/gi, '');
    slug = slug.replace(/ /gi, "-");
    slug = slug.replace(/\-\-\-\-\-/gi, '-');
    slug = slug.replace(/\-\-\-\-/gi, '-');
    slug = slug.replace(/\-\-\-/gi, '-');
    slug = slug.replace(/\-\-/gi, '-');
    slug = '@' + slug + '@';
    slug = slug.replace(/\@\-|\-\@|\@/gi, '');
    
    const targetElement = document.getElementById(targetId);
    if (targetElement) {
        targetElement.value = slug;
    }
}

/**
 * Lắng nghe sự kiện Click vào nút Sửa
 * Sử dụng Event Delegation để đảm bảo hoạt động ngay cả khi danh sách được tải lại qua AJAX
 */
document.addEventListener('click', function (event) {
    // Kiểm tra xem phần tử được click có lớp 'btn-edit' hoặc nằm trong 'btn-edit' không
    const btn = event.target.closest('.btn-edit');
    
    if (btn) {
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        const slug = btn.dataset.slug;
        const thumbnail = btn.dataset.thumbnail;

        // Điền dữ liệu vào form modal
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_slug').value = slug;
        document.getElementById('edit_old_image').value = thumbnail;

        // Hiển thị ảnh hiện tại
        const previewArea = document.getElementById('preview_area');
        const previewImg = document.getElementById('edit_preview_img');

        if (thumbnail && previewImg) {
            previewArea.style.display = 'block';
            previewImg.src = '/web_qlsp/Public/Picture/categories/' + thumbnail;
        } else if (previewArea) {
            previewArea.style.display = 'none';
        }
    }
});