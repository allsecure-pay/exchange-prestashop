<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

/**
 * Class AllsecureExchange
 *
 * @extends PaymentModule
 */
class AllsecureExchange extends PaymentModule
{
    const ALLSECURE_EXCHANGE_OS_STARTING = 'ALLSECURE_EXCHANGE_OS_STARTING';
    const ALLSECURE_EXCHANGE_OS_AWAITING = 'ALLSECURE_EXCHANGE_OS_AWAITING';

    protected $config_form = false;

    public function __construct()
    {
        require_once(_PS_MODULE_DIR_ . 'allsecureexchange' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

        $this->name = 'allsecureexchange';
        $this->tab = 'payments_gateways';
        $this->version = '1.3.0';
        $this->author = 'AllSecure';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        $this->controllers = [
            'payment',
            'callback',
            'return',
        ];

        parent::__construct();

        $this->displayName = $this->l('AllSecure Exchange');
        $this->description = $this->l('Accept payments in your store using AllSecure Exchange Payment Gateway.');
        $this->confirmUninstall = $this->l('confirm_uninstall');
    }

    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        if (!parent::install()
            || !$this->registerHook('paymentOptions')
            || !$this->registerHook('displayPaymentReturn')
			|| !$this->registerHook('displayFooterAfter')
            || !$this->registerHook('payment')
            || !$this->registerHook('displayAfterBodyOpeningTag')
            || !$this->registerHook('header')
        ) {
            return false;
        }

        $this->createOrderState(static::ALLSECURE_EXCHANGE_OS_STARTING);
        $this->createOrderState(static::ALLSECURE_EXCHANGE_OS_AWAITING);

        // set default configuration
        Configuration::updateValue('ALLSECURE_EXCHANGE_HOST', 'https://asxgw.com/');
		Configuration::updateValue('ALLSECURE_EXCHANGE_BANNER', '0');
		Configuration::updateValue('ALLSECURE_EXCHANGE_CREDITCARDS_BANK', 'none');
		
        return true;
    }

