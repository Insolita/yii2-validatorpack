<?php
/**
 * Created by solly [25.05.17 1:59]
 */

namespace tests\unit;

use Codeception\Specify;
use Codeception\Test\Unit;
use Codeception\Util\Debug;
use insolita\validators\PathValidator;
use yii\base\DynamicModel;
use yii\helpers\FileHelper;

/**
 * Class PathValidatorTest
 *
 * @package tests\unit
 */
class PathValidatorTest extends Unit
{
    use Specify;
    
    /**
     * @var
     */
    private $testModel;
    
    /**
     *
     */
    public function testAll()
    {
        $this->specify(
            'checkDefaults',
            function () {
                $dir = __DIR__;
                $file = __FILE__;
                $empty = '';
                $nonFile1 = '12345';
                $nonFile2 = 234567;
                $error = null;
                $validator = new PathValidator([]);
                verify('dir', $validator->validate($dir, $error))->true();
                verify('file', $validator->validate($file, $error))->true();
                verify('empty', $validator->validate($empty, $error))->false();
                verify('nonFile1', $validator->validate($nonFile1, $error))->false();
                verify('nonFile2', $validator->validate($nonFile2, $error))->false();
            }
        );
        $this->specify(
            'checkNonTypes',
            function () {
                $dir = __DIR__;
                $file = __FILE__;
                $validator = new PathValidator(['strictDir' => true]);
                verify('dir', $validator->validate($dir, $error))->true();
                verify('file', $validator->validate($file, $error))->false();
                
                $validator = new PathValidator(['strictFile' => true]);
                verify('dir', $validator->validate($dir, $error))->false();
                verify('file', $validator->validate($file, $error))->true();
            }
        );
        $this->specify(
            'checkBasePath',
            function () {
                $dir = __DIR__;
                $file = __FILE__;
                $validator = new PathValidator(['requiredBase' => __DIR__.'/../']);
                verify('dir|base@valid', $validator->validate($dir, $error))->true();
                verify('file|base@valid', $validator->validate($file, $error))->true();
                
                $validator = new PathValidator(['requiredBase' => '@wDir']);
                verify('dir|base@bad', $validator->validate($dir, $error))->false();
                verify('file|base@bad', $validator->validate($file, $error))->false();
            }
        );
        $this->specify(
            'checkDirectories',
            function () {
                $dir = \Yii::getAlias('@wDir');
                $rDir = \Yii::getAlias('@rDir');
                $uDir = \Yii::getAlias('@uDir');
                $error = null;
                $validator = new PathValidator(
                    [
                        'strictDir' => true,
                    ]
                );
                verify('ddir|strict', $validator->validate($dir, $error))->true();
                $validator = new PathValidator(
                    [
                        'strictDir' => true,
                        'readable'  => true,
                    ]
                );
                verify('ddir|r', $validator->validate($dir, $error))->true();
                verify('readOnly|r', $validator->validate($rDir, $error))->true();
                verify('unReadable|r', $validator->validate($uDir, $error))->false();
                $validator = new PathValidator(
                    [
                        'strictDir' => true,
                        'readable'  => true,
                        'writeable' => true,
                    ]
                );
                verify('ddir|rw', $validator->validate($dir, $error))->true();
                verify('readOnly|rw', $validator->validate($rDir, $error))->false();
                verify('unReadable|rw', $validator->validate($uDir, $error))->false();
            }
        );
        $this->specify(
            'checkFiles',
            function () {
                $dir = \Yii::getAlias('@wFile');
                $rDir = \Yii::getAlias('@rFile');
                $uDir = \Yii::getAlias('@uFile');
                $error = null;
                $validator = new PathValidator(
                    [
                        'strictFile' => true,
                    ]
                );
                verify('file|strict', $validator->validate($dir, $error))->true();
                $validator = new PathValidator(
                    [
                        'strictFile' => true,
                        'readable'   => true,
                    ]
                );
                verify('file|r', $validator->validate($dir, $error))->true();
                verify('readOnly|r', $validator->validate($rDir, $error))->true();
                verify('unReadable|r', $validator->validate($uDir, $error))->false();
                $validator = new PathValidator(
                    [
                        'strictFile' => true,
                        'readable'   => true,
                        'writeable'  => true,
                    ]
                );
                verify('file|rw', $validator->validate($dir, $error))->true();
                verify('readOnly|rw', $validator->validate($rDir, $error))->false();
                verify('unReadable|rw', $validator->validate($uDir, $error))->false();
            }
        );
        $this->specify(
            'testAliases',
            function () {
                DynamicModel::validateData(['dir' => '@wDir', 'file' => '@wFile'],
                    [
                        [['dir','file'],PathValidator::class,'readable'=>true,'writeable'=>true]
                    ]);
            }
        );
    }
    
    /**
     *
     */
    protected function _before()
    {
        parent::_before();
        \Yii::setAlias('@curDir', __DIR__);
        try {
            FileHelper::createDirectory(__DIR__ . '/chmods/', 0777);
            FileHelper::createDirectory(__DIR__ . '/chmods/rDir/', 0466);
            file_put_contents(__DIR__ . '/chmods/wFile.txt', '...');
            file_put_contents(__DIR__ . '/chmods/rFile.txt', '...');
            chmod(__DIR__ . '/chmods/wFile.txt', 0777);
            chmod(__DIR__ . '/chmods/rFile.txt', 0466);
        } catch (\Throwable $e) {
            Debug::debug(['fail before ' => $e->getMessage()]);
            FileHelper::removeDirectory(__DIR__ . '/chmods/');
        }
        \Yii::setAlias('@wDir', __DIR__ . '/chmods/');
        \Yii::setAlias('@rDir', __DIR__ . '/chmods/rDir/');
        \Yii::setAlias('@uDir', '/root');
        \Yii::setAlias('@wFile', __DIR__ . '/chmods/wFile.txt');
        \Yii::setAlias('@rFile', __DIR__ . '/chmods/rFile.txt');
        \Yii::setAlias('@uFile', '/root/messages');
        
        $this->testModel = new DynamicModel(
            [
                'aliasedDir'    => '@testDir',
                'aliasedFile'   => '@testFile',
                'dir'           => __DIR__,
                'file'          => __FILE__,
                'empty'         => '',
                'notFile'       => 1234567,
                'unexistedDir'  => __DIR__ . '/../qwertyuiop/',
                'unexistedFile' => __DIR__ . '/qwertyuiop.jpg',
            ]
        );
    }
    
    /**
     *
     */
    protected function _after()
    {
        FileHelper::removeDirectory(__DIR__ . '/chmods');
        parent::_after();
    }
}
