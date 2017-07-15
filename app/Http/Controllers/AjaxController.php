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

  public function __construct() {
    $this->url = env('ROOT_API_URL');
    $this->key = env('API_KEY');
    $this->output = env('OUTPUT_API');
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
    $offset = ($request->input('offset')) ? $request->input('offset') : 10;

    $res = $client->request('GET', $this->url.'/search/hotel?token='.$token.'&output='.$this->output.'&q='.$keyword.'&page='.$page.'&offset='.$offset);

    return $res->getBody();
  }
}
