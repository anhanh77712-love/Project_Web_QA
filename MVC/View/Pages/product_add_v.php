<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Quản lý sản phẩm</h4>
</div>

<!-- TAB CHỌN CHẾ ĐỘ -->
<div class="row justify-content-center mb-4">
    <div class="col-md-12">
        <ul class="nav nav-tabs" id="productTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="new-product-tab" data-bs-toggle="tab" data-bs-target="#new-product" type="button" role="tab">
                    <i class="fas fa-plus-circle"></i> Tạo sản phẩm mới
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="new-variant-tab" data-bs-toggle="tab" data-bs-target="#new-variant" type="button" role="tab">
                    <i class="fas fa-palette"></i> Thêm variant cho sản phẩm có sẵn
                </button>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content" id="productTabContent">
    <!-- TAB 1: TẠO SẢN PHẨM MỚI -->
    <div class="tab-pane fade show active" id="new-product" role="tabpanel">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold mb-3">
                            <i class="fas fa-info-circle text-primary"></i> Tạo sản phẩm mới + variant đầu tiên
                        </h5>
                        <form action="/web_qlsp/api/product_api/Add" method="post" enctype="multipart/form-data">

                    <!-- PHẦN 1: THÔNG TIN CƠ BẢN SẢN PHẨM -->
                    <div class="card bg-light mb-4">
                        <div class="card-header bg-primary text-white fw-bold">
                            <i class="fas fa-info-circle"></i> Thông tin cơ bản
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name"
                                    onkeyup="generateSlug(this.value)"
                                    placeholder="VD: Áo Thun Coolmate..." required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Slug (Đường dẫn tĩnh) <span class="text-danger">*</span></label>
                                <input type="text" id="slug" class="form-control bg-light" readonly name="slug"
                                    placeholder="VD: ao-thun-coolmate...">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-select" name="category_id" required>
                                        <option value="" disabled selected>-- Chọn danh mục --</option>
                                        <?php
                                        if (isset($data['categories_list']) && mysqli_num_rows($data['categories_list']) > 0) {
                                            while ($row = mysqli_fetch_assoc($data['categories_list'])) {
                                        ?>
                                                <option value="<?php echo $row['id']; ?>">
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
                                        <option value="" disabled selected>-- Chọn bộ sưu tập --</option>
                                        <?php
                                        if (isset($data['collections_list']) && mysqli_num_rows($data['collections_list']) > 0) {
                                            while ($row = mysqli_fetch_assoc($data['collections_list'])) {
                                        ?>
                                                <option value="<?php echo $row['id']; ?>">
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
                                        <option value="" disabled selected>-- Chọn giới tính --</option>
                                        <option value="Nam">Nam</option> 
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Trạng thái <span class="text-danger">*</span></label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_sale" name="is_sale" value="1">
                                        <label class="form-check-label fw-bold" for="is_sale">
                                            Là sản phẩm khuyễn mãi
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" placeholder="150000" name="base_price" step="0.01" required>
                                <small class="form-text text-muted d-block mt-2">Giá bán cơ bản của sản phẩm</small>
                            </div>
                        </div>
                    </div>

                    <!-- PHẦN 2: ẢNH -->
                    <div class="card bg-light mb-4">
                        <div class="card-header bg-primary text-white fw-bold">
                            <i class="fas fa-images"></i> Ảnh sản phẩm
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ảnh đại diện <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" accept="image/*" name="thumbnail" required>
                                <small class="form-text text-muted d-block mt-2">
                                    <i class="fas fa-info-circle"></i> Ảnh này sẽ hiển thị ở danh sách sản phẩm
                                </small>
                            </div>

                            <hr>

                            <div class="alert alert-info" role="alert">
                                <strong><i class="fas fa-lightbulb"></i> Lưu ý:</strong> Các ảnh chi tiết dưới đây sẽ được liên kết với variant (Màu + Kích cỡ) của sản phẩm này.
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Ảnh chi tiết cho variant này</label>
                                <input type="file" class="form-control" accept="image/*" name="detail_images[]" multiple>
                                <small class="form-text text-muted d-block mt-2">
                                    <i class="fas fa-info-circle"></i> Bạn có thể chọn nhiều ảnh. Nhấn Ctrl (Cmd trên Mac) + Click để chọn nhiều file
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- PHẦN 3: VARIANT (MÀU + KÍCH CỠ + GIÁ + SỐ LƯỢNG) -->
                    <div class="card bg-light mb-4">
                        <div class="card-header bg-success text-white fw-bold">
                            <i class="fas fa-palette"></i> Variant sản phẩm (Phiên bản)
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning" role="alert">
                                <strong><i class="fas fa-exclamation-triangle"></i> Quan trọng:</strong> Mỗi variant là một phiên bản cụ thể của sản phẩm với màu, kích cỡ, giá và số lượng riêng. Ảnh chi tiết ở trên sẽ được liên kết với variant này.
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Màu sắc <span class="text-danger">*</span></label>
                                    <select class="form-select" name="color" required>
                                        <option value="" disabled selected>-- Chọn màu sắc --</option>
                                        <option value="Đen">Đen</option>
                                        <option value="Trắng">Trắng</option>
                                        <option value="Đỏ">Đỏ</option>
                                        <option value="Xanh">Xanh</option>
                                        <option value="Vàng">Vàng</option>
                                        <option value="Hồng">Hồng</option>
                                        <option value="Xám">Xám</option>
                                        <option value="Nâu">Nâu</option>
                                        <option value="Tím">Tím</option>
                                        <option value="Cam">Cam</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Kích cỡ <span class="text-danger">*</span></label>
                                    <select class="form-select" name="size" required>
                                        <option value="" disabled selected>-- Chọn kích cỡ --</option>
                                        <option value="XS">XS</option>
                                        <option value="S">S</option>
                                        <option value="M">M</option>
                                        <option value="L">L</option>
                                        <option value="XL">XL</option>
                                        <option value="XXL">XXL</option>
                                        <option value="XXXL">XXXL</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Giá nhập (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" placeholder="100000" name="cost_price" step="0.01" required>
                                    <small class="form-text text-muted d-block mt-2">Giá vốn/giá nhập hàng</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Số lượng kho <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" placeholder="50" name="quantity" min="0" required>
                                    <small class="form-text text-muted d-block mt-2">Số lượng có sẵn cho variant (màu + kích cỡ) này</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PHẦN 4: MÔ TẢ CHI TIẾT -->
                    <div class="card bg-light mb-4">
                        <div class="card-header bg-primary text-white fw-bold">
                            <i class="fas fa-align-left"></i> Mô tả chi tiết
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold mb-0">Nội dung mô tả</label>
                                <button type="button" id="btnAiGemini" class="btn-ai-gemini">
                                    <i class="fas fa-wand-magic-sparkles"></i> Dùng AI Viết Hộ
                                </button>
                            </div>
                            <textarea class="form-control" rows="6" name="description"
                                placeholder="Mô tả chi tiết sản phẩm: chất liệu, cách sử dụng, bảo quản..."></textarea>
                        </div>
                    </div>

                    <!-- NÚT HÀNH ĐỘNG -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-custom" name="btnLuu">
                            <i class="fas fa-save"></i> Lưu sản phẩm
                        </button>
                        <button type="button" class="btn btn-light border" onclick="history.back()">
                            <i class="fas fa-times"></i> Hủy
                        </button>
                    </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: THÊM VARIANT CHO PRODUCT CÓ SẲN -->
    <div class="tab-pane fade" id="new-variant" role="tabpanel">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold mb-3">
                            <i class="fas fa-plus text-success"></i> Thêm variant mới cho sản phẩm có sẵn
                        </h5>
                        <p class="text-muted mb-4">
                            Sử dụng form này để thêm một variant mới (màu, kích cỡ, giá, số lượng, hình ảnh khác) cho một sản phẩm đã tồn tại.
                        </p>
                        
                        <form action="/web_qlsp/api/product_api/AddVariant" method="post" enctype="multipart/form-data">

                            <!-- CHỌN SẢN PHẨM HIỆN CÓ -->
                            <div class="card bg-light mb-4">
                                <div class="card-header bg-info text-white fw-bold">
                                    <i class="fas fa-search"></i> Chọn sản phẩm
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Sản phẩm <span class="text-danger">*</span></label>
                                        <select class="form-select" name="product_id" id="productSelect" required>
                                            <option value="" disabled selected>-- Chọn sản phẩm --</option>
                                            <?php
                                            // Lấy danh sách các sản phẩm có sẵn
                                            if (isset($data['products_list']) && mysqli_num_rows($data['products_list']) > 0) {
                                                while ($row = mysqli_fetch_assoc($data['products_list'])) {
                                            ?>
                                                    <option value="<?php echo $row['id']; ?>" data-name="<?php echo $row['name']; ?>">
                                                        [ID: <?php echo $row['id']; ?>] <?php echo $row['name']; ?>
                                                    </option>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                        <small class="form-text text-muted d-block mt-2">Chọn sản phẩm muốn thêm variant mới</small>
                                    </div>
                                    <div class="alert alert-info" role="alert">
                                        <strong><i class="fas fa-lightbulb"></i> Lưu ý:</strong> Sản phẩm được chọn sẽ không thay đổi, chỉ thêm một variant (phiên bản) mới với màu, kích cỡ, giá, hình ảnh khác.
                                    </div>
                                </div>
                            </div>

                            <!-- ẢNH CHI TIẾT CHO VARIANT MỚI -->
                            <div class="card bg-light mb-4">
                                <div class="card-header bg-primary text-white fw-bold">
                                    <i class="fas fa-images"></i> Ảnh chi tiết
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Ảnh chi tiết cho variant này</label>
                                        <input type="file" class="form-control" accept="image/*" name="detail_images[]" multiple>
                                        <small class="form-text text-muted d-block mt-2">
                                            <i class="fas fa-info-circle"></i> Nhấn Ctrl (Cmd trên Mac) + Click để chọn nhiều file
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- VARIANT (MÀU + KÍCH CỠ + GIÁ + SỐ LƯỢNG) -->
                            <div class="card bg-light mb-4">
                                <div class="card-header bg-success text-white fw-bold">
                                    <i class="fas fa-palette"></i> Thông tin variant
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Màu sắc <span class="text-danger">*</span></label>
                                            <select class="form-select" name="color" id="variantColor" required>
                                                <option value="" disabled selected>-- Chọn màu sắc --</option>
                                                <option value="Đen">Đen</option>
                                                <option value="Trắng">Trắng</option>
                                                <option value="Đỏ">Đỏ</option>
                                                <option value="Xanh">Xanh</option>
                                                <option value="Vàng">Vàng</option>
                                                <option value="Hồng">Hồng</option>
                                                <option value="Xám">Xám</option>
                                                <option value="Nâu">Nâu</option>
                                                <option value="Tím">Tím</option>
                                                <option value="Cam">Cam</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Kích cỡ <span class="text-danger">*</span></label>
                                            <select class="form-select" name="size" id="variantSize" required>
                                                <option value="" disabled selected>-- Chọn kích cỡ --</option>
                                                <option value="XS">XS</option>
                                                <option value="S">S</option>
                                                <option value="M">M</option>
                                                <option value="L">L</option>
                                                <option value="XL">XL</option>
                                                <option value="XXL">XXL</option>
                                                <option value="XXXL">XXXL</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Giá nhập (VNĐ) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control variant-cost" placeholder="100000" name="cost_price" step="0.01" required>
                                            <small class="form-text text-muted d-block mt-2">Giá vốn/giá nhập hàng</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Số lượng kho <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" placeholder="50" name="quantity" min="0" required>
                                            <small class="form-text text-muted d-block mt-2">Số lượng có sẵn cho variant mới</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success" name="btnLuu">
                                    <i class="fas fa-plus"></i> Thêm variant
                                </button>
                                <button type="button" class="btn btn-light border" onclick="history.back()">
                                    <i class="fas fa-times"></i> Hủy
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="/web_qlsp/Public/js/products.js"></script>
<script src="/web_qlsp/Public/js/result.js"></script>

<script>
    // Tính toán lợi nhuận tự động cho tab 1 (Tạo product mới)
    const costInput = document.querySelector('input[name="cost_price"]');
    const basePriceInput = document.querySelector('input[name="base_price"]');
    const profitDisplay = document.getElementById('profit');

    function calculateProfit() {
        const cost = parseFloat(costInput.value) || 0;
        const price = parseFloat(basePriceInput.value) || 0;
        const profit = price - cost;
        
        if (profit > 0) {
            profitDisplay.value = profit.toLocaleString('vi-VN') + ' VNĐ';
            profitDisplay.classList.remove('text-danger');
            profitDisplay.classList.add('text-success');
        } else if (profit < 0) {
            profitDisplay.value = profit.toLocaleString('vi-VN') + ' VNĐ';
            profitDisplay.classList.remove('text-success');
            profitDisplay.classList.add('text-danger');
        } else {
            profitDisplay.value = '0 VNĐ';
            profitDisplay.classList.remove('text-success', 'text-danger');
        }
    }

    costInput.addEventListener('input', calculateProfit);
    basePriceInput.addEventListener('input', calculateProfit);

    // Tính toán lợi nhuận tự động cho tab 2 (Thêm variant)
    const variantCostInputs = document.querySelectorAll('.variant-cost');
    const variantPriceInputs = document.querySelectorAll('.variant-price');
    const variantProfitDisplays = document.querySelectorAll('.variant-profit');

    function setupVariantProfitCalculation() {
        variantCostInputs.forEach((costInput, index) => {
            const priceInput = variantPriceInputs[index];
            const profitDisplay = variantProfitDisplays[index];

            function calculateVariantProfit() {
                const cost = parseFloat(costInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const profit = price - cost;
                
                if (profit > 0) {
                    profitDisplay.value = profit.toLocaleString('vi-VN') + ' VNĐ';
                    profitDisplay.classList.remove('text-danger');
                    profitDisplay.classList.add('text-success');
                } else if (profit < 0) {
                    profitDisplay.value = profit.toLocaleString('vi-VN') + ' VNĐ';
                    profitDisplay.classList.remove('text-success');
                    profitDisplay.classList.add('text-danger');
                } else {
                    profitDisplay.value = '0 VNĐ';
                    profitDisplay.classList.remove('text-success', 'text-danger');
                }
            }

            costInput.addEventListener('input', calculateVariantProfit);
            priceInput.addEventListener('input', calculateVariantProfit);
        });
    }

    setupVariantProfitCalculation();
</script>

<script>
    // Ẩn màu/kích cỡ đã tồn tại cho sản phẩm được chọn
    $(function() {
        const allSizes = ['XS','S','M','L','XL','XXL','XXXL'];
        let variantMap = {}; // color -> Set of sizes

        function resetColorAndSize() {
            $('#variantColor').val('');
            $('#variantSize').val('');
            // Show all options by default
            $('#variantColor option').prop('disabled', false).removeClass('d-none');
            $('#variantSize option').prop('disabled', false).removeClass('d-none');
        }

        function refreshColorOptions() {
            $('#variantColor option').each(function() {
                const color = $(this).val();
                if (!color) return; // skip placeholder
                const sizesUsed = variantMap[color] ? Array.from(variantMap[color]) : [];
                if (sizesUsed.length >= allSizes.length) {
                    // Color is fully used across all sizes → hide
                    $(this).prop('disabled', true).addClass('d-none');
                } else {
                    $(this).prop('disabled', false).removeClass('d-none');
                }
            });
        }

        function refreshSizeOptions(selectedColor) {
            // Show all sizes first
            $('#variantSize option').each(function() {
                const size = $(this).val();
                if (!size) return; // skip placeholder
                $(this).prop('disabled', false).removeClass('d-none');
            });

            if (!selectedColor) return;
            const used = variantMap[selectedColor] ? Array.from(variantMap[selectedColor]) : [];
            // Hide sizes already used for the selected color
            $('#variantSize option').each(function() {
                const sz = $(this).val();
                if (!sz) return;
                if (used.includes(sz)) {
                    $(this).prop('disabled', true).addClass('d-none');
                }
            });
        }

        function loadExistingVariants(productId) {
            if (!productId) return;
            $.ajax({
                url: '/web_qlsp/api/product_api/GetVariants',
                method: 'GET',
                dataType: 'json',
                data: { product_id: productId },
                success: function(resp) {
                    variantMap = {};
                    const list = (resp && resp.variants) ? resp.variants : [];
                    list.forEach(function(v) {
                        const color = v.color || '';
                        const size = v.size || '';
                        if (!color || !size) return;
                        if (!variantMap[color]) variantMap[color] = new Set();
                        variantMap[color].add(size);
                    });
                    resetColorAndSize();
                    refreshColorOptions();
                    // Size options will be filtered after color selection
                },
                error: function() {
                    // If error, just reset to defaults
                    variantMap = {};
                    resetColorAndSize();
                }
            });
        }

        // Events
        $('#productSelect').on('change', function() {
            const pid = $(this).val();
            loadExistingVariants(pid);
        });
        $('#variantColor').on('change', function() {
            const color = $(this).val();
            $('#variantSize').val('');
            refreshSizeOptions(color);
        });

        // If a product is preselected (unlikely), load variants
        const initialPid = $('#productSelect').val();
        if (initialPid) {
            loadExistingVariants(initialPid);
        }
    });
</script>

<script>
    // Nút AI: gọi ajax_ai.php để sinh mô tả sản phẩm
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('btnAiGemini');
        if (!btn) return;

        const nameInput = document.getElementById('name');
        const descTextarea = document.querySelector('textarea[name="description"]');
        const originalHtml = btn.innerHTML;

        function setLoading(isLoading) {
            if (isLoading) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Đang tạo mô tả...';
            } else {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }

        btn.addEventListener('click', function() {
            const productName = (nameInput && nameInput.value ? nameInput.value.trim() : '');
            if (!productName) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Thiếu tên sản phẩm',
                    text: 'Vui lòng nhập Tên sản phẩm trước khi dùng AI.'
                });
                return;
            }

            setLoading(true);
            $.ajax({
                url: '/web_qlsp/ajax_ai.php',
                method: 'POST',
                dataType: 'json',
                data: { product_name: productName },
                success: function(resp) {
                    if (resp && resp.success && resp.content) {
                        const cleaned = resp.content
                            .replace(/^```(?:html)?\s*/i, '')
                            .replace(/\s*```$/, '');
                        // Chuyển HTML sang văn bản thuần để tránh lỗi thẻ <p>, <li>
                        let plain = cleaned
                            .replace(/<\/?ul[^>]*>/gi, '\n')
                            .replace(/<li[^>]*>/gi, '- ')
                            .replace(/<\/li>\s*/gi, '\n')
                            .replace(/<p[^>]*>/gi, '')
                            .replace(/<\/p>\s*/gi, '\n\n')
                            .replace(/<br\s*\/?\s*>/gi, '\n')
                            .replace(/<[^>]+>/g, '')
                            .replace(/[\t ]+/g, ' ')
                            .replace(/\n{3,}/g, '\n\n')
                            .trim();
                        if (descTextarea) {
                            descTextarea.value = plain;
                        }
                        if (resp.fallback) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Đã dùng mô tả mẫu',
                                text: 'Đã chèn mô tả mẫu.',
                                timer: 2500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Đã tạo mô tả (văn bản)',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    } else {
                        const msg = (resp && resp.message) ? resp.message : 'AI chưa trả về nội dung.';
                        Swal.fire({ icon: 'error', title: 'Không tạo được mô tả', text: msg });
                    }
                },
                error: function(xhr) {
                    const msg = (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Không thể kết nối máy chủ AI.';
                    Swal.fire({ icon: 'error', title: 'Lỗi kết nối', text: msg });
                },
                complete: function() {
                    setLoading(false);
                }
            });
        });
    });
    </script>

<?php if (isset($_SESSION['status_msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            checkFlashMessage("<?php echo $_SESSION['status_msg']; ?>");
        });
    </script>
    <?php unset($_SESSION['status_msg']); ?>
<?php endif; ?>