    public function uninstall()
    {
        Configuration::deleteByName('ALLSECURE_EXCHANGE_ENABLED');
        Configuration::deleteByName('ALLSECURE_EXCHANGE_HOST');
		Configuration::deleteByName('ALLSECURE_EXCHANGE_BANNER');
		Configuration::deleteByName('ALLSECURE_EXCHANGE_CREDITCARDS_TYPE');	
        foreach ($this->getCreditCards() as $creditCard) {
			$prefix = strtoupper($creditCard);
            Configuration::deleteByName('ALLSECURE_EXCHANGE_' . $prefix . '_TITLE');
            Configuration::deleteByName('ALLSECURE_EXCHANGE_' . $prefix . '_ACCOUNT_USER');
            Configuration::deleteByName('ALLSECURE_EXCHANGE_' . $prefix . '_ACCOUNT_PASSWORD');
            Configuration::deleteByName('ALLSECURE_EXCHANGE_' . $prefix . '_API_KEY');
            Configuration::deleteByName('ALLSECURE_EXCHANGE_' . $prefix . '_SHARED_SECRET');
            Configuration::deleteByName('ALLSECURE_EXCHANGE_' . $prefix . '_INTEGRATION_KEY');
            Configuration::deleteByName('ALLSECURE_EXCHANGE_' . $prefix . '_SEAMLESS');
			Configuration::deleteByName('ALLSECURE_EXCHANGE_' . $prefix . '_TRANSACTION_TYPE');							   
			Configuration::deleteByName('ALLSECURE_EXCHANGE_' . $prefix . '_TYPE');
			
        }
		$this->unregisterHook('displayFooterAfter');
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitAllsecureExchangeModule')) == true) {
            $form_values = $this->getConfigFormValues();
            foreach (array_keys($form_values) as $key) {
                $key = str_replace(['[', ']'], '', $key);
                $val = Tools::getValue($key);
                if (is_array($val)) {
					$val = \json_encode($val);
				}
				if ($key == 'ALLSECURE_EXCHANGE_HOST') {
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
        $helper->submit_action = 'submitAllsecureExchangeModule';
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
        /**
         * Comment/disable adapters that are not applicable
         */
        return [
            'cc' => 'CreditCards',
            'ideal' => 'iDeal',
            'klarna' => 'Klarna',
            'sepadd' => 'DirectDebit',
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
                    'PaymentMethods' => 'Payment Methods',
                ],
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'name' => 'ALLSECURE_EXCHANGE_ENABLED',
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
                    // [
                        // 'name' => 'ALLSECURE_EXCHANGE_HOST',
                        // 'label' => $this->l('Host'),
                        // 'tab' => 'General',
                        // 'type' => 'text',
                    // ],
					[
					'name' => 'ALLSECURE_EXCHANGE_HOST',
					'label' => $this->l('Host'),
					'type' => 'select',
					'tab' => 'General',
					'options'  =>  [
						'query' => [
							['id'   => 'https://asxgw.com/', 'name' => $this->l('LIVE')],
							['id'   => 'https://asxgw.paymentsandbox.cloud/', 'name' => $this->l('TEST')],
						],
						'id'    => 'id',
						'name'  => 'name',
						],
					],
					[
                        'name' => 'ALLSECURE_EXCHANGE_BANNER',
                        'label' => $this->l('Banner'),
                        'tab' => 'General',
                        'type' => 'switch',
						'is_bool' => 1,
						'values' => [
							['id' => 'active_on', 'value' => 1,	'label' => 'Enabled',],
							['id' => 'active_off', 'value' => 0, 'label' => 'Disabled',],
						],
                    ],
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
                'tab' => 'PaymentMethods',
                'html_content' => '<h3 style="margin-top: 10px;">' . $creditCard . '</h3>',
            ];

            $form['form']['input'][] = [
                'name' => 'ALLSECURE_EXCHANGE_' . $prefix . '_ENABLED',
                'label' => $this->l('Enable'),
                'tab' => 'PaymentMethods',
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
                'name' => 'ALLSECURE_EXCHANGE_' . $prefix . '_TITLE',
                'label' => $this->l('Title'),
                'tab' => 'PaymentMethods',
                'type' => 'text',
            ];
            $form['form']['input'][] = [
                'name' => 'ALLSECURE_EXCHANGE_' . $prefix . '_ACCOUNT_USER',
                'label' => $this->l('User'),
                'tab' => 'PaymentMethods',
                'type' => 'text',
            ];
            $form['form']['input'][] = [
                'name' => 'ALLSECURE_EXCHANGE_' . $prefix . '_ACCOUNT_PASSWORD',
                'label' => $this->l('Password'),
                'tab' => 'PaymentMethods',
                'type' => 'text',
            ];
            $form['form']['input'][] = [
                'name' => 'ALLSECURE_EXCHANGE_' . $prefix . '_API_KEY',
                'label' => $this->l('API Key'),
                'tab' => 'PaymentMethods',
                'type' => 'text',
            ];
            $form['form']['input'][] = [
                'name' => 'ALLSECURE_EXCHANGE_' . $prefix . '_SHARED_SECRET',
                'label' => $this->l('Shared Secret'),
                'tab' => 'PaymentMethods',
                'type' => 'text',
            ];
            
			$form['form']['input'][] = [
              'name' => 'line',
              'type' => 'html',
              'tab' => 'PaymentMethods',
              'html_content' => '<hr>',
            ];
                        
			if ($prefix=='CREDITCARDS') {
				$form['form']['input'][] = [
					'name' => 'ALLSECURE_EXCHANGE_' . $prefix . '_SEAMLESS',
					'label' => $this->l('Seamless Integration'),
					'tab' => 'PaymentMethods',
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
					'name' => 'ALLSECURE_EXCHANGE_' . $prefix . '_INTEGRATION_KEY',
					'label' => $this->l('Integration Key'),
					'tab' => 'PaymentMethods',
					'type' => 'text',
				];
				$form['form']['input'][] = [
					'name' => 'ALLSECURE_EXCHANGE_' . $prefix . '_TYPE',
					'label' => $this->l('Cards'),
					'type' => 'select',
					'multiple' => true,
					'required' =>  true,
					'tab' => 'PaymentMethods',
					'options' => [
						'query' => [
							['id' => 'visa', 'name' => $this->l('Visa')],
							['id' => 'mastercard', 'name' => $this->l('MasterCard')],
							['id' => 'maestro', 'name' => $this->l('Maestro')],
							['id' => 'diners', 'name' => $this->l('DinersClub')],
							['id' => 'amex', 'name' => $this->l('American Express')],
							['id' => 'jcb', 'name' => $this->l('JCB')],
							['id' => 'discover', 'name' => $this->l('Discover')],
						],
						'id'    => 'id',
						'name'  => 'name'
					],
				];
			
            	$form['form']['input'][] = [
					'name' => 'ALLSECURE_EXCHANGE_' . $prefix . '_BANK',
					'label' => $this->l('Acquirer'),
					'type' => 'select',
					'tab' => 'PaymentMethods',
					'required' =>  true,
					'options'  =>  [
						'query' => [ 
							['id'   => 'null',  'name' => $this->l('---')],
							['id'   => 'aik', 'name' => $this->l('AIK Bank')],
							['id'   => 'bib',  'name' => $this->l('Banca Intesa')],
							['id'   => 'hbm',  'name' => $this->l('Hipotekarna Banka')],
							['id'   => 'payv',  'name' => $this->l('PayVision')],
							['id'   => 'ucb',  'name' => $this->l('UniCredit Bank')],
							['id'   => 'wcd',  'name' => $this->l('WireCard Bank')],
						],
						'id'    => 'id',
						'name'  => 'name'
					],
				];
						
				$form['form']['input'][] = [
					'name' => 'ALLSECURE_EXCHANGE_' . $prefix . '_TRANSACTION_TYPE',
					'label' => $this->l('Transaction Type'),
					'type' => 'select',
					'tab' => 'PaymentMethods',
					'required' =>  true,
					'options'  =>  [
						'query' => [ 
							['id'   => 'PREAUTHORIZE', 'name' => $this->l('Preauth')],
							['id'   => 'DEBIT',  'name' => $this->l('Debit')],
						],
						'id'    => 'id',
						'name'  => 'name'
					],
				];
			}
        }

        return $form;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $values = [
            'ALLSECURE_EXCHANGE_ENABLED' => Configuration::get('ALLSECURE_EXCHANGE_ENABLED', null),
            'ALLSECURE_EXCHANGE_HOST' => Configuration::get('ALLSECURE_EXCHANGE_HOST', null),
			'ALLSECURE_EXCHANGE_BANNER' => Configuration::get('ALLSECURE_EXCHANGE_BANNER', null),
			'ALLSECURE_EXCHANGE_CREDITCARDS_BANK' => Configuration::get('ALLSECURE_EXCHANGE_CREDITCARDS_BANK', null),
        ];

        foreach ($this->getCreditCards() as $creditCard) {
            $prefix = strtoupper($creditCard);
            $values['ALLSECURE_EXCHANGE_' . $prefix . '_ENABLED'] = Configuration::get('ALLSECURE_EXCHANGE_' . $prefix . '_ENABLED', null);
            $values['ALLSECURE_EXCHANGE_' . $prefix . '_TITLE'] = Configuration::get('ALLSECURE_EXCHANGE_' . $prefix . '_TITLE') ?: $creditCard;
            $values['ALLSECURE_EXCHANGE_' . $prefix . '_ACCOUNT_USER'] = Configuration::get('ALLSECURE_EXCHANGE_' . $prefix . '_ACCOUNT_USER', null);
            $values['ALLSECURE_EXCHANGE_' . $prefix . '_ACCOUNT_PASSWORD'] = Configuration::get('ALLSECURE_EXCHANGE_' . $prefix . '_ACCOUNT_PASSWORD', null);
            $values['ALLSECURE_EXCHANGE_' . $prefix . '_API_KEY'] = Configuration::get('ALLSECURE_EXCHANGE_' . $prefix . '_API_KEY', null);
            $values['ALLSECURE_EXCHANGE_' . $prefix . '_SHARED_SECRET'] = Configuration::get('ALLSECURE_EXCHANGE_' . $prefix . '_SHARED_SECRET', null);
            $values['ALLSECURE_EXCHANGE_' . $prefix . '_INTEGRATION_KEY'] = Configuration::get('ALLSECURE_EXCHANGE_' . $prefix . '_INTEGRATION_KEY', null);
            $values['ALLSECURE_EXCHANGE_' . $prefix . '_SEAMLESS'] = Configuration::get('ALLSECURE_EXCHANGE_' . $prefix . '_SEAMLESS', null);
			$values['ALLSECURE_EXCHANGE_' . $prefix . '_TRANSACTION_TYPE'] = Configuration::get('ALLSECURE_EXCHANGE_' . $prefix . '_TRANSACTION_TYPE', null);
			$values['ALLSECURE_EXCHANGE_' . $prefix . '_TYPE[]'] = json_decode(Configuration::get('ALLSECURE_EXCHANGE_' . $prefix . '_TYPE'), true);
        }

        return $values;
    }

