<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## RULES/Cara menggunakan project ini :

1. Clone proyek ini ke dalam lokal Anda.
2. Setelah proses cloning selesai, buka terminal atau git bash di direktori root proyek Anda.
3. Jalankan perintah composer install untuk menginstal semua dependensi proyek.
4. Setelah proses instalasi selesai, buat file .env di root proyek Anda. Anda juga dapat meng-clone file env.example dan mengubah namanya menjadi .env.
5. Di dalam file .env, ganti nama database menjadi "point_of_sales" atau bebas.
6. Selanjutnya, jalankan perintah php artisan key:generate di terminal atau git bash untuk menghasilkan kunci aplikasi.
7. Setelah itu, jalankan perintah php artisan migrate --seed untuk menjalankan migrasi database dan seeding data.
8. Setelah proses tersebut selesai, Anda dapat menjalankan proyek dengan mengetikkan perintah php artisan serve di terminal atau git bash.
9. Sekarang proyek sudah siap untuk digunakan!
10. Untuk mengakses proyek anda, anda bisa mengetikan di url browser 127.0.0.1:8000

## Rules Agar Bisa Menggunakan Payemnt Gateway Xendit
1. buat akun xendit anda lalu isi semua data yang di perlukan
2. ketika semua data sudah dimasukan masuk kedalam dashboard xendit dan ubah menjadi testing mode
3. buka menu pengaturan
4. lalu buka menu api Keys
5. setelah api key selesa di generate lalu letakan di bagian .env untuk format peletakan envnya silahkan buka folder evv-project
6. letakan di file.env untuk api key nya di : XENDIT_APP_KEY,dan xendit urlnya di : XENDIT_URL
7. selesa.

## Pengunaan Xendit Menggunakan Ngrok sebagai server web hook
1. buka xendit dashboard
2. buka sidebar dengan menu pengaturan
3. lalu liat di bagian developer lalu klik webhooks
4. lalu cari menu " URL WEBHOOK dan letakan url dari ngrok dengan format "urlngrok/api/callback"
5. contoh "https://6df4-180-252-170-38.ngrok-free.app/api/callback"
6. lalu simpan dan liat di terminal ngroknya harusnya 500 server error
7. selesai pembayaran payment gateway bisa dilakukan
   
   

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# PointOfSales
