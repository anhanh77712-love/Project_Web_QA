<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Shop Demo - White Background</title>

<style>
/* ===== SHOP PAGE WRAPPER ===== */
.shop-page {
    /* Đã sửa thành màu trắng theo yêu cầu */
    background: #ffffff;
}

.shop-page * {
    box-sizing: border-box;
}

/* ===== LAYOUT ===== */
.shop-container {
    display: flex;
    max-width: 1200px;
    margin: 0 auto;
    gap: 20px;
}

/* ===== SIDEBAR (Sticky + Scroll) ===== */
.shop-sidebar {
    width: 250px;
    background: #fff;
    padding: 16px;
    border-radius: 10px;
    /* Tăng shadow lên một chút để tách biệt với nền trắng */
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    
    /* Sticky logic */
    position: sticky;
    top: 20px;
    align-self: flex-start;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
}

/* ===== FILTER STYLES (Toggle + Radio) ===== */
.shop-filter {
    margin-bottom: 18px;
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 15px;
}
.shop-filter:last-child {
    border-bottom: none;
}

.shop-sidebar__title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 15px;
    border-bottom: 2px solid #333;
    padding-bottom: 8px;
}

.shop-filter__header {
    font-weight: 600;
    color: #333;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    background: transparent;
    border: 0;
    padding: 0;
    width: 100%;
    font: inherit;
    margin-bottom: 10px;
}

.shop-filter__arrow {
    font-size: 14px;
    color: #666;
    transition: transform .2s ease;
}

.shop-filter__content { display: none; }
.shop-filter.open .shop-filter__content { display: block; }
.shop-filter.open .shop-filter__arrow { transform: rotate(180deg); }

.shop-filter__item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    padding: 6px 8px;
    border-radius: 6px;
    cursor: pointer;
    transition: 0.2s;
}

.shop-filter__item:hover {
    background: #f2f2f2;
}

.shop-filter__item input {
    accent-color: #e74c3c;
}

/* ===== PRODUCTS AREA ===== */
.shop-products {
    flex: 1;
}

.shop-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.shop-topbar__count {
    font-size: 14px;
    color: #555;
}

.shop-topbar__sort {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #ddd;
}

/* ===== GRID ===== */
.shop-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}

