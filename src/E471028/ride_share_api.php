<?php
/*
* @name: ride_share_api.php
* @author : Gunjan Joshi (E471028)
* @purpose: The Ride Share Solution API - Provides means to book a Cab.
* @code : 
* @created:  1 Feb 2018
*/

//This class is part of CodeIgniter Framework. It extends the Rest Controller which in turn extends the CodeIgniter Controller

header('Access-Control-Allow-Origin: *');

require_once APPPATH.'/libraries/REST_Controller.php';

class Ride_share_api
{
	private $arrRequestData = array();
	private $arrDriverDetails = array(
							1=> array(
								'driver_id' => '1',
								'driver_name' => 'Srinivas',
								'driver_phone' => '1111111111',
								'vehicle_type' => 'Indica',
								'vehicle_color' => 'Red',
								'vehicle_number' => 'TS09 1111',
								'latitude' => '17.451',
								'longitude' => '78.3748113',
								'driver_available' => '1',
								'seats_available' => '3'
								),
							2=> array(
								'driver_id' => '2',
								'driver_name' => 'Venkat',
								'driver_phone' => '2222222222',
								'vehicle_type' => 'Swift',
								'vehicle_color' => 'Black',
								'vehicle_number' => 'TS09 2222',
								'latitude' => '17.4347211',
								'longitude' => '78.3845559',
								'driver_available' => '1',
								'seats_available' => '1'
								),
							3=> array(
								'driver_id' => '3',
								'driver_name' => 'Shyam',
								'driver_phone' => '3333333333',
								'vehicle_type' => 'Baleno',
								'vehicle_color' => 'Silver',
								'vehicle_number' => 'TS09 3333',
								'latitude' => '17.3615636',
								'longitude' => '78.4724758',
								'driver_available' => '1',
								'seats_available' => '2'
								),
							4=> array(
								'driver_id' => '4',
								'driver_name' => 'Raju',
								'driver_phone' => '4444444444',
								'vehicle_type' => 'Alto',
								'vehicle_color' => 'Blue',
								'vehicle_number' => 'TS09 4444',
								'latitude' => '17.4391858',
								'longitude' => '78.4358806',
								'driver_available' => '0',
								'seats_available' => '3'
								),
							5=> array(
								'driver_id' => '5',
								'driver_name' => 'Lokesh',
								'driver_phone' => '5555555555',
								'vehicle_type' => 'Innova',
								'vehicle_color' => 'Ash',
								'vehicle_number' => 'TS09 55555',
								'latitude' => '17.4262805',
								'longitude' => '78.0194307',
								'driver_available' => '1',
								'seats_available' => '2'
							),
							6=> array(
								'driver_id' => '6',
								'driver_name' => 'Ravi',
								'driver_phone' => '6666666666',
								'vehicle_type' => 'Polo',
								'vehicle_color' => 'Red',
								'vehicle_number' => 'TS09 6666',
								'latitude' => '17.2447128',
								'longitude' => '78.3914275',
								'driver_available' => '0',
								'seats_available' => '3'
							),
							);
	
	/**
	*@author: Gunjan Joshi (E471028)
    *@Purpose: Constructor
	*@param: Initializes the class variables
    *@return: None
    **/		
	/*public function __construct($arrData)
	{
		$this->arrRequestData = $arrData;	
	}**/
	
	/**
    *@Purpose: Clears the output buffer
    *@param: None
    *@return: None
    */
	private function clearOutputBuffer()
	{
		ob_clean();
		ob_start();
	}
	
