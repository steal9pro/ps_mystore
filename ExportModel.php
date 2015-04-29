<?php

include_once(dirname(__FILE__) . '/MoySkladLogic.php');

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
     *
     * @param null $goods
     */
    public function exportGoods($goods = null)
    {
        $manager = new MoySkladLogic($this->login, $this->password);

        $manager->updateGoods($goods);
    }
}
