<?php

namespace App\Console\Commands;

use Goutte\Client;
use App\Models\Test;
use App\Models\Scrap;
use Illuminate\Console\Command;

class ScrapData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scraping apartments';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = "https://www.olx.ua/d/uk/nedvizhimost/kvartiry/kremenchug/?currency=USD&search%5Border%5D=created_at:desc&page=1";
        $client = new Client();
        $crawler = $client->request('GET', $url);
        $links_count = (int) trim($crawler->filter('.pagination-list .pagination-item:nth-last-child(2)')->last()->text());

        $all_links = [];

        for ($i = 1; $i <= $links_count; $i++) {
            $url = "https://www.olx.ua/d/uk/nedvizhimost/kvartiry/kremenchug/?currency=USD&search%5Border%5D=created_at:desc&page=" . $i;
            if ($links_count > 0) {
                $links = $crawler->filter('a')->links();
                foreach ($links as $link) {
                    $all_links[] = $link->getURI();
                }
            } else {
                echo "No Links Found";
            }
            // die;
        }

        $podStroka = 'https://www.olx.ua/d/uk/obyavlenie';

        $all_links = array_filter($all_links, function ($link) use ($podStroka) {
            return str_contains($link, $podStroka);
        });

        $all_links = array_values($all_links);

        $all_links = array_unique($all_links, SORT_REGULAR);

        // ['', '', '']

        // [['link' => ''], ['link' => ''], ['link' => '']]

        $test = array_filter($all_links, function ($link) {
            return empty(Scrap::where('url', '=', $link)->first());
        });

        // $test = array_map(function ($link) {
        //     return ['link' => $link];
        // }, $test);

        foreach ($test as $link) {
            // return $link;
        }
        // Test::insert($link);

        // echo "All Avialble Links From this page $url Page<pre>";
        // print_r($test);
        // echo "</pre>";
    }
}
