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
        <title>ReSoC - Actualités</title> 
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
            <img src="User1.jpg" alt="Portrait de l'utilisatrice" style="border-radius: 50%;">
        
                <section>
                    
                </section>
            </aside>
            <main>
    
                <?php
                
                // Etape 1: Ouvrir une connexion avec la base de donnée.
                $mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
                //verification
                if ($mysqli->connect_errno)
                {
                    echo "<article>";
                    echo("Échec de la connexion : " . $mysqli->connect_error);
                    echo("<p>Indice: Vérifiez les parametres de <code>new mysqli(...</code></p>");
                    echo "</article>";
                    exit();
                }

                // Etape 2: Poser une question à la base de donnée et récupérer ses informations
                // cette requete vous est donnée, elle est complexe mais correcte, 
                // si vous ne la comprenez pas c'est normal, passez, on y reviendra
                $laQuestionEnSql = "
                    SELECT posts.content,
                    posts.id as post_number,
                    posts.created,
                    users.alias as author_name,  
                    users.id as id,
                    tags.id as tagId,
                    count(likes.id) as like_number,  
                    GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM posts
                    JOIN users ON  users.id=posts.user_id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    GROUP BY posts.id, tags.id
                    ORDER BY posts.created DESC  
                    LIMIT 5
                    ";
                
                $stmt = $mysqli->prepare($laQuestionEnSql);
                if ($stmt === false) {
                    // Gérer les erreurs de préparation de la requête
                    exit();
                }

                if (!$stmt->execute()) {
                    // Gérer les erreurs d'exécution de la requête
                    exit();
                }else{

                $lesInformations = $stmt->get_result();
                }

                if (!$lesInformations) {
                    echo "<article>";
                    echo("Échec de la requete : " . $mysqli->error);
                    echo("<p>Indice: Vérifiez la requete  SQL suivante dans phpmyadmin<code>$laQuestionEnSql</code></p>");
                    exit();
                }
                $post = $lesInformations->fetch_assoc();

                if (isset($_POST['like_button'])) {
                    // Récupérer l'ID du post à liker depuis le formulaire
                    $post_id = $mysqli->real_escape_string($_POST['post_id']);
                }

                    // Mettre à jour le nombre de likes dans la base de données
                    $mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
                    if ($mysqli->connect_errno) {
                        echo "Échec de la connexion : " . $mysqli->connect_error;
                        exit();
                    }

                    $updateQuery = "INSERT INTO likes (`user_id`, `post_id`) VALUES (?, ?)";
                    $stmt = $mysqli->prepare($updateQuery);
                    if ($stmt === false) {
                        // Gérer les erreurs de préparation de la requête
                        exit();
                    }

                    $stmt->bind_param("ii", $_SESSION['connected_id'], $post['post_number']);
                    if (!$stmt->execute()) {
                        // Gérer les erreurs d'exécution de la requête
                        exit();
                    }

                            
                // Etape 3: Parcourir ces données et les ranger bien comme il faut dans du html
                while ($post = $lesInformations->fetch_assoc())
                {
                    
                    ?>
                    <article>
                        <h3>
                            <time><?php echo $post['created'] ?></time>
                        </h3>
                        <address><a href = "wall.php?user_id=<?php echo($post['id'])?>"><?php echo($post['author_name'])?></a></address>
                        <div>
                            <p><?php echo $post['content'] ?></p>
                        </div>
                        <footer>
                            <small>♡<?php echo $post['like_number'] ?> </small>
                            <form action="news.php" method="post">
                            <input type="hidden" name="post_id" value="<?php echo $post['post_number']; ?>">
                            <button type="submit" name="like_button">J'aime</button>
                        </form>
                                    
                            <?php $taglist = explode(",", $post['taglist']);
                            foreach ($taglist as $tag){?>
                            <a href="tags.php?tag_id=<?php echo $post['tagId'] ?>">#<?php echo($tag)?></a>
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
