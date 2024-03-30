<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature</title>
</head>
<body>

<!-- Mail message -->
<table style="border-collapse: collapse; font-family: 'Times New Roman', Times, serif; width: 100%; font-size: 15px;">
    <tr><td style="padding-top: 15px;"><b>Dear Concern,</b></td></tr>

    <tr><td style="padding-top: 15px; padding-bottom: 150px;">Please find the attached file for BTrac IOS IN OUT Call Summary of <b>{{ \Carbon\Carbon::yesterday()->format('d M Y') }}.</b></td></tr>

    <tr>
        <td style="background:#e7f3fe; border: 1px solid #5cacee; color: #237FA8;font-size: 14px; padding: 8px;">
            <b style="color: #237FA8;">ⓘ</b> This is an auto-generated email. Please do not reply directly to this email. If you have any queries or require further assistance, feel free to reach out to our dedicated billing team at <a href="mailto:billing.team@btraccl.com" style="color:#088632;"><b>billing.team@btraccl.com</b></a>. Or
            <p style="font-family:'Agency FB', 'Open Sans'; color:#90A3AA; font-size: 13px;"><u>Contact developer:</u><br>
                <em>Mohammad Shah Alam | Assistant Manager | Billing | <a href="mailto:info@btraccl.com" style="display:inline-block; color:#90A3AA; text-decoration: none;">shah.alam@btraccl.com</a> | +8801709632295</em><br>
                <em>Bangla Trac Communications Ltd.</em><br>
                <em>Plot#68, Block H, Road 11, Banani, Dhaka 1213, Bangladesh | +8802-883 8001-4, 984 1117-8 (PABX NO.5812)</em><br>
                <em><a href="http://www.btraccl.com/" style="display: inline-block; color: #90A3AA">www.btraccl.com</a></em>
            </p>
        </td>
    </tr>
    <tr><td style="height: 30px;"></td></tr>
</table>

<!-- Signature -->
{{--<table style="border-collapse: collapse; font-family:'Agency FB', 'Open Sans'; font-size: 16px; color: #212121;">--}}
{{--    <!-- User basic info -->--}}
{{--    <tr><td style="font-weight: 400; font-size: 18px; padding-bottom: 10px;">Regards.</td></tr>--}}
{{--    <tr><td style="font-weight: 700; font-size: 20px; color: #222;">Mohammad Shah Alam</td></tr>--}}
{{--    <tr><td>Assistant Manager</td></tr>--}}
{{--    <tr><td>Bangla Trac Communications Ltd.</td></tr>--}}
{{--    <tr><td><span>-------------------------------------------------------</span></td></tr>--}}

{{--    <!-- User basic contact details -->--}}
{{--    <tr>--}}
{{--        <td>--}}
{{--            <table style="font-family:'Agency FB', 'Open Sans';">--}}
{{--                <tr>--}}
{{--                    <td width="16" height="15"><img src="{{ $message->embed(public_path().'/signature_img/iphone.png') }}" alt="Mobile:"></td>--}}
{{--                    <td width="95" style="font-size:13px; color:#212121;">+880 1709632295</td>--}}

{{--                    <td width="20" style="font-size:12px; color:#212121;">|</td>--}}
{{--                    <td width="16" height="15"><img src="{{ $message->embed(public_path().'/signature_img/envelope.png') }}" alt="Email:"></td>--}}
{{--                    <td width="145" style="font-size:13px; color:#212121;"><a href="mailto:info@btraccl.com" style="color:#212121;">shah.alam@btraccl.com</a></td>--}}
{{--                </tr>--}}
{{--                <tr>--}}
{{--                    <td width="16" height="15"><img src="{{ $message->embed(public_path().'/signature_img/old_phone.png') }}" alt="Office:"></td>--}}
{{--                    <td colspan="5" style="font-size:13px; color:#212121;">+8802-883 8001-4, 984 1117-8 (PABX NO.5812)</td>--}}
{{--                </tr>--}}
{{--                <tr>--}}
{{--                    <td width="16" height="15"><img src="{{ $message->embed(public_path().'/signature_img/home.png') }}" alt="Address:"></td>--}}
{{--                    <td colspan="5" style="font-size:13px; color:#212121;">Plot#68, Block H, Road 11, Banani, Dhaka 1213, Bangladesh</td>--}}
{{--                </tr>--}}
{{--                <tr>--}}
{{--                    <td width="16" height="15"><img src="{{ $message->embed(public_path().'/signature_img/world.png') }}" alt="Website:"></td>--}}
{{--                    <td style="font-size:13px;"><a href="http://www.btraccl.com/" style="color:#0054a6;">www.btraccl.com</a></td>--}}
{{--                </tr>--}}
{{--            </table>--}}
{{--        </td>--}}
{{--    </tr>--}}
{{--</table>--}}


</body>
</html>
