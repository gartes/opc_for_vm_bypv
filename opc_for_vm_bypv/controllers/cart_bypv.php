<?php defined('_JEXEC') or die('Restricted access');

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
 
// import Joomla controller library
jimport('joomla.application.component.controller');
 
if (!class_exists('VirtueMartControllerCart')) require(JPATH_VM_SITE . DS . 'controllers' . DS . 'cart.php');




class VirtueMartControllerCart_byPV extends VirtueMartControllerCart
{
	public static $BYPV_CHECKED_USER_FIELDS = array('virtuemart_country_id', 'zip', 'euvatin');

	/*** OVERRIDE ***/
	
	public function __construct($config = array()){
		$this->json = new stdClass();
		
		$config['base_path'] = OPC_FOR_VM_BYPV_PLUGIN_PATH;
		parent::__construct($config);

		// HACK: Because VirtueMartControllerCart::__construct() is not same as JController::__construct() 
		$this->basePath = OPC_FOR_VM_BYPV_PLUGIN_PATH;
		$this->setPath('view', $this->basePath . '/views');
		
		// Prevention before displaying the confirmation page unless permitted
		$cart = VirtueMartCart::getCart();
		if (!plgSystemOPC_for_VM_byPV::isPluginParamEnabled('allow_confirmation_page') && $cart->getDataValidated() === TRUE)
		{
			$cart->setDataValidation(FALSE);
		}
	}
	
	
	
	
	/**
	 * @param bool $cachable
	 * @param bool $urlparams
	 *
	 * @return $this|bool|JControllerLegacy|VirtueMartControllerCart
	 * @throws Exception
	 * @author    Gartes
	 * @since     3.8
	 * @copyright 19.11.18
	 */
	public function display($cachable = false, $urlparams = false)
	{
		
		if (VM_VERSION < 3)
		{
			return parent::display($cachable, $urlparams);
		}#END IF
		
		$document = JFactory::getDocument();
		$viewType = $document->getType();
		$viewName = vRequest::getCmd('view', $this->default_view);
		$viewLayout = vRequest::getCmd('layout', 'default');
		
		# Page - CART THANKYOU
		if ($viewLayout == 'order'){
			return parent::display($cachable, $urlparams);
		}
		
		
		
		
		$view = $this->getView($viewName, $viewType, '', array('layout' => $viewLayout));
		
		
		
//		if ($viewName == 'order_done_bypv') {
//			$view->setLayout( 'order_done' );
			
			
//			$view->display();
			
//			return $this;
//			echo'<pre>';print_r( $view );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $urlparams );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $cachable );echo'</pre>'.__FILE__.' '.__LINE__;
		//	die(__FILE__ .' Lines '. __LINE__ );
			
			
			
		//	parent::display( $cachable , $urlparams);
			//$view->display();
			
//		}
		
		
		
	
		$view->assignRef('document', $document);
	
		$cart = VirtueMartCart::getCart();

		$cart->_fromCart = FALSE;
		$cart->order_language = vRequest::getString('order_language', $cart->order_language);
		$cart->prepareCartData();

		$view->display();
	
		return $this;
	}
	
	public function getView($name = '', $type = '', $prefix = '', $config = array()){
		
		
		if ($name == 'cart' ) {
			$name = 'cart_bypv';
		}
		
		return parent::getView($name, $type, $prefix, $config);
	}
	
	public function checkout(){
		if (VmConfig::get('use_as_catalog', 0)) return;

		$input = JFactory::getApplication()->input;
		
		$return_to_cart = (
			$input->getMethod() === 'POST'
			? $this->checkoutFromPost()
			: FALSE
		);
		
		if ($return_to_cart == FALSE)
		{
			
			 
		 
			// Checkout
			if (plgSystemOPC_for_VM_byPV::isPluginParamEnabled('allow_confirmation_page'))
			{
				$cart = VirtueMartCart::getCart();
				
				if (VM_VERSION < 3)
				{
					// Fix of loading Agreed field in VM 2.6.x
					$userFieldsModel = VmModel::getModel('Userfields');
					$userFieldsModel->_data = $userFieldsModel->getTable('userfields');
					$userFieldsModel->_data->load('agreed', 'name');
					
					$cart->checkout();
				}
				else
				{
					$cart->prepareCartData();
					$cart->checkoutData();
				}
				 
				$return_to_cart = TRUE;
			}
			// Confirm
			else
			{
				$this->confirm(TRUE);
			}
		}
		 
		if ($return_to_cart) {
			$this->redirectToCart_byPV();
		}
	}
	
