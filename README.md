# 🚀 Laravel Test App

---

## 📦 Шаг 1:

```bash
docker-compose up -d --build
```

## 📦 Шаг 2:

```bash
docker exec -it changebox-php-fpm composer install 
```

## 📦 Шаг 3:

```bash
cp .env.example .env
```

## 📦 Шаг 4:

```bash
docker exec -it changebox-php-fpm php artisan migrate
```
## 📦 Шаг 5:

```bash
docker exec -it changebox-php-fpm php artisan test
```
