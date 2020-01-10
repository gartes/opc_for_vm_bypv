<?php defined ('_JEXEC') or die('Restricted access');

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

/*** TEMPLATE VARIABLES ***/ 

$CART = $this->getCartData_byPV();
$PRODUCT_LIST = $this->getProductListData_byPV();
$COUPON_CODE = $this->getCouponCodeData_byPV();

?>
<div id="bypv_cart_product_list" class="cart_block">
    <div class="wrap_product_cart">
    <?php foreach ($PRODUCT_LIST->PRODUCTS as $PRODUCT_ID => $PRODUCT) { ?>
    
    <div class="product_cart span12 floatleft" data-bypv-opc-for-vm-product-id="<?php echo $PRODUCT_ID; ?>">
    	<?php foreach ($PRODUCT_LIST->PRODUCT_COLS as $COL) { ?>
			
            
			<?php if ($COL->ID == 'NAME') { ?>
				<div class="wrap_product_cart_Left span4 floatleft">
					<?php if ($PRODUCT->SHOW_IMAGE) { ?>
						<div class="image">
							<?php echo $PRODUCT->IMAGE_HTML; ?>
						</div>
					<?php } ?>
				</div> 
			<?php } ?>
             
             
              
			<?php if ($COL->ID == 'NAME') { ?> 
            <div class="wrap_product_cart_Right span8 floatleft">   
            
               <div class="wwwww"><input type="button" class="bypv_product_remove image_button" title="<?php echo JText::_('COM_VIRTUEMART_CART_DELETE') ?>" value=" " /></div>
           		<div class="text wrap_product_cart_text">		
					<?php echo $PRODUCT->LINK_NAME_HTML . $PRODUCT->CUSTOM_FIELDS_HTML; ?>
				</div>
                

                
                
            </div>      
			<?php } ?>  
               
                         

			       

        

			<?php if ($COL->ID == 'QUANTITY') { ?>
            <div class="wrap_product_cart_Right2 span8 floatleft"> 
				<div class="quantity">
					<div class="bypv_product_quantity">
                    	<div class="bypv_product_quantity_name"><?php echo JText::_('COM_VIRTUEMART_CART_QUANTITY'); ?></div>
                    
						<?php if ($COL->SHOW_QUANTITY_CONTROLS) { ?>
								
									<input type="hidden" class="bypv_step_order_level" value="<?php echo $PRODUCT->STEP_ORDER_LEVEL; ?>" />
								
									<input type="text"
									   title="<?php echo JText::_('COM_VIRTUEMART_CART_UPDATE'); ?>"
									   class="bypv_quantity" size="3" maxlength="4"
									   name="bypv_quantity[<?php echo $PRODUCT_ID; ?>]"
									   value="<?php echo $PRODUCT->QUANTITY; ?>"
									/>
	
									<span class="bypv_quantity_controls quantity-controls"> 
										<input type="button" class="bypv_quantity_plus quantity-controls image_button" title="<?php echo JText::_('PLG_SYSTEM_OPC_FOR_VM_BYPV_QUANTITY_PLUS') ?>" value=" " />
										<input type="button" class="bypv_quantity_minus quantity-controls image_button" title="<?php echo JText::_('PLG_SYSTEM_OPC_FOR_VM_BYPV_QUANTITY_MINUS') ?>" vlaue=" " />
									</span>
                                    
									<span class="bypv_product_update">	
									<input type="button" class="bypv_product_update image_button" title="<?php echo JText::_('COM_VIRTUEMART_CART_UPDATE') ?>" value=" " />
                                    </span>
                                    
                                    
									<input type="hidden" class="bypv_product_remove image_button" title="<?php echo JText::_('COM_VIRTUEMART_CART_DELETE') ?>" value=" " />
									<?php if ($COL->SHOW_DROP_BUTTON) { ?>
										
									<?php } ?>
									
								<?php } else { ?>
	
									<?php if ($CART->IS_PHASE_CHECKOUT) { ?>
										<input type="hidden"
										   class="bypv_quantity"
										   name="bypv_quantity[<?php echo $PRODUCT_ID; ?>]"
										   value="<?php echo $PRODUCT->QUANTITY; ?>"
										/>
									<?php } ?>
								
									<?php echo $PRODUCT->QUANTITY; ?>
									
								<?php } ?>
							</div>
						</div>
                        
                        <div class="price_excl_tax"> <?php
                                
                                
                                if ( isset($COL->SHOW_ORIGINAL_AND_DISCOUNTED) &&   $COL->SHOW_ORIGINAL_AND_DISCOUNTED && $PRODUCT->IS_DISCOUNTED) { ?>
                                    <span class="original"><?php echo $PRODUCT->PRICE_EXCL_TAX_ORIGINAL; ?></span>
                                    <?php echo $PRODUCT->PRICE_EXCL_TAX; ?>
                            <?php } else { ?>
                            <?php echo $PRODUCT->TOTAL_INCL_TAX; ?>
                            <?php } ?>
                        </div>
                                
                        
                        </div>
					<?php } ?>

  
            
    	<?php } ?>
        </div>
        <div class="clear"></div>
    <?php } ?>
    </div>



	<div id="orderTotalNew">
    <div class="orderTotalNewLeft span8 floatleft">
    	<div class="orderTotal_title">
        	<span><?= JText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_PRICES_TOTAL_0'); ?>:</span>
        </div>
    </div>
    
    
    
    
    <?php 
	foreach ($PRODUCT_LIST->PRICE_COLS as $COL) {
		if ($COL->ID == 'TOTAL_INCL_TAX') { ?>
        
    	<div class="orderTotalNewRight span4 floatleft">   
			<div class="total_incl_tax total_predvarit">
            	<span><?php echo $PRODUCT_LIST->SUBTOTAL->TOTAL_INCL_TAX; ?></span>
            </div>
		</div> 
		<?php 
		} 
		 
		
		foreach ($PRODUCT_LIST->TAX_RULES as $ROW_CLASS => $RULES) { ?>
			<?php // ROW_CLASS = db_tax_rule OR tax_rule OR da_tax_rule ?>
			<div class="Wrap_orderTotalNewLeft">
			<?php foreach ($RULES as $RULE) { ?>
			<div class="orderTotalNewLeft span8 floatleft">
                <div class="<?php echo $ROW_CLASS; ?>">
					<?php echo $RULE->NAME; ?></div>
            </div>
            <div class="orderTotalNewRight span4 floatleft">          
 					<?php foreach ($PRODUCT_LIST->PRICE_COLS as $COL) { ?>
						<?php if ($COL->ID == 'DISCOUNT') { ?>
							<div class="discount"><?php echo $RULE->DISCOUNT; ?></div>
						<?php } ?>
						<?php if ($COL->ID == 'TOTAL_EXCL_TAX') { ?>
							<div class="total_excl_tax"><?php echo $RULE->TOTAL_EXCL_TAX; ?></div>
						<?php } ?>
						
						<?php if ($COL->ID == 'TAX') { ?>
							<div class="tax"><?php echo $RULE->TAX; ?></div>
						<?php } ?>
						
						<?php if ($COL->ID == 'TOTAL_INCL_TAX') { ?>
							<div class="total_incl_tax"><?php echo $RULE->TOTAL_INCL_TAX; ?></div>
						<?php } ?>
					<?php } ?>
			</div>	
            <div class="clear"></div>
			<?php } ?>
			</div>
		<?php }?>
        
		<div class="total">
        	<div class="orderTotalNewLeft span8 floatleft">
            	<div class="total_mix">
					<span><?php echo JText::_('COM_VIRTUEMART_CART_TOTAL'); ?>:</span>
                </div>
            </div>
            
		
			<?php foreach ($PRODUCT_LIST->PRICE_COLS as $COL) { ?>
            <div class="orderTotalNewRight span4 floatleft">  
				<?php if ($COL->ID == 'DISCOUNT') { ?>
					<div class="discount"><?php echo $PRODUCT_LIST->TOTAL->DISCOUNT; ?></div>
				<?php } ?>
				
				<?php if ($COL->ID == 'TOTAL_EXCL_TAX') { ?>
					<div class="total_excl_tax"><?php echo $PRODUCT_LIST->TOTAL->TOTAL_EXCL_TAX; ?></div>
				<?php } ?>
				
				<?php if ($COL->ID == 'TAX') { ?>
					<div class="tax"><?php echo $PRODUCT_LIST->TOTAL->TAX; ?></div>
				<?php } ?>
				
				<?php if ($COL->ID == 'TOTAL_INCL_TAX') { ?>
					<div class="total_incl_tax total_mix_data">
						<?php echo $PRODUCT_LIST->TOTAL->TOTAL_INCL_TAX; ?>
                    </div>
				<?php } ?>
            </div>    
			<?php } ?>
			
		</div>
        <div class="clear"></div>
		
		
	<?php 	
	}?>
    
    
    
    <?php //echo '<pre>'; print_r (); echo '</pre>'; ?>
    
    </div>    
    
</div>

<!-- END NEW -->