	private function checkoutFromPost(){
		$cart = VirtueMartCart::getCart();
		$cart_bypv = VirtueMartCart_byPV::getCart();
		
		if (!$cart_bypv->isProductInCart())
		{
			$this->redirectToCart_byPV();
		}

		VmConfig::loadJLang('com_virtuemart_shoppers', TRUE);
		
		/* @var $userModel VirtueMartModelUser */
		$userModel = VmModel::getModel('user');
		
		$return_to_cart = FALSE;

		$input = JFactory::getApplication()->input;

		// Customer Comment
		
		if (VM_VERSION < 3)
		{
			$this->validateCustomerComment_byPV();
		}
		
		// Product List Form
		
		$product_quantity = $input->get('bypv_quantity', NULL, 'array');
		
		// TODO: Check Product List Configuration
		if ($product_quantity === NULL) {
// 			vmInfo('COM_VIRTUEMART_EMPTY_CART');
// 			$return_to_cart = TRUE;
		}
		else
		{
			if ($this->updateProductsInCart_byPV($product_quantity))
			{
				vmInfo('COM_VIRTUEMART_CART_PRODUCT_UPDATED');
				$return_to_cart = TRUE;
			}
		}

		// Customer Type Form
		
		$customer_type = $input->getWord('bypv_customer_type');

		if ($this->isUserLogged_byPV()) {
			if (empty($customer_type)) {
				$customer_type = VirtueMartCart_byPV::CT_LOGIN;
			}

			if ($customer_type !== VirtueMartCart_byPV::CT_LOGIN) {
				vmError('User is logged, but customer_type has filled value "' . $customer_type . '"!', JText::_('COM_VIRTUEMART_CART_DATA_NOT_VALID'));
				$return_to_cart = TRUE;
				$customer_type = FALSE;
			}
		}
		elseif ($customer_type === VirtueMartCart_byPV::CT_LOGIN) {
			vmInfo('JGLOBAL_YOU_MUST_LOGIN_FIRST');
			$return_to_cart = TRUE;
			$customer_type = FALSE;
		}
		
		if ($customer_type) {
			if ($cart_bypv->setCustomerType($customer_type)) {
				$cart_bypv->setCartIntoSession();
			}
			else {
				$return_to_cart = TRUE;
				$customer_type = FALSE;
			}
		}
		elseif ($customer_type === NULL) {
			vmWarn('PLG_SYSTEM_OPC_FOR_VM_BYPV_NO_CUSTOMER_TYPE_SELECTED');
			$return_to_cart = TRUE;
		}

		// Address Forms
		
		if ($customer_type) {
			
			// Billing Address Form
			
			if (!$this->validateUserForm_byPV(VirtueMartCart_byPV::UFT_BILLING_ADDRESS, $input)) {
				$return_to_cart = TRUE;
			}

			// Shipping Address Select Form
			
			if (plgSystemOPC_for_VM_byPV::isPluginParamEnabled('show_shipping_address'))
			{
				$shipto = $input->getInt('shipto');
				
				if ($shipto === NULL) {
					vmWarn('PLG_SYSTEM_OPC_FOR_VM_BYPV_NO_SHIPTO_SELECTED');
					$return_to_cart = TRUE;
				}
				else {
					if ($cart_bypv->setShipTo($shipto)) {
						$cart_bypv->setCartIntoSession();
						// VM Checkout checks REQUEST for NULL
						JRequest::setVar('shipto', NULL);
						
						if ($shipto > VirtueMartCart_byPV::ST_SAME_AS_BILL_TO) {
				
							// Shipping Address Form
							
							if (!$this->validateUserForm_byPV(VirtueMartCart_byPV::UFT_SHIPPING_ADDRESS, $input)) {
								$return_to_cart = TRUE;
							}
						}
					}
					else {
						$return_to_cart = TRUE;
					}
				}
			}
		}

		// Shipment Form
		
		$cart_bypv->fixShipment();
		
		$virtuemart_shipmentmethod_id = $input->getInt('virtuemart_shipmentmethod_id');
		
		if ($virtuemart_shipmentmethod_id === NULL && $cart->automaticSelectedShipment === TRUE) {
			$virtuemart_shipmentmethod_id = $cart->virtuemart_shipmentmethod_id;
		}
			
		if ($virtuemart_shipmentmethod_id === NULL) {
			vmWarn('COM_VIRTUEMART_CART_NO_SHIPMENT_SELECTED');
			$return_to_cart = TRUE;
		}
		else {
			if ($cart_bypv->setShipment($virtuemart_shipmentmethod_id, TRUE))
			{
				JRequest::setVar('virtuemart_shipmentmethod_id', $virtuemart_shipmentmethod_id);
			}
			else $return_to_cart = TRUE;
		}
		
		// Payment Form

		$cart_bypv->fixPayment();

		$virtuemart_paymentmethod_id = $input->getInt('virtuemart_paymentmethod_id');
		
		if ($virtuemart_paymentmethod_id === NULL && $cart->automaticSelectedPayment === TRUE) {
			$virtuemart_paymentmethod_id = $cart->virtuemart_paymentmethod_id;
		}

		if ($virtuemart_paymentmethod_id === NULL) {
			vmWarn('COM_VIRTUEMART_CART_NO_PAYMENT_SELECTED');
			$return_to_cart = TRUE;
		}
		else {
			if ($cart_bypv->setPayment($virtuemart_paymentmethod_id, TRUE))
			{
				JRequest::setVar('virtuemart_paymentmethod_id', $virtuemart_paymentmethod_id);
			}
			else $return_to_cart = TRUE;
		}
		
		// TOS or Cart Fields

		if (VM_VERSION < 3)
		{
			$cart->tosAccepted = 0;
		}
		else
		{
			// Billing Address Form
				
			if (!$this->validateUserForm_byPV(VirtueMartCart_byPV::UFT_CART, $input)) {
				$return_to_cart = TRUE;
			}
			
// 			$cart->saveCartFieldsInCart();
// 			$cart->tosAccepted = 0;
		}
		
		// Save Cart
		
		$cart->setCartIntoSession();

		return $return_to_cart;
	}
	