    /**
     * Payment options hook
     *
     * @param $params
     * @throws Exception
     * @return bool|void
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->active || !Configuration::get('ALLSECURE_EXCHANGE_ENABLED', null)) {
            return;
        }

        $result = [];

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

            if (!Configuration::get('ALLSECURE_EXCHANGE_' . $prefix . '_ENABLED', null)) {
                continue;
            }

            $payment = new PaymentOption();
            $payment
                ->setModuleName($this->name)
                ->setCallToActionText($this->l(Configuration::get('ALLSECURE_EXCHANGE_' . $prefix . '_TITLE', null)))
                ->setAction($this->context->link->getModuleLink($this->name, 'payment', [
                        'type' => $creditCard,
                    ], true));

            if (Configuration::get('ALLSECURE_EXCHANGE_' . $prefix . '_SEAMLESS', null)) {
				$selectedCards = Configuration::get('ALLSECURE_EXCHANGE_CREDITCARDS_TYPE', null);
				$this->context->smarty->assign([
                    'paymentType' => $creditCard,
                    'id' => 'p' . bin2hex(random_bytes(10)),
                    'action' => $payment->getAction(),
                    'integrationKey' => Configuration::get('ALLSECURE_EXCHANGE_' . $prefix . '_INTEGRATION_KEY', null),
					'allowedCards' => json_decode($selectedCards, true),
					'this_path' => _MODULE_DIR_.$this->name,
                ]);

                $payment->setInputs([['type' => 'input', 'name' => 'test', 'value' => 'value']]);

                $payment->setForm($this->fetch('module:allsecureexchange' . DIRECTORY_SEPARATOR . 'views' .
                    DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'front' . DIRECTORY_SEPARATOR . 'seamless.tpl'));
            }

            $payment->setLogo(
                Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/'.strtoupper($creditCard).'/' . $key . '.png')
            );

            $result[] = $payment;
        }

        return count($result) ? $result : false;
    }

    public function hookDisplayPaymentReturn($params)
    {
        if (!$this->active || !Configuration::get('ALLSECURE_EXCHANGE_ENABLED', null)) {
            return;
        }

        return $this->display(__FILE__, 'views/templates/hook/payment_return.tpl');
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
        $this->context->controller->addCSS(($this->_path).'views/css/front.css', 'all');
		if ($this->context->controller instanceof OrderControllerCore && $this->context->controller->page_name == 'checkout') {
		    $uri = '/modules/allsecureexchange/views/js/front.js';
            $this->context->controller->registerJavascript(sha1($uri), $uri, ['position' => 'bottom']);
        }
    }

    public function hookDisplayAfterBodyOpeningTag()
    {
        if ($this->context->controller instanceof OrderControllerCore && $this->context->controller->page_name == 'checkout') {
            $host = Configuration::get('ALLSECURE_EXCHANGE_HOST', null);
            return '<script data-main="payment-js" src="' . $host . 'js/integrated/payment.min.js"></script><script>window.allsecureExchangePayment = {};</script>';
        }

        return null;
    }
	
	/**
     * the PrestaShop hook to display a banner at the footer
     * @return boolean
     */
    public function hookdisplayFooterAfter()
     {
       $selectedBrands = json_decode(Configuration::get('ALLSECURE_EXCHANGE_CREDITCARDS_TYPE'), true);  
	   $selectedBank = Configuration::get('ALLSECURE_EXCHANGE_CREDITCARDS_BANK');
	   if (Configuration::get('ALLSECURE_EXCHANGE_BANNER') == 1){
		   echo	'<div id="allsecure_exchange_banner">
				<div class="allsecure">
					<img src="'. _MODULE_DIR_.$this->name.'/views/img/allsecure.svg">
				</div>
				<div class="allsecure_exchange_threeds">
					<img src="'. _MODULE_DIR_.$this->name.'/views/img/3dvbv.svg">
					<img src="'. _MODULE_DIR_.$this->name.'/views/img/3dmcsc.svg">
				</div>
				<div class="allsecure_exchange_cards">';
			if (!empty($selectedBrands)) {
				foreach ($selectedBrands as $ccBrand) {
					echo '<img src="'. _MODULE_DIR_.$this->name.'/views/img/creditcard/'. strtolower($ccBrand).'.svg">';
				 }
			 }
		 echo '</div><div class="allsecure_exchange_bank"><img src="'. _MODULE_DIR_.$this->name.'/views/img/creditcard/'.$selectedBank.'.svg"></div></div>';
		}
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
                case self::ALLSECURE_EXCHANGE_OS_STARTING:
                    $names = [
                        'de' => 'AllSecure Exchange Bezahlung gestartet',
                        'en' => 'AllSecure Exchange payment started',
                    ];
                    break;
                case self::ALLSECURE_EXCHANGE_OS_AWAITING:
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
            $orderState->logable = false;
            $orderState->delivery = false;
            $orderState->add();

            \Configuration::updateValue(
                $stateName,
                (int)($orderState->id)
            );
        }
    }
}
