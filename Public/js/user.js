function confirmDelete(deleteUrl) {
    Swal.fire({
        title: 'Bạn có chắc chắn?',
        text: "Dữ liệu khách hàng sẽ bị mất vĩnh viễn!",
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

// Xử lý khi nhấn nút chi tiết
document.querySelectorAll('.btn-detail').forEach(btn => {
    btn.addEventListener('click', function() {
        const fullname = this.dataset.fullname;
        const email = this.dataset.email;
        const phone = this.dataset.phone;
        const points = this.dataset.points;
        const role = this.dataset.role;
        const province = this.dataset.province;
        const district = this.dataset.district;
        const ward = this.dataset.ward;
        const address = this.dataset.address;
        const googleid = this.dataset.googleid;
        const avatar = this.dataset.avatar;
        const created = this.dataset.created;
        const password = this.dataset.password;

        // Hiển thị dữ liệu
        document.getElementById('detail_avatar').src = avatar;
        document.getElementById('detail_fullname').textContent = fullname;
        document.getElementById('detail_email').textContent = email;
        document.getElementById('detail_phone').textContent = phone || 'Chưa cập nhật';
        document.getElementById('detail_points').textContent = parseInt(points).toLocaleString() + ' điểm';
        document.getElementById('detail_created').textContent = created;
        document.getElementById('detail_province').textContent = province || 'Chưa cập nhật';
        document.getElementById('detail_district').textContent = district || 'Chưa cập nhật';
        document.getElementById('detail_ward').textContent = ward || 'Chưa cập nhật';
        document.getElementById('detail_address').textContent = address || 'Chưa cập nhật';
        document.getElementById('detail_password').textContent = password || 'N/A';
        
        // Role badge
        const roleBadge = document.getElementById('detail_role_badge');
        if(role === 'admin') {
            roleBadge.textContent = 'Quản trị viên';
            roleBadge.className = 'badge bg-danger';
        } else {
            roleBadge.textContent = 'Khách hàng';
            roleBadge.className = 'badge bg-primary';
        }
        
        // Google badge
        const googleBadge = document.getElementById('detail_google_badge');
        if(googleid) {
            googleBadge.style.display = 'inline-block';
        } else {
            googleBadge.style.display = 'none';
        }
    });
});



