<?php
namespace Wgoogle;

/**
 * Wgoogle
 *
 * @package     Fuel
 * @subpackage  Wgoogle
 * @author       Darrye Steplight
 */
class Wgoogle
{
        const API_KEY = 'AIzaSyDRG9yl5mFblh1m-49gv2atuUH00neIp7o';
        const GOOGLE_URL = 'https://maps.googleapis.com/maps/api/place/search/json?location=';
        const GOOGLE_PLACES_URL = "https://maps.googleapis.com/maps/api/place/details/json?reference=";
        const PLACES_ATTRIBUTES = "&sensor=true&key=";
        const ATTRIBUTES = '&radius=100&types=food&name=harbour&sensor=false&key=';

        protected $_longitude = NULL;
        protected $_latitude = NULL;

	/**
	 * Store Coordinates
         * @access public
         * @return void
	 */
	public function __construct($array = null) 
        {
            if (is_array($array))
            {
                $this->_set_cordinates($array); 
            }
            else
            {
               throw new Exception("Coordinates are not a valid array."); 
            }
        
        }

	/**
	 * Attach Coordinates to object
         * @access protected
         * @param Array $array 
	 */
        protected function _set_cordinates($array)
        {
            $this->_longitude = $array['longitude'];
            $this->_latitude = $array['latitude'];

        return true;
        }

        /**
         * Execute. Do all of the heavy lifting with retrieving data.
         * @access public
         * @return boolean
         */
        public function run()
        {
            $url = self::GOOGLE_URL.$this->_longitude.','.$this->_latitude.self::ATTRIBUTES.self::API_KEY;
            $places_url = self::GOOGLE_PLACES_URL.self::PLACES_ATTRIBUTES.self::API_KEY;

            $results =  $this->curl_me($url); 

            $locations_array = json_decode($results)->results;

                if( is_array($locations_array))
                {
                    //store entire location listing information in this array
                    $location_que = array();
                    
                    //loop through each return location's data
                    foreach($locations_array as $location)
                    {
                        //grab the referernce id, use it to curl for specifc location details
                        $places_url = self::GOOGLE_PLACES_URL.$location->reference.self::PLACES_ATTRIBUTES.self::API_KEY;
                        $location_details =  $this->curl_me($places_url); 

                        //attach location details to location object
                        $l_data = json_decode($location_details);
                        $location->custom_details = $l_data;
                        
                        //attach entire location specific information to location que
                        $location_que[] = $location; 
                    }
                        return json_encode($location_que);
                }
                return false;
        }

        /**
         * Curl a url and return a json string
         * @param String $url
         * @access public
         * @return data
         */
        public function curl_me($url)
        {
            $ch = curl_init();

            // set params
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // grab URL and pass it to the browser
            $data =  curl_exec($ch);
            curl_close($ch);

            return $data;
        }

}

