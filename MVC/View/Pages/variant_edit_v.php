<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Sửa Variant <span class="text-primary">#<span id="display_variant_id"><?= $data['variant_id'] ?></span></span></h4>
    <button class="btn btn-outline-secondary" onclick="history.back()">
        <i class="fas fa-arrow-left"></i> Quay lại
    </button>
</div>

<input type="hidden" id="variant_id" value="<?= $data['variant_id'] ?>">

<div class="row justify-content-center">
    <div class="col-md-8">
        
        <div id="variant-loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Đang tải dữ liệu biến thể...</p>
        </div>

        <div id="variant-content" style="display: none;">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-palette"></i> Thông tin Variant
                </div>
                <div class="card-body p-4">
                    <form id="formEditVariant">
                        <input type="hidden" name="variant_id" id="v_id">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Màu sắc <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="color" id="v_color" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Kích cỡ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="size" id="v_size" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Giá nhập (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control fw-bold text-danger" name="input_price" id="v_input_price" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Số lượng <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="stock" id="v_stock" required>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white fw-bold">
                    <i class="fas fa-images"></i> Quản lý Ảnh chi tiết
                </div>
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">Ảnh hiện tại:</h6>
                    
                    <div class="d-flex flex-wrap gap-3 mb-4" id="images_container">
                        </div>

                    <h6 class="fw-bold mb-3 border-bottom pb-2 mt-4">Thêm ảnh mới:</h6>
                    <form id="formAddVariantImages" class="bg-light p-3 rounded border border-dashed">
                        <input type="hidden" name="variant_id" id="v_upload_id">
                        
                        <div class="mb-3">
                            <label class="form-label text-muted"><i class="fas fa-upload"></i> Chọn file ảnh từ máy tính</label>
                            <input type="file" class="form-control" name="detail_images[]" multiple accept="image/*" required>
                            <small class="form-text text-muted mt-1 d-block">Có thể bấm Ctrl (hoặc Cmd) để chọn nhiều ảnh cùng lúc.</small>
                        </div>

                        <button type="submit" class="btn btn-success fw-bold">
                            <i class="fas fa-cloud-upload-alt"></i> Tải lên ngay
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const API_BASE = '/web_qlsp/product_list';
    const VARIANT_ID = document.getElementById('variant_id').value;
    let productIdToReturn = null; // Lưu lại ID sản phẩm mẹ để làm nút quay lại

    // ==========================================
    // 1. TẢI DỮ LIỆU BIẾN THỂ (Để mượn lại API của Product)
    // ==========================================
    // Do hệ thống hiện tại đang trả toàn bộ variant khi gọi chi tiết Product, 
    // ta cần "đi đường vòng" một chút: Gọi API cũ của PHP để lấy cục JSON, hoặc 
    // nếu bạn muốn mình viết một API get_single_variant riêng cũng được.
    // Ở đây mình dùng cách đơn giản: Vì trang này render từ hàm suaVariant($id) của PHP cũ, 
    // và bạn ĐÃ CÓ data truyền từ PHP sang, ta sẽ gán thẳng nó vào JavaScript luôn cho nhanh!

    const variantData = <?php echo isset($data['variant']) ? json_encode($data['variant']) : 'null'; ?>;
    const imagesData = <?php echo isset($data['images']) ? json_encode($data['images']) : '[]'; ?>;

    document.addEventListener("DOMContentLoaded", () => {
        if (!variantData) {
            Swal.fire('Lỗi', 'Không tìm thấy biến thể', 'error').then(() => history.back());
            return;
        }

        productIdToReturn = variantData.product_id;

        // Điền form
        document.getElementById('v_id').value = variantData.id;
        document.getElementById('v_upload_id').value = variantData.id;
        document.getElementById('v_color').value = variantData.color;
        document.getElementById('v_size').value = variantData.size;
        document.getElementById('v_input_price').value = variantData.input_price;
        document.getElementById('v_stock').value = variantData.stock;

        // Vẽ ảnh
        renderImages(imagesData);

        document.getElementById('variant-loading').style.display = 'none';
        document.getElementById('variant-content').style.display = 'block';
    });

    function renderImages(images) {
        const container = document.getElementById('images_container');
        let html = '';
        if(images && images.length > 0) {
            images.forEach(img => {
                html += `
                <div class="card shadow-sm border-0" style="width: 140px;">
                    <img src="/web_qlsp/Public/Picture/${img.image_url}" class="card-img-top" style="height: 140px; object-fit: cover;">
                    <div class="card-body p-2 text-center bg-light">
                        <button type="button" class="btn btn-sm btn-danger w-100 fw-bold" onclick="deleteImage(${img.id})">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </div>
                </div>`;
            });
        } else {
            html = `<p class="text-muted w-100 mb-0"><i class="fas fa-images"></i> Chưa có ảnh nào.</p>`;
        }
        container.innerHTML = html;
    }

    // ==========================================
    // 2. LƯU THÔNG TIN TEXT
    // ==========================================
    document.getElementById('formEditVariant').addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({ title: 'Đang lưu...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch(`${API_BASE}/api_update_variant`, { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({ 
                        icon: 'success', title: 'Đã lưu', text: 'Cập nhật thành công', showCancelButton: true,
                        confirmButtonText: 'Về trang Sản phẩm', cancelButtonText: 'Ở lại'
                    }).then((result) => {
                        if (result.isConfirmed && productIdToReturn) {
                            window.location.href = `/web_qlsp/product_list/sua/${productIdToReturn}`;
                        }
                    });
                } else {
                    Swal.fire('Lỗi', data.message, 'error');
                }
            });
    });

    // ==========================================
    // 3. UPLOAD ẢNH MỚI
    // ==========================================
    document.getElementById('formAddVariantImages').addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({ title: 'Đang tải ảnh...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        fetch(`${API_BASE}/api_upload_variant_images`, { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({ icon: 'success', title: 'Hoàn tất', timer: 1000, showConfirmButton: false });
                    setTimeout(() => location.reload(), 1000); // Reload nhanh để lấy ảnh từ DB
                } else {
                    Swal.fire('Lỗi', data.message, 'error');
                }
            });
    });

    // ==========================================
    // 4. XÓA ẢNH
    // ==========================================
    function deleteImage(imageId) {
        Swal.fire({
            title: 'Xóa ảnh này?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${API_BASE}/api_delete_variant_image/${imageId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload(); // Reload nhanh để cập nhật khung ảnh
                        } else {
                            Swal.fire('Lỗi', data.message, 'error');
                        }
                    });
            }
        });
    }
</script>