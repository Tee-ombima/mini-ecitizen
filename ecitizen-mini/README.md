Demo logins for all(password: `password`):
- admin@ecitizen.test (admin)
- licensing@ecitizen.test / lands@ecitizen.test / dci@ecitizen.test / registrar@ecitizen.test (officers, one per department)
- Register a new citizen at /register. OTP codes are sent to email
- Payment is simulated (button on a "Pending Payment" application).

Notes
- Uploads are stored on the private `local` disk and streamed through an authorised route.
- Payment is simulated (button on a "Pending Payment" application). Swap `CitizenController@pay` for M-Pesa/card callback.
- Certificates use a client-side QR (CDN) + browser Print -> Save as PDF. For server PDFs: composer require barryvdh/laravel-dompdf.
- The default tests/Feature/ExampleTest expects 200 on "/", which now redirects; delete or update it.
