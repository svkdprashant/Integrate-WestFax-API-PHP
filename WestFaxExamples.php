<?php

/*
    Created By : Prashant Jethwa
    Date       : 29th June, 2017
    Email      : codebucket.co@gmail.com
    Website    : http://code-bucket.co/
	Purpose	   : This file includes all working example of sending/receiving online fax via WestFax API.

*/

include_once('WestFax.class.php');

/*
	Arguments
	First: Product Id (Provided by WestFax)
	Second: Username
	Third: Password
*/
$objWestFax = new WestFax('8c89x2c7-2a7a-47d3-9336-e4072d484ed6', '8UaMPvbR', 'jYU68jxG');

/*
~~~~~~~~~~~~~ Send Fax START ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	Call sendFax to send a fax
	Arguments
		1. Cover From
		2. Document Path
		3. Phone Numbers in array Ex. array('12345678')
		4. Job Name
		5. Billing Code
*/
$strCoverFrom = 'Test Cover For Fax';
$strDocument = 'LearnwithGoogle.pdf';
$strPhoneNumber = array('5105868243');
$strJobName = 'First Test Job';

$arrSendFaxResponse = $objWestFax->sendFax($strCoverFrom, $strDocument, $strPhoneNumber, $strJobName);
if($arrSendFaxResponse['ApiResultOfString']['Success'] == 'true')
{
	$strJobId = $arrSendFaxResponse['ApiResultOfString']['Result'];
	echo $strJobId;
}
else
{
	echo $arrSendFaxResponse['ApiResultOfString']['ErrorString'];
}

/* ~~~~~~~~~~~~~ Send Fax END ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */


/* ~~~~~~~~~~~~~ Get Fax Status START ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

$strJobId = 'bcfd4b37-ffaf-4ffa-bab9-ddfb776a0620';
$arrFaxStatus = $objWestFax->getFaxStatus($strJobId);

if($arrFaxStatus['ApiResultOfListOfJobStatusContainer']['Success'] == 'true')
{
	$arrFaxPhoneStatus = $arrFaxStatus['ApiResultOfListOfJobStatusContainer']['Result']['JobStatusContainer']['Calls']['CallContainer'];

	// For single Fax Number
	if(array_key_exists('CallResult',$arrFaxPhoneStatus))
	{
		$strPhoneNo = $arrFaxPhoneStatus['PhoneNumber'];
		$strFaxStatus = $arrFaxPhoneStatus['CallResult'];
		echo $strPhoneNo.' - '.$strFaxStatus;
	}
	// For multiple fax number
	else
	{
		for($intFax = 0; $intFax < count($arrFaxPhoneStatus); $intFax++)
		{
			$strPhoneNo = $arrFaxPhoneStatus[$intFax]['PhoneNumber'];
			$strFaxStatus = $arrFaxPhoneStatus[$intFax]['CallResult'];
			echo $strPhoneNo.' - '.$strFaxStatus.'<br/>';
		}
	}
}
else
{
	echo $arrFaxStatus['ApiResultOfListOfJobStatusContainer']['ErrorString'];
}
/* ~~~~~~~~~~~~~ Get Fax Status END ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

?>