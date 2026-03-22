<div class="row">
    <div class="col-12 mb-4">
        <h4 class="fw-bold">Nhập dữ liệu từ Excel </h4>
        <p class="text-muted">
            Lưu ý: Hệ thống hỗ trợ định dạng <b>.xlsx</b> hoặc <b>.xls</b> (PHPExcel)
        </p>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white fw-bold">
                1. NHẬP DANH MỤC
            </div>
            <div class="card-body">
                <form method="POST" action="/web_qlsp/import/upload_categories" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Chọn file Danh mục</label>
                        <input type="file" name="txtfile" class="form-control" required>
                    </div>

                    <div class="alert alert-light border">
                        <small>
                            <strong>Cấu trúc file mẫu:</strong><br>
                            Cột A: Tên danh mục | Cột B: Slug | Cột C: Ảnh
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary" name="btnUpload">
                        <i class="fas fa-upload"></i> Upload Danh mục
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white fw-bold">
                2. NHẬP SẢN PHẨM
            </div>
            <div class="card-body">
                <form method="POST" action="/web_qlsp/import/upload_products" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Chọn file Sản phẩm</label>
                        <input type="file" name="txtfile" class="form-control" required>
                    </div>

                    <div class="alert alert-light border">
                        <small>
                            <strong>Cấu trúc cột:</strong><br>
                            A: Tên | B: Slug | C: Giá | D: ID Danh mục | E: ID BST | F: Mô tả | G: Ảnh
                        </small>
                    </div>

                    <button type="submit" class="btn btn-success" name="btnUpload">
                        <i class="fas fa-upload"></i> Upload Sản phẩm
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>