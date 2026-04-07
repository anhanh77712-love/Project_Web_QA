<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Danh sách sản phẩm</title>

<style>
.shop-page { background: #ffffff; }
.shop-page * { box-sizing: border-box; }
.shop-container { display: flex; max-width: 1200px; margin: 0 auto; gap: 20px; }
.shop-sidebar { width: 250px; background: #fff; padding: 16px; border-radius: 10px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); position: sticky; top: 20px; align-self: flex-start; max-height: calc(100vh - 40px); overflow-y: auto; }
.shop-filter { margin-bottom: 18px; border-bottom: 1px solid #f0f0f0; padding-bottom: 15px; }
.shop-filter:last-child { border-bottom: none; }
.shop-sidebar__title { font-size: 18px; font-weight: bold; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 8px; }
.shop-filter__header { font-weight: 600; color: #333; display: flex; justify-content: space-between; align-items: center; cursor: pointer; background: transparent; border: 0; padding: 0; width: 100%; font: inherit; margin-bottom: 10px; }
.shop-filter__arrow { font-size: 14px; color: #666; transition: transform .2s ease; }
.shop-filter__content { display: none; }
.shop-filter.open .shop-filter__content { display: block; }
.shop-filter.open .shop-filter__arrow { transform: rotate(180deg); }
.shop-filter__item { display: flex; align-items: center; gap: 8px; font-size: 14px; padding: 6px 8px; border-radius: 6px; cursor: pointer; transition: 0.2s; }
.shop-filter__item:hover { background: #f2f2f2; }
.shop-filter__item input { accent-color: #e74c3c; }

.shop-products { flex: 1; }
.shop-topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.shop-topbar__count { font-size: 14px; color: #555; }
.shop-topbar__sort { padding: 6px 10px; border-radius: 6px; border: 1px solid #ddd; outline: none; }

.shop-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; }
.fs-card { background: transparent; border: 0; }
.fs-img-wrap { background: #f9f9f9; border-radius: 8px; overflow: hidden; height: 300px; margin-bottom: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); border: 1px solid #eee; }
.fs-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease; display:block; mix-blend-mode: multiply; }
.fs-card:hover .fs-img-wrap img { transform: scale(1.04); }
.fs-title { font-size: 14px; font-weight: 500; color: #333; margin-bottom: 8px; text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.fs-price { font-size: 14px; font-weight: 700; color: #e74c3c; }
.fs-price-old { color: #999; font-weight: 400; font-size: 13px; margin-left: 8px; text-decoration: line-through; }
.fs-badge { background: #e74c3c; color: #fff; font-size: 12px; padding: 2px 8px; border-radius: 999px; margin-left: 8px; }

.color-swatches{ display:flex; gap:6px; margin:6px 0 8px; }
.color-dot{ width:18px; height:18px; border-radius:50%; border:1px solid #ddd; position:relative; cursor:pointer; }
.color-dot.active::after{ content:''; position:absolute; top:-3px; left:-3px; right:-3px; bottom:-3px; border:1px solid #999; border-radius:50%; }

.skeleton { background: #e2e8f0; animation: pulse 1.5s infinite; border-radius: 8px; }
@keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }

@media (max-width: 1024px) { .shop-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px) { .shop-container { flex-direction: column; } .shop-sidebar { width: 100%; position: static; max-height: none; } .shop-grid { grid-template-columns: repeat(2, 1fr); } }
</style>
</head>

<body>

<div class="shop-page">
    <div class="shop-container">

        <aside class="shop-sidebar">
            <div class="shop-sidebar__title">Bộ lọc</div>

            <div class="shop-filter open" id="filter-category">
                <button type="button" class="shop-filter__header" onclick="toggleFilter('filter-category')">
                    <span>Danh mục</span><span class="shop-filter__arrow">▾</span>
                </button>
                <div class="shop-filter__content">
                    <label class="shop-filter__item">
                        <input type="radio" name="filter_category" value="" onchange="updateFilters()" checked> Tất cả
                    </label>
                    <?php if(isset($data['categories']) && mysqli_num_rows($data['categories']) > 0): ?>
                        <?php while($cat = mysqli_fetch_assoc($data['categories'])): ?>
                        <label class="shop-filter__item">
                            <input type="radio" name="filter_category" value="<?= htmlspecialchars($cat['slug']) ?>" onchange="updateFilters()">
                            <?= htmlspecialchars($cat['name']) ?>
                        </label>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="shop-filter open" id="filter-price">
                <button type="button" class="shop-filter__header" onclick="toggleFilter('filter-price')">
                    <span>Giá</span><span class="shop-filter__arrow">▾</span>
                </button>
                <div class="shop-filter__content">
                    <label class="shop-filter__item">
                        <input type="radio" name="filter_price" value="" onchange="updateFilters()" checked> Tất cả giá
                    </label>
                    <label class="shop-filter__item"><input type="radio" name="filter_price" value="0-500000" onchange="updateFilters()"> Dưới 500K</label>
                    <label class="shop-filter__item"><input type="radio" name="filter_price" value="500000-1000000" onchange="updateFilters()"> 500K - 1tr</label>
                    <label class="shop-filter__item"><input type="radio" name="filter_price" value="1000000-999999999" onchange="updateFilters()"> Trên 1tr</label>
                </div>
            </div>
        </aside>

        <main class="shop-products">
            
            <div id="dynamic-banner-container"></div>

            <div class="shop-topbar">
                <div class="shop-topbar__count"><span id="product-count">0</span> kết quả</div>
                <select class="shop-topbar__sort" id="sortSelect" onchange="updateFilters()">
                    <option value="default">Mặc định</option>
                    <option value="price_asc">Giá tăng dần</option>
                    <option value="price_desc">Giá giảm dần</option>
                </select>
            </div>

            <div class="shop-grid" id="loading-skeleton">
                <?php for($i=0; $i<8; $i++): ?>
                <div class="fs-card">
                    <div class="skeleton" style="height: 300px; margin-bottom: 12px;"></div>
                    <div class="skeleton" style="height: 16px; width: 80%; margin-bottom: 8px;"></div>
                    <div class="skeleton" style="height: 16px; width: 50%;"></div>
                </div>
                <?php endfor; ?>
            </div>

            <div class="shop-grid" id="product-grid" style="display: none;"></div>

        </main>

    </div>
</div>

<script>
    function toggleFilter(filterId) {
        var section = document.getElementById(filterId);
        if(section) section.classList.toggle('open');
    }

    function syncUIFromUrl() {
        const params = new URLSearchParams(window.location.search);
        
        const cat = params.get('category');
        if (cat) {
            const el = document.querySelector(`input[name="filter_category"][value="${cat}"]`);
            if(el) el.checked = true;
        }

        const price = params.get('price');
        if (price) {
            const el = document.querySelector(`input[name="filter_price"][value="${price}"]`);
            if(el) el.checked = true;
        }

        const sort = params.get('sort');
        if (sort) {
            document.getElementById('sortSelect').value = sort;
        }
    }

    function updateFilters() {
        const catRadio = document.querySelector('input[name="filter_category"]:checked');
        const priceRadio = document.querySelector('input[name="filter_price"]:checked');
        const sortSelect = document.getElementById('sortSelect');

        const params = new URLSearchParams(window.location.search); 
        
        if (catRadio && catRadio.value) params.set('category', catRadio.value);
        else params.delete('category');

        if (priceRadio && priceRadio.value) params.set('price', priceRadio.value);
        else params.delete('price');

        if (sortSelect && sortSelect.value !== 'default') params.set('sort', sortSelect.value);
        else params.delete('sort');

        const newUrl = window.location.pathname + '?' + params.toString();
        window.history.pushState({path: newUrl}, '', newUrl);

        loadProducts();
    }

    window.addEventListener('popstate', function() {
        syncUIFromUrl();
        loadProducts();
    });

    function loadProducts() {
        document.getElementById('loading-skeleton').style.display = 'grid';
        document.getElementById('product-grid').style.display = 'none';

        // ĐÃ SỬA LỖI ĐƯỜNG DẪN API TẠI ĐÂY (Xóa chữ /Customer/)
        const apiUrl = '/web_qlsp/product_list_customer/api_get_data' + window.location.search;

        fetch(apiUrl)
            .then(async res => {
                if (!res.ok) throw new Error("Mã lỗi máy chủ: " + res.status);
                const text = await res.text();
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("Dữ liệu lỗi từ PHP:", text);
                    throw new Error("Dữ liệu trả về bị hỏng hoặc chứa mã lỗi PHP. Hãy ấn F12 để xem chi tiết.");
                }
            })
            .then(data => {
                if (data.success) {
                    renderDynamicBanner(data);
                    renderProductGrid(data.products);
                    document.getElementById('product-count').textContent = data.count;
                } else {
                    Swal.fire('Lỗi truy xuất', data.message || 'Lỗi không xác định', 'error');
                    document.getElementById('loading-skeleton').style.display = 'none';
                    document.getElementById('product-grid').innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #e74c3c;"><h5>Hệ thống gặp sự cố</h5><p>${data.message}</p></div>`;
                    document.getElementById('product-grid').style.display = 'grid';
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('loading-skeleton').style.display = 'none';
                document.getElementById('product-grid').innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #e74c3c;"><h5>Lỗi kết nối</h5><p>${err.message}</p></div>`;
                document.getElementById('product-grid').style.display = 'grid';
            });
    }

    function renderDynamicBanner(data) {
        const container = document.getElementById('dynamic-banner-container');
        let html = '';
        
        if (data.current_filter === 'new') {
            html = `<div style="background: #e8f4fd; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #2f5acf;">
                        <div style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 5px;">
                            <i class="fas fa-sparkles" style="color: #2f5acf;"></i> <span style="color: #2f5acf;">SẢN PHẨM MỚI</span>
                        </div>
                        <div style="font-size: 14px; color: #666;">Hiển thị <strong>${data.count}</strong> sản phẩm mới nhất</div>
                    </div>`;
        } else if (data.current_filter === 'sale') {
            html = `<div style="background: #fff2f2; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #e74c3c;">
                        <div style="font-size: 16px; font-weight: 700; color: #e74c3c; margin-bottom: 5px;">
                            <i class="fas fa-bolt"></i> FLASH SALE
                        </div>
                        <div style="font-size: 14px; color: #666;">Hiển thị <strong>${data.count}</strong> sản phẩm đang giảm giá</div>
                    </div>`;
        } else if (data.current_filter === 'bestseller') {
            html = `<div style="background: #fff8e1; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #ffa726;">
                        <div style="font-size: 16px; font-weight: 700; color: #f57c00; margin-bottom: 5px;">
                            <i class="fas fa-fire"></i> SẢN PHẨM BÁN CHẠY
                        </div>
                        <div style="font-size: 14px; color: #666;">Hiển thị <strong>${data.count}</strong> sản phẩm được mua nhiều nhất</div>
                    </div>`;
        } else if (data.search_keyword) {
            html = `<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #2f5acf;">
                        <div style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 5px;">
                            Kết quả tìm kiếm cho: "${data.search_keyword}"
                        </div>
                        <div style="font-size: 14px; color: #666;">Tìm thấy <strong>${data.count}</strong> sản phẩm</div>
                    </div>`;
        } else {
            html = `<div style="background: #f0f7ff; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #5a9fd4;">
                        <div style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 5px;">
                            <i class="fas fa-th-large" style="color: #5a9fd4;"></i> <span style="color: #5a9fd4;">TẤT CẢ SẢN PHẨM</span>
                        </div>
                    </div>`;
        }
        container.innerHTML = html;
    }

    function renderProductGrid(products) {
        const grid = document.getElementById('product-grid');
        let html = '';

        if (products && products.length > 0) {
            products.forEach(p => {
                let swatchesHtml = '';
                if (p.colors && p.colors.length > 0) {
                    swatchesHtml = `<div class="color-swatches" data-product-id="${p.id}">`;
                    p.colors.forEach((color, idx) => {
                        const vData = p.variant_map[color];
                        const active = idx === 0 ? 'active' : '';
                        const border = color === 'Trắng' ? '2px solid #ddd' : '1px solid transparent';
                        swatchesHtml += `<div class="color-dot ${active}" data-color="${color}" data-images='${JSON.stringify(vData.images)}' style="background-color: ${vData.hex}; border: ${border};" title="${color}"></div>`;
                    });
                    swatchesHtml += `</div>`;
                }

                const priceHtml = p.is_sale == 1 
                    ? `${Number(p.base_price).toLocaleString('vi-VN')}đ <span class="fs-price-old">${Number(p.base_price * 1.1).toLocaleString('vi-VN')}đ</span> <span class="fs-badge">-10%</span>`
                    : `${Number(p.base_price).toLocaleString('vi-VN')}đ`;

                // ĐÃ SỬA LỖI ĐƯỜNG DẪN CHI TIẾT SẢN PHẨM TẠI ĐÂY (Xóa chữ /Customer/)
                html += `
                <div class="fs-card">
                    <div class="fs-img-wrap">
                        <a href="/web_qlsp/product_detail?slug=${encodeURIComponent(p.slug)}">
                            <img src="/web_qlsp/Public/Picture/${p.thumbnail}" class="product-image" data-product-id="${p.id}" alt="${p.name}" onerror="this.src='https://via.placeholder.com/300x380'">
                        </a>
                    </div>
                    <div>
                        <a href="/web_qlsp/product_detail?slug=${encodeURIComponent(p.slug)}" class="fs-title">${p.name}</a>
                        ${swatchesHtml}
                        <div class="fs-price">${priceHtml}</div>
                    </div>
                </div>`;
            });
        } else {
            html = `<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #999;">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <p>Không tìm thấy sản phẩm nào</p>
                    </div>`;
        }

        grid.innerHTML = html;
        document.getElementById('loading-skeleton').style.display = 'none';
        grid.style.display = 'grid';

        attachColorEvents();
    }

    function attachColorEvents() {
        document.querySelectorAll('.color-dot').forEach(dot => {
            dot.addEventListener('click', function(e) {
                e.preventDefault();
                const swatches = this.closest('.color-swatches');
                const pid = swatches.getAttribute('data-product-id');
                const imagesStr = this.getAttribute('data-images');
                
                if (!imagesStr) return;
                const images = JSON.parse(imagesStr);
                
                swatches.querySelectorAll('.color-dot').forEach(d => d.classList.remove('active'));
                this.classList.add('active');
                
                if (images && images.length > 0) {
                    const imgEl = document.querySelector(`img.product-image[data-product-id="${pid}"]`);
                    if (imgEl) {
                        imgEl.style.opacity = 0.5;
                        setTimeout(() => {
                            imgEl.src = '/web_qlsp/Public/Picture/' + images[0];
                            imgEl.style.opacity = 1;
                        }, 100);
                    }
                }
            });
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        syncUIFromUrl();
        loadProducts();
    });

</script>

</body>
</html>