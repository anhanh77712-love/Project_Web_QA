<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Danh mục sản phẩm</h4>
    <p class="text-muted small mb-0">Quản lý các nhóm sản phẩm hiển thị trên website</p>
  </div>
</div>
<div class="card-table">
  <div class="p-3 border-bottom bg-white">
        
        <form method="POST" action="/web_qlsp/api/categories_api/search"
              class="d-flex align-items-center w-100">

            <div class="search-wrapper position-relative me-auto">
                <div class="search-icon">
                    <i class="fas fa-search"></i>
                </div>
                <input type="text"
                       name="txtSearch"
                       class="form-control form-search"
                       placeholder="Tìm kiếm danh mục..."
                       value="<?php echo isset($data['search']) ? $data['search'] : ''; ?>">
            </div>

            <div class="d-flex gap-2">
                
                <button type="submit" class="btn btn-dark-blue" name="btnTimkiem">
                    <i class="fas fa-search"></i> Tìm
                </button>

                <button type="button" class="btn btn-light-gray" onclick="window.location.href='/web_qlsp/api/categories_api'">
                    <i class="fas fa-undo-alt"></i> Làm mới
                </button>

                <button type="submit" class="btn btn-green" name="btnXuat">
                    <i class="fas fa-file-excel"></i> Xuất Excel
                </button>

                <button type="button" class="btn btn-blue" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fas fa-file-import"></i> Nhập Excel
                </button>

                <button type="button" class="btn btn-dark-blue" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus"></i> Thêm Danh Mục
                </button>
            </div>

        </form>
    </div>


  <div class="table-responsive">
    <table class="table-modern">
      <thead>
        <tr>
          <th width="10%">ID</th>
          <th width="15%">Hình ảnh</th>
          <th width="35%">Tên danh mục</th>
          <th width="25%">Đường dẫn (Slug)</th>
          <th width="15%" class="text-end">Hành động</th>
        </tr>
      </thead>
      <tbody id="loading-skeleton"> <?php for ($i = 0; $i < 5; $i++): // Hiển thị 5 dòng giả lập 
                                    ?> <tr>
            <td>
              <div class="skeleton" style="width: 30px; height: 15px;"></div>
            </td>
            <td>
              <div class="skeleton rounded border" style="width: 50px; height: 50px;"></div>
            </td>
            <td>
              <div class="skeleton" style="width: 150px; height: 18px;"></div>
            </td>
            <td>
              <div class="skeleton" style="width: 100px; height: 20px; border-radius: 10px;"></div>
            </td>
            <td class="text-end">
              <div class="skeleton" style="width: 32px; height: 32px; margin-right: 4px;"></div>
              <div class="skeleton" style="width: 32px; height: 32px;"></div>
            </td>
          </tr> <?php endfor; ?> </tbody>



      <tbody id="actual-content" style="display: none;">
        <?php
        if (isset($data['categories_list']) && mysqli_num_rows($data['categories_list']) > 0) {
          foreach ($data['categories_list'] as $c) {
        ?>
            <tr>
              <td class="text-muted">#<?php echo $c['id']; ?></td>
              <td>
                <?php if (!empty($c['thumbnail'])) { ?>
                  <img src="/web_qlsp/Public/Picture/categories/<?php echo $c['thumbnail']; ?>"
                    class="rounded border"
                    style="width: 50px; height: 50px; object-fit: cover;">
                <?php } else { ?>
                  <span class="badge bg-light text-secondary border">No Image</span>
                <?php } ?>
              </td>
              <td class="fw-bold text-dark"><?php echo $c['name']; ?></td>
              <td><span class="badge-slug"><?php echo $c['slug']; ?></span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary btn-edit me-1"
                  data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                  data-id="<?php echo $c['id']; ?>"
                  data-name="<?php echo $c['name']; ?>"
                  data-slug="<?php echo $c['slug']; ?>"
                  data-thumbnail="<?php echo $c['thumbnail']; ?>" title="Chỉnh sửa">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger"
                  onclick="confirmDelete('/web_qlsp/api/categories_api/delete/<?php echo $c['id']; ?>')" title="Xóa">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          <?php
          }
        } else {
          ?>
          <tr>
            <td colspan="5" class="text-center py-5 text-muted">
              Không tìm thấy danh mục nào.
            </td>
          </tr>
        <?php } ?>

      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Thêm danh mục mới</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data" action="/web_qlsp/api/categories_api/add">
          <div class="mb-3">
            <label class="form-label">Tên danh mục</label>
            <input type="text" name="name" class="form-control" required onkeyup="generateSlug(this.value, 'slug')">
          </div>
          <div class="mb-3">
            <label class="form-label">Ảnh đại diện</label>
            <input type="file" name="image" class="form-control" accept="image/*">
          </div>
          <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" id="slug" class="form-control bg-light" readonly>
          </div>
          <button type="submit" name="add_category" class="btn btn-dark w-100">Thêm ngay</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Chỉnh sửa danh mục</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data" action="/web_qlsp/api/categories_api/update">
          <input type="hidden" name="id" id="edit_id">
          <input type="hidden" name="old_image" id="edit_old_image">

          <div class="mb-3">
            <label class="form-label">Tên danh mục</label>
            <input type="text" name="name" id="edit_name" class="form-control" required onkeyup="generateSlug(this.value, 'edit_slug')">
          </div>

          <div class="mb-3">
            <label class="form-label">Ảnh đại diện</label>
            <input type="file" name="image" class="form-control" accept="image/*">

            <div id="preview_area" class="mt-2" style="display:none;">
              <small class="text-muted d-block mb-1">Ảnh hiện tại:</small>
              <img id="edit_preview_img" src="" class="rounded border" width="80">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" id="edit_slug" class="form-control bg-light" readonly>
          </div>
          <button type="submit" name="edit_category" class="btn btn-primary w-100">Lưu thay đổi</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="importModalLabel">Nhập Danh Mục từ Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/web_qlsp/api/categories_api/importExcelCat" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fileExcel" class="form-label">Chọn file Excel (.xlsx, .xls)</label>
                        <input class="form-control" type="file" id="fileExcel" name="import_file" accept=".xlsx, .xls, .csv" required>
                    </div>
                    <div class="alert alert-info" role="alert">
                        <small>
                            <strong>Cấu trúc file Excel:</strong><br>
                            - Cột A: Tên danh mục<br>
                            - Cột B: Đường dẫn ảnh (Thumbnail)
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" name="btn_import" class="btn btn-primary">Xác nhận nhập</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/web_qlsp/Public/css/loading.css">
<link rel="stylesheet" href="/web_qlsp/Public/Css/categories.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/web_qlsp/Public/js/result.js"></script>
<script src="/web_qlsp/Public/js/category.js"></script>
<script src="/web_qlsp/Public/js/loading.js"></script>


<?php if (isset($_SESSION['status_msg'])): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      checkFlashMessage("<?php echo $_SESSION['status_msg']; ?>");
    });
  </script>
  <?php unset($_SESSION['status_msg']); ?>
<?php endif; ?>