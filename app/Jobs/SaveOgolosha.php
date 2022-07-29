<?php

namespace App\Jobs;

use Goutte\Client;
use App\Models\Test;
use App\Models\Scrap;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Symfony\Component\DomCrawler\Crawler;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SaveOgolosha implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // protected $sohranenie;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->sohranenie = $saving;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $firstLink = Test::first();

        if (empty($firstLink)) {
            return;
        }

        $client = new Client();
        $crawler = $client->request('GET', $firstLink->link);

        if ($crawler->filter('h3')->count() === 0) {
            $firstLink->delete();
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
            'url' => $firstLink->link,
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
            // 'parse_mode' => view('zalupjuha', $scrapData)->render(),
        ]);

        // return view('zalupjuha', $scrapData);

        $firstLink->delete();
    }
}
