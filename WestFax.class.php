<?php

/*
	Created By : Prashant Jethwa
    Date       : 29th June, 2017
    Email      : codebucket.co@gmail.com
    Website    : http://code-bucket.co/

	Functions
	-------------
	1. Constructor
		Arguments
			1. Product id (Provided by WestFax)
			2. Username
			3. Password

	2. sendFax: Call this function to send a fax using WestFax API
		Arguments
			1. Cover From
			2. Document Path
			3. Phone Numbers in array Ex. array('12345678')
			4. Job Name
			5. Billing Code

		Response will be as below
		------------------------------
			Array
			(
			    [ApiResultOfString] => Array
			        (
			            [Success] => true
			            [ErrorString] => Array
			                (
			                )

			            [InfoString] => Array
			                (
			                )

			            [Result] => bcfd4b37-ffaf-4ffa-bab9-ddfb776a0620
			        )

			    [ApiResultOfString_attr] => Array
			        (
			            [xmlns:xsi] => http://www.w3.org/2001/XMLSchema-instance
			            [xmlns:xsd] => http://www.w3.org/2001/XMLSchema
			        )

			)

	3. getFaxStatus: Call this function to get status of sent fax using API
		Arguments
			1. Job Id provided by WestFax API

		Response will be as below
		------------------------------
			Array
			(
			    [ApiResultOfListOfJobStatusContainer] => Array
			        (
			            [Success] => true
			            [ErrorString] => Array
			                (
			                )

			            [InfoString] => Array
			                (
			                )

			            [Result] => Array
			                (
			                    [JobStatusContainer] => Array
			                        (
			                            [JobId] => bcfd4b37-ffaf-4ffa-bab9-ddfb776a0620
			                            [JobState] => Production
			                            [QuerySuccess] => true
			                            [JobEstimate] => 0
			                            [Calls] => Array
			                                (
			                                    [CallContainer] => Array
			                                        (
			                                            [PhoneNumber] => 5105868243
			                                            [CallResult] => NoFaxDevice
			                                            [PagesSent] => 0
			                                            [JobPages] => 1
			                                            [DurationSeconds] => 61
			                                            [CompleteUtc] => 6/29/2017 10:31:29 AM
			                                            [FaxConnectionSpeed] => 33600
			                                            [FaxRemoteCsid] => Array
			                                                (
			                                                )

			                                        )

			                                )

			                        )

			                )

			        )

			    [ApiResultOfListOfJobStatusContainer_attr] => Array
			        (
			            [xmlns:xsi] => http://www.w3.org/2001/XMLSchema-instance
			            [xmlns:xsd] => http://www.w3.org/2001/XMLSchema
			        )

			)

*/

class WestFax
{
    public $strWestFaxProductId;
    public $strWestFaxUsername;
    public $strWestFaxPassword;

    /*
	1. Constructor
	Arguments
		1. Product id (Provided by WestFax)
		2. Username
		3. Password
    */
    public function  __construct($strProductId, $strUserName, $strPassword)
    {
        $this->strWestFaxProductId = $strProductId;
        $this->strWestFaxUsername = $strUserName;
        $this->strWestFaxPassword = $strPassword;
    }

    /*
	2. sendFax: Call this function to send a fax using WestFax API
	Arguments
		1. Cover From
		2. Document Path
		3. Phone Numbers in array Ex. array('12345678')
		4. Job Name
		5. Billing Code
    */
    public function sendFax($strCoverFrom, $strDocumentPath, $arrPhoneNumbers, $strJobName, $strBillingCode = '')
    {   
        $arrFaxDetails = array();
        $arrFaxDetails['Header'] = $strCoverFrom;
        $arrFaxDetails['ProductId'] = $this->strWestFaxProductId;
        $arrFaxDetails['UserName'] = $this->strWestFaxUsername;
        $arrFaxDetails['Password'] = $this->strWestFaxPassword;
		$arrFaxDetails['BillingCode'] = $strBillingCode;
        $arrFaxDetails['Files1'] = "@" . $strDocumentPath;
        $intCounter = 1;

        foreach($arrPhoneNumbers as $strPhoneNumber)
        {
            $arrFaxDetails['Numbers'. $intCounter] = $strPhoneNumber;
            $intCounter += 1;
        }

        $arrFaxDetails['JobName'] = $strJobName;
		
        $objResponse = $this->curlPost($arrFaxDetails, 'https://api.westfax.com/Polka.Api/REST/SendFax/XML');
			
		return $this->convertXMLtoArray($objResponse, 1,  'tag');
    }

