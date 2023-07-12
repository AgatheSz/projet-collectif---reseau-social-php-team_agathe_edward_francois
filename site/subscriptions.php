<?php
session_start();
if (!isset($_SESSION['connected_id'])){
    header("Location: login.php");
    exit();
}
?>

<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Mes abonnements</title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
        <link rel="stylesheet" href="style copie.css">
    </head>
    <body>
    <?php 
        include_once 'header.php';
        ?>
        
        <div id="wrapper">
            <aside>
                <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
                <section>
                    

                </section>
            </aside>
            <main class='contacts'>
                <?php
                // Etape 1: récupérer l'id de l'utilisateur
                $userId = intval($_GET['user_id']);
                // Etape 2: se connecter à la base de donnée
                $mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
                // Etape 3: récupérer le nom de l'utilisateur
                $laQuestionEnSql = "
                    SELECT users.* 
                    FROM followers 
                    LEFT JOIN users ON users.id=followers.followed_user_id 
                    WHERE followers.following_user_id='$userId'
                    GROUP BY users.id
                    ";

                    // Utilisation d'une requête préparée avec un paramètre lié
                    $stmt = $mysqli->prepare($laQuestionEnSql);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $lesInformations = $stmt->get_result();
            
                //@todo: faire la boucle while de parcours des abonnés et mettre les bonnes valeurs ci dessous 
                while ($user = $lesInformations->fetch_assoc())
                {?>
                <article>
                    <img src="User1.jpg" alt="Portrait de l'utilisatrice" style="border-radius: 50%;">
                    <h3><a href = "wall.php?user_id=<?php echo($user['id'])?>"><?php echo($user['alias'])?></a></h3>
                    <p>id: <?php echo($user['id'])?></p>                    
                </article>
                <?php } ?>
            </main>
        </div>
    </body>
</html>
