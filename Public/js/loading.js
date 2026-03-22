    // Xử lý ẩn loading khi trang đã tải xong
    window.addEventListener('load', function() {
        // Có thể dùng setTimeout để hiệu ứng hiển thị rõ hơn (ví dụ 500ms)
        setTimeout(() => {
            const skeleton = document.getElementById('loading-skeleton');
            const content = document.getElementById('actual-content');
            if(skeleton) skeleton.style.display = 'none';
            if(content) content.style.display = 'table-row-group';
        }, 400);
    });