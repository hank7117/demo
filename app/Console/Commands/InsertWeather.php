<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Weather;
use App\Models\WeatherForecast;
use Illuminate\Console\Command;
use App\Services\CrawlerService;

class InsertWeather extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:weather';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $crawlerService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->crawlerService = app(CrawlerService::class);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $crawler = $this->crawlerService->getRaw('https://www.cwb.gov.tw/V8/C/W/County/MOD/wf7dayNC_NCSEI/ALL_Week.html');
        $weatherForecasts = $this->crawlerService->getWeatherForecasts($crawler);

        foreach ($weatherForecasts as $weatherForecast){
            $country = $weatherForecast["country"];
            $weather = $weatherForecast["weather"];

            if (empty($country) || empty($weather)){
                continue;
            }
            $category = $weatherForecast["category"];

            $country = Country::query()->updateOrCreate([
                "cid" => $country['cid']
            ], [
                "name" => $country['name']
            ]);
            $weather = Weather::query()->updateOrCreate([
                "name" => $weather['name']
                , "category" => $category
            ], [
                "img" => $weather['img']
            ]);
            $temperature = $weatherForecast["temperature"];

            $weatherForecast = Weatherforecast::query()->updateOrCreate([
                "date" => $weatherForecast["date"]
                , "cid" => $country->cid
                , "category" => $category
            ], [
                "wid" => $weather->id
                , "min_temperature" => $temperature["min"]
                , "max_temperature" => $temperature["max"]
            ]);
        }
    }
}
