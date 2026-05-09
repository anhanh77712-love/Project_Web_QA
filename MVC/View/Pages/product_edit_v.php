<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Sửa sản phẩm <span class="text-primary">#<span id="display_product_id"><?= $data['product_id'] ?></span></span></h4>
    <button class="btn btn-outline-secondary" onclick="window.location.href='/web_qlsp/product_list'">
        <i class="fas fa-arrow-left"></i> Quay lại danh sách
    </button>
</div>

<input type="hidden" id="product_id" value="<?= $data['product_id'] ?>">

<div class="row justify-content-center">
    <div class="col-md-12">
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="fas fa-info-circle"></i> Thông tin cơ bản sản phẩm
            </div>
            <div class="card-body p-4">
                
                <div id="product-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Đang tải dữ liệu sản phẩm...</p>
                </div>

                <form id="formEditProduct" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" id="name" class="form-control" name="name" onkeyup="generateSlug(this.value)" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Slug (Đường dẫn tĩnh) <span class="text-danger">*</span></label>
                        <input type="text" id="slug" class="form-control bg-light" name="slug" readonly required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" id="category_id" required>
                                <option value="">-- Đang tải --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Bộ sưu tập <span class="text-danger">*</span></label>
                            <select class="form-select" name="collection_id" id="collection_id" required>
                                <option value="">-- Đang tải --</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Giới tính <span class="text-danger">*</span></label>
                            <select class="form-select" name="gender" id="gender" required>
                                <option value="nam">Nam</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Trạng thái <span class="text-danger">*</span></label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="is_sale" name="is_sale" value="1">
                                <label class="form-check-label fw-bold text-danger" for="is_sale">Là sản phẩm khuyến mãi (Sale)</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Giá bán cơ bản (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control fw-bold text-primary" id="base_price" name="base_price" step="0.01" required>
                    </div>

                    <div class="card bg-light mb-3 border-0">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold"><i class="fas fa-image text-primary"></i> Ảnh đại diện mới (Tùy chọn)</label>
                                    <input type="file" class="form-control" accept="image/*" name="thumbnail">
                                    <small class="form-text text-muted">Để trống nếu không muốn thay đổi</small>
                                </div>
                                <div class="col-md-4 text-center">
                                    <p class="form-label fw-bold mb-1">Ảnh hiện tại</p>
                                    <img id="current_thumbnail" src="" width="90" height="90" style="object-fit: cover; border-radius: 8px; border: 2px solid #ddd;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Mô tả chi tiết</label>
                        <textarea class="form-control" rows="6" id="description" name="description"></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary fw-bold px-4"><i class="fas fa-save"></i> Lưu Sản Phẩm</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-palette"></i> Danh sách Biến thể (Variants)</span>
                <button class="btn btn-sm btn-light text-success fw-bold" onclick="window.location.href='/web_qlsp/product_add'">
                    <i class="fas fa-plus"></i> Thêm mới
                </button>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="8%">ID</th>
                                <th width="12%">Màu sắc</th>
                                <th width="10%">Kích cỡ</th>
                                <th width="15%">Giá nhập</th>
                                <th width="12%">Kho</th>
                                <th width="25%">Ảnh chi tiết</th>
                                <th width="18%" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="variants-table-body">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="editVariantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            
            <div class="modal-header bg-warning py-3">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit"></i> Sửa chi tiết Biến Thể</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4 bg-light">
                
                <form id="formEditVariant" class="card p-3 border-0 shadow-sm mb-3">
                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">1. Thông tin chung</h6>
                    <input type="hidden" id="v_variant_id" name="variant_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Màu sắc</label>
                            <select id="v_color" name="color" class="form-select" required>
                                <option value="">-- Chọn màu --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Kích cỡ</label>
                            <select id="v_size" name="size" class="form-select" required>
                                <option value="">-- Chọn kích cỡ --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Giá nhập</label>
                            <input type="number" id="v_input_price" name="input_price" class="form-control text-danger fw-bold" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Tồn kho</label>
                            <input type="number" id="v_stock" name="stock" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning fw-bold w-100"><i class="fas fa-save"></i> Cập nhật thông tin</button>
                </form>

                <div class="card p-3 border-0 shadow-sm">
                    <h6 class="fw-bold text-success mb-3 border-bottom pb-2">2. Quản lý Ảnh chi tiết</h6>
                    
                    <div class="d-flex flex-wrap gap-2 mb-4" id="v_images_container">
                        </div>

                    <form id="formAddVariantImages" class="bg-white p-3 rounded border border-dashed">
                        <input type="hidden" id="v_upload_variant_id" name="variant_id">
                        <label class="fw-bold text-muted mb-2"><i class="fas fa-upload"></i> Tải thêm ảnh mới</label>
                        <div class="input-group">
                            <input type="file" name="detail_images[]" class="form-control" accept="image/*" multiple required>
                            <button type="submit" class="btn btn-success"><i class="fas fa-cloud-upload-alt"></i> Tải lên</button>
                        </div>
                        <small class="text-muted mt-1 d-block">Có thể chọn nhiều ảnh cùng lúc (Bấm Ctrl hoặc Cmd để chọn).</small>
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
    const PRODUCT_ID = document.getElementById('product_id').value;
    let globalVariants = []; // Lưu lại để dùng cho Modal

    // 1. Hàm tiện ích giao diện
    const formatMoney = (amount) => new Intl.NumberFormat('vi-VN').format(amount || 0) + 'đ';
    const getColorHex = (color) => {
        const colors = {'Đen':'#000000','Trắng':'#FFFFFF','Đỏ':'#DC3545','Xanh':'#0D6EFD','Vàng':'#FFC107','Hồng':'#E83E8C','Xám':'#6C757D','Nâu':'#8B4513','Tím':'#6F42C1','Cam':'#FD7E14'};
        return colors[color] || '#6C757D';
    };
    const getTextColor = (color) => ['trắng','trang','white'].includes(color.toLowerCase()) ? '#000' : '#FFF';

    const defaultVariantColorOptions = ['Đen','Trắng','Đỏ','Xanh','Vàng','Hồng','Xám','Nâu','Tím','Cam'];
    const defaultVariantSizeOptions = ['XS','S','M','L','XL','XXL','XXXL'];
    let variantMap = {};

    const buildVariantMap = (excludeVariantId = null) => {
        variantMap = {};
        globalVariants.forEach(v => {
            if (excludeVariantId && v.id == excludeVariantId) return;
            if (!variantMap[v.color]) variantMap[v.color] = new Set();
            variantMap[v.color].add(v.size);
        });
    };

    const refreshColorOptions = (currentColor = '') => {
        const colorSelect = document.getElementById('v_color');
        Array.from(colorSelect.options).forEach(option => {
            const color = option.value;
            if (!color) return;
            const sizesUsed = variantMap[color] ? Array.from(variantMap[color]) : [];
            if (sizesUsed.length >= defaultVariantSizeOptions.length && color !== currentColor) {
                option.disabled = true;
                option.classList.add('d-none');
            } else {
                option.disabled = false;
                option.classList.remove('d-none');
            }
        });
    };

    const refreshSizeOptions = (selectedColor = '') => {
        const sizeSelect = document.getElementById('v_size');
        Array.from(sizeSelect.options).forEach(option => {
            option.disabled = false;
            option.classList.remove('d-none');
        });
        if (!selectedColor) return;
        const used = variantMap[selectedColor] ? Array.from(variantMap[selectedColor]) : [];
        Array.from(sizeSelect.options).forEach(option => {
            if (used.includes(option.value)) {
                option.disabled = true;
                option.classList.add('d-none');
            }
        });
    };

    const getVariantOptions = (currentVariantId, selectedColor, selectedSize) => {
        const colorSet = new Set(defaultVariantColorOptions);
        const sizeSet = new Set(defaultVariantSizeOptions);

        globalVariants.forEach(v => {
            if (v.color && v.color.trim() !== '') {
                colorSet.add(v.color.trim());
            }
            if (v.size && v.size.trim() !== '') {
                sizeSet.add(v.size.trim());
            }
        });

        const colors = Array.from(colorSet).sort((a, b) => a.localeCompare(b, 'vi'));
        const sizes = Array.from(sizeSet).sort((a, b) => a.localeCompare(b, 'vi'));

        return {
            colors,
            sizes
        };
    };

    const populateVariantSelects = (selectedColor, selectedSize, currentVariantId = null) => {
        const colorSelect = document.getElementById('v_color');
        const sizeSelect = document.getElementById('v_size');
        const { colors, sizes } = getVariantOptions(currentVariantId, selectedColor, selectedSize);

        colorSelect.innerHTML = '<option value="">-- Chọn màu --</option>';
        colors.forEach(color => {
            colorSelect.insertAdjacentHTML('beforeend', `<option value="${color}">${color}</option>`);
        });
        if (selectedColor && !colors.includes(selectedColor)) {
            colorSelect.insertAdjacentHTML('afterbegin', `<option value="${selectedColor}">${selectedColor}</option>`);
        }

        sizeSelect.innerHTML = '<option value="">-- Chọn kích cỡ --</option>';
        sizes.forEach(size => {
            sizeSelect.insertAdjacentHTML('beforeend', `<option value="${size}">${size}</option>`);
        });
        if (selectedSize && !sizes.includes(selectedSize)) {
            sizeSelect.insertAdjacentHTML('afterbegin', `<option value="${selectedSize}">${selectedSize}</option>`);
        }
    };

    function generateSlug(title) {
        let slug = title.toLowerCase();
        slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a').replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e').replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i').replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o').replace(/ú|ù|ủ|ũ|ư|ứ|ừ|ử|ữ|ự/gi, 'u').replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y').replace(/đ/gi, 'd');
        slug = slug.replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
        document.getElementById('slug').value = slug;
    }

    // 2. TẢI DỮ LIỆU SẢN PHẨM & BIẾN THỂ
    function loadProductData() {
        fetch(`${API_BASE}/api_get_product_detail/${PRODUCT_ID}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const p = data.product;
                    globalVariants = data.variants;

                    // Đổ danh sách Category
                    let catHtml = '<option value="">-- Chọn --</option>';
                    data.categories.forEach(c => catHtml += `<option value="${c.id}" ${c.id == p.category_id ? 'selected' : ''}>${c.name}</option>`);
                    document.getElementById('category_id').innerHTML = catHtml;

                    // Đổ danh sách Collection
                    let colHtml = '<option value="">-- Chọn --</option>';
                    data.collections.forEach(c => colHtml += `<option value="${c.id}" ${c.id == p.collection_id ? 'selected' : ''}>${c.name}</option>`);
                    document.getElementById('collection_id').innerHTML = colHtml;

                    // Đổ Thông tin cơ bản
                    document.getElementById('name').value = p.name;
                    document.getElementById('slug').value = p.slug;
                    document.getElementById('gender').value = p.gender || 'nam';
                    document.getElementById('is_sale').checked = (p.is_sale == 1);
                    document.getElementById('base_price').value = p.base_price;
                    document.getElementById('description').value = p.description;
                    document.getElementById('current_thumbnail').src = `/web_qlsp/Public/Picture/${p.thumbnail}`;

                    // Vẽ bảng Variant
                    renderVariantsTable();

                    // Hiện form, ẩn loading
                    document.getElementById('product-loading').style.display = 'none';
                    document.getElementById('formEditProduct').style.display = 'block';
                } else {
                    Swal.fire('Lỗi', data.message, 'error').then(() => history.back());
                }
            })
            .catch(err => {
                Swal.fire('Lỗi', 'Không thể kết nối server', 'error').then(() => history.back());
            });
    }

    // Vẽ bảng Variant
    function renderVariantsTable() {
        const tbody = document.getElementById('variants-table-body');
        let html = '';

        if (globalVariants.length > 0) {
            globalVariants.forEach(v => {
                const stockBadge = v.stock > 50 ? 'bg-success' : (v.stock > 0 ? 'bg-warning' : 'bg-danger');
                
                // Trình bày ảnh
                let imagesHtml = '<div class="d-flex gap-1 flex-wrap">';
                if (v.images && v.images.length > 0) {
                    const displayCount = Math.min(4, v.images.length);
                    for (let i=0; i<displayCount; i++) {
                        imagesHtml += `<img src="/web_qlsp/Public/Picture/${v.images[i].image_url}" width="40" height="40" style="object-fit:cover; border-radius:4px; border:1px solid #ddd;">`;
                    }
                    if (v.images.length > 4) imagesHtml += `<span class="badge bg-light text-dark border align-self-center">+${v.images.length - 4}</span>`;
                } else {
                    imagesHtml += `<span class="text-muted small">Không có ảnh</span>`;
                }
                imagesHtml += '</div>';

                html += `
                    <tr>
                        <td class="fw-bold text-muted">#${v.id}</td>
                        <td><span class="badge" style="background:${getColorHex(v.color)}; color:${getTextColor(v.color)}; border:1px solid #ccc;">${v.color}</span></td>
                        <td><span class="badge bg-secondary">${v.size}</span></td>
                        <td class="text-danger fw-bold">${formatMoney(v.input_price)}</td>
                        <td><span class="badge ${stockBadge}"><i class="fas fa-box"></i> ${v.stock}</span></td>
                        <td>${imagesHtml}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary me-1" onclick="openVariantModal(${v.id})"><i class="fas fa-edit"></i> Sửa</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteVariant(${v.id})"><i class="fas fa-trash"></i> Xóa</button>
                        </td>
                    </tr>
                `;
            });
        } else {
            html = `<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle mb-2 d-block fs-3"></i> Sản phẩm này chưa có biến thể nào.</td></tr>`;
        }
        tbody.innerHTML = html;
    }

    // Chạy khi web tải xong
    document.addEventListener("DOMContentLoaded", loadProductData);


    // ==========================================
    // 3. CẬP NHẬT THÔNG TIN SẢN PHẨM (Form Chính)
    // ==========================================
    document.getElementById('formEditProduct').addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({ title: 'Đang lưu...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        let formData = new FormData(this);
        formData.append('id', PRODUCT_ID);

        fetch(`${API_BASE}/api_update_product`, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Đã lưu thành công!', 
                        text: 'Thông tin sản phẩm đã được cập nhật.', 
                        showCancelButton: true,
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Về danh sách SP',
                        cancelButtonText: 'Ở lại trang này'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '/web_qlsp/product_list';
                        } else {
                            loadProductData(); 
                        }
                    });
                } else {
                    Swal.fire('Lỗi', data.message, 'error');
                }
            })
            .catch(() => Swal.fire('Lỗi', 'Lỗi kết nối máy chủ', 'error'));
    });


    // ==========================================
    // 4. QUẢN LÝ BIẾN THỂ (MODAL)
    // ==========================================
    
    // Mở Modal và đổ dữ liệu
    function openVariantModal(variantId) {
        const v = globalVariants.find(item => item.id == variantId);
        if(!v) return;

        document.getElementById('v_variant_id').value = v.id;
        document.getElementById('v_upload_variant_id').value = v.id;
        buildVariantMap(v.id);
        populateVariantSelects(v.color, v.size, v.id);
        refreshColorOptions(v.color);
        refreshSizeOptions(v.color);
        document.getElementById('v_color').value = v.color;
        document.getElementById('v_size').value = v.size;
        document.getElementById('v_input_price').value = v.input_price;
        document.getElementById('v_stock').value = v.stock;

        renderVariantImages(v.images);

        new bootstrap.Modal(document.getElementById('editVariantModal')).show();
    }

    // Event listeners cho select màu và kích thước
    document.getElementById('v_color').addEventListener('change', function() {
        const selectedColor = this.value;
        const currentVariantId = document.getElementById('v_variant_id').value;
        buildVariantMap(currentVariantId);
        populateVariantSelects(selectedColor, '', currentVariantId);
        refreshColorOptions(selectedColor);
        refreshSizeOptions(selectedColor);
        document.getElementById('v_color').value = selectedColor;
        document.getElementById('v_size').value = '';
    });

    // Vẽ hình ảnh trong Modal
    function renderVariantImages(images) {
        const container = document.getElementById('v_images_container');
        let html = '';
        if(images && images.length > 0) {
            images.forEach(img => {
                html += `
                <div class="position-relative d-inline-block">
                    <img src="/web_qlsp/Public/Picture/${img.image_url}" width="80" height="80" style="object-fit:cover; border-radius:6px; border:2px solid #ddd;">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 start-100 translate-middle rounded-circle p-1" style="width:24px; height:24px; line-height:10px;" onclick="deleteVariantImage(${img.id})">
                        <i class="fas fa-times" style="font-size:12px;"></i>
                    </button>
                </div>`;
            });
        } else {
            html = `<p class="text-muted w-100 mb-0"><i class="fas fa-images"></i> Chưa có ảnh nào.</p>`;
        }
        container.innerHTML = html;
    }

    // Cập nhật thông tin chữ (Màu, Size, Giá...)
    document.getElementById('formEditVariant').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(`${API_BASE}/api_update_variant`, { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({ icon: 'success', title: 'Đã cập nhật', timer: 1000, showConfirmButton: false });
                    loadProductData(); 
                } else {
                    Swal.fire('Lỗi', data.message, 'error');
                }
            });
    });

    // Upload thêm ảnh cho Variant
    document.getElementById('formAddVariantImages').addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({ title: 'Đang tải ảnh...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        fetch(`${API_BASE}/api_upload_variant_images`, { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    this.reset();
                    Swal.fire({ icon: 'success', title: 'Hoàn tất', timer: 1000, showConfirmButton: false });
                    loadProductData(); // Load nền
                    setTimeout(() => openVariantModal(document.getElementById('v_variant_id').value), 600); // Reload modal data
                } else {
                    Swal.fire('Lỗi', data.message, 'error');
                }
            });
    });

    // Xóa 1 ảnh
    function deleteVariantImage(imageId) {
        Swal.fire({
            title: 'Xóa ảnh này?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Xóa', cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${API_BASE}/api_delete_variant_image/${imageId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            loadProductData();
                            setTimeout(() => openVariantModal(document.getElementById('v_variant_id').value), 600);
                        } else {
                            Swal.fire('Lỗi', data.message, 'error');
                        }
                    });
            }
        });
    }

    // Xóa toàn bộ Variant
    function deleteVariant(variantId) {
        Swal.fire({
            title: 'Xóa biến thể này?', text: "Hành động này sẽ xóa toàn bộ số lượng và ảnh của nó!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Xóa ngay', cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${API_BASE}/api_delete_variant/${variantId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Đã xóa', timer: 1000, showConfirmButton: false });
                            loadProductData();
                        } else {
                            Swal.fire('Lỗi', data.message, 'error');
                        }
                    });
            }
        });
    }
</script>