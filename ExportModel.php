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
     * Export goods to moysklad service
     *
     * @param array $goods Goods
     */
    public function exportGoods($goods = null)
    {
        $manager = new MoySkladLogic($this->login, $this->password);

        $manager->updateGoods($goods);
    }

    /**
     * Export companies to moysklad service
     *
     * @param array $companies Customers
     */
    public function exportCompanies($companies = null)
    {
        $manager = new MoySkladLogic($this->login, $this->password);

        $manager->updateCompanies($companies);
    }

    /**
     * Export customer orders to moysklad service
     *
     * @param array $orders Customer Orders
     */
    public function exportOrders($orders = null)
    {
        $manager = new MoySkladLogic($this->login, $this->password);

        $manager->updateCustomerOrders($orders);
    }
}
