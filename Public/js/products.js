// file: /public/js/product.js

function confirmDelete(deleteUrl) {
    Swal.fire({
        title: "Bạn có chắc chắn muốn xóa?",
        text: "Dữ liệu này sẽ bị xóa vĩnh viễn và không thể khôi phục!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Đúng, xóa nó!",
        cancelButtonText: "Hủy bỏ"
    }).then((result) => {
        if (result.isConfirmed) {
            // Chuyển hướng đến URL xóa khi người dùng xác nhận
            window.location.href = deleteUrl;
        }
    });
}


function generateSlug(title, targetId) {
    let slug = title.toLowerCase();
    slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
    slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
    slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
    slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
    slug = slug.replace(/ú|ù|ủ|ũ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
    slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
    slug = slug.replace(/đ/gi, 'd');
    // Loại bỏ ký tự đặc biệt
    slug = slug.replace(/[^a-z0-9\s-]/g, '');
    // Chuyển khoảng trắng thành dấu gạch ngang và rút gọn chuỗi gạch ngang
    slug = slug.replace(/\s+/g, '-').replace(/-+/g, '-');

    // Nếu không truyền targetId, mặc định ghi vào input có id='slug'
    const target = targetId || 'slug';
    const targetElement = document.getElementById(target);
    if (targetElement) {
        targetElement.value = slug;
    }
    return slug;
}

function toggleVariants(productId) {
    const variantsRow = document.getElementById('variants-' + productId);
    const icon = document.getElementById('icon-' + productId);
    const button = icon.parentElement;
    
    if (variantsRow.style.display === 'none') {
        variantsRow.style.display = 'table-row';
        button.classList.add('expanded');
    } else {
        variantsRow.style.display = 'none';
        button.classList.remove('expanded');
    }
}

function confirmDeleteVariant(variantId) {
    Swal.fire({
        title: 'Xác nhận xóa variant?',
        text: 'Bạn có chắc muốn xóa variant này không? Hành động này không thể hoàn tác!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/web_qlsp/product_list/deleteVariant/' + variantId;
        }
    });
}