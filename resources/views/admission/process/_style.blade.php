<style>
    /* Admission module look & feel (scoped with the adm- prefix; Bootstrap 3 + Font Awesome as used by the ERP) */
    .adm-intro { color:#607d8b; font-size:14px; margin:0 0 18px; max-width:900px; }
    .adm-scope { color:#90a4ae; font-size:12px; margin:-8px 0 16px; }
    .adm-h { font-size:15px; font-weight:600; color:#37474f; margin:6px 0 12px; text-transform:uppercase; letter-spacing:.4px; }
    .adm-h small { text-transform:none; font-weight:400; color:#90a4ae; letter-spacing:0; }

    /* dashboard tiles */
    .adm-tile { display:block; position:relative; background:#fff; border-radius:8px; padding:16px 16px 14px; margin-bottom:16px; min-height:112px;
        box-shadow:0 1px 4px rgba(0,0,0,.09); border-left:5px solid var(--c, #1e88e5); color:#37474f; text-decoration:none !important; transition:all .15s ease; }
    .adm-tile:hover { box-shadow:0 5px 14px rgba(0,0,0,.14); transform:translateY(-2px); color:#263238; }
    .adm-tile .num { font-size:32px; font-weight:600; line-height:1; color:var(--c, #1e88e5); }
    .adm-tile .ttl { font-size:14px; font-weight:600; margin-top:6px; }
    .adm-tile .sub { font-size:12px; color:#78909c; margin-top:3px; line-height:1.35; }
    .adm-tile .ico { position:absolute; right:14px; top:14px; font-size:28px; color:var(--c, #1e88e5); opacity:.28; }
    .adm-tile.clear .num { color:#9e9e9e; }
    .adm-tile.clear { --c:#b0bec5 !important; }
    .adm-clear-tag { font-size:11px; color:#43a047; font-weight:600; }

    /* quick actions */
    .adm-actions .btn { margin:0 8px 8px 0; }

    /* progress steps on the application page */
    .adm-steps { display:flex; list-style:none; margin:0 0 6px; padding:6px 0 0; overflow-x:auto; }
    .adm-steps li { flex:1 0 82px; text-align:center; position:relative; font-size:12px; color:#78909c; }
    .adm-steps li a { color:inherit; text-decoration:none; display:block; }
    .adm-steps .dot { position:relative; z-index:1; width:32px; height:32px; line-height:32px; border-radius:50%; background:#cfd8dc; color:#fff; font-weight:600; margin:0 auto 6px; }
    .adm-steps li:not(:last-child):after { content:''; position:absolute; top:15px; left:calc(50% + 18px); right:calc(-50% + 18px); height:3px; background:#cfd8dc; }
    .adm-steps li.done .dot { background:#43a047; }
    .adm-steps li.done:not(:last-child):after { background:#43a047; }
    .adm-steps li.current .dot { background:#1e88e5; box-shadow:0 0 0 5px rgba(30,136,229,.22); }
    .adm-steps li.current { color:#1565c0; font-weight:600; }
    .adm-steps li.done { color:#2e7d32; }

    /* "what to do next" banner */
    .adm-next { background:#e3f2fd; border-left:5px solid #1e88e5; border-radius:6px; padding:14px 18px; margin:0 0 18px; }
    .adm-next.done { background:#e8f5e9; border-color:#43a047; }
    .adm-next.stop { background:#fff3e0; border-color:#fb8c00; }
    .adm-next .k { font-size:11px; text-transform:uppercase; letter-spacing:.6px; color:#78909c; }
    .adm-next .t { font-size:18px; font-weight:600; color:#263238; margin:2px 0; }
    .adm-next .d { color:#546e7a; margin:0 0 8px; }

    /* sections */
    .adm-sec { scroll-margin-top:80px; }
    .adm-sec h4 { margin:0 0 4px; font-size:17px; }
    .adm-sec h4 .n { display:inline-block; min-width:26px; height:26px; line-height:26px; text-align:center; border-radius:50%; background:#1e88e5; color:#fff; font-size:13px; margin-right:8px; }
    .adm-help { color:#78909c; font-size:13px; margin:0 0 14px; }
    .adm-sec form.form-inline .form-control { margin-bottom:6px; }

    /* tables */
    .adm-table > thead > tr > th { background:#f5f7f9; color:#546e7a; font-size:12px; text-transform:uppercase; letter-spacing:.3px; border-bottom:1px solid #e0e6ea; }
    .adm-table > tbody > tr > td { vertical-align:middle; }
    .adm-name { font-weight:600; color:#263238; }
    .adm-sub { color:#90a4ae; font-size:12px; }
    .adm-empty { text-align:center; padding:34px 10px; color:#90a4ae; }
    .adm-empty .big { font-size:34px; display:block; margin-bottom:6px; opacity:.6; }
    .adm-empty b { display:block; color:#607d8b; font-size:15px; margin-bottom:4px; }
    .label { font-size:12px; font-weight:600; padding:.35em .7em; }

    /* keep title-bar buttons clear of the ERP "Show Sidebar" tab on the right edge */
    .bg-title .text-right { padding-right:44px; }

    /* collapsible help */
    details.adm-how { background:#fafbfc; border:1px solid #e3e8ec; border-radius:6px; padding:10px 14px; margin-bottom:18px; }
    details.adm-how summary { cursor:pointer; font-weight:600; color:#1565c0; }
    details.adm-how ol { margin:10px 0 0 18px; padding:0; color:#546e7a; }
    details.adm-how li { margin-bottom:4px; }
    @media (max-width:768px) { .adm-tile { min-height:0; } .adm-next .t { font-size:16px; } }
</style>
