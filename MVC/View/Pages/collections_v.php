<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Bộ sưu tập (Collections)</h4>
        <p class="text-muted small mb-0">
            Quản lý các nhóm sản phẩm theo chủ đề (VD: Mùa hè, Chạy bộ...)
        </p>
    </div>

</div>

<div class="card-table">

    <!-- SEARCH BAR -->
    <div class="p-3 border-bottom bg-white">
        
        <form method="POST" action="/web_qlsp/api/collections_api/search"
              class="d-flex align-items-center w-100">

            <div class="search-wrapper position-relative me-auto">
                <div class="search-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                        <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 1 0 0 13.5 6.75 6.75 0 0 0 0-13.5ZM2.25 10.5a8.25 8.25 0 1 1 14.59 5.28l4.69 4.69a.75.75 0 1 1-1.06 1.06l-4.69-4.69A8.25 8.25 0 0 1 2.25 10.5Z" clip-rule="evenodd" />
                    </svg>
                </div>

                <input type="text"
                       name="txtSearch"
                       class="form-control form-search"
                       placeholder="Tìm kiếm bộ sưu tập..."
                       value="<?php echo isset($data['search']) ? $data['search'] : ''; ?>">
            </div>

            <div class="d-flex gap-2">
                
                <button type="submit" class="btn btn-dark-blue" name="btnTimkiem">
                    <i class="fas fa-search"></i> Tìm
                </button>

                <button type="button" class="btn btn-light-gray" onclick="window.location.href='/web_qlsp/collections'">
                    <i class="fas fa-undo-alt"></i> Làm mới
                </button>

                <button type="submit" class="btn btn-green" name="btnXuat">
                    <i class="fas fa-file-excel"></i> Xuất Excel
                </button>

                <button type="button" class="btn btn-blue" data-bs-toggle="modal" data-bs-target="#importCollectionModal">
                    <i class="fas fa-file-import"></i> Nhập Excel
                </button>

                <button type="button" class="btn btn-dark-blue" data-bs-toggle="modal" data-bs-target="#addCollectionModal">
                    <i class="fas fa-plus"></i> Thêm bộ sưu tập
                </button>

            </div>

        </form>
    </div>

    <!-- TABLE -->
   <div class="table-responsive">
    <table class="table-modern">
        <thead>
            <tr>
                <th width="10%">ID</th>
                <th width="10%">Ảnh</th>
                <th width="30%">Tên Bộ sưu tập</th>
                <th width="15%" class="text-center">Số lượng</th> <th width="25%">Slug</th>
                <th width="10%" class="text-end">Hành động</th>
            </tr>
        </thead>
        
        <tbody id="loading-skeleton"> 
            <?php for ($i = 0; $i < 5; $i++): ?> 
            <tr> 
                <td><div class="skeleton" style="width: 30px; height: 18px;"></div></td> 
                <td><div class="skeleton rounded border" style="width: 50px; height: 50px;"></div></td> 
                <td><div class="skeleton" style="width: 180px; height: 20px;"></div></td> 
                <td><div class="skeleton" style="width: 40px; height: 20px; margin: 0 auto;"></div></td> 
                <td><div class="skeleton" style="width: 120px; height: 22px; border-radius: 12px;"></div></td> 
                <td class="text-end"> 
                    <div class="skeleton" style="width: 24px; height: 24px; display: inline-block;"></div> 
                    <div class="skeleton" style="width: 24px; height: 24px; display: inline-block;"></div> 
                </td> 
            </tr> 
            <?php endfor; ?> 
        </tbody>

        <tbody id="actual-content" style="display: none;">
            <?php
            // Kiểm tra dữ liệu có tồn tại và có dòng nào không
            if (isset($data['collections_list']) && mysqli_num_rows($data['collections_list']) > 0) {
                
                // Lặp qua từng dòng dữ liệu
                foreach ($data['collections_list'] as $c) {
                    
                    // --- ĐOẠN CODE GỌI MODEL ĐỂ ĐẾM ---
                    $soLuongSanPham = 0;
                    // Kiểm tra xem model đã được truyền từ Controller sang chưa
                    if (isset($data['collec_model'])) {
                        $soLuongSanPham = $data['collec_model']->countProductsInCollection($c['id']);
                    }
                    // -----------------------------------
            ?>
                <tr>
                    <td class="text-muted">#<?php echo $c['id']; ?></td>
                    
                    <td>
                        <?php if (!empty($c['thumbnail'])) { ?>
                            <img src="/web_qlsp/Public/Picture/collections/<?php echo $c['thumbnail']; ?>"
                                class="rounded border"
                                style="width: 50px; height: 50px; object-fit: cover;">
                        <?php } else { ?>
                            <span class="badge bg-light text-secondary border">No Image</span>
                        <?php } ?>
                    </td>
                    
                    <td class="fw-bold text-dark"><?php echo $c['name']; ?></td>
                    
                    <td class="text-center">
                        <span class="badge bg-info text-dark rounded-pill px-3">
                            <?php echo $soLuongSanPham; ?> sản phẩm
                        </span>
                    </td>

                    <td><span class="badge-slug"><?php echo $c['slug']; ?></span></td>
                    
                    <td class="text-end">
                        <button class="btn-icon text-primary me-1 btn-edit"
                            data-bs-toggle="modal"
                            data-bs-target="#editCollectionModal"
                            data-id="<?php echo $c['id']; ?>"
                            data-name="<?php echo $c['name']; ?>"
                            data-slug="<?php echo $c['slug']; ?>"
                            data-thumbnail="<?php echo $c['thumbnail']; ?>"
                            title="Chỉnh sửa">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                            <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" />
                            </svg>
                        </button>

                        <button class="btn-icon text-danger" 
                            onclick="confirmDelete('/web_qlsp/api/collections_api/delete/<?php echo $c['id']; ?>')" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php
                } // Kết thúc vòng lặp
            } else {
            ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        Chưa có Bộ sưu tập nào.
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editCollectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Chỉnh sửa Bộ sưu tập</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" action="/web_qlsp/api/collections_api/update">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="old_image" id="edit_old_image">

                    <div class="mb-3">
                        <label class="form-label">Tên Bộ sưu tập</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required onkeyup="generateSlug(this.value, 'edit_slug_modal')">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ảnh bìa</label>
                        <input type="file" name="image" class="form-control" accept="image/*">

                        <div id="preview_area" class="mt-2" style="display:none;">
                            <small class="text-muted d-block mb-1">Ảnh hiện tại:</small>
                            <img id="edit_preview_img" src="" class="rounded border" width="80">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" id="edit_slug_modal" class="form-control bg-light" readonly>
                    </div>
                    <button type="submit" name="edit_collection" class="btn btn-primary w-100">Lưu thay đổi</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="addCollectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Thêm Bộ sưu tập mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" action="/web_qlsp/api/collections_api/add">
                    <div class="mb-3">
                        <label class="form-label">Tên Bộ sưu tập</label>
                        <input type="text" class="form-control" placeholder="VD: Mùa hè 2025" name="name" onkeyup="generateSlug(this.value, 'edit_slug')">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control bg-light" readonly name="slug" id="edit_slug">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ảnh bìa (Thumbnail)</label>
                        <input type="file" class="form-control" accept="image/*" name="image">
                    </div>
                    <button type="submit" class="btn btn-dark w-100" name="add_collection">Thêm ngay</button>
                </form>
            </div>
        </div>
    </div>
    
</div>

<div class="modal fade" id="importCollectionModal" tabindex="-1" aria-labelledby="importCollectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="importCollectionModalLabel">Nhập Bộ Sưu Tập từ Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/web_qlsp/api/collections_api/importExcelCollections" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fileExcelCollection" class="form-label">Chọn file Excel (.xlsx, .xls)</label>
                        <input class="form-control" type="file" id="fileExcelCollection" name="import_file_collection" accept=".xlsx, .xls" required>
                    </div>
                    <div class="alert alert-warning" role="alert">
                        <small>
                            <strong>Cấu trúc cột Excel:</strong><br>
                            - Cột A: Tên Bộ Sưu Tập<br>
                            - Cột B: Link ảnh (Online) hoặc Tên ảnh (có sẵn trong folder)
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" name="btn_import_collection" class="btn btn-success">Xác nhận nhập</button>
                </div>
            </form>
        </div>
    </div>
</div>
<link rel="stylesheet" href="/web_qlsp/Public/css/loading.css">
<link rel="stylesheet" href="/web_qlsp/Public/css/collections.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="/web_qlsp/Public/js/collections.js"></script>
<script src="/web_qlsp/Public/js/result.js"></script>
<script src="/web_qlsp/Public/js/loading.js"></script>

<?php if(isset($_SESSION['status_msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            checkFlashMessage("<?php echo $_SESSION['status_msg']; ?>");
        });
    </script>
    <?php unset($_SESSION['status_msg']); ?>
<?php endif; ?>