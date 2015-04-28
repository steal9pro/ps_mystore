<?php

include_once(dirname(__FILE__) . '/MoySkladModel.php');

/**
 * ExportModel
 *
 * @author Oleg Kachinsky <logansoleg@gmail.com>
 */
class ExportModel extends ObjectModel
{
    /**
     * @var string $login Login to MoySklad API
     */
    private $login;

    /**
     * @var string $password Password to MoySklad API
     */
    private $password;

    /**
     * Constructor
     *
     * @param string $login
     * @param string $password
     */
    public function __construct($login, $password)
    {
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * Export goods to moysklad
     */
    public function exportGoods()
    {
        $manager = new MoySkladModel($this->login, $this->password);


    }
}
