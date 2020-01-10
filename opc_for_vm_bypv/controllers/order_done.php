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
	
	if(!class_exists('VmController')) require(VMPATH_ADMIN.DS.'helpers'.DS.'vmcontroller.php');





class VirtueMartControllerOrder_done extends VmController
{
	

	
	
	public function __construct($config = array()){
		parent::__construct();
		$this->addViewPath(JPATH_PLUGINS.DS.'system'.DS.'opc_for_vm_bypv'.DS.'views');
	}#END FN
	
	
	
	
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
		parent::display( $cachable , $urlparams);
		
	}#END FN
	
	
	
}#END CLASS