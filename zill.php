<?php
// Copyright: Copyright(c) 2008 - 2009 The UltimateIDX - All Rights Reserved
// Publisher: The UltimateIDX - Real Estate WordPress Solutions
// Plugin URL: http://www.ultimateidx.com/wp-zillow/
// Version: 0.9.6 for WordPress 2.7+
// Support: http://www.ultimateidx.com/
// Design Features: Jared Ritchey
// Developed by: Damian Danielczyk for UltimateIDX.com


class Zillow{
public function __construct($address){
// Add your Zillow API Key Below;
// Modify Below This Line
$zillow_api_key = "X1-ZWz1ct2ec632ff_8clop";
$this->zillow_search_url = "http://www.zillow.com/webservice/GetSearchResults.htm?zws-id=".$zillow_api_key;
$this->zillow_chart_url = "http://www.zillow.com/webservice/GetChart.htm?zws-id=".$zillow_api_key;
$this->zillow_region_chart_url = "http://www.zillow.com/webservice/GetRegionChart.htm?zws-id=".$zillow_api_key;
$this->zillow_demographic_data_url = "http://www.zillow.com/webservice/GetDemographics.htm?zws-id=".$zillow_api_key;
// width : 200-600, height: 100-300, unit-type: percent or dollar, chartDuration: 1year, 5years or 10years //
$this->zillow_charts_settings = array('width' => 400, 'height' => 200, 'unit-type' => 'percent', 'chartDuration' => '1year');
$this->address = $address;
$this->zpid = $this->zillowGetPropertyID();
}
// Modify Above This Line
//

//regionID
public function zillowGetRegionId()
{
	$url = $this->zillow_demographic_data_url."&state=".$this->address['state']."&city=".$this->address['city'];
	$result = $this->makeCURLrequest($url);
	$xml = new SimpleXMLElement($result);
	
	return $xml->response->region->id;
}


// Demographic Data Layout Here
public function zillowGetDemographicData()
{
	$url = $this->zillow_demographic_data_url."&state=".$this->address['state']."&city=".$this->address['city'];
	$result = $this->makeCURLrequest($url);
	$xml = new SimpleXMLElement($result);
	$data ='';
	foreach($xml->response->charts->chart as $chart)
	{
	$data .= "<div class='wpzillow-chart-wrapper'>";
	$data .= "<h4 class='wpzillow-chart-title'>".$chart->name."</h4>";
	$data .= "<img class='wpzillow-chart-image' src='".$chart->url."'>";
	$data .= "</div>";
	}
	return $data;
}

public function zillowGetRegionChart()
{
	$url = $this->zillow_region_chart_url.'&zip='.$this->address['postcode'].'&unit-type='.$this->zillow_charts_settings['unit-type'].'&chartDuration='.$this->zillow_charts_settings['chartDuration'].'&width='.$this->zillow_charts_settings['width'].'&height='.$this->zillow_charts_settings['height'];
	$result = $this->makeCURLrequest($url);
	$xml = new SimpleXMLElement($result);
	return $xml->response->url;
}
	
public function zillowGetChart()
{

	$url = $this->zillow_chart_url.'&zpid='.$this->zpid.'&unit-type='.$this->zillow_charts_settings['unit-type'].'&chartDuration='.$this->zillow_charts_settings['chartDuration'].'&width='.$this->zillow_charts_settings['width'].'&height='.$this->zillow_charts_settings['height'];
	$result = $this->makeCURLrequest($url);
	$xml = new SimpleXMLElement($result);
	return $xml->response->url;
}	
	
public function zillowGetPropertyID()
{
	$url = $this->zillow_search_url.'&address='.urlencode($this->address['street']).'&citystatezip='.urlencode($this->address['city'].', '.$this->address['state'].' '.$this->address['postcode']);
	$result = $this->makeCURLrequest($url);
	$xml = new SimpleXMLElement($result);
	return $xml->response->results->result[0]->zpid;
}	
	
private function makeCURLrequest($url){
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch); 
	curl_close($ch);
	return $output;
}
}
?>