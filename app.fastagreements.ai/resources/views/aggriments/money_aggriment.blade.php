<!DOCTYPE html>
<html lang="gu">

<head>
    <meta charset="UTF-8">
    <!-- <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

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
            background-image: url('{$bg_image}');
            background-position: center center;
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
            padding-left: 1rem;
            font-size: 14px;
        }

        ol li {
            margin-bottom: 0.3rem;
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
            padding: 100px 50x 25px 50px;
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
            background-image: url('/assets/img/img.jpeg');
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
</head>

<body style="background-image: url('{{ $bg_image }}');
            background-size: cover;
            /* background-repeat: no-repeat; */
            font-family: Arial, sans-serif;
            background-size: 100%;
            ">

    <!-- <span>oov EF0F SZFZoov</span> -->
    <div>
        <div class="lmg-arun">

            <h2 class="english_data">Rent Agreement</h2>
            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ;\JT Z_Z(_ GF EFNZJFG ;]N 5}GDG[4 TFP!(DL4 JFZ o A]WJFZ4 DFC[o ;%8[dAZ4 ;G[vZ_Z$ GF V\U|[HL lNG[ PPPPPPPPP</p>
            <p class="p_strong"><strong>VF EF0F SZFZ ,BFJL ,[GFZ DFl,S T[ 5C[,F 51FGFov</strong></p>
            <p class="english_data">{{$persone_1_details->name}}</p>
            <p>pPVFPJPZ(4 W\WMo 3ZSFD4</p>
            <p>ZC[P <span class="english_data">{{$persone_1_details->address}}</span>4 </p>
            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; vqq H[VMG[ CJ[ 5KL VF SZFZDF\ 5C[,F 51FGF VUZ DSFG DFL,S TZLS[ ;\AMWJFDF\ VFJ[, K[P H[VMGF ;\5}6" VY"DF\ 5C[,F 51FGF 5MT[ TYF T[VMGF J\XvJF,L4 JFZ;M V[;F.GM4 V[ShlSI]8;"&nbsp; .tIFNLGM ;DFJ[X Y. HFI K[P qqv</p>
            <p><strong>VF EF0F SZFZ ,BL VF5GFZ EF0]T T[ ALHF 51FGF ov</strong></p>
            <p class="english_data">{{$persone_2_details->name}}</p>
            <p>pPVFPJP$*4 W\WM o DH]ZL4</p>
            <p>ZC[P <span class="english_data">{{$persone_2_details->address}}</span>4 </p>
            <p>vqq H[VMG[ CJ[ 5KL VF SZFZDF\&nbsp; ALHF 51FGF VUZ EF0]VFT TZLS[ ;\AMWJFDF\ VFJ[, K[4 H[VMGF ;\5}6" VY"DF\ ALHF 51FGF 5MT[ TYF T[VMGF J\XvJF,L4 JFZ;M4 V[;F.GM4 V[ShLSI]8;" .tIFNLGM ;DFJ[X Y. HFI K[P qqv</p>
            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; HT VFHZMH CDM A\G[ 51FSFZM VF EF0F SZFZGF ,[B YSL A\WF.V[ KLV[ S[ PPPPPPP</p>
            <p class="like-h2"><strong>voo EF0[ VF5[, lD,STGL lJUT oov</strong></p>
            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 0L:8=LS84 ;]ZT4 ;Av0L:8=LS84 ;]ZT ;L8LGF DMH[ UFD o 0]\EF,GF Z[P;PG\P 5&amp;4 a,MS G\P *&amp;4 8LP5LP:SLD G\P &amp;$4 V[OP5LPG\P &amp;4 ;L8L ;J[" G\P !!&amp;5 JF/L HDLGDF\ VFIMHLT <strong>UM5F,GUZ U'5 CFp;L\UDF\ VFJ[, VFJF; s%,M8f G\P !Z&amp; GL U|Fpg0 O,MZ</strong> JF/L lD,ST H[GM ;]PDP5FP NOTZ[ 8[GFP G\P _Z#V[v!5v#)Z!v_v__! TYF 8MZ[g8 5FJZ ;lJ"; G\P 5__Z!(!)5 K[P H[ lD,ST TDM 5C[,F 51FGFGL DFL,SL VG[ SAHF EMUJ8FGL RF,L VFJ[, K[P ;NZC]\ lD,ST CDM ALHF 51FGFV[ DFl;S <strong>~FPZ___qv V\S[ ~l5IF A[ CHFZ 5]ZFGF</strong> EF0[YL TFP_!q_)qZ_Z$ GF ZMHYL !! DF; DF8[ ZC[9F6GF C[T] DF8[ ZFB[, K[P H[ EF0F SZFZ VF56[ VFHZMH GLR[ D]HAGL XZTMG[ VFlWG SZL&nbsp; VF5LV[ KLV[P</p>
            <!-- <p>&nbsp;</p> -->
            <p class="like-h2"><strong>vo XZTMov</strong></p>
            <table>
                <tbody>
                    <tr>
                        <td>
                            <p>s!f</p>
                        </td>
                        <td>
                            <p>;NZC]\&nbsp; lD,STGL 0L5MhL8 5[8[ <strong>~FP<span class="english_data">{{$deposite}}</span>qv V\S[ ~l5IF 5F\R CHFZ 5]ZF TYF T[G]\ DFl;S EF0] ~F<span class="english_data">{{$rent}}</span>qv V\S[ ~l5IF A[ CHFZ 5]ZF</strong> GSSL SZ[, K[P</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>sZf</p>
                        </td>
                        <td>
                            <p>;NZC]\ lD,ST CDM ALHF 51FGFV[ TDM 5C[,F 51FGF 5F;[YL TF<span class="english_data">{{$start_date}}</span> GF ZMHYL <span class="english_data">{{$duration}}</span> DF;GL D]N'T[ J5ZFX DF8[ ZFB[, K[P NZ DF;GL ! YL !_ TFZLB ;]WLDF\ EF0] R]SJL VF5JFG]\ ZC[X[P</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>&nbsp;</p>
                        </td>
                        <td>
                            <p>&nbsp;</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>s#f</p>
                        </td>
                        <td>
                            <p>VF EF0F SZFZGM TF<span class="english_data">{{$start_date}}</span> YL TFP<span class="english_data">{{$end_date}}</span>;]WL VD, SZJFGM ZC[X[P TYF VF EF0F SZFZGL D]N'T 5}6" YIF AFN ;NZ EF0F SZFZ OZL ZLgI] SZJFGM ZC[X[P</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>s$f</p>
                        </td>
                        <td>
                            <p>;NZC]\ EF0[ lD,STG]\ DFl;S EF0] NZ DF;MDF; R-FR-L ZLT[ lGIlDT VF5JFG]\ K[P T[DF\&nbsp; SM. DF; R]SL HJFGM GYLP VF AFAT[ ElJQIDF\ SM. TZvTSZFZ SZJFGL ZC[X[ GCLP</p>
                        </td>
                    </tr>
                    <tr style="page-break-after: always;
                               break-after: always;
                               margin: top 20px;">
                        <td>
                            <p>s5f</p>
                        </td>
                        <td>
                            <p>;NZC]\ EF0[ VF5[,L lD,STGM EF0FGM DF; NZ V\U|[HL DF;GL !,L TFZLB[ X~ YTM&nbsp; U6LG[ T[ H DlCGFGL VFBZL TFZLB[ 5]ZM YTM U6JFGM K[P</p>
                        </td>
                    </tr>
                    <br><br><br><br><br><br>
                    <tr style="padding-top:50px;">
                        <td>
                            <p>s&amp;f</p>
                        </td>
                        <td>
                            <p>;NZC]\ lD,ST CDM ALHF 51FGFV[ ZC[9F6GF C[T] DF8[ EF0[YL ZFB[, K[P T[ l;JFI ALHM SM. p5IMU SZJFGM TYLP T[DH SM.G[ EF0[4 5[8F EF0[ S[ ,LJ V[g0 ,FI;g;YL IFG[ U]0,LJYL VF5JFGL GYLP T[DH VDFZF EF0]T TZLS[GF CSSM SM.G[ 8=g;OZ S[ V[;F.G SZJF vSZFJJFGF GYLP</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>s*f</p>
                        </td>
                        <td>
                            <p>;NZC]\ lD,STDF\ TZT ;/UL p9[ T[JF 5|NFYM" T[DH ;NZSFZ[ DGF. OZDFJ[, 5|NFYM"GM ;\U|C SZJFGM GYL KTF\ HM ElJQIDF\ SM. VS:DFT S[ V[S;L0g8 YFI TM T[GL TDFD HJFANFZL CDM ALHF 51FGFGL ZC[X[PPPP ;CLPPP</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>s(f</p>
                        </td>
                        <td>
                            <p>&nbsp;;NZC]\ ZC[9F6GF C[T] DF8[ ZFB[,L lD,STDF\ VDMV[ SM. EF\UOM0 S[ G]SXFG SZJFG]\ GYL VG[ lD,ST H[JL ;FZL l:YlTDF\ JF5ZJF VF5[, K[ T[JL H ;FZL l:YlTDF\ 5ZT SZJFGL ZC[X[P T[DH VFH]AFH]JF/FVMG[ gI];g; YFI T[JF SM. S'tI SZFJFv SZFJJFGF GYLP</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>s)f</p>
                        </td>
                        <td>
                            <p>;NZC]\ ZC[9F6GM C[T] DF8[ ZFB[, lD,STGF VF56[ A\G[ 51FSFZM VZ;v5Z; BF,L SZJFv SZFJJF DF\UTF CM.V[ TM V[S DF; VUFpYL HF6 SZJFGL ZC[X[P</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>s!_f</p>
                        </td>
                        <td>
                            <p>&nbsp;p5ZGL SM.56 XZTMG[ E\U YFI TM TDM 5C[,F 51FGF lJGF GM8L;[&nbsp; VG[ lJGF TSZFZ ;NZC]\ J5ZFX DF8[ ZFB[,L lD,STGM SAHM 5ZT ,[JF CSNFZ KMP ;CL PPPPP</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>s!!f</p>
                        </td>
                        <td>
                            <p>&nbsp;;NZC]\ lD,STG]\ ,F.8 CDM ALHF 51FGF IFG[ EF0]VFT[ EZFJFGF ZC[X[ TYF J[ZFAL,DF\ TYF SM.56 8[1F VFJ[ TM T[ VDM 5|YD 51FGF IFG[&nbsp; DFl,S[ EZJFGF ZC[X[P</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>s!Zf</p>
                        </td>
                        <td>
                            <p>;NZC]\ EF0F SZFZ VgJI[ EF0]VFTG[ AMdA[ Z[g8 V[S8 !)$* C[9/ SM.56 5|SFZG]\ Z1F6 D/X[ GCLPV[8,[ S[4 AMdA[ Z[g8 V[S8 !)$*GL S,Dvs!vV[f GF GJF ;]WFZ[,F SFINF D]HA DHS]Z EF0F SZFZG[ Z[g8 V[S8 ,FU 50JF 5F+ GYLP</p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; V[6L lJUTM VF EF0F SZFZ CDM ALHF 51FGFVM TDM 5C[,FGF 51FGFG[ :J:YlRT[4 ALGO[S4 TGDG ;FJW ZFBL SM.56 HFTGF NAF6 JUZ4 ZFHLB]XLYL GLR[ H6FJ[, ;F1FLVMGL ~A~ CFHZLDF\ ;\5}6"56[ JF\RL4 J\RFJL ;DHLG[ ;CL VF5[, K[P H[ VF56G[ TYF VF56F J\XJF,L&nbsp; JFZ;MG[ SA],4 D\H]Z VG[ A\WGSTF" K[P</p>
            <p>&nbsp;</p>
            
            <p style="page-break-after: always;
                break-after: always;">V+[PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP DT] T+[PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP XFB</p>
                    <br><br><br><br><br><br>

            <table style="page-break-after: always;
                break-after: always;">

                <tr>
                    <td>
                        <div class="section">
                            <p>VF EF0F SZFZ ,BFJL ,[GFZ DFl,S T[ 5C[,F 51FGF ov</p>
                            <!-- <p>V<5[XS]DFZ ZF3JEF. ,F0]DMZ4</p> -->
                            <span class="english_data" style="padding-left: 35px;">{{$persone_1_details->name}}</span>
                        </div>
                        <br><br><br>
                        <div class="new-page">
                            <p>PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP</p>
                        </div>
                    </td>
                    <th>
                        <div class="shapesx">
                            <div class="rectanglex">
                                <img src="data:image/jpeg;base64,{{ $person_1_image_url }}" alt="Person 2 Image" style="width:150px; height:auto;" />

                            </div>
                        </div>
                    </th>
                    <th>
                        <!-- <div class="shapes">
                            <div class="circle"></div>
                        </div> -->
                    </th>
                </tr>
                <tr>
                    <td>
                        <div class="section">
                            <p>VF EF0F SZFZ, BL VF5GFZ DFl, S T[ ALHF 51FGF ov</p>
                            <!-- <p>HFNJ VXMSEF. 5ZQFMTDEF.4</p> -->
                            <span class="english_data" style="padding-left: 35px;">{{$persone_2_details->name}}</span>

                        </div>
                        <br><br><br>

                        <div class="new-page">
                            <p>PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP</p>
                        </div>
                    </td>
                    <th>
                        <div class="shapesx">
                            <div class="rectanglex">
                                <img src="data:image/jpeg;base64,{{ $person_2_image_url }}" alt="Person 2 Image" style="width:150px; height:auto;" />

                                <!-- <img src="{{$persone_2_details->person_image_url}}" alt="image"> -->
                            </div>
                        </div>
                    </th>
                    <th>
                        <!-- <div class="shapes">
                            <div class="circle"></div>
                        </div> -->
                    </th>

                </tr>
                <tr>
                    <td>

                        <br><br><br>

                        <div class="new-page">
                            <P>;F1FLov </P>
                            <p>!PPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP</p>
                            <br><br>
                            <!-- <p>ZPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP</p> -->
                        </div>
                    </td>
                    <th>
                        <!-- <div class="shapes">
                        <div class="rectangle"></div>
                    </div> -->
                    </th>
                    <th>
                        <!-- <div class="shapes">
                        <div class="circle"></div>
                    </div> -->
                    </th>

                </tr>
            </table>




</body>

</html>