<?php
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
