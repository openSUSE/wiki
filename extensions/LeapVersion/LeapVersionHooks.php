<?php

class LeapVersionHooks {
	public static function onParserBeforeInternalParse( &$parser, &$text, &$strip_state )
	{
		$versions = [
			'LEAP_VERSION' => '15.1',
			'LEAP_VERSION_OLD' => '15.0',
			'LEAP_VERSION_OLDER' => '42.3',
		];

		foreach ($versions as $key => $value) {
			$text = str_replace('{{'.$key.'}}', $value, $text);
		}
	}
}
