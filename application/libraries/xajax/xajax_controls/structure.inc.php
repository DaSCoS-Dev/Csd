<?php
/*
	File: structure.inc.php

	HTML Control Library - Structure Tags

	Title: xajax HTML control class library

	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	@package xajax
	@version $Id: structure.inc.php,v 1.1 2013-02-11 00:49:40 developer Exp $
	@copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Section: Description
	
	This file contains the class declarations for the following HTML Controls:
	
	- div, span
*/

class clsDiv extends xajaxControlContainer
{
	function clsDiv($aConfiguration=array())
	{
		xajaxControlContainer::xajaxControlContainer('div', $aConfiguration);

		$this->sClass = '%block';
	}
}

class clsSpan extends xajaxControlContainer
{
	function clsSpan($aConfiguration=array())
	{
		xajaxControlContainer::xajaxControlContainer('span', $aConfiguration);

		$this->sClass = '%inline';
	}
}
