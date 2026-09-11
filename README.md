# SIMASET - Sistem Informasi Manajemen Aset & Inventaris

SIMASET adalah aplikasi manajemen inventaris berbasis web yang dirancang untuk mengelola data perangkat, brand, tipe perangkat, dan pengguna secara terstruktur. Repository ini terintegrasi dengan alur **Automated CI/CD Deployment** dari lingkungan lokal ke *remote private server* (LAN) menggunakan GitHub dan Cron Job, serta didukung strategi sinkronisasi database diferensial berbasis *timestamp*.

---

## 🛠️ Arsitektur & Teknologi

* **Bahasa Pemrograman:** PHP
* **Database:** MySQL / MariaDB
* **Version Control:** Git & GitHub
* **Automation & Sync:** Crontab (Server-side auto-pull)
* **Keamanan:** SSH, GitHub Personal Access Token (PAT)

---

## 🚀 Alur Sinkronisasi Otomatis

Project ini menggunakan skema sinkronisasi dua arah untuk file dan database tanpa memerlukan IP Publik atau NAT Port Forwarding:
