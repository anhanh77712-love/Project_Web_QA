<div class="container-fluid px-4">
    <h1 class="mt-4 fw-bold">Quản Lý Giao Diện Trang Chủ</h1>

    <div class="row">

        <!-- FORM CONFIG -->
        <div class="col-md-5">
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-dark text-white fw-bold">
                    Thêm Section Mới
                </div>
                <div class="card-body bg-light">
                    <form method="POST" action="/web_qlsp/campaigns/add" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="fw-bold">Loại hiển thị (*)</label>
                            <select class="form-select border-primary" id="sType" name="type" onchange="toggleForm()">
                                <option value="category_grid">1. Danh sách Danh mục</option>
                                <option value="overlay_banner">2. Banner Chữ Đè Ảnh</option>
                                <option value="collection">3. Danh sách Sản phẩm</option>
                                <option value="flash_sale">4. Flash Sale</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold">Tiêu đề Section (*)</label>
                            <input type="text" class="form-control" name="title" placeholder="VD: Hàng Mới Về" required>
                        </div>

                        <!-- BLOCK BANNER -->
                        <div id="block_banner" class="mb-3 p-3 border rounded bg-white shadow-sm" style="display:none;">
                            <h6 class="text-primary fw-bold border-bottom pb-2">Cấu hình Banner</h6>
                            <label>Ảnh nền (*)</label>
                            <input type="file" class="form-control mb-2" name="banner_image" accept="image/*">

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label>Chữ trên nút</label>
                                    <input type="text" class="form-control" name="button_text" value="XEM NGAY">
                                </div>
                                <div class="col-6">
                                    <label>Vị trí chữ</label>
                                    <select class="form-select" name="text_position">
                                        <option value="left">Trái</option>
                                        <option value="center">Giữa</option>
                                        <option value="right">Phải</option>
                                    </select>
                                </div>
                            </div>

                            <label>Link khi bấm vào</label>
                            <input type="text" class="form-control" name="link_url" placeholder="#">
