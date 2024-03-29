<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Juergen Furrer <juergen.furrer@gmail.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


/**
 * 'tceFunc' for the 'jftcaforms' extension.
 *
 * @author     Juergen Furrer <juergen.furrer@gmail.com>
 * @package    TYPO3
 * @subpackage tx_jftcaforms
 */
class tx_jftcaforms_tceFunc
{
	/**
	 * This will render a spinner to choose one value from a defined range.
	 * 
	 * @param	array		$PA An array with additional configuration options.
	 * @param	object		$fobj TCEForms object reference
	 * @return	string		The HTML code for the TCEform field
	 */
	public function getExtSpinner($PA, &$fObj)
	{
		$conf = $PA['fieldConf']['config'];

        // Define the unique vars
        $id_spinner = uniqid('tceforms-spinner-');
        $id_checkbox = uniqid('tceforms-check-');
        $var = uniqid('spinner_');

        // define the options
        if (is_numeric($conf['width'])) {
            $option[] = "width: {$conf['width']}";
        }
        $lower = 0;
        if (is_numeric($conf['range']['lower'])) {
            $lower = $conf['range']['lower'];
            $option[] = "minValue: {$lower}";
        }
        if (is_numeric($conf['range']['upper'])) {
            $option[] = "maxValue: {$conf['range']['upper']}";
        }

        // 
        $default = (is_numeric($conf['default']) ? $conf['default'] : $lower);
        $value = (is_numeric($PA['itemFormElValue']) ? $PA['itemFormElValue'] : $default);

        $emptyValue = ($conf['emptyValue'] ? $conf['emptyValue'] : '0');
        if (! is_numeric($PA['itemFormElValue']) && $emptyValue) {
            $disabled = true;
        } else {
            $disabled = false;
        }

        $option[] = "disabled: ".($disabled ? 'true' : 'false');

        // get the pagerenderer
        $pagerender = $GLOBALS['TBE_TEMPLATE']->getPageRenderer();

        if ($emptyValue) {
            $pagerender->addExtOnReadyCode("
            Ext.get('{$id_checkbox}').on('click', function(obj1, obj2) {
                if (obj2.checked) {
                    Ext.get('{$id_spinner}').set({value: ''});
                    {$var}.disable();
                } else {
                    {$var}.enable();
                    Ext.get('{$id_spinner}').set({value: '{$default}'});
                }
            });");
            $checkboxCode = '<input type="checkbox" class="checkbox" id="'.$id_checkbox.'" name="'.$PA['itemFormElName'].'_cb"'.($disabled ? ' checked="checked"' : '').'>';
        }

        // Add the Spinner Script
        $pagerender->addCssFile(t3lib_extMgm::extRelPath('jftcaforms') . 'res/extjs/ux/css/Spinner.css');
        $pagerender->addJsFile(t3lib_extMgm::extRelPath('jftcaforms')  . 'res/extjs/ux/Spinner.js');
        $pagerender->addJsFile(t3lib_extMgm::extRelPath('jftcaforms')  . 'res/extjs/ux/SpinnerField.js');

        // Add the spinner
        $pagerender->addExtOnReadyCode("
        var {$var} = new Ext.ux.form.SpinnerField({
            ".implode(",\n	", $option)."
        });
        {$var}.applyToMarkup('{$id_spinner}');");

        return '' .
        '<div class="t3-form-field t3-form-field-flex">' .
            '<table><tr><td>' .
                $checkboxCode .
            '</td><td>' .
                '<input type="text" name="'.$PA['itemFormElName'].'" value="'.($disabled ? '' : $value).'" id="'.$id_spinner.'"/>' .
            '</td></tr></table>' .
        '</div>';
	}


	/**
	 * This will render a color-picker tt the tca.
	 * 
	 * @param	array		$PA An array with additional configuration options.
	 * @param	object		$fobj TCEForms object reference
	 * @return	string		The HTML code for the TCEform field
	 */
	public function getColorPicker($PA, &$fObj)
	{
		$conf = $PA['fieldConf']['config'];

		$pickerObj = uniqid('ColorPicker');
		$id_picker = uniqid('tceforms-colorpicker-');
		$id_checkbox = uniqid('tceforms-check-');
		$preview = uniqid('tceforms-preview-');

		$value = ($PA['itemFormElValue'] ? $PA['itemFormElValue'] : '');
		$value = str_replace('#', '', $value);
		if (strlen($value) > 0 && ! preg_match("/[0-9a-f]{6}/i", $value)) {
			if ($value != 'on') {
				t3lib_div::devLog('Value "'.$value.'" is no valid HEX-value', 'jftcaforms', 1);
			}
			$value = '';
		}

		$emptyValue = ($conf['emptyValue'] ? $conf['emptyValue'] : '0');
		if (! $value && $emptyValue) {
			$disabled = true;
		} else {
			$disabled = false;
		}

		$checkObserve = null;
		if ($emptyValue) {
			$checkboxCode = '<input type="checkbox" class="checkbox" id="'.$id_checkbox.'" name="'.$PA['itemFormElName'].'_cb"'.($disabled ? ' checked="checked"' : '').' />';
			$checkObserve .= "
Event.observe('{$id_checkbox}', 'change', function(event) {
	if (this.checked) {
		$('$id_picker').value = '';
		$('$preview').setStyle({ backgroundColor: 'transparent' });
	}
	$('$id_picker').disabled = this.checked;
});";
		}
		$checkObserve .= "
Event.observe('$id_picker', 'change', function(event) {
	var reg = /[0-9a-f]{6}/i;
	if (! reg.test($('$id_picker').value)) {
		$('$id_picker').value = '';
	} else {
		$('$id_picker').value = $('$id_picker').value.toLowerCase();
	}
});";

		// get the pagerenderer
		$pagerender = $GLOBALS['TBE_TEMPLATE']->getPageRenderer();

		// Laod ExtJs
		$pagerender->loadExtJs();

		// Add the colorpicker scripts
		$pagerender->addCssFile(t3lib_extMgm::extRelPath('jftcaforms') . 'res/colorpicker/css/colorpicker.css');
		$pagerender->addJsFile(t3lib_extMgm::extRelPath('jftcaforms') . 'res/colorpicker/js/colorpicker.js', 'text/javascript', false);

		$code = "
$pickerObj = new Object();
$pickerObj.init = function(){
	if ($('$id_picker') == undefined || !Ext.isReady) {
		window.setTimeout(\"$pickerObj.init();\", 20);
	} else {
		var cp".md5($id_picker)." = new colorPicker('{$id_picker}', {
			color:'#".($value ? $value : '000000')."',
			previewElement:'{$preview}'
		});{$checkObserve}
	}
};
$pickerObj.init();";
		// Add the colorpicker
		if ($fObj->inline->isAjaxCall) {
			$pagerender->addJsInlineCode("ColorPicker", $code, false);
		} else {
			$pagerender->addExtOnReadyCode($code);
		}


		return '' .
		'<div class="t3-form-field t3-form-field-flex">' .
			'<table><tr><td>' .
				$checkboxCode .
			'</td><td>' .
				'#<input type="text" name="'.$PA['itemFormElName'].'" id="'.$id_picker.'" value="'.$value.'" size="6"'.($disabled ? ' disabled="disabled"' : '').' onfocus="blur()" />' .
			'</td>' .
			'<td style="padding:4px;border:1px solid #888;background-color:#fff;">' .
				'<div id="'.$preview.'" style="width:15px;height:15px;"></div>' .
			'</td></tr></table>' .
		'</div>';
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jftcaforms/lib/class.tx_jftcaforms_tceFunc.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jftcaforms/lib/class.tx_jftcaforms_tceFunc.php']);
}

