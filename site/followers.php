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
        <title>ReSoC - Mes abonnés </title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
        <link rel="stylesheet" href="style copie.css">
    </head>
    <body>
        <header>
            <img src="resoc.jpg" alt="Logo de notre réseau social"/> 
            <nav id="menu">
                <a href="news.php">Actualités</a>
                <a href="wall.php?user_id=5">Mur</a>
                <a href="feed.php?user_id=5">Flux</a>
                <a href="tags.php?tag_id=1">Mots-clés</a>
            </nav>
            <nav id="user">
                <a href="#">Profil</a>
                <ul>
                    <li><a href="settings.php?user_id=5">Paramètres</a></li>
                    <li><a href="followers.php?user_id=5">Mes suiveurs</a></li>
                    <li><a href="subscriptions.php?user_id=5">Mes abonnements</a></li>
                </ul>

            </nav>
        </header>
        <div id="wrapper">          
            <aside>
                <img src="User1.jpg" alt="Portrait de l'utilisatrice" style="border-radius: 50%;">
                <section>
                    

                </section>
            </aside>
            <main class='contacts'>
                <!-- <h1> COUCOU </h1> -->
                <?php
                // Etape 1: récupérer l'id de l'utilisateur
                $userId = intval($_GET['user_id']);
                // Etape 2: se connecter à la base de donnée
                $mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
                // Etape 3: récupérer le nom de l'utilisateur
                $laQuestionEnSql = $mysqli ->prepare("
                    SELECT users.*
                    FROM followers
                    LEFT JOIN users ON users.id=followers.following_user_id
                    WHERE followers.followed_user_id=?
                    GROUP BY users.id
                    ");
                    $laQuestionEnSql->bind_param("i", $userId);
                    $laQuestionEnSql->execute();
                    $lesInformations = $laQuestionEnSql->get_result();

                    // $lesInformations = $mysqli->query($laQuestionEnSql);
               
                //@todo: faire la boucle while de parcours des abonnés et mettre les bonnes valeurs ci dessous 
                while ($user = $lesInformations->fetch_assoc())
                { ?>
                <article>
                    <img src="user.jpg" alt="blason"/>
                    <h3><a href = "wall.php?user_id=<?php echo($user['id'])?>"><?php echo($user['alias'])?></a></h3>
                    <p>id: <?php echo($user['id'])?></p>
                </article>
                <?php } ?>
            </main>
        </div>
    </body>
</html>
