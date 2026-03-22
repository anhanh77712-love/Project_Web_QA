# Sử dụng môi trường PHP 8.2 có sẵn máy chủ Apache
FROM php:8.2-apache

# Cài đặt công cụ kết nối Database
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Bật URL Rewrite cho mô hình MVC
RUN a2enmod rewrite

# Cấu hình thư mục làm việc
WORKDIR /var/www/html

# Copy toàn bộ code vào trong thư mục web_qlsp
COPY . /var/www/html/web_qlsp/

# Tự động chuyển hướng từ trang chủ vào /web_qlsp/
RUN echo "RedirectMatch ^/$ /web_qlsp/" > /etc/apache2/conf-available/redirect.conf \
    && a2enconf redirect

# Cấp quyền để PHP có thể xử lý file
RUN chown -R www-data:www-data /var/www/html/web_qlsp/
RUN chmod -R 775 /var/www/html/web_qlsp/

# Mở cổng 80 cho web hoạt động
EXPOSE 80