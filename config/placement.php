<?php

return [
    // Filesystem disk for logos, JDs, resumes, offer letters. Set PLACEMENT_DISK=s3 in production.
    'disk' => env('PLACEMENT_DISK', 'public'),

    'upload' => [
        'logo_mimes' => 'jpg,jpeg,png,webp',
        'logo_max_kb' => 2048,
        'doc_mimes' => 'pdf,doc,docx',
        'doc_max_kb' => 5120,
    ],

    // Same Twilio content template / messaging service the ERP's WhatsappController uses for text messages.
    'whatsapp' => [
        'content_sid'           => env('PLACEMENT_WHATSAPP_CONTENT_SID', 'HX3a292a1ee72924adb532e807a2ed9b36'),
        'messaging_service_sid' => env('PLACEMENT_WHATSAPP_MESSAGING_SERVICE_SID', 'MGdec43b1bbd9428a72fa0c7a633905319'),
    ],

    // Keys masked before values are written to the audit log.
    'sensitive_keys' => ['gst_no', 'pan_no', 'password', 'otp', 'mobile', 'email'],
];
