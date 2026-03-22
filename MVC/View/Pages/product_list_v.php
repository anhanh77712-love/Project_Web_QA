<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Quản lý sản phẩm</h3>

</div>

<!-- Search + Button -->
<div class="toolbar-container d-flex align-items-center">

    <form method="POST" action="/web_qlsp/product_list/search" 
          class="d-flex align-items-center w-100">

        <div class="search-wrapper me-auto">
            <i class="fas fa-search search-icon"></i>
            <input type="text" name="txtSearch" class="form-control form-search" 
                   placeholder="Tìm kiếm sản phẩm..."
                   value="<?php echo isset($data['search']) ? $data['search'] : ''; ?>">
        </div>

        <div class="d-flex gap-2">
            
            <button type="submit" class="btn btn-dark-blue" name="btnTimkiem">
                <i class="fas fa-search"></i> Tìm
            </button>

            <button type="button" class="btn btn-light-gray" onclick="window.location.href='/web_qlsp/product_list/reset'">
                <i class="fas fa-undo-alt"></i> Làm mới
            </button>

            <button type="submit" class="btn btn-green" name="btnXuatExcel">
                <i class="fas fa-file-excel"></i> Xuất Excel
            </button>
            
            <button type="button" class="btn btn-blue" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import"></i> Nhập Excel
            </button>

            <button type="button" class="btn btn-dark-blue" onclick="window.location.href='/web_qlsp/product_add'" >
                <i class="fas fa-plus"></i> Thêm Sản Phẩm
            </button>
        </div>

    </form>

</div>


