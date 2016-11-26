<?php

class GetRandomFilm
{

    private $API_URL = 'https://api.themoviedb.org/3';

    private $api_key = '8ae519abc739288e881d3389fe49aa0d';

    private $poster_path = 'https://image.tmdb.org/t/p/w600_and_h900_bestv2/';
    private $backdrop_path = 'https://image.tmdb.org/t/p/w600_and_h900_bestv2/';


    private $pages_count = null;

    private $url_params;


    private $validators = [
        'years' => "/[0-9]{4}-[0-9]{4}/"
    ];

    private $params = [];


    function __construct() {

        $this->set_params();

        $this->run_request();

    }



    private function set_params()
    {
        $this->params = [
            'genres' => (isset($_GET['g'])) ? $_GET['g'] : false,
            'without_genres' => (isset($_GET['ng'])) ? $_GET['ng'] : false,
            'year_range' => (isset($_GET['y'])) ? $_GET['y'] : false,
            'popularity' => (isset($_GET['p'])) ? $_GET['p'] : false,
        ];
    }

    private function makeRequest(){

        $u = [];

        if ($this->params['genres']) {
            $u[] = 'with_genres=' . $this->params['genres'];
        }

        if ($this->params['without_genres']) {
            $u[] = 'without_genres' . $this->params['without_genres'];
        }

        if ($this->params['year_range']) {
            $y = $this->params['year_range'];
            if (preg_match($this->validators['years'], $y)) {
                $yy = explode('-', $y);
                if ($yy[0] > 1850 && $yy[0] <= $yy[1]) {
                    $u[] = 'primary_release_date.gte=' . $yy[0];
                    $u[] = 'primary_release_date.lte=' . $yy[1];
                }
            }


        }


        $randoms = [];

        if($this->pages_count != null) {
            if($this->params['popularity']){
                $pop = $this->params['popularity'];
                $count = $this->pages_count;
                if(intval($pop)>0 && intval($pop)<=10){

                    $pop = 10-$pop+1;

                    $range = $count/10;

                    $start = round($pop*$range - $range);
                    $stop  = round($pop*$range);

                    if($start < 1) $start = 1;
                    if($stop < 1) $stop = 1;

                }

            }
            var_dump($count);
            var_dump($start);
            var_dump($stop);
            $randoms[] = 'page=' . rand($start, $stop);
        }

        $out = array_merge($u, $randoms);


        return implode('&', $out);

    }

    function run_request(){

        $this->url_params = $this->makeRequest();


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->API_URL . "/discover/movie?api_key=". $this->api_key."&language=en-US&include_adult=false&include_video=false&" . $this->url_params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "{}",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $result = json_decode($response);
            if ($this->pages_count == null) {

                $this->pages_count = $result->total_pages;
                echo 'tp' . $result->total_pages;
                $this->run_request($this->url_params);

            } else {

                $random_film_id = rand(0,count($result->results)-1);

                $res = $result->results[$random_film_id];

                echo '<img src="'.$this->poster_path.$res->poster_path.'">';
                echo '<img src="'.$this->poster_path.$res->backdrop_path.'">';

                var_dump($res);
            }
        }

    }



}

$film = new GetRandomFilm;



/*
28:"Action"
12:"Adventure"
16:"Animation"
35:"Comedy"
80:"Crime"
99:"Documentary"
18:"Drama"
10751:"Family"
14:"Fantasy"
36:"History"
27:"Horror"
10402:"Music"
9648:"Mystery"
10749:"Romance"
878:"Science Fiction"
10770:"TV Movie"
53:"Thriller"
10752:"War"
37:"Western"
*/


