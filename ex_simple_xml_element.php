<?php

// import class for adding CDATA with SimpleXML
/** 
 * 
 * Extension for SimpleXMLElement 
 * @author Alexandre FERAUD 
 * 
 */ 
class ExSimpleXMLElement extends SimpleXMLElement { 
	/** 
	* Add CDATA text in a node 
	* @param string $cdata_text The CDATA value  to add 
	*/ 
	public function addCData($cdata_text){ 
		$node= dom_import_simplexml($this); 
	 	$no = $node->ownerDocument; 
	 	$node->appendChild($no->createCDATASection($cdata_text)); 
	}

	public function addChildCData($name,$cdata_text){ 
		$child = $this->addChild($name); 
		$child->addCData($cdata_text); 
	} 

	public function appendXML($append){ 
		if($append){ 
			if (strlen(trim((string) $append))==0){ 
				$xml = $this->addChild($append->getName()); 
				foreach($append->children() as $child) { 
					$xml->appendXML($child); 
				} 
			} 
			else { 
				$xml = $this->addChild($append->getName(), (string) $append); 
			} 
			foreach($append->attributes() as $n => $v) { 
				$xml->addAttribute($n, $v); 
			} 
		} 
	} 
	
}

?>