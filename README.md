1. Зайти в Google Cloud Console и создать проект.
2. Включить Sheets API.
3. Создать OAuth2 Client ID для веб-приложения, обязательно указать в redirect url: http://localhost
4. Скачать OAuth json и поместить в корень с названием credentials.json.
5. Запустить в корне: `composer install`
6. Запустить в папке src: `php -S localhost:80`
7. Открыть в браузере http://localhost
8. Перекинет на авторизацию в Google аккаунте. Предоставить доступ.
9. Перекинет обратно. Ввести название таблицы, нажать кнопку.
10. При успешной отправке страница обновится без ошибок и таблица создастся.
