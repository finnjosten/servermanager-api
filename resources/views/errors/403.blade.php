<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>403 | Forbidden</title>

        <style>
            * { margin: 0; padding: 0; font-family: Arial, sans-serif; }

            body{ background-color: #1a202c; display: flex; justify-content: center; align-items: center; flex-direction: column; gap: 12px; widows: 100%; height: 100vh; }

            .text { display: flex; }
            .text .error-code { padding-right: 20px; border-right: 1px solid #a0aec0; }
            .text .error-text { padding-left: 20px; border-left: 1px solid #a0aec0; }

            h1, p { color: #a0aec0; }
        </style>
    </head>

    <body>
        <div class="text">
            <h1 class="error-code">403</h1>
            <h1 class="error-text">Forbidden</h1>
        </div>
        <div class="text">
            <p class="error-message">The client has been forbidden to view this resource</p>
        </div>
    </body>
</html>
