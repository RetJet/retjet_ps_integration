<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_ROOT_DIR_.'/classes/WebserviceKey.php');

class Retjet_Ps_Integration extends Module
{
    public function __construct()
    {
        $this->name = 'retjet_ps_integration';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'RetJet';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('RetJet Integration ');
        $this->description = $this->l('Module that generates and manages an API key for integration with the RetJet platform.');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install() && $this->registerHook('actionAdminControllerSetMedia') && $this->createApiKey();
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->deleteApiKey();
    }

    public function createApiKey()
    {
        if (!Configuration::get('RETJET_INTEGRATION_API_KEY')) {
            $apiKey = bin2hex(random_bytes(16));
            Configuration::updateValue('RETJET_INTEGRATION_API_KEY', $apiKey);
            $this->addWebserviceKey($apiKey);
        }
        return true;
    }

    public function deleteApiKey()
    {
        $apiKey = Configuration::get('RETJET_INTEGRATION_API_KEY');
        if ($apiKey) {
            $this->removeWebserviceKey($apiKey);
        }
        Configuration::deleteByName('RETJET_INTEGRATION_API_KEY');
        return true;
    }

    public function addWebserviceKey($apiKey)
    {
        $webserviceAccount = new WebserviceKey();
        $webserviceAccount->key = $apiKey;
        $webserviceAccount->description = 'RetJet Integration API Key';
        $webserviceAccount->active = 1;
        $webserviceAccount->save();

        // Assign to current shop
        $id_shop = (int)Context::getContext()->shop->id;
        Db::getInstance()->insert('webservice_account_shop', array(
            'id_webservice_account' => (int)$webserviceAccount->id,
            'id_shop' => $id_shop,
        ));

        // Set permissions for all resources
        $resources = WebserviceRequest::getResources();
        foreach ($resources as $resource) {
            Db::getInstance()->insert('webservice_permission', array(
                'resource' => pSQL($resource),
                'method' => 'GET',
                'id_webservice_account' => (int)$webserviceAccount->id,
            ));
            Db::getInstance()->insert('webservice_permission', array(
                'resource' => pSQL($resource),
                'method' => 'PUT',
                'id_webservice_account' => (int)$webserviceAccount->id,
            ));
            Db::getInstance()->insert('webservice_permission', array(
                'resource' => pSQL($resource),
                'method' => 'POST',
                'id_webservice_account' => (int)$webserviceAccount->id,
            ));
            Db::getInstance()->insert('webservice_permission', array(
                'resource' => pSQL($resource),
                'method' => 'DELETE',
                'id_webservice_account' => (int)$webserviceAccount->id,
            ));
        }
    }

    public function removeWebserviceKey($apiKey)
    {
        $webserviceAccount = WebserviceKey::getWebserviceKey($apiKey);
        if ($webserviceAccount && $webserviceAccount->id) {
            Db::getInstance()->delete('webservice_account_shop', 'id_webservice_account = '.(int)$webserviceAccount->id);
            Db::getInstance()->delete('webservice_permission', 'id_webservice_account = '.(int)$webserviceAccount->id);
            $webserviceAccount->delete();
        }
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submit'.$this->name)) {
            $this->createApiKey();
            $output .= $this->displayConfirmation($this->l('API Key generated successfully.'));
        } elseif (Tools::isSubmit('deleteApiKey')) {
            $this->deleteApiKey();
            $output .= $this->displayConfirmation($this->l('API Key deleted successfully.'));
        }

        return $output.$this->renderForm();
    }

    public function renderForm()
    {
        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->show_toolbar = false;
        $helper->toolbar_scroll = false;
        $helper->submit_action = 'submit'.$this->name;
        $helper->fields_value['api_key'] = Configuration::get('RETJET_INTEGRATION_API_KEY');

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('API Key Management'),
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('API Key'),
                        'name' => 'api_key',
                        'readonly' => true,
                        'desc' => $this->l('Your current API key.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Generate New API Key'),
                    'name' => 'submit'.$this->name,
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Delete API Key'),
                        'name' => 'deleteApiKey',
                        'type' => 'submit',
                        'class' => 'btn btn-default pull-right',
                    ),
                ),
            ),
        );

        return $helper->generateForm(array($fields_form));
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        $this->context->controller->addCSS($this->_path.'views/css/admin.css');
        $this->context->controller->addJS($this->_path.'views/js/admin.js');
    }
}