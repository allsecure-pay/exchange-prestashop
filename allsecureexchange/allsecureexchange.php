<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

/**
 * Class AllSecureExchange
 *
 * @extends PaymentModule
 */
class AllSecureExchange extends PaymentModule
{
    const ALL_SECURE_EXCHANGE_OS_STARTING = 'ALL_SECURE_EXCHANGE_OS_STARTING';
    const ALL_SECURE_EXCHANGE_OS_AWAITING = 'ALL_SECURE_EXCHANGE_OS_AWAITING';

    protected $config_form = false;

    public function __construct()
    {
        require_once(_PS_MODULE_DIR_ . 'allsecureexchange' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

        $this->name = 'allsecureexchange';
        $this->tab = 'payments_gateways';
        $this->version = '1.1.0';
        $this->author = 'AllSecure';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->is_eu_compatible = 1;
        $this->controllers = ['payment'];

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('AllSecure Exchange');
        $this->description = $this->l('Accept payments in your PrestaShop store using AllSecure Exchange Payment Gateway.');

        $this->confirmUninstall = $this->l('confirm_uninstall');

        //$this->limited_currencies = array('EUR');
    }

    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        if (!parent::install()
            || !$this->registerHook('paymentOptions')
            || !$this->registerHook('payment')
            || !$this->registerHook('displayAfterBodyOpeningTag')
            || !$this->registerHook('header')
        ) {
            return false;
        }

        $this->createOrderState(static::ALL_SECURE_EXCHANGE_OS_STARTING);
        $this->createOrderState(static::ALL_SECURE_EXCHANGE_OS_AWAITING);

        return true;
    }

    public function uninstall()
    {
        // TODO: delete Configuration
        // Configuration::deleteByName('ALL_SECURE_EXCHANGE_ENABLED');
        // Configuration::deleteByName('ALL_SECURE_EXCHANGE_ACCOUNT_USER');
        // Configuration::deleteByName('ALL_SECURE_EXCHANGE_ACCOUNT_PASSWORD');
        // Configuration::deleteByName('ALL_SECURE_EXCHANGE_HOST');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitAllSecureExchangeModule')) == true) {
            $form_values = $this->getConfigFormValues();
            foreach (array_keys($form_values) as $key) {
                $key = str_replace(['[', ']'], '', $key);
                $val = Tools::getValue($key);
                if (is_array($val)) {
                    $val = \json_encode($val);
                }
                if ($key == 'ALL_SECURE_EXCHANGE_HOST') {
                    $val = rtrim($val, '/') . '/';
                }
                Configuration::updateValue($key, $val);
            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAllSecureExchangeModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    private function getCreditCards()
    {
        return [
            'cc' => 'CreditCard',
            'visa' => 'Visa',
            'mastercard' => 'MasterCard',
            'amex' => 'Amex',
            'diners' => 'Diners',
            'jcb' => 'JCB',
            'discover' => 'Discover',
            'unionpay' => 'UnionPay',
            'maestro' => 'Maestro',
            // 'uatp' => 'UATP',
        ];
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $form = [
            'form' => [
                'tabs' => [
                    'General' => 'General',
                    'CreditCard' => 'CreditCard',
                ],
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'name' => 'ALL_SECURE_EXCHANGE_ENABLED',
                        'label' => $this->l('Enable'),
                        'tab' => 'General',
                        'type' => 'switch',
                        'is_bool' => 1,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => 'Enabled',
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => 'Disabled',
                            ],
                        ],
                    ],
                    [
                        'name' => 'ALL_SECURE_EXCHANGE_ACCOUNT_USER',
                        'label' => $this->l('User'),
                        'tab' => 'General',
                        'type' => 'text',
                    ],
                    [
                        'name' => 'ALL_SECURE_EXCHANGE_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                        'tab' => 'General',
                        'type' => 'text',
                    ],
					[
                        
						'name' => 'ALL_SECURE_EXCHANGE_HOST',
						'label' => $this->l('Host'),
						'tab' => 'General',
						'type' => 'select',
						'options' => [
							'query' => [
								['key' => 'https://asxgw.com', 'value' => 'LIVE'],
								['key' => 'https://asxgw.paymentsandbox.cloud', 'value' => 'TEST'],
							],
							'id' => 'key',
							'name' => 'value',
						],

                    ],
					
                    // [
                        // 'name' => 'ALL_SECURE_EXCHANGE_HOST',
                        // 'label' => $this->l('Host'),
                        // 'tab' => 'General',
                        // 'type' => 'text',
                    // ],

                    //                    [
                    //                        'type' => 'select',
                    //                        'name' => 'ALL_SECURE_EXCHANGE_CC_TYPES[]',
                    //                        'label' => $this->l('Credit Cards'),
                    //                        'multiple' => true,
                    //                        'options' => [
                    //                            'query' => [
                    //                                ['key' => 'visa', 'value' => 'Visa'],
                    //                                ['key' => 'mastercard', 'value' => 'MasterCard'],
                    //                                ['key' => 'dinersclub', 'value' => 'Dinersclub'],
                    //                                ['key' => 'americanexpress', 'value' => 'American Express'],
                    //                            ],
                    //                            'id' => 'key',
                    //                            'name' => 'value',
                    //                        ],
                    //                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        foreach ($this->getCreditCards() as $creditCard) {

            $prefix = strtoupper($creditCard);


            $form['form']['input'][] = [
                'name' => 'line',
                'type' => 'html',
                'tab' => 'CreditCard',
                'html_content' => '<h3 style="margin-top: 10px;">' . $creditCard . '</h3>',
            ];

            $form['form']['input'][] = [
                'name' => 'ALL_SECURE_EXCHANGE_' . $prefix . '_ENABLED',
                'label' => $this->l('Enable'),
                'tab' => 'CreditCard',
                'type' => 'switch',
                'is_bool' => 1,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => 'Enabled',
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => 'Disabled',
                    ],
                ],
            ];
            $form['form']['input'][] = [
                'name' => 'ALL_SECURE_EXCHANGE_' . $prefix . '_API_KEY',
                'label' => $this->l('API Key'),
                'tab' => 'CreditCard',
                'type' => 'text',
            ];
            $form['form']['input'][] = [
                'name' => 'ALL_SECURE_EXCHANGE_' . $prefix . '_SHARED_SECRET',
                'label' => $this->l('Shared Secret'),
                'tab' => 'CreditCard',
                'type' => 'text',
            ];
            $form['form']['input'][] = [
                'name' => 'ALL_SECURE_EXCHANGE_' . $prefix . '_INTEGRATION_KEY',
                'label' => $this->l('Integration Key'),
                'tab' => 'CreditCard',
                'type' => 'text',
            ];
            $form['form']['input'][] = [
                'name' => 'ALL_SECURE_EXCHANGE_' . $prefix . '_SEAMLESS',
                'label' => $this->l('Seamless Integration'),
                'tab' => 'CreditCard',
                'type' => 'switch',
                'is_bool' => 1,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => 'Enabled',
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => 'Disabled',
                    ],
                ],
            ];
            //            $form['form']['input'][] = [
            //                'name' => 'line',
            //                'type' => 'html',
            //                'tab' => 'CreditCard',
            //                'html_content' => '<hr>',
            //            ];
        }

        return $form;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $values = [
            'ALL_SECURE_EXCHANGE_ENABLED' => Configuration::get('ALL_SECURE_EXCHANGE_ENABLED', null),
            'ALL_SECURE_EXCHANGE_ACCOUNT_USER' => Configuration::get('ALL_SECURE_EXCHANGE_ACCOUNT_USER', null),
            'ALL_SECURE_EXCHANGE_ACCOUNT_PASSWORD' => Configuration::get('ALL_SECURE_EXCHANGE_ACCOUNT_PASSWORD', null),
            'ALL_SECURE_EXCHANGE_HOST' => Configuration::get('ALL_SECURE_EXCHANGE_HOST', null),
            //            'ALL_SECURE_EXCHANGE_CC_TYPES[]' => json_decode(Configuration::get('ALL_SECURE_EXCHANGE_CC_TYPES', null)),
        ];

        foreach ($this->getCreditCards() as $creditCard) {

            $prefix = strtoupper($creditCard);
            $values['ALL_SECURE_EXCHANGE_' . $prefix . '_ENABLED'] = Configuration::get('ALL_SECURE_EXCHANGE_' . $prefix . '_ENABLED', null);
            $values['ALL_SECURE_EXCHANGE_' . $prefix . '_API_KEY'] = Configuration::get('ALL_SECURE_EXCHANGE_' . $prefix . '_API_KEY', null);
            $values['ALL_SECURE_EXCHANGE_' . $prefix . '_SHARED_SECRET'] = Configuration::get('ALL_SECURE_EXCHANGE_' . $prefix . '_SHARED_SECRET', null);
            $values['ALL_SECURE_EXCHANGE_' . $prefix . '_INTEGRATION_KEY'] = Configuration::get('ALL_SECURE_EXCHANGE_' . $prefix . '_INTEGRATION_KEY', null);
            $values['ALL_SECURE_EXCHANGE_' . $prefix . '_SEAMLESS'] = Configuration::get('ALL_SECURE_EXCHANGE_' . $prefix . '_SEAMLESS', null);
        }

        return $values;
    }


    /**
     * Payment options hook
     *
     * @param $params
     * @return bool|void
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        $result = [];

        if (!Configuration::get('ALL_SECURE_EXCHANGE_ENABLED', null)) {
            return;
        }

        $years = [];
        $years[] = date('Y');
        for ($i = 1; $i <= 10; $i++) {
            $years[] = $years[0] + $i;
        }

        $this->context->smarty->assign([
            'months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            'years' => $years,
        ]);

        foreach ($this->getCreditCards() as $key => $creditCard) {

            $prefix = strtoupper($creditCard);

            if (!Configuration::get('ALL_SECURE_EXCHANGE_' . $prefix . '_ENABLED', null)) {
                continue;
            }

            $payment = new PaymentOption();
            $payment
                ->setModuleName($this->name)
                ->setCallToActionText($this->l($creditCard))
                ->setAction($this->context->link->getModuleLink($this->name, 'payment', ['type' => $creditCard], true));

            if (Configuration::get('ALL_SECURE_EXCHANGE_' . $prefix . '_SEAMLESS', null)) {

                $this->context->smarty->assign([
                    'paymentType' => $creditCard,
                    'id' => bin2hex(random_bytes(10)),
                    'action' => $payment->getAction(),
                    'integrationKey' => Configuration::get('ALL_SECURE_EXCHANGE_' . $prefix . '_INTEGRATION_KEY', null),
                ]);

                $payment->setInputs([['type' => 'input', 'name' => 'test', 'value' => 'value']]);

                $payment->setForm($this->fetch('module:allsecureexchange' . DIRECTORY_SEPARATOR . 'views' .
                    DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'front' . DIRECTORY_SEPARATOR . 'seamless.tpl'));

                //                $payment->setAdditionalInformation($this->fetch('module:allsecureexchange' . DIRECTORY_SEPARATOR . 'views' .
                //                    DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'front' . DIRECTORY_SEPARATOR . 'seamless.tpl'));
            }

            $payment->setLogo(
                Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/creditcard/'
                    . $key . '.png')
            );

            $result[] = $payment;
        }

        return count($result) ? $result : false;
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        if ($this->context->controller instanceof OrderControllerCore && $this->context->controller->page_name == 'checkout') {
            $uri = '/modules/allsecureexchange/views/js/front.js';
            $this->context->controller->registerJavascript(sha1($uri), $uri, ['position' => 'bottom']);
        }
    }

    public function hookDisplayAfterBodyOpeningTag()
    {
        if ($this->context->controller instanceof OrderControllerCore && $this->context->controller->page_name == 'checkout') {
            $host = Configuration::get('ALL_SECURE_EXCHANGE_HOST', null);
            return '<script data-main="payment-js" src="' . $host . 'js/integrated/payment.min.js"></script><script>window.allSecureExchangePayment = {};</script>';
        }

        return null;
    }

    /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookPayment($params)
    {
        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int)$currency_id);

        if (in_array($currency->iso_code, $this->limited_currencies) == false) {
            return false;
        }

        $this->smarty->assign('module_dir', $this->_path);

        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    private function createOrderState($stateName)
    {
        if (!\Configuration::get($stateName)) {
            $orderState = new \OrderState();
            $orderState->name = [];

            switch ($stateName) {
                case self::ALL_SECURE_EXCHANGE_OS_STARTING:
                    $names = [
                        'de' => 'AllSecure Exchange Bezahlung gestartet',
                        'en' => 'AllSecure Exchange payment started',
                    ];
                    break;
                case self::ALL_SECURE_EXCHANGE_OS_AWAITING:
                default:
                    $names = [
                        'de' => 'AllSecure Exchange Bezahlung ausständig',
                        'en' => 'AllSecure Exchange payment awaiting',
                    ];
                    break;
            }

            foreach (\Language::getLanguages() as $language) {
                if (\Tools::strtolower($language['iso_code']) == 'de') {
                    $orderState->name[$language['id_lang']] = $names['de'];
                } else {
                    $orderState->name[$language['id_lang']] = $names['en'];
                }
            }
            $orderState->invoice = false;
            $orderState->send_email = false;
            $orderState->module_name = $this->name;
            $orderState->color = '#076dc4';
            $orderState->hidden = false;
            $orderState->logable = true;
            $orderState->delivery = false;
            $orderState->add();

            \Configuration::updateValue(
                $stateName,
                (int)($orderState->id)
            );
        }
    }
}