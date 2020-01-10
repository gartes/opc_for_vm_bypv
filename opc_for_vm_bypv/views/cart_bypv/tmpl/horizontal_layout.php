<?php defined ('_JEXEC') or die('Restricted access');

/*** TEMPLATE VARIABLES ***/

$LOGIN = $this->getLoginData_byPV();

?>

<div class="row-fluid">
	<div class="floatleft span8 vertical-separator">
		<?php // Product List ?>
        <?php echo $this->loadFormTemplate_byPV(VirtueMartViewCart_byPV::TPL_PRODUCT_LIST); ?>
        
		<?php // Coupon Code ?>
		<?php echo $this->loadFormTemplate_byPV(VirtueMartViewCart_byPV::TPL_COUPON_CODE); ?>
		      
    </div>
    
   
    
    
    
    <div class="floatleft span4 vertical-separator cart_top_right"> 
    
    <div class="cart_title-top_right"> 
    	<?php $this->printHeader_byPV(3, 'PLG_SYSTEM_OPC_FOR_VM_CART_SELECT_TOPRIGHT_TITLE'); ?>
    </div>
    
    <div class="bypv_cart_billing_text">
		<?php echo JText::_('PLG_SYSTEM_OPC_FOR_VM_BYPV_TEXT_TOP'); ?> 
	</div>


		<?php // Customer Type Select ?>     
        <?php echo $this->loadFormTemplate_byPV(VirtueMartViewCart_byPV::TPL_CUSTOMER_TYPE_SELECT); ?>
        

        <div id="cart_customer">
            <fieldset class="clean">
                <?php // Login ?>                
                <?php if (!$LOGIN->IS_USER_LOGGED) echo $this->loadFormTemplate_byPV(VirtueMartViewCart_byPV::TPL_LOGIN); ?>
            
                <?php // Billing Address ?>        
                <?php echo $this->loadFormTemplate_byPV(VirtueMartViewCart_byPV::TPL_BILLING_ADDRESS); ?>
        
                <?php // Shipping Address ?>        
                <?php echo $this->loadFormTemplate_byPV(VirtueMartViewCart_byPV::TPL_SHIPPING_ADDRESS); ?>
            </fieldset>
        </div>
    	<div class="clear"></div>
        
		
		<?php // Shipment ?>
		<?php echo $this->loadFormTemplate_byPV(VirtueMartViewCart_byPV::TPL_SHIPMENTS); ?>
   		<div class="clear"></div>    
		
		<?php // Payment ?>
		<?php echo $this->loadFormTemplate_byPV(VirtueMartViewCart_byPV::TPL_PAYMENTS); ?>
        <div class="clear"></div>
        

        <?php // Terms Of Service (VM2) ?>
        <?php echo $this->loadFormTemplate_byPV(VirtueMartViewCart_byPV::TPL_TOS); ?>
        
		<?php // Customer Comment (VM2) ?>
        <?php echo $this->loadFormTemplate_byPV(VirtueMartViewCart_byPV::TPL_COMMENT); ?>
        <div class="clear"></div>

		<?php // Cart Fields (VM3) ?>
        <?php echo $this->loadFormTemplate_byPV(VirtueMartViewCart_byPV::TPL_CART_FIELDS); ?>
        <div class="clear"></div>       
        
      
      <div id="orderCost">
       <div class="orderCost_text"><?php echo JText::_('COM_VIRTUEMART_CART_TOTAL'); ?>:</div>
      	<span class="total_mix_right"></span>
        
      </div>
      
        
        <?php // Checkout and Confirm Button ?>
        <?php echo $this->loadTemplate('buttons'); ?>
        <div class="clear"></div>         
        
        
        
    </div>
</div>    
 
  



<?php // External Modules ?>

<?php echo $this->loadFormTemplate_byPV(VirtueMartViewCart_byPV::TPL_EXTERNAL_MODULES); ?>


<div id="cart_panel"> 
	<fieldset class="clean">
		
		<?php // Logout ?>
		
		<?php if ($LOGIN->IS_USER_LOGGED) echo $this->loadFormTemplate_byPV(VirtueMartViewCart_byPV::TPL_LOGIN); ?>
      
<?php // Advertisements ?>

<?php echo $this->loadFormTemplate_byPV(VirtueMartViewCart_byPV::TPL_ADVERTISEMENTS); ?>

       
	</fieldset>
</div>








