# php-realtypult-partner-lib [![Build Status](https://travis-ci.org/bushev/php-realtypult-partner-lib.svg?branch=master)](https://travis-ci.org/bushev/php-realtypult-partner-lib)

API библиотека для интеграции с RealtyPult. Подключении портала недвижимости к [партнерской программе](https://realtypult.ru/partner/signup).

## Нужна версия для NodeJS?

[https://github.com/bushev/nodejs-realtypult-partner-lib](https://github.com/bushev/nodejs-realtypult-partner-lib)

## Как установить

Вы можете скачать последнюю версию библиотеки по ссылке:

[https://github.com/bushev/php-realtypult-partner-lib/archive/master.zip](https://github.com/bushev/php-realtypult-partner-lib/archive/master.zip)

Скопируйте файл `realtypult-importer.php` в ваш PHP проект и подключите его в коде:

``` php
require_once 'realtypult-importer.php';
```

Если вы используете Composer для управления зависимостями проекта, вы можете установить библиотеку для RealtyPult напрямую из репозитория.

Обновите ваш файл composer.json (добавьте новую зависимость):

``` json
{
  "require": {
    "bushev/php-realtypult-partner-lib": "^1.0"
  }
}
```

После чего выполните установку:

`php composer.phar install`

## Как пользоваться

``` php
require_once 'realtypult-importer.php';

/**
 * Обработчик одного объявления
 *
 * Требуется реализовать алгоритм:
 *
 * 1. Проверить (по $item->id) размещали ли вы это объявления ранее
 *
 * Если не размещали:
 *      2.1 Сохраните объявление в вашей базе данных (не забудьте скачать себе изображения объекта)
 *      2.2 Верните объект (sdtClass) со ссылкой на объявление на вашем сайте
 *          Например:
 *              $result->url = 'http://your-site.ru/item-123';
 *              return $result;
 *
 *
 * Если размещали:
 *      2.1 Проверьте, изменилось ли обновление, если да то обновите его в вашей базе данных
 *          (не забудьте про изображения)
 *      2.2 Получите из вашей базы данных количество просмотров этого объявления (рекомендуется)
 *      2.2 Верните объект (sdtClass) со ссылкой на объявление на вашем сайте,
 *          и количеством просмотров (рекомендуется)
 *          Например:
 *              $result->url   = 'http://your-site.ru/item-123';
 *              $result->views = 15;
 *              return $result;
 *
 *
 * В случае если на каком-либо этапе вы понимаете что это объявление не может быть
 * обработано корректно, верните объект (sdtClass) с сообщением об ошибке
 * Например:
 *      $result->error = 'Произошел технический сбой';
 *      return $result;
 *
 *
 * Помните, что сообщение о об ошибке увидит конечный пользователь!
 *
 * Примеры частых ошибок:
 *
 * > Произошел технический сбой (проблема с базой данных, нет места на диске, и тп.)
 * > Не удалось определить адрес объекта
 * > И тп.
 *
 * В случае если в вашей базе данных есть очень похожее объявление, верните
 * объект (sdtClass) со ссылкой на это объявление (размещать это объявление не нужно)
 * Например:
 *      $result->similarUrl = 'http://your-site.ru/item2';
 *      return $result;
 *
 *
 * В случае если вы не можете разместить объявление в силу нарушений ваших правил,
 * верните объект (sdtClass) с сообщением пользователю
 * Например:
 *      $result->rejectReason = 'Номер телефона заблокирован';
 *      return $result;
 *
 * Примеры частых ошибок:
 *
 * > Номер телефона заблокирован
 * > Слишком низкая цена для этого объявления
 * > И тп.
 *
 * Помните, что сообщение об отклонении объявления увидит конечный пользователь!
 *
 *
 * @param $item - объект содержащий всю информацию об объявлении
 * @return stdClass - результат обработки
 */
$onItem = function ($item) {

    echo "Обрабатываем объявление #" . $item->id . "\r\n";

    print_r($item);

    $result = new \stdClass();

    // Успешно размещено
    // $result->url = 'http://your-site.ru/item-123';
    // $result->views = 15;

    // ИЛИ

    // Проблема с доступом к базе данных / Недостаточно памяти
    // $result->error = 'Произошел технический сбой';

    // ИЛИ

    // Объявление дубликат
    // $result->similarUrl = 'http://your-site.ru/item2';

    // ИЛИ

    // Не соответствует правилам вашего портала
    // $result->rejectReason = 'Номер телефона заблокирован';

    return $result;
};

/**
 * Обработчик конца импорта
 *
 * Здесь нужно удалить из вашей базы данных все объявления которые отсутствовали в
 * XML фиде (не забудьте удалить изображения)
 *
 * @param $report
 * @param $report ->location            - Путь до файла с готовым отчетом (ссылку на него нужно
 *                                        вставить в личный кабинет партнера)
 * @param $report ->statistics.total    - Общее число объявление в фиде
 * @param $report ->statistics.success  - Число объявление обработанных успешно
 * @param $report ->statistics.rejected - Число объявление отклоненных от публикации
 * @param $report ->statistics.errors   - Число объявление обработанных с ошибками
 */
$onEnd = function ($report) {

    echo "Обработка XML фида завершена!\r\n";

    var_dump($report);
};

/**
 * Обработчик ошибки обработки XML фида
 *
 * @param $error
 */
$onError = function ($error) {

    echo "Произошла ошибка " . $error . "\r\n";

    $this->assertEquals('I\'m here!', 'Should not be here!');
};

$options = new \stdClass();

// Ссылка на XML фид из личного кабинета партнера
$options->xmlFeedUrl = 'https://realtypult.ru/api/xml/export/partner/FORMAT/TOKEN';

// Путь к файлу отчета, файл будет создаваться автоматически
$options->reportFileLocation = '/var/www/my-site/public/rm-report.xml';

// Формат XML фида ('realtypult' или 'yandex')
$options->format = 'realtypult';

// Функция будет вызвана для каждого объявления из XML фида
$options->onItem = $onItem;

// Функция будет вызвана когда весь XML фид будет обработан
$options->onEnd = $onEnd;

// Функция будет вызвана в случае непредвиденной критической ошибки
$options->onError = $onError;

$importer = new \RealtyPultImporter($options);

// Запускаем импорт
$importer->run();
```

Код этого примера находится [тут](examples/example.php).