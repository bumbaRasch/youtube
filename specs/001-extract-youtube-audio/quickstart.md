# quickstart.md

## Developer quickstart (MVP)

1. Install system dependencies (Linux):

```bash
sudo apt update
sudo apt install -y ffmpeg python3-pip
pip3 install yt-dlp
```

2. Install PHP deps and run dev server:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

3. Run tests (CI will run these):

```bash
./vendor/bin/pest
```

4. CI smoke checks should verify `yt-dlp --version` and `ffmpeg -version`. Example script is `scripts/ci/check-binaries.sh`.

5. API route for the extractor is registered in `routes/api.php` as POST `/api/extract` (the route is mounted under the `/api` prefix by Laravel routing conventions).