	/**
	 * @param bool $from_checkout
	 *
	 * @throws Exception
	 * @author    Gartes
	 * @since     3.8
	 * @copyright 19.11.18
	 */
	public function confirm ( $from_checkout = false )
	{
		if ( VmConfig::get( 'use_as_catalog', 0 ) ) return;
		
		$app       = JFactory::getApplication();
		$cart      = VirtueMartCart::getCart();
		$cart_bypv = VirtueMartCart_byPV::getCart();
		
		
		if ( VM_VERSION == 3 )
		{
			$cart->prepareCartData();
		}#END IF
		
		if ( !$cart_bypv->isProductInCart() || ( $from_checkout === false && $cart->getDataValidated() !== true ) )
		{
			$this->redirectToCart_byPV();
		}#END IF
		
		// Probably not needed, but for sure we set the correct Task
		JRequest::setVar( 'task', 'confirm' );
		
		// Back to cart button
		if ( $app->input->getString( 'bypv_submit_back_to_checkout' ) !== null )
		{
			$this->redirectToCart_byPV();
		}#END IF
		
		// Remember products quantity for check after $cart->checkout()
		if ( $from_checkout === false )
		{
			$this->updateProductsInCart_byPV();
			$products_quantity_tmp = $cart_bypv->getProductsQuantity();
		}#END IF
		
		// ApplicationWrapper for forbid redirect
		require_once( OPC_FOR_VM_BYPV_PLUGIN_PATH . DS . 'helpers' . DS . 'application_wrapper_bypv.php' );
		
		ApplicationWrapper_OPC_for_VM_byPV::attach();
		
		try
		{
			$cart->setDataValidation( false );
			$cart->checkoutData();
			
		}
		catch ( RedirectException_byPV $e )
		{
			$message = trim( $e->getMessage() );
			if ( !empty( $message ) && $message != JText::_( 'COM_VIRTUEMART_CART_CHECKOUT_DONE_CONFIRM_ORDER' ) )
			{
				$app->enqueueMessage( $message, $e->getMessageType() );
			}
		}
		
		ApplicationWrapper_OPC_for_VM_byPV::detach();
		
		# Check changes in products quantity after $cart->checkout()
		if ( $from_checkout === false )
		{
			if ( isset ( $products_quantity_tmp ) && $cart_bypv->getProductsQuantity() != $products_quantity_tmp )
			{
				vmInfo( 'COM_VIRTUEMART_CART_PRODUCT_UPDATED' );
				$this->redirectToCart_byPV();
				
			}#END IF
		}#END IF
		
		# Back to cart if not valid checkout
		if ( !$cart->getDataValidated() )
		{
			$this->redirectToCart_byPV();
		}#END IF
		
		
		// Save user account information
		$cart->BT = (array) $cart_bypv->getUserFieldsData( VirtueMartCart_byPV::UFT_BILLING_ADDRESS );
		$cart->ST = $cart_bypv->getUserFieldsData( VirtueMartCart_byPV::UFT_SHIPPING_ADDRESS );
		
		#Проверка shiping adress
		if ( empty( $cart->BT ) )
		{
			vmError( 'Missing Billing Address!', JText::_( 'COM_VIRTUEMART_CART_DATA_NOT_VALID' ) );
			$this->redirectToCart_byPV();
		}#END IF
		
		# Проверка адрес доставки
		if ( $cart_bypv->getShipTo() > VirtueMartCart_byPV::ST_SAME_AS_BILL_TO && empty( $cart->ST ) )
		{
			vmError( 'Missing Shipping Address!', JText::_( 'COM_VIRTUEMART_CART_DATA_NOT_VALID' ) );
			$this->redirectToCart_byPV();
		}#END IF
		
		
		// Register User
		if ( $cart_bypv->checkCustomerType( VirtueMartCart_byPV::CT_REGISTRATION ) )
		{
			if ( $this->registerUser_byPV( $cart->BT ) )
			{
				$userModel = VmModel::getModel( 'user' );
				
				$cart->customer_number = $userModel->getCustomerNumberById();
				$cart->setCartIntoSession();
			}
			else
			{
				$this->redirectToCart_byPV();
			}#END IF
		}#END IF
		
		// Update User
		if ( $cart_bypv->checkCustomerType( VirtueMartCart_byPV::CT_LOGIN ) )
		{
			if ( !$this->updateUser_byPV( $cart->BT ) )
			{
				$this->redirectToCart_byPV();
			}#END IF
		}#END IF
		
		// Save Addresses
		if ( $cart_bypv->checkCustomerType( VirtueMartCart_byPV::CT_LOGIN, VirtueMartCart_byPV::CT_REGISTRATION ) )
		{
			if ( !$this->saveAddress_byPV( 'BT', $cart->BT ) )
			{
				vmWarn( 'PLG_SYSTEM_OPC_FOR_VM_BYPV_BILLTO_WAS_NOT_UPDATED' );
			}#END IF
			
			if ( $cart_bypv->getShipTo() > VirtueMartCart_byPV::ST_SAME_AS_BILL_TO )
			{
				if ( !$this->saveAddress_byPV( 'ST', $cart->ST ) )
				{
					vmWarn( 'PLG_SYSTEM_OPC_FOR_VM_BYPV_SHIPTO_WAS_NOT_UPDATED' );
				}#END IF
			}#END IF
		}#END IF
		
		// Save order
		if ( VM_VERSION == 3 )
		{
			$cart->cartfields = (array) $cart_bypv->getUserFieldsData( VirtueMartCart_byPV::UFT_CART );
			$cart->BT         = array_merge( $cart->BT, $cart->cartfields );
		}#END IF
		
		$cart->_confirmDone = true;
		$cart->confirmedOrder();
		
		// If user is blocked (beacause user activation is enabled) then unlog user
		
		$user = JFactory::getUser();
		
		if ( intval( $user->block ) === 1 )
		{
			JFactory::getSession()->clear( 'user' );
		}#END IF
		
		# Empty Cart
		$cart_bypv->emptyCart();
		
		# If any plugin has own order_done, there is general
		$view = $this->getView( 'cart', 'html' );
		
		
		// $view->set_arguments('order_done');
		$view->setLayout( 'order_done' );
		
		
		
		
		# Если включен редирект на страницу благодарности
		if (plgSystemOPC_for_VM_byPV::isPluginParamEnabled('orderDonnePage') ){
			
			
			
			
			
			
			$infArr = [
				'virtuemart_order_id' => $cart->orderDetails['details']['BT']->virtuemart_order_id ,
				'order_number' => $cart->orderDetails['details']['BT']->order_number ,
				'order_pass' => $cart->orderDetails['details']['BT']->order_pass ,
				'order_create_invoice_pass' => $cart->orderDetails['details']['BT']->order_create_invoice_pass ,
				'virtuemart_paymentmethod_id' => $cart->orderDetails['details']['BT']->virtuemart_paymentmethod_id ,
				'virtuemart_shipmentmethod_id' => $cart->orderDetails['details']['BT']->virtuemart_shipmentmethod_id ,
				'hrml' => $app->input->get('html' , JText::_('COM_VIRTUEMART_ORDER_PROCESSED') ,'RAW' )  ,
				
			];
			$sesion = JFactory::getSession();
			$sesion->set('orderDonnePage', $infArr, 'cart_bypv' );
			
			/*echo'<pre>';print_r( $cart->orderDetails );echo'</pre>'.__FILE__.' '.__LINE__;
			die(__FILE__ .' Lines '. __LINE__ );*/
			
			
			$app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&layout=order', FALSE));
		}#END IF
		
		
		
		
		
		$view->display();
	}#END FN
	
	
	public function add(){
		$cart = VirtueMartCart::getCart();
		if ($cart->getDataValidated() === TRUE) {
			$cart->setDataValidation(FALSE);
		}#END IF
		parent::add();
	}#END FN
	
	
	
	/**
	 * # Order Done
	 *
	 * @throws Exception
	 * @author    Gartes
	 * @since     3.8
	 * @copyright 19.11.18
	 */
	public function order_done() {
		
		
		
		
		$cart_bypv = VirtueMartCart_byPV::getCart();
		
		if ($cart_bypv->isOrderDoneHTML()) {
			JRequest::setVar('html', $cart_bypv->getOrderDoneHTML());
			$cart_bypv->emptyCart(TRUE);
		}
		
		$view = $this->getView('cart', 'html');
		
		$view->setLayout('order_done');
		$view->display();
	}#END FN
	
	/*** Methods byPV ***/

