<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class MyStore extends Module
{
    /**
     * @var boolean $_errors error
     */
    protected $_errors = false;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->name    = 'mystore';
        $this->tab     = 'my_store';
        $this->version = '1.0';
        $this->author  = 'Andrew';

        parent::__construct();

        $this->displayName = $this->l('Moy sklad');
        $this->description = $this->l('Module unknown.');
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
}
