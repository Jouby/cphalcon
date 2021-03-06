<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Exception\ModuleException;
use Codeception\TestInterface;
use PDO;

use function date;
use function getenv;
use function getOptionsMysql;
use function getOptionsPostgresql;
use function getOptionsSqlite;
use function sprintf;

/**
 * Class Database
 *
 * @property string $driver
 * @property string $password
 * @property string $username
 */
class Database extends \Codeception\Module
{
    /**
     * @var string
     */
    private $driver = 'sqlite';

    /**
     * @var string
     */
    private $password   = '';

    /**
     * @var string
     */
    private $username   = '';

    /**
     * @param TestInterface $test
     */
    public function _before(TestInterface $test)
    {
        $driver = $test->getMetadata()->getCurrent('env');
        if (!empty($driver)) {
            $this->driver = $driver;
        }

        parent::_before($test);
    }

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @return PDO
     * @throws ModuleException
     */
    public function getConnection(): PDO
    {
        return $this->getModule('Db')->_getDbh();
    }

    /**
     * @return array
     */
    public function getDatabaseOptions(): array
    {
        switch ($this->driver) {
            case 'pgsql':
            case 'postgres':
                return getOptionsPostgresql();
            case 'sqlsrv':
                return getOptionsSqlite();
            case 'mysql':
                return getOptionsMysql();
            default:
                return [];
        }
    }

    /**
     * @return string
     */
    public function getDatabaseDsn(): string
    {
        switch ($this->driver) {
            case 'mysql':
                $this->password = getenv('TEST_MYSQL_PASS');
                $this->username = getenv('TEST_MYSQL_USER');

                return sprintf(
                    "mysql:host=%s;dbname=%s;charset=utf8mb4",
                    getenv('TEST_MYSQL_HOST'),
                    getenv('TEST_MYSQL_NAME')
                );
            case 'pgsql':
            case 'postgres':
                $this->password = getenv('TEST_POSTGRES_PASS');
                $this->username = getenv('TEST_POSTGRES_USER');

                return sprintf(
                    "pgsql:host=%s;dbname=%s;user=%s;password=%s",
                    getenv('TEST_POSTGRES_HOST'),
                    getenv('TEST_POSTGRES_NAME'),
                    $this->username,
                    $this->password
                );
            case 'sqlsrv':
                return "";
                break;
            default:
                return "sqlite:memory";
        }
    }

    /**
     * @param string $driver
     *
     * @return string
     */
    public function getDatabaseNow(string $driver): string
    {
        switch ($driver) {
            case "sqlite":
                return date("'Y-m-d H:i:s'");
            default:
                return "NOW()";
        }
    }

    /**
     * @return string
     */
    public function getDatabasePassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getDatabaseUsername(): string
    {
        return $this->username;
    }
}
