<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Client;

class AjaxController extends Controller {

  private $url;
  private $key;
  private $output;
  private $lang;

  public function __construct() {
    $this->url = env('ROOT_API_URL');
    $this->key = env('API_KEY');
    $this->output = env('OUTPUT_API');
    $this->LANG = env('API_LANG');
  }

  public function getToken (){
    $client = new Client();

    $res = $client->request('GET', $this->url.'/api/v1/payexpress?method=getToken&secretkey='.$this->key.'&output='.$this->output);

    return $res->getBody();
  }

  public function getHotelPromo (Request $request) {
    $client = new Client();
    $token = $request->input('token');
    $keyword = $request->input('keyword');
    $page = ($request->input('page')) ? $request->input('page') : 1;
    $adult = ($request->input('adult')) ? $request->input('adult') : 2;
    $offset = ($request->input('offset')) ? $request->input('offset') : 10;
    $startDate = ($request->input('startdate')) ? $request->input('startdate') : date('Y-m-d');
    $night = ($request->input('night')) ? $request->input('night') : 1;
    $room = ($request->input('room')) ? $request->input('room') : 1;
    $endDate = ($request->input('enddate')) ? $request->input('enddate') : date('Y-m-d', strtotime("+1 day"));
    $maxStar = ($request->input('star')) ? $request->input('star') : 5;
    $minStar = ($request->input('star')) ? $request->input('star') : 0;
    $maxPrice = ($request->input('maxprice')) ? $request->input('maxprice') : 10000000;
    $minPrice = ($request->input('minprice')) ? $request->input('minprice') : 0;

    $res = $client->request('GET', $this->url.'/search/hotel?token='.$token.'&output='.$this->output.'&lang='.$this->lang.'&q='.$keyword.'&page='.$page.'&offset='.$offset.'&startdate='.$startDate.'&maxstar='.$maxStar.'&minstar='.$minStar.'&minprice='.$minPrice.'&maxprice='.$maxPrice.'&adult='.$adult.'&room='.$room.'&night='.$night);

    return $res->getBody();
  }

  public function getHotelDetail (Request $request) {
    $client = new Client();
    $token = $request->input('token');
    $uri = str_replace('_', '/', $request->input('uri'));
    $startdate = $request->input('startdate');
    $night = $request->input('night');
    $room = $request->input('room');
    $adult = $request->input('adult');

    $res = $client->request('GET', $this->url."/$uri?token=$token&startdate=$startdate&night=$night&room=$room&adult=$adult&output=".$this->output.'&lang='.$this->lang);

    return $res->getBody();
  }

  public function addHotelOrder (Request $request) {
    $client = new Client();
    $token = $request->input('token');
    $cart = $request->input('currentCart');
    $uri = $request->input('uri');
    $uri = urldecode($uri);

    // DELETE CURRENT CART FIRST
    if($cart) {
      $client->request('GET', $this->url.'/order/delete_order?order_detail_id='.$cart.'&token='.$token.'&output='.$this->output);
    }

    $res = $client->request('GET', $uri.'&token='.$token.'&output='.$this->output);

    return $res->getBody();
  }

  public function getHotelOrder (Request $request) {
    $client = new Client();
    $token = $request->input('token');

    $res = $client->request('GET', $this->url.'/order?token='.$token.'&output='.$this->output);
    $response = json_decode($res->getBody(), true);
    if($res->getStatusCode() == 200) {
      $getPaymentMethod = $client->request('GET', $this->url.'/checkout/checkout_payment?token='.$token.'&output='.$this->output.'&lang='.$this->lang);
      $paymentMethod = json_decode($getPaymentMethod->getBody());
      $response['payment_method'] = isset($paymentMethod->available_payment) ? $paymentMethod->available_payment : [];
    }

    return $response;
  }

  public function checkoutCustomer (Request $request) {
    $client = new Client();
    $token = $request->input('token');
    $salute = $request->input('salute');
    $firstName = $request->input('first_name');
    $lastName = $request->input('last_name');
    $email = $request->input('email');
    $phone = $request->input('phone');
    $order_id = $request->input('order_id');
    $detail_id = $request->input('order_detail_id');
    $uri = $request->input('uri');
    $uri = urldecode($uri);

    $res = $client->request('GET', $this->url.'/checkout/checkout_customer?token='.$token.'&output='.$this->output.'&salutation='.$salute.'&firstName='.$firstName.'&lastName='.$lastName.'&phone='.$phone.'&emailAddress='.$email.'&conSalutation='.$salute.'&conFirstName='.$firstName.'&conLastName='.$lastName.'&conEmailAddress='.$email.'&conPhone='.$phone.'&detailId='.$detail_id.'&country=id&saveContinue=2');
    $res = $client->request('GET', $this->url.'/checkout/checkout_customer?token='.$token.'&output='.$this->output.'&salutation='.$salute.'&firstName='.$firstName.'&lastName='.$lastName.'&phone='.$phone.'&emailAddress='.$email.'&conSalutation='.$salute.'&conFirstName='.$firstName.'&conLastName='.$lastName.'&conEmailAddress='.$email.'&conPhone='.$phone.'&detailId='.$detail_id.'&country=id');

    if(stristr($uri, '?') === FALSE) { // processed by own website ex: bank transfer
      $client->request('GET', $uri.'?token='.$token.'&output='.$this->output.'&btn_booking=1');
    }

    return $res->getBody();
  }