	private function updateProductsInCart_byPV($quantity = NULL){
		$cart = VirtueMartCart::getCart();
		
		if (VM_VERSION < 3)
		{
			$productModel = VmModel::getModel('product');
				
			if (!empty($cart->products) && is_array($cart->products))
			{
				foreach ($cart->products as $product)
				{
					$tmpProduct = $productModel->getProduct($product->virtuemart_product_id, true, false, true);
				
					$product->product_in_stock = $tmpProduct->product_in_stock;
					$product->product_ordered = $tmpProduct->product_ordered;
					$product->min_order_level = $tmpProduct->min_order_level;
					$product->max_order_level = $tmpProduct->max_order_level;
					$product->step_order_level= $tmpProduct->step_order_level;
				}
			}
		}
		
		if (!empty($quantity) && is_array($quantity))
		{
			$UPDATED = FALSE;
			
			$cart_bypv = VirtueMartCart_byPV::getCart();
			$products_quantity_tmp = $cart_bypv->getProductsQuantity();
			$products_quantity_requested_tmp = $products_quantity_tmp;
			
			foreach ($quantity as $product_id => $set_quantity) {
				$products_quantity_requested_tmp[$product_id] = $set_quantity;
				
				if (VM_VERSION < 3)
				{
					JRequest::setVar('cart_virtuemart_product_id', $product_id);
					JRequest::setVar('quantity', $set_quantity);
				}
				else
				{
					JRequest::setVar('quantity', array($product_id => $set_quantity));
				}
					
				$cart->updateProductCart();
			}
			
			$UPDATED = ($cart_bypv->getProductsQuantity() != $products_quantity_tmp || $products_quantity_requested_tmp != $products_quantity_tmp);
				
			if (VM_VERSION < 3) JRequest::setVar('cart_virtuemart_product_id');
			JRequest::setVar('quantity');
			
			return $UPDATED;
		}
		
		return FALSE;
	}
	
	/**
	 * REDIRECT TO CART
	 *
	 * @throws Exception
	 * @author    Gartes
	 * @since     3.8
	 * @copyright 19.11.18
	 */
	private function redirectToCart_byPV(){
		$app = JFactory::getApplication();
		
		$cart = VirtueMartCart::getCart();
		$cart->setDataValidation(FALSE);
		$cart->setCartIntoSession();
		
		$app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart', FALSE));
		jexit();
	}
	
	private function validateCustomerComment_byPV(){
		$cart = VirtueMartCart::getCart();
		
		// @since VM 2.0.26
		if (method_exists($cart, 'getFilterCustomerComment'))
		{
			$cart->getFilterCustomerComment();
		}
		else
		{
			$cart->customer_comment = JRequest::getVar('customer_comment', $cart->customer_comment);
			// no HTML TAGS but permit all alphabet
			$value = preg_replace('@<[\/\!]*?[^<>]*?>@si','',$cart->customer_comment);//remove all html tags
			$value = (string)preg_replace('#on[a-z](.+?)\)#si','',$value);//replace start of script onclick() onload()...
			$value = trim(str_replace('"', ' ', $value),"'") ;
			$cart->customer_comment = (string)preg_replace('#^\'#si','',$value);//replace ' at start
		}
	}
		
	/**
	 * @param string $user_field_type
	 * @param JInput $input
	 * @return boolean
	 */
	private function validateUserForm_byPV($user_field_type, $input){
		$cart = VirtueMartCart::getCart();
		$cart_bypv = VirtueMartCart_byPV::getCart();
		
		$form_valid = TRUE;
		$form_data = array();
		
		
		switch ($user_field_type)
		{
			case VirtueMartCart_byPV::UFT_BILLING_ADDRESS:
				$form_data['address_type'] = 'BT';
				break;
		
			case VirtueMartCart_byPV::UFT_SHIPPING_ADDRESS:
				$form_data['address_type'] = 'ST';
				break;
		}
		
		$password_data = array();
	
		foreach ($cart_bypv->getUserFields($user_field_type) as $field)
		{
			$field_prefix = 'bypv_' . $user_field_type . '_';
			
			$form_data[$field->name] = $input->get($field_prefix . $field->name, NULL, 'RAW');
			
			if (is_scalar($form_data[$field->name]))
			{
				$form_data[$field->name] = $this->stripSlashes_byPV(trim($form_data[$field->name]));
			}
	
			if ($form_data[$field->name] === '' || is_array($form_data[$field->name]) && empty($form_data[$field->name]))
			{
				$form_data[$field->name] = NULL;
			}
	
			// VM: This is a special test for the virtuemart_state_id. There is the speciality that the virtuemart_state_id could be 0 but is valid.
			if ($field->name === 'virtuemart_state_id') {
				$virtuemart_country_id = $input->getInt($field_prefix . 'virtuemart_country_id', NULL);
	
				if ($virtuemart_country_id !== NULL) {
					if (!class_exists('VirtueMartModelState')) require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'state.php');
	
					if (VirtueMartModelState::testStateCountry($virtuemart_country_id, $form_data[$field->name])) {
						if ($form_data[$field->name] === NULL) {
							$form_data[$field->name] = 0;
						}
					}
					else {
						$form_data[$field->name] = NULL;
					}
				}
			}
			
			if ($form_data[$field->name] === NULL) {
				if ($field->required) {
					vmWarn(JText::sprintf('COM_VIRTUEMART_MISSING_VALUE_FOR_FIELD', JText::_($field->title)));
					$form_valid = FALSE;
				}
				
				// Problem with shipment plugin "weight_countries" - if "zip" not exists, then COND is FALSE
				if ($field->name === 'zip') {
					$form_data[$field->name] = '';
				}
			}
			
			// Plugin Validation
			else {
				$valid = TRUE;
				
				switch ($field->name) {
					case 'email':
						$valid = JMailHelper::isEmailAddress($form_data[$field->name]);
// 						if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
// 							$valid = (filter_var($form_data[$field->name], FILTER_VALIDATE_EMAIL) !== FALSE);
// 						}
						break; 
				}
				
				if (!$valid) {
					vmWarn(JText::sprintf('PLG_SYSTEM_OPC_FOR_VM_BYPV_INVALID_VALUE_FOR_FIELD', JText::_($field->title)));
					$form_valid = FALSE;
				}
			}
			
			if ($field->type === 'password') {
				$password_data[] =& $form_data[$field->name];
			}
		}
		
		if (!empty($password_data) && count(array_unique($password_data)) > 1) {
			// Load the language file for com_users
			$lang = JFactory::getLanguage();
			$lang->load('com_users', JPATH_SITE);
			
			vmWarn('COM_USERS_REGISTER_PASSWORD1_MESSAGE');
			$form_valid = FALSE;

			foreach ($password_data as &$password) {
				$password = NULL;
			}
		}
		
		// VMUserFieldPlugins
		JPluginHelper::importPlugin('vmuserfield');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('plgVmOnBeforeUserfieldDataSave',array(&$form_valid, JFactory::getUser()->get('id'), &$form_data, JFactory::getUser()));

		$cart_bypv->setUserFieldsData($user_field_type, $form_data);
		
		$cart->setCartIntoSession();
		
