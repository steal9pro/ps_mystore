<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(dirname(__FILE__) . '/ExportModel.php');

/**
 * Class MoySklad
 *
 * @author Oleg Kachinsky <logansoleg@gmail.com>
 */
class MoySklad extends Module
{
    /**
     * @var boolean $_errors error
     */
    protected $_errors = false;

    /**
     * @var int $langId Language Id
     */
    private $langId = 0;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->name      = 'moysklad';
        $this->tab       = 'moy_sklad';
        $this->version   = '1.0';
        $this->author    = 'Andrew & Oleg';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Moy sklad');
        $this->description = $this->l('Module which allows to upload data to http://www.moysklad.ru/ service');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->langId           = $this->context->language->id;
    }

    /**
     * Install
     *
     * @return bool
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        return true;
    }

    /**
     * Un install
     *
     * @return bool
     */
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }

    /**
     * Get content
     *
     * @return mixed
     */
    public function getContent()
    {
        if (Tools::isSubmit('submitModule')) {
            if (Tools::getValue('moysklad_login') && Tools::getValue('moysklad_password')) {
                Configuration::updateValue('PS_MOYSKLAD_LOGIN', Tools::getValue('moysklad_login'));
                Configuration::updateValue('PS_MOYSKLAD_PASSWORD', Tools::getValue('moysklad_password'));
            }

            $login    = Tools::getValue('moysklad_login');
            $password = Tools::getValue('moysklad_password');

            $exportModel = new ExportModel($login, $password);

            $exportModel->exportGoods($this->getFormattedProducts());
            $exportModel->exportCompanies($this->getFormattedCompanies());
            $exportModel->exportOrders($this->getFormattedCustromerOrders());
        }

        return $this->renderForm();
    }

    /**
     * Get formatted products
     *
     * @return array
     */
    private function getFormattedProducts()
    {
        $productsArray = Product::getProducts($this->langId, 0, 1000, 'id_product', 'ASC');
        $customArray   = [];

        $j = 0;

        foreach ($productsArray as $product) {
            $productObject = new Product($product['id_product']);

            $combinations = $productObject->getAttributeCombinations($this->langId);

            foreach ($combinations as $key => $combination) {
                if (!isset($customArray[$j]['name'])) {
                    $customArray[$j]['name'] = $productObject->name[$this->langId] . "_" .
                                               $combination['group_name'] . "_" .
                                               $combination['attribute_name'];
                }

                if ($key % 2 == 1) {
                    $customArray[$j]['name'] .= "_" . $combination['group_name'] . "_" . $combination['attribute_name'];

                    $productPrice = floatval($productObject->price);
                    $combinationPrice = floatval(Combination::getPrice($productObject->id));
                    $salePrice = number_format($productPrice + $combinationPrice, 2, '', '');

                    $customArray[$j]['salePrice']      = $salePrice;
                    $customArray[$j]['vat']            = $product['rate'];
                    $customArray[$j]['productCode']    = $productObject->reference;
                    $customArray[$j]['product_sku_id'] = $productObject->id . "_" .
                                                         $combination['id_product_attribute'];
                    $j++;
                }
            }

            $customArray[$j]['name']           = $productObject->name[$this->langId];
            $customArray[$j]['salePrice']      = number_format($productObject->price, 2, '', '');
            $customArray[$j]['vat']            = $product['rate'];
            $customArray[$j]['productCode']    = $productObject->reference;
            $customArray[$j]['product_sku_id'] = $productObject->id;

            $j++;
        }

            return $customArray;
    }

    /**
     * Get formatted companies
     *
     * @return array
     */
    private function getFormattedCompanies()
    {
        $companiesArray = Customer::getCustomers();
        $customArray   = [];

        $j = 0;

        foreach ($companiesArray as $company) {
            $customArray[$j]['name'] = $company['firstname'] . " " . $company['lastname'];
            $customArray[$j]['email'] = $company['email'];
            $customArray[$j]['code'] = $company['id_customer'];

            $j++;
        }

        return $customArray;
    }

    /**
     * Get formatted customer orders
     */
    private function getFormattedCustromerOrders()
    {
        // Get orders information
        $orders = Order::getOrdersWithInformations();
        $customArray = [];

        $j = 0;

        // Create custom array of orders
        foreach ($orders as $order) {
            // Fill details for order
            $customArray[$j]['order']['order_id'] = $order['id_order'];
            $customArray[$j]['order']['status'] = $order['state_name'];
            $customArray[$j]['order']['total'] = number_format('12', 2, '', '');

            $orderDetails = OrderDetail::getList($order['id_order']);

            $i = 0;
            // Fill details for order products
            foreach ($orderDetails as $orderDetail) {
                $customArray[$j]['orderProducts'][$i]['id'] = $orderDetail['product_id'];
                if (intval($orderDetail['product_attribute_id']) > 0) {
                    $customArray[$j]['orderProducts'][$i]['id'] .= '_' . $orderDetail['product_attribute_id'];
                }
                $customArray[$j]['orderProducts'][$i]['quantity'] = $orderDetail['product_quantity'];
                $customArray[$j]['orderProducts'][$i]['product_price'] = number_format($orderDetail['unit_price_tax_incl'], 2, '', '');

                $i++;
            }

            // Fill details for customer
            $customArray[$j]['customer']['id'] = $order['id_customer'];

            $j++;
        }

        return $customArray;
    }

    /**
     * Render custom form
     *
     * @return string
     */
    private function renderForm()
    {
        $fieldsForm = [
            'form' => [
                'input'  => [
                    [
                        'type'  => 'text',
                        'label' => 'Login',
                        'name'  => 'moysklad_login',
                    ],
                    [
                        'type'  => 'text',
                        'label' => 'Password',
                        'name'  => 'moysklad_password',
                    ],
                ],
                'submit' => [
                    'title' => 'Submit',
                ],
            ],
        ];

        $lang   = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();

        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $helper->default_form_language    = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')
            ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')
            : 0;
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'submitModule';
        $helper->currentIndex             = $this->context
            ->link
            ->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token                    = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id
        ];

        return $helper->generateForm([
            $fieldsForm
        ]);
    }

    /**
     * Get config fields values
     *
     * @return array
     */
    private function getConfigFieldsValues()
    {
        return [
            'moysklad_login'    => Configuration::get('PS_MOYSKLAD_LOGIN'),
            'moysklad_password' => Configuration::get('PS_MOYSKLAD_PASSWORD'),
        ];
    }
}
