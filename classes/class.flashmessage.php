<?php

class FlashMessage {
	
	public static function addError($messages) {
		global $lang;
		self::addMessage('error', $lang->phrase('re_error_title'), $messages);
	}
	
	public static function addNotice($messages) {
		global $lang;
		self::addMessage('notice', $lang->phrase('general_notice_title'), $messages);
	}
	
	public static function addConfirmation($messages) {
		global $lang;
		self::addMessage('ok', $lang->phrase('re_ok_title'), $messages);
	}

	public static function addMessage($type, $title, $messages) {
		global $my, $gpc;
		$my->settings['messages'][] = array(
			'type' => $type,
			'title' => $gpc->save_str($title, false),
			'messages' => $gpc->save_str($messages, false)
		);
	}
	
	public static function show() {
		global $my, $tpl;
		$code = '';
		if (!empty($my->settings['messages'])) {
			foreach($my->settings['messages'] as $type => $messages) {
				$tpl->globalvars($messages);
				$code .= $tpl->parse('main/message_box');
			}
			unset($my->settings['messages']);
		}
		return $code;
	}
	
}