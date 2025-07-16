<?php

namespace App\Http\Controllers\USL;

use Inertia\Inertia;
use App\Models\UslShortLink;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

function getFaviconUrl($url)
{
    // Fetch HTML content of the webpage
    $html = file_get_contents($url);

    // Check if HTML content is fetched successfully
    if ($html !== false) {
        // Use a regular expression to find the favicon link
        preg_match('/<link.+?rel=["\']shortcut icon["\'].+?href=["\'](.+?)["\'].*?>/i', $html, $matches);

        // Check if a favicon link is found
        if (isset($matches[1])) {
            $faviconUrl = $matches[1];

            // Determine the base URL of the website
            $base_url = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);

            // Concatenate the base URL with the relative favicon URL
            $completeFaviconUrl = $base_url . $faviconUrl;

            echo 'Complete Favicon URL: ' . $completeFaviconUrl;
        } else {
            echo 'No favicon found on the webpage';
        }
    } else {
        echo 'Failed to fetch HTML content';
    }
}

function trimStringTo20Chars($inputString)
{
    if (strlen($inputString) <= 20) {
        return $inputString;
    }
    // Use substr to get the first 10 characters
    $trimmedString = substr($inputString, 0, 20);

    return $trimmedString;
}

class ShortUrlControllers extends Controller
{
    //
    public function mainShort($url = "empty")
    {
        if ($url === "empty") {
            return redirect('/sku/admin/login');
        } else {
            // $destination = ShortUrl::where('url', $url)->first();
            $destination = UslShortLink::whereRaw("BINARY url = ?", [$url])->first();

            if ($destination === null) {
                return Inertia::render('USL/LinkNotFound', ['url' => $url]);
                // return "Short Link [" . $url . "] Tidak Ditemukan !";
            } else {
                $editShortUrl = UslShortLink::find(intval($destination->id));

                $count = intval($editShortUrl->visitor_count);
                $count += 1;
                $editShortUrl->visitor_count = $count;

                $editShortUrl->save();

                $shortUrl = UslShortLink::find(intval($destination->id));

                $url = $shortUrl->destination_url;
                $title = trimStringTo20Chars($shortUrl->title) . " - UNUSIDA Link Shortener";
                $description = 'UNUSIDA Link Shortener, create your own costumized short link';
                $image = getFaviconUrl("https://www.unusida.ac.id");


                // return $image;
                // return redirect($destination->destination_url);
                return View::make('redirect')->with(compact('url', 'title', 'description', 'image'));
            }
        }
    }
}
