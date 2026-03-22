<style>
    /* 1. Cấu hình chung */
    body {
    /* Đổi Inter thành Montserrat */
    font-family: "Montserrat", sans-serif;
    background-color: #ffffff;
    margin: 0;
}
    a { text-decoration: none; color: #000; transition: 0.2s; }
    a:hover { color: #2f5acf; }

    /* 2. Tiêu đề Section */
    .section-header {
        display: flex; justify-content: space-between; align-items: flex-end;
        margin-bottom: 25px;
    }
    .section-title {
        font-weight: 800; /* Rất đậm */
        font-size: 28px;
        text-transform: uppercase;
        letter-spacing: -0.5px;
        margin: 0;
    }
    .view-more {
        font-weight: 600;
        font-size: 14px;
        text-decoration: underline;
        color: #000;
    }

    /* 3. Product Card (Giống ảnh mẫu) */
    .cool-card {
        border: none;
        background: transparent;
    }
    
    /* Khối chứa ảnh (Nền xám) */
    .cool-card-img-wrapper {
        background-color: #F3F3F3; /* Màu xám nhạt giống ảnh */
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        aspect-ratio: 1/1.1; /* Tỉ lệ ảnh hơi cao một chút */
        margin-bottom: 12px;
    }

    .cool-card-img-wrapper img {
        width: 100%; height: 100%;
        object-fit: cover; /* Hoặc contain nếu muốn thấy hết áo */
        mix-blend-mode: multiply; /* Giúp ảnh hòa trộn vào nền xám tốt hơn */
        transition: transform 0.4s ease;
    }
    .cool-card:hover .cool-card-img-wrapper img {
        transform: scale(1.05); /* Zoom nhẹ */
    }

    /* Badge (Tag ĐÁNG MUA / NEW) - Góc phải */
    .cool-badge {
        position: absolute; top: 12px; right: 12px;
        background-color: #000; color: #fff;
        font-size: 10px; font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        text-transform: uppercase;
        z-index: 2;
    }
    .cool-badge.new { background-color: #2f5acf; } /* Màu xanh cho New */

    /* Các chấm màu (Swatches) */
    .color-swatches {
        display: flex; gap: 6px; margin-bottom: 8px;
    }
    .color-dot {
        width: 18px; height: 18px;
        border-radius: 50%;
        border: 1px solid #ddd;
        position: relative;
    }
    .color-dot.active::after { /* Vòng bao ngoài cho màu đang chọn */
        content: ''; position: absolute;
        top: -3px; left: -3px; right: -3px; bottom: -3px;
        border: 1px solid #999; border-radius: 50%;
    }

    /* Thông tin sản phẩm */
    .cool-prod-name {
        font-size: 14px;
        font-weight: 500;
        color: #333;
        margin-bottom: 4px;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .cool-price {
        font-size: 14px; 
        font-weight: 700; /* Giá đậm */
        color: #e74c3c;
    }
    .cool-price del {
        color: #999; font-weight: 400; font-size: 13px; margin-left: 5px;
    }

    /* Nút điều hướng (Giả lập giống ảnh) */
    .nav-arrow {
        width: 40px; height: 40px;
        background: #000; color: #fff;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        position: absolute; top: 40%;
        z-index: 10; cursor: pointer;
        opacity: 0; transition: 0.3s;
    }
    .section-wrapper:hover .nav-arrow { opacity: 1; }
    .nav-prev { left: -20px; }
    .nav-next { right: -20px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý click color dot
    document.querySelectorAll('.color-dot').forEach(dot => {
        dot.addEventListener('click', function(e) {
            e.preventDefault();
            
            const colorSwatches = this.closest('.color-swatches');
            const productId = colorSwatches.getAttribute('data-product-id');
            const color = this.getAttribute('data-color');
            const images = JSON.parse(this.getAttribute('data-images'));
            
            // Xóa active từ tất cả dots
            colorSwatches.querySelectorAll('.color-dot').forEach(d => {
                d.classList.remove('active');
            });
            
            // Thêm active cho dot được click
            this.classList.add('active');
            
            // Thay đổi ảnh
            if(images && images.length > 0) {
                const productImg = document.querySelector(`.product-image[data-product-id="${productId}"]`);
                if(productImg) {
                    productImg.src = '/web_qlsp/Public/Picture/' + images[0];
                }
            }
        });
    });
});
</script>

<?php 
$banners = $data['banners'] ?? null;
$sections = $data['sections'] ?? null;
$home_model = $data['home_model'] ?? null;
?>

<!-- BANNERS CAROUSEL -->
<?php if($banners && mysqli_num_rows($banners) > 0): ?>
    <div id="heroCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php 
            $i = 0;
            mysqli_data_seek($banners, 0);
            while($b = mysqli_fetch_assoc($banners)): 
            ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i==0?'active':'' ?>"></button>
            <?php 
                $i++;
            endwhile; 
            ?>
        </div>
        <div class="carousel-inner">
            <?php 
            $i = 0;
            mysqli_data_seek($banners, 0);
            while($b = mysqli_fetch_assoc($banners)): 
            ?>
                <div class="carousel-item <?= $i==0?'active':'' ?>">
                    <a href="<?= htmlspecialchars($b['link_url']) ?>">
                        <img src="/web_qlsp/Public/Picture/banners/<?= htmlspecialchars($b['image_url']) ?>" 
                             class="d-block w-100" 
                             style="object-fit: cover; min-height: 400px; max-height: 600px;"
                             alt="<?= htmlspecialchars($b['title']) ?>">
                    </a>
                </div>
            <?php 
                $i++;
            endwhile; 
            ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
    </div>
<?php endif; ?>

<!-- SECTIONS -->
<?php 
if($sections && mysqli_num_rows($sections) > 0):
    mysqli_data_seek($sections, 0);
    while($sec = mysqli_fetch_assoc($sections)): 
?>
    
    <?php if (in_array($sec['section_type'], ['collection', 'flash_sale'])): ?>
    <!-- COLLECTION / FLASH SALE SECTION -->
    <section class="section-wrapper py-5 position-relative" style="background-color: <?= $sec['bg_color'] ?>;">
        <div class="container" >
            
            <div class="section-header">
                <h2 class="section-title" style="color: <?= $sec['text_color'] ?>;">
                    <?php if($sec['section_type'] == 'flash_sale'): ?>
                        <i class="fas fa-bolt text-danger me-2"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($sec['title']) ?>
                </h2>
                
                <?php if(!empty($sec['collection_id']) && $home_model): 
                    $cSlug = $home_model->collection_getSlug($sec['collection_id']); 
                ?>
                    <a href="/web_qlsp/product_list_customer?sale=1" class="view-more">Xem thêm</a>
                <?php endif; ?>
            </div>

            <div class="row g-4">
                <?php
                // Lấy sản phẩm theo collection
                $prods = [];
                if(!empty($sec['collection_id']) && $home_model) {
                    $prods_result = $home_model->products_getByCollection($sec['collection_id'], 4);
                    if($prods_result) {
                        while($p = mysqli_fetch_assoc($prods_result)) {
                            $prods[] = $p;
                        }
                    }
                }
                
                if(count($prods) > 0):
                    foreach($prods as $p):
                ?>
                <div class="col-6 col-md-3">
                    <div class="cool-card">
                        
                        <div class="cool-card-img-wrapper">
                            <a href="/web_qlsp/product_detail?slug=<?= urlencode($p['slug']) ?>">
                                <img src="/web_qlsp/Public/Picture/<?= htmlspecialchars($p['thumbnail']) ?>" 
                                     alt="<?= htmlspecialchars($p['name']) ?>"
                                     class="product-image"
                                     data-product-id="<?= $p['id'] ?>">
                            </a>
                        </div>

                        <div class="card-info">
                            <a href="/web_qlsp/product_detail?slug=<?= urlencode($p['slug']) ?>" class="cool-prod-name">
                                <?= htmlspecialchars($p['name']) ?>
                            </a>
                            
                            <!-- Màu sắc -->
                            <?php
                            $variants_result = $home_model->get_variants_by_product($p['id']);
                            $colors = [];
                            $variant_map = []; // Lưu mapping color -> variant_id & images
                            if($variants_result) {
                                while($v = mysqli_fetch_assoc($variants_result)) {
                                    if(!in_array($v['color'], $colors)) {
                                        $colors[] = $v['color'];
                                        // Lấy ảnh cho variant này
                                        $images_result = $home_model->get_images_by_variant($v['id']);
                                        $images = [];
                                        if($images_result) {
                                            while($img = mysqli_fetch_assoc($images_result)) {
                                                $images[] = $img['image_url'];
                                            }
                                        }
                                        // Nếu không có ảnh chi tiết, dùng ảnh đại diện
                                        if(empty($images)) {
                                            $images[] = $p['thumbnail'];
                                        }
                                        $variant_map[$v['color']] = [
                                            'variant_id' => $v['id'],
                                            'images' => $images
                                        ];
                                    }
                                }
                            }
                            ?>
                            
                            <!-- Color Swatches -->
                            <?php if(count($colors) > 0): ?>
                            <div class="color-swatches mb-2" data-product-id="<?= $p['id'] ?>" data-variant-map='<?= json_encode($variant_map) ?>'>
                                <?php foreach($colors as $idx => $color): ?>
                                <div class="color-dot <?= $idx === 0 ? 'active' : '' ?>" 
                                     data-color="<?= htmlspecialchars($color) ?>"
                                     data-images='<?= json_encode($variant_map[$color]['images']) ?>'
                                     style="background-color: <?= $home_model->get_color_hex($color) ?>; border: <?= $color === 'Trắng' ? '2px solid #ddd' : '1px solid transparent' ?>; cursor: pointer;"
                                     title="<?= $color ?>"></div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="cool-price">
                                <?php if($p['is_sale'] == 1): ?>
                                    <?= number_format($p['base_price']) ?>đ
                                    <del><?= number_format($p['base_price'] * 1.1) ?>đ</del>
                                <?php else: ?>
                                    <?= number_format($p['base_price']) ?>đ
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
                <?php 
                    endforeach;
                else:
                ?>
                    <p class='text-center text-muted w-100'>Đang cập nhật sản phẩm...</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php elseif ($sec['section_type'] == 'category_grid'): ?>
    <!-- CATEGORY GRID SECTION -->
    <section class="py-5" style="background-color: <?= $sec['bg_color'] ?>;">
        <div class="container">
            <div class="section-header justify-content-center mb-4">
                 <h2 class="section-title" style="color: <?= $sec['text_color'] ?>;">
                    <?= htmlspecialchars($sec['title']) ?>
                 </h2>
            </div>
            
            <div class="row g-3 justify-content-center">
                <?php 
                if($home_model):
                    $categories = $home_model->categories_selectAll();
                    $count = 0;
                    while($c = mysqli_fetch_assoc($categories)): 
                        if($count >= 6) break;
                        $img = !empty($c['thumbnail']) ? 
                               "/web_qlsp/Public/Picture/categories/" . $c['thumbnail'] : 
                               "https://placehold.co/300x400";
                ?>
                <div class="col-4 col-md-2">
                    <a href="/web_qlsp/product_list_customer?category=<?= urlencode($c['slug']) ?>" class="d-block text-center">
                        <div class="rounded-3 overflow-hidden mb-2 shadow-sm">
                            <img src="<?= $img ?>" 
                                 class="w-100" 
                                 style="aspect-ratio: 3/4; object-fit: cover;"
                                 alt="<?= htmlspecialchars($c['name']) ?>">
                        </div>
                        <div class="fw-bold small text-uppercase" style="color: <?= $sec['text_color'] ?>">
                            <?= htmlspecialchars($c['name']) ?>
                        </div>
                    </a>
                </div>
                <?php 
                        $count++;
                    endwhile;
                endif;
                ?>
            </div>
        </div>
    </section>

    <?php elseif ($sec['section_type'] == 'overlay_banner'): ?>
    <!-- OVERLAY BANNER SECTION -->
    <section class="py-4">
        <div class="container">
            <div class="position-relative rounded-3 overflow-hidden">
                <a href="<?= htmlspecialchars($sec['link_url']) ?>">
                    <img src="/web_qlsp/Public/Picture/campaigns/<?= htmlspecialchars($sec['image_url']) ?>" 
                         class="w-100 d-block" 
                         style="min-height: 350px; object-fit: cover;"
                         alt="<?= htmlspecialchars($sec['title']) ?>">
                </a>
                <?php 
                $pos = "start-0"; 
                if($sec['text_position'] == 'center') {
                    $pos = "start-50 translate-middle-x"; 
                } elseif($sec['text_position'] == 'right') {
                    $pos = "end-0 me-5"; 
                }
                ?>
                <div class="position-absolute top-50 translate-middle-y <?= $pos ?> p-5" 
                     style="z-index: 2; max-width: 600px; color: <?= $sec['text_color'] ?>;">
                    <h2 class="fw-bold display-5 mb-4"><?= htmlspecialchars($sec['title']) ?></h2>
                    <a href="<?= htmlspecialchars($sec['link_url']) ?>" 
                       class="btn btn-light rounded-pill px-4 py-2 fw-bold text-dark border-0">
                        <?= htmlspecialchars($sec['button_text'] ?? 'XEM NGAY') ?>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php 
    endwhile;
endif; 
?>
