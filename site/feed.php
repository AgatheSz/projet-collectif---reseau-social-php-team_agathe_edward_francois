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
        <title>ReSoC - Flux</title>         
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
        <link rel="stylesheet" href="style copie.css">
    </head>
    <body>
    <?php 
        include_once 'header.php';
        ?>
        
                <?php

if (isset($_SESSION['connected_id']) && $_SESSION['connected_id'] === true) {
// L'utilisateur est connecté, afficher le lien du profil
    echo '<a href="profile.php">Profil</a>';
} else {
// L'utilisateur n'est pas connecté, afficher le lien de connexion
    // echo '<a href="login.php">Connexion</a>';
}
?>

                
        <div id="wrapper">
            <?php
            
            $userId = intval($_GET['user_id']);
            ?>
            <?php
            /**
             * Etape 2: se connecter à la base de donnée
             */
            $mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
            ?>

            <aside>
                <?php
                
                $userId = intval(mysqli_real_escape_string($mysqli, $_GET['user_id']));
                /**
                 * Etape 3: récupérer le nom de l'utilisateur
                 */
                $laQuestionEnSql = "SELECT * FROM `users` WHERE id= ? ";
                $stmt = $mysqli->prepare($laQuestionEnSql);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $lesInformations = $stmt->get_result();
                $user = $lesInformations->fetch_assoc();

                
                ?>
                <img src="User1.jpg" alt="Portrait de l'utilisatrice" style="border-radius: 50%;">
                <section>
                    

                </section>
            </aside>
            <main>
                <?php
                /**
                 * Etape 3: récupérer tous les messages des abonnements
                 */
                $laQuestionEnSql = "
                    SELECT posts.content,
                    posts.created,
                    users.alias as author_name, 
                    users.id as id, 
                    tags.id as tagId,
                    count(likes.id) as like_number,  
                    GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM followers 
                    JOIN users ON users.id=followers.followed_user_id
                    JOIN posts ON posts.user_id=users.id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE followers.following_user_id='$userId'
                    GROUP BY posts.id, tags.id
                    ORDER BY posts.created DESC  
                    ";
                    $stmt = $mysqli->prepare($laQuestionEnSql);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $lesInformations = $stmt->get_result();
                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                }

                /**
                 * Etape 4: @todo Parcourir les messsages et remplir correctement le HTML avec les bonnes valeurs php
                
                 */
                while ($post = $lesInformations->fetch_assoc()) {
                    // echo "<pre>" . print_r($post, 1) . "</pre>"?>                
                <article>
                    <h3>
                        <time datetime='2020-02-01 11:12:13' ><?php echo($post['created'])?></time>
                    </h3>
                    <address><a href = "wall.php?user_id=<?php echo($post['id'])?>"><?php echo($post['author_name'])?></a></address>
                    <div>
                        <p><?php echo($post['content'])?></p>
                    </div>                                            
                    <footer>
                        <small>♡<?php echo $post['like_number'] ?> </small>
                        <form action="news.php" method="post">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" name="like_button">J'aime</button>
                        </form>
                        <?php $taglist = explode(",", $post['taglist']);
                        foreach ($taglist as $tag){?>
                        <a href="tags.php?tag_id=<?php echo($post['tagId'])?>">#<?php echo($tag)?></a>
                        <?php } ?>
                    </footer>
                </article>
                <?php
                }
                ?>


            </main>
        </div>
    </body>
</html>
