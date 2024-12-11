<?php

// Template Name: Accessing Account

use Infomaker\Everyware\Twig\View;
use Infomaker\Everyware\Base\Models\Page;
//use Infomaker\Everyware\Base\OpenContent\OpenContentProvider;
//use KTDTheme\ViewModels\BasePage;

$currentPage = Page::current();

View::render('@base/page/part/engage.twig');

?>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, shrink-to-fit=no, maximum-scale=5">
        <link rel="stylesheet" href="https://www.khaleejtimes.com/wp-content/themes/ktd-theme/assets/css/fonts.css">

        <style>
            .blink_me {
                animation: blinker 1s linear infinite;
            }

            @keyframes blinker {
                0% {
                    opacity: 0;
                }
                30% {
                    opacity: 0;
                }
                50% {
                    opacity: 1;
                }
                100% {
                    opacity: 1;
                }
            }

            html {
                background: #0179c8;
            }

            body {
                margin: 0;
            }
        </style>
    </head>
    <body>
        <p
            style="font-family: 'proxima_novabold'; color: #000000; background: #ffffff; padding: 10px 0px 10px 20px;"
        >If the page doesn't redirect within 5 seconds, please click on the KT Logo and try again.</p>

        <div style="width:100%; min-height: 52vh; position: relative; top: 50%; transform: translateY(-50%);" class="d-flex justify-content-center acc-blue-bx-nf">

            <div style="width:auto;  text-align: center;" class="align-self-center">
                <a href="/">
                    <img
                        src="https://static.khaleejtimes.com/wp-content/uploads/sites/2/2023/02/16125647/kt-icon-1.png"
                        style="max-width: 200px; text-align: center;"
                    />
                </a>
                <h1 style="color:#FFF;" class="blink_me">Redirecting, please wait…<h1>

            </div>

        </div>
    </body>
</html>