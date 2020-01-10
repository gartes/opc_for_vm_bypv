"undefined"===typeof VirtueMartCart_byPV&&(VirtueMartCart_byPV={});

var VirtueMartCart_byPV = {
		
	/*** Public (you can override externally according to your needs) ***/
		
	/**
	 * Style of LoaderMessage
	 * - value can be simple value ( OPACITY ) or complex object with SHOW and HIDE parameters ( { SHOW: 'STANDARD', HIDE: 'OPACITY' } ).
	 * 
	 * Predefined styles:
	 * - null = nothing - not recomended
	 * - STANDARD = show and hide simple overlay
	 * - OPACITY = opacity effect during simple overlay is appear and disapear
	 * - CENTER = overlay is growing "from center" and shrinking "to center" 
	 * - function with own style
	 * 		SHOW: function ($el_loader, $el_block) {}
	 * 		HIDE: function ($el_loader) {}
	 *  	- for example see LOADING_MESSAGE_DEFINITION below in code
	 */
	LOADING_MESSAGE_STYLE: {
		SHOW: 'CENTER',
		HIDE: 'OPACITY'
	},
	
	/**
	 * Selectors of blocks where loader will be showed
	 * - key is preddefined ID of action
	 * - value can be simple value with selector or javascript array of selectors 
	 */
	LOADING_MESSAGE_BLOCK_SELECTORS: {
		CART: '#bypv_cart',
		PRODUCT_LIST: '#bypv_cart_product_list',
		COUPON_CODE: '#bypv_cart_coupon_code',
		SHIPMENT: [ '#bypv_cart_product_list', '#bypv_cart_shipments' ],
		PAYMENT: [ '#bypv_cart_product_list', '#bypv_cart_payments' ],
		CUSTOMER_TYPE: [ '#bypv_cart_customer_type_select', '#cart_customer' ],
//		CUSTOMER_TYPE: [ '#bypv_cart_customer_type_select', '#bypv_cart_login', '#bypv_cart_billing_address', '#bypv_cart_shipping_address', '#bypv_cart_shipping_address_select' ],
		SHIPPING_ADDRESS: [ '#bypv_cart_shipping_address', '#bypv_cart_shipping_address_select' ],
//		ADDRESS_FIELDS: [ '#bypv_cart_shipments', '#bypv_cart_payments' ],
		LOGIN: '#cart_customer',
		LOGOUT: 'form#bypv_cart'
	},

	LOADING_MESSAGE_HTML: '<div class="bypv_loader"><div class="bypv_background"></div><div class="bypv_image"></div></div>',
	LOADING_MESSAGE_SELECTOR: 'div.bypv_loader',
	
	SYSTEM_MESSAGE_CONTAINER_SELECTOR: '#system-message-container',

	REMEMBER_FORM_FIELDS: true,
	
	CHECKED_USER_FIELDS: [ 'virtuemart_country_id', 'zip', 'euvatin' ],

	/*** Private (don't change) ***/

	CART_SELECTOR: '#bypv_cart',
	PRODUCT_LIST_SELECTOR: '#bypv_cart_product_list',
	PRODUCT_QUANTITY_SELECTOR: '.bypv_product_quantity',
	COUPON_CODE_SELECTOR: '#bypv_cart_coupon_code',
	SHIPMENT_SELECTOR: '#bypv_cart_shipments',
	PAYMENT_SELECTOR: '#bypv_cart_payments',
	CUSTOMER_TYPE_SELECT_SELECTOR: '#bypv_cart_customer_type_select',
	LOGIN_SELECTOR: '#bypv_cart_login',
	BILLING_ADDRESS_SELECTOR: '#bypv_cart_billing_address',
	SHIPPING_ADDRESS_SELECTOR: '#bypv_cart_shipping_address',
	SHIPPING_ADDRESS_SELECT_SELECTOR: '#bypv_cart_shipping_address_select',

	json_requests: [],
	json_request_progress: null,

	base_uri: '',

	form_submitted: false,
	form_event: null,
	form_initial_state_empty: true,
	
	shipments_incompatible_with_ajax: [],
	payments_incompatible_with_ajax: [],
	cached_methods: {},

	/*** Init Methods ***/
	
	initialize : function ()
	{
		var $ = jQuery;
		
		if ($(this.PRODUCT_LIST_SELECTOR).length > 0)
		{
			this.form_initial_state_empty = false;
		}
		
		VirtueMartCart_byPV.initFormEvents();
		VirtueMartCart_byPV.initProductListEvents();
		VirtueMartCart_byPV.initCouponEvents();
		VirtueMartCart_byPV.initShipmentsEvents();
		VirtueMartCart_byPV.initPaymentsEvents();
		VirtueMartCart_byPV.initCustomerTypeSelectEvents();
		VirtueMartCart_byPV.initLoginEvents();
		VirtueMartCart_byPV.initBillingAddressEvents();
		VirtueMartCart_byPV.initShippingAddressEvents();
		VirtueMartCart_byPV.initShippingAddressSelectEvents();
		
		
		
		
		
	},
	
	initFormEvents : function ()
	{
		var $ = jQuery;
	
		$(this.CART_SELECTOR)
			.submit(function (event) {
				if (VirtueMartCart_byPV.form_submitted === true) return false;
				
				VirtueMartCart_byPV.form_submitted = true;
				VirtueMartCart_byPV.form_event = event;
				
				setTimeout(function ()
				{
					if (VirtueMartCart_byPV.form_event.originalEvent.returnValue === false)
					{
						VirtueMartCart_byPV.form_submitted = false;
						$('input[type=submit]', VirtueMartCart_byPV.CART_SELECTOR).fadeTo(500, 1);
					}
					else
					{
						VirtueMartCart_byPV.clearFieldsLocal();
					}
				}, 500);
				
				$('input[type=submit]', VirtueMartCart_byPV.CART_SELECTOR).fadeTo(500, 0.5);
			})
			.keypress(function (event) {
				if (event.which == 13) {
					var $el_target = $(event.target);
					
					if (!$el_target.is("textarea") && !$el_target.is(":button,:submit")) {
						var focusNext = false;
						
						$(this).find(":input:visible:not([disabled],[readonly]), a").each(function () {
							if (this === event.target) {
								focusNext = true;
							}
							else if (focusNext){
								$(this).focus();
								return false;
							}
						});
						
						return false;
					}
				}
			});
			    
	},
	
	initProductListEvents : function ()
	{
		var $ = jQuery;
		var $el_product_list_form = $(this.PRODUCT_LIST_SELECTOR);
		
		$el_product_list_form.find('.bypv_product_quantity input.bypv_quantity')
			.change( function(event) {
				VirtueMartCart_byPV.checkProductQuantity(event.target);
			});
		
		
		
		
		$el_product_list_form.find('.bypv_product_quantity span.bypv_quantity_controls > input')
			.click( function(event) {
				
				VirtueMartCart_byPV.setProductQuantity(event.target);
			});
		
		$el_product_list_form.find('.bypv_product_quantity input.bypv_product_update')
			.click( function(event) {
				
				VirtueMartCart_byPV.updateProductQuantity(event.target);
				/*var totalOrder = $('.total .total_incl_tax').html();
				$('#orderCost span').html(totalOrder)*/

				
			});
	
		$el_product_list_form.find('input.bypv_product_remove')
			.click( function(event) {
				
				VirtueMartCart_byPV.dropProduct(event.target);
			});
		
		$('input[name=bypv_coupon_code]', this.PRODUCT_LIST_SELECTOR)
			.keypress(function(event) {
				if (event.which == 13) {
					event.preventDefault();
					VirtueMartCart_byPV.updateCouponCode(VirtueMartCart_byPV.PRODUCT_LIST_SELECTOR);
				}
			});
		
		$('.bypv_coupon_code_button', this.PRODUCT_LIST_SELECTOR)
			.click( function(event) {
				VirtueMartCart_byPV.updateCouponCode(VirtueMartCart_byPV.PRODUCT_LIST_SELECTOR);
			});
	},

	initCouponEvents : function ()
	{
		var $ = jQuery;
		
		$('input[name=bypv_coupon_code]', this.COUPON_CODE_SELECTOR)
			.keypress(function(event) {
				if (event.which == 13) {
					event.preventDefault();
					VirtueMartCart_byPV.updateCouponCode(VirtueMartCart_byPV.COUPON_CODE_SELECTOR);
				}
			});

		$('.bypv_coupon_code_button', this.COUPON_CODE_SELECTOR)
			.click( function(event) {
				VirtueMartCart_byPV.updateCouponCode(VirtueMartCart_byPV.COUPON_CODE_SELECTOR);
			});
	},
	
	initShipmentsEvents : function ()
	{
		var $ = jQuery;

		$('*[name=virtuemart_shipmentmethod_id]', this.SHIPMENT_SELECTOR)
			.change( function(event) {
				VirtueMartCart_byPV.updateShipment();
			});
	},
	
	initPaymentsEvents : function ()
	{
		var $ = jQuery;

		$('*[name=virtuemart_paymentmethod_id]', this.PAYMENT_SELECTOR)
			.change( function(event) {
				VirtueMartCart_byPV.updatePayment();
			});
	},
	
	initCustomerTypeSelectEvents : function ()
	{
		var $ = jQuery;
		
		$('*[name=bypv_customer_type]', this.CUSTOMER_TYPE_SELECT_SELECTOR)
			.change( function(event) {
				VirtueMartCart_byPV.updateCustomerForm();
			});
	},

	initLoginEvents : function ()
	{
		var $ = jQuery;

		$('input:text, input:password', this.LOGIN_SELECTOR)
			.keypress(function(event) {
				if (event.which == 13) {
					event.preventDefault();
					VirtueMartCart_byPV.login();
				}
			});
		
		$('input#bypv_login', this.LOGIN_SELECTOR)
			.click( function(event) {
				VirtueMartCart_byPV.login();
			});

		$('input#bypv_logout', this.LOGIN_SELECTOR)
			.click( function(event) {
				VirtueMartCart_byPV.logout();
			});
	},

	initBillingAddressEvents : function ()
	{
		var $ = jQuery;
		
        // Check if browser support Storage (HTML5)
        if (this.REMEMBER_FORM_FIELDS === true && typeof(Storage) !== "undefined" && typeof(JSON) !== "undefined")
        {
			VirtueMartCart_byPV.loadFieldsLocal('bt');

			var $el_address_block = $(this.BILLING_ADDRESS_SELECTOR, this.CART_SELECTOR);
			var $el_inputs = $el_address_block.find('input, select, textarea');
			
			$el_inputs
				.change(function (event) {
					VirtueMartCart_byPV.saveFieldLocal('bt', this);
				});
        }

		var $elements = $();

		$.each(this.CHECKED_USER_FIELDS, function () {
			$elements = $elements.add('*[name=bypv_billing_address_' + this + ']', this.BILLING_ADDRESS_SELECTOR);
		});
		
		$elements
			.change(function (event) {
				VirtueMartCart_byPV.updateShipmentAndPaymentForm();
			});
	},
	
	initShippingAddressEvents : function ()
	{
		var $ = jQuery;
		
        // Check if browser support Storage (HTML5)
        if (this.REMEMBER_FORM_FIELDS === true && typeof(Storage) !== "undefined" && typeof(JSON) !== "undefined")
        {
			VirtueMartCart_byPV.loadFieldsLocal('st');

			var $el_address_block = $(this.SHIPPING_ADDRESS_SELECTOR, this.CART_SELECTOR);
			var $el_inputs = $el_address_block.find('input, select, textarea');
			
			$el_inputs
				.change(function (event) {
					VirtueMartCart_byPV.saveFieldLocal('st', this);
				});
        }

		var $elements = $();

		$.each(this.CHECKED_USER_FIELDS, function () {
			$elements = $elements.add('*[name=bypv_shipping_address_' + this + ']', this.SHIPPING_ADDRESS_SELECTOR);
		});

		$elements
			.change(function (event) {
				VirtueMartCart_byPV.updateShipmentAndPaymentForm();
			});
	},
	
	initShippingAddressSelectEvents : function ()
	{
		var $ = jQuery;
		
		$('*[name=shipto]', this.SHIPPING_ADDRESS_SELECT_SELECTOR)
			.change(function (event) {
				VirtueMartCart_byPV.updateShippingAddress();
			});
	},

	initVirtueMartHooks : function ()
	{
		var $ = jQuery;

//		if (this.getVirtueMartVersion() == 2)
//		{
			if (typeof(Virtuemart) === 'object' && typeof(Virtuemart.productUpdate) === 'function')
			{
				VirtueMartCart_byPV.vmProductUpdateCache = Virtuemart.productUpdate;
				Virtuemart.productUpdate = VirtueMartCart_byPV.vmProductUpdate;
			}
//		}
//		else if (this.getVirtueMartVersion() == 3)
//		{
//			$('body').on('updateVirtueMartCartModule', function(e) {
//		        $('#vmCartModule').updateVirtueMartCartModule();
//		    });
//		}
	},
	
	/*** VirtueMart Overrided Methods ***/

	vmProductUpdateCache: null,
	
	vmProductUpdate: function(mod, refresh_cart_data)
	{
		var $ = jQuery;
		
		// Default Value = TRUE
		refresh_cart_data = (refresh_cart_data == null ? true : refresh_cart_data);

		if (refresh_cart_data === true)
		{
			VirtueMartCart_byPV.refreshCartData();
		}

		if (VirtueMartCart_byPV.getVirtueMartVersion() == 2 && mod == null)
		{
			mod = $('.vmCartModule');
		}

		VirtueMartCart_byPV.vmProductUpdateCache(mod);
	},
	
	/*** Refresh Methods ***/
	
	refreshCartData : function ()
	{
		
		
		
		var $ = jQuery;
		
		
		
		
		var $el_product_list = $(VirtueMartCart_byPV.PRODUCT_LIST_SELECTOR);
		var $el_task_checkout = $('input:hidden[name=task][value=checkout]', VirtueMartCart_byPV.CART_SELECTOR);
		
		if ($el_product_list.length > 0 && $el_task_checkout.length > 0)
		{
			return this.sendJSONRequest('refreshCartBlocksJS_byPV', this.LOADING_MESSAGE_BLOCK_SELECTORS['PRODUCT_LIST']);
		}
		else
		{
			if (this.form_initial_state_empty === true)
			{
				VirtueMartCart_byPV.showLoaders('form#bypv_cart');
				window.document.location.reload();
				return false;
			}
			else return this.sendJSONRequest('refreshCartJS_byPV', this.LOADING_MESSAGE_BLOCK_SELECTORS['CART']);
		}
	},
	
	/*** Product Methods ***/
	
	checkProductQuantity : function (el_quantity)
	{
		var $ = jQuery;

		var $el_product_block = $(el_quantity, this.PRODUCT_LIST_SELECTOR).parents(this.PRODUCT_QUANTITY_SELECTOR);
		
		
		var $el_step_order_level = $el_product_block.find('input.bypv_step_order_level');


		if (!$(el_quantity).hasClass('bypv_quantity') || $el_product_block.length == 0 || $el_step_order_level.length == 0) {
			return false;
		}

		var step_order_level_array = $el_step_order_level[0].value.split(':');
		
		if (step_order_level_array.length != 3) {
			return false;
		}

		var quantity = parseInt(el_quantity.value);
		var min_order_level = parseInt(step_order_level_array[0]);
		var step_order_level = parseInt(step_order_level_array[1]);
		var max_order_level = parseInt(step_order_level_array[2]);
		
		if (isNaN(quantity)) quantity = 0;
		if (isNaN(min_order_level)) min_order_level = 1;
		if (isNaN(step_order_level)) step_order_level = 1;
		if (isNaN(max_order_level)) max_order_level = 0;
		
		var remainder = quantity % step_order_level;

		if (remainder > 0) {
			quantity = quantity - remainder;
		}
		
		if (quantity < min_order_level) {
			quantity = min_order_level;
		}

		if (max_order_level > 0 && quantity > max_order_level) {
			quantity = max_order_level;
		}

		el_quantity.value = quantity;
		
		return true;
	},
	
	setProductQuantity : function (el_control)
	{
		
		var $ = jQuery;

		var $el_control = $(el_control, this.PRODUCT_LIST_SELECTOR);
		var $el_product_block = $el_control.parents(this.PRODUCT_QUANTITY_SELECTOR);
		var $el_quantity = $el_product_block.find('input.bypv_quantity');
		var $el_step_order_level = $el_product_block.find('input.bypv_step_order_level');
		
		if ($el_product_block.length == 0 || $el_quantity.length == 0 || $el_step_order_level.length == 0) {
			return false;
		}

		var step_order_level_array = $el_step_order_level[0].value.split(':');
		
		if (step_order_level_array.length != 3) {
			return false;
		}

		var el_quantity = $el_quantity[0];
		var quantity = parseInt(el_quantity.value);
		var step_order_level = parseInt(step_order_level_array[1]);
		
		if (isNaN(quantity) || isNaN(step_order_level)) {
			return false;
		}

		if ($el_control.hasClass('bypv_quantity_plus')) {
			el_quantity.value = quantity + step_order_level; 
		}
		else if ($el_control.hasClass('bypv_quantity_minus')) {
			el_quantity.value = quantity - step_order_level;
		}
		else {
			return false;
		}
		
		return $(el_quantity).change();
	},

	dropProduct : function (el_remove)
	{
		var $ = jQuery;
		var $el_row = $(el_remove).closest('.product_cart')

		//var $el_row = $(el_remove, this.PRODUCT_LIST_SELECTOR).parents('.bypv_product_quantity');
		 
		
		
		var $el_quantity = $el_row.find(/*this.PRODUCT_QUANTITY_SELECTOR + */' input.bypv_quantity');

		var hidBat = $el_row.find('input.bypv_product_remove');

console.log ( hidBat);




		if ($el_quantity.length == 0)
		{
			var productId = $el_row.data('bypvOpcForVmProductId');
			
			if (productId)
			{
				$(el_remove).after('<input type="hidden" name="bypv_quantity[' + productId + ']" class="bypv_quantity" value="0" />');
			}
			else return false;
		}
		else
		{
			$el_quantity[0].value = 0;
		}

		
		return VirtueMartCart_byPV.updateProductQuantity(hidBat);
	},

	updateProductQuantity: function (el)
	{
		
		var $ = jQuery;

		var $el_row = $(el, this.PRODUCT_LIST_SELECTOR).parents('.bypv_product_quantity');
		var $el_quantity = $el_row.find('input.bypv_quantity');


		if ($el_row.length == 0 || $el_quantity.length == 0) {
			return false;
		}

		return this.sendJSONRequest('setCartDataJS_byPV', $el_quantity.serialize(), this.LOADING_MESSAGE_BLOCK_SELECTORS['PRODUCT_LIST']);
	},

	/*** Coupon Methods ***/
	
	updateCouponCode: function (parent_selector)
	{
		var $ = jQuery;

		var $el_coupon_code = $('input[name=bypv_coupon_code]', parent_selector);

		if ($el_coupon_code.length == 0) {
			return false;
		}
		
		message_block_selector = (parent_selector == this.PRODUCT_LIST_SELECTOR ? 'PRODUCT_LIST' : 'COUPON_CODE');

		if ($.trim($el_coupon_code[0].value) != '') {
			if (this.sendJSONRequest('setCartDataJS_byPV', $el_coupon_code.serialize(), this.LOADING_MESSAGE_BLOCK_SELECTORS[message_block_selector])) {
				$el_coupon_code.val(null);
				return true;
			}
		}
		return false;
	},
	
	/*** Shipment and Payment Methods ***/

	updateShipment: function ()
	{
		var $ = jQuery;

		var $el_shipment = $('input:radio[name=virtuemart_shipmentmethod_id]', this.SHIPMENT_SELECTOR);

		if ($el_shipment.length === 0) {
			return false;
		}

		return this.sendJSONRequest('setCartDataJS_byPV', $el_shipment.serialize(), this.LOADING_MESSAGE_BLOCK_SELECTORS['SHIPMENT']);
	},

	updatePayment: function ()
	{
		var $ = jQuery;
		
		var $el_payment = $('input:radio[name=virtuemart_paymentmethod_id]', this.PAYMENT_SELECTOR);
		
		if ($el_payment.length == 0) {
			return false;
		}
		
		return this.sendJSONRequest('setCartDataJS_byPV', $el_payment.serialize(), this.LOADING_MESSAGE_BLOCK_SELECTORS['PAYMENT']);
	},

	/*** Customer Form Methods ***/

	updateCustomerForm: function ()
	{
		var $ = jQuery;
		
		var $el_customer_type = $('input:radio[name=bypv_customer_type]', this.CUSTOMER_TYPE_SELECT_SELECTOR);
		
		if ($el_customer_type.length == 0) {
			return false;
		}

		var fields_data_bt = this.getFieldsLocal('bt');
		var fields_data_st = this.getFieldsLocal('st');
		var address_data = '';
		
		if (fields_data_bt) address_data += '&address_bt=' + JSON.stringify(fields_data_bt);
		if (fields_data_st) address_data += '&address_st=' + JSON.stringify(fields_data_st);
		
		return this.sendJSONRequest('updateCustomerFormJS_byPV', $el_customer_type.serialize() + address_data, this.LOADING_MESSAGE_BLOCK_SELECTORS['CUSTOMER_TYPE']);
	},

	/*** Shipping Address Methods ***/

	updateShippingAddress: function ()
	{
		var $ = jQuery;

		var $el_shipto = $('input:radio[name=shipto]', this.SHIPPING_ADDRESS_SELECT_SELECTOR);

		if ($el_shipto.length == 0) {
			return false;
		}

		var fields_data_st = this.getFieldsLocal('st');
		var address_data = '';
		
		if (fields_data_st) address_data += '&address_st=' + JSON.stringify(fields_data_st);

		return this.sendJSONRequest('updateShippingAddressJS_byPV', $el_shipto.serialize() + address_data, this.LOADING_MESSAGE_BLOCK_SELECTORS['SHIPPING_ADDRESS']);
	},

	/*** Address Methods ***/
	
	updateShipmentAndPaymentForm: function ()
	{
		var $ = jQuery;
		
		var $el_address_blocks = $(this.BILLING_ADDRESS_SELECTOR + ', ' + this.SHIPPING_ADDRESS_SELECTOR, this.CART_SELECTOR);

		var $el_inputs = $el_address_blocks.find('input, select, textarea').not('input[type=password]');
		
		if ($el_inputs.length == 0) {
			return false;
		}
		
		return this.sendJSONRequest('setCartDataJS_byPV', $el_inputs.serialize(), this.LOADING_MESSAGE_BLOCK_SELECTORS['ADDRESS_FIELDS']);
	},
	
	overrideFieldsData: function (text)
	{
		var overrideValue = (window.confirm(text) ? 1 : 0);
		
		return this.sendJSONRequest('overrideFieldsDataJS_byPV', 'override=' + overrideValue, this.LOADING_MESSAGE_BLOCK_SELECTORS['CART']);
	},

	/*** Login / Logout Methods ***/

	login: function ()
	{
		var $ = jQuery;

		var $el_inputs = $(this.LOGIN_SELECTOR, this.CART_SELECTOR).find('input, select, textarea');

		if ($el_inputs.length == 0) {
			return false;
		}
		
		this.clearFieldsLocal();
		return this.sendJSONRequest('loginJS_byPV', $el_inputs.serialize(), this.LOADING_MESSAGE_BLOCK_SELECTORS['LOGIN']);
	},
	
	logout: function ()
	{
		this.clearFieldsLocal();
		return this.sendJSONRequest('logoutJS_byPV', null, this.LOADING_MESSAGE_BLOCK_SELECTORS['LOGOUT']);
	},

	/*** JSON Request ***/

	sendJSONRequest: function (task, request_data, loading_message_block_selectors)
	{
		var $ = jQuery;

		if (task) {
			this.json_requests.push({
				'task': task,
				'request_data': (request_data ? request_data.replace("'", "%27") : null), // Encode char ' to %27
				'loading_message_block_selectors': loading_message_block_selectors,
			});
		}

		if (this.json_request_progress == null) {
			if (this.json_requests.length > 0) {
				this.json_request_progress = this.json_requests.pop();
			}
			else {
				return false;
			}
		}
		else {
			if (loading_message_block_selectors) {
				VirtueMartCart_byPV.showLoaders(loading_message_block_selectors);
			}

			return true;
		}
		
		if (this.json_request_progress['loading_message_block_selectors']) {
			VirtueMartCart_byPV.showLoaders(this.json_request_progress['loading_message_block_selectors']);
		}
		
		$.getJSON(Virtuemart.vmSiteurl /*this.base_uri*/
			+ "index.php?option=com_virtuemart&nosef=1&view=cart&task=" + this.json_request_progress['task']
			+ "&format=json"
			+ (window.vmLang ? window.vmLang : '') // Enabled Language Javascript Fix
			+ '&' + $('input[name=bypv_form_checksum]', this.CART_SELECTOR).serialize(),

			this.json_request_progress['request_data'],
			
			function(data, textStatus) {
				
				
				if ($.isPlainObject(data.replaceHTML)) {
					$.each(data.replaceHTML, function (cover_id, html) {
						var el_selector = '#' + cover_id;

						var finalizeRequest = function () {
							VirtueMartCart_byPV.replaceBlock(el_selector, html);

							VirtueMartCart_byPV.fixLoaders();

							if ($.isPlainObject(data.evalJS) && data.evalJS[cover_id]) {
								try {
									eval(data.evalJS[cover_id]);
								} catch (e) {
								}
							}

							VirtueMartCart_byPV.hideLoaders(el_selector);
						};






						if (VirtueMartCart_byPV.isLoader(el_selector)) {
							finalizeRequest();
						} else {
							VirtueMartCart_byPV.showLoaders(el_selector);
							finalizeRequest();

							// TODO: We probably remove next line in future - evalOtherJS (for example TOS init) is called after html replace  
							// setTimeout(finalizeRequest, 200);
						}
					});
				}

				var total_incl_tax =  jQuery('.total .total_incl_tax').html()
				$('#orderCost span').html(total_incl_tax)
				
				


				var $el_system_message_container = $(VirtueMartCart_byPV.SYSTEM_MESSAGE_CONTAINER_SELECTOR);

				if ($el_system_message_container.length > 0) {
					if (data.systemMessageHTML) {
						var initial_height = $el_system_message_container.height();

						$el_system_message_container.replaceWith(data.systemMessageHTML); 
						$el_system_message_container = $(VirtueMartCart_byPV.SYSTEM_MESSAGE_CONTAINER_SELECTOR);

						var target_scrollTop = $el_system_message_container.offset().top;
						var target_height = $el_system_message_container.height();

						if ($(document).scrollTop() > target_scrollTop) {
							$('html, body').animate({
								'scrollTop': (target_scrollTop < 50 ? 0 : target_scrollTop - 50)
							}, 200, 'swing');
						}
						
						$el_system_message_container
							.css('height', initial_height).css('opacity', 0)
							.animate({
								'height': target_height + 'px',
								'opacity':  '1.0'
							}, 200, function () {
								VirtueMartCart_byPV.fixLoaders();
							});
					}
					else {
						$el_system_message_container.animate({
							'height': 0,
							'opacity': 0
						}, 100, function() {
							$(this).css('height', '').css('opacity', '');
							$el_system_message_container.empty();
							VirtueMartCart_byPV.fixLoaders();
						});
					}
				}
				
				if (data.evalOtherJS) {
					try {
						eval(data.evalOtherJS);
					}
					catch (e) {}
				}

				if (data.formChecksum) {
					$('input[name=bypv_form_checksum]', VirtueMartCart_byPV.CART_SELECTOR).val(data.formChecksum);
				}
			}
		)
		.always(function() {
			VirtueMartCart_byPV.hideLoaders(VirtueMartCart_byPV.json_request_progress['loading_message_block_selectors']);

			VirtueMartCart_byPV.json_request_progress = null;
			VirtueMartCart_byPV.sendJSONRequest();
			$('body').trigger('updateVirtueMartCartModule');
		});;
		
		return true;
	},
	
	replaceBlock: function (block_selector, html)
	{
		var $ = jQuery;
		
		var methodIdsIncompatibleWithAjax = $([]);
			
		if (block_selector === VirtueMartCart_byPV.CART_SELECTOR || block_selector === VirtueMartCart_byPV.SHIPMENT_SELECTOR)
		{
			if (VirtueMartCart_byPV.shipments_incompatible_with_ajax.length > 0)
			{
				$(VirtueMartCart_byPV.shipments_incompatible_with_ajax).each(function (i, methodId)
				{
					methodIdsIncompatibleWithAjax = methodIdsIncompatibleWithAjax.add(['#bypv_cart_shipment_' + methodId]);
				});
			}
		}
		
		if (block_selector === VirtueMartCart_byPV.CART_SELECTOR || block_selector === VirtueMartCart_byPV.PAYMENT_SELECTOR)
		{
			if (VirtueMartCart_byPV.payments_incompatible_with_ajax.length > 0)
			{
				$(VirtueMartCart_byPV.payments_incompatible_with_ajax).each(function (i, methodId)
				{
					methodIdsIncompatibleWithAjax = methodIdsIncompatibleWithAjax.add(['#bypv_cart_payment_' + methodId]);
				});
			}
		}

		var $elementsToMove = $();
		var reloadCart = false;
		
		if (methodIdsIncompatibleWithAjax.length > 0)
		{
			$(methodIdsIncompatibleWithAjax).each(function (i, methodId)
			{
				var $elementToMove = $(methodId, block_selector);
				var $elementToReplace = $(methodId, html);
				
				if ($elementToReplace.length > 0)
				{
					if ($elementToMove.length == 0 && VirtueMartCart_byPV.cached_methods[methodId])
					{
						$elementToMove = $(VirtueMartCart_byPV.cached_methods[methodId]);
					}
					
					if ($elementToMove.length == 0)
					{
						reloadCart = true;
						return false;
					}
					else
					{
						$elementsToMove = $elementsToMove.add($elementToMove);
					}
				}
				else if ($elementToMove.length > 0)
				{
					$elementToMove.find('link').appendTo('head');
					VirtueMartCart_byPV.cached_methods[methodId] = $elementToMove[0];
				}
			});
		}
		
		if (reloadCart)
		{
			VirtueMartCart_byPV.showLoaders('form#bypv_cart');
			window.document.location.reload();
			return false;
		}

		$(block_selector).replaceWith(html);

		if ($elementsToMove.length > 0)
		{
			$elementsToMove.each(function (i, elementToMove)
			{
				var $elementToReplace = $('#' + elementToMove.id, block_selector);

				$elementLabelToMove = $('input[type=radio][name]:first + label', $elementToReplace);
				
				$elementToReplace[0].parentNode.replaceChild(elementToMove, $elementToReplace[0]);
				
				if ($elementLabelToMove.length > 0)
				{
					$('input[type=radio][name]:first + label', '#' + elementToMove.id)
						.replaceWith($elementLabelToMove);
				}
			});
		}


		if ( block_selector === '#bypv_cart_shipments'){
			setTimeout(function () {

			},2000);

			$( '#CityRecipient , #nova_pochta_RecipientAddress').chosen();
			VirtueMartCart_byPV.setShowOn();


			console.log( block_selector )
		}

	},


	setShowOn :function(){
		var $ = jQuery ;
		/**
		 * Method to check condition and change the target visibility
		 * @param {jQuery}  target
		 * @param {Boolean} animate
		 */
		function linkedoptions(target, animate) {
			var showfield = true,
				jsondata  = target.data('showon') || [],
				itemval, condition, fieldName, $fields;

			// Check if target conditions are satisfied
			for (var j = 0, lj = jsondata.length; j < lj; j++) {
				condition = jsondata[j] || {};
				fieldName = condition.field;
				$fields   = $('[name="' + fieldName + '"], [name="' + fieldName + '[]"]');

				condition['valid'] = 0;

				// Test in each of the elements in the field array if condition is valid
				$fields.each(function() {
					var $field = $(this);

					// If checkbox or radio box the value is read from properties
					if (['checkbox', 'radio'].indexOf($field.attr('type')) !== -1) {
						if (!$field.prop('checked')) {
							// unchecked fields will return a blank and so always match a != condition so we skip them
							return;
						}
						itemval = $field.val();
					}
					else {
						// select lists, textarea etc. Note that multiple-select list returns an Array here
						// se we can always tream 'itemval' as an array
						itemval = $field.val();
						// a multi-select <select> $field  will return null when no elements are selected so we need to define itemval accordingly
						if (itemval == null && $field.prop("tagName").toLowerCase() == "select") {
							itemval = [];
						}
					}

					// Convert to array to allow multiple values in the field (e.g. type=list multiple)
					// and normalize as string
					if (!(typeof itemval === 'object')) {
						itemval = JSON.parse('["' + itemval + '"]');
					}

					// for (var i in itemval) loops over non-enumerable properties and prototypes which means that != will ALWAYS match
					// see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/for...in
					// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/getOwnPropertyNames
					// use native javascript Array forEach - see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/forEach
					// We can't use forEach because its not supported in MSIE 8 - once that is dropped this code could use forEach instead and not have to use propertyIsEnumerable
					//
					// Test if any of the values of the field exists in showon conditions
					for (var i in itemval) {
						// See https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/propertyIsEnumerable
						// Needed otherwise we pick up unenumerable properties like length etc. and !: will always match one of these  !!
						if (!itemval.propertyIsEnumerable(i)) {
							continue;
						}
						// ":" Equal to one or more of the values condition
						if (jsondata[j]['sign'] == '=' && jsondata[j]['values'].indexOf(itemval[i]) !== -1) {
							jsondata[j]['valid'] = 1;
						}
						// "!:" Not equal to one or more of the values condition
						if (jsondata[j]['sign'] == '!=' && jsondata[j]['values'].indexOf(itemval[i]) === -1) {
							jsondata[j]['valid'] = 1;
						}

					}

				});

				// Verify conditions
				// First condition (no operator): current condition must be valid
				if (condition['op'] === '') {
					if (condition['valid'] === 0) {
						showfield = false;
					}
				}
				// Other conditions (if exists)
				else {
					// AND operator: both the previous and current conditions must be valid
					if (condition['op'] === 'AND' && condition['valid'] + jsondata[j - 1]['valid'] < 2) {
						showfield = false;
						condition['valid'] = 0;
					}
					// OR operator: one of the previous and current conditions must be valid
					if (condition['op'] === 'OR' && condition['valid'] + jsondata[j - 1]['valid'] > 0) {
						showfield = true;
						condition['valid'] = 1;
					}
				}
			}

			// If conditions are satisfied show the target field(s), else hide.
			// Note that animations don't work on list options other than in Chrome.
			if (target.is('option')) {
				target.toggle(showfield);
				target.attr('disabled', showfield ? false : true);
				// If chosen active for the target select list then update it
				var parent = target.parent();
				if ($('#' + parent.attr('id') + '_chzn').length) {
					parent.trigger("liszt:updated");
					parent.trigger("chosen:updated");
				}
			}

			animate = animate
				&& !target.hasClass('no-animation')
				&& !target.hasClass('no-animate')
				&& !target.find('.no-animation, .no-animate').length;

			if (animate) {
				(showfield) ? target.slideDown() : target.slideUp();

				return;
			}

			target.toggle(showfield);
		}

		/**
		 * Method for setup the 'showon' feature, for the fields in given container
		 * @param {HTMLElement} container
		 */
		function setUpShowon(container) {
			container = container || document;

			var $showonFields = $(container).find('[data-showon]');

			// Setup each 'showon' field
			for (var is = 0, ls = $showonFields.length; is < ls; is++) {
				// Use anonymous function to capture arguments
				(function() {
					var $target = $($showonFields[is]), jsondata = $target.data('showon') || [],
						field, $fields                           = $();

					// Collect an all referenced elements
					for (var ij = 0, lj = jsondata.length; ij < lj; ij++) {
						field   = jsondata[ij]['field'];
						$fields = $fields.add($('[name="' + field + '"], [name="' + field + '[]"]'));
					}

					// Check current condition for element
					linkedoptions($target);

					// Attach events to referenced element, to check condition on change and keyup
					$fields.on('change keyup', function() {
						linkedoptions($target, true);
					});
				})();
			}
		}

		setUpShowon();
	},

	
	/*** Loader Message ***/
	
	LOADING_MESSAGE_DEFINITION: {
		'STANDARD': {
			SHOW: function ($el_loader, $el_block)
			{
				$el_loader
					.css('top', $el_block[0].offsetTop + 'px')
					.css('left', $el_block[0].offsetLeft + 'px')
					.css('width', $el_block[0].offsetWidth + 'px')
					.css('height', $el_block[0].offsetHeight + 'px')
				;
			}
		},
		'OPACITY': {
			SHOW: function ($el_loader, $el_block)
			{
				// Initital position, size and style
				$el_loader
					.css('top', $el_block[0].offsetTop + 'px')
					.css('left', $el_block[0].offsetLeft + 'px')
					.css('width', $el_block[0].offsetWidth + 'px')
					.css('height', $el_block[0].offsetHeight + 'px')
					.css('opacity', 0)
				;
				
				// Target style
				$el_loader.animate({
					'opacity': 1
				}, 200);
			},
			HIDE: function ($el_loader)
			{
				$el_loader.animate({
					'opacity': 0
				}, 200);
			}
		},
		'CENTER': {
			SHOW: function ($el_loader, $el_block)
			{
				// Initital position and size
				$el_loader
					.css('top', ($el_block[0].offsetTop + $el_block[0].offsetHeight / 2) + 'px')
					.css('left', ($el_block[0].offsetLeft + $el_block[0].offsetWidth / 2) + 'px')
					.css('width', 0)
					.css('height', 0)
				;

				// Target position and size
				$el_loader.animate({
					'top': $el_block[0].offsetTop + 'px',
					'left': $el_block[0].offsetLeft + 'px',
					'width': $el_block[0].offsetWidth + 'px',
					'height': $el_block[0].offsetHeight + 'px'
				}, 200);
			},
			HIDE: function ($el_loader)
			{
				$el_loader.animate({
					'top': ($el_loader[0].offsetTop + $el_loader[0].offsetHeight / 2) + 'px',
					'left': ($el_loader[0].offsetLeft + $el_loader[0].offsetWidth / 2) + 'px',
					'width': 0,
					'height': 0
				}, 200);
			}
		}
	},
	
	isLoader: function (element_selector)
	{
		var $ = jQuery;

		var result = false;
		var $el_loaders = null;
		var is_loader_func = function () {
			return $el_loaders.length > 0 && !$el_loaders.hasClass('bypv_hide');
		};

		$el_loaders = $(element_selector + ' + ' + VirtueMartCart_byPV.LOADING_MESSAGE_SELECTOR); 
		result = is_loader_func();
		
		if (result === false) {
			$el_loaders = $(element_selector).parents().next(VirtueMartCart_byPV.LOADING_MESSAGE_SELECTOR);
			result = is_loader_func();
		}
			
		return result;
	},
	
	showLoaders: function (element_selectors)
	{
		var $ = jQuery;
		
		var loaderFunction = this.LOADING_MESSAGE_STYLE;
		
		if ($.isPlainObject(loaderFunction)) {
			loaderFunction = loaderFunction['SHOW'];
		}

		if (!$.isFunction(loaderFunction)) {
			if (this.LOADING_MESSAGE_DEFINITION[loaderFunction]) {
				loaderFunction = this.LOADING_MESSAGE_DEFINITION[loaderFunction]['SHOW'];
			}
		}

		if (!$.isFunction(loaderFunction)) {
			return false;
		}
		
		if (!$.isArray(element_selectors)) {
			element_selectors = [ element_selectors ];
		}

		$.each(element_selectors, function (i) {
			var $el_block = $(element_selectors[i]);
			
			if ($el_block.length === 0) {
				return true;
			}

			if (VirtueMartCart_byPV.isLoader(element_selectors[i])) {
				return true;
			}
			
			var $el_loader = $(VirtueMartCart_byPV.LOADING_MESSAGE_HTML); 

			$el_block.after($el_loader);
			
			var $el_loader_block = $el_block.children('fieldset');
			if ($el_loader_block.length === 0) $el_loader_block = $el_block;
			
			loaderFunction($el_loader, $el_loader_block);
			
			$el_loader.queue(function () {
				VirtueMartCart_byPV.fixLoaders($el_loader);
				
				$(this).dequeue();
			});
		});
	},
	
	hideLoaders: function (element_selectors)
	{
		var $ = jQuery;

		var loaderFunction = this.LOADING_MESSAGE_STYLE;
		
		if ($.isPlainObject(loaderFunction)) {
			loaderFunction = loaderFunction['HIDE'];
		}

		if (!$.isFunction(loaderFunction)) {
			if (this.LOADING_MESSAGE_DEFINITION[loaderFunction]) {
				loaderFunction = this.LOADING_MESSAGE_DEFINITION[loaderFunction]['HIDE'];
			}
		}

		if (element_selectors == null) {
			element_selectors = this.CART_SELECTOR;
		}
		
		if (!$.isArray(element_selectors)) {
			element_selectors = [ element_selectors ];
		}

		$.each(element_selectors, function (i) {
			$(element_selectors[i] + ' + ' + VirtueMartCart_byPV.LOADING_MESSAGE_SELECTOR).each(function () {
				var $el_loader = $(this);
				
				$el_loader.addClass('bypv_hide');

				if ($.isFunction(loaderFunction)) {
					loaderFunction($el_loader);
				}

				$el_loader.queue(function () {
					$(this).remove();
				});
			});
		});
	},
	
	fixLoaders: function ($el_loaders)
	{
		var $ = jQuery;

		$el_loaders = $($el_loaders);
		
		if ($el_loaders.length == 0) {
			$el_loaders = $(VirtueMartCart_byPV.LOADING_MESSAGE_SELECTOR);
		}

		$el_loaders.each(function () {
			var $el_loader = $(this);

			if ($el_loader.hasClass('bypv_hide')) {
				return true;
			}
			
			var $el_loader_block = $el_loader.prev().children('fieldset');
			if ($el_loader_block.length == 0) $el_loader_block = $el_loader.prev();
			
			$el_loader
				.css('top', $el_loader_block[0].offsetTop + 'px')
				.css('left', $el_loader_block[0].offsetLeft + 'px')
				.css('width', $el_loader_block[0].offsetWidth + 'px')
				.css('height', $el_loader_block[0].offsetHeight + 'px')
			;
		});
	},
	
	/*** Fields Storage ***/
	
	saveFieldLocal : function (type, el)
	{
		if (el.getAttribute('type') === 'password' || el.getAttribute('name') === 'shipto') return;
		
		var shipto = null;
		
		if (type == 'st')
		{
			var $ = jQuery;
			shipto = $('*[name=shipto]:checked', this.SHIPPING_ADDRESS_SELECT_SELECTOR).val();
			
			if (shipto === '-1') return;
		}
		
		var data = sessionStorage.getItem('bypv.virtuemartcart.' + type);
		
		try {
			data = JSON.parse(data);
		}
		catch (e) {}
		
		if (data === null) data = {};

		if (type == 'st') {
			if (typeof(data[shipto]) === "undefined") data[shipto] = {};
			data[shipto][el.name] = el.value;
		}
		else data[el.name] = el.value;

		sessionStorage.setItem('bypv.virtuemartcart.' + type, JSON.stringify(data));
	},

	getFieldsLocal : function (type)
	{
		// Check if browser support Storage (HTML5)
        if (this.REMEMBER_FORM_FIELDS !== true && typeof(Storage) === "undefined" || typeof(JSON) === "undefined") return;

		var $ = jQuery;
		var shipto = null;
		
		if (type == 'st')
		{
			shipto = $('*[name=shipto]:checked', this.SHIPPING_ADDRESS_SELECT_SELECTOR).val();
			
			if (shipto === '-1') return;
		}

		var data = sessionStorage.getItem('bypv.virtuemartcart.' + type);
		
		try {
			data = JSON.parse(data);
			if (data !== null && type == 'st') data = data[shipto];
		}
		catch (e) {}

		return data;
	},
	
	loadFieldsLocal : function (type)
	{
		var $ = jQuery;
		var shipto = null;
		
		if (type == 'st')
		{
			shipto = $('*[name=shipto]:checked', this.SHIPPING_ADDRESS_SELECT_SELECTOR).val();
			
			if (shipto === '-1') return;
		}

		var data = sessionStorage.getItem('bypv.virtuemartcart.' + type);
		
		try {
			data = JSON.parse(data);
			if (data !== null && type == 'st') data = data[shipto];
		}
		catch (e) {}

		if (data === null) return;

		var $el = null;

		for (var el_name in data) {
			$el = $('*[name=' + el_name + ']', this.CART_SELECTOR);
			
			if ($el.length > 0 && $el.val() !== data[el_name])
			{
				$el.val(data[el_name]);
			}
		}
	},
	
	clearFieldsLocal : function (bt, st)
	{
		// Check if browser support Storage (HTML5)
        if (this.REMEMBER_FORM_FIELDS !== true && typeof(Storage) === "undefined" || typeof(JSON) === "undefined") return;

		bt = (bt == null ? true : bt);
		st = (st == null ? true : st);

		if (bt === true) sessionStorage.setItem('bypv.virtuemartcart.bt', null);
		if (st === true) sessionStorage.setItem('bypv.virtuemartcart.st', null);
	},
	
	getVirtueMartVersion: function ()
	{
		if (typeof(Virtuemart) === "object")
		{
			return (typeof(Virtuemart.updateContent) === "function" ? 3 : 2);
		}
		
		return null;
	}

};

