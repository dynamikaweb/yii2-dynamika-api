dynamikaweb/yii2-dynamika-api (API Controller) 
====================================
[![Latest Stable Version](https://img.shields.io/github/v/release/dynamikaweb/yii2-dynamika-api)](https://github.com/dynamikaweb/yii2-dynamika-api/releases)
![Total Downloads](https://poser.pugx.org/dynamikaweb/yii2-dynamika-api/downloads)
[![License](https://poser.pugx.org/dynamikaweb/yii2-dynamika-api/license)](https://github.com/dynamikaweb/yii2-dynamika-api/blob/master/LICENSE)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/cae8eef36fdf4826a9ec5d31945147ad)](https://www.codacy.com/gh/dynamikaweb/yii2-dynamika-api?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=dynamikaweb/yii2-dynamika-api&amp;utm_campaign=Badge_Grade)
[![Build Test](https://scrutinizer-ci.com/g/dynamikaweb/yii2-dynamika-api/badges/build.png?b=master)](https://scrutinizer-ci.com/g/dynamikaweb/yii2-dynamika-api/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dynamikaweb/yii2-dynamika-api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dynamikaweb/yii2-dynamika-api/?branch=master)

> **Aviso!** está api é de exclusividade de _Dynamika Soluções Web LTDA_ e só funcionara em projetos utilizando a estrutura da mesma, o código fonte demostrado está documentado para utilização de terceiros sobre o consumo e funcionamento do serviço, não deve ser possível instalar em outra estrutura!
<br/>

Instalação
------------
ultilize [composer](http://getcomposer.org/download/) para instalar esta extensão.

execute

```bash
$ composer require "dynamikaweb/yii2-dynamika-api" "*" 
```
ou adicione essa linha em seu arquivo `composer.json`

```json
"dynamikaweb/yii2-dynamika-api" : "*"
```

Configure um controller para API
-----

adicione ao arquivo `frontend/controllers/ApiController.php` e configure os modulos permitidos
```PHP 
<?php

namespace frontend\controllers;

/**
 * Api controller
 */
class ApiController extends \dynamikaweb\api\BaseApiController
{
    public static function modulos()
    {
        return [
            'noticia',
            'pagina'
        ];
    }
}
```

Como utilizar
-----

  * **Leia a documentação:** [https://github.com/dynamikaweb/yii2-dynamika-api/wiki](https://github.com/dynamikaweb/yii2-dynamika-api/wiki)
