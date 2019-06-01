# oc-seeder
Плагин для October CMS, позволяющий синхронизировать справочные-единые данные между разработчиками.
После установки плагина нужно выбрать модели, которые нужно сохранить: 
```
/backend/hookprod/seeder/models
```
Перед коммитом в git нужно создать сиды
```
php artisan seed:generate
php artisan queue:work
```
После того, как прошёл git pull, нужно обновить данные в таблицах:
```
php artisan seed:set
```

В .gitignore нужно добавить исключение для папки /storage/app/seeder/*