		return $form_valid;
	}
	
	private function registerUser_byPV($data){
		$user_keys = array('email', 'username', 'password', 'password2', 'name');
		$user_data = array();
		
		foreach ($data as $key => $value) {
			if (in_array($key, $user_keys)) $user_data[$key] = $value;
		}

		$user_data['email1'] = $user_data['email'];
		$user_data['password1'] = $user_data['password'];

		// Load the language file for com_users
		$lang = JFactory::getLanguage();
		$lang->load('com_users', JPATH_SITE);
		
		$com = JPATH_SITE . DS . 'components' . DS . 'com_users';
		if (!class_exists('UsersModelRegistration')) require($com . DS . 'models' . DS . 'registration.php');
		
		$model = new UsersModelRegistration();
		$result = $model->register($user_data);

		if (in_array($result, array('useractivate', 'adminactivate'))) {
			$q = '
				SELECT `id`
				FROM #__users
				WHERE `username` = "' . $user_data['username'] . '" AND `email` = "' . $user_data['email'] . '"
			';
		
			$db = JFactory::getDbo();
			$db->setQuery($q, 0, 1);
			$user_id = (int) $db->loadResult();
		}
		else {
			$user_id = $result;
		}
		
		if (is_numeric($user_id) && $user_id > 0) {
			$user = JFactory::getUser($user_id);
			JFactory::getSession()->set('user', $user);

			$userModel = VmModel::getModel('user');
			// Set ID and reset CutomerNumber.
			$userModel->getCurrentUser();
			
			$userModel->saveUserData($user_data);
			
			return TRUE;
		}
		else {
			$app = JFactory::getApplication();

			foreach ($model->getErrors() as $error) {
				$app->enqueueMessage($error, 'error'); 
			}
		}
		
		return FALSE;
	}
	
	private function updateUser_byPV($data){
		$user = JFactory::getUser();
		
		if ($user->id < 1) {
			vmError('User is not logged!', JText::_('COM_VIRTUEMART_CART_DATA_NOT_VALID'));
			return FALSE; 
		} 
		
		if (!isset($data['email'])) {
			vmError('Input field "email" not found!', JText::_('COM_VIRTUEMART_CART_DATA_NOT_VALID'));
			return FALSE; 
		}
		
		$user->set('email', $data['email']);
		
		// Load the language file for com_users
		$lang = JFactory::getLanguage();
		$lang->load('com_users', JPATH_SITE);
		
		// Store the data.
		if (!$user->save(TRUE)) {
			$app = JFactory::getApplication();
			
			foreach ($user->getErrors() as $error) {
				$app->enqueueMessage($error, 'error');
			}

			// Joomla! Fix - Why errors are saved to session???
			$user->set('_errors', array());
			
			return FALSE;
		}
		
		return TRUE;
	}
	
	private function saveAddress_byPV($type, $data){
		if (!in_array($type, array('BT', 'ST'))) {
			vmError(
				'Invalid argument $type in function ' . __FUNCTION__ . '. Valid argument is BT or ST ($type = ' . json_encode($type) . ').',
				'Error when save user address.'
			);
			
			return FALSE;
		}

		if (empty($data) || !is_array($data)) {
			vmError(
				'Invalid argument $data in function ' . __FUNCTION__ . '. Argument $data must be non-empty array with address fields ($data = ' . json_encode($data) . ').',
				'Error when save user address.'
			);
			
			return FALSE;
		}
		
		$cart_bypv = VirtueMartCart_byPV::getCart(FALSE);

		$data['virtuemart_user_id'] = JFactory::getUser()->id;
		$virtuemart_userinfo_id = 0;

		if ($type == 'BT' && $cart_bypv->getCustomerType() == VirtueMartCart_byPV::CT_LOGIN) {
			$q = 'SELECT `virtuemart_userinfo_id`, `agreed` FROM #__virtuemart_userinfos
				WHERE `virtuemart_user_id` = ' . JFactory::getUser()->id . ' AND `address_type` = "BT"';
			
			$db = JFactory::getDbo();
			$db->setQuery($q, 0, 1);
			list($virtuemart_userinfo_id, $agreed) = $db->loadRow();

			if ($virtuemart_userinfo_id > 0)
			{
				// Field "agreed" is not in $data if user is logged
				$data['agreed'] = $agreed;
			}
			
			// TODO: Je toto nutne? E-mail se aktualizuje uz v updateUser_byPV() ??? Asi to tu bylo driv nez vznkil prave ten update..
// 			if (array_key_exists('email', $data)) {
// 				$user = JFactory::getUser();
// 				$user->email = $data['email'];
// 				// No return FALSE if user save fail. We try to save address.
// 				$user->save(TRUE);
// 			}
		}
		
		if ($type == 'ST') {
			if ($cart_bypv->getShipTo() === VirtueMartCart_byPV::ST_SAME_AS_BILL_TO) {
				return FALSE;
			}
			
			if ($cart_bypv->getShipTo() > 0) {
				$virtuemart_userinfo_id = $cart_bypv->getShipTo();
			}
		}
		
		$userModel = VmModel::getModel('user');
		$userInfoTable = $userModel->getTable('userinfos');
		
		if ($virtuemart_userinfo_id > 0) {
			$userInfoTable->load($virtuemart_userinfo_id);
		}
		
		$userInfoData = $userModel->_prepareUserFields($data, $type, $userInfoTable);
		
		if (!$userInfoTable->bindChecknStore($userInfoData)) {
			vmError('storeAddress ' . $userInfoTable->getError());
			return FALSE;
		}
		
		return TRUE;
	}
	
	/*** JSON ***/
	
	public function setCartDataJS_byPV(){
		$UPDATED = FALSE;
		
		$cart = VirtueMartCart::getCart(FALSE);
		$cart_bypv = VirtueMartCart_byPV::getCart();
		
		
		
		
		
		
		$input = JFactory::getApplication()->input;
		
		$input_fields = array(
			'bypv_quantity'					=> 'array',
			'bypv_coupon_code'				=> 'string',
			'virtuemart_shipmentmethod_id'	=> 'int',
			'virtuemart_paymentmethod_id'	=> 'int',
		);
		
		
		
		
		foreach (VirtueMartCart_byPV::$USER_FIELD_TYPES as $user_field_type => $user_field_type_params) {
			foreach (self::$BYPV_CHECKED_USER_FIELDS as $field_name) {
				$input_fields['bypv_' . $user_field_type . '_' . $field_name] = 'string';
			}
		}
		
		$form = $input->getArray($input_fields);

		


		if (is_array($form)) {
			// Product Quantity
			
			if (isset($form['bypv_quantity']) && is_array($form['bypv_quantity'])) {
				$UPDATED = $this->updateProductsInCart_byPV($form['bypv_quantity']);
				
				
			}
			
			// Coupon Code

			if (isset($form['bypv_coupon_code'])) {
				
				// TODO: Bug in VM 2.1.1 - Coupon Code is always not found 
				// /components/com_virtuemart/helpers/coupon.php[55]: CouponHelper::ValidateCouponCode(): $_db->getEscaped($_code) -> $_db->escape($_code)
				$result = $cart->setCouponCode($form['bypv_coupon_code']);

				if (is_string($result)) {
					JFactory::getApplication()->enqueueMessage($result);
				}

				// VM Hack - If coupon is not valid, info remains...
				unset(
					$cart->cartData['couponCode'],
					$cart->cartData['couponDescr']
				);
				
				if (VM_VERSION < 3)
				{
					// VM Hack - For loading coupon data we must recreate calculationHelper instance
					calculationHelper::$_instance = NULL;
					// @since VM 2.0.26
					$cart->getCartPrices();
				}
				else
				{
					if ($cart->couponCode != $form['bypv_coupon_code'])
					{
						$cart->couponCode = '';
						$cart->setCartIntoSession();
					}
				}
				
				$UPDATED = TRUE;
			}

			// Shipment

			if (isset($form['virtuemart_shipmentmethod_id'])) {
				$cart_bypv->setShipment($form['virtuemart_shipmentmethod_id'], FALSE);
				$UPDATED = TRUE;
			}
			
			// Payment
			
			if (isset($form['virtuemart_paymentmethod_id'])) {
				$cart_bypv->setPayment($form['virtuemart_paymentmethod_id'], FALSE);
				$UPDATED = TRUE;
			}

			// User Fields

			$USER_FIELDS_UPDATED = FALSE;

			foreach (VirtueMartCart_byPV::$USER_FIELD_TYPES as $user_field_type => $user_field_type_params)
			{
				$field_prefix = 'bypv_' . $user_field_type . '_';
				$fields_data = $cart_bypv->getUserFieldsData($user_field_type);
				
				foreach (self::$BYPV_CHECKED_USER_FIELDS as $field_name)
				{
					if (isset($form[$field_prefix . $field_name]))
					{
						$fields_data[$field_name] = $form[$field_prefix . $field_name];
						$USER_FIELDS_UPDATED = TRUE;
					}
				}
				
				$cart_bypv->setUserFieldsData($user_field_type, $fields_data);
			}

			if ($USER_FIELDS_UPDATED)
			{
				// VMUserFieldPlugins
				JPluginHelper::importPlugin('vmuserfield');
				$dispatcher = JDispatcher::getInstance();
				$form_valid = TRUE;
				
				$dispatcher->trigger('plgVmOnBeforeUserfieldDataSave',array(&$form_valid, JFactory::getUser()->get('id'), &$cart->BT, JFactory::getUser()));
				$cart_bypv->setUserFieldsData(VirtueMartCart_byPV::UFT_BILLING_ADDRESS, $cart->BT);
				
				if (!empty($cart->ST))
				{
					$dispatcher->trigger('plgVmOnBeforeUserfieldDataSave',array(&$form_valid, JFactory::getUser()->get('id'), &$cart->ST, JFactory::getUser()));
					$cart_bypv->setUserFieldsData(VirtueMartCart_byPV::UFT_SHIPPING_ADDRESS, $cart->ST);
				}
				
				$cart->setCartIntoSession();
				$UPDATED = TRUE;
			}
		}
		
		$cart_bypv->setCartIntoSession();

		// Refresh VM Modules
		// TODO: Maybe only if product list or salesPrice is changed 
		
		if ($UPDATED)
		{
			$this->refreshVMCartModuleJS_byPV();
		}
		
		// Check empty cart

		if (!$cart_bypv->isProductInCart())
		{
			$this->refreshCartJS_byPV();
		}
			
		if ($UPDATED) {
			if (!class_exists('VirtueMartViewCart_byPV')) {
				require(OPC_FOR_VM_BYPV_PLUGIN_PATH . DS . 'views' . DS . 'cart_bypv' . DS . 'view.html.php');
			}
				
			$form_templates = array(
				VirtueMartViewCart_byPV::TPL_PRODUCT_LIST,
				VirtueMartViewCart_byPV::TPL_COUPON_CODE,
				VirtueMartViewCart_byPV::TPL_SHIPMENTS,
				VirtueMartViewCart_byPV::TPL_PAYMENTS,
				VirtueMartViewCart_byPV::TPL_EXTERNAL_MODULES,
			);

			$this->generateResultJS_byPV($form_templates , array(VirtueMartViewCart_byPV::TPL_PRODUCT_LIST));
		}

		$this->exitJSON_byPV();
	}
	
	/**
	 * Login user by JSON request.
	 * 
	 * Based on com_users->user(controller)->login() method.
	 * 
	 * @return void
	 */
	public function loginJS_byPV(){
		$app = JFactory::getApplication();
		
		$data = array();
		$data['username'] = JRequest::getVar('username', '', 'get', 'username');
		$data['password'] = JRequest::getString('password', '', 'get', JREQUEST_ALLOWRAW);
		
		// Get the log in options.
		$options = array();
		$options['remember'] = JRequest::getBool('remember', FALSE);
		
		// Get the log in credentials.
		$credentials = array();
		$credentials['username'] = $data['username'];
		$credentials['password'] = $data['password'];
		
		// Perform the log in.
		if (true === $app->login($credentials, $options)) {
			// Success
			
			// VM Fix: In $cart->setPreferred() is condition for count(BT) < 1
			$cart = VirtueMartCart::getCart();
			$cart->BT = NULL;
			
			$app->setUserState('users.login.form.data', array());

			$this->refreshCartJS_byPV();
		}
		else {
			// Login failed !
			$data['remember'] = (int)$options['remember'];
			$app->setUserState('users.login.form.data', $data);
			
			$this->exitJSON_byPV();
		}
	}

	/**
	 * Login user by JSON request.
	 * 
	 * Based on com_users->user(controller)->logout() method.
	 * 
	 * @return void
	 */
	public function logoutJS_byPV(){
		$app = JFactory::getApplication();
		
		// Perform the log out.
		$error = $app->logout();
		
		VirtueMartCart::getCart()->emptyCart();
		$this->refreshVMCartModuleJS_byPV();
		
		$this->refreshCartJS_byPV();
	}
	
	public function updateCustomerFormJS_byPV(){
		echo '<pre>'; print_r (  ); echo '</pre>'.__FILE__.' in line:  '.__LINE__ ;
		die(__FILE__.' in line '.__LINE__);
		
		$cart_bypv = VirtueMartCart_byPV::getCart();
		
		$input = JFactory::getApplication()->input;
		$customer_type = $input->getWord('bypv_customer_type');
		
		
		 
		
		if ($customer_type !== NULL) {
			if ($cart_bypv->setCustomerType($customer_type)) {
				$address_bt = $this->stripSlashes_byPV($input->getString('address_bt'));
				$address_st = $this->stripSlashes_byPV($input->getString('address_st'));

				$this->setCartAddressByUserLocalFields_byPV(VirtueMartCart_byPV::UFT_BILLING_ADDRESS, $address_bt);
				$this->setCartAddressByUserLocalFields_byPV(VirtueMartCart_byPV::UFT_SHIPPING_ADDRESS, $address_st);

				$cart_bypv->setCartIntoSession();
			}
		}

		if (!class_exists('VirtueMartViewCart_byPV')) {
			require(OPC_FOR_VM_BYPV_PLUGIN_PATH . DS . 'views' . DS . 'cart_bypv' . DS . 'view.html.php');
		}
			
		$form_templates = array(
			VirtueMartViewCart_byPV::TPL_CUSTOMER_TYPE_SELECT,
			VirtueMartViewCart_byPV::TPL_LOGIN,
			VirtueMartViewCart_byPV::TPL_BILLING_ADDRESS,
			VirtueMartViewCart_byPV::TPL_SHIPPING_ADDRESS,
			VirtueMartViewCart_byPV::TPL_SHIPPING_ADDRESS_SELECT,

			VirtueMartViewCart_byPV::TPL_SHIPMENTS,
			VirtueMartViewCart_byPV::TPL_PAYMENTS,
				
			VirtueMartViewCart_byPV::TPL_PRODUCT_LIST,
			VirtueMartViewCart_byPV::TPL_EXTERNAL_MODULES
		);
		
		$this->generateResultJS_byPV($form_templates);
		
		$this->exitJSON_byPV();
	}
	
	public function overrideFieldsDataJS_byPV(){
		$cart_bypv = VirtueMartCart_byPV::getCart();
		
		$input = JFactory::getApplication()->input;
		$override = ($input->getInt('override') === 1);
		
		$cart_bypv->overrideFieldsData($override);

		if ($override)
		{
			if (!class_exists('VirtueMartViewCart_byPV')) {
				require(OPC_FOR_VM_BYPV_PLUGIN_PATH . DS . 'views' . DS . 'cart_bypv' . DS . 'view.html.php');
			}
			
			$form_templates = array(
					VirtueMartViewCart_byPV::TPL_CUSTOMER_TYPE_SELECT,
					VirtueMartViewCart_byPV::TPL_LOGIN,
					VirtueMartViewCart_byPV::TPL_BILLING_ADDRESS,
					VirtueMartViewCart_byPV::TPL_SHIPPING_ADDRESS,
					VirtueMartViewCart_byPV::TPL_SHIPPING_ADDRESS_SELECT,
			
					VirtueMartViewCart_byPV::TPL_SHIPMENTS,
					VirtueMartViewCart_byPV::TPL_PAYMENTS,
			
					VirtueMartViewCart_byPV::TPL_PRODUCT_LIST,
					VirtueMartViewCart_byPV::TPL_EXTERNAL_MODULES
			);
			
			$this->generateResultJS_byPV($form_templates);
		}
		
		$this->exitJSON_byPV();
	}
	
	public function updateShippingAddressJS_byPV(){
		$cart_bypv = VirtueMartCart_byPV::getCart();
		
		$input = JFactory::getApplication()->input;
		$shipto = $input->getInt('shipto');
		
		if ($shipto !== NULL) {
			if ($cart_bypv->setShipTo($shipto)) {
				$address_st = $this->stripSlashes_byPV($input->getString('address_st'));
				$this->setCartAddressByUserLocalFields_byPV(VirtueMartCart_byPV::UFT_SHIPPING_ADDRESS, $address_st);
				
				$cart_bypv->setCartIntoSession(TRUE);
			}
			else {
				vmError('Invalid Shipping Address ID!', JText::_('COM_VIRTUEMART_CART_DATA_NOT_VALID'));
			}
		}
		
		if (!class_exists('VirtueMartViewCart_byPV')) {
			require(OPC_FOR_VM_BYPV_PLUGIN_PATH . DS . 'views' . DS . 'cart_bypv' . DS . 'view.html.php');
		}
			
		$form_templates = array(
			VirtueMartViewCart_byPV::TPL_SHIPPING_ADDRESS,
			VirtueMartViewCart_byPV::TPL_SHIPPING_ADDRESS_SELECT,

			VirtueMartViewCart_byPV::TPL_SHIPMENTS,
			VirtueMartViewCart_byPV::TPL_PAYMENTS,
			
			VirtueMartViewCart_byPV::TPL_PRODUCT_LIST,
			VirtueMartViewCart_byPV::TPL_EXTERNAL_MODULES
		);
		
		$this->generateResultJS_byPV($form_templates);
		
		$cart = VirtueMartCart::getCart();
		
		$this->exitJSON_byPV();
	}
	
	private function setCartAddressByUserLocalFields_byPV($user_field_type, $address_data){
		if (empty($address_data)) return;
		$cart_bypv = VirtueMartCart_byPV::getCart();

		if (!isset(VirtueMartCart_byPV::$USER_FIELD_TYPES[$user_field_type])) return;
		
		if (
			$cart_bypv->checkCustomerType(VirtueMartCart_byPV::CT_REGISTRATION, VirtueMartCart_byPV::CT_GUEST)
			||
			$cart_bypv->checkCustomerType(VirtueMartCart_byPV::CT_LOGIN) && $this->isUserLogged_byPV()
		)
		{
			if ($user_field_type != VirtueMartCart_byPV::UFT_SHIPPING_ADDRESS || $cart_bypv->getShipTo() > VirtueMartCart_byPV::ST_SAME_AS_BILL_TO)
			{
				if ($address_data = json_decode($address_data, TRUE))
				{
					$field_prefix = 'bypv_' . $user_field_type . '_';
					$fields_data = $cart_bypv->getUserFieldsData($user_field_type);
					
					foreach ($address_data as $field_name => $field_value)
					{
						if (substr($field_name, 0, strlen($field_prefix)) === $field_prefix)
						{
							$fields_data[substr($field_name, strlen($field_prefix))] = $field_value;
						}
					}
					
					$cart_bypv->setUserFieldsData($user_field_type, $fields_data);
				}
			}
		}
	}
	
	private function generateResultJS_byPV($form_templates, $form_templates_force_replace = array()){
		 
		
		$cart_bypv = VirtueMartCart_byPV::getCart();
		
		if (!$cart_bypv->isProductInCart())
		{
			$this->refreshCartJS_byPV();
		}
		
		$input = JFactory::getApplication()->input;
		$document = JFactory::getDocument();
		
		$form_checksum_old = $input->getString('bypv_form_checksum');
		$form_checksum_old = json_decode(base64_decode($form_checksum_old), TRUE);
		
		if (!is_array($form_checksum_old)) {
			$form_checksum_old = array();
		}
		
		$view = $this->getView('cart', 'html');
		
		ob_start();
		$view->display('blank_bypv');
		ob_end_clean();
		
		
		
		
		$this->json->replaceHTML = array();
		$this->json->evalJS = array();

		foreach ($form_templates as $form_tpl) {
			$cover_id = $view->getCoverId_byPV($form_tpl);
			$html = $view->loadFormTemplate_byPV($form_tpl);

			if (in_array($form_tpl, $form_templates_force_replace) || !isset($form_checksum_old[$form_tpl]) || $form_checksum_old[$form_tpl] !== $view->getFormChecksum_byPV($form_tpl, FALSE)) {
					
					$this->json->replaceHTML[$cover_id] = $html;
					$code = $view->getFormTemplateInitializationJS_byPV();
				
					if (!empty($code)) {
						$this->json->evalJS[$cover_id] = $code;
					}
			}
			
			$view->dropFormTemplateInitializationJS_byPV();
		}
		
		$loaded_form_templates = $view->getLoadedFormTemplates_byPV();
		
		while ($form_tpl = array_pop($loaded_form_templates)) {
			if (in_array($form_tpl, $loaded_form_templates)) {
				$cover_id = $view->getCoverId_byPV($form_tpl);
				
				if (isset($this->json->replaceHTML[$cover_id])) {
					unset($this->json->replaceHTML[$cover_id]);
				}
				if (isset($this->json->evalJS[$cover_id])) {
					unset($this->json->evalJS[$cover_id]);
				}
			}
		}
	}
	
	public function refreshCartBlocksJS_byPV(){
		if (!class_exists('VirtueMartViewCart_byPV')) {
			require(OPC_FOR_VM_BYPV_PLUGIN_PATH . DS . 'views' . DS . 'cart_bypv' . DS . 'view.html.php');
		}
		
		$this->generateResultJS_byPV(array_keys(VirtueMartViewCart_byPV::$FORM_TEMPLATES));
		$this->exitJSON_byPV();
	}
	
	public function refreshCartJS_byPV(){
		 
		$view = $this->getView('cart', 'html');
	
		ob_start();
		$view->display();
		$this->json->replaceHTML['bypv_cart'] = ob_get_clean();
		
		$this->json->evalJS['bypv_cart'] = 'VirtueMartCart_byPV.initialize();';
	
		$this->exitJSON_byPV();
	}
	
	private function refreshVMCartModuleJS_byPV(){
		$document = JFactory::getDocument();
		 $document->addScriptDeclaration('VirtueMartCart_byPV.vmProductUpdate(null, false);');
	}
	
	public static function debugJS_byPV($var){
		$args = func_get_args();
		
		foreach ($args as &$arg) {
			$arg = json_encode($arg);
		}
		
		$document = JFactory::getDocument();
		$document->addScriptDeclaration('console.log(' . implode(', ', $args) . ');');
	}

	private function exitJSON_byPV(){
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
	
		$message_queue = $app->getMessageQueue();
		
		if (!empty($message_queue)) {
			$document->setType('html');
			$renderer = $document->loadRenderer('message');
			$this->json->systemMessageHTML = $renderer->render(null);
		}
	
		if (isset($document->_script['text/javascript'])) {
			$this->json->evalOtherJS = $document->_script['text/javascript'];
		}
		
		// Fix for non-standard inserting of JS Sripts in the VM3 (since VM 2.9.9c)
		if (VM_VERSION == 3 && method_exists('vmJsApi', 'getJScripts'))
		{
			$vmJS = vmJsApi::getJScripts();
				
			if (!empty($vmJS)) foreach($vmJS as $name => $jsToAdd)
			{
				if (!empty($jsToAdd['script']) && (strpos($jsToAdd['script'],'/') !== 0 || strpos($jsToAdd['script'],'//<![CDATA[') === 0))
				{
					$script = trim($jsToAdd['script']);
					
					if (!empty($script)) $this->json->evalOtherJS .= $script;
				}
			}
		}

		$view = $this->getView('cart', 'html');
		$this->json->formChecksum = $view->getFormChecksum_byPV();
	
		echo json_encode($this->json);
		jexit();
	}

	private function isUserLogged_byPV(){
		return (JFactory::getUser()->guest != 1); // Different solution = JFactory::getUser()->id > 0
	}
	
	private function stripSlashes_byPV($string){
		if (empty($string) || !get_magic_quotes_gpc()) return $string;
		
		if (is_array($string)) foreach ($string as &$value) $value = $this->stripSlashes_byPV($value);
		else $string =  stripslashes($string);

		return $string;
	}
	
	public function viewJS(){
		jimport('joomla.application.module.helper');
		
		if (!JModuleHelper::isEnabled('mod_virtuemart_cart'))
		{
			return parent::viewJS();
		}
		
		if (!class_exists('VirtueMartCart')) require(VMPATH_SITE . '/helpers/cart.php');
		$cart = VirtueMartCart::getCart(FALSE);
		$cart->prepareCartData();
		$data = $cart->prepareAjaxData(TRUE);
		
		if (VM_VERSION < 3)
		{
			$extension = 'com_virtuemart';
			VmConfig::loadJLang($extension); //  when AJAX it needs to be loaded manually here >> in case you are outside virtuemart !
			
			if ($data->totalProduct > 1) $data->totalProductTxt = JText::sprintf('COM_VIRTUEMART_CART_X_PRODUCTS', $data->totalProduct);
			elseif ($data->totalProduct == 1) $data->totalProductTxt = JText::_('COM_VIRTUEMART_CART_ONE_PRODUCT');
			else $data->totalProductTxt = JText::_('COM_VIRTUEMART_EMPTY_CART');
			
			if ($data->dataValidated == true)
			{
				$taskRoute = '&task=confirm';
				$linkName = JText::_('COM_VIRTUEMART_ORDER_CONFIRM_MNU');
			}
			else
			{
				$taskRoute = '';
				$linkName = JText::_('COM_VIRTUEMART_CART_SHOW');
			}
			
			$data->cart_show = '<a class="floatright" href="' . JRoute::_("index.php?option=com_virtuemart&view=cart" . $taskRoute, $this->useXHTML, $this->useSSL) . '" rel="nofollow">' . $linkName . '</a>';
			$data->billTotal = JText::_('COM_VIRTUEMART_CART_TOTAL') . ' : <strong>' . $data->billTotal . '</strong>';
		}

		// VM Hack for module "mod_virtuemart_cart" if the cart is empty
		
		if ($data->totalProduct == 0)
		{
			$data->totalProduct = 1;
			$data->billTotal = '';
			$data->cart_show = '';
		}
		
		echo json_encode($data);
		jexit();
	}
}