<?php

/* =============================================================================
   Helper Methods
   ========================================================================== */

function is_set($val = undefined) {
	return isset($val);
}

function first_set() {
	return reset(array_filter(func_get_args(), 'is_set'));
}

function first_set_in_array($array = array()) {
	return reset(array_filter($array, 'first_set'));
}

function first_match($pattern = '', $subject = '', $else_string = null) {
	preg_match($pattern, $subject, $matches);

	array_shift($matches);
	array_push($matches, $else_string);

	return first_set_in_array($matches);
}

function get_file_json($filename = '', $assoc = true) {
	return json_decode(file_get_contents($filename), $assoc);
}

function get_cached_array($array_string = '', $requested_features_array = array()) {
	$json_filename  = 'data.json';
	if ($array_string === 'support') {
		$array_filename = 'cache/' . $array_string . '-' . implode('_', $requested_features_array) . '.php';
	} elseif ($array_string === 'features') {
		$array_filename = 'cache/' . $array_string . '-' . implode('_', $requested_features_array) . '.php';
	} else {
		$array_filename = 'cache/' . $array_string . '.php';
	}

	$json_filetime  = @filemtime($json_filename);
	$array_filetime = @filemtime($array_filename);

	$array_function = 'get_' . $array_string . '_array';

	if ($json_filetime != $array_filetime) {
		$json_array = get_file_json($json_filename);

		$array_array = $array_function($json_array, $requested_features_array);

		file_put_contents($array_filename, '<?php $array_array = ' . var_export($array_array, true) . '; ?>');

		touch($array_filename, $json_filetime);
	} else {
		include $array_filename;
	}

	return $array_array;
}



/* =============================================================================
   Get Agents Array
   ========================================================================== */

function get_agents_array(&$json_array = array()) {
	$return_array = array();
	$agents_array = &$json_array['agents'];

	foreach ($agents_array as $agentid_string => &$agent_array) {
		$return_array[$agentid_string] = $agent_array;

		unset($return_array[$agentid_string]['abbr']);
		unset($return_array[$agentid_string]['versions']);
	}

	return $return_array;
}



/* =============================================================================
   Get Features Array
   ========================================================================== */

function get_features_array(&$json_array = array(), &$requested_features_array = array()) {
	$return_array = array();
	$data_array   = &$json_array['data'];

	if ($requested_features_array) {
		foreach ($requested_features_array as $feature_string) {
			if (isset($data_array[$feature_string])) {
				$return_array[$feature_string] = $data_array[$feature_string]['title'];
			} else {
				return;
			}
		}
	}

	return $return_array;
}



/* =============================================================================
   Get Support Array
   ========================================================================== */

function get_support_array($json_array = array(), &$requested_features_array = array()) {
	$data_array = &$json_array['data'];

	$agentslist_array = array(
		'ie' => true,
		'firefox' => true,
		'chrome' => true,
		'safari' => true,
		'opera' => true,
		'ios_saf' => true,
		'op_mini' => true,
		'op_mob' => true,
		'android' => true
	);

	$return_array = array(
		'by_feature' => array(),
		'by_agent' => array(),
		'agents' => array()
	);

	$error_array = array();

	$return_by_feature_array =& $return_array['by_feature'];
	$return_by_agent_array   =& $return_array['by_agent'];
	$return_agents_array     =& $return_array['agents'];

	foreach ($requested_features_array as &$feature_string) {
		$feature_array = @$data_array[$feature_string];
		$stats_array   = @$feature_array['stats'];

		if ($stats_array) {
			$return_by_feature_array[$feature_string] = array();

			foreach ($stats_array as $agentid_string => $agentstats_array) {
				$version = get_agent_support_array($agentstats_array);

				if ($version) {
					$return_by_feature_array[$feature_string][$agentid_string] = $version;
					$return_by_agent_array[$agentid_string][$feature_string] = $version;

					if ($agentslist_array[$agentid_string]) {
						$return_agents_array[$agentid_string] = version_compare(
							@$return_agents_array[$agentid_string],
							$version
						) ? $version : @$return_agents_array[$agentid_string];
					}
				} else {
					$agentslist_array[$agentid_string] = false;
					unset($return_agents_array[$agentid_string]);
				}
			}
		} else {
			array_push($error_array, $feature_string);
		}
	}

	if (!empty($error_array)) {
		return array('supported' => false, 'error' => $error_array);
	} else {
		return $return_array;
	}
}