</div>

                        <!-- BLOCK COLLECTION -->
                        <div id="block_collection" class="mb-3 p-3 border rounded bg-white shadow-sm" style="display:none;">
                            <h6 class="text-success fw-bold border-bottom pb-2">Chọn Nguồn Sản Phẩm</h6>
                            <label>Lấy từ Bộ Sưu Tập nào?</label>
                            <select class="form-select" name="collection_id">
                                <option value="">-- Chọn Collection --</option>
                                <?php
                                if(isset($data['collections_list']) && mysqli_num_rows($data['collections_list']) > 0) {
                                    mysqli_data_seek($data['collections_list'], 0);
                                    while($col = mysqli_fetch_assoc($data['collections_list'])) {
                                        echo '<option value="' . $col['id'] . '">' . htmlspecialchars($col['name']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- BLOCK FLASH -->
                        <div id="block_flash" class="mb-3 p-3 border rounded bg-white shadow-sm" style="display:none;">
                            <h6 class="text-danger fw-bold border-bottom pb-2">Thời gian Flash Sale</h6>
                            <label>Kết thúc lúc:</label>
                            <input type="datetime-local" class="form-control" name="end_time">
                        </div>

                        <!-- STYLE -->
                        <div class="p-3 border rounded bg-white shadow-sm mb-3">
                            <h6 class="fw-bold border-bottom pb-2">Giao diện & Thứ tự</h6>
                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="fw-bold small">Màu nền</label>
                                    <input type="color" class="form-control form-control-color w-100" name="bg_color" value="#ffffff">
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold small">Màu chữ tiêu đề</label>
                                    <input type="color" class="form-control form-control-color w-100" name="text_color" value="#000000">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="fw-bold small">Thứ tự hiển thị</label>
                                <input type="number" class="form-control" name="display_order" value="0">
                            </div>
                            <div class="form-check">
<input class="form-check-input" type="checkbox" name="is_active" checked>
                                <label class="form-check-label">Hiển thị ngay</label>
                            </div>
                        </div>

                        <button type="submit" name="add_campaign" class="btn btn-primary w-100 fw-bold">
                            <i class="fas fa-save me-1"></i> LƯU CẤU HÌNH
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- LIST -->
        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    Danh sách các mục trên trang chủ
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>TT</th>
                                <th>Tiêu đề</th>
                                <th>Loại</th>
                                <th>Màu sắc</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($data['campaigns_list']) && mysqli_num_rows($data['campaigns_list']) > 0) {
                                $stt = 1;
                                foreach ($data['campaigns_list'] as $c) {
                                    // Xác định loại hiển thị
                                    $type_label = '';
                                    $badge_class = 'bg-info';
                                    switch($c['section_type']) {
                                        case 'category_grid':
                                            $type_label = 'Danh mục';
                                            $badge_class = 'bg-primary';
                                            break;
                                        case 'overlay_banner':
                                            $type_label = 'Banner';
                                            $badge_class = 'bg-warning text-dark';
                                            break;
                                        case 'collection':
                                            $type_label = 'DS Sản phẩm';
                                            $badge_class = 'bg-info text-dark';
                                            break;
                                        case 'flash_sale':
                                            $type_label = 'Flash Sale';
                                            $badge_class = 'bg-danger';
                                            break;
                                    }
                            ?>
<tr style="border-left: 5px solid <?php echo $c['bg_color']; ?>">
                                <td class="text-center fw-bold"><?php echo $stt++; ?></td>
                                <td class="fw-bold" style="color:<?php echo $c['text_color']; ?>">
                                    <?php echo htmlspecialchars($c['title']); ?>
                                </td>
                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo $type_label; ?></span></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <div style="width:20px;height:20px;background:<?php echo $c['bg_color']; ?>;border:1px solid #ddd;" 
                                             title="Màu nền"></div>
                                        <div style="width:20px;height:20px;background:<?php echo $c['text_color']; ?>;border:1px solid #ddd;" 
                                             title="Màu chữ"></div>
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-1" 
                                            type="button"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editModal"
                                            onclick="editCampaign(<?php echo htmlspecialchars(json_encode($c)); ?>)"
                                            title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="/web_qlsp/campaigns/toggle/<?php echo $c['id']; ?>" 
                                       class="btn btn-sm btn-<?php echo $c['status'] ? 'success' : 'secondary'; ?> me-1" 
                                       title="<?php echo $c['status'] ? 'Ẩn' : 'Hiển'; ?>">
                                        <i class="fas fa-eye<?php echo $c['status'] ? '' : '-slash'; ?>"></i>
                                    </a>
                                     <button class="btn btn-sm btn-danger"
                                        onclick="confirmDelete('/web_qlsp/campaigns/delete/<?php echo $c['id']; ?>')" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                                </td>
                            </tr>
                            <?php
                                }
                            } else {
                            ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Chưa có section nào.
                                </td>
                            </tr>
                            <?php } ?>
</tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL CHỈNH SỬA -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/web_qlsp/campaigns/update" enctype="multipart/form-data">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold">Chỉnh Sửa Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="old_banner_image" id="edit_old_image">
                    
                    <div class="mb-3">
                        <label class="fw-bold">Loại hiển thị (*)</label>
                        <select class="form-select" id="edit_type" name="type" onchange="toggleEditForm()">
                            <option value="category_grid">1. Danh sách Danh mục</option>
                            <option value="overlay_banner">2. Banner Chữ Đè Ảnh</option>
                            <option value="collection">3. Danh sách Sản phẩm</option>
                            <option value="flash_sale">4. Flash Sale</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Tiêu đề Section (*)</label>
                        <input type="text" class="form-control" name="title" id="edit_title" required>
                    </div>

                    <!-- BLOCK BANNER -->
                    <div id="edit_block_banner" class="mb-3 p-3 border rounded bg-light" style="display:none;">
                        <h6 class="text-primary fw-bold">Cấu hình Banner</h6>
                        <div id="current_image_preview" class="mb-2"></div>
                        <label>Ảnh nền mới (để trống nếu không đổi)</label>
                        <input type="file" class="form-control mb-2" name="banner_image" accept="image/*">
                        <div class="row mb-2">
                            <div class="col-6">
                                <label>Chữ trên nút</label>
                                <input type="text" class="form-control" name="button_text" id="edit_button_text">
                            </div>
                            <div class="col-6">
                                <label>Vị trí chữ</label>
                                <select class="form-select" name="text_position" id="edit_text_position">
                                    <option value="left">Trái</option>
                                    <option value="center">Giữa</option>
                                    <option value="right">Phải</option>
</select>
                            </div>
                        </div>
                        <label>Link khi bấm vào</label>
                        <input type="text" class="form-control" name="link_url" id="edit_link_url">
                    </div>

                    <!-- BLOCK COLLECTION -->
                    <div id="edit_block_collection" class="mb-3 p-3 border rounded bg-light" style="display:none;">
                        <h6 class="text-success fw-bold">Chọn Nguồn Sản Phẩm</h6>
                        <label>Lấy từ Bộ Sưu Tập nào?</label>
                        <select class="form-select" name="collection_id" id="edit_collection_id">
                            <option value="">-- Chọn Collection --</option>
                            <?php
                            if(isset($data['collections_list']) && mysqli_num_rows($data['collections_list']) > 0) {
                                mysqli_data_seek($data['collections_list'], 0);
                                while($col = mysqli_fetch_assoc($data['collections_list'])) {
                                    echo '<option value="' . $col['id'] . '">' . htmlspecialchars($col['name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- BLOCK FLASH -->
                    <div id="edit_block_flash" class="mb-3 p-3 border rounded bg-light" style="display:none;">
                        <h6 class="text-danger fw-bold">Thời gian Flash Sale</h6>
                        <label>Kết thúc lúc:</label>
                        <input type="datetime-local" class="form-control" name="end_time" id="edit_end_time">
                    </div>

                    <!-- STYLE -->
                    <div class="p-3 border rounded bg-light mb-3">
                        <h6 class="fw-bold">Giao diện & Thứ tự</h6>
                        <div class="row mb-2">
                            <div class="col-6">
                                <label class="fw-bold small">Màu nền</label>
                                <input type="color" class="form-control form-control-color w-100" name="bg_color" id="edit_bg_color">
                            </div>


                            <div class="col-6">
                                <label class="fw-bold small">Màu chữ tiêu đề</label>
                                <input type="color" class="form-control form-control-color w-100" name="text_color" id="edit_text_color">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="fw-bold small">Thứ tự hiển thị</label>
                            <input type="number" class="form-control" name="display_order" id="edit_display_order">
                        </div>
                        <div class="form-check">
<input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
                            <label class="form-check-label">Hiển thị ngay</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="update_campaign" class="btn btn-warning fw-bold">
                        <i class="fas fa-save me-1"></i> CẬP NHẬT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="/web_qlsp/Public/js/campaigns.js"></script>
<script src="/web_qlsp/Public/js/result.js"></script>

<?php if(isset($_SESSION['status_msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            checkFlashMessage("<?php echo $_SESSION['status_msg']; ?>");
        });
    </script>
    <?php unset($_SESSION['status_msg']); ?>
<?php endif; ?>

<script>
    const colorPicker = document.getElementById('edit_bg_color_picker');
    const colorText = document.getElementById('edit_bg_color_text');

    // Khi chọn màu từ bảng màu -> Cập nhật ô text
    colorPicker.addEventListener('input', (e) => {
        colorText.value = e.target.value.toUpperCase();
    });

    // Khi nhập mã màu vào ô text -> Cập nhật ô chọn màu
    colorText.addEventListener('input', (e) => {
        const color = e.target.value;
        // Kiểm tra định dạng mã HEX hợp lệ (ví dụ: #FFFFFF hoặc #FFF)
        if (/^#[0-9A-F]{6}$/i.test(color)) {
            colorPicker.value = color;
        }
    });
</script>