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
        <title>ReSoC - Mur</title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
        <link rel="stylesheet" href="style copie.css">
    </head>
    <body>
    <?php 
        include_once 'header.php';
        ?>
        <div id="wrapper" >
        
        <div id="wrapper">
            <?php
            /**
             * Etape 1: Le mur concerne un utilisateur en particulier
             * La première étape est donc de trouver quel est l'id de l'utilisateur
             * Celui ci est indiqué en parametre GET de la page sous la forme user_id=...
             * Documentation : https://www.php.net/manual/fr/reserved.variables.get.php
             * ... mais en résumé c'est une manière de passer des informations à la page en ajoutant des choses dans l'url
             */
            $userId =intval($_GET['user_id']);
            ?>
            <?php
            /**
             * Etape 2: se connecter à la base de donnée
             */
            $mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
            ?>

            <aside>
                <?php
                /**
                 * Etape 3: récupérer le nom de l'utilisateur
                 */                
                $laQuestionEnSql = "SELECT * FROM users WHERE id= '$userId' ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                $user = $lesInformations->fetch_assoc();
                //@todo: afficher le résultat de la ligne ci dessous, remplacer XXX par l'alias et effacer la ligne ci-dessous
                
                ?>
                <div style="display: flex; flex-direction: column; align-items: center;">
                <img src="User1.jpg" alt="Portrait de l'utilisatrice" style="border-radius: 50%;">
                <p id="nom" style="text-align: center; text-transform: uppercase; font-weight: bold;"><?php echo $user['alias'];?></p>

                </div>
                <section>
                    
                    
                    <?php 
                    function isFollowing ($followed, $following) {
                        $mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
                        $result = $mysqli->query("SELECT * FROM followers WHERE followed_user_id = $followed AND following_user_id = $following");
                        $num_rows = mysqli_num_rows($result);
                        if ($num_rows > 0) {
                            return true;
                        } else {
                            return false;
                        }
                    }

                    $questionSql = "SELECT * FROM followers";
                    $informations = $mysqli->query($questionSql);
                    $followers = $informations->fetch_assoc();
                    $followingId = intval($_SESSION['connected_id']);
                    $followedId = intval($_GET['user_id']);
                    
                    if ($followedId !== $followingId && ! isFollowing($followedId, $followingId)) {?>
                        <form action="wall.php?user_id=<?php echo ($_GET['user_id']) ?>" method="post">
                            <dl>
                                <dd><button type="submit" name="abonnement", value="<?php echo ($_GET['user_id']) ?>">S'abonner</button></dd>
                            </dl>
                        </form>
                        <?php
                        if (isset($_POST['abonnement'])) {
                        $followedId = intval($_POST['abonnement']);
                        $lInstructionSql = "INSERT INTO followers "
                            . "(followed_user_id, following_user_id) "
                            . "VALUES ('" . $followedId . "', "
                            . "'" . $followingId . "'); "
                            ;

                        $ok = $mysqli->query($lInstructionSql);
                        if ( ! $ok) {
                            echo "Impossible de s'abonner: " . $mysqli->error;
                        } else {
                            echo "Vous êtes maintenant abonné.";
                        }
                    }
                } elseif ($followedId !== $followingId && isFollowing($followedId, $followingId)) {?>
                    <form action="wall.php?user_id=<?php echo ($_GET['user_id']) ?>" method="post">
                        <dl>
                            <dd><button type="submit" name="abonnement", value="<?php echo ($_GET['user_id']) ?>">Se désabonner</button></dd>
                        </dl>
                    </form>
                    <?php
                    if (isset($_POST['abonnement'])) {
                        $followedId = intval($_POST['abonnement']);
                        $lInstructionSql = "DELETE FROM followers WHERE "
                            . "followed_user_id = " . $followedId . " AND following_user_id = " . $followingId . "; "
                            ;

                        $ok = $mysqli->query($lInstructionSql);
                        if ( ! $ok) {
                            echo "Impossible de se désabonner: " . $mysqli->error;
                        } else {
                            echo "Vous êtes maintenant désabonné.";
                        }
                    }
                }
                ?>
                </section>
            </aside>
            <main>
                <article>
                <?php 
                /**
                     * TRAITEMENT DU FORMULAIRE
                     */
                    // Etape 1 : vérifier si on est en train d'afficher ou de traiter le formulaire
                    // si on recoit un champs email rempli il y a une chance que ce soit un traitement
                    $enCoursDeTraitement = isset($_POST['auteur']);
                    if ($enCoursDeTraitement)
                    {
                        
                        $authorId = intval($_POST['auteur']);
                        $postContent = $mysqli->real_escape_string($_POST['message']);


                        //Etape 3 : Petite sécurité
                        // pour éviter les injection sql : https://www.w3schools.com/sql/sql_injection.asp
                        
                        $authorId = intval($mysqli->real_escape_string($authorId));
                        $postContent = $mysqli->real_escape_string($postContent);

                        //Etape 4 : construction de la requete
                        
                        $lInstructionSql = "INSERT INTO posts (id, user_id, content, created, parent_id) VALUES (NULL, ?, ?, NOW(), NULL)";
                        $laQuestionEnSql = $mysqli->prepare($lInstructionSql);
                        $laQuestionEnSql->bind_param("is", $authorId, $postContent);
                        $ok = $laQuestionEnSql->execute();
                        // Etape 5 : execution
                        // $ok = $mysqli->query($lInstructionSql);
                        if ( ! $ok)
                        {
                            echo "Impossible d'ajouter le message: " . $mysqli->error;
                        } else
                        {
                            echo "Message posté en tant que :" . $user["alias"];
                        }
                    }
                    ?>                     
                    <form action="wall.php?user_id=<?php echo ($_GET["user_id"]) ?>" method="post">
                        <dl>
                            <dt><label for='auteur'>auteur</label></dt> 
                            <dd><select name='auteur'>
                                <?php echo "<option value=" . $_GET["user_id"] . ">" . $user["alias"] . "</option>" ?>
                            </select></dd>
                            <dt><label for='message'>Message</label></dt> 
                            <dd><textarea name='message'></textarea></dd>      
                        </dl>
                        <input type='submit'>
                    </form>   
                    
                </article>  
                    <?php
                /**
                 * Etape 3: récupérer tous les messages de l'utilisatrice
                 */
                $laQuestionEnSql = "
                    SELECT posts.content, posts.created, users.alias as author_name, tags.id as tagId,
                    COUNT(likes.id) as like_number, GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM posts
                    JOIN users ON  users.id=posts.user_id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE posts.user_id='$userId' 
                    GROUP BY posts.id, tags.id
                    ORDER BY posts.created DESC  
                    ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                }

                /**
                 * Etape 4: @todo Parcourir les messsages et remplir correctement le HTML avec les bonnes valeurs php
                 */
                while ($post = $lesInformations->fetch_assoc())
                {

                    // echo "<pre>" . print_r($post, 1) . "</pre>";
                    ?>                
                    <article>
                        <h3>
                            <time datetime='2020-02-01 11:12:13' ><?php echo($post['created'])?></time>
                        </h3>
                        <address><?php echo($post['author_name'])?></address>
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
                <?php } ?>


            </main>
        </div>
    </body>
</html>