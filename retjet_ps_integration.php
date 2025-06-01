<?php
/**
 * RetJet PrestaShop Integration Module
 *
 * This file contains the main module class for the RetJet PrestaShop Integration module.
 * It handles module installation, uninstallation, and configuration.
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



class Retjet_Ps_Integration extends Module
{
    private $rjBaseUrl = 'https://app.retjet.com';
    private $integrationBaseUri = '/panel/sales_channel/add?data=';
    private $integrationBaseUrl = '';

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
        $this->integrationBaseUrl = $this->rjBaseUrl.$this->integrationBaseUri;
    }

    public function install()
    {
        return parent::install()
                && $this->createApiKey()
                && $this->registerHook('displayFooter')
                && $this->installConfiguration();
    }

    public function uninstall()
    {
        return parent::uninstall()
                && $this->deleteApiKey()
                && $this->unregisterHook('displayFooter')
                && $this->uninstallConfiguration();
    }

    private function installConfiguration()
    {
        return Configuration::updateValue('RETJET_COMPANY_ID', '');
    }

    private function uninstallConfiguration()
    {
        return Configuration::deleteByName('RETJET_COMPANY_ID');
    }

    public function hookDisplayFooter($params)
    {
        $controller = $this->context->controller;
        if ($controller->php_self === 'cms') {
            $companyId = Configuration::get('RETJET_COMPANY_ID');
            $lang = $this->context->language->iso_code;
            $this->context->smarty->assign(array(
                'companyId' => $companyId,
                'lang' => $lang
            ));
            return $this->display(__FILE__, 'views/templates/hook/displayFooter.tpl');
        }
    }

    public function getContent()
    {
        if (Tools::isSubmit('generate_api_key')) {
            $this->createApiKey();
            $this->context->cookie->__set('confirmations', $this->l('API Key generated successfully.'));
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
        } elseif (Tools::isSubmit('delete_api_key')) {
            $this->deleteApiKey();
            $this->context->cookie->__set('confirmations', $this->l('API Key deleted successfully.'));
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
        }
        if (Tools::isSubmit('submitRetJetConfig')) {
            $companyId = Tools::getValue('RETJET_COMPANY_ID');
            Configuration::updateValue('RETJET_COMPANY_ID', $companyId);
            //$output .= $this->displayConfirmation($this->l('Settings updated.'));
            $this->context->cookie->__set('confirmations', $this->l('Settings updated.'));
        }

        $confirmations = $this->context->cookie->__get('confirmations');
        if ($confirmations) {
            $this->context->smarty->assign('confirmations', array($confirmations));
            $this->context->cookie->__unset('confirmations');
        }

        return $this->renderForm();
    }


    public function renderForm()
    {
        $apiKey = Configuration::get('RETJET_INTEGRATION_API_KEY');

        // check if key exists
        if ($apiKey) {
            $id_webservice_account = Db::getInstance()->getValue('SELECT `id_webservice_account` FROM `'._DB_PREFIX_.'webservice_account` WHERE `key` = \''.pSQL($apiKey).'\'');
            if (!$id_webservice_account) {
                // if not exists, remove from configuration
                Configuration::deleteByName('RETJET_INTEGRATION_API_KEY');
                $apiKey = null;
            }
        }

        $integrationUrl = $this->getIntegrationUrl($apiKey);

        $this->context->smarty->assign(array(
            'api_key' => $apiKey,
            'company_id' => Configuration::get('RETJET_COMPANY_ID'),
            'form_action' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'integration_url' => $integrationUrl,
            'rj_base_url' => $this->rjBaseUrl
        ));

        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
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
        $id_webservice_account = (int)$webserviceAccount->id;

        // Check if the entry already exists to avoid duplicate entries
        $exists = Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'webservice_account_shop WHERE id_webservice_account = '.$id_webservice_account.' AND id_shop = '.$id_shop);

        if (!$exists) {
            Db::getInstance()->insert('webservice_account_shop', array(
                'id_webservice_account' => $id_webservice_account,
                'id_shop' => $id_shop,
            ));
        }

        // Set permissions for all resources
        $resources = WebserviceRequest::getResources();
        foreach ($resources as $resource => $values) {
            Db::getInstance()->insert('webservice_permission', array(
                'resource' => pSQL($resource),
                'method' => 'GET',
                'id_webservice_account' => $id_webservice_account,
            ));
            Db::getInstance()->insert('webservice_permission', array(
                'resource' => pSQL($resource),
                'method' => 'PUT',
                'id_webservice_account' => $id_webservice_account,
            ));
            Db::getInstance()->insert('webservice_permission', array(
                'resource' => pSQL($resource),
                'method' => 'POST',
                'id_webservice_account' => $id_webservice_account,
            ));
            Db::getInstance()->insert('webservice_permission', array(
                'resource' => pSQL($resource),
                'method' => 'DELETE',
                'id_webservice_account' => $id_webservice_account,
            ));
        }
    }

    public function removeWebserviceKey($apiKey)
    {
        $id_webservice_account = Db::getInstance()->getValue('SELECT `id_webservice_account` FROM `'._DB_PREFIX_.'webservice_account` WHERE `key` = \''.pSQL($apiKey).'\'');

        if ($id_webservice_account) {
            Db::getInstance()->delete('webservice_account_shop', 'id_webservice_account = '.(int)$id_webservice_account);
            Db::getInstance()->delete('webservice_permission', 'id_webservice_account = '.(int)$id_webservice_account);
            Db::getInstance()->delete('webservice_account', 'id_webservice_account = '.(int)$id_webservice_account);
        }
    }

    public function getIntegrationUrl($apiKey)
    {
        $shopDomain = Tools::getShopDomainSsl(true, true);
        $parsedUrl = parse_url($shopDomain);
        $domain = isset($parsedUrl['host']) ? $parsedUrl['host'] : $shopDomain;

        $domain = rtrim($domain, '/');

        $formName = $domain . ' Auto-Configuration';
        $formLabel = $domain . ' Auto-Configuration by RetJet Module';

        $data = array(
            'form_name' => $formName,
            'form_label' => $formLabel,
            'form_channel_type' => 'prestashop',
            'form_api_endpoint_url' => $shopDomain.'/api/',
            'form_api_auth_type' => 'bearer',
            'form_api_key' => $apiKey
        );

        $json_data = json_encode($data);
        $encoded_data = urlencode($json_data);

        return $this->integrationBaseUrl . $encoded_data;
    }
}