	/**
	*@author: Gunjan Joshi (E471028)
    *@Purpose: To Book a Cab
	*@param: None
    *@return: None
    **/						
	public function bookCab()
	{
		// Gets nearest drivers, confirms acceptance from driver and send the driver details to the requested client
		//Rejects requests coming in from locations outside Hyderabad, Bangalore, Madurai
		
		//Get the request type and serve accordingly..
		$this->arrRequestData=$_REQUEST;
		
		//Get the nearest (available) drivers..
		$arrNearestDrivers = $this->getNearestDrivers($this->arrRequestData['latitude'], $this->arrRequestData['longitude'], '0');
		$arrKeys = array_keys($arrNearestDrivers);
						
		//Get the confirmed driver key/id
		//$strDriverId = $this->confirmDriverAcceptance($arrNearestDrivers);

		//Update the number of seats.. Reduce count by 1..
		$this->arrDriverDetails[$arrKeys[0]]['seats_available'] = $this->arrDriverDetails[$arrKeys[0]]['seats_available'] -1 ;
		file_put_contents('driver_db.txt', json_encode($this->arrDriverDetails));
		
		//Update the Trip in the DB..
		$arrBookings = json_decode(file_get_contents('booking_db.txt', FILE_USE_INCLUDE_PATH), TRUE);
		$tripId = count($arrBookings) + 1;
		$arrTripDetails = array(
						'trip_id' => $tripId,
						'driver_id'=>$arrNearestDrivers[$arrKeys[0]]['driver_id'], 
						'customer_id' => $_REQUEST['customer_id'],
						
						);
		array_push($arrBookings, $arrTripDetails);
		file_put_contents('booking_db.txt', json_encode($arrBookings));
		
		$this->clearOutputBuffer();
		$arrBookingDetails = $arrNearestDrivers[$arrKeys[0]];
		$arrBookingDetails['trip_id'] = $tripId;
		echo json_encode($arrBookingDetails);
	}
	
	/**
	*@author: Gunjan Joshi (E471028)
    *@Purpose: To get the Nearest Drivers - This function will fetch the positions of all near by drivers using GeoLocation methods
	*@param: Latitude, Longitude of the requested customer and flag
    *@return: Nearest Available Driver Details
    **/
	public function getNearestDrivers($latitude, $longitude, $flag=1)
	{
		//This function will fetch the positions of all near by drivers using GeoLocation methods
		//Postgress SQL will give better performance here
		
		//Get the request type and serve accordingly..
		$strLatitude = trim($_REQUEST['latitude']);
		$strLongitude = trim($_REQUEST['longitude']);
		
		if($strLatitude == '' || $strLongitude == '')
		{
			//17.4187033, 78.3419884
			$strLatitude = 17.4187033;
			$strLongitude = 78.3419884;
		}
		
		//Calculate the nearest driver using manhattan distance formula. Assumption is to have 10 km as limit.
		$arrNearestDrivers = array();
		
		$this->arrDriversDetails = json_decode(file_get_contents('driver_db.txt', FILE_USE_INCLUDE_PATH), TRUE);
		foreach($this->arrDriverDetails as $key=>$val)
		{
			//$strDistance = $this->calculateHaversineDistance($this->arrRequestData['latitude'], $this->arrRequestData['longitude'], $val['latitude'], $val['longitude']);
			if($val['driver_available'] == 0 || $val['seats_available'] == 0)
			{
				continue;	
			}
			$strDistance = $this->calculateHaversineDistance(17.4187033, 78.3419884, $val['latitude'], $val['longitude']);
			//if($strDistance < 10)
			{
				$tmpArr = $this->arrDriverDetails[$key];
				$tmpArr['distance'] = $strDistance;
				$arrNearestDrivers[$key] = $tmpArr;
			}
		}
		
		//Sort these drivers..
		uasort($arrNearestDrivers, function($a, $b) {
			return $a['distance'] - $b['distance'];
		});
		
		if($flag == 1)
		{
			$this->clearOutputBuffer();
			echo json_encode($arrNearestDrivers);
		}
		else
		{
			return $arrNearestDrivers;
		}
	}
	
	/**
	*@author: Gunjan Joshi (E471028)
    *@Purpose: Send Request to the Driver
	*@param: Source and Destination of the requested customer
    *@return: Nearest Avialable Driver Details
    **/
	private function sendRequestToDrivers($sourceLatitude, $sourceLongitude, $destLatitude, $destLongitude)
	{
		
	}
	
