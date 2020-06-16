<?php
namespace App\Services;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class CrawlerService
{
    /** @var Client  */
    private $client;

    public function __construct()
    {
        $this->client = app(Client::class);
    }

    /**
     * @param string $path
     * @return Crawler
     */
    public function getRaw(string $path): Crawler
    {
        $crawler = $this->client->request('GET', $path);

        return $crawler;
    }

    /**
     * @param Crawler $crawler
     * @return array
     * 解析頁面上的天氣資料並傳回
     */
    public function getWeatherForecasts(Crawler $crawler)
    {
        $dateMapping = $this->getDateMapping($crawler);

        $weatherForecasts = [];

        //tbody: 每個縣市整週的天氣資料
        foreach($crawler->filter('tbody') as $countryWeekWeatherCategoryNode){
            $countryWeekWeatherCategoryCrawler = new Crawler($countryWeekWeatherCategoryNode);

            //縣市資料
            $country = [];
            foreach ($countryWeekWeatherCategoryCrawler->filter('th') as $countryNode){
                $countryCrawler = new Crawler($countryNode);
                $country = $this->getCountryForWeatherForecasts($countryCrawler);
                break;
            }

            foreach ($countryWeekWeatherCategoryCrawler->filter("tr") as $countryWeekWeatherNode){
                $countryWeekWeatherCrawler = new Crawler($countryWeekWeatherNode);

                //早or晚
                $category =  $this->getCategoryForWeatherForecasts($countryWeekWeatherCrawler);

                //基本單位->每天的最低/最高溫資料，再將先前的早/晚及縣市資料合併
                foreach ($countryWeekWeatherCrawler->filter('td') as $weatherNode){
                    $weatherCrawler = new Crawler($weatherNode);
                    $day = $this->getDayForWeatherForecasts($weatherCrawler);
                    $date = $dateMapping[$day];
                    $weather =  $this->getWeatherForWeatherForecasts($weatherCrawler);
                    $temperature =  $this->getTemperatureForWeatherForecasts($weatherCrawler);

                    $weatherForecasts[] = [
                        "category" => $category
                        , "country" => $country
                        , "date" => $date
                        , "weather" => $weather
                        , "temperature" => $temperature
                    ];
                }
            }
        }

        return $weatherForecasts;
    }

    /**
     * @param Crawler $node
     * @return |null
     * 取得day1, day2...
     */
    private function getDayForWeatherForecasts(Crawler $node)
    {
        $raw = $node->attr("headers");
        $raws = explode(" ", $raw);

        if(count($raws) != 2){
            return null;
        }

        return $raws[1];
    }

    /**
     * @param Crawler $node
     * @return array
     * 組出 [day1 => '2020-05-30', day2 => '2020-05-31'] 的對應
     */
    private function getDateMapping(Crawler $node)
    {
        $dateMappings = $node->filter('thead th')->each(
            function($thNode){

                //取得 day1, day2...
                $id = $thNode->attr('id');
                if($id == "County"){
                    return;
                }

                //取得每個日期的值(2020-05-31)
                $dateMapping = $thNode->filter('.heading_3')->each(
                    function($heading) use($id){
                        $dates = explode("<br>",$heading->html());
                        $date = "";
                        if(count($dates)> 1){
                            $year = date('Y');
                            $date = str_replace("/", "-", $dates[0]);

                            return [$id => "$year-$date"];
                        }
                    }
                );
                return $dateMapping[0];
            }
        );

        //優化結構
        $tmp = [];
        foreach ($dateMappings as $dateMapping){
            if($dateMapping !== null){
                foreach ($dateMapping as $key => $value){
                    $tmp[$key] = $value;
                }
            }
        }
        $dateMapping = $tmp;

        return $dateMapping;
    }

    /**
     * @param Crawler $node
     * @return array
     * 取得Country資料
     */
    private function getCountryForWeatherForecasts(Crawler $node)
    {
        $cid = $node->attr("id");
        $name = $node->filter(".heading_3")->text();

        $country = [
            "cid" => $cid
            , "name" => $name
        ];

        return $country;
    }

    /**
     * @param Crawler $node
     * @return array
     * 取得天氣資料(圖片路徑，天氣名稱)
     */
    private function getWeatherForWeatherForecasts(Crawler $node)
    {
        $imgDom = $node->filter("img");
        $img = $imgDom->attr('src');
        $name = $imgDom->attr('alt');

        $weather = [
            "img" => $img
            , "name" => $name
        ];
        return $weather;
    }

    /**
     * @param Crawler $node
     * @return int
     * 取得分類 (1:day 2:night)
     */
    private function getCategoryForWeatherForecasts(Crawler $node)
    {
        $categoryClass = $node->attr("class");

        $category = 0;
        if ($categoryClass == "day"){
            $category = 1;
        }
        elseif ($categoryClass == "night"){
            $category = 2;
        }
        return $category;
    }

    /**
     * @param Crawler $node
     * @return array
     * 取得最低/最高溫
     */
    private function getTemperatureForWeatherForecasts(Crawler $node)
    {
        $temperatureInfo = $node->filter(".tem-C")->text();

        list($min, $max) = explode(" - ", $temperatureInfo);
        $temperature = [
            "min" => $min
            , "max" => $max
        ];

        return $temperature;
    }
}
