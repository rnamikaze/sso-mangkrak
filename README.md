![Halaman Awal](https://storage.unusida.id/storage/images/home_crop.jpg)

## About UNUSIDA SSO

UNUSIDA SSO (Single Sign On) is a gateway to every helpful UNUSIDA website, all of which are accessible with a single account.

## Main Framework/Library Used

-   [ReactJS](https://react.dev).
-   [Laravel](https://laravel.com).

## Todo

-   Make sure you transfer your node_modules folder to the server after deploying this repository to shared hosting or a server.
-   Use "composer update" to install the composer dependencies, or move your vendor folder to the server.
-   The .env file that Laravel requires on your server has to be uploaded and configured.
-   Build the project using the "npm run build" command in your local ReactJS project, compress it, upload it to the server, and then extract the build/ folder into the public/ folder.

## Known Issues

-   Layout is still not responsive
-   after uploading excel files, its unexpectedly error. just try to upload the file again
-   uncomment line on these Models files StrangerCounter.php, LoginLoger.php based on environment.
