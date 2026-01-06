<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enregistrement d'un Administrateur</title>
    <style>
        body {
            background: linear-gradient(120deg, #e0e7ff 0%, #f1f5f9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 2.5rem 2rem;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(60, 72, 98, 0.13);
            min-width: 330px;
            max-width: 380px;
            width: 100%;
        }
        .back-btn {
            display: inline-block;
            margin-bottom: 1rem;
            background: #eef2ff;
            color: #4f46e5;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 1rem;
            border: none;
            transition: background 0.18s;
        }
        .back-btn:hover {
            background: #c7d2fe;
            color: #3730a3;
        }
        h2 {
            text-align: center;
            margin-bottom: 1.6rem;
            color: #1e293b;
        }
        .form-group {
            margin-bottom: 1.15rem;
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            color: #334155;
            font-weight: 500;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            padding: 10px 12px;
            border: 1.3px solid #d1d5db;
            border-radius: 7px;
            font-size: 1rem;
            outline: none;
            transition: border 0.18s;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border: 1.5px solid #4f46e5;
            background: #f5f3ff;
        }
        input[type="submit"] {
            margin-top: 0.5rem;
            width: 100%;
            background: linear-gradient(90deg, #6366f1 0%, #818cf8 100%);
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            letter-spacing: 0.05em;
            cursor: pointer;
            box-shadow: 0 3px 14px rgba(99,102,241,0.09);
            transition: background 0.18s, box-shadow 0.18s;
        }
        input[type="submit"]:hover {
            background: linear-gradient(90deg, #4338ca 0%, #6366f1 100%);
            box-shadow: 0 6px 24px rgba(99,102,241,0.18);
        }
        @media (max-width: 480px) {
            .container {
                min-width: 90vw;
                padding: 1.2rem 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a class="back-btn" href="{{ route('admin.accueil') }}">&#8592; Retour à la liste</a>
        <h2>Enregistrement Admin</h2>
        <form action="{{ route('admin.create') }}" method="post" autocomplete="off">
            @csrf
            @method('post')
            <div class="form-group">
                <label for="nom">Nom complet</label>
                <input type="text" name="nom" id="nom" required placeholder="Ex: Jean Dupont">
            </div>
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" name="email" id="email" required placeholder="exemple@email.com">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" name="password" id="password" required placeholder="Votre mot de passe">
            </div>
            <input type="submit" value="Valider">
        </form>
    </div>
</body>
</html>
