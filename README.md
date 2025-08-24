# Uzman Psikoloji Merkezi Web Sitesi

Modern ve kullanıcı dostu bir psikoloji merkezi web sitesi. PHP, MySQL ve JavaScript kullanılarak geliştirilmiştir.

## 🚀 Özellikler

### Kullanıcı Arayüzü
- **Responsive Design**: Tüm cihazlarda mükemmel görünüm
- **Modern UI/UX**: Kullanıcı dostu arayüz tasarımı
- **Blog Sistemi**: SEO dostu blog yazıları
- **Randevu Sistemi**: Online randevu alma
- **İletişim Formu**: Mesaj gönderme sistemi

### Admin Paneli
- **Dashboard**: İstatistikler ve genel bakış
- **Randevu Yönetimi**: Randevu onaylama/reddetme
- **Mesaj Yönetimi**: Gelen mesajları okuma ve cevaplama
- **Blog Yönetimi**: Blog yazıları ekleme/düzenleme/silme
- **Beslenme & Diyetetik**: İçerik yönetimi
- **Danışan Yönetimi**: Hasta kayıtları

### Teknik Özellikler
- **Güvenlik**: CSRF koruması, SQL injection önleme
- **Mail Sistemi**: PHPMailer ile SMTP entegrasyonu
- **Veritabanı**: MySQL ile ilişkisel veritabanı
- **Performance**: Optimized queries ve lazy loading

## 🛠️ Teknolojiler

- **Backend**: PHP 8.x, MySQL
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Mail**: PHPMailer
- **Database**: MySQL 8.0
- **Design**: Responsive, Mobile-First

## 📋 Kurulum

### Gereksinimler
- PHP 8.0 veya üzeri
- MySQL 8.0 veya üzeri
- Apache/Nginx web sunucusu
- Composer (PHPMailer için)

### Adımlar

1. **Projeyi klonlayın**
```bash
git clone https://github.com/kullaniciadi/psikolog-sitesi.git
cd psikolog-sitesi
```

2. **Veritabanını kurun**
```bash
# MySQL'de veritabanı oluşturun
mysql -u root -p
CREATE DATABASE psikolog_merkezi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit

# Veritabanı şemasını import edin
mysql -u root -p psikolog_merkezi < database_setup.sql
```

3. **Konfigürasyon dosyasını düzenleyin**
```bash
# includes/config.php dosyasını kopyalayın
cp includes/config.example.php includes/config.php

# Veritabanı bilgilerini düzenleyin
# Mail ayarlarını yapılandırın
```

4. **Dosya izinlerini ayarlayın**
```bash
chmod 755 uploads/
chmod 644 includes/config.php
```

5. **Web sunucusunu yapılandırın**
- Apache/Nginx'te virtual host oluşturun
- Document root'u proje klasörüne yönlendirin

## 🔧 Konfigürasyon

### Veritabanı Ayarları
`includes/config.php` dosyasında:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'psikolog_merkezi');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Mail Ayarları
```php
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
define('MAIL_FROM', 'your-email@gmail.com');
define('MAIL_FROM_NAME', 'Uzman Psikoloji Merkezi');
```

## 📁 Proje Yapısı

```
psikolog-sitesi/
├── admin/                 # Admin paneli dosyaları
├── css/                   # Stil dosyaları
├── img/                   # Görseller
├── includes/              # PHP include dosyaları
├── js/                    # JavaScript dosyaları
├── uploads/               # Yüklenen dosyalar
├── PHPMailer-master/      # Mail kütüphanesi
├── index.php              # Ana sayfa
├── database_setup.sql     # Veritabanı şeması
└── README.md              # Bu dosya
```

## 🔐 Güvenlik

- CSRF token koruması
- SQL injection önleme (Prepared Statements)
- XSS koruması
- Input validation ve sanitization
- Session güvenliği

## 📧 Mail Sistemi

PHPMailer kullanılarak SMTP üzerinden mail gönderimi:
- Randevu durumu bildirimleri
- Admin mesaj cevapları
- Sistem bildirimleri

## 🚀 Deployment

### Production Ortamı
1. Veritabanı yedekleme
2. Dosya yedekleme
3. SSL sertifikası kurulumu
4. Performance optimizasyonu
5. Error logging yapılandırması

## 🤝 Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/AmazingFeature`)
3. Değişikliklerinizi commit edin (`git commit -m 'Add some AmazingFeature'`)
4. Branch'inizi push edin (`git push origin feature/AmazingFeature`)
5. Pull Request oluşturun

## 📄 Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## 📞 İletişim

Proje Sahibi - [@kullaniciadi](https://github.com/kullaniciadi)

Proje Linki: [https://github.com/kullaniciadi/psikolog-sitesi](https://github.com/kullaniciadi/psikolog-sitesi) 