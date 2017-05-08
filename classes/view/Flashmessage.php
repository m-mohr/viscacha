<?php

namespace Viscacha\View;

class FlashMessage {
	
	public static function addError($messages) {
		global $my, $lang;
		$my->settings['messages'][] = array(
			'type' => 'error',
			'title' => $lang->phrase('re_error_title'),
			'messages' => $messages
		);
	}
	
	public static function addNotice($messages) {
		global $my, $lang;
		$my->settings['messages'][] = array(
			'type' => 'notice',
			'title' => $lang->phrase('general_notice_title'),
			'messages' => $messages
		);
	}
	
	public static function addConfirmation($messages) {
		global $my, $lang;
		$my->settings['messages'][] = array(
			'type' => 'ok',
			'title' => $lang->phrase('re_ok_title'),
			'messages' => $messages
		);
	}
	
	public static function show() {
		global $my, $tpl;
		$code = '';
		if (!empty($my->settings['messages'])) {
			foreach($my->settings['messages'] as $type => $messages) {
				$tpl->assignVars($messages);
				$code .= $tpl->parse('main/message_box');
			}
			unset($my->settings['messages']);
		}
		return $code;
	}
	
}