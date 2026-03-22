<?php
// Get data from $data array
$variant = isset($data['variant']) ? $data['variant'] : null;
$variant_id = isset($data['variant_id']) ? $data['variant_id'] : 0;
$images = isset($data['images']) ? $data['images'] : [];

if (isset($variant) && !is_null($variant) && is_array($variant)) {
    $variant_data = $variant;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Sửa Variant #<?= $variant_id ?></h4>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <!-- PHẦN 1: THÔNG TIN VARIANT -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="fas fa-palette"></i> Thông tin Variant
            </div>
            <div class="card-body p-4">
                <form action="/web_qlsp/product_list/updateVariant" method="post" id="form-variant-info">
                    <input type="hidden" name="variant_id" value="<?php echo $variant_id; ?>">
                    <input type="hidden" name="product_id" value="<?php echo $variant_data['product_id']; ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Màu sắc <span class="text-danger">*</span></label>
                            <select class="form-select" name="color" required>
                                <option value="" disabled>-- Chọn màu --</option>
                                <?php
                                $colors = ['Đen', 'Trắng', 'Đỏ', 'Xanh', 'Vàng', 'Hồng', 'Xám', 'Nâu', 'Tím', 'Cam'];
                                foreach ($colors as $color) {
                                    $selected = ($color == $variant_data['color']) ? 'selected' : '';
                                    echo "<option value='$color' $selected>$color</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kích cỡ <span class="text-danger">*</span></label>
                            <select class="form-select" name="size" required>
                                <option value="" disabled>-- Chọn kích cỡ --</option>
                                <?php
                                $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
                                foreach ($sizes as $size) {
                                    $selected = ($size == $variant_data['size']) ? 'selected' : '';
                                    echo "<option value='$size' $selected>$size</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Giá nhập (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="input_price" 
                                value="<?php echo $variant_data['input_price']; ?>" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Số lượng <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="stock" 
                                value="<?php echo $variant_data['stock']; ?>" required>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mb-4">
                        <button type="submit" name="btnLuu" class="btn btn-primary-custom">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                        <button type="button" class="btn btn-light border" onclick="history.back()">
                            <i class="fas fa-times"></i> Hủy
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- PHẦN 2: QUẢN LÝ ẢNH CHI TIẾT -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-success text-white fw-bold">
                <i class="fas fa-images"></i> Ảnh chi tiết
            </div>
            <div class="card-body p-4">
                <h6 class="mb-3">Ảnh hiện tại:</h6>
                <?php if (count($images) > 0): ?>
                    <div class="row mb-4">
                        <?php foreach ($images as $image): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <img src="/web_qlsp/Public/Picture/<?php echo $image['image_url']; ?>" 
                                     class="card-img-top" style="height: 150px; object-fit: cover;">
                                <div class="card-body p-2">
                                    <small class="text-muted"><?php echo $image['image_url']; ?></small>
                                    <div class="mt-2">
                                        <a href="javascript:void(0);" 
                                           onclick="confirmDeleteImage(<?php echo $image['id']; ?>)"
                                           class="btn btn-sm btn-danger w-100">
                                            <i class="fas fa-trash"></i> Xóa
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Không có ảnh nào</p>
                <?php endif; ?>

                <hr>
                <h6 class="mb-3">Thêm ảnh mới:</h6>
                <form action="/web_qlsp/product_list/uploadVariantImages" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="variant_id" value="<?php echo $variant_id; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn ảnh</label>
                        <input type="file" class="form-control" name="detail_images[]" multiple accept="image/*">
                        <small class="form-text text-muted">Có thể chọn nhiều ảnh cùng lúc</small>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Tải lên
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteImage(imageId) {
    Swal.fire({
        title: 'Xác nhận xóa',
        text: 'Bạn có chắc chắn muốn xóa ảnh này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/web_qlsp/product_list/deleteVariantImage/' + imageId;
        }
    });
}
</script>

<?php } else { ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> Không tìm thấy variant này!
    </div>
<?php } ?>
