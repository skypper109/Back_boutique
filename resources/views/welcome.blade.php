<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Admin - Accueil</title>
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(110deg, #e0e7ff 0%, #f1f5f9 100%);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .container {
            background: #fff;
            padding: 2.8rem 2.2rem 2rem 2.2rem;
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(60, 72, 98, 0.13);
            min-width: 340px;
            max-width: 420px;
            width: 100%;
            text-align: center;
        }
        .container h1 {
            color: #4f46e5;
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }
        .slogan {
            color: #64748b;
            font-size: 1.08rem;
            margin-bottom: 1.6rem;
        }
        .cta-btn {
            background: linear-gradient(90deg, #6366f1 0%, #818cf8 100%);
            color: #fff;
            padding: 13px 0;
            border: none;
            border-radius: 9px;
            font-size: 1.14rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            width: 100%;
            margin-bottom: 1.2rem;
            cursor: pointer;
            box-shadow: 0 3px 14px rgba(99,102,241,0.11);
            transition: background 0.15s, box-shadow 0.15s;
            text-decoration: none;
            display: inline-block;
        }
        .cta-btn:hover {
            background: linear-gradient(90deg, #4338ca 0%, #6366f1 100%);
            box-shadow: 0 6px 24px rgba(99,102,241,0.17);
        }
        .footer {
            color: #94a3b8;
            font-size: 0.98rem;
            margin-top: 1.5rem;
        }
        @media (max-width: 500px) {
            .container {
                min-width: 94vw;
                padding: 1.2rem 0.6rem 1rem 0.6rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bienvenue sur l’espace Admin</h1>
        <div class="slogan">Merci de vous authentifier pour continuer.<br>
            Gérez vos accès de façon sécurisée et professionnelle.</div>
        <a class="cta-btn" href="{{ route('admin.loginPage') }}">Accéder à l’authentification</a>
        @yield('section')
        <div class="footer">
            &copy; {{ date('Y') }} ADMservice – Espace d’administration
        </div>
    </div>
</body>
</html>
