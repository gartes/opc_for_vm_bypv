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

/*** CSS ***/

JHtml::_('behavior.keepalive');

?>

<form method="post" id="bypv_cart" action="<?php echo $CART->CHECKOUT_URL; ?>" class="<?php echo ($CART->IS_PHASE_CHECKOUT ? 'checkout' : 'confirm'); ?> form-validate">
	<div class="cart">
	
		<?php // Continue Link ?>
	
		<?php //if ($CART->IS_CONTINUE_LINK) { ?>
		
			<div class="continue_link">
				<?php //echo $CART->CONTINUE_LINK_HTML; ?>
			</div>
			
		<?php //} ?>

		<?php $this->printHeader_byPV(1, 'COM_VIRTUEMART_CART_TITLE'); ?>
		
		<?php if ($CART->IS_EMPTY) { ?>
	
			<?php // Empty Cart ?>
		
			<p class="empty"><?php echo JText::_('COM_VIRTUEMART_EMPTY_CART'); ?></p>
			
		<?php } else { ?>

			<?php // Layout ?>
		
			<?php echo $this->loadTemplate('layout'); ?>
		
		<?php } ?>
	
		<?php // Hidden Form Inputs ?>
		
		<input type='hidden' name='order_language' value='<?php echo $CART->ORDER_LANGUAGE; ?>' />
		<input type='hidden' name='task' value='<?php echo $CART->CHECKOUT_TASK; ?>' />
		<input type='hidden' name='option' value='com_virtuemart' />
		<input type='hidden' name='view' value='cart' />
		
		<?php // Form Checksum (must be here - in the end of main form) ?>
		
		<input type='hidden' name='bypv_form_checksum' value='<?php echo $this->getFormChecksum_byPV(); ?>' />

	</div>
</form>