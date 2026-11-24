# Cveta Cvetić: WordPress-версия (финальная копия, 24.11.2026)

В этой папке хранится то, на чём сейчас работает живой сайт **https://cvetacvetic.rs/**: тема WordPress, вспомогательные плагины и скрипты для локального запуска.

Статические HTML-файлы в корне репозитория — первая демо-версия дизайна. Публикация через GitHub Pages выключена 24.11.2026 (адрес vikbal25.github.io/cveta-cvetic больше не открывается); файлы оставлены как архив.

Документация проекта в Figma: макеты, дизайн-система, заказ и доставка, письма, панель владельца, техника.
https://www.figma.com/design/ud5zSPr7O5SAWMhYZ3KGf5

## Что где лежит

| Папка или файл | Что это |
|---|---|
| `theme/cveta-cvetic/` | Тема сайта, исходники |
| `theme/cveta-cvetic.zip` | Та же тема архивом для загрузки в WordPress |
| `helper-plugins/cc-odrzavanje` | Страница «сайт на обслуживании» (503) для посетителей во время работ |
| `helper-plugins/cc-podesavanje` | Одноразовая настройка: страницы, опции, главная страница, адреса korpa / porucivanje / moj-nalog |
| `helper-plugins/cc-sitemap-fix` | Одноразовая замена старого sitemap.xml с резервной копией и правка robots.txt |
| `local-dev/` | Запуск копии сайта на своём компьютере (PHP + MariaDB + WP-CLI) и импорт данных |

## Файлы темы

| Файл | За что отвечает |
|---|---|
| `inc/helpers.php` | Контакты магазина: телефон, адрес, e-mail, соцсети, PIB/MB (функция `cc_shop()`) |
| `inc/delivery.php` | Зоны и цены доставки, пороги бесплатной доставки |
| `inc/checkout.php` | Поля заказа, проверки, страна Сербия по умолчанию, блок «Dostava» в заказе и письмах |
| `inc/emails.php`, `woocommerce/emails/email-header.php` | Оформление писем, адрес для ответов покупателей |
| `inc/panel.php`, `assets/panel/` | Панель владельца по секретному адресу (адрес хранится в базе, опция `cc_panel_slug`) |
| `inc/admin-prices.php` | «Proizvodi → Brze cene» в админке |
| `inc/redirects.php` | Перенаправления со старых адресов (cart → korpa и т.д.) |
| `inc/sitemap.php` | Карта сайта для Google без служебных страниц и списка пользователей |
| `inc/strings.php` | Сербские тексты WooCommerce на сайте |
| `woocommerce/` | Шаблоны каталога, корзины, оформления заказа, страницы «Hvala» |

## Доставка

| Зона | Доставка | Бесплатно от |
|---|---|---|
| Novi Sad, левый берег Дуная | 500 RSD | 3.000 RSD |
| Petrovaradin и Sremska Kamenica | 700 RSD | 3.100 RSD |
| Пригороды (Veternik, Futog, Kać, S. Karlovci…) | по договорённости | — |
| Самовывоз из магазина | 0 RSD | — |

Цены меняются в `theme/cveta-cvetic/inc/delivery.php`.

## Письма

- Отправитель: `info@cvetacvetic.rs`. Это нужно только для доставки писем: Gmail не принимает письма «от gmail.com», отправленные сервером сайта.
- Ответы покупателей, новые заказы и почта администратора идут на `tomatasovac@gmail.com`.
- Отправкой занимается плагин WP Mail SMTP.

## Как обновить тему на живом сайте

1. Соберите архив: из папки `theme` выполните `zip -r cveta-cvetic.zip cveta-cvetic -x '*.DS_Store'`.
2. В админке: **Izgled → Teme → Dodaj → Učitaj temu**, выберите архив, затем **«Zameni postojeću»**.
3. Перед крупными изменениями сделайте резервную копию в Duplicator Pro.

## Версии на живом сайте

- WordPress 7.1, WooCommerce 11.3.1. Заказы хранятся как записи (HPOS выключен).
- PHP 7.4 на хостинге. Тема с ним работает, но переход на PHP 8.3 нужно сделать в течение 1–3 месяцев.
- Активные плагины: WooCommerce, Wooplatnica 1.0, WP Mail SMTP, Site Kit by Google, Duplicator Pro, Two Factor, Disable Comments, Duplicate Page, Loco Translate.

## Чего здесь нет (специально)

Резервные копии сайта, база данных, пароли и локальные настройки (`wp-local/`, `backups/`, `WP-LOCAL.md`) хранятся только на компьютере разработчика. В публичный репозиторий они не попадают.