	/**
	*@author: Gunjan Joshi (E471028)
    *@Purpose: Confirm Driver Acceptance
	*@param: Request Id, Trip Id
    *@return: Trip Id, Driver Id
    **/
	private function confirmDriverAcceptance($arrDrivers)
	{
		//Generate a random number
		//return mt_rand(1, 6);
		$rand_key = array_rand ($arrDrivers); 
		return array_rand ($arrDrivers);
	}
	
	/*
	*@author: Gunjan Joshi (E471028)
	*@Purpose: convert degrees To Radian
	*@param: degrees
	*@return: radians
	*/
	public function convertToRad($deg)
	{
		$dms= explode(".", $deg);
		//$ss=(substr($dms[1], 0, 2).".".substr($dms[1], 2, 2)) / 60;
		$temp_dms= str_pad($dms[1],4,"0",STR_PAD_RIGHT);
        $ss=(substr($temp_dms, 0, 2).".".substr($temp_dms, 2, 2)) / 60;
		if($deg < 0)
		{
			$dd= $dms[0] - $ss;
		}
		elseif ($deg > 0)
		{
			$dd= $dms[0] + $ss;
		}

		$rad= deg2rad($dd);
		return $rad;
	}// end convertToRad
	
	/*
	*@author: Gunjan Joshi (E471028)
	*@Purpose: Calculate Distance
	*@param: departure and destination latitude and longitude
	*@return: distance
	*/
	private function calculateHaversineDistance($sourceLatitude, $sourceLongitude, $destLatitude, $destLongitude)
	{
		$deptlat=round($sourceLatitude,3);
		$deptlon=round($sourceLongitude,3);
		$destlat=round($destLatitude,3);
		$destlon=round($destLongitude,3);

		// Convert the  Latitude and Longitude to radians
		$lat1= $this->convertToRad($deptlat);
		$lon1= $this->convertToRad(($deptlon) *(-1));
		$lat2= $this->convertToRad($destlat);
		$lon2= $this->convertToRad(($destlon) *(-1));

		//Computations. Following equations are used to determine the distance (Dist) and the forward azimuth (Brg12) of the geodesic at P1.
		//Constant values used in the calculations:
		$bo= 3432.457854;
		$f= 0.003366962;
		$onemf= 0.996633037;

		//calcualtion for intial bearing
		$lon_diff= $lon2 - $lon1;

		$lon_sin= sin($lon_diff);
		$lon_cos= cos($lon_diff);
		$lat1_tan= atan($onemf * tan($lat1));
		$lat1_sin= sin($lat1_tan);
		$lat1_cos= cos($lat1_tan);
		$lat2_tan= atan($onemf * tan($lat2));
		$lat2_sin= sin($lat2_tan);
		$lat2_cos= cos($lat2_tan);
		$a= $lat1_sin * $lat2_sin;
		$b= $lat1_cos * $lat2_cos;
		$m= sqrt(pow(($lon_sin * $lat2_cos), 2) + pow((($lat2_sin * $lat1_cos) -($lat1_sin * $lat2_cos * $lon_cos)), 2));
		$g= $a + $b * $lon_cos;
		$p= atan2($m, $g);
		if($m == 0) {
			$c= 0;
			$q= $lon_diff;
		} else {
			$c= $b * $lon_sin / $m;
			$q= $lon_diff + $c * $p * $f;
		}
		//calculate of the distance
		$dst= $bo *($p + $f *($p + $m * $a -0.5 *(1 - pow($c, 2)) *($p + $m * $g)));	
		
		return $dst;
	}
	
	/*
	*@author: Gunjan Joshi (E471028)
	*@Purpose: Get Trip for Current Driver
	*@param: Driver Id
	*@return: trip details
	*/
	function getDriverTrips()
	{
		$arrTrips = file_get_contents('booking_db.txt', FILE_USE_INCLUDE_PATH);
		echo $arrTrips;
		/*$arrTrips = json_decode($arrTrips, TRUE);
		$driver_id = trim($_REQUEST['driver_id']);
		print_r($arrTrips);
		foreach($arrTrips as $key=>$val)
		{
			if(trim($val['driver_id']) ==$driver_id)
			{
				print_r( $val);
				exit;
			}
		}*/
		
		//echo json_encode($arrTrips[$driver_id]);
	}
}

?>