<?php
/**
 * Created by solly [25.05.17 16:48]
 */

namespace insolita\validators;

use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use yii\validators\Validator;

/**
 * Class PathValidator
 *
 * @package insolita\fixturegii\services
 */
class PathValidator extends Validator
{
    /**
     * @var bool - ensure that path - is Directory
     */
    public $strictDir = false;
    
    /**
     * @var bool - ensure that path - is File
     */
    public $strictFile = false;
    
    /**
     * ensure that path is writeable
     *
     * @var bool
     */
    public $writeable = false;
    
    /**
     * ensure that path is readable
     *
     * @var bool
     */
    public $readable = false;
    
    /**
     * If set - validator will ensure that path belongs to current
     *
     * @var null
     */
    public $requiredBase = null;
    
    /**
     * If true - attribute with alias will overwritten as path
     *
     * @var bool
     */
    public $aliasReplace = true;
    
    /**
     * If true -  attribute will overwritten with normalized path
     *
     * @var bool
     */
    public $normalize = true;
    
    /**
     * @var string the user-defined error message. It may contain the following placeholders which
     * will be replaced accordingly by the validator:
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     */
    public $message;
    
    /**
     * @var string error message if path not file or not directory
     * Supported tokens
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     * - `{type}`: the required strict type - file or directory
     */
    public $notTypeMessage;
    
    /**
     * @var string error message if path not writeable
     */
    public $notWriteableMessage;
    
    /**
     * @var string message if path not readable
     */
    public $notReadableMessage;
    
    /**
     * @var string
     *  - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     * - `{base}`: the required part of path
     */
    public $notBelongsMessage;
    
    /**
     * @var bool
     */
    public $skipOnEmpty = true;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->enableClientValidation = false;
        if ($this->message === null) {
            $this->message = \Yii::t('yii', '{attribute} cannot be blank.');
        }
        if ($this->notReadableMessage === null) {
            $this->notReadableMessage = '{attribute} is not readable';
        }
        if ($this->notWriteableMessage === null) {
            $this->notWriteableMessage = '{attribute} is not writeable';
        }
        if ($this->notBelongsMessage === null) {
            $this->notBelongsMessage = '{attribute} is not belongs ' . $this->requiredBase;
        }
        if ($this->notTypeMessage === null) {
            $this->notTypeMessage = $this->strictDir
                ? '{attribute} is not directory'
                : ($this->strictFile ? '{attribute} is not file' : '');
        }
    }
    
    /**
     * @param \yii\base\Model $model
     * @param string          $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        if ($this->skipOnEmpty === false && !trim($model->$attribute)) {
            $this->addError($model, $attribute, $this->message);
        } else {
            $path = \Yii::getAlias($model->$attribute);
            $normalPath = FileHelper::normalizePath($path);
            $result = $this->validateValue($normalPath);
            if (!empty($result)) {
                $this->addError($model, $attribute, $result[0], $result[1]);
            } else {
                if ($this->normalize === true) {
                    $model->$attribute = $normalPath;
                } elseif ($this->aliasReplace === true) {
                    $model->$attribute = $path;
                }
            }
        }
    }
    
    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $result = $this->checkStrictTypes($value);
        if (is_null($result)) {
            $result = $this->checkPermissions($value);
            if (is_null($result)) {
                $result = $this->ensureBasePath($value, $this->requiredBase);
            }
        }
        return $result;
    }
    
    /**
     * @param $normalPath
     *
     * @return array|null
     */
    protected function checkStrictTypes($normalPath)
    {
        $result = null;
        
        if ($this->strictDir === true && !is_dir($normalPath)) {
            $result = [$this->notTypeMessage, ['type' => 'directory']];
        } elseif ($this->strictFile === true && !is_file($normalPath)) {
            $result = [$this->notTypeMessage, ['type' => 'file']];
        }elseif (!file_exists($normalPath)){
            $result = [$this->notTypeMessage, ['type' => 'file']];
        }
        return $result;
    }
    
    /**
     * @param $normalPath
     *
     * @return array|null
     */
    protected function checkPermissions($normalPath)
    {
        $result = null;
        
        if ($this->readable === true && !is_readable($normalPath)) {
            $result = [$this->notReadableMessage, []];
        } elseif ($this->writeable === true && !is_writable($normalPath)) {
            $result = [$this->notWriteableMessage, []];
        }
        return $result;
    }
    
    /**
     * @param $normalPath
     * @param $basePath
     *
     * @return array|null
     */
    protected function ensureBasePath($normalPath, $basePath)
    {
        $result = null;
        if ($basePath) {
            $basePath = FileHelper::normalizePath(\Yii::getAlias($basePath));
            if (StringHelper::startsWith($normalPath, $basePath) === false) {
                $result = [$this->notBelongsMessage, ['base' => $basePath]];
            }
        }
        return $result;
    }
}