/*** Document OnLoad ***/

jQuery( function($) {
	VirtueMartCart_byPV.initialize();
	VirtueMartCart_byPV.initVirtueMartHooks();
	
	
	
});




	function maskJqInitStart(el){
		var $=jQuery,
			aInd=0;
		var msk='+38(000)000-00-00';
		jQuery(el).mask(msk,{
            onKeyPress: function (v, event, currentField, options) {
                $('body').trigger('maskJqOnKeyPress');
                var mobOperatorID = '';

                if (v.length < 7) {
                    aInd = 0;
                    $('#MobOPR').remove();
                }

                if (v.length == 7) {
                    var pat = /^.{4}(\d{3})/g;


                    var result = v.match(/\d+/g);
                    $.each(mobOperator, function (i, o) {
                        if (result[1] == +o.k) {
                            mobOperatorID = o.o
                        }
                        aInd = 1;
                    });

                    if (mobOperatorID) {
                        console.log($(el).parent())

                        $(el).parent().find('#MobOPR').remove();
                        $(el).parent().prepend($('<div />', {
                            id: 'MobOPR',
                            class: mobOperatorID
                        }));
                    }
                }
            },
				onComplete:function(cep){
					$('body').trigger('maskJqOnComplete');
					console.log('CEP Completed!:'+cep);
				},
				onInvalid:function(val,e,f,invalid,options){
					var error=invalid[0];
					console.log("Digit: ",error.v," is invalid for the position: ",error.p,". We expect something like: ",error.e);
				},
			});
		}




jQuery( function($) {
	$(document).ready(function(){
		var total_incl_tax =  jQuery('.total .total_incl_tax').html();
		$('#orderCost span').html(total_incl_tax);
		
		
		/*	var phone_field = $('#bypv_billing_address_phone_2_field');
		maskJqInitStart (phone_field);						 	// инициализация маски телефона.
		$('body').on('maskJqOnComplete', function  (){
			$(phone_field).addClass('success');
		});*/
		
		
		
		
	})	
})







