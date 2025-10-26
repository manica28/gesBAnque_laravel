<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue à la Banque</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .credentials { background-color: #fff; padding: 15px; border: 1px solid #ddd; margin: 20px 0; }
        .warning { color: #dc3545; font-weight: bold; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenue à la Banque Sénégalaise</h1>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $client->user->nom }} {{ $client->user->prenom }}</strong>,</p>

            <p>Félicitations ! Votre compte bancaire a été créé avec succès. Voici vos identifiants de connexion :</p>

            <div class="credentials">
                <h3>Vos identifiants :</h3>
                <p><strong>Email :</strong> {{ $client->user->email }}</p>
                <p><strong>Mot de passe :</strong> {{ $password }}</p>
                <p><strong>Numéro de compte :</strong> {{ $client->comptes->first()->numero_compte ?? 'À définir' }}</p>
            </div>

            <p class="warning">
                ⚠️ <strong>Important :</strong> Pour votre sécurité, veuillez changer votre mot de passe lors de votre première connexion.
            </p>

            <p>Vous recevrez également un SMS avec votre code de vérification pour finaliser votre inscription.</p>

            <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>

            <p>Cordialement,<br>
            L'équipe de la Banque Sénégalaise</p>
        </div>

        <div class="footer">
            <p>Cette adresse email est générée automatiquement. Merci de ne pas y répondre.</p>
            <p>&copy; 2025 Banque Sénégalaise. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>