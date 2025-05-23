# Система электронных рецептов

## Описание
Этот проект представляет собой веб-приложение для врачей, позволяющее быстро и удобно выписывать электронные рецепты пациентам. Интерфейс выполнен на русском языке и ориентирован на медучреждения. Все данные о пациентах и лекарствах берутся из локальной базы данных PostgreSQL.

## Основные возможности
- **Авторизация сотрудников**  — вход по индивидуальному коду и паролю.
- **Поиск пациентов**  — автодополнение по фамилии, имени, отчеству или коду пациента.
- **Поиск и подбор лекарств**  — автодополнение по названию или действующему веществу, выбор формы выпуска.
- **Формирование рецепта**  — удобная форма для добавления нескольких лекарств, дозировок и инструкций.
- **Печать рецепта**  — генерация печатной формы рецепта для подписи и выдачи пациенту.
- **Безопасный выход**  — завершение сессии пользователя.

## Структура проекта
- `auth.php` — страница авторизации сотрудников.
- `patient_prescription.php` — основная страница выбора пациента и назначения лекарств.
- `patient_search.php` — API поиска пациентов по базе.
- `drug_search.php` — API поиска лекарств и форм выпуска.
- `drug_dosageform.php` — API получения форм выпуска для выбранного лекарства.
- `print_form.php` — страница для печати готового рецепта.
- `logout.php` — завершение сессии и выход пользователя.
- `recepts.docx` — пример шаблона рецепта для печати (Word).

## Требования
- **PHP** 7.4+
- **PostgreSQL** (используется база данных `Legacy`)
- **Веб-сервер** (Apache, Nginx или встроенный PHP)
- **Расширение PHP для PostgreSQL** (`php-pgsql`)
- **jQuery** (подключается локально)

## Быстрый старт
1. Скопируйте файлы проекта в директорию веб-сервера.
2. Проверьте параметры подключения к БД в каждом PHP-скрипте (host, dbname, user, password).
3. Убедитесь, что база данных PostgreSQL доступна и содержит необходимые таблицы (`m_employes`, `drugs`, `pat_ident` и др.).
4. Откройте в браузере страницу `auth.php` и выполните вход.

## Безопасность
- Все операции требуют авторизации.
- Пароли сотрудников хранятся в базе данных.
- Сессии очищаются при выходе.

