<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

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
     * Construct
     */
    public function __construct()
    {
        $this->name    = 'Moy Sklad';
        $this->tab     = 'moy_sklad';
        $this->version = '1.0';
        $this->author  = 'Andrew & Oleg';

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