/* ===== CARD STYLES ===== */
.fs-card { background: transparent; border: 0; }
/* Vì nền trang màu trắng, ta đổi nền ảnh thành xám nhạt để nổi bật sản phẩm */
.fs-img-wrap { background: #f9f9f9; border-radius: 8px; overflow: hidden; height: 300px; margin-bottom: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); border: 1px solid #eee; }
.fs-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease; display:block; mix-blend-mode: multiply; }
.fs-card:hover .fs-img-wrap img { transform: scale(1.04); }
.fs-title { font-size: 14px; font-weight: 500; color: #333; margin-bottom: 8px; text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.fs-price { font-size: 14px; font-weight: 700; color: #e74c3c; }
.fs-price-old { color: #999; font-weight: 400; font-size: 13px; margin-left: 8px; text-decoration: line-through; }
.fs-badge { background: #e74c3c; color: #fff; font-size: 12px; padding: 2px 8px; border-radius: 999px; margin-left: 8px; }

/* Color swatches */
.color-swatches{ display:flex; gap:6px; margin:6px 0 8px; }
.color-dot{ width:18px; height:18px; border-radius:50%; border:1px solid #ddd; position:relative; cursor:pointer; }
.color-dot.active::after{ content:''; position:absolute; top:-3px; left:-3px; right:-3px; bottom:-3px; border:1px solid #999; border-radius:50%; }

/* Responsive */
@media (max-width: 1024px) {
    .shop-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 768px) {
    .shop-container { flex-direction: column; }
    .shop-sidebar { 
        width: 100%; 
        position: static;
        max-height: none; 
    }
    .shop-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
</head>

<body>

<div class="shop-page">
    <div class="shop-container">

        <aside class="shop-sidebar">
            <div class="shop-sidebar__title">Bộ lọc</div>

            <?php
            $categories = $data['categories'] ?? null;
            $current_category = $data['current_category'] ?? '';
            $category_open_class = $current_category ? 'open' : '';
            ?>
            <div class="shop-filter <?= $category_open_class ?>" id="filter-category">
                <button type="button" class="shop-filter__header" onclick="toggleFilter('filter-category')">
                    <span>Danh mục</span>
                    <span class="shop-filter__arrow">▾</span>
                </button>
                
                <div class="shop-filter__content">
                <?php
                if($categories && mysqli_num_rows($categories) > 0):
                    while($cat = mysqli_fetch_assoc($categories)):
                        $checked = ($current_category == $cat['slug']) ? 'checked' : '';
                ?>
                    <label class="shop-filter__item">
                        <input type="radio" 
                               name="filter_category"
                               value="<?= htmlspecialchars($cat['slug']) ?>" 
                               <?= $checked ?>
                               onchange="filterByCategory(this)">
                        <?= htmlspecialchars($cat['name']) ?>
                    </label>
                <?php 
                    endwhile;
                endif;
                ?>
                </div>
            </div>

            <?php
            $current_price = $data['current_price'] ?? '';
            $price_open_class = $current_price ? 'open' : '';
            ?>
            <div class="shop-filter <?= $price_open_class ?>" id="filter-price">
                <button type="button" class="shop-filter__header" onclick="toggleFilter('filter-price')">
                    <span>Giá</span>
                    <span class="shop-filter__arrow">▾</span>
                </button>

                <div class="shop-filter__content">
                <?php
                $prices = [
                    '0-500000' => 'Dưới 500K',
                    '500000-1000000' => '500K - 1tr',
                    '1000000-999999999' => 'Trên 1tr'
                ];
                foreach($prices as $value => $label):
                    $checked = ($current_price == $value) ? 'checked' : '';
                ?>
                    <label class="shop-filter__item">
                        <input type="radio" 
                               name="filter_price"
                               value="<?= $value ?>" 
                               <?= $checked ?>
                               onchange="filterByPrice(this)"> 
                        <?= $label ?>
                    </label>
                <?php endforeach; ?>
                </div>
            </div>
        </aside>

        <script>
        function toggleFilter(filterId) {
            var section = document.getElementById(filterId);
            if(!section) return;
            section.classList.toggle('open');
        }

        function filterByCategory(radio) {
            var currentParams = new URLSearchParams(window.location.search);
            if(radio.checked) {
                currentParams.set('category', radio.value);
            }
            var newUrl = '/web_qlsp/product_list_customer';
            if(currentParams.toString()) {
                newUrl += '?' + currentParams.toString();
            }
            window.location.href = newUrl;
        }

        function filterByPrice(radio) {
            var currentParams = new URLSearchParams(window.location.search);
            if(radio.checked) {
                currentParams.set('price', radio.value);
            }
            var newUrl = '/web_qlsp/product_list_customer';
            if(currentParams.toString()) {
                newUrl += '?' + currentParams.toString();
            }
            window.location.href = newUrl;
        }

        function sortProducts(sortValue) {
            var currentParams = new URLSearchParams(window.location.search);
            if(sortValue && sortValue !== 'default') {
                currentParams.set('sort', sortValue);
            } else {
                currentParams.delete('sort');
            }
            var newUrl = '/web_qlsp/product_list_customer';
            if(currentParams.toString()) {
                newUrl += '?' + currentParams.toString();
            }
            window.location.href = newUrl;
        }

        document.addEventListener('DOMContentLoaded', function(){
            document.querySelectorAll('.color-swatches').forEach(function(group){
                const productEl = group.closest('.fs-card');
                const img = productEl ? productEl.querySelector('.fs-img-wrap img') : null;
                group.querySelectorAll('.color-dot').forEach(function(dot){
                    dot.addEventListener('click', function(){
                        group.querySelectorAll('.color-dot').forEach(d=>d.classList.remove('active'));
                        this.classList.add('active');
                        try{
                            const imgs = JSON.parse(this.getAttribute('data-images') || '[]');
                            if(img && imgs.length>0){
                                img.src = '/web_qlsp/Public/Picture/' + imgs[0];
                            }
                        }catch(e){ console.warn('Invalid image data'); }
                    });
                });
            });
        });
        </script>

        <main class="shop-products">
            <?php
            $products = $data['products'] ?? null;
            $home_model = $data['home_model'] ?? null;
            $product_count = $products ? mysqli_num_rows($products) : 0;
            $search_keyword = $data['search_keyword'] ?? '';
            $current_filter = $data['current_filter'] ?? '';
            ?>

            <?php if($current_filter === 'new'): ?>
            <div style="background: #e8f4fd; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #2f5acf;">
                <div style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 5px;">
                    <i class="fas fa-sparkles" style="color: #2f5acf;"></i> <span style="color: #2f5acf;">SẢN PHẨM MỚI</span>
                </div>
                <div style="font-size: 14px; color: #666;">Hiển thị <strong><?= $product_count ?></strong> sản phẩm mới nhất</div>
            </div>
            <?php elseif($current_filter === 'sale'): ?>
            <div style="background: #fff2f2; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #e74c3c;">
                <div style="font-size: 16px; font-weight: 700; color: #e74c3c; margin-bottom: 5px;">
                    <i class="fas fa-bolt"></i> FLASH SALE
                </div>
                <div style="font-size: 14px; color: #666;">Hiển thị <strong><?= $product_count ?></strong> sản phẩm đang giảm giá</div>
            </div>
            <?php elseif($current_filter === 'bestseller'): ?>
            <div style="background: #fff8e1; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #ffa726;">
                <div style="font-size: 16px; font-weight: 700; color: #f57c00; margin-bottom: 5px;">
                    <i class="fas fa-fire"></i> SẢN PHẨM BÁN CHẠY
                </div>
                <div style="font-size: 14px; color: #666;">Hiển thị <strong><?= $product_count ?></strong> sản phẩm được mua nhiều nhất</div>
            </div>
            <?php elseif($search_keyword): ?>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #2f5acf;">
                <div style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 5px;">
                    Kết quả tìm kiếm cho: "<?= htmlspecialchars($search_keyword) ?>"
                </div>
                <div style="font-size: 14px; color: #666;">Tìm thấy <strong><?= $product_count ?></strong> sản phẩm</div>
            </div>
            <?php else: ?>
            <div style="background: #f0f7ff; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #5a9fd4;">
                <div style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 5px;">
                    <i class="fas fa-th-large" style="color: #5a9fd4;"></i> <span style="color: #5a9fd4;">TẤT CẢ SẢN PHẨM</span>
                </div>
                <div style="font-size: 14px; color: #666;">Hiển thị <strong><?= $product_count ?></strong> sản phẩm</div>
            </div>
            <?php endif; ?>

            <div class="shop-topbar">
                <div class="shop-topbar__count"><?= $product_count ?> kết quả</div>
                <select class="shop-topbar__sort" onchange="sortProducts(this.value)">
                    <?php $current_sort = $data['current_sort'] ?? 'default'; ?>
                    <option value="default" <?= $current_sort == 'default' ? 'selected' : '' ?>>Mặc định</option>
                    <option value="price_asc" <?= $current_sort == 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                    <option value="price_desc" <?= $current_sort == 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                </select>
            </div>

            <div class="shop-grid">
                <?php
                if($products && mysqli_num_rows($products) > 0):
                    while($p = mysqli_fetch_assoc($products)):
                        $original_price = $p['base_price'] * 1.3;
                ?>
                <div class="fs-card">
                    <div class="fs-img-wrap">
                        <a href="/web_qlsp/product_detail?slug=<?= urlencode($p['slug']) ?>">
                            <img src="/web_qlsp/Public/Picture/<?= htmlspecialchars($p['thumbnail']) ?>" 
                                 alt="<?= htmlspecialchars($p['name']) ?>"
                                 onerror="this.src='https://via.placeholder.com/300x380'">
                        </a>
                    </div>
                    <div>
                        <a href="/web_qlsp/product_detail?slug=<?= urlencode($p['slug']) ?>" class="fs-title">
                            <?= htmlspecialchars($p['name']) ?>
                        </a>
                        
                        <?php
                        $colors = [];
                        $variant_map = [];
                        if($home_model){
                            $variants_result = $home_model->get_variants_by_product($p['id']);
                            if($variants_result){
                                while($v = mysqli_fetch_assoc($variants_result)){
                                    if(!in_array($v['color'], $colors)){
                                        $colors[] = $v['color'];
                                        $images_result = $home_model->get_images_by_variant($v['id']);
                                        $images = [];
                                        if($images_result){
                                            while($img = mysqli_fetch_assoc($images_result)){
                                                $images[] = $img['image_url'];
                                            }
                                        }
                                        if(empty($images)) $images[] = $p['thumbnail'];
                                        $variant_map[$v['color']] = [ 'variant_id' => $v['id'], 'images' => $images ];
                                    }
                                }
                            }
                        }
                        ?>
                        <?php if(!empty($colors) && $home_model): ?>
                        <div class="color-swatches" data-product-id="<?= $p['id'] ?>">
                            <?php foreach($colors as $idx => $color): ?>
                                <div class="color-dot <?= $idx===0 ? 'active' : '' ?>"
                                     data-images='<?= json_encode($variant_map[$color]['images']) ?>'
                                     title="<?= htmlspecialchars($color) ?>"
                                     style="background-color: <?= $home_model->get_color_hex($color) ?>; border: <?= $color==='Trắng' ? '2px solid #ddd' : '1px solid transparent' ?>;"></div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <div class="fs-price">
                            <?php if(isset($p['is_sale']) && intval($p['is_sale']) === 1): ?>
                                <?= number_format($p['base_price']) ?>đ
                                <span class="fs-price-old"><?= number_format($p['base_price'] * 1.1) ?>đ</span>
                                <span class="fs-badge">-10%</span>
                            <?php else: ?>
                                <?= number_format($p['base_price']) ?>đ
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php
                    endwhile;
                else:
                ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #999;">
                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <p>Không tìm thấy sản phẩm nào</p>
                </div>
                <?php endif; ?>
            </div>
        </main>

    </div>
</div>

</body>
</html>