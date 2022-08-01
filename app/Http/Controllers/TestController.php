<?php

namespace App\Http\Controllers;

use Goutte\Client;

use App\Models\Test;
use App\Models\Scrap;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use Telegram\Bot\Laravel\Facades\Telegram;

class TestController extends Controller
{
    public function index()
    {
        $client = new Client();
        $crawler = $client->request('GET', $this->link);

        if ($crawler->filter('h3')->count() === 0) {
            return;
        };

        $title = $crawler->filter('h1')->text();

        $cost = $crawler->filter('h3')->text();
        $costText = str_replace('$', '', $cost);
        $costInt = (int) str_replace(' ', '', $costText);

        $description = $crawler->filter('.css-g5mtbi-Text')->text();

        $nodeValues = $crawler->filter('p')->each(function (Crawler $node) {
            return $node->text();
        });

        $floorEbosh = 'Поверх:';
        $floor = array_filter($nodeValues, function ($value) use ($floorEbosh) {
            return str_contains($value, $floorEbosh);
        });
        $floor = array_values($floor);
        $floorInt = (int) str_replace('Поверх: ', '', $floor[0]);

        $superficialityEbosh = 'Поверховість:';
        $superficiality = array_filter($nodeValues, function ($value) use ($superficialityEbosh) {
            return str_contains($value, $superficialityEbosh);
        });
        $superficiality = array_values($superficiality);
        $superficialityInt = (int) str_replace('Поверховість: ', '', $superficiality[0]);

        $opalennjaEbosh = 'Опалення:';
        $opalennja = array_filter($nodeValues, function ($value) use ($opalennjaEbosh) {
            return str_contains($value, $opalennjaEbosh);
        });
        $opalennja = array_values($opalennja);
        if (!empty($opalennja)) {
            $opalennjaText = str_replace('Опалення: ', '', $opalennja[0]);
        } else {
            $opalennjaText = '';
        };

        $scrapData = [
            'title' => $title,
            'url' => $this->link,
            'cost' => $costInt,
            'description' => $description,
            'floor' => $floorInt,
            'superficiality' => $superficialityInt,
            'opalennja' => $opalennjaText,
        ];
        Scrap::insert($scrapData);

        Telegram::sendMessage([
            'chat_id' => '642114867',
            'text' => view('zalupjuha', $scrapData)->render(),
            'parse_mode' => 'HTML',
        ]);
    }
}