function get_agent_support_array($agent_stats_array = array()) {
	foreach ($agent_stats_array as $version => $supported) {
		if (preg_match('/y/', $supported)) {
			return $version;
		}
	}

	return false;
}



/* =============================================================================
   Get Unsupported Array
   ========================================================================== */

function get_unsupported_array(&$support_array = array(), &$user_agent_array = array()) {
	$return_array = array();

	if ($support_array && @$support_array['by_feature']) {
		foreach($support_array['by_feature'] as $feature_string => &$feature_array) {
			if (
				!isset($feature_array[$user_agent_array['id']]) ||
				version_compare(@$feature_array[$user_agent_array['id']], @$user_agent_array['version']) > -1
			) {
				array_push($return_array, $feature_string);
			}
		}
	} else {
		return;
	}

	return $return_array;
}



/* =============================================================================
   Get User Agent Array
   ========================================================================== */

function get_user_agent_array($agents_array) {
	$user_agent_string = $_SERVER['HTTP_USER_AGENT'];

	foreach($agents_array as $agent_string => &$agent_array) {
		$agent_sniffer = $agent_array['sniffer'];

		$agent_boolean = preg_match($agent_sniffer, $user_agent_string, $agent_matches);

		if ($agent_boolean) {
			$return_array = $agent_array;
			$return_array['version'] = first_set(@$agent_matches[1], @$agent_matches[2]);
			$return_array['id'] = $agent_string;

			unset($return_array['prefix']);
			unset($return_array['sniffer']);

			return $return_array;
		}
	}
}



/* =============================================================================
   Get Alternatives Array
   ========================================================================== */

function get_alternatives_array(&$agents_array = array(), &$support_array = array(), &$user_agent_array = array()) {
	$return_array = array();
	$user_agent_id_string   = $user_agent_array['id'];
	$user_agent_type_string = $user_agent_array['type'];

	if (isset($support_array['agents'])) {
		foreach ($support_array['agents'] as $agent_string => &$agent_version) {
			if ($user_agent_id_string !== $agent_string && $user_agent_type_string === $agents_array[$agent_string]['type']) {
				$return_array[$agent_string] = $agents_array[$agent_string];

				unset($return_array[$agent_string]['prefix']);
				unset($return_array[$agent_string]['sniffer']);
				unset($return_array[$agent_string]['type']);
			}
		}
	} else {
		return;
	}

	return $return_array;
}



/* =============================================================================
   Get Upgradable Array
   ========================================================================== */

function get_upgradable_array(&$support_array = array(), $user_agent_array = array()) {
	$return_array = $user_agent_array;

	unset($return_array['type']);
	unset($return_array['version']);

	return !!@$support_array['agents'][$user_agent_array['id']] ? $return_array : false;
}



/* =============================================================================
   HTML Encode
   ========================================================================== */

function html_encode_agents(&$agents_array, $requested_style_string = '') {
	$html = '';

	if ($agents_array) {
		foreach ($agents_array as $agent_string => &$agent_array) {
			$html .= html_encode_agent($agent_string, $agent_array, $requested_style_string);
		}
	}

	return $html;
}

function html_encode_agent(&$agent_string = '', &$agent_array, $requested_style_string = '') {
	$html = '';
	$html .= '<a class="caniuse-agt" href="' . @$agent_array['url'] . '" rel="external" target="_blank">';

	if ($requested_style_string !== 'text') {
		$html .= '<span class="caniuse-agt-ico caniuse-ico-' . @$agent_string . '"></span>';
	}

	if ($requested_style_string !== 'icon') {
		$html .= '<span class="caniuse-agt-ttl">' . @$agent_array['name'] . '</span>';
	}
	
	if ($requested_style_string === 'button') {
        	$html .= '<span class="caniuse-agt-txt">[Download]</span>';    
   	}


	$html .= '</a>';

	return $html;
}

function html_encode_feature($feature_string = '', $feature_name_string = '', $supported = true) {
	$html = '';
	$html .= '<a class="' . ($supported ? 'caniuse-yes' : 'caniuse-no') . '" href="http://caniuse.com/#search=' . $feature_string .'" rel="external" target="_blank">';
	$html .= $feature_name_string;
	$html .= '</a>';

	return $html;
}

function html_encode_features(&$return_array) {
	$features_array     = $return_array['features'];
	$unsupported_string = @implode(' ', $return_array['unsupported']);

	$html_array = array();

	foreach($features_array as $feature_string => &$feature_name_string) {
		array_push($html_array, html_encode_feature($feature_string, $feature_name_string, !preg_match('/' . $feature_string . '/', $unsupported_string)));
	}

	return implode(', ', $html_array);
}

function html_encode(&$return_array = array(), $requested_style_string = '', $requested_style_boolean = true) {
	$html = '';

	if (isset($return_array['error'])) {
		return $html;
	}

	if ($requested_style_boolean) {
		$html .= '<style>' . preg_replace('/[\s]+/', ' ', @file_get_contents('style.' . $requested_style_string . '.css')) . '</style>';
	}

	$html .= file_get_contents('tpl.' . $requested_style_string . '.html');

	if ($return_array['supported']) {
		$html = preg_replace('/\s*<% supported %>|<% \/supported %>/', '', $html);
		$html = preg_replace('/\s*<% supported_with_upgrade %>[\W\w]*?<% \/supported_with_upgrade %>/', '', $html);
		$html = preg_replace('/\s*<% unsupported %>[\W\w]*?<% \/unsupported %>/', '', $html);
	} elseif ($return_array['upgradable']) {
		$html = preg_replace('/\s*<% supported %>[\W\w]*?<% \/supported %>/', '', $html);
		$html = preg_replace('/\s*<% supported_with_upgrade %>|<% \/supported_with_upgrade %>/', '', $html);
		$html = preg_replace('/\s*<% unsupported %>[\W\w]*?<% \/unsupported %>/', '', $html);
	}
	else {
		$html = preg_replace('/\s*<% supported %>[\W\w]*?<% \/supported %>/', '', $html);
		$html = preg_replace('/\s*<% supported_with_upgrade %>[\W\w]*?<% \/supported_with_upgrade %>/', '', $html);
		$html = preg_replace('/\s*<% unsupported %>|<% \/unsupported %>/', '', $html);
	}

	$html = preg_replace('/<%= browserid %>/', $return_array['current']['id'], $html);
	$html = preg_replace('/<%= browserurl %>/', $return_array['current']['url'], $html);
	$html = preg_replace('/<%= alternatives %>/', html_encode_agents($return_array['alternatives'], $requested_style_string), $html);
	$html = preg_replace('/<%= features %>/', html_encode_features($return_array), $html);

	$html = preg_replace('/[\s]+/', ' ', $html);

	return $html;
}



/* =============================================================================
   XML Encode
   ========================================================================== */

function is_assoc($array) {
	return is_array($array) && (count( $array )==0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
}

function is_array_with_numeric ($array) {
	foreach ($array as $key => $value) {
		$keyFirstChar = substr($key, 0);
		$keyFirstCharIsAlpha = preg_match('/[A-z]/', $keyFirstChar);

		if (!$keyFirstCharIsAlpha) {
			return true;
		}
	}

	return false;
}

function xml_node_encode(&$array = array(), $node_name = 'node', $tab_indent = "") {
	$xml = '';
	$isArray = is_array($array) || is_object($array);
	$isAssoc = $isArray && is_assoc($array);
	$isAssocWithNumeric = $isAssoc && is_array_with_numeric($array);

	if ($isArray) {
		$xml .= "\n";

		foreach ($array as $key => &$value) {
			$isValueArray = is_array($value) || is_object($value);

			if ($isAssocWithNumeric) {
				$tagOpen = $node_name . ' name="' . $key . '"';
				$tagClose = $node_name;
			} else {
				$tagOpen = $isAssoc ? $key : $node_name;
				$tagClose = $tagOpen;
			}

			if ($isValueArray) {
				$xml .= $tab_indent . '<' . $tagOpen . '>';
				$xml .= xml_node_encode($value, $node_name, $tab_indent . "\t");
				$xml .= $tab_indent . '</' . $tagClose . '>';
			} else {
				$xml .= $tab_indent . '<' . $tagOpen . '>' . $value . '</' . $tagClose . '>';
			}

			$xml .= "\n";
		}
	} else {
		$xml .= htmlspecialchars($array, ENT_QUOTES);
	}

	return $xml;
}

function xml_encode($array, $node_block = 'nodes', $node_name = 'node') {
	$xml = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";

	$xml .= '<' . $node_block . '>';
	$xml .= xml_node_encode($array, $node_name, "\t");
	$xml .= '</' . $node_block . '>';

	return $xml;
}

?>