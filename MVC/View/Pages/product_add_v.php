<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Quản lý sản phẩm</h4>
</div>

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
    <div class="tab-pane fade show active" id="new-product" role="tabpanel">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold mb-3">
                            <i class="fas fa-info-circle text-primary"></i> Tạo sản phẩm mới + variant đầu tiên
                        </h5>
                        <form id="formAddProduct">

                            <div class="card bg-light mb-4">
                                <div class="card-header bg-primary text-white fw-bold"><i class="fas fa-info-circle"></i> Thông tin cơ bản</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" onkeyup="generateSlug(this.value)" placeholder="VD: Áo Thun Coolmate..." required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Slug (Đường dẫn tĩnh) <span class="text-danger">*</span></label>
                                        <input type="text" id="slug" class="form-control bg-light" readonly name="slug">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                                            <select class="form-select" name="category_id" id="catSelect" required>
                                                <option value="" disabled selected>-- Đang tải... --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Bộ sưu tập <span class="text-danger">*</span></label>
                                            <select class="form-select" name="collection_id" id="colSelect" required>
                                                <option value="" disabled selected>-- Đang tải... --</option>
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
                                                <label class="form-check-label fw-bold" for="is_sale">Là sản phẩm khuyến mãi</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" placeholder="150000" name="base_price" step="0.01" required>
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-light mb-4">
                                <div class="card-header bg-primary text-white fw-bold"><i class="fas fa-images"></i> Ảnh sản phẩm</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Ảnh đại diện <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" accept="image/*" name="thumbnail" required>
                                    </div>
                                    <hr>
                                    <div class="alert alert-info"><strong><i class="fas fa-lightbulb"></i> Lưu ý:</strong> Các ảnh chi tiết dưới đây sẽ được liên kết với variant.</div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Ảnh chi tiết cho variant này</label>
                                        <input type="file" class="form-control" accept="image/*" name="detail_images[]" multiple>
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-light mb-4">
                                <div class="card-header bg-success text-white fw-bold"><i class="fas fa-palette"></i> Variant sản phẩm (Phiên bản)</div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Màu sắc <span class="text-danger">*</span></label>
                                            <select class="form-select" name="color" required>
                                                <option value="" disabled selected>-- Chọn màu --</option>
                                                <option value="Đen">Đen</option><option value="Trắng">Trắng</option><option value="Đỏ">Đỏ</option>
                                                <option value="Xanh">Xanh</option><option value="Vàng">Vàng</option><option value="Hồng">Hồng</option>
                                                <option value="Xám">Xám</option><option value="Nâu">Nâu</option><option value="Tím">Tím</option><option value="Cam">Cam</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Kích cỡ <span class="text-danger">*</span></label>
                                            <select class="form-select" name="size" required>
                                                <option value="" disabled selected>-- Chọn size --</option>
                                                <option value="XS">XS</option><option value="S">S</option><option value="M">M</option>
                                                <option value="L">L</option><option value="XL">XL</option><option value="XXL">XXL</option><option value="XXXL">XXXL</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Giá nhập (VNĐ) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="cost_price" step="0.01" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Số lượng kho <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="quantity" min="0" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-light mb-4">
                                <div class="card-header bg-primary text-white fw-bold"><i class="fas fa-align-left"></i> Mô tả chi tiết</div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-bold mb-0">Nội dung mô tả</label>
                                        <button type="button" id="btnAiGemini" class="btn btn-sm btn-outline-primary"><i class="fas fa-wand-magic-sparkles"></i> Dùng AI Viết Hộ</button>
                                    </div>
                                    <textarea class="form-control" rows="6" name="description"></textarea>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu sản phẩm</button>
                                <button type="button" class="btn btn-light border" onclick="history.back()"><i class="fas fa-times"></i> Hủy</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="new-variant" role="tabpanel">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold mb-3"><i class="fas fa-plus text-success"></i> Thêm variant mới cho sản phẩm có sẵn</h5>
                        
                        <form id="formAddVariant">
                            <div class="card bg-light mb-4">
                                <div class="card-header bg-info text-white fw-bold"><i class="fas fa-search"></i> Chọn sản phẩm</div>
                                <div class="card-body">
                                    <select class="form-select" name="product_id" id="productSelect" required>
                                        <option value="" disabled selected>-- Đang tải... --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="card bg-light mb-4">
                                <div class="card-header bg-primary text-white fw-bold"><i class="fas fa-images"></i> Ảnh chi tiết</div>
                                <div class="card-body">
                                    <input type="file" class="form-control" accept="image/*" name="detail_images[]" multiple>
                                </div>
                            </div>

                            <div class="card bg-light mb-4">
                                <div class="card-header bg-success text-white fw-bold"><i class="fas fa-palette"></i> Thông tin variant</div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Màu sắc <span class="text-danger">*</span></label>
                                            <select class="form-select" name="color" id="variantColor" required>
                                                <option value="" disabled selected>-- Chọn màu sắc --</option>
                                                <option value="Đen">Đen</option><option value="Trắng">Trắng</option><option value="Đỏ">Đỏ</option>
                                                <option value="Xanh">Xanh</option><option value="Vàng">Vàng</option><option value="Hồng">Hồng</option>
                                                <option value="Xám">Xám</option><option value="Nâu">Nâu</option><option value="Tím">Tím</option><option value="Cam">Cam</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Kích cỡ <span class="text-danger">*</span></label>
                                            <select class="form-select" name="size" id="variantSize" required>
                                                <option value="" disabled selected>-- Chọn kích cỡ --</option>
                                                <option value="XS">XS</option><option value="S">S</option><option value="M">M</option>
                                                <option value="L">L</option><option value="XL">XL</option><option value="XXL">XXL</option><option value="XXXL">XXXL</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Giá nhập (VNĐ) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="cost_price" step="0.01" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Số lượng kho <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="quantity" min="0" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Thêm variant</button>
                                <button type="button" class="btn btn-light border" onclick="history.back()"><i class="fas fa-times"></i> Hủy</button>
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

<script>
    const API_BASE = '/web_qlsp/product_add';

    // 1. Tải danh mục, bộ sưu tập, sản phẩm để điền vào Select
    document.addEventListener("DOMContentLoaded", function() {
        fetch(`${API_BASE}/api_get_form_data`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let catHtml = '<option value="" disabled selected>-- Chọn danh mục --</option>';
                    data.categories.forEach(c => catHtml += `<option value="${c.id}">${c.name}</option>`);
                    document.getElementById('catSelect').innerHTML = catHtml;

                    let colHtml = '<option value="" disabled selected>-- Chọn bộ sưu tập --</option>';
                    data.collections.forEach(c => colHtml += `<option value="${c.id}">${c.name}</option>`);
                    document.getElementById('colSelect').innerHTML = colHtml;

                    let prodHtml = '<option value="" disabled selected>-- Chọn sản phẩm --</option>';
                    data.products.forEach(p => prodHtml += `<option value="${p.id}">[ID: ${p.id}] ${p.name}</option>`);
                    document.getElementById('productSelect').innerHTML = prodHtml;
                }
            });
    });

    // Hàm hỏi sau khi thành công
    function askAfterSuccess(msg) {
        Swal.fire({
            title: 'Thành công!', text: msg, icon: 'success',
            showCancelButton: true, confirmButtonText: 'Về danh sách SP', cancelButtonText: 'Thêm tiếp'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '/web_qlsp/product_list';
            } else {
                // Tải lại dữ liệu dropdowns để cập nhật SP mới (nếu có)
                location.reload(); 
            }
        });
    }

    // 2. Xử lý Thêm Sản Phẩm Mới (Tab 1)
    document.getElementById('formAddProduct').addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({ title: 'Đang lưu dữ liệu...', text: 'Vui lòng chờ (Có thể mất chút thời gian tải ảnh)', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch(`${API_BASE}/api_add`, { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    askAfterSuccess(data.message);
                } else {
                    Swal.fire('Lỗi', data.message, 'error');
                }
            }).catch(() => Swal.fire('Lỗi', 'Không thể kết nối máy chủ', 'error'));
    });

    // 3. Xử lý Thêm Variant (Tab 2)
    document.getElementById('formAddVariant').addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({ title: 'Đang lưu Variant...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch(`${API_BASE}/api_add_variant`, { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    askAfterSuccess(data.message);
                } else {
                    Swal.fire('Lỗi', data.message, 'error');
                }
            }).catch(() => Swal.fire('Lỗi', 'Không thể kết nối máy chủ', 'error'));
    });

    // 4. Sinh Slug
    function generateSlug(text) {
        let slug = text.toLowerCase();
        slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
        slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
        slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
        slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
        slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
        slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
        slug = slug.replace(/đ/gi, 'd');
        slug = slug.replace(/[^a-z0-9 -]/g, '');
        slug = slug.replace(/\s+/g, '-');
        slug = slug.replace(/-+/g, '-');
        document.getElementById('slug').value = slug;
    }

    // 5. Logic ẩn Màu/Size đã tồn tại (Giữ nguyên của bạn)
    $(function() {
        const allSizes = ['XS','S','M','L','XL','XXL','XXXL'];
        let variantMap = {};

        function refreshColorOptions() {
            $('#variantColor option').each(function() {
                const color = $(this).val();
                if (!color) return; 
                const sizesUsed = variantMap[color] ? Array.from(variantMap[color]) : [];
                if (sizesUsed.length >= allSizes.length) {
                    $(this).prop('disabled', true).addClass('d-none');
                } else {
                    $(this).prop('disabled', false).removeClass('d-none');
                }
            });
        }

        function refreshSizeOptions(selectedColor) {
            $('#variantSize option').prop('disabled', false).removeClass('d-none');
            if (!selectedColor) return;
            const used = variantMap[selectedColor] ? Array.from(variantMap[selectedColor]) : [];
            $('#variantSize option').each(function() {
                if (used.includes($(this).val())) {
                    $(this).prop('disabled', true).addClass('d-none');
                }
            });
        }

        $('#productSelect').on('change', function() {
            const pid = $(this).val();
            fetch(`${API_BASE}/api_get_variants?product_id=${pid}`)
                .then(r => r.json())
                .then(data => {
                    variantMap = {};
                    if(data.success && data.variants) {
                        data.variants.forEach(v => {
                            if (!variantMap[v.color]) variantMap[v.color] = new Set();
                            variantMap[v.color].add(v.size);
                        });
                    }
                    $('#variantColor').val(''); $('#variantSize').val('');
                    refreshColorOptions();
                });
        });

        $('#variantColor').on('change', function() {
            $('#variantSize').val('');
            refreshSizeOptions($(this).val());
        });
    });

    // 6. Gemini AI
    document.getElementById('btnAiGemini').addEventListener('click', function() {
        const nameInput = document.getElementById('name').value.trim();
        if (!nameInput) return Swal.fire('Thiếu tên SP', 'Vui lòng nhập Tên sản phẩm.', 'warning');
        
        const btn = this;
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang tạo...';
        btn.disabled = true;

        $.post('/web_qlsp/ajax_ai.php', { product_name: nameInput }, function(resp) {
            btn.innerHTML = oldHtml; btn.disabled = false;
            if(resp && resp.success) {
                let plain = resp.content.replace(/<[^>]+>/g, '').trim();
                document.querySelector('textarea[name="description"]').value = plain;
                Swal.fire({ icon: 'success', title: 'Tạo thành công', timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire('Lỗi AI', 'Không thể tạo mô tả', 'error');
            }
        }, 'json').fail(function() {
            btn.innerHTML = oldHtml; btn.disabled = false;
            Swal.fire('Lỗi mạng', 'Không thể kết nối AI', 'error');
        });
    });
</script>