Yii2 Validators Collection
==========================
...
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
composer require --prefer-dist "insolita/yii2-validatorpack:~0.0.1"
```

or add

```
"insolita/yii2-validatorpack": "~0.0.1"
```