<!-- Table -->
<div class="card-table">
    <table class="table-modern">
        <thead>
            <tr>
                <th width="6%">ID</th>
                <th width="8%">Ảnh</th>
                <th width="18%">Tên sản phẩm</th>
                <th width="10%">Giá bán</th>
                <th width="12%">Danh mục</th>
                <th width="8%">Lượt xem</th>
                <th width="12%">Trạng thái</th>
                <th width="12%">Màu sắc</th>
                <th width="10%">Kích thước</th>
                <th width="8%" class="text-end">Hành động</th>
            </tr>
        </thead>
        <tbody id="loading-skeleton">
            <?php for ($i = 0; $i < 5; $i++): ?>
            <tr>
                <td>
                    <div class="skeleton" style="width:20px; height:15px;"></div>
                </td>
                
                <td>
                    <div class="skeleton" style="width:60px; height:60px; border-radius: 8px;"></div>
                </td>
                
                <td>
                    <div class="skeleton" style="width:90%; height:15px; margin-bottom: 5px;"></div>
                    <div class="skeleton" style="width:50%; height:10px;"></div>
                </td>
                
                <td>
                    <div class="skeleton" style="width:80px; height:15px;"></div>
                </td>
                
                <td>
                    <div class="skeleton" style="width:100px; height:25px; border-radius: 4px;"></div>
                </td>
                
                <td>
                    <div class="skeleton" style="width:40px; height:15px;"></div>
                </td>
                
                <td>
                    <div class="skeleton" style="width:80px; height:25px; border-radius: 4px;"></div>
                </td>
                
                <td>
                    <div class="skeleton" style="width:60px; height:20px; border-radius: 4px;"></div>
                </td>
                
                <td>
                    <div class="skeleton" style="width:30px; height:20px; border-radius: 4px;"></div>
                </td>
                
                <td class="text-end">
                    <div class="d-flex justify-content-end gap-2">
                        <div class="skeleton" style="width:20px; height:20px;"></div>
                        <div class="skeleton" style="width:20px; height:20px;"></div>
                    </div>
                </td>
            </tr>
            <?php endfor; ?>
        </tbody>
        <tbody id="actual-content" style="display: none;">
            <?php
            if (isset($data['products_list']) && is_array($data['products_list']) && count($data['products_list']) > 0) {
                foreach ($data['products_list'] as $p) {
                    $product_id = $p['id'];
                    
                    // Get variants for this product
                    $product_variants = isset($data['variants'][$product_id]) ? $data['variants'][$product_id] : [];
                    $variant_count = count($product_variants);
                    
                    // Nếu không có variant nào, hiển thị 1 dòng với thông tin product
                    if ($variant_count == 0) {
                        $variant_count = 1;
                    }
                    
                    // Loop through variants
                    foreach ($product_variants as $index => $variant) {
            ?>
                    <tr class="product-row">
                        <?php if ($index == 0): // Chỉ hiển thị cells chung ở dòng đầu tiên ?>
                        <td rowspan="<?php echo $variant_count; ?>" class="text-muted fw-bold">#<?php echo $product_id; ?></td>
                        <td rowspan="<?php echo $variant_count; ?>">
                            <img src="/web_qlsp/Public/Picture/<?php echo $p['thumbnail']; ?>"
                                width="60" height="60"
                                style="object-fit: cover; border-radius: 8px; border: 1px solid #eee;">
                            
                        </td>
                        <td rowspan="<?php echo $variant_count; ?>">
                            <div class="fw-bold text-dark"><?php echo $p['name']; ?></div>
                            <small class="text-muted"><i class="fas fa-link"></i> <?php echo $p['slug']; ?></small>
                        </td>
                        <td rowspan="<?php echo $variant_count; ?>" class="fw-bold text-primary"><?php echo number_format($p['base_price']); ?>đ</td>
                        <td rowspan="<?php echo $variant_count; ?>">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-folder"></i> <?php echo $p['category_name']; ?>
                            </span>
                        </td>
                        <td rowspan="<?php echo $variant_count; ?>">
                            <div class="d-flex align-items-center text-muted">
                                <i class="fas fa-eye me-1"></i>
                                <?php echo number_format($p['views']); ?>
                            </div>
                        </td>
                        <td rowspan="<?php echo $variant_count; ?>">
                            <?php if ($p['is_sale'] == 1): ?>
                                <span class="badge bg-danger">
                                    <i class="fas fa-tag"></i> Sale
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Bình thường
                                </span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        
                        <!-- Màu sắc và kích thước - mỗi variant 1 dòng -->
                        <td>
                            <?php $bg = getColorHex($variant['color']); $txt = isWhiteColor($variant['color']) ? '#000000' : '#FFFFFF'; ?>
                            <span class="badge" style="background: <?php echo $bg; ?>; color: <?php echo $txt; ?>; border: 1px solid #e0e0e0;">
                                <?php echo $variant['color']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo $variant['size']; ?></span>
                        </td>
                        
                        <?php if ($index == 0): // Hành động chỉ hiển thị ở dòng đầu tiên ?>
                        <!-- Hành động cho product -->
                        <td rowspan="<?php echo $variant_count; ?>" class="text-end">
                            <a href="/web_qlsp/product_list/sua/<?php echo $product_id; ?>" class="btn-icon text-primary me-1" title="Sửa">
                                <i class="fas fa-edit" style="width:18px;"></i>
                            </a>
                            <a href="javascript:void(0);" onclick="confirmDelete('/web_qlsp/product_list/delete/<?php echo $product_id; ?>')" class="btn-icon text-danger" title="Xóa">
                                <i class="fas fa-trash" style="width:18px;"></i>
                            </a>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php
                    }
                    
                    // Nếu không có variant, hiển thị 1 dòng trống
                    if (count($product_variants) == 0) {
                ?>
                    <tr class="product-row">
                        <td class="text-muted fw-bold">#<?php echo $product_id; ?></td>
                        <td>
                            <img src="/web_qlsp/<?php echo $p['thumbnail']; ?>"
                                width="60" height="60"
                                style="object-fit: cover; border-radius: 8px; border: 1px solid #eee;">
                               
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?php echo $p['name']; ?></div>
                            <small class="text-muted"><i class="fas fa-link"></i> <?php echo $p['slug']; ?></small>
                        </td>
                        <td class="fw-bold text-primary"><?php echo number_format($p['base_price']); ?>đ</td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-folder"></i> <?php echo $p['category_name']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center text-muted">
                                <i class="fas fa-eye me-1"></i>
                                <?php echo number_format($p['views']); ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($p['is_sale'] == 1): ?>
                                <span class="badge bg-danger">
                                    <i class="fas fa-tag"></i> Sale
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Bình thường
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><span class="text-muted">-</span></td>
                        <td><span class="text-muted">-</span></td>
                        <td class="text-end">
                            <a href="/web_qlsp/product_list/sua/<?php echo $product_id; ?>" class="btn-icon text-primary me-1" title="Sửa">
                                <i class="fas fa-edit" style="width:18px;"></i>
                            </a>
                           
                            <button class="btn-icon text-danger"
                            onclick="confirmDelete('/web_qlsp/product_list/delete/<?php echo $product_id; ?>')" title="Xóa">
                             <i class="fas fa-trash" style="width:18px;"></i>
                            </button>
                        </td>
                    </tr>
                <?php
                    }
                }
            } else {
                ?>
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>Không tìm thấy sản phẩm nào.</p>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>


<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="fas fa-file-upload"></i> Nhập dữ liệu Excel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="importTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="product-tab" data-bs-toggle="tab" data-bs-target="#product-import" type="button" role="tab">
                            <i class="fas fa-box"></i> Import Sản Phẩm
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="variant-tab" data-bs-toggle="tab" data-bs-target="#variant-import" type="button" role="tab">
                            <i class="fas fa-palette"></i> Import Biến Thể
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="importTabContent">
                    <!-- Tab Import Sản Phẩm -->
                    <div class="tab-pane fade show active" id="product-import" role="tabpanel">
                        <form action="/web_qlsp/product_list/importExcel" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="importType" value="product">
                            
                            <div class="alert alert-info" role="alert">
                                <small>
                                    <i class="fas fa-info-circle"></i> Chưa có file mẫu? 
                                    <a href="/web_qlsp/product_list/downloadProductTemplate" class="fw-bold">Tải file mẫu Sản Phẩm</a>
                                </small>
                                <div class="mt-2">
                                    <strong>Cột bắt buộc:</strong> Tên sản phẩm, CategoryID, BasePrice, Description, Gender, ThumbnailImages
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="fileExcelProduct" class="form-label fw-bold">Chọn file Excel (.xlsx, .xls)</label>
                                <input class="form-control" type="file" id="fileExcelProduct" name="txtfile" accept=".xlsx, .xls" required>
                                <div class="form-text text-muted">
                                    File chứa thông tin sản phẩm mới
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                                <button type="submit" name="btnUpload" class="btn btn-primary">
                                    <i class="fas fa-cloud-upload-alt"></i> Import Sản Phẩm
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab Import Biến Thể -->
                    <div class="tab-pane fade" id="variant-import" role="tabpanel">
                        <form action="/web_qlsp/product_list/importExcel" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="importType" value="variant">
                            
                            <div class="alert alert-info" role="alert">
                                <small>
                                    <i class="fas fa-info-circle"></i> Chưa có file mẫu? 
                                    <a href="/web_qlsp/product_list/downloadVariantTemplate" class="fw-bold">Tải file mẫu Biến Thể</a>
                                </small>
                                <div class="mt-2">
                                    <strong>Cột bắt buộc:</strong> Tên sản phẩm, Color, Size, Stock, InputPrice, Images
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="fileExcelVariant" class="form-label fw-bold">Chọn file Excel (.xlsx, .xls)</label>
                                <input class="form-control" type="file" id="fileExcelVariant" name="txtfile" accept=".xlsx, .xls" required>
                                <div class="form-text text-muted">
                                    File chứa biến thể cho các sản phẩm đã tồn tại
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                                <button type="submit" name="btnUpload" class="btn btn-success">
                                    <i class="fas fa-cloud-upload-alt"></i> Import Biến Thể
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
// Helper function to convert color name to hex
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

// Kiểm tra màu trắng để đổi màu chữ cho dễ đọc
function isWhiteColor($color) {
    $normalized = trim(mb_strtolower($color));
    if ($normalized === 'trắng' || $normalized === 'trang' || $normalized === 'white') {
        return true;
    }
    // Nếu color đã là mã hex
    if (preg_match('/^#?([a-f0-9]{6})$/i', $color, $m)) {
        $hex = '#' . strtoupper($m[1]);
        if ($hex === '#FFFFFF') return true;
    }
    return false;
}
?>
<link rel="stylesheet" href="/web_qlsp/Public/css/loading.css">
<link rel="stylesheet" href="/web_qlsp/Public/css/product_list.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script src="/web_qlsp/Public/js/products.js"></script>
<script src="/web_qlsp/Public/js/result.js"></script>
<script src="/web_qlsp/Public/js/loading.js"></script>
<?php if (isset($_SESSION['status_msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            checkFlashMessage("<?php echo $_SESSION['status_msg']; ?>");
        });
    </script>
    <?php unset($_SESSION['status_msg']); ?>
<?php endif; ?>