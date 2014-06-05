<?php

namespace Parm\Exception;

class ConnectionErrorException extends ErrorException
{
    const MYSQL_ER_DBACCESS_DENIED_ERROR = 1044;
    const MYSQL_ER_ACCESS_DENIED_ERROR = 1045;

    public function __construct($connection)
    {
        if ($connection != null && $connection->connect_errno != null && ($connection->connect_errno == self::MYSQL_ER_ACCESS_DENIED_ERROR || $connection->connect_errno == self::MYSQL_ER_DBACCESS_DENIED_ERROR)) {
            parent::__construct("Parm Connection Error. Database username, password, host, port, socket, charset or collation casued an error.", null, E_USER_ERROR);
        } else {
            parent::__construct("Parm Connection Error " . $connection->connect_error, null, E_USER_ERROR);

        }
    }
}
