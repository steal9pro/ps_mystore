<?php

/**
 * Class MoySkladLogic
 *
 * @author Oleg Kachinsky <logansoleg@gmail.com>
 */
class MoySkladLogic extends ObjectModel
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
     * @var SimpleXMLElement $orderMetadata
     */
    private $orderMetadata;

    /**
     * @var SimpleXMLElement $orderStatus
     */
    private $orderStatus;

    /**
     * @var string $myCompanyId
     */
    private $myCompanyId;

    /**
     * Constructor
     *
     * @param string $login
     * @param string $password
     */
    public function __construct($login, $password)
    {
        $this->login    = $login;
        $this->password = $password;
    }

    /**
     * Get list of goods
     *
     * @param array $filter
     * @param int   $count
     * @param int   $start
     *
     * @return string XML document
     */
    public function getGoodsXml($filter = [], $count = 1000, $start = 0)
    {
        $url    = '/exchange/rest/ms/xml/Good/list?' . $this->buildUrl($filter, $count, $start);
        $result = $this->handleRequest($url, 'GET');

        return $result;
    }

    /**
     * Create new good
     *
     * @param array $product Product
     *
     * @return SimpleXMLElement
     */
    public function createGoodXml($product)
    {
        $dataXML = simplexml_load_string('<good></good>');
        $dataXML->addAttribute('name', $product['name']);
        $dataXML->addAttribute('salePrice', $product['salePrice']);
        $dataXML->addAttribute('vat', $product['vat']);
        $dataXML->addAttribute('productCode', $product['productCode']);
        $dataXML->code         = $product['product_sku_id'];
        $dataXML->externalcode = $product['product_sku_id'];

        return $dataXML;
    }

    /**
     * XML adopt
     *
     * @param SimpleXMLElement $root Root
     * @param SimpleXMLElement $new  New element
     */
    public function xmlAdopt($root, $new)
    {
        $node = $root->addChild($new->getName(), (string) $new);
        foreach ($new->attributes() as $attr => $value) {
            $node->addAttribute($attr, $value);
        }
        foreach ($new->children() as $ch) {
            $this->xmlAdopt($node, $ch);
        }
    }

    /**
     * Update/Create goods on moysklad
     *
     * @param array $products Goods
     *
     * @return mixed
     */
    public function updateGoods($products)
    {
        $existingGoodsXml = $this->parsingXmlDocument($this->getExistingGoodsXml($products));
        foreach ($products as $product) {
            if (count($existingGoodsXml->xpath("good/externalcode[.='" . $product['product_sku_id'] . "']"))) {
                foreach ($existingGoodsXml->xpath('good') as $foundGood) {
                    if ($foundGood->externalcode->__toString() == $product['product_sku_id']) {
                        $foundGood['name'] = $product['name'];
                        unset($foundGood['updated']);
                        unset($foundGood['updatedBy']);
                    }
                }
            } else {
                $productXml = $this->createGoodXml($product);
                $this->xmlAdopt($existingGoodsXml, $productXml);
            }
        }

        $url = '/exchange/rest/ms/xml/Good/list/update';
        $this->handleRequest($url, 'PUT', $existingGoodsXml->asXML());
    }

    /**
     * Create company XML
     *
     * @param array $company
     *
     * @return SimpleXMLElement
     */
    public function createCompanyXml($company)
    {
        $resultXML = simplexml_load_string('<company></company>');
        $resultXML->addAttribute('name', $company['name']);
        $resultXML->addChild('code', $company['code']);
        $resultXML->addChild('contact');
        $resultXML->contact->addAttribute('email', $company['email']);

        return $resultXML;
    }

    /**
     * Get company xml
     *
     * @param array $filter
     * @param int   $count
     * @param int   $start
     *
     * @return SimpleXMLElement
     */
    public function getCompaniesXml($filter = [], $count = 1000, $start = 0)
    {
        $url    = '/exchange/rest/ms/xml/Company/list?' . $this->buildUrl($filter, $count, $start);
        $result = $this->handleRequest($url, 'GET');

        return $result;
    }

    /**
     * Update company
     *
     * @param array $companies Companies
     *
     * @return array|null
     */
    public function updateCompanies($companies)
    {
        $existingCompaniesXml = $this->parsingXmlDocument($this->getExistingCompaniesXml($companies));

        foreach ($companies as $company) {
            if (count($existingCompaniesXml->xpath("company/code[.='" . $company['code'] . "']"))) {
                foreach ($existingCompaniesXml->xpath('company') as $foundCompany) {
                    if ($foundCompany->code->__toString() == $company['code']) {
                        $foundCompany['name'] = $company['name'];
                        unset($foundCompany['updated']);
                        unset($foundCompany['updatedBy']);
                    }
                }
            } else {
                $companyXml = $this->createCompanyXml($company);
                $this->xmlAdopt($existingCompaniesXml, $companyXml);
            }
        }

        $url = '/exchange/rest/ms/xml/Company/list/update';
        $this->handleRequest($url, 'PUT', $existingCompaniesXml->asXML());


    }

    /**
     * Get collection id
     *
     * @param String $xmlData
     *
     * @return array
     */
    private function getCollectionId($xmlData)
    {
        $temp = $this->parsingXmlDocument($xmlData);
        $id   = [];
        foreach ($temp->xpath('id') as $collectionId) {
            $id[] = $collectionId->__toString();
        }

        return $id;
    }

    /**
     * Handle and send request to moysklad
     *
     * @param string $url
     * @param string $method
     * @param null   $dataXML
     *
     * @return string
     */
    private function handleRequest($url, $method = 'GET', $dataXML = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://online.moysklad.ru' . $url);
        if (strlen($dataXML) > 0) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataXML);
        }
        if ($method == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/xml; charset=utf-8']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->login . ':' . $this->password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $out    = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status == 200) {
            return $out;
        } else {
            die('Error with sending the request to the MoySklad service.');
        }
    }

    /**
     * Build url
     *
     * @param array $filter
     * @param int   $count
     * @param int   $start
     *
     * @return string
     */
    private function buildUrl($filter = [], $count = 1000, $start = 0)
    {
        $urlFilter = '';
        foreach ($filter as $filterName => $filterVal) {
            if (is_array($filterVal)) {
                foreach ($filterVal as $val) {
                    $urlFilter .= $filterName . urlencode($val) . ';';
                }
            } else {
                $urlFilter .= $filterName . urlencode($filterVal) . ';';
            }
        }
        $url = '';
        if ($urlFilter) {
            $url .= '&filter=' . trim($urlFilter, ';');
        }
        if ($count) {
            $url .= '&count=' . (int) $count;
        }
        if ($start) {
            $url .= '&start=' . (int) $start;
        }

        $result = trim($url, '&');

        return $result;
    }

    /**
     * Convert XML document into object
     *
     * @param string $dataXml
     *
     * @return SimpleXMLElement
     */
    private function parsingXmlDocument($dataXml)
    {
        $dom = new domDocument;
        $dom->loadXML($dataXml);
        if (!$dom) {
            echo 'Error while parsing the document';
            exit;
        }

        return simplexml_import_dom($dom);
    }

    /**
     * Get existing goods
     *
     * @param $products
     *
     * @return string XML document
     */
    private function getExistingGoodsXml($products)
    {
        $filter['externalCode'] = [];
        foreach ($products as $product) {
            $filter['externalCode'][] = '=' . $product['product_sku_id'];
        }
        $result = $this->getGoodsXml($filter);

        return $result;
    }

    /**
     * Get existing companies
     *
     * @param array $companies
     *
     * @return string XML document
     */
    private function getExistingCompaniesXml($companies)
    {
        $filter['code'] = [];
        foreach ($companies as $company) {
            $filter['code'][] = '=' . $company['code'];
        }
        $result = $this->getCompaniesXml($filter);

        return $result;
    }

    /**
     * Отримання id компанії
     *
     * @return string
     */
    public function getMyCompanyId()
    {
        if (!$this->myCompanyId) {
            $url = '/exchange/rest/ms/xml/MyCompany/list';

            $xmlData           = $this->parsingXmlDocument($this->handleRequest($url, 'GET'));
            $this->myCompanyId = $xmlData->myCompany->uuid->__toString();
        }

        return $this->myCompanyId;
    }

    /**
     * Get order metadata
     *
     * @return string
     */
    protected function getOrderMetadata()
    {
        if (!$this->orderMetadata) {
            $url = '/exchange/rest/ms/xml/Metadata/list?filter=code%3DCustomerOrder';

            $xml     = $this->handleRequest($url, 'GET');
            $xmlData = $this->parsingXmlDocument($xml);
            $result = [];
            foreach ($xmlData->xpath('embeddedEntityMetadata/attributeMetadata') as $attributeMetadata) {
                $result[$attributeMetadata->attributes()->name->__toString()] = [
                    'uuid' => $attributeMetadata->uuid->__toString(),
                    'type' => 'value' . ucfirst(strtolower($attributeMetadata->attributes()->attrType->__toString()))
                ];
            }
            $this->orderMetadata = $result;
        }

        return $this->orderMetadata;
    }

    /**
     * Get all order status
     *
     * @return string
     */
    protected function getAllOrderStatus()
    {
        if (!$this->orderStatus) {
            $url = '/exchange/rest/ms/xml/Workflow/list?filter=code%3DCustomerOrder';

            $xml     = $this->handleRequest($url, 'GET');
            $xmlData = $this->parsingXmlDocument($xml);
            foreach ($xmlData->xpath('workflow/state') as $state) {
                $result[$state->attributes()->name->__toString()] = $state->uuid->__toString();
            }
            $this->orderStatus = $result;
        }

        return $this->orderStatus;
    }

    /**
     * Get customer orders Xml
     *
     * @param array $filter
     * @param int   $count
     * @param int   $start
     *
     * @return SimpleXMLElement
     */
    public function getCustomerOrdersXml($filter = [], $count = 1000, $start = 0)
    {
        $url    = '/exchange/rest/ms/xml/CustomerOrder/list?' . $this->buildUrl($filter, $count, $start);
        $result = $this->handleRequest($url, 'GET');

        return $result;
    }

    /**
     * Create customer order
     *
     * @param SimpleXMLElement $order
     *
     * @return boolean
     */
    public function createCustomerOrder($order)
    {
        $orderStatus = $this->getAllOrderStatus();
        //        $orderMetadata = $this->getOrderMetadata();

        $resultXML = simplexml_load_string('<customerOrder></customerOrder>');
        $resultXML->addAttribute('targetAgentUuid', $this->getMyCompanyId());

        // Get customer by id
        $customer        = $this->getCompaniesXml([
            'code'       => '=' . $order['customer']['id'],
            'applicable' => '=true'
        ]);
        $customerXmlData = $this->parsingXmlDocument($customer);

        $resultXML->addAttribute('sourceAgentUuid', $customerXmlData->company->uuid->__toString());
        $resultXML->addAttribute('stateUuid', $orderStatus[$order['order']['status']]);
        $resultXML->addAttribute('applicable', 'true');
        $resultXML->addAttribute('name', $order['order']['order_id']);
        $resultXML->addChild('externalcode', $order['order']['order_id']);

        // Check if customer order already exist
        $customerOrderXml     = $this->getCustomerOrdersXml([
            'externalCode' => '=' . $order['order']['order_id'],
            'applicable'   => '=true'
        ]);
        $customerOrderXmlData = $this->parsingXmlDocument($customerOrderXml);
        if ($customerOrderXmlData->attributes()->total->__toString()) {
            $resultXML->addChild('uuid', $customerOrderXmlData->customerOrder->uuid->__toString());
        }

        //        $resultXML->addChild('sum');
        //        $resultXML->sum->addAttribute('sum', $order['order']['total'] * 100);
        //        $resultXML->sum->addAttribute('sumInCurrency', $order['order']['total'] * 100);

        foreach ($order['orderProducts'] as $product) {
            $productXml     = $this->getGoodsXml([
                'externalCode' => '=' . $product['id']
            ]);
            $productXmlData = $this->parsingXmlDocument($productXml);
            if (!$productXmlData->attributes()->total->__toString()) {
                echo "На МойСкладе обнаружена несуществующая номенклатура товара. id = " . $product['id'] . PHP_EOL;

                return false;
            }

            $i = $resultXML->customerOrderPosition->count() - 1;

            // Add customer order position
            $resultXML->addChild('customerOrderPosition');
            $resultXML->customerOrderPosition[$i]->addAttribute('quantity', $product['quantity']);
            $resultXML->customerOrderPosition[$i]->addAttribute('goodUuid', $productXmlData->good->uuid->__toString());

            $productPrice = $product['priceTotal'] / $product['quantity'];

            // Add base price
            $resultXML->customerOrderPosition[$i]->addChild('basePrice');
            $resultXML->customerOrderPosition[$i]->basePrice->addAttribute('sum', $productPrice * 100);
            $resultXML->customerOrderPosition[$i]->basePrice->addAttribute('sumInCurrency', $productPrice * 100);

            // Add price
            $resultXML->customerOrderPosition[$i]->addChild('price');
            $resultXML->customerOrderPosition[$i]->price->addAttribute('sum', $productPrice * 100);
            $resultXML->customerOrderPosition[$i]->price->addAttribute('sumInCurrency', $productPrice * 100);
        }

        $url = '/exchange/rest/ms/xml/CustomerOrder/list/update';
        $XML = simplexml_load_string('<collection></collection>');

        $this->xmlAdopt($XML, $resultXML);
        $this->handleRequest($url, 'PUT', $XML->asXML());

        return true;
    }
}

