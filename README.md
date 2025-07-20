<p align="center">
    <h1 align="center">❤️Laravel API🚀 Sustainable-Trades❤️</h1>
</p>

## Introduction 😍

<p> A Sustainable-Trades with an awesome admin panel setup, user login & logout, registration, status, delete, profile settings and system information, and many more. </p>

## Contributor 😎

-   <a href="https://github.com/mizanurmillon" target="_blank">Md Mizanur Rahman</a>

## Installation 🤷‍♂

To Install & Run This Project You Have To Follow the following Steps:

```sh
git clone https://github.com/mizanurmillon/Sustainable-Trades.git
```

```sh
cd sustainable_trades
```

```sh
composer update
```

Open your `.env` file and change the database name (`DB_DATABASE`) to whatever you have, the username (`DB_USERNAME`) and password (`DB_PASSWORD`) field correspond to your configuration

```sh
php artisan key:generate
```

```sh
php artisan migrate:fresh --seed
```

```sh
php artisan optimize:clear
```

```sh
php artisan serve
```
For Admin Login `http://127.0.0.1:8000/admin` <br>
Admin gmail = `admin@admin.com` & password = `12345678`

For User Login `http://127.0.0.1:8000/admin` <br>
Admin gmail = `user@user.com` & password = `12345678`