    /*
    3. getFaxStatus: Call this function to get status of sent fax using API
		Arguments
			1. Job Id (Response from sendFax function)
    */
    public function getFaxStatus($strJobID)
    {
		$arrData = http_build_query(
			array
			(
			  'Username' => $this->strWestFaxUsername,
			  'Password' => $this->strWestFaxPassword,
			  'ProductId' => $this->strWestFaxProductId,
			  'Ids1' => $strJobID,
			),
			'', '&'
		);

		$arrOpts = array('http' =>
			array
			(
			  'method' => 'POST',
			  'header' => 'Content-type: application/x-www-form-urlencoded',
			  'content' => $arrData
			)
		);

		$rsContext = stream_context_create($arrOpts);
		$objResponse = file_get_contents('https://api.westfax.com/Polka.Api/REST/GetFaxStatus', false, $rsContext);

		return $this->convertXMLtoArray($objResponse, 1,  'tag');
    }

    function curlPost($arrData, $strHost)
    {
    	$curl	=	curl_init();
		curl_setopt($curl,CURLOPT_URL,$strHost);
		curl_setopt($curl, CURLOPT_POST, 1);		
		curl_setopt($curl, CURLOPT_POSTFIELDS, $arrData);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		
		return curl_exec($curl);
    }


	function convertXMLtoArray($contents, $get_attributes=1, $priority = 'tag') 
	{
		 
		if(!$contents) return array();

		if(!function_exists('xml_parser_create')) {
			//print "'xml_parser_create()' function not found!";
			return array();
		}

		//Get the XML parser of PHP - PHP must have this module for the parser to work
		$parser = xml_parser_create('');
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); 
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xml_values);
		xml_parser_free($parser);

		if(!$xml_values) return;//Hmm...

		//Initializations
		$xml_array = array();
		$parents = array();
		$opened_tags = array();
		$arr = array();

		$current = &$xml_array; //Refference

		//Go through the tags.
		$repeated_tag_index = array();//Multiple tags with same name will be turned into an array
		foreach($xml_values as $data) {
			unset($attributes,$value);//Remove existing values, or there will be trouble

			//This command will extract these variables into the foreach scope
			// tag(string), type(string), level(int), attributes(array).
			extract($data);//We could use the array by itself, but this cooler.

			$result = array();
			$attributes_data = array();
			
			if(isset($value)) {
				if($priority == 'tag') $result = $value;
				else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
			}

			//Set the attributes too.
			if(isset($attributes) and $get_attributes) {
				foreach($attributes as $attr => $val) {
					if($priority == 'tag') $attributes_data[$attr] = $val;
					else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
				}
			}

			//See tag status and do the needed.
			if($type == "open") {//The starting of the tag '<tag>'
				$parent[$level-1] = &$current;
				if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
					$current[$tag] = $result;
					if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
					$repeated_tag_index[$tag.'_'.$level] = 1;

					$current = &$current[$tag];

				} else { //There was another element with the same tag name

					if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
						$current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
						$repeated_tag_index[$tag.'_'.$level]++;
					} else {//This section will make the value an array if multiple tags with the same name appear together
						$current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
						$repeated_tag_index[$tag.'_'.$level] = 2;
						
						if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
							$current[$tag]['0_attr'] = $current[$tag.'_attr'];
							unset($current[$tag.'_attr']);
						}

					}
					$last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
					$current = &$current[$tag][$last_item_index];
				}

			} elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
				//See if the key is already taken.
				if(!isset($current[$tag])) { //New Key
					$current[$tag] = $result;
					$repeated_tag_index[$tag.'_'.$level] = 1;
					if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;

				} else { //If taken, put all things inside a list(array)
					if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

						// ...push the new element into that array.
						$current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
						
						if($priority == 'tag' and $get_attributes and $attributes_data) {
							$current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
						}
						$repeated_tag_index[$tag.'_'.$level]++;

					} else { //If it is not an array...
						$current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
						$repeated_tag_index[$tag.'_'.$level] = 1;
						if($priority == 'tag' and $get_attributes) {
							if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
								
								$current[$tag]['0_attr'] = $current[$tag.'_attr'];
								unset($current[$tag.'_attr']);
							}
							
							if($attributes_data) {
								$current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
							}
						}
						$repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
					}
				}

			} elseif($type == 'close') { //End of tag '</tag>'
				$current = &$parent[$level-1];
			}
		}
		
		return($xml_array);
	}  

}

?>