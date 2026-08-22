<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{
            margin:0;
            padding:0;
            background:#f4f7fb;
            font-family:Arial, sans-serif;
            display:flex;
            justify-content:center;
            align-items:center;
            min-height:100vh;
        }

        .status-card{
            background:#ffffff;
            width:90%;
            max-width:450px;
            border-radius:16px;
            padding:40px 25px;
            text-align:center;
            box-shadow:0 5px 20px rgba(0,0,0,0.08);
        }

        .success-icon{
            width:90px;
            height:90px;
            background:#22c55e;
            color:#fff;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:40px;
            margin:0 auto 20px;
        }

        h1{
            font-size:28px;
            font-weight:700;
            color:#111827;
            margin-bottom:10px;
        }

        p{
            font-size:16px;
            color:#6b7280;
            margin-bottom:0;
        }

        .btn-home{
            margin-top:25px;
            background:#111827;
            color:#fff;
            border:none;
            padding:12px 28px;
            border-radius:8px;
            font-weight:600;
            transition:0.3s;
        }

        .btn-home:hover{
            background:#000;
            color:#fff;
        }

        @media(max-width:576px){

            .status-card{
                padding:30px 20px;
            }

            h1{
                font-size:22px;
            }

            p{
                font-size:14px;
            }

            .success-icon{
                width:75px;
                height:75px;
                font-size:32px;
            }
        }
    </style>
</head>

<body>

    <div class="status-card">

        <div class="success-icon">
            ✓
        </div>

        <h4>Your Application Work Fine</4>

        <!--<p>-->
        <!--    Everything is working properly and successfully completed.-->
        <!--</p>-->

        <!--<button class="btn btn-home">-->
        <!--    Go Back-->
        <!--</button>-->

    </div>

</body>
</html>