  public function getAirport (Request $request) {
    $client = new Client();
    $token = $request->input('token');

    $res = $client->request('GET', $this->url.'/flight_api/all_airport?token='.$token.'&output='.$this->output.'&lang='.$this->lang);

    return $res->getBody();
  }

  public function getFlight(Request $request) {
    $client = new Client();
    $token = $request->input('token');
    $departureCode = $request->input('dcode');
    $arrivalCode = $request->input('acode');
    $depDate = $request->input('ddate');
    $retDate = ($request->input('rdate')) ? 'ret_date='.$request->input('rdate') : '';
    $adult = $request->input('adult');
    $child = $request->input('child');
    $infant = $request->input('infant');

    $getAirport = $client->request('GET', $this->url.'/flight_api/all_airport?token='.$token.'&output='.$this->output.'&lang='.$this->lang);
    $airport = json_decode($getAirport->getBody(), true);

    $res = $client->request('GET', $this->url."/search/flight?d=$departureCode&a=$arrivalCode&date=$depDate&$retDate&adult=$adult&child=$child&infant=$infant&token=$token&output=".$this->output.'&lang='.$this->lang.'&v=3');

    $body = json_decode($res->getBody(), true);
    $body['airport'] = $airport['all_airport']['airport'];

    return $body;
  }

  public function getFlightData (Request $request) {
    $client = new Client();
    $token = $request->input('token');
    $depFlightId = $request->input('dep_flight_id');
    $retFlightId = ($request->input('ret_flight_id')) ? 'ret_flight_id='.$request->input('ret_flight_id') : '';
    $depDate = $request->input('dep_date');
    $retDate = ($request->input('ret_date')) ? 'ret_date='.$request->input('ret_date') : '';

    $res = $client->request('GET', $this->url.'/flight_api/get_flight_data?token='.$token.'&output='.$this->output.'&flight_id='.$depFlightId.'&'.$retFlightId.'&date='.$depDate.'&'.$retDate.'&lang='.$this->lang);

    return $res->getBody();
  }

  public function addFlightOrder(Request $request) {
    $client = new Client();
    $token = $request->input('token');
    $email = $request->input('email');
    $phone = $request->input('phone');
    $params = urldecode($request->input('params'));
    $flightId = $request->input('flight_id');
    $retFlightId = ($request->input('ret_flight_id') && $request->input('ret_flight_id') != 'undefined') ? 'ret_flight_id='.$request->input('ret_flight_id') : '';
    $flightName = $request->input('flight_name');
    $retFlightName = $request->input('ret_flight_name');
    $adult = $request->input('adult');
    $child = $request->input('child');
    $infant = $request->input('infant');

    $param = json_decode($params, true);
    $strParam = '';

    foreach($param as $key => $val) {
      $strParam .= "$key=$val&";
    }

    // LION CAPTCHA
    if(strtolower($flightName) == 'lion' || strtolower($retFlightName) == 'lion') {
      $lion = $client->request('GET', $this->url."/flight_api/getLionCaptcha?token=$token&output=".$this->output);
      $dataLion = json_decode($lion->getBody(), true);
      $strParam .= "lioncaptcha=".$dataLion['lioncaptcha']."&lionsessionid=".$dataLion['lionsessionid'];
    }

    $res = $client->request('GET', $this->url."/order/add/flight?token=$token&$strParam&output=".$this->output."&flight_id=$flightId&$retFlightId&adult=$adult&child=$child&infant=$infant");

    return $res->getBody();
  }

  public function checkoutFlight (Request $request) {
    $client = new Client();
    $token = $request->input('token');
    $salute = $request->input('salute');
    $firstName = $request->input('first_name');
    $lastName = $request->input('last_name');
    $email = $request->input('email');
    $phone = $request->input('phone');
    $uri = $request->input('uri');
    $uri = urldecode($uri);
    $res = $client->request('GET', $this->url.'/checkout/checkout_customer?token='.$token.'&output='.$this->output.'&salutation='.$salute.'&firstName='.$firstName.'&lastName='.$lastName.'&phone='.$phone.'&emailAddress='.$email.'&conSalutation='.$salute.'&conFirstName='.$firstName.'&conLastName='.$lastName.'&conEmailAddress='.$email.'&conPhone='.$phone.'&country=id&saveContinue=2');

    if(stristr($uri, '?') === FALSE) { // processed by own website ex: bank transfer
      $client->request('GET', $uri.'?token='.$token.'&output='.$this->output.'&btn_booking=1');
    }

    return 1;
  }

}
