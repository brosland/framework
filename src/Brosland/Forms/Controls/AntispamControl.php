<?php

namespace Brosland\Forms\Controls;

use Nette\Forms\Controls\TextInput,
	Nette\Forms\Form,
	Nette\Utils\Html;

/**
 * AntispamControl
 * add basic antispam feature to Nette forms.
 *
 * @version 0.4
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @license CC BY <http://creativecommons.org/licenses/by/3.0/cz/>
 */
class AntispamControl extends TextInput
{
	/** @var int  minimum delay [sec] to send form */
	public static $minDelay = 5;


	/**
	 * Register Antispam to Nette Forms
	 * @return void
	 */
	public static function register()
	{
		Form::extensionMethod('addAntispam', function(Form $form, $name = 'spam', $label = 'Toto pole vymažte', $msg = 'Bol odhalený pokus o zaslanie spamu.')
		{
			// "All filled" protection
			$form[$name] = new AntispamControl($label, NULL, NULL, $msg);

			// "Send delay" protection
			$form->addHidden('form_created', strtr(time(), '0123456789', 'jihgfedcba'))
				->addRule(
					function($item)
				{
					if (AntispamControl::$minDelay <= 0)
						return TRUE;  // turn off "Send delay protection"

					$value = (int) strtr($item->value, 'jihgfedcba', '0123456789');
					return $value <= (time() - AntispamControl::$minDelay);
				}, $msg
			);

			return $form;
		});
	}

	/**
	 * @param string|Html
	 * @param int
	 * @param int
	 * @param string
	 */
	public function __construct($label = '', $cols = NULL, $maxLength = NULL, $msg = '')
	{
		parent::__construct($label, $cols, $maxLength);

		$this->setDefaultValue('http://');
		$this->addRule(~Form::FILLED, $msg);
	}

	/**
	 * @return TextInput
	 */
	public function getControl()
	{
		$control = $this->addAntispamScript(parent::getControl());
		return $control;
	}

	/**
	 * @param Html
	 * @return Html
	 */
	protected function addAntispamScript(Html $control)
	{
		$control = Html::el('')->add($control);
		$control->add(Html::el('script', array ('type' => 'text/javascript'))
				->setHtml("
				$(document).ready(function(){
					$('#{$control[0]->id}').val(null).closest('.control-group').removeClass('control-group').hide();
				});"));

		return $control;
	}
}