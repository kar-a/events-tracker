# Task Memory Card
Date: 2026-02-02
Task Key: 1308
Title: Пример вывода каратности в приложении

## Context
- В приложении ImShop нужно было показать пример отображения веса вставок в каратах.
- В фиде для ImShop требуется: для тестового товара (артикул 00001051) у атрибута «Вес» добавить unit «вес золота», затем добавить второй param «Вес» с unit «Вес вставок» и значением — сумма веса вставок из InsertTable.
- Данные о вставках хранятся в InsertTable: UF_XML_ID, UF_INSERTS_INFO (JSON с массивом вставок, у каждой поле ves и др.).

## Key Decisions
- Включение примера только при константе APP_IMSHOP_KARATNOST_TEST (в constants.php).
- Условие по товару: (EXTERNAL_ID ?? XML_ID) == '00001051'.
- Первый param «Вес» получает unit="Вес золота"; второй param добавляется после цикла по children: name="Вес", unit="Вес вставок", значение — сумма ves из InsertTable + « карат».
- Данные вставок берутся по offer (SKU): GetList по ID оффера с select PROPERTY_INSERT, затем getInsertData($el) по InsertTable по UF_XML_ID.
- Добавлен вывод данных о вставках в блок «Характеристики» (collapsibleDescription) при APP_IMSHOP_KARATNOST_TEST: название камня, форма вставки, цветность, цвет (используются TemplateHelper::getInsertPhoto, getInsertShapeByCut).

## Code Touchpoints
- `app/local/php_interface/lib/events/yandexmarket/onExportOfferWriteData.php`:
  - Импорт TemplateHelper.
  - Перед циклом по param: $insertWeightSumToAdd = null.
  - case 'Вес': при APP_IMSHOP_KARATNOST_TEST и артикуле 00001051 — addAttribute('unit', 'Вес золота'); загрузка offer по ID с PROPERTY_INSERT, getInsertData($el), getInsertWeightSum($insertData) . ' карат' → $insertWeightSumToAdd.
  - После цикла по children: если $insertWeightSumToAdd !== null — addChild('param', …) с name="Вес", unit="Вес вставок".
  - В блоке «Характеристики»: при APP_IMSHOP_KARATNOST_TEST и $insertData — дополнение $properties данными по вставкам (kamen, forma_ogranki, gruppa_cveta, cvet_kamnya).
  - getElementsSelect(): добавлены XML_ID, PROPERTY_INSERT.
  - getInsertData($offer): по PROPERTY_INSERT_VALUE/PROPERTY_INSERT загрузка InsertTable, возврат массива из UF_INSERTS_INFO.
  - getInsertWeightSum($offer): через getInsertData суммирование ves, возврат number_format или null.
  - loadOffersToProductsMap(): в select офферов добавлен PROPERTY_INSERT.
- `app/local/php_interface/inc/constants.php`: определена константа APP_IMSHOP_KARATNOST_TEST (true).

## Verification
- При APP_IMSHOP_KARATNOST_TEST в YML для товара 00001051 в offer два param «Вес»: первый с unit="Вес золота", второй с unit="Вес вставок" и значением суммы веса вставок в каратах.
- В приложении ImShop в характеристиках товара отображаются данные о вставках.

## Follow-ups
- После проверки примера рассмотреть отключение APP_IMSHOP_KARATNOST_TEST или распространение логики на другие товары с вставками.
- В блоке характеристик для «Чистота» в коде использовано gruppa_cveta; при необходимости заменить на поле, соответствующее чистоте (например, gruppa_defecta).
