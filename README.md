Yii2 Validators Collection
==========================
![Status](https://travis-ci.org/Insolita/yii2-validatorpack.svg?branch=master)
![Latest Stable Version](https://img.shields.io/packagist/v/insolita/yii2-validatorpack.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/insolita/yii2-validatorpack.svg)](https://packagist.org/packages/insolita/yii2-validatorpack.svg)
![License](https://img.shields.io/packagist/l/insolita/yii2-validatorpack.svg)
 - PathValidator

   ```
       public function rules(){
           return [
              [['pathAttribute'],PathValidator::class,'strictDir'=>true],
              [['pathAttribute'],PathValidator::class,'strictFile'=>true,'writeable'=>true],
              [['pathAttribute'],PathValidator::class,'requiredBase'=>'@common/data','readable'=>true],
           ];
       }
   ```

Installation
------------

run

```
composer require --prefer-dist "insolita/yii2-validatorpack:~0.0.2"
```

or add

```
"insolita/yii2-validatorpack": "~0.0.2"
```
