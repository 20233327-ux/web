# 🎬 PhimWeb - Website Xem Phim PHP

Web xem phim trực tuyến viết bằng PHP + MySQL với giao diện dark theme đẹp như Netflix.

---

## ✅ Tính năng

### Người dùng
- 🔐 Đăng ký / Đăng nhập / Đăng xuất (CSRF protected)
- 🎬 Xem phim online (video upload, YouTube, direct URL, embed code)
- 🔍 Tìm kiếm phim theo tên, đạo diễn, diễn viên
- 📂 Lọc phim theo thể loại
- ⭐ Đánh giá phim (1–10 sao)
- 💬 Bình luận phim
- 📜 Lịch sử xem phim
- 👤 Hồ sơ cá nhân + đổi mật khẩu

### Admin
- 📊 Dashboard với thống kê (phim, user, lượt xem, bình luận)
- 🎞️ Thêm phim: tải file MP4 **hoặc** gắn URL YouTube/link thẳng/embed code
- ✏️ Sửa phim, xóa phim (tự động xóa file)
- 🎬 Quản lý tập phim (episode) cho phim bộ
- ⭐ Đặt phim nổi bật (hiện ở banner trang chủ)
- 👁️ Bật/tắt hiển thị phim
- 👥 Quản lý người dùng (khóa/mở khóa, đổi role, xóa)
- 🏷️ Quản lý thể loại & quốc gia
- 💬 Kiểm duyệt bình luận (ẩn/hiện/xóa)

### Phân quyền
- `admin`: toàn quyền
- `editor`: quản lý phim, tập phim, thể loại/quốc gia
- `moderator`: quản lý bình luận, khóa/mở khóa user
- `user`: chỉ dùng tính năng người xem

---

## 🚀 Cài đặt

### Yêu cầu
- PHP 8.0+ với PDO, fileinfo
- MySQL 5.7+ hoặc MariaDB 10+
- Apache với mod_rewrite **hoặc** Nginx
- XAMPP / WAMP / Laragon (local) hoặc hosting PHP

### Bước 1: Đặt file lên server
Đưa thư mục `baitapphp` vào `htdocs` (XAMPP) hoặc `www` (WAMP/Laragon).

### Bước 2: Chạy trình cài đặt
Truy cập: `http://localhost/baitapphp/setup.php`

Điền thông tin:
- DB Host, User, Pass, Name
- Tài khoản admin (username + email + password)

Nhấn **Cài đặt ngay** → hệ thống tự tạo database và tài khoản admin.

> ⚠️ **Xóa file `setup.php` sau khi cài đặt xong!**

### Bước 3: Đăng nhập
Truy cập: `http://localhost/baitapphp/login.php`
Đăng nhập bằng tài khoản admin vừa tạo.

### Nâng cấp bản cũ lên bản mới
Nếu bạn đã cài bản trước đó, chạy file `upgrade_v2.sql` để cập nhật role + bảng episode.

---

## 🌐 Deploy lên Hosting

1. Upload toàn bộ file lên hosting (thông qua FTP/cPanel File Manager)
2. Tạo database MySQL trên cPanel, nhập file `database.sql`
3. Cập nhật `config/database.php` với thông tin DB của hosting
4. Kiểm tra `BASE_URL` trong `config/database.php` (để trống nếu ở root domain; hoặc `/ten-thu-muc` nếu trong subfolder)
5. Đảm bảo thư mục `uploads/movies/` và `uploads/thumbnails/` có quyền ghi (chmod 755 hoặc 775)

---

## 📁 Cấu trúc thư mục

```
baitapphp/
├── admin/              ← Trang quản trị
│   ├── index.php       ← Dashboard
│   ├── movies.php      ← Danh sách phim
│   ├── add_movie.php   ← Thêm phim (upload MP4 / URL)
│   ├── edit_movie.php  ← Sửa phim
│   ├── users.php       ← Quản lý user
│   ├── categories.php  ← Thể loại & quốc gia
│   └── comments.php    ← Kiểm duyệt bình luận
├── assets/
│   ├── css/style.css   ← Dark theme
│   ├── css/admin.css   ← Admin CSS
│   └── js/main.js      ← JavaScript
├── config/
│   └── database.php    ← ⚙️ Cấu hình DB (sửa file này)
├── includes/           ← Thư viện shared
│   ├── auth.php        ← Login/logout/CSRF
│   ├── functions.php   ← Helper functions
│   ├── header.php      ← HTML header + navbar
│   ├── footer.php      ← HTML footer
│   └── movie_card.php  ← Component card phim
├── uploads/
│   ├── movies/         ← File MP4 tải lên
│   └── thumbnails/     ← Ảnh bìa tải lên
├── index.php           ← Trang chủ
├── login.php           ← Đăng nhập
├── register.php        ← Đăng ký
├── movie.php           ← Chi tiết phim
├── watch.php           ← Xem phim (Plyr.js player)
├── stream.php          ← Stream MP4 với HTTP Range
├── search.php          ← Tìm kiếm
├── genre.php           ← Phim theo thể loại
├── profile.php         ← Hồ sơ người dùng
├── setup.php           ← 🔧 Trình cài đặt (xóa sau khi dùng)
├── database.sql        ← Schema CSDL
└── .htaccess           ← Cấu hình Apache
```

---

## 🔐 Bảo mật

- Mật khẩu hash bcrypt
- Chống SQL injection (PDO prepared statements)
- Chống XSS (htmlspecialchars)
- CSRF token cho mọi form POST
- Kiểm tra MIME type thực khi upload file
- Upload video chỉ phục vụ qua `stream.php` (không truy cập trực tiếp)
- Giới hạn upload: 2GB video, 10MB ảnh

### Checklist production
- Đặt `APP_ENV = 'production'` trong `config/database.php`
- Đặt `FORCE_HTTPS = true` khi server có SSL
- Tắt `display_errors` trên production
- Xóa `setup.php` sau khi cài đặt
- Không public file SQL (`database.sql`, `upgrade_v2.sql`)
- Đổi mật khẩu admin mặc định

## 💾 Backup database

### Windows (PowerShell)
```powershell
.\scripts\backup_db.ps1 -Host localhost -Port 3306 -User root -Database phimweb -OutputDir .\backups
```

### Linux/macOS
```bash
bash scripts/backup_db.sh localhost 3306 root phimweb ./backups
```

---

## 💡 Ghi chú thêm

- **Video Type "File"**: Upload MP4 lên server, phát qua `stream.php` với HTTP Range (hỗ trợ tua)
- **Video Type "YouTube"**: Chỉ cần paste URL YouTube → tự nhúng iframe
- **Video Type "URL"**: Link thẳng đến file .mp4 bất kỳ
- **Video Type "Embed"**: Paste code `<iframe>` từ bất kỳ nền tảng nào
