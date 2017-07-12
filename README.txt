Plugin: InterKassa Gateway
Description: Payment gateway "Intercassa" for sites on WordPress. (Version of Intercasse 2.0)
Purpose: The module adds a payment method "Intercass" for the plugin WooCommerce 2.5.5+, Engine 4.5.X version
Version: 1.5
Author: Gateon
Site: http://www.gateon.net

#Install
1. Make sure the versions of the module and your CMS match, they must match.
2. Download the plugin to your computer, unpack it.
3. Download the plugin folder to the server with the site in the /wp-content/plugins/ folder
4. Go to the admin panel, find the plug-in "InterKassa Gateway" in the list and click "Activate".
5. Go to the WooCommerce settings /Checkout/Intercass 2.0 tab.
6. Enable the plugin, enter your wallet data from the Intercasse account, a unique key, a purse number, 
   a header and a description of the payment method to display in the list of payments for your store
7. If you want to use the new IPA Intercasse, which allows you to choose the payment system before 
   switching to the payment gateway of the Intercasse, then do the following:
	- API Id, API Key is in your account settings in the IPA section
	- In the IP filter field, add the IP of your site (you can learn it by simply scoring in the search "find the site address" 
	 or by using the ping command in the console)
	- Activate in the settings of the Admin module. Another button appears after selecting the method of payment and confirmation.
	Click "Save"

The plugin is active and will appear in the list of payments for your store.

# Test mode for debugging the module
1. Add a payment method "Test mode" (currency XTS) in the settings of the purse on the intercash site.
1. Enter the test key from the cash register settings
2. Activate the test mode of the module
3. Click "Save"

REQUIRED TO READ:
!!! To test payments at the checkout, use the test currency XTS (test payment system), 
for this it is necessary to include this item in the checkout, and then, when redirecting to the "Interbank" site, 
choose it as payment, then the system will generate a test page with all kinds of response from the server , 
This will help you configure all the necessary settings and make sure your system is working.




Плагин: InterKassa Gateway
Описание: Платежный шлюз "Интеркасса" для сайтов на WordPress. (версия Интеркассы 2.0)
Предназначение: Модуль добавляет способ оплаты "Интеркасса" для плагина WooCommerce 2.5.5+, Версия движка 4.5.X
Версия: 1.5
Автор: Gateon
Сайт автора: http://www.gateon.net

#Установка
1. Убедитесь в соответствии версий модуля и вашей CMS, они должны совпадать.
2. Скачать плагин к себе на компьютер, распаковать
3. Закачать папку с плагином на сервер с сайтом в папку /wp-content/plugins
4. зайти в админку, найти в списке плагин "InterKassa Gateway" и нажать "Активировать"
5. Перейти на вкладку настроек WooCommerce/вкладка Оплата(Checkout)/ Интеркасса 2.0
6. Включить плагин, ввести данные своего кошелька с аккаунта Интеркассы, уникальный ключ, номер кошелька, 
   заголовок и описание метода оплаты для отображения в списке оплат вашего магазина
7.Если у Вас есть желание воспользоваться новым АПИ Интеркассы , которое дает возможность выбора платежной системы до перехода 
на платежный шлюз Интеркассы, тогда выполните следующее:
	-API Id , API Key находится в настройках вашего аккаунта в разделе АПИ
	-в поле IP фильтр добавьте IP вашего сайта(узнать вы его можете просто забив в поиске "узнать адрес сайта" или 
	 воспользовавшись в консоли командой ping)
	-Активируйте в настройках модуля админки АПИ. Появиться еще одна кнопка после выбора метода оплаты и подтверджения.

Жмем "Сохранить изменения"

Плагин активен и появится в списке оплат вашего магазина.

#Тестовый режим для отладки модуля
1. Добавить в настройках кошелька на сайте интеркасса вид оплаты "Тестовый режим" (валюта XTS)
1. Вписать тестовый ключ из настроек кассы
2. Активировать тестовый режим модуля
3. жмем "Сохранить"


ОБЯЗАТЕЛЬНО ОЗНАКОМИТСЯ:
!!! Для тестирования платежей в кассе используйте тестовую валюту XTS (тестовая платежная система), для это необходимо 
включить в кассе этот пункт, и далее при перенаправлении на сайт "Интеркассы" выбрать ее в качестве оплаты, 
далее система сгенерирует тестовую страничку со всеми видами ответа от сервера, это поможет вам настроить все необходимые параметры 
и убедиться в работоспособности вашей системы.

