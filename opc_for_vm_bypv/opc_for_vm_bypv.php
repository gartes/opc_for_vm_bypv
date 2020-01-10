<?php defined('_JEXEC') or die('Restricted access');



define('OPC_FOR_VM_BYPV_PLUGIN_PATH', dirname(__FILE__));
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

JForm::addFieldPath(dirname(__FILE__) . '/models/fields');

class plgSystemOPC_for_VM_byPV extends JPlugin
{
	const DEMO_MODULE = 'mod_demo_opc_for_vm_bypv';
	
	/**
	 * @var JRegistry
	 */
	private static $plugin_params = NULL;
	
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		if ($this->initDemoModule_byPV())
		{
			modDemoOPCforVMbyPVHelper::onLoad($this);
		}
		 
		self::$plugin_params = $this->params;
		
	}
	
	/**
	 * Получение параметров настройки плагина
	 *
	 * @param $path
	 *
	 * @return mixed
	 * @author    Gartes
	 * @since     3.8
	 * @copyright 20.11.18
	 */
	public static function getPluginParam($path)
	{
		switch ($path)
		{
			case 'show_shipments':
			case 'show_payments':
			case 'show_shipping_address':
			case 'show_advertisements':
			case 'show_comment':
			case 'show_selected_shipment':
			case 'show_selected_payment':
			case 'allow_order_from_guest':
			case 'allow_confirmation_page':

			# Редирект на страницу благодарности
			case 'orderDonnePage':

			case 'remember_form_fields':
			case 'use_plugin_layout_css':
			case 'allow_autorefresh_for_external_modules':
				$default_value = '1';
				break;

			case 'header_level_offset':
			case 'show_customer_types_always':
			case 'allow_order_from_guest':
				$default_value = '0';
				break;

			case 'default_customer_type':
				$default_value = 'login';
				break;

			case 'plugin_layout':
				$default_value = 'vertical';
				break;
				
			case 'plugin_theme_css':
				$default_value = 'j25_beez_vm20_default';
				break;
				
			case 'show_coupon_code_in':
				$default_value = 'product_list';
				break;
				
			case 'product_list_col_1':
				$default_value = 'SKU';
				break;
			case 'product_list_col_2':
				$default_value = 'NAME';
				break;
			case 'product_list_col_3':
				$default_value = 'PRICE_EXCL_TAX::ORIGINAL';
				break;
			case 'product_list_col_4':
				$default_value = 'QUANTITY::EDIT';
				break;
			case 'product_list_col_5':
				$default_value = 'DISCOUNT';
				break;
			case 'product_list_col_6':
				$default_value = 'TOTAL_EXCL_TAX::DISCOUNTED';
				break;
			case 'product_list_col_7':
				$default_value = 'TAX::DISCOUNTED';
				break;
			case 'product_list_col_8':
				$default_value = 'TOTAL_INCL_TAX::DISCOUNTED';
				break;
			case 'product_list_col_9':
				$default_value = 'DROP';
				break;
			
			case 'shipments_incompatible_with_ajax':
			case 'payments_incompatible_with_ajax':
				$default_value = array();
				break;
			// --------------- здесь !
			default:
				$default_value = NULL;
		}
		
		// -- $default_value = NULL;
		// -- 
		return self::$plugin_params->get($path, $default_value);
	}
	
	public static function isPluginParamEnabled($path)
	{
		return (self::getPluginParam($path) === '1');
	}
	
	/**
	 * @param $_controller
	 *
	 * @throws Exception
	 * @author    Gartes
	 * @since     3.8
	 * @copyright 19.11.18
	 */
	public function onVmSiteController ( $_controller ){
		
		$controllersArray = ['order_done_bypv'];
		if ( !in_array(   $_controller , $controllersArray )) { return ; } #END IF
		
		if(!class_exists('VirtueMartControllerOrder_done')) require(JPATH_PLUGINS.DS.'system'.DS.'opc_for_vm_bypv'.DS.'controllers'.DS.'order_done.php');
		
		
		
		$app    = JFactory::getApplication();
		
		
		$VirtueMartControllerOrder_done = new VirtueMartControllerOrder_done();
		$VirtueMartControllerOrder_done->display();
		
		 return ;
	
	 
	/*
	
		$option = $app->input->get( 'option', false, 'WORD' );
		$view   = $app->input->get( 'view', false, 'WORD' );
		$task   = $app->input->get( 'task', false, 'WORD' );
		
		
		
		*/
		
		
		
		
		
		
		
	}#END FN
	/**
	 * @throws Exception
	 * @author    Gartes
	 * @since     3.8
	 * @copyright 19.11.18
	 */
	public function onAfterRoute ()
	{
		
		$app    = JFactory::getApplication();
		$router = $app->getRouter();
		
		$option = $app->input->get( 'option', false, 'WORD' );
		$view   = $app->input->get( 'view', false, 'WORD' );
		
		
		$task   = $app->input->get( 'task', false, 'WORD' );
		
		if ($task != 'checkout'){
			
			// echo'<pre>';print_r(  $app->input );echo'</pre>'.__FILE__.' '.__LINE__;
			// die(__FILE__ .' Lines '. __LINE__ );
		}
		
		
		// echo'<pre>';print_r( plgSystemOPC_for_VM_byPV::isPluginParamEnabled('orderDonnePage') );echo'</pre>'.__FILE__.' '.__LINE__;
		
		
		
		// Component Virtuemart
		if ( $router->getVar( 'option' ) === 'com_virtuemart' || $option === 'com_virtuemart' )
		{
			switch ( $view )
			{
				// View Cart
				case 'cart':
					
					
					if ( $app->input->get( 'task', false, 'WORD' ) != 'addJS' )
					{
						if ( $this->initDemoModule_byPV() && modDemoOPCforVMbyPVHelper::onRequest() )
						{
							$this->redirectToCart_byPV();
						}#END IF
						
						// We set manually ItemId for correct function of JModuleHelper Class 
						$menus     = $app->getMenu( 'site' );
						$component = JComponentHelper::getComponent( 'com_virtuemart' );
						$items     = $menus->getItems( 'component_id', $component->id );
						
						foreach ( $items as $item )
						{
							if ( isset( $item->query, $item->query[ 'view' ] ) )
							{
								if ( $item->query[ 'view' ] === 'cart' )
								{
									$app->input->set('Itemid', $item->id);
									// JRequest::setVar( 'Itemid', $item->id );
									break;
								}#END IF
							}#END IF
						}#END FOREACH
					}#END IF
				
				 
				
				
				// Plugin
//     			case 'plugin':
				
				// Plugin Response
				case 'vmplg':
				case 'pluginresponse':
				// case 'order_done':
					
					
					
					
					// Load VmConfig
					if ( !class_exists( 'VmConfig' ) )
					{
						require( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php' );
					}
					VmConfig::loadConfig();
					
					// Fix for older VM with undefined constant
					if ( !defined( 'VM_VERSION' ) )
					{
						define( 'VM_VERSION', ( version_compare( vmVersion::$RELEASE, '2.9.0', '>=' ) ? 3 : 2 ) );
					}
					
					// For sure
					/*vmJsApi::jQuery();
					JHtml::_('behavior.formvalidator'  );*/
					
					
					// If OPC Enabled
					if ( VmConfig::get( 'oncheckout_opc', 1 ) == 1 )
					{
						
						# FIX от перехвата платежей Плагин kaznachey
						if ( JRequest::getVar( 'pelement' ) !== 'kaznachey' ){
							JRequest::setVar( 'view', JRequest::getVar( 'view' ) . '_bypv' );
						}
						
						
						require_once( OPC_FOR_VM_BYPV_PLUGIN_PATH . DS . 'helpers' . DS . 'cart_bypv.php' );
						require_once( OPC_FOR_VM_BYPV_PLUGIN_PATH . DS . 'controllers' . DS . 'cart_bypv.php' );
						
						// VM >= 2.9.9f
						if ( VM_VERSION == 3 && is_file( JPATH_VM_SITE . DS . 'controllers' . DS . 'vmplg.php' ) )
						{
							require_once( OPC_FOR_VM_BYPV_PLUGIN_PATH . DS . 'controllers' . DS . 'vmplg_bypv.php' );
						}
						else
						{
							require_once( OPC_FOR_VM_BYPV_PLUGIN_PATH . DS . 'controllers' . DS . 'pluginresponse_bypv.php' );
						}#END IF
						
						
						if ( JRequest::getVar( 'task' ) === 'editpayment' || JRequest::getVar( 'task' ) === 'edit_shipment' )
						{
							$this->redirectToCart_byPV();
						}#END IF
						
						// Load default language
						$extension = strtolower( 'plg_' . $this->_type . '_' . $this->_name );
						$lang      = JFactory::getLanguage();
						
						
						$langTag = $lang->getTag(); //example output format: en-GB
						$lang->load( $extension, JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name, $langTag, false, false );
						
						// Load custom language (method is in JPlugin Class)
						$this->loadLanguage( $extension, JPATH_SITE );
					}
					break;
				
				// View User
				case 'user':
					if ( JRequest::getCmd( 'task' ) === 'editaddresscheckout' )
					{
						$this->redirectToCart_byPV();
					}#END IF
					break;
			}#END SWITCH
		}#END IF
	}#END FN
    
    private function initDemoModule_byPV()
    {
    	if (!class_exists('modDemoOPCforVMbyPVHelper', FALSE))
    	{
    		$helper_path = JPATH_BASE . DS . 'modules' . DS . self::DEMO_MODULE . DS . 'helper.php';
    	
    		if (is_file($helper_path))
    		{
    			require_once($helper_path);
    		}
    	}
    	 
    	return class_exists('modDemoOPCforVMbyPVHelper', FALSE);
	}
    
    private function redirectToCart_byPV()
    {
    	$app = JFactory::getApplication();
    	$app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart', FALSE));
    }
	
	public function onContentBeforeDisplay ($context,&$article,&$params,$limitstart){ }// end function
	
	public function onBeforeCompileHead (){
		if (\JFactory::getApplication()->isAdmin() ) return ;
		vmJsApi::jQuery();
		JHtml::_('behavior.formvalidator'  );
		
		
	}// end function
	
	
}