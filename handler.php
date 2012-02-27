<?php

include 'handler.methods.php';

/* =============================================================================
   Main
   ========================================================================== */

extract(filter_options());

$agents_array   = file_get_cached_json('agents.json', null);
$data_array     = file_get_cached_json('data.json', 'filter_datajson');
$keywords_array = file_get_cached_json('keywords.json', 'filter_keywords');

$option_features = filter_features($option_features, $keywords_array);

$support_array = filter_supportmetrics($option_features, $agents_array, $data_array, 'supported');

if (!$option_noagent) {
	$useragent_array = filter_useragent($agents_array);

	filter_agents($support_array, $useragent_array);

	$support_array['agent'] = $useragent_array;

	$support_array['supported'] = isset($support_array['results'][$useragent_array['id']]) && version_compare($useragent_array['version'], $support_array['results'][$useragent_array['id']]) > -1;

	$support_array['upgradable'] = !$support_array['supported'] && isset($support_array['results'][$useragent_array['id']]);

	if ($option_barebones) {
		$support_array = array(
			'supported' => $support_array['supported'],
			'upgradable' => $support_array['upgradable']
		);
	}
}

if ($option_noagents) {
	unset($support_array['agents']);
}

if ($option_nofeatures) {
	unset($support_array['features']);
}

if ($option_noresult) {
	unset($support_array['result']);
}

if ($option_noresults) {
	unset($support_array['results']);
}
if(!empty($support_array['results']) && !empty($support_array['agents'])) {
  if ($option_format === 'js' || $option_format === 'json' || $option_callback) {
    if ($option_format === 'json' && !$option_callback) {
      header('Content-Type: application/json;charset=UTF-8');
    } else {
      header('Content-Type: text/javascript;charset=UTF-8');
    }

    if ($option_format === 'html' || $option_html) {
      $support_array['html'] = html_encode($support_array, $option_style, !$option_nocss);
    }

  
    $string = json_encode($support_array);

    if ($option_readable) {
      $string = json_readable($support_array);
    }

    if ($option_callback) {
      $string = $option_callback . '(' . $string . ')';
    }
  } else if ($option_format === 'html') {
    header('Content-Type: text/html;charset=UTF-8');

    $string = file_get_contents('tpl/html.html');
    $string = preg_replace('/<%= content %>/', html_encode($support_array, $option_style, !$option_nocss), $string);
  } else if ($option_format === 'xml') {
    header('Content-Type: text/xml;charset=UTF-8');

    if ($option_html) {
      $support_array['html'] = html_encode($support_array, $option_style, !$option_nocss);
    }

    $string = xml_encode($support_array);
  }
} else {
  $support_array = array('supported' => 'unknown');
  if($option_format === 'xml') {
    $string = xml_encode($support_array);
  } else if ($option_format === 'html') {
    $string = '';
  }else {
    $string = json_encode($support_array);
  }
}

exit($string);

?>