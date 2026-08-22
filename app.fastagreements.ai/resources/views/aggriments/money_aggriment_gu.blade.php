<!DOCTYPE html>
<html lang="gu">

<head>
    <meta charset="UTF-8">
    <!-- <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
@if ($show->language_id == 2)
    <style>
        @page {
            size: A4;
            margin: 0;
            padding: 0;
        }

        body {
            padding: {
                $branding.page_margin_top
            }

            cm {
                $branding.page_margin_right
            }

            cm {
                $branding.page_margin_bottom
            }

            cm {
                $branding.page_margin_left
            }

            cm;
            font-size: 11pt;
            font-family: helvetica !important;
            background-image: url('{{ $bg_image }}');
            background-position: center center;
            background-size: cover;
            background-repeat: no-repeat;
            font-size: 10pt;

        }



        @font-face {
            font-family: 'GujaratiFont';
            src: url('{{ public_path(' storage/fonts/NotoSansGujarati-Regular.ttf') }}') format('truetype');
        }

        @font-face {
            /* font-family: 'LMG-Arun';
            src: url('{{ public_path('storage/fonts/LMG-Arun.ttf') }}') format('truetype');
            font-weight: 100;
            font-style: normal; */

            font-family: 'LMG-Arun';
            src: url("{{asset('fonts/LMG-Arun.woff2')}}") format('woff2'),
            url("{{asset('fonts/LMG-Arun.woff')}}") format('woff'),
            url("{{asset('fonts/LMG-Arun.ttf')}}") format('truetype');
            /* font-weight: 100; */
            font-style: normal;
        }

        /* Apply the fonts */
        p {
            font-family: 'GujaratiFont', 'LEGO', sans-serif;
            /* Apply both fonts */
            margin: 0px 25px;
            /* top, right, bottom, left */

            /* font-family: 'Times New Roman', serif; */
            line-height: 0.4;
        }

        body,
        span,
        strong,
        p {
            font-family: 'LMG-Arun';
            font-weight: 100;
            font-style: normal;
            /* font-size: 30px; */
        }

        h1,
        h2,
        h3 {
            text-align: center;
            font-size: 20px;
            /* Reduced font size */
            margin-top: 10px;
            margin-bottom: 5px;
            /* Adjust spacing */
        }

        p {
            font-size: 18.5px;
            margin-bottom: 0.3rem;
            line-height: 16px;
            padding: 0px 10px;
        }

        .p_strong {
            font-size: 19.5px;
            margin-bottom: 0.3rem;
            /* Reduced spacing */
            line-height: 16px;
            /* Reduced line height */
            font-weight: bold;
        }

        .contract-title {
            font-size: 18px;
            padding-bottom: 0;

        }

        ol {
            /*padding-left: 1rem;*/
            /*font-size: 14px;*/
             padding-left: 1rem;
            font-size: 19.5px;
            padding-left: 70px;
        }

        ol li {
            /*margin-bottom: 0.3rem;*/
                        margin-bottom: 1rem;

        }

        .like-h2 {
            font-size: 2em;
            margin: 0.50em 0;
            line-height: 1.1;
            color: inherit;
            text-align: center;
        }

        .like-h1 {
            font-size: 2.5em;
            font-weight: bolder;
            margin: 0.67em 0;
            line-height: 1.2;
            color: 000000;
            text-align: center;
        }

        .like-h6 {
            font-size: 1.125em;
            font-weight: bolder;
            margin: 0.67em 0;
            line-height: 1.2;
            color: 000000;
            text-align: center;
        }

        .lmg-arun {
            /*padding: 10px 50x 25px 50px;*/
            /* font-size: 8px !important; */
            /* top, right, bottom, left */

        }

        .container {
            width: 80%;
            margin: 0 auto;
        }

        .section {
            margin-bottom: 40px;
        }

        .row {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .text {
            flex: 2;
        }

        .shapes {
            display: flex;
            gap: 20px;
            flex: 1;
        }

        .rectangle {
            width: 100px;
            height: 150px;
            border: 1px solid black;
            padding-left: 20px;
        }

        .circle {
            width: 50px;
            height: 50px;
            border: 1px solid black;
            border-radius: 50%;
        }

        .signature {
            margin-top: 20px;
        }

        .signature p {
            margin-bottom: 5px;
        }



        .table-eng {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        .td-eng {
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        .th-eng {
            font-size: 20px;
            text-align: center;
        }

        .header-image img {
            width: 120px;
            height: auto;
        }

        .header-title {
            font-size: 24px;
            text-align: center;
            font-weight: bold;
        }

        .subtitle {
            font-size: 20px;
            text-align: center;
            font-weight: bold;
            margin-top: -10px;
        }

        .content-table td {
            padding: 5px 10px;
            font-size: 16px;
        }

        .bold {
            font-weight: bold;
        }

        .bg-img {
            background-image: url('{{ $bg_image }}');
            background-size: 100%;
            background-repeat: no-repeat;
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .english_data {

            font-family: Arial, sans-serif;
            line-height: 0.4;

        }
    </style>
    @endif
    @if ($show->language_id == 1)
    <style>
        @if ($show->is_active == 0 )

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0px;
            background-size: cover;
            /* background-repeat: no-repeat; */
            font-family: Arial, sans-serif;
            background-size: 100%
        }
        @else
         body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0px;
            background-image: url('{{ $bg_image }}');
            background-size: cover;
            /* background-repeat: no-repeat; */
            font-family: Arial, sans-serif;
            background-size: 100%
        }
        @endif

        h1,
        h2,
        h3 {
            text-align: center;
        }

        .section {
            margin-bottom: 20px;
        }

        .signature {
            margin-top: 30px;
        }

        .signature div {
            margin-bottom: 10px;
        }

        .rectangle {
            width: 100px;
            height: 150px;
            border: 1px solid black;
            padding-left: 20px;
        }

        .circle {
            width: 50px;
            height: 50px;
            border: 1px solid black;
            border-radius: 50%;
        }

        .signature {
            margin-top: 20px;
        }

        .signature p {
            margin-bottom: 5px;

        }

        p {
            /*padding-left: 80px;*/
            /*padding-right: 40px;*/

        }

        ol {
            counter-reset: item;
            list-style: none;
            padding-left: 80px;
            padding-right: 40px;

        }

        ol>li {
            position: relative;
            padding-left: 40px;
            /* Spacing between number and text */
        }

        ol>li::before {
            content: counter(item) ".";
            counter-increment: item;
            position: absolute;
            left: 0;
            font-weight: bold;
            /* Optional: to make the number stand out */
        }
    </style>
    @endif


</head>

@if ($show->language_id == 2)
<!--dd("dd");-->

<body style="
<!--background-image: url('{{ $bg_image }}');--> //for remove background 
            background-size: cover;
            /* background-repeat: no-repeat; */
            font-family: Arial, sans-serif;
            background-size: 100%;
            ">

    <!-- <span>oov EF0F SZFZoov</span> -->
    <div>
        <div class="lmg-arun" >

        {!! $show->agreement_text!!}

        </div>

</body>

@endif

@if ($show->language_id == 1)
<body>
{!! $show->agreement_text!!}

</body>
@endif

</html>