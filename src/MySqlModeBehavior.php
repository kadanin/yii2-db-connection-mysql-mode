<?php
/**
 * Created by PhpStorm.
 * User: Kadanin Artyom
 * Date: 14.03.18
 * Time: 17:30
 */

namespace kadanin\yii2\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\i18n\PhpMessageSource;

/**
 * Class MySqlModeBehavior
 *
 * @property string[] $sqlMode
 */
class MySqlModeBehavior extends Behavior
{
    const MODE_TRADITIONAL                = 'TRADITIONAL';
    const MODE_STRICT_ALL_TABLES          = 'STRICT_ALL_TABLES';
    const MODE_STRICT_TRANS_TABLES        = 'STRICT_TRANS_TABLES';
    const MODE_NO_ZERO_IN_DATE            = 'NO_ZERO_IN_DATE';
    const MODE_NO_ZERO_DATE               = 'NO_ZERO_DATE';
    const MODE_ERROR_FOR_DIVISION_BY_ZERO = 'ERROR_FOR_DIVISION_BY_ZERO';
    const MODE_NO_ENGINE_SUBSTITUTION     = 'NO_ENGINE_SUBSTITUTION';

    const POSSIBLE_MODES = [
        self::MODE_TRADITIONAL                => self::MODE_TRADITIONAL,
        self::MODE_STRICT_ALL_TABLES          => self::MODE_STRICT_ALL_TABLES,
        self::MODE_STRICT_TRANS_TABLES        => self::MODE_STRICT_TRANS_TABLES,
        self::MODE_NO_ZERO_IN_DATE            => self::MODE_NO_ZERO_IN_DATE,
        self::MODE_NO_ZERO_DATE               => self::MODE_NO_ZERO_DATE,
        self::MODE_ERROR_FOR_DIVISION_BY_ZERO => self::MODE_ERROR_FOR_DIVISION_BY_ZERO,
        self::MODE_NO_ENGINE_SUBSTITUTION     => self::MODE_NO_ENGINE_SUBSTITUTION,
    ];

    public $_sqlMode = [self::MODE_TRADITIONAL];

    public function init()
    {
        parent::init();

        $this->registerTranslations();
    }

    public function events()
    {
        return [
            Connection::EVENT_AFTER_OPEN => [$this, 'handleAfterOpen'],
        ];
    }

    /**
     * @param Connection $owner
     *
     * @throws \BadMethodCallException
     */
    public function attach($owner)
    {
        $this->ensureOwner($owner, \BadMethodCallException::class);

        parent::attach($owner);
    }

    private function registerTranslations()
    {
        if (isset(Yii::$app->i18n->translations['kadanin/yii2/behaviors/my-sql-mode'])) {
            return;
        }

        Yii::$app->i18n->translations['kadanin/yii2/behaviors/my-sql-mode'] = [
            'class'          => PhpMessageSource::class,
            'sourceLanguage' => 'en-US',
            'basePath'       => __DIR__ . '/messages',
            'fileMap'        => [
                'errors' => 'errors.php',
            ],
        ];
    }

    /**
     * @param mixed  $owner
     * @param string $exceptionClass
     *
     * @return Connection
     */
    private function ensureOwner($owner, $exceptionClass)
    {
        if ($owner instanceof Connection) {
            return $owner;
        }

        throw new $exceptionClass(Yii::t(
            'kadanin/yii2/behaviors/my-sql-mode'
            , 'Behavior {className} can be attached only to instance of {connectionClassName}'
            , [
                'className'           => static::class,
                'connectionClassName' => Connection::class,
            ]
        ));
    }

    /**
     * @return void
     *
     * @throws InvalidConfigException
     */
    public function handleAfterOpen()
    {
        $this->ensureOwner($this->owner, InvalidConfigException::class)->pdo->exec("SET SQL_MODE='{$this->sqlModeString()}'");
    }

    public function sqlModeString()
    {
        return \implode(',', $this->_sqlMode);
    }

    /**
     * @return string[]
     */
    public function getSqlMode()
    {
        return $this->_sqlMode;
    }

    /**
     * @param string[] $sqlMode
     */
    public function setSqlMode(array $sqlMode)
    {
        $sqlMode = \array_combine($sqlMode, $sqlMode);

        $sqlMode = \array_intersect_key($sqlMode, self::POSSIBLE_MODES);

        $this->_sqlMode = $sqlMode;
    }
}
