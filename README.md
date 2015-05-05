# Moy Sklad
Module for PrestaShop CMS which allows to upload data to http://www.moysklad.ru/ service

 > The [existing module](http://elcommerce.com.ua/s-servisom-moysklad/27-modul-sinhronizacii-cms-prestashop-154-i-moysclad.html) and  [instruction](https://support.moysklad.ru/hc/ru/articles/203053716-%D0%98%D0%BD%D1%82%D0%B5%D0%B3%D1%80%D0%B0%D1%86%D0%B8%D1%8F-%D1%81-%D0%B8%D0%BD%D1%82%D0%B5%D1%80%D0%BD%D0%B5%D1%82-%D0%BC%D0%B0%D0%B3%D0%B0%D0%B7%D0%B8%D0%BD%D0%B0%D0%BC%D0%B8) for it.

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/LogansUA/ps_moysklad/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/LogansUA/ps_moysklad/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/LogansUA/ps_moysklad/badges/build.png?b=master)](https://scrutinizer-ci.com/g/LogansUA/ps_moysklad/build-status/master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/1310b1af-058f-404a-b7b1-eaa5ed8e199a/mini.png)](https://insight.sensiolabs.com/projects/1310b1af-058f-404a-b7b1-eaa5ed8e199a)

## Installation
* Download latest version of module
```
git clone https://github.com/LogansUA/ps_moysklad.git
```
* Move module dir (`ps_moysklad`) to modules folder of your shop
```
mv ps_moysklad/ YourShop/modules/
```
* Go to the settings module and input `Login` and `Password` to MoySklad service
![Screenshot](http://s13.postimg.org/laeuctrxj/Modules_Test_Shop.png)
* Press `Submit` to export data (`Goods`, `Сontracting parties` and `Customer orders`) into MoSklad
