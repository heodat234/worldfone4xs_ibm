<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function send_xml_over_post($url, $xml) {
	$ch = curl_init();
	$headers = array(
	    "Content-type: text/xml",
	    "Content-length: " . strlen($xml),
	    "Connection: close",
	);
	curl_setopt($ch, CURLOPT_URL, $url);

	// For xml, change the content-type.
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // ask for results to be returned

	// Send to remote and return data to caller.
	$result = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$err = curl_error($ch);
	curl_close($ch);
    $now = date("Y-M-d H:i:s");
    $file = "xml_logs.txt";         
    $fd = fopen (APPPATH . "/helpers/" . $file, 'a');
    
	if($httpcode != 200) {
        $content = " - DATETIME: $now\n - URL: $url\n - XML: $xml\n - HTTPCODE: $httpcode\n - ERROR: $err\n\n";
		fwrite($fd, $content);
        fclose($fd);
		throw new Exception("Code: {$httpcode}. Message: {$err}");
	} else {
        $content = " - DATETIME: $now\n - URL: $url\n - XML: $xml\n - HTTPCODE: $httpcode\n - RESPONSE: $result\n\n";
        fwrite($fd, $content);
        fclose($fd);
		return $result;
	}
}

function xml_to_array($root) {
    $result = array();

    if ($root->hasAttributes()) {
        $attrs = $root->attributes;
        foreach ($attrs as $attr) {
            $result['@attributes'][$attr->name] = $attr->value;
        }
    }

    if ($root->hasChildNodes()) {
        $children = $root->childNodes;
        if ($children->length == 1) {
            $child = $children->item(0);
            if ($child->nodeType == XML_TEXT_NODE) {
                $result['_value'] = $child->nodeValue;
                return count($result) == 1
                    ? $result['_value']
                    : $result;
            }
        }
        $groups = array();
        foreach ($children as $child) {
            if (!isset($result[$child->nodeName])) {
                $result[$child->nodeName] = xml_to_array($child);
            } else {
                if (!isset($groups[$child->nodeName])) {
                    $result[$child->nodeName] = array($result[$child->nodeName]);
                    $groups[$child->nodeName] = 1;
                }
                $result[$child->nodeName][] = xml_to_array($child);
            }
        }
    }

    return $result ? $result : "";
}

function array_to_xml(array $arr, SimpleXMLElement $xml)
{
    foreach ($arr as $k => $v) {
        is_array($v)
            ? array_to_xml($v, $xml->addChild($k))
            : $xml->addChild($k, $v);
    }
    return $xml;
}
