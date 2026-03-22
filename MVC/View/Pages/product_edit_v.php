<?php
// Define helper function first
function getColorHex($color) {
    $colors = [
        'Đen' => '#000000',
        'Trắng' => '#FFFFFF',
        'Đỏ' => '#DC3545',
        'Xanh' => '#0D6EFD',
        'Vàng' => '#FFC107',
        'Hồng' => '#E83E8C',
        'Xám' => '#6C757D',
        'Nâu' => '#8B4513',
        'Tím' => '#6F42C1',
        'Cam' => '#FD7E14'
    ];
    return isset($colors[$color]) ? $colors[$color] : '#6C757D';
}

if (isset($data['item']) && mysqli_num_rows($data['item']) > 0) {
    $product = mysqli_fetch_assoc($data['item']);
    $product_id = $product['id'];
    $variants = isset($data['variants']) ? $data['variants'] : [];
    $variant_images = isset($data['variant_images']) ? $data['variant_images'] : [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Sửa sản phẩm #<?= $product_id ?></h4>
</div>

<script>
    function generateSlug(title) {
        let slug = title.toLowerCase();
        slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
        slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
        slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
        slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
        slug = slug.replace(/ú|ù|ủ|ũ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
        slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
        slug = slug.replace(/đ/gi, 'd');
        slug = slug.replace(/[^a-z0-9\s-]/g, '');
        slug = slug.replace(/\s+/g, '-').replace(/-+/g, '-');
        document.getElementById('slug').value = slug;
    }
</script>

<div class="row justify-content-center">
    <div class="col-md-12">
        <!-- PHẦN 1: THÔNG TIN CƠ BẢN -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="fas fa-info-circle"></i> Thông tin cơ bản sản phẩm
            </div>
            <div class="card-body p-4">
                <form action="/web_qlsp/product_list/update" method="post" enctype="multipart/form-data" id="form-product-info">
                    <input type="hidden" name="id" value="<?php echo $product_id; ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" id="name" class="form-control" name="name"
                            value="<?php echo $product['name']; ?>"
                            onkeyup="generateSlug(this.value)" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Slug (Đường dẫn tĩnh) <span class="text-danger">*</span></label>
                        <input type="text" id="slug" class="form-control bg-light" name="slug"
                            value="<?php echo $product['slug']; ?>" readonly required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" required>
                                <option value="" disabled>-- Chọn danh mục --</option>
                                <?php
                                if (isset($data['categories_list']) && is_array($data['categories_list']) && count($data['categories_list']) > 0) {
                                    foreach ($data['categories_list'] as $row) {
                                        $selected = ($row['id'] == $product['category_id']) ? 'selected' : '';
                                ?>
                                        <option value="<?php echo $row['id']; ?>" <?php echo $selected; ?>>
                                            <?php echo $row['name']; ?>
                                        </option>
                                <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Bộ sưu tập <span class="text-danger">*</span></label>
                            <select class="form-select" name="collection_id" required>
                                <option value="" disabled>-- Chọn bộ sưu tập --</option>
                                <?php
                                if (isset($data['collections_list']) && is_array($data['collections_list']) && count($data['collections_list']) > 0) {
                                    foreach ($data['collections_list'] as $row) {
                                        $selected = ($row['id'] == $product['collection_id']) ? 'selected' : '';
                                ?>
                                        <option value="<?php echo $row['id']; ?>" <?php echo $selected; ?>>
                                            <?php echo $row['name']; ?>
                                        </option>
                                <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Giới tính <span class="text-danger">*</span></label>
                            <select class="form-select" name="gender" required>
                                <option value="Nam" <?php echo ($product['gender'] == 'Nam') ? 'selected' : ''; ?>>Nam</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Trạng thái <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_sale" name="is_sale" value="1"
                                    <?php echo ($product['is_sale'] == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label fw-bold" for="is_sale">
                                    Là sản phẩm khuyễn mãi
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" placeholder="150000" name="base_price" 
                            value="<?php echo $product['base_price']; ?>" step="0.01" required>
                        <small class="form-text text-muted d-block mt-2">Giá bán cơ bản của sản phẩm</small>
                    </div>

                    <div class="card bg-light mb-3">
                        <div class="card-header bg-secondary text-white fw-bold">
                            <i class="fas fa-images"></i> Ảnh đại diện
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label fw-bold">Chọn ảnh mới (nếu muốn thay đổi)</label>
                                    <input type="file" class="form-control" accept="image/*" name="thumbnail">
                                    <small class="form-text text-muted d-block mt-2">Để trống nếu không muốn thay đổi</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Ảnh hiện tại</label>
                                    <img src="/web_qlsp/Public/Picture/<?= $product['thumbnail'] ?>" 
                                        width="100" height="100"
                                        style="object-fit: cover; border-radius: 8px; border: 1px solid #eee;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Mô tả chi tiết <span class="text-danger">*</span></label>
                        <textarea class="form-control" rows="6" name="description" required><?php echo $product['description']; ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
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

        <!-- PHẦN 2: DANH SÁCH VARIANTS -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white fw-bold">
                <i class="fas fa-palette"></i> Danh sách Variants
            </div>
            <div class="card-body p-4">
                <?php 
                $variants = isset($variants) && is_array($variants) ? $variants : [];
                $variant_images = isset($variant_images) && is_array($variant_images) ? $variant_images : [];
                ?>
                <?php if (count($variants) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="8%">ID</th>
                                    <th width="12%">Màu sắc</th>
                                    <th width="10%">Kích cỡ</th>
                                    <th width="15%">Giá nhập</th>
                                    <th width="12%">Số lượng</th>
                                    <th width="25%">Ảnh chi tiết</th>
                                    <th width="18%" class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($variants as $variant): ?>
                                <tr>
                                    <td class="fw-bold">#<?php echo $variant['id']; ?></td>
                                    <td>
                                        <span class="badge" style="background: <?php echo getColorHex($variant['color']); ?>; color: <?php echo ($variant['color'] === 'Trắng') ? '#000' : 'white'; ?>;">
                                            <?php echo $variant['color']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $variant['size']; ?></span>
                                    </td>
                                    <td class="text-muted fw-bold"><?php echo number_format($variant['input_price']); ?>đ</td>
                                    <td>
                                        <?php 
                                        $stock = $variant['stock'];
                                        $badge_class = $stock > 50 ? 'bg-success' : ($stock > 0 ? 'bg-warning' : 'bg-danger');
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <i class="fas fa-box"></i> <?php echo $stock; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $variant_id = $variant['id'];
                                        $images_count = (isset($variant_images) && isset($variant_images[$variant_id])) ? count($variant_images[$variant_id]) : 0;
                                        if ($images_count > 0):
                                        ?>
                                            <div class="d-flex gap-1">
                                                <?php 
                                                $display_count = min(3, $images_count);
                                                for ($i = 0; $i < $display_count; $i++): 
                                                    $img = $variant_images[$variant_id][$i];
                                                ?>
                                                    <img src="/web_qlsp/Public/Picture/<?php echo $img['image_url']; ?>" 
                                                         width="40" height="40" 
                                                         style="object-fit: cover; border-radius: 4px; border: 1px solid #ddd;"
                                                         title="<?php echo $img['image_url']; ?>">
                                                <?php endfor; ?>
                                                <?php if ($images_count > 3): ?>
                                                    <span class="badge bg-light text-dark border align-self-center">+<?php echo $images_count - 3; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Không có ảnh</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="/web_qlsp/product_list/suaVariant/<?php echo $variant['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary me-1" title="Sửa variant">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                        <a href="javascript:void(0);" 
                                           onclick="confirmDeleteVariant(<?php echo $variant['id']; ?>)" 
                                           class="btn btn-sm btn-outline-danger" title="Xóa variant">
                                            <i class="fas fa-trash"></i> Xóa
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-lightbulb"></i> <strong>Lưu ý:</strong> Để sửa chi tiết màu, kích cỡ, giá nhập, số lượng và ảnh của variant, vui lòng click nút "Sửa"
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Sản phẩm này chưa có variant nào. <a href="/web_qlsp/product_add">Thêm variant ngay</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/web_qlsp/Public/js/result.js"></script>

<script>
    function confirmDeleteVariant(variantId) {
        Swal.fire({
            title: 'Xác nhận xóa',
            text: 'Bạn có chắc chắn muốn xóa variant này? Hành động này không thể hoàn tác.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '/web_qlsp/product_list/deleteVariant/' + variantId;
            }
        });
    }
</script>

<?php } else { ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> Không tìm thấy sản phẩm này!
    </div>
<?php } ?>