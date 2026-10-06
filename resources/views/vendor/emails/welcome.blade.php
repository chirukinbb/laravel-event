<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        body {
            background-color: #f8fafc;
            color: #718096;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            width: 100% !important;
        }

        .wrapper {
            background-color: #f8fafc;
            padding: 30px 0;
            width: 100%;
        }

        .content {
            margin: 0 auto;
            max-width: 570px;
            width: 100%;
        }

        .header {
            padding: 25px 0;
            text-align: center;
        }

        .body-card {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        h1 {
            color: #2d3748;
            font-size: 18px;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 16px;
        }

        p {
            color: #718096;
            font-size: 16px;
            line-height: 1.5em;
            margin-top: 0;
            margin-bottom: 16px;
        }

        .credentials {
            background-color: #f8fafc;
            border-radius: 4px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .credentials p {
            margin: 4px 0;
            color: #2d3748;
        }

        .btn-container {
            text-align: center;
            margin: 24px 0;
        }

        .button {
            background-color: #18181b;
            border-radius: 6px;
            color: #ffffff !important;
            display: inline-block;
            font-size: 16px;
            font-weight: bold;
            padding: 12px 24px;
            text-decoration: none;
        }

        .salutation {
            margin-top: 24px;
            border-top: 1px solid #e2e8f0;
            padding-top: 16px;
            color: #718096;
        }
    </style>
</head>
<body>
<table class="wrapper" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table class="content" cellpadding="0" cellspacing="0" role="presentation">
                {{-- Шапка с вашим логотипом --}}
                <tr>
                    <td class="header">
                        <a href="{{ config('app.url') }}" style="display: inline-block;">
                            <img src="{{ asset('images/icon.png') }}" alt="{{ config('app.name') }}"
                                 style="max-height: 50px; width: auto;">
                        </a>
                    </td>
                </tr>

                {{-- Основная карточка --}}
                <tr>
                    <td class="body-card">
                        <h1>Hello {{ $name }}!</h1>
                        <p>Thank you for registering on our platform. Your account has been created successfully.</p>

                        <p>Here are your login credentials:</p>
                        <div class="credentials">
                            <p><strong>Email:</strong> {{ $email }}</p>
                            <p><strong>Password:</strong> {{ $password }}</p>
                        </div>

                        <p>Please keep this password secure. You can change it after logging in.</p>

                        <div class="btn-container">
                            <a href="{{ $actionUrl }}" class="button">Login Now</a>
                        </div>

                        <p style="font-size: 14px; color: #a0aec0;">If you did not register, no further action is
                            required.</p>

                        <div class="salutation">
                            Best regards,<br>
                            <strong>{{ config('app.name') }}</strong>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>