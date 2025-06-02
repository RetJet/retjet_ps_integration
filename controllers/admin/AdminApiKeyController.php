<?php
/**
 * Admin API Key Controller for RetJet PrestaShop Integration Module
 *
 * This controller handles the management of API keys for the RetJet PrestaShop Integration module
 * in the admin panel.
 *
 * @author    RetJet
 * @copyright Copyright (c) RetJet
 * @license   GNU General Public License v3
 * @version   1.0.0
 * @link      https://www.retjet.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminApiKeyController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent()
    {
        $this->context->smarty->assign('api_key', Configuration::get('RETJET_INTEGRATION_API_KEY'));
        parent::initContent();
        $this->setTemplate('api_key.tpl');
    }

    public function postProcess()
    {
        if (Tools::isSubmit('generate_api_key')) {
            $apiKey = bin2hex(random_bytes(16));
            Configuration::updateValue('RETJET_INTEGRATION_API_KEY', $apiKey);
            $this->module->addWebserviceKey($apiKey);
            $this->confirmations[] = $this->module->l('API Key generated successfully.');
        } elseif (Tools::isSubmit('delete_api_key')) {
            $apiKey = Configuration::get('RETJET_INTEGRATION_API_KEY');
            if ($apiKey) {
                $this->module->removeWebserviceKey($apiKey);
                Configuration::deleteByName('RETJET_INTEGRATION_API_KEY');
                $this->confirmations[] = $this->module->l('API Key deleted successfully.');
            }
        }
    }
}
