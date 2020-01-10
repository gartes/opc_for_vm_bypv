<?php defined( '_JEXEC' ) or die( 'Restricted access' );
	
	/**
	 * Plugin: One Page Checkout for VirtueMart byPV
	 * Copyright (C) 2014 byPV.org <info@bypv.org>
	 *
	 * This program is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation, either version 3 of the License, or
	 * (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
	 */

// if (!class_exists('VirtueMartViewCart')) require(JPATH_VM_SITE . DS . 'views' . DS . 'cart' . DS . 'view.html.php');
	if ( !class_exists( 'VmView' ) ) require( JPATH_VM_SITE . DS . 'helpers' . DS . 'vmview.php' );

// class VirtueMartViewCart_byPV extends VirtueMartViewCart
	class VirtueMartViewCart_byPV extends VmView
	{
		// Form Template constants
		
		const TPL_PRODUCT_LIST = 'product_list';
		const TPL_COUPON_CODE = 'coupon_code';
		const TPL_SHIPMENTS = 'shipments';
		const TPL_PAYMENTS = 'payments';
		const TPL_CUSTOMER_TYPE_SELECT = 'customer_type_select';
		const TPL_LOGIN = 'login';
		const TPL_BILLING_ADDRESS = 'billing_address';
		const TPL_SHIPPING_ADDRESS = 'shipping_address';
		const TPL_SHIPPING_ADDRESS_SELECT = 'shipping_address_select';
		const TPL_ADVERTISEMENTS = 'advertisements';
		const TPL_CART_FIELDS = 'cart_fields';
		const TPL_COMMENT = 'comment';
		const TPL_TOS = 'tos';
		const TPL_EXTERNAL_MODULES = 'external_modules';
		
		public static $FORM_TEMPLATES = [
			self::TPL_PRODUCT_LIST            => '',
			self::TPL_COUPON_CODE             => '',
			self::TPL_SHIPMENTS               => '',
			self::TPL_PAYMENTS                => '',
			self::TPL_CUSTOMER_TYPE_SELECT    => '',
			self::TPL_LOGIN                   => '',
			self::TPL_BILLING_ADDRESS         => '',
			self::TPL_SHIPPING_ADDRESS        => '',
			self::TPL_SHIPPING_ADDRESS_SELECT => '',
			self::TPL_ADVERTISEMENTS          => '',
			self::TPL_CART_FIELDS             => '',
			self::TPL_COMMENT                 => '',
			self::TPL_TOS                     => '',
			self::TPL_EXTERNAL_MODULES        => '',
		];
		
		private $bypv_form_checksum = [];
		private $form_template_initialization_js = [];
		private $form_template_cache = [];
		private $loaded_form_templates = [];
		
		private $layout_html = null;
		private $layout_css = null;
		
		/*** OVERRIDE ***/
		
		public function __construct ( $config = [] )
		{
			$config[ 'base_path' ] = OPC_FOR_VM_BYPV_PLUGIN_PATH;
			
			parent::__construct( $config );
			
			// Preserve VirtueMart Cart path for non-checkout templates.
			
			$app                         = JFactory::getApplication();
			$this->_path[ 'template' ][] = JPATH_THEMES . DS . $app->getTemplate() . DS . 'html' . DS . 'com_virtuemart' . DS . 'cart';
			$this->_path[ 'template' ][] = JPATH_VM_SITE . DS . 'views' . DS . 'cart' . DS . 'tmpl';
			
			// Initialize Form Checksum
			
			$input             = JFactory::getApplication()->input;
			$form_checksum_old = $input->getString( 'bypv_form_checksum' );
			
			if ( !empty( $form_checksum_old ) )
			{
				$this->bypv_form_checksum = json_decode( base64_decode( $form_checksum_old ), true );
				
				if ( !is_array( $this->bypv_form_checksum ) )
				{
					$this->bypv_form_checksum = [];
				}
			}
			
			// Layout
			
			$plugin_layout = plgSystemOPC_for_VM_byPV::getPluginParam( 'plugin_layout' );
			
			if ( strpos( $plugin_layout, '::' ) === false ) $this->layout_html = $plugin_layout;
			else list( $this->layout_html, $this->layout_css ) = explode( '::', $plugin_layout );
		}
		
		
		/**
		 * @param null $tpl
		 *
		 * @return string
		 * @throws Exception
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 03.11.18
		 */
		public function loadTemplate ( $tpl = null )
		{
			$layout = $this->getLayout();
			
			
			/*
			if ( $form_tpl == 'billing_address'){
				echo'<pre>';print_r( $html );echo'</pre>'.__FILE__.' '.__LINE__;
				
			}*/
			
			if ( in_array( $layout, [ 'default', $this->layout_html ] ) )
			{
				$layout = $this->layout_html;
			}
			
			
			$previousLayout = $this->setLayout( $layout );
			
			
			$html = parent::loadTemplate( $tpl );
			
			
			$this->setLayout( $previousLayout );
			
			return $html;
		}
		
		/**
		 * @param null $tpl
		 *
		 * @return mixed|string|void
		 * @throws Exception
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 19.11.18
		 */
		public function display ( $tpl = null )
		{
			$app      = JFactory::getApplication();
			$document = JFactory::getDocument();
			
			$pathway = $app->getPathway();
			
			$layoutName = $this->getLayout();
			if ( !$layoutName )
			{
				$layoutName = $app->input->get( 'layout', 'default', 'WORD' );
			}#END IF
			
			$useSSL = VmConfig::get( 'useSSL', 0 );
			$this->assignRef( 'useSSL', $useSSL );
			$useXHTML = true;
			$this->assignRef( 'useXHTML', $useXHTML );
			
			
			
			
			
			#For page CART THANKYOU
			if ( $this->_layout == 'order' || $app->input->get('task' , false , 'WORD') == 'order' )
			{
				
				$sesion = JFactory::getSession();
				
				$modelOrder = VmModel::getModel( 'orders' );
				
				
				$document->setTitle( JText::_( 'COM_VIRTUEMART_CART_THANKYOU' ) );
				$pathway->addItem( JText::_( 'COM_VIRTUEMART_CART_THANKYOU' ) );
				$this->display_title = 1;
				
				
				$order = $sesion->get( 'orderDonnePage', [], 'cart_bypv' );
				
				
				
				$payment_name = $shipment_name = '';
				if ( !class_exists( 'vmPSPlugin' ) ) require( VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php' );
				
				
				$this->html = $order[ 'hrml' ];
				$app->input->set('html' ,  $order[ 'hrml' ] ) ;
				
				$this->setLayout( 'order_done' );
				
				
				parent::display( $tpl );
				$this->setDocument_byPV();
				
				return;
			}#END IF
			
			
			$cart_bypv = VirtueMartCart_byPV::getCart();
			$cart_bypv->detectChangesInUserFieldsData();
			
			// We don't want validate cart when show checkout
			$cart            = VirtueMartCart::getCart();
			$cart->_redirect = true;
			// @since VM 2.0.26a changed condition in parent::display() from _redirect to _inCheckOut
			$cart->_inCheckOut = true;
			
			$cart_bypv->fixShipment();
			$cart_bypv->fixPayment();
			
			if ( VM_VERSION == 3 )
			{
				$cart->prepareCartData();
			}#END IF
			
			
			$this->assignRef( 'layoutName', $layoutName );
			$this->assignRef( 'cart', $cart );
			
			VmConfig::loadJLang( 'com_virtuemart_shoppers', true );
			
			if ( $layoutName == 'order_done' )
			{
				$display_title = JRequest::getBool( 'display_title', true );
				$this->assignRef( 'display_title', $display_title );
				
				$html = JRequest::getVar( 'html', JText::_( 'COM_VIRTUEMART_ORDER_PROCESSED' ), 'default', 'STRING', JREQUEST_ALLOWRAW );
				$this->assignRef( 'html', $html );
				
				$document->setTitle( JText::_( 'COM_VIRTUEMART_CART_THANKYOU' ) );
				
				$pathway->addItem( JText::_( 'COM_VIRTUEMART_CART_THANKYOU' ) );
				
				/*echo '<pre>'; print_r ( $pathway ); echo '</pre>'.__FILE__.' in line:  '.__LINE__ ;
				 die(__FILE__.' in line '.__LINE__);*/
				
			}
			else
			{
				if ( VM_VERSION < 3 )
				{
					$cart->prepareCartViewData();
					
					if ( VmConfig::get( 'enable_content_plugin', 0 ) )
					{
						shopFunctionsF::triggerContentPlugin( $cart->vendor, 'vendor', 'vendor_terms_of_service' );
					}
				}
				else
				{
					$cart->prepareVendor();
				}
				
				// Continue Link
				
				$lastVisitedCategoryId = shopFunctionsF::getLastVisitedCategoryId();
				$categoryQueryParam    = ( !empty( $lastVisitedCategoryId ) ? '&virtuemart_category_id=' . $lastVisitedCategoryId : '' );
				
				if ( method_exists( 'shopFunctionsF', 'getLastVisitedItemId' ) )
				{
					$lastVisitedItemid = shopFunctionsF::getLastVisitedItemId();
				}
				$itemQueryParam = ( !empty( $lastVisitedItemid ) ? '&Itemid=' . $lastVisitedItemid : '' );
				
				$this->continue_link      = JRoute::_( 'index.php?option=com_virtuemart&view=category' . $categoryQueryParam . $itemQueryParam, false );
				$this->continue_link_html = '<a class="continue_link" href="' . $this->continue_link . '" >' . JText::_( 'COM_VIRTUEMART_CONTINUE_SHOPPING' ) . '</a>';
				
				$this->cart_link = JRoute::_( 'index.php?option=com_virtuemart&view=cart' . $itemQueryParam, false );
				
				// Coupon Code
				
				$this->couponCode  = ( isset( $this->cart->couponCode ) ? $this->cart->couponCode : '' );
				$this->coupon_text = JText::_( 'COM_VIRTUEMART_COUPON_CODE_' . ( empty( $this->cart->couponCode ) ? 'ENTER' : 'CHANGE' ) );
				
				// Currency
				
				if ( !class_exists( 'CurrencyDisplay' ) ) require( JPATH_VM_ADMINISTRATOR . '/helpers/currencydisplay.php' );
				$currencyDisplay = CurrencyDisplay::getInstance( $this->cart->pricesCurrency );
				$this->assignRef( 'currencyDisplay', $currencyDisplay );
				
				// Custom Fields
				
				if ( VM_VERSION == 3 )
				{
					$customfieldsModel = VmModel::getModel( 'Customfields' );
					$this->assignRef( 'customfieldsModel', $customfieldsModel );
				}
				
				// Total In Payment Currency
				
				if ( empty( $this->cart->virtuemart_paymentmethod_id ) )
				{
					$totalInPaymentCurrency = null;
				}
				else if ( !$this->cart->paymentCurrency || $this->cart->paymentCurrency == $this->cart->pricesCurrency )
				{
					$totalInPaymentCurrency = null;
				}
				else
				{
					$paymentCurrency = CurrencyDisplay::getInstance( $this->cart->paymentCurrency );
					
					$totalInPaymentCurrency = $paymentCurrency->priceDisplay(
						$this->cart->pricesUnformatted[ 'billTotal' ],
						$this->cart->paymentCurrency
					);
					
					$currencyDisplay = CurrencyDisplay::getInstance( $this->cart->pricesCurrency );
				}
				
				$this->assignRef( 'totalInPaymentCurrency', $totalInPaymentCurrency );
				
				// Checkout Advertise
				
				JPluginHelper::importPlugin( 'vmcoupon' );
				JPluginHelper::importPlugin( 'vmshipment' );
				JPluginHelper::importPlugin( 'vmpayment' );
				
				$checkoutAdvertise = [];
				
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger( 'plgVmOnCheckoutAdvertise', [ $this->cart, &$checkoutAdvertise ] );
				
				$this->assignRef( 'checkoutAdvertise', $checkoutAdvertise );
				
				/////
				
				$cart_bypv->fixShipment();
				$cart_bypv->fixPayment();
				
				// Validation
				
				if ( $cart->getDataValidated() )
				{
					$pathway->addItem( JText::_( 'COM_VIRTUEMART_ORDER_CONFIRM_MNU' ) );
					$document->setTitle( JText::_( 'COM_VIRTUEMART_ORDER_CONFIRM_MNU' ) );
					$checkout_task = 'confirm';
				}
				else
				{
					$pathway->addItem( JText::_( 'COM_VIRTUEMART_CART_OVERVIEW' ) );
					$document->setTitle( JText::_( 'COM_VIRTUEMART_CART_OVERVIEW' ) );
					$checkout_task = 'checkout';
				}
				
				$this->assignRef( 'checkout_task', $checkout_task );
				
				
				@$this->assignRef( 'select_shipment_text',
					JText::_( 'COM_VIRTUEMART_CART_' .
					empty( $cart->virtuemart_shipmentmethod_id ) ? 'EDIT' : 'CHANGE' . '_SHIPPING' ) );
				
				if ( empty( $cart->virtuemart_paymentmethod_id ) )
				{
					$r = 'EDIT';
				}
				else
				{
					$r = 'CHANGE';
				} // end if
				
				@$this->assignRef( 'select_payment_text', JText::_( 'COM_VIRTUEMART_CART_' . $r . '_PAYMENT' ) );
				
				$this->prepareShipmentMethods();
				$this->preparePaymentMethods();
				
				// Check Shipments and Payments
				
				$shipments = $this->getShipmentsData_byPV();
				if ( !isset( $shipments->OPTIONS[ $cart_bypv->getShipment() ] ) )
				{
					$cart_bypv->setShipment( null );
				}
				
				
				$payments = $this->getPaymentsData_byPV();
				if ( !isset( $payments->OPTIONS[ $cart_bypv->getPayment() ] ) )
				{
					$cart_bypv->setPayment( null );
				}
				
				// VM Fix - invalid shipment and payment is reset later and name is blank string
				
				if ( $cart->virtuemart_shipmentmethod_id == 0 )
				{
					$cart->cartData[ 'shipmentName' ] = JText::_( 'COM_VIRTUEMART_CART_NO_SHIPMENT_SELECTED' );
				}
				if ( $cart->virtuemart_paymentmethod_id == 0 )
				{
					$cart->cartData[ 'paymentName' ] = JText::_( 'COM_VIRTUEMART_CART_NO_PAYMENT_SELECTED' );
				}
				
				// Set Order Language
				
				$lang = JFactory::getLanguage();
				@$this->assignRef( 'order_language', $lang->getTag() );
			}
			
			
			$cart->setCartIntoSession();
			
			parent::display( $tpl );
			
			// Set the document
			$this->setDocument_byPV();
		}
		
		/*** Methods byPV ***/
		
		private function prepareShipmentMethods ()
		{
			$shipments_shipment_rates = [];
			
			$shipmentModel = VmModel::getModel( 'Shipmentmethod' );
			$shipments     = $shipmentModel->getShipments();
			
			if ( empty( $shipments ) )
			{
				vmInfo( 'COM_VIRTUEMART_NO_SHIPPING_METHODS_CONFIGURED', '' );
			}
			else
			{
				if ( !class_exists( 'vmPSPlugin' ) ) require( JPATH_VM_PLUGINS . DS . 'vmpsplugin.php' );
				JPluginHelper::importPlugin( 'vmshipment' );
				
				$dispatcher   = JDispatcher::getInstance();
				$returnValues = $dispatcher->trigger( 'plgVmDisplayListFEShipment', [
					$this->cart,
					empty( $this->cart->virtuemart_shipmentmethod_id ) ? 0 : $this->cart->virtuemart_shipmentmethod_id,
					&$shipments_shipment_rates,
				] );
			}
			
			$this->assignRef( 'shipments_shipment_rates', $shipments_shipment_rates );
			@$this->assignRef( 'found_shipment_method', count( $shipments_shipment_rates ) );
			@$this->assignRef( 'shipment_not_found_text', JText::_( 'COM_VIRTUEMART_CART_NO_SHIPPING_METHOD_PUBLIC' ) );
		}
		
		private function preparePaymentMethods ()
		{
			$payments_payment_rates = [];
			
			$paymentModel = VmModel::getModel( 'Paymentmethod' );
			$payments     = $paymentModel->getPayments( true, false );
			
			if ( empty( $payments ) )
			{
				vmInfo( 'COM_VIRTUEMART_NO_PAYMENT_METHODS_CONFIGURED', '' );
			}
			else
			{
				if ( !class_exists( 'vmPSPlugin' ) ) require( JPATH_VM_PLUGINS . DS . 'vmpsplugin.php' );
				JPluginHelper::importPlugin( 'vmpayment' );
				
				$dispatcher   = JDispatcher::getInstance();
				$returnValues = $dispatcher->trigger( 'plgVmDisplayListFEPayment', [
					$this->cart,
					empty( $this->cart->virtuemart_paymentmethod_id ) ? 0 : $this->cart->virtuemart_paymentmethod_id,
					&$payments_payment_rates,
				] );
			}
			
			$this->assignRef( 'paymentplugins_payments', $payments_payment_rates );
			@$this->assignRef( 'found_payment_method', count( $payments_payment_rates ) );
			// ??? Language Constant has %s in English
			@$this->assignRef( 'payment_not_found_text', JText::sprintf( 'COM_VIRTUEMART_CART_NO_PAYMENT_METHOD_PUBLIC', '' ) );
		}
		
		/**
		 * Установка ресурсов страницы корзины
		 *
		 * @throws Exception
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 18.11.18
		 */
		protected function setDocument_byPV ()
		{
			// VirtueMart
			
			if ( VM_VERSION == 3 )
			{
				vmJsApi::jPrice();
				vmJsApi::chosenDropDowns();
			}
			
			// OPC
			
			$document = JFactory::getDocument();
			$document->setMetaData( 'robots', 'noindex, nofollow' );
			
			
			$document->addStyleSheet( $this->getScriptUrl_byPV( self::SU_CSS, 'virtuemartcart_bypv' ) );
			$document->addScript( $this->getScriptUrl_byPV( self::SU_JS, 'virtuemartcart_bypv' ), [], [ 'defer' => 1 ] );
			
			$script = "
			'undefined'===typeof VirtueMartCart_byPV&&(VirtueMartCart_byPV={});
			VirtueMartCart_byPV.base_uri = '" . JURI::root() . "';
		";
			
			
			if ( !plgSystemOPC_for_VM_byPV::isPluginParamEnabled( 'remember_form_fields' ) )
			{
				$script .= "VirtueMartCart_byPV.REMEMBER_FORM_FIELDS = false;";
				
			}
			
			
			$shipments_incompatible_with_ajax = plgSystemOPC_for_VM_byPV::getPluginParam( 'shipments_incompatible_with_ajax' );
			
			if ( !empty( $shipments_incompatible_with_ajax ) )
			{
				$script .= "VirtueMartCart_byPV.shipments_incompatible_with_ajax = " . json_encode( $shipments_incompatible_with_ajax ) . ";";
				
			}
			
			
			$payments_incompatible_with_ajax = plgSystemOPC_for_VM_byPV::getPluginParam( 'payments_incompatible_with_ajax' );
			
			if ( !empty( $payments_incompatible_with_ajax ) )
			{
				$script .= "VirtueMartCart_byPV.payments_incompatible_with_ajax = " . json_encode( $payments_incompatible_with_ajax ) . ";";
				
			}
			
			$document->addScriptDeclaration( $script );
			
			
			$use_plugin_layout_css = plgSystemOPC_for_VM_byPV::isPluginParamEnabled( 'use_plugin_layout_css' );
			
			if ( $use_plugin_layout_css )
			{
				$layout_css_variant = $this->layout_html;
				if ( !empty( $this->layout_css ) ) $layout_css_variant .= '_' . $this->layout_css;
				
				$document->addStyleSheet( $this->getScriptUrl_byPV( self::SU_CSS, 'layout_' . $layout_css_variant ) );
				
				$plugin_layout_css_responsive_media = plgSystemOPC_for_VM_byPV::getPluginParam( 'plugin_layout_css_responsive_media' );
				
				if ( trim( $plugin_layout_css_responsive_media ) !== '' )
				{
					$document->addStyleSheet(
						$this->getScriptUrl_byPV( self::SU_CSS, 'layout_' . $this->layout_html . '_responsive' ),
						'text/css',
						$plugin_layout_css_responsive_media
					);
				}
				
			}
			
			$plugin_theme_css = plgSystemOPC_for_VM_byPV::getPluginParam( 'plugin_theme_css' );
			
			if ( $plugin_theme_css !== 'none' )
			{
				$document->addStyleSheet( $this->getScriptUrl_byPV( self::SU_CSS, 'theme_' . $plugin_theme_css ) );
			}
		}#END IF
		
		const SU_CSS = 'css';
		const SU_JS = 'js';
		
		/**
		 * Определение путей к рессурсам плагина
		 *
		 * @param $type
		 * @param $script_name
		 *
		 * @return null|string
		 * @throws Exception
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 18.11.18
		 */
		private function getScriptUrl_byPV ( $type, $script_name )
		{
			static $urlPrefix = null;
			static $urlSuffix = null;
			static $templateFolder = null;
			
			$app = \JFactory::getApplication();
			
			
			if ( $urlPrefix === null || $urlSuffix === null || $templateFolder === null )
			{
				$urlPrefix = JURI::root( true );
				
				$installer = JInstaller::getInstance();
				$installer->setPath( 'source', OPC_FOR_VM_BYPV_PLUGIN_PATH );
				
				if ( $installer->findManifest() )
				{
					$manifest = $installer->getManifest();
					
					if ( isset( $manifest->version ) ) $version = $manifest->version;
				}
				
				if ( isset( $version ) ) $urlSuffix = '?v=' . $version;
				else $urlSuffix = '';
				
				$templateFolder = '/templates/';
				
				if ( VM_VERSION < 3 )
				{
					$templateFolder .= $app->getTemplate();
				}
				else
				{
					if ( !class_exists( 'VmTemplate' ) ) require( VMPATH_SITE . '/helpers/vmtemplate.php' );
					$vmStyle        = VmTemplate::loadVmTemplateStyle();
					$templateFolder .= $vmStyle[ 'template' ];
				}
			}
			
			$scriptUrl          = $urlPrefix;
			$templateScriptPath = $templateFolder . sprintf( '/%1$s/plg_system_opc_for_vm_bypv/%2$s.%1$s', $type, $script_name );
			$pluginScriptPath   = '/media' . sprintf( '/plg_system_opc_for_vm_bypv/%1$s/%2$s.%1$s', $type, $script_name );
			
			
			$pluginAssetsScriptPath = '/plugins/system' . sprintf( '/opc_for_vm_bypv/assets/%1$s/%2$s.%1$s', $type, $script_name );
			
			if ( is_file( JPATH_ROOT . $templateScriptPath ) )
			{
				$scriptUrl .= $templateScriptPath;
			}
			else if ( is_file( JPATH_ROOT . $pluginAssetsScriptPath ) )
			{
				$scriptUrl .= $pluginAssetsScriptPath . $urlSuffix;
			}
			else
			{
				$scriptUrl .= $pluginScriptPath . $urlSuffix;
			}
			
			//echo'<pre>';print_r( $scriptUrl );echo'</pre>'.__FILE__.' '.__LINE__;
			
			return $scriptUrl;
		}#END IF
		
		public function getCoverId_byPV ( $form_tpl )
		{
			return 'bypv_cart_' . $form_tpl;
		}#END IF
		
		public function isFormTemplateLoaded_byPV ( $form_tpl )
		{
			return isset( $this->form_template_cache[ $form_tpl ] );
		}#END IF
		
		
		/**
		 * Загрузка шаблонов блоков формы корзины
		 *
		 * @param $form_tpl
		 *
		 * @return null|string
		 * @throws Exception
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 03.11.18
		 */
		public function loadFormTemplate_byPV ( $form_tpl )
		{
			
			
			if ( !isset( self::$FORM_TEMPLATES[ $form_tpl ] ) )
			{
				return null;
			}
			
			$this->loaded_form_templates[] = $form_tpl;
			
			if ( $this->isFormTemplateLoaded_byPV( $form_tpl ) )
			{
				$this->initializeFormTemplateJS_byPV( $form_tpl );
				
				return $this->form_template_cache[ $form_tpl ];
			}
			
			
			$IS_PHASE_CHECKOUT = ( $this->checkout_task === 'checkout' );
			
			
			if ( in_array( $form_tpl, [ self::TPL_PRODUCT_LIST ] ) )
			{
				$loadTemplate = true;
			}
			else if ( $form_tpl == self::TPL_COUPON_CODE )
			{
				$loadTemplate = $IS_PHASE_CHECKOUT && VmConfig::get( 'coupons_enable' )
					&& in_array( plgSystemOPC_for_VM_byPV::getPluginParam( 'show_coupon_code_in' ), [ 'page', 'product_list_and_page' ] );
			}
			else if ( $form_tpl == self::TPL_SHIPMENTS )
			{
				$loadTemplate = plgSystemOPC_for_VM_byPV::isPluginParamEnabled( 'show_' . $form_tpl )
					&& $IS_PHASE_CHECKOUT;
			}
			else if ( $form_tpl == self::TPL_PAYMENTS )
			{
				$CART_PRICES = $this->getCartPrices_byPV();
				
				$loadTemplate = plgSystemOPC_for_VM_byPV::isPluginParamEnabled( 'show_' . $form_tpl )
					&& $IS_PHASE_CHECKOUT && $CART_PRICES[ 'salesPrice' ] != 0;
			}
			else if ( $form_tpl == self::TPL_CUSTOMER_TYPE_SELECT )
			{
				$loadTemplate = $IS_PHASE_CHECKOUT
					&& ( plgSystemOPC_for_VM_byPV::isPluginParamEnabled( 'show_customer_types_always' ) || !$this->isUserLogged_byPV() );
			}
			else if ( $form_tpl == self::TPL_COMMENT )
			{
				if ( VM_VERSION < 3 )
				{
					$loadTemplate = plgSystemOPC_for_VM_byPV::isPluginParamEnabled( 'show_' . $form_tpl )
						&& ( $IS_PHASE_CHECKOUT || !empty( $this->cart->customer_comment ) );
				}
				else return null;
			}
			else if ( $form_tpl == self::TPL_ADVERTISEMENTS )
			{
				$ADVERTISEMENTS = $this->getAdvertisementsData_byPV();
				
				$loadTemplate = plgSystemOPC_for_VM_byPV::isPluginParamEnabled( 'show_' . $form_tpl )
					&& $ADVERTISEMENTS->IS_ADVERTISEMENT;
			}
			else if ( $form_tpl == self::TPL_CART_FIELDS )
			{
				if ( VM_VERSION == 3 )
				{
					$CART_FIELDS  = $this->getCartFieldsData_byPV();
					$loadTemplate = !empty( $CART_FIELDS->GROUPS );
				}
				else return null;
			}
			else if ( $form_tpl == self::TPL_TOS )
			{
				if ( VM_VERSION < 3 )
				{
					$loadTemplate = $IS_PHASE_CHECKOUT && $this->isUserFieldAgreedRequired_byPV();
				}
				else return null;
			}
			else if ( $form_tpl == self::TPL_EXTERNAL_MODULES )
			{
				$external_modules_position = plgSystemOPC_for_VM_byPV::getPluginParam( 'external_modules_position' );
				
				if ( !empty( $external_modules_position ) )
				{
					jimport( 'joomla.application.module.helper' );
					$external_modules = JModuleHelper::getModules( $external_modules_position );
				}
				
				$loadTemplate = !empty( $external_modules_position ) && !empty( $external_modules );
				
				if ( $loadTemplate )
				{
					$input = JFactory::getApplication()->input;
					
					if ( !plgSystemOPC_for_VM_byPV::isPluginParamEnabled( 'allow_autorefresh_for_external_modules' ) && $input->getString( 'format' ) === 'json' )
					{
						return null;
					}
				}
			}
			else if (
				in_array( $form_tpl, [ self::TPL_SHIPPING_ADDRESS, self::TPL_SHIPPING_ADDRESS_SELECT ] )
				&&
				plgSystemOPC_for_VM_byPV::isPluginParamEnabled( 'show_shipping_address' ) === false
			)
			{
				$loadTemplate = false;
			}
			else if ( $this->isUserLogged_byPV() )
			{
				if ( $form_tpl == self::TPL_LOGIN )
				{
					$loadTemplate = $IS_PHASE_CHECKOUT;
				}
				else
				{
					$loadTemplate = in_array( $form_tpl, [
						self::TPL_BILLING_ADDRESS,
						self::TPL_SHIPPING_ADDRESS,
						self::TPL_SHIPPING_ADDRESS_SELECT,
					] );
				}
			}
			else
			{
				$cart_bypv = VirtueMartCart_byPV::getCart();
				$forms     = [];
				
				switch ( $cart_bypv->getCustomerType() )
				{
					case VirtueMartCart_byPV::CT_LOGIN:
						if ( $IS_PHASE_CHECKOUT ) $forms = [ self::TPL_LOGIN ];
						break;
					
					case VirtueMartCart_byPV::CT_REGISTRATION:
					case VirtueMartCart_byPV::CT_GUEST:
						$forms = [ self::TPL_BILLING_ADDRESS, self::TPL_SHIPPING_ADDRESS_SELECT, self::TPL_SHIPPING_ADDRESS ];
						break;
				}
				
				$loadTemplate = in_array( $form_tpl, $forms );
			}
			
			
			if ( $loadTemplate )
			{
				
				$html = $this->loadTemplate( $form_tpl . '_bypv' );
				
				/*if ( $form_tpl == 'billing_address'){
					echo'<pre>';print_r( $html );echo'</pre>'.__FILE__.' '.__LINE__;
					
				}*/
				
				
				$this->initializeFormTemplateJS_byPV( $form_tpl );
				$this->form_template_cache[ $form_tpl ] = $html;
			}
			else
			{
				$html = '<span id="' . $this->getCoverId_byPV( $form_tpl ) . '" class="bypv_empty_cover"></span>';
			}
			
			if ( in_array( $form_tpl, [ self::TPL_SHIPMENTS, self::TPL_PAYMENTS ] ) )
			{
				$html_for_checksum                     = preg_replace( [ '/checked="checked"/', '/\s/' ], '', $html );
				$this->bypv_form_checksum[ $form_tpl ] = md5( $html_for_checksum );
			}
			else
			{
				$this->bypv_form_checksum[ $form_tpl ] = md5( $html );
			}
			
			return $html;
		}#END FN
		
		
		public function getLoadedFormTemplates_byPV ()
		{
			return $this->loaded_form_templates;
		}#END FN
		
		private function initializeFormTemplateJS_byPV ( $form_tpl )
		{
			if ( !isset( self::$FORM_TEMPLATES[ $form_tpl ] ) )
			{
				return null;
			}
			
			switch ( $form_tpl )
			{
				case self::TPL_PRODUCT_LIST:
					$js = 'VirtueMartCart_byPV.initProductListEvents();';
					break;
				
				case self::TPL_COUPON_CODE:
					$js = 'VirtueMartCart_byPV.initCouponEvents();';
					break;
				
				case self::TPL_SHIPMENTS:
					$js = 'VirtueMartCart_byPV.initShipmentsEvents();';
					break;
				
				case self::TPL_PAYMENTS:
					$js = 'VirtueMartCart_byPV.initPaymentsEvents();';
					break;
				
				case self::TPL_CUSTOMER_TYPE_SELECT:
					$js = 'VirtueMartCart_byPV.initCustomerTypeSelectEvents();';
					break;
				
				case self::TPL_LOGIN:
					$js = 'VirtueMartCart_byPV.initLoginEvents();';
					break;
				
				case self::TPL_BILLING_ADDRESS:
					$js = 'VirtueMartCart_byPV.initBillingAddressEvents();';
					break;
				
				case self::TPL_SHIPPING_ADDRESS:
					$js = 'VirtueMartCart_byPV.initShippingAddressEvents();';
					break;
				
				case self::TPL_SHIPPING_ADDRESS_SELECT:
					$js = 'VirtueMartCart_byPV.initShippingAddressSelectEvents();';
					break;
				
				default:
					return null;
			}
			
			if ( !in_array( $js, $this->form_template_initialization_js ) )
			{
				$this->form_template_initialization_js[] = $js;
			}
		}#END FN
		
		public function getFormTemplateInitializationJS_byPV ()
		{
			if ( empty( $this->form_template_initialization_js ) )
			{
				return null;
			}
			
			return implode( "\n", $this->form_template_initialization_js );
		}#END FN
		
		public function dropFormTemplateInitializationJS_byPV ()
		{
			$this->form_template_initialization_js = [];
		}#END FN
		
		public function isUserLogged_byPV ()
		{
			return ( JFactory::getUser()->guest != 1 ); // Different solution = JFactory::getUser()->id > 0
		}
		
		/*** Template Methods byPV ***/
		
		public function getCartPrices_byPV ()
		{
			if ( VM_VERSION < 3 )
				return $this->cart->pricesUnformatted;
			else
				return $this->cart->cartPrices;
		}
		
		public function getFormChecksum_byPV ( $form_tpl = null, $json_encode = true )
		{
			if ( $json_encode === true )
			{
				return base64_encode( json_encode( $this->bypv_form_checksum ) );
			}
			else
			{
				return ( $form_tpl === null ? $this->bypv_form_checksum : $this->bypv_form_checksum[ $form_tpl ] );
			}
		}
		
		public function getCartData_byPV ()
		{
			$DATA = new stdClass();
			
			// Plugin Config
			
			$DATA->PLGCFG_SHOW_SELECTED_SHIPMENT  = plgSystemOPC_for_VM_byPV::getPluginParam( 'show_selected_shipment' );
			$DATA->PLGCFG_SHOW_SELECTED_PAYMENT   = plgSystemOPC_for_VM_byPV::getPluginParam( 'show_selected_payment' );
			$DATA->PLGCFG_ALLOW_CONFIRMATION_PAGE = plgSystemOPC_for_VM_byPV::isPluginParamEnabled( 'allow_confirmation_page' );
			
			// Virtuemart Config
			
			$DATA->VMCFG_SHOW_ORIGPRICE         = VmConfig::get( 'checkout_show_origprice', 1 );
			$DATA->VMCFG_SHOW_TAX               = VmConfig::get( 'show_tax' );
			$DATA->VMCFG_COUPONS_ENABLE         = VmConfig::get( 'coupons_enable' );
			$DATA->VMCFG_ONCHECKOUT_SHOW_IMAGES = VmConfig::get( 'oncheckout_show_images' );
			$DATA->VMCFG_SHOW_LEGAL_INFO        = VmConfig::get( 'oncheckout_show_legal_info', 1 );
			$DATA->VMCFG_USE_FANCY              = VmConfig::get( 'usefancy', 0 );
			
			// Conditions
			
			$DATA->IS_PHASE_CHECKOUT  = ( $this->checkout_task === 'checkout' );
			$DATA->IS_PHASE_CONFIRM   = ( $this->checkout_task === 'confirm' );
			$DATA->IS_EMPTY           = empty( $this->cart->products );
			$DATA->IS_CONTINUE_LINK   = !empty( $this->continue_link );
			$DATA->CONTINUE_LINK      = $this->continue_link;
			$DATA->CONTINUE_LINK_HTML = $this->continue_link_html;
			$DATA->CHECKOUT_URL       = JRoute::_( 'index.php?option=com_virtuemart&view=cart', $this->useXHTML, $this->useSSL );
			$DATA->ORDER_LANGUAGE     = $this->order_language;
			$DATA->CHECKOUT_TASK      = $this->checkout_task;
			
			return $DATA;
		}
		
		public function getProductListData_byPV ()
		{
			$CART        = $this->getCartData_byPV();
			$CART_PRICES = $this->getCartPrices_byPV();
			
			$DATA = new stdClass();
			
			/*** COLS ***/
			
			$DATA->PRODUCT_COLS = [];
			$DATA->PRICE_COLS   = [];
			$DATA->ACTION_COLS  = [];
			
			for ( $i = 1; $i <= 9; ++$i )
			{
				$col_id = plgSystemOPC_for_VM_byPV::getPluginParam( 'product_list_col_' . $i );
				
				if ( !empty( $col_id ) && $col_id !== 'none' )
				{
					if ( strpos( $col_id, '::' ) === false ) $col_type = null;
					else list( $col_id, $col_type ) = explode( '::', $col_id );
					
					if ( $CART->IS_PHASE_CONFIRM && $col_id == 'DROP' ) continue;
					
					$COL     = new stdClass();
					$COL->ID = $col_id;
					
					switch ( $col_id )
					{
						case 'PRICE_EXCL_TAX':
						case 'TOTAL_EXCL_TAX':
						case 'TOTAL_INCL_TAX':
						case 'TAX':
							$COL->SHOW_ORIGINAL_AND_DISCOUNTED = ( $col_type == 'ORIGINAL_AND_DISCOUNTED' );
							break;
						
						case 'QUANTITY':
							$COL->SHOW_QUANTITY_CONTROLS = ( $col_type == 'EDIT' || $col_type == 'EDIT_DROP' ) && $CART->IS_PHASE_CHECKOUT;
							$COL->SHOW_DROP_BUTTON       = ( $col_type == 'EDIT_DROP' );
							break;
					}
					
					if ( $i < 5 )
						$DATA->PRODUCT_COLS[] = $COL;
					else if ( $i < 9 )
						$DATA->PRICE_COLS[] = $COL;
					else
						$DATA->ACTION_COLS[] = $COL;
				}
			}
			
			/*** PRODUCTS ***/
			
			$DATA->PRODUCTS = [];
			
			
			foreach ( $this->cart->products as $product_id => $product )
			{
				
				
				$PRODUCT = new stdClass();
				
				// Image
				
				if ( VM_VERSION < 3 )
				{
					$PRODUCT->IS_IMAGE   = !empty( $product->virtuemart_media_id ) && !empty( $product->image );
					$PRODUCT->SHOW_IMAGE = $CART->VMCFG_ONCHECKOUT_SHOW_IMAGES && $PRODUCT->IS_IMAGE;
					$PRODUCT->IMAGE_HTML = ( $PRODUCT->SHOW_IMAGE ? $product->image->displayMediaThumb( '', false ) : '' );
				}
				else
				{
					$PRODUCT->IS_IMAGE   = !empty( $product->images[ 0 ] );
					$PRODUCT->SHOW_IMAGE = $CART->VMCFG_ONCHECKOUT_SHOW_IMAGES && $PRODUCT->IS_IMAGE;
					$PRODUCT->IMAGE_HTML = ( $PRODUCT->SHOW_IMAGE ? $product->images[ 0 ]->displayMediaThumb( '', false ) : '' );
				}
				
				// Atributes
				
				$PRODUCT->NAME             = $product->product_name;
				$PRODUCT->LINK_NAME_HTML   = JHTML::link( $product->url, $product->product_name );
				$PRODUCT->SKU              = $product->product_sku;
				$PRODUCT->QUANTITY         = $product->quantity;
				$PRODUCT->STEP_ORDER_LEVEL = $this->getProductStepOrderLevel_byPV( $product );
				
				if ( VM_VERSION < 3 )
				{
					$PRODUCT_PRICES              = $CART_PRICES[ $product_id ];
					$PRODUCT->CUSTOM_FIELDS_HTML = $product->customfields;
				}
				else
				{
					$PRODUCT_PRICES              = $product->prices;
					$PRODUCT->CUSTOM_FIELDS_HTML = $this->customfieldsModel->CustomsFieldCartDisplay( $product );
				}
				
				// Prices
				
				
				$PRODUCT->IS_DISCOUNTED = $PRODUCT_PRICES[ 'discountedPriceWithoutTax' ] != $PRODUCT_PRICES[ 'priceWithoutTax' ];
// 			$PRODUCT->IS_DISCOUNTED = $PRODUCT_PRICES['discountedPriceWithoutTax'] != $PRODUCT_PRICES['basePriceVariant'];
				
				$PRODUCT->PRICE_EXCL_TAX_ORIGINAL = $this->currencyDisplay->createPriceDiv( 'basePriceVariant', '', $PRODUCT_PRICES, true, false );
				
				/*echo '<pre>';
				print_r($this->cart->products);
				echo '</pre>';	*/
				
				
				$PRODUCT->PRICE_EXCL_TAX = $this->currencyDisplay->createPriceDiv( 'discountedPriceWithoutTax', '', $PRODUCT_PRICES, true, false );
				
				$PRODUCT->DISCOUNT = $this->currencyDisplay->createPriceDiv( 'discountAmount', '', $PRODUCT_PRICES, true, false, $PRODUCT->QUANTITY );
				
				$PRODUCT->TOTAL_EXCL_TAX_ORIGINAL = $this->currencyDisplay->createPriceDiv( 'basePriceVariant', '', $PRODUCT_PRICES, true, false, $PRODUCT->QUANTITY );
				$PRODUCT->TOTAL_EXCL_TAX          = $this->currencyDisplay->createPriceDiv( 'discountedPriceWithoutTax', '', $PRODUCT_PRICES, true, false, $PRODUCT->QUANTITY );
				
				$PRODUCT->TAX_ORIGINAL = $this->currencyDisplay->createPriceDiv( 'taxAmountOriginal', '', $PRODUCT_PRICES[ 'basePriceWithTax' ] - $PRODUCT_PRICES[ 'basePriceVariant' ], true, false, $PRODUCT->QUANTITY );
				$PRODUCT->TAX          = $this->currencyDisplay->createPriceDiv( 'taxAmount', '', $PRODUCT_PRICES, true, false, $PRODUCT->QUANTITY );
				
				if ( !empty( $PRODUCT_PRICES[ 'basePriceWithTax' ] ) && $PRODUCT_PRICES[ 'basePriceWithTax' ] != $PRODUCT_PRICES[ 'salesPrice' ] )
				{
					$PRODUCT->TOTAL_INCL_TAX_ORIGINAL = $this->currencyDisplay->createPriceDiv( 'basePriceWithTax', '', $PRODUCT_PRICES, true, false, $PRODUCT->QUANTITY );
				}
				else if ( empty( $PRODUCT_PRICES[ 'basePriceWithTax' ] ) && $PRODUCT_PRICES[ 'basePriceVariant' ] != $PRODUCT_PRICES[ 'salesPrice' ] )
				{
					$PRODUCT->TOTAL_INCL_TAX_ORIGINAL = $this->currencyDisplay->createPriceDiv( 'basePriceVariant', '', $PRODUCT_PRICES, true, false, $PRODUCT->QUANTITY );
				}
				
				$PRODUCT->TOTAL_INCL_TAX = $this->currencyDisplay->createPriceDiv( 'salesPrice', '', $PRODUCT_PRICES, true, false, $PRODUCT->QUANTITY );
				
				$DATA->PRODUCTS[ $product_id ] = $PRODUCT;
			}
			
			// Subtotal
			
			$DATA->SUBTOTAL                 = new stdClass();
			$DATA->SUBTOTAL->DISCOUNT       = $this->currencyDisplay->createPriceDiv( 'discountAmount', '', $CART_PRICES, true );
			$DATA->SUBTOTAL->TOTAL_EXCL_TAX = $this->currencyDisplay->createPriceDiv( 'discountedPriceWithoutTax', '', $CART_PRICES, true );
			$DATA->SUBTOTAL->TAX            = $this->currencyDisplay->createPriceDiv( 'taxAmount', '', $CART_PRICES, true );
			$DATA->SUBTOTAL->TOTAL_INCL_TAX = $this->currencyDisplay->createPriceDiv( 'salesPrice', '', $CART_PRICES, true );
			
			// Coupon code
			
			if ( $CART->VMCFG_COUPONS_ENABLE )
			{
				$SHOW_COUPON_CODE_INPUT = $CART->IS_PHASE_CHECKOUT && in_array( plgSystemOPC_for_VM_byPV::getPluginParam( 'show_coupon_code_in' ), [ 'product_list', 'product_list_and_page' ] );
				
				$DATA->COUPON_CODE                         = $this->getCouponCodeData_byPV( !$SHOW_COUPON_CODE_INPUT );
				$DATA->COUPON_CODE->SHOW_COUPON_CODE_INPUT = $SHOW_COUPON_CODE_INPUT;
			}
			
			// Tax rules bill
			
			$DATA->TAX_RULES = [];
			
			$TAX_RULES = [
				'DBTaxRulesBill' => 'db_tax_rule',
				'taxRulesBill'   => 'tax_rule',
				'DATaxRulesBill' => 'da_tax_rule',
			];
			
			foreach ( $TAX_RULES as $key => $class )
			{
				$DATA->TAX_RULES[ $class ] = [];
				
				foreach ( $this->cart->cartData[ $key ] as $rule )
				{
					$RULE = new stdClass();
					
					$RULE->NAME           = $rule[ 'calc_name' ];
					$RULE->TOTAL_EXCL_TAX = '';
					$RULE->TOTAL_INCL_TAX = $this->currencyDisplay->createPriceDiv( $rule[ 'virtuemart_calc_id' ] . 'Diff', '', $CART_PRICES[ $rule[ 'virtuemart_calc_id' ] . 'Diff' ], true );
					
					if ( $key == 'taxRulesBill' )
					{
						$RULE->TAX      = $RULE->TOTAL_INCL_TAX;
						$RULE->DISCOUNT = '';
					}
					else
					{
						$RULE->TAX      = '';
						$RULE->DISCOUNT = $RULE->TOTAL_INCL_TAX;
					}
					
					$DATA->TAX_RULES[ $class ][] = $RULE;
				}
			}
			
			// Shipment
			
			if ( $CART->PLGCFG_SHOW_SELECTED_SHIPMENT == '1' || $CART->PLGCFG_SHOW_SELECTED_SHIPMENT == 'ONLY_WITH_FEE' && !empty( $CART_PRICES[ 'salesPriceShipment' ] ) )
			{
				$DATA->SHIPMENT                 = new stdClass();
				$DATA->SHIPMENT->NAME           = $this->cart->cartData[ 'shipmentName' ];
				$DATA->SHIPMENT->TOTAL_EXCL_TAX = $this->currencyDisplay->createPriceDiv( 'shipmentValue', '', $CART_PRICES[ 'shipmentValue' ], true );
				$DATA->SHIPMENT->TAX            = $this->currencyDisplay->createPriceDiv( 'shipmentTax', '', $CART_PRICES[ 'shipmentTax' ], true );
				$DATA->SHIPMENT->TOTAL_INCL_TAX = $this->currencyDisplay->createPriceDiv( 'salesPriceShipment', '', $CART_PRICES[ 'salesPriceShipment' ], true );
				$DATA->SHIPMENT->DISCOUNT       = ( $CART_PRICES[ 'salesPriceShipment' ] < 0 ? $DATA->SHIPMENT->TOTAL_INCL_TAX : '' );
			}
			
			// Payment
			
			if ( $CART->PLGCFG_SHOW_SELECTED_PAYMENT == '1' || $CART->PLGCFG_SHOW_SELECTED_PAYMENT == 'ONLY_WITH_FEE' && !empty( $CART_PRICES[ 'salesPricePayment' ] ) )
			{
				$DATA->PAYMENT                 = new stdClass();
				$DATA->PAYMENT->NAME           = $this->cart->cartData[ 'paymentName' ];
				$DATA->PAYMENT->TOTAL_EXCL_TAX = $this->currencyDisplay->createPriceDiv( 'paymentValue', '', $CART_PRICES[ 'paymentValue' ], true );
				$DATA->PAYMENT->TAX            = $this->currencyDisplay->createPriceDiv( 'paymentTax', '', $CART_PRICES[ 'paymentTax' ], true );
				$DATA->PAYMENT->TOTAL_INCL_TAX = $this->currencyDisplay->createPriceDiv( 'salesPricePayment', '', $CART_PRICES[ 'salesPricePayment' ], true );
				$DATA->PAYMENT->DISCOUNT       = ( $CART_PRICES[ 'salesPricePayment' ] < 0 ? $DATA->PAYMENT->TOTAL_INCL_TAX : '' );
			}
			
			// Total
			
			$DATA->TOTAL                 = new stdClass();
			$DATA->TOTAL->DISCOUNT       = $this->currencyDisplay->createPriceDiv( 'billDiscountAmount', '', $CART_PRICES[ 'billDiscountAmount' ], true );
			$DATA->TOTAL->TOTAL_EXCL_TAX = $this->currencyDisplay->createPriceDiv( 'billTotalExclTax', '', $CART_PRICES[ 'billTotal' ] - $CART_PRICES[ 'billTaxAmount' ], true );
			$DATA->TOTAL->TAX            = $this->currencyDisplay->createPriceDiv( 'billTaxAmount', '', $CART_PRICES[ 'billTaxAmount' ], true );
			$DATA->TOTAL->TOTAL_INCL_TAX = $this->currencyDisplay->createPriceDiv( 'billTotal', '', $CART_PRICES[ 'billTotal' ], true );
			
			// Total in payment currency
			
			if ( $this->totalInPaymentCurrency )
			{
				$DATA->TOTAL_CURRENCY                 = new stdClass();
				$DATA->TOTAL_CURRENCY->TOTAL_EXCL_TAX = '';
				$DATA->TOTAL_CURRENCY->TAX            = '';
				$DATA->TOTAL_CURRENCY->TOTAL_INCL_TAX = $this->totalInPaymentCurrency;
				$DATA->TOTAL_CURRENCY->DISCOUNT       = '';
			}
			else
			{
				$DATA->TOTAL_CURRENCY = null;
			}
			
			return $DATA;
		}
		
		public function getCouponCodeData_byPV ( $show_no_enter_message = true )
		{
			$CART        = $this->getCartData_byPV();
			$CART_PRICES = $this->getCartPrices_byPV();
			
			$DATA = new stdClass();
			
			$DATA = new stdClass();
			
			if ( $show_no_enter_message === true ) $DATA->NAME = JText::_( 'PLG_SYSTEM_OPC_FOR_VM_BYPV_NO_COUPON_CODE_ENTERED' );
			else $DATA->NAME = '';
			
			$DATA->DISCOUNT       = '';
			$DATA->TOTAL_EXCL_TAX = '';
			$DATA->TAX            = '';
			$DATA->TOTAL_INCL_TAX = '';
			
			if ( !empty( $this->cart->cartData[ 'couponCode' ] ) )
			{
				$DATA->NAME = $this->cart->cartData[ 'couponCode' ];
				
				if ( !empty( $this->cart->cartData[ 'couponDescr' ] ) )
				{
					$DATA->NAME .= ' (' . $this->cart->cartData[ 'couponDescr' ] . ')';
				}
				
				$DATA->TOTAL_EXCL_TAX = $this->currencyDisplay->createPriceDiv( 'salesPriceCouponWithoutTax', '', $CART_PRICES[ 'salesPriceCoupon' ] - $CART_PRICES[ 'couponTax' ], true );
				$DATA->TAX            = $this->currencyDisplay->createPriceDiv( 'couponTax', '', $CART_PRICES[ 'couponTax' ], true );
				$DATA->TOTAL_INCL_TAX = $this->currencyDisplay->createPriceDiv( 'salesPriceCoupon', '', $CART_PRICES[ 'salesPriceCoupon' ], true );
			}
			
			$DATA->PLACEHOLDER_TEXT = $this->coupon_text;
			
			return $DATA;
		}
		
		public function getProductStepOrderLevel_byPV ( $product )
		{
			$step_order_level = (int) $product->step_order_level;
			if ( $step_order_level < 1 ) $step_order_level = 1;
			
			$min_order_level = (int) $product->min_order_level;
			if ( $min_order_level < 1 ) $min_order_level = 1;
			if ( $min_order_level % $step_order_level > 0 ) $min_order_level -= $min_order_level % $step_order_level;
			if ( $min_order_level < $step_order_level ) $min_order_level = $step_order_level;
			
			$max_order_level = (int) $product->max_order_level;
			if ( $max_order_level % $step_order_level > 0 ) $max_order_level -= $max_order_level % $step_order_level;
			if ( $max_order_level < 1 || $max_order_level < $min_order_level ) $max_order_level = 0; // Unlimited
			
			return $min_order_level . ':' . $step_order_level . ':' . $max_order_level;
		}
		
		public function getShipmentsData_byPV ()
		{
			static $DATA = null;
			
			if ( empty( $DATA ) )
			{
				$DATA = new stdClass();
				
				$DATA->IS_AUTOMATIC_SELECTED = $this->cart->automaticSelectedShipment;
				$DATA->IS_FOUND_METHOD       = $this->found_shipment_method;
				
				$DATA->NAME               = $this->cart->cartData[ 'shipmentName' ];
				$DATA->NOT_FOUND_TEXT     = $this->shipment_not_found_text;
				$DATA->INFO_HTML          = JText::_( plgSystemOPC_for_VM_byPV::getPluginParam( 'shipment_info' ) );
				$DATA->INFO_HTML_POSITION = JText::_( plgSystemOPC_for_VM_byPV::getPluginParam( 'shipment_info_position' ) );
				
				$DATA->OPTIONS = $this->getMethodsOptions_byPV( $this->shipments_shipment_rates );
			}
			
			
			// VirtueMartCart Object
			// echo '<pre>'; print_r ($this->cart); echo '</pre>';
			return $DATA;
		}
		
		public function getPaymentsData_byPV ()
		{
			static $DATA = null;
			
			if ( empty( $DATA ) )
			{
				$CART_PRICES = $this->getCartPrices_byPV();
				
				$DATA = new stdClass();
				
				$DATA->IS_ZERO_SALES_PRICE   = ( $CART_PRICES[ 'salesPrice' ] == 0 );
				$DATA->IS_AUTOMATIC_SELECTED = $this->cart->automaticSelectedPayment;
				$DATA->IS_FOUND_METHOD       = $this->found_payment_method;
				
				$DATA->NAME           = $this->cart->cartData[ 'paymentName' ];
				$DATA->NOT_FOUND_TEXT = $this->payment_not_found_text;
				$DATA->INFO_HTML      = JText::_( plgSystemOPC_for_VM_byPV::getPluginParam( 'payment_info' ) );
				
				$DATA->INFO_HTML_POSITION = JText::_( plgSystemOPC_for_VM_byPV::getPluginParam( 'payment_info_position' ) );
				
				$DATA->OPTIONS = $this->getMethodsOptions_byPV( $this->paymentplugins_payments );
			}
			
			return $DATA;
		}
		
		private function getMethodsOptions_byPV ( $plugins_methods )
		{
			$OPTIONS = [];
			
			if ( is_array( $plugins_methods ) ) foreach ( $plugins_methods as $plugin_methods )
			{
				$method_content = [];
				
				if ( is_array( $plugin_methods ) ) foreach ( $plugin_methods as $method_html )
				{
					$method_content[] = $method_html;
					
					// ID is in the first INPUT
					preg_match( '/value="(\d+)"/', $method_html, $matches );
					
					if ( !empty( $matches[ 1 ] ) )
					{
						$OPTION       = new stdClass();
						$OPTION->ID   = (int) $matches[ 1 ];
						$OPTION->HTML = implode( '', $method_content );
						
						$OPTIONS[ $OPTION->ID ] = $OPTION;
						
						$method_content = [];
					}
				}
			}
			
			return $OPTIONS;
		}
		
		public function getLoginData_byPV ()
		{
			JFactory::getLanguage()->load( 'com_users', JPATH_SITE );
			
			$DATA = new stdClass();
			
			$DATA->IS_REMEMBER_ALLOWED = JPluginHelper::isEnabled( 'system', 'remember' );
			$DATA->IS_USER_LOGGED      = $this->isUserLogged_byPV();
			
			$DATA->LOGIN_RESET_URL  = JRoute::_( 'index.php?option=com_users&view=reset' );
			$DATA->LOGIN_REMIND_URL = JRoute::_( 'index.php?option=com_users&view=remind' );
			
			$DATA->USER_NAME = JFactory::getUser()->get( 'name' );
			
			return $DATA;
		}
		
		public function getCustomerData_byPV ()
		{
			$cart_bypv = VirtueMartCart_byPV::getCart();
			
			$DATA = new stdClass();
			
			$DATA->SELECTED_TYPE = $cart_bypv->getCustomerType();
			$DATA->TYPES         = [];
			
			foreach ( VirtueMartCart_byPV::$CUSTOMER_TYPES as $type_id => $type )
			{
				$TYPE = new stdClass();
				
				$TYPE->NAME        = $type[ 'name' ];
				$TYPE->DESCRIPTION = $type[ 'description' ];
				$TYPE->ALLOWED     = !$this->isUserLogged_byPV() || $type_id == VirtueMartCart_byPV::CT_LOGIN;
				
				$DATA->TYPES[ $type_id ] = $TYPE;
			}
			
			return $DATA;
		}
		
		public function getBillToData_byPV ()
		{
			$DATA = new stdClass();
			
			$DATA->GROUPS = $this->getGroupedUserFields_byPV( VirtueMartCart_byPV::UFT_BILLING_ADDRESS );
			
			return $DATA;
		}
		
		public function getShipToData_byPV ()
		{
			$DATA = new stdClass();
			
			$DATA->GROUPS = $this->getGroupedUserFields_byPV( VirtueMartCart_byPV::UFT_SHIPPING_ADDRESS );
			
			return $DATA;
		}
		
		public function getCartFieldsData_byPV ()
		{
			$DATA = new stdClass();
			
			$DATA->GROUPS = $this->getGroupedUserFields_byPV( VirtueMartCart_byPV::UFT_CART );
			
			return $DATA;
		}
		
		public function getGroupedUserFields_byPV ( $user_field_type )
		{
// 		$cart = VirtueMartCart::getCart();
			$cart_bypv = VirtueMartCart_byPV::getCart();
			
			// Load User Fields
			$userFields = $cart_bypv->getUserFields( $user_field_type );
			if ( empty( $userFields ) ) return [];
			
			// Set User Data
			switch ( $user_field_type )
			{
// 			case VirtueMartCart_byPV::UFT_BILLING_ADDRESS:
// 				$userCartFields = (array) $cart_bypv->getUserFields(VirtueMartCart_byPV::UFT_CART);
// 				$userCartFieldNames = array();

// 				foreach ($userCartFields as $field)
// 				{
// 					$userCartFieldNames[] = $field->name;
// 				}

// 				foreach ($userFields as $i => $field)
// 				{
// 					if (in_array($field->name, $userCartFieldNames))
// 					{
// 						unset($userFields[$i]);
// 					}
// 				}
// 				break;
				
				case VirtueMartCart_byPV::UFT_SHIPPING_ADDRESS:
					if ( $cart_bypv->getShipTo() === VirtueMartCart_byPV::ST_SAME_AS_BILL_TO )
					{
						return [];
					}
					break;
			}
			
			$userData = $cart_bypv->getUserFieldsData( $user_field_type );
			
			/* @var $userFieldsModel VirtueMartModelUserfields */
			$userFieldsModel = VmModel::getModel( 'userfields' );
			
			// Load language file for translating titles in getUserFieldsFilled() method
			VmConfig::loadJLang( 'com_virtuemart_shoppers', true );
			
			// Fill User Data
			$userFieldsFilled = $userFieldsModel->getUserFieldsFilled(
				$userFields,
				$userData,
				'bypv_' . $user_field_type . '_'
			);
			
			// Create groups of user fields
			$GROUP         = new stdClass();
			$GROUP->ID     = 'none';
			$GROUP->TITLE  = '';
			$GROUP->FIELDS = [];
			
			$GROUPS = [ $GROUP->ID => $GROUP ];
			
			foreach ( $userFieldsFilled[ 'fields' ] as $item_id => $item )
			{
				if ( $item[ 'type' ] === 'delimiter' )
				{
					if ( empty( $GROUP->FIELDS ) ) unset( $GROUPS[ $GROUP->ID ] );
					
					$GROUP         = new stdClass();
					$GROUP->ID     = $item_id;
					$GROUP->TITLE  = $item[ 'title' ];
					$GROUP->FIELDS = [];
					
					$GROUPS[ $GROUP->ID ] = $GROUP;
				}
				else
				{
					$FIELD = new stdClass();
					
					foreach ( $item as $key => $value )
					{
						$key         = strtoupper( $key );
						$FIELD->$key = $value;
					}
					
					switch ( $FIELD->TYPE )
					{
						case 'checkbox':
							$FIELD->FORMCODE_PREVIEW = JText::_( $FIELD->VALUE == 1 ? 'COM_VIRTUEMART_YES' : 'COM_VIRTUEMART_NO' );
							break;
						
						case 'password':
							$FIELD->FORMCODE_PREVIEW = ( empty( $FIELD->VALUE ) ? '' : '**********' );
							break;
						
						case 'custom':
							if ( VM_VERSION == 3 )
							{
								$FIELD->FORMCODE = str_replace(
									'bypv_' . $user_field_type . '_' . 'bypv_' . $user_field_type . '_',
									'bypv_' . $user_field_type . '_',
									$FIELD->FORMCODE
								);
							}
						
						default:
							$FIELD->FORMCODE_PREVIEW = $FIELD->VALUE;
					}
					
					switch ( $item_id )
					{
						case 'tos':
							$FIELD->FORMCODE_PREVIEW = JText::_( 'COM_VIRTUEMART_USER_FORM_BILLTO_TOS_' . ( $FIELD->VALUE ? 'YES' : 'NO' ) );
							break;
					}
					
					if ( !isset( $FIELD->DESCRIPTION ) ) $FIELD->DESCRIPTION = null;
					
					$GROUP->FIELDS[ $item_id ] = $FIELD;
				}
			}
			
			return $GROUPS;
		}
		
		public function getShipToSelectData_byPV ()
		{
			$cart_bypv = VirtueMartCart_byPV::getCart();
			
			$DATA = new stdClass();
			
			$DATA->SELECTED_ADDRESS = $cart_bypv->getShipTo();
			
			/* @var $userModel VirtueMartModelUser */
			$userModel = VmModel::getModel( 'user' );
			
			// Load BT address, because the user might want to use the information about it in template.
			$addressesBT = $userModel->getUserAddressList( $userModel->getId(), 'BT' );
			if ( empty( $addressesBT ) ) $addressesBT = [ 0 => new stdClass() ];
			
			$addressesBT[ 0 ]->virtuemart_userinfo_id = VirtueMartCart_byPV::ST_SAME_AS_BILL_TO;
			$addressesBT[ 0 ]->address_type_name      = JText::_( 'COM_VIRTUEMART_ACC_BILL_DEF' );
			
			$addressesST = $userModel->getUserAddressList( $userModel->getId(), 'ST' );
			
			$new_address                         = new stdClass();
			$new_address->virtuemart_userinfo_id = VirtueMartCart_byPV::ST_NEW_ADDRESS;
			$new_address->address_type_name      = 'PLG_SYSTEM_OPC_FOR_VM_BYPV_ADD_SHIPTO_LABEL';
			
			$addressesST[] = $new_address;
			
			$DATA->ADDRESSES = [];
			
			foreach ( array_merge( $addressesBT, $addressesST ) as $address )
			{
				$ADDRESS       = new stdClass();
				$ADDRESS->NAME = $address->address_type_name;
				
				$DATA->ADDRESSES[ $address->virtuemart_userinfo_id ] = $ADDRESS;
			}
			
			return $DATA;
		}
		
		public function isUserFieldAgreedRequired_byPV ()
		{
			if ( !class_exists( 'VirtueMartModelUserfields' ) )
			{
				require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'userfields.php' );
			}
			
			$userFieldsModel = VmModel::getModel( 'userfields' );
			
			return ( $userFieldsModel->getIfRequired( 'agreed' ) == 1 );
		}
		
		public function isTermOfServiceAccepted_byPV ()
		{
			if ( !VmConfig::get( 'agree_to_tos_onorder', 0 ) )
			{
				$userModel = VmModel::getModel( 'user' );
				$user      = $userModel->getCurrentUser();
				
				foreach ( $user->userInfo as $address )
				{
					if ( $address->address_type === 'BT' && $address->agreed == 1 )
					{
						return true;
					}
				}
			}
			
			return false;
		}
		
		public function printHeader_byPV ( $level, $text )
		{
			if ( !is_numeric( $level ) ) $level = 1;
			
			$offset = plgSystemOPC_for_VM_byPV::getPluginParam( 'header_level_offset' );
			if ( $offset > 0 ) $level += $offset;
			
			$text = JText::_( $text );
			
			if ( !empty( $text ) )
			{
				echo '<h' . $level . ' class="cart_block_title">' . JText::_( $text ) . '</h' . $level . '>';
			}
		}
		
		public function getAdvertisementsData_byPV ()
		{
			$DATA = new stdClass();
			
			$DATA->IS_ADVERTISEMENT = !empty( $this->checkoutAdvertise );
			
			if ( $DATA->IS_ADVERTISEMENT )
			{
				$DATA->IS_ADVERTISEMENT = false;
				
				foreach ( $this->checkoutAdvertise as $advertise ) if ( !empty( $advertise ) )
				{
					$DATA->IS_ADVERTISEMENT = true;
					break;
				}
			}
			
			$DATA->ADVERTISEMENTS_HTML = $this->checkoutAdvertise;
			
			return $DATA;
		}
		
		public function getTermOfServiceData_byPV ()
		{
			$DATA = new stdClass();
			
			$DATA->IS_USER_FIELD_AGREED_REQUIRED = $this->isUserFieldAgreedRequired_byPV();
			$DATA->IS_ACCEPTED                   = $this->isTermOfServiceAccepted_byPV();
			
			$DATA->CONTENT_HTML = $this->cart->vendor->vendor_terms_of_service;
			$DATA->URL          = JRoute::_( 'index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=1', false );
			
			if ( VM_VERSION < 3 )
			{
				$DATA->INPUT_NAME = 'tosAccepted';
			}
			else
			{
				$DATA->INPUT_NAME = 'tos';
			}
			
			return $DATA;
		}
		
		public function getCommentData_byPV ()
		{
			$DATA = new stdClass();
			
			$DATA->IS_ENTERED = !empty( $this->cart->customer_comment );
			$DATA->TEXT       = $this->cart->customer_comment;
			
			return $DATA;
		}
		
		/**
		 * Получить параметры кнопки ПОДДТВЕРДИТЬ ЗАКАЗ
		 *
		 * @return stdClass
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 20.11.18
		 */
		public function getButtonsData_byPV ()
		{
			$doc = JFactory::getDocument();
			
			$CART = $this->getCartData_byPV();
			
			
			
			$DATA = new stdClass();
			
			$DATA->SHOW_CHECKOUT_BUTTON         = ( $CART->PLGCFG_ALLOW_CONFIRMATION_PAGE && $CART->IS_PHASE_CHECKOUT );
			$DATA->SHOW_BACK_TO_CHECKOUT_BUTTON = ( $CART->PLGCFG_ALLOW_CONFIRMATION_PAGE && $CART->IS_PHASE_CONFIRM );
			$DATA->SHOW_CONFIRM_BUTTON          = ( !$CART->PLGCFG_ALLOW_CONFIRMATION_PAGE || $CART->IS_PHASE_CONFIRM );
			
			$Script = '';
			# Добавить скрипты на кнопку подтверждения заказа.
			$analitikConfirmButton = plgSystemOPC_for_VM_byPV::getPluginParam( 'analitikConfirmButton' );
			if ( !empty( $analitikConfirmButton ) ) {
				$Script .= '
				document.addEventListener("DOMContentLoaded", function () {
					document.getElementById("bypv_submit_confirm").addEventListener("click", ()=>{
						'.$analitikConfirmButton.'
					});
				});
				';
			}
			$doc->addScriptDeclaration($Script) ;
			
			
			
			return $DATA;
		}#END FN
		
		public function getExternalModulesData_byPV ()
		{
			$DATA = new stdClass();
			
			$DATA->MODULES = [];
			
			$external_modules_position = plgSystemOPC_for_VM_byPV::getPluginParam( 'external_modules_position' );
			
			if ( !empty( $external_modules_position ) )
			{
				jimport( 'joomla.application.module.helper' );
				$modules = JModuleHelper::getModules( $external_modules_position );
				
				$external_modules_chrome_style = plgSystemOPC_for_VM_byPV::getPluginParam( 'external_modules_chrome_style' );
				
				foreach ( $modules as $module )
				{
					$MODULE = new stdClass();
					
					$attribs = [
						'headerLevel' => 2,
					];
					
					if ( !empty( $external_modules_chrome_style ) )
					{
						$attribs[ 'style' ] = $external_modules_chrome_style;
					}
					
					$MODULE->HTML = JModuleHelper::renderModule( $module, $attribs );
					
					$DATA->MODULES[] = $MODULE;
				}
			}
			
			return $DATA;
		}
	}
