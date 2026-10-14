<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $title }}</title>
    <style>
        /* Unique class names + inline styles on the toolbar, so browser extensions / generic stylesheets cannot restyle it */
        html, body { margin:0; padding:0; background:#e9edf0; font-family:Arial, sans-serif; }
        .admrc-paper { background:#fff; max-width:820px; margin:18px auto; padding:18px; box-shadow:0 1px 6px rgba(0,0,0,.18); box-sizing:border-box; }
        @media print { .admrc-toolbar { display:none !important; } html, body { background:#fff; } .admrc-paper { box-shadow:none; margin:0; max-width:none; } }
    </style>
</head>
<body>
    <div class="admrc-toolbar" style="display:block;width:100%;box-sizing:border-box;background:#fff;padding:10px 16px;box-shadow:0 1px 4px rgba(0,0,0,.15);overflow:hidden">
        <button type="button" id="admrc-print" style="float:right;background:#1e88e5;color:#fff;border:0;border-radius:4px;padding:8px 18px;cursor:pointer;font-size:14px;margin-left:12px">Print receipt</button>
        <div style="font-size:15px;font-weight:bold;color:#37474f;line-height:34px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis">{{ $title }}</div>
    </div>
    <div class="admrc-paper">{!! $html !!}</div>
    <script nonce="{{ $nonce }}">document.getElementById('admrc-print').addEventListener('click', function () { window.print(); });</script>
</body>
</html>
