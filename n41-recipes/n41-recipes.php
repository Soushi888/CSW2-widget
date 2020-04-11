<?php
/*
Plugin Name: N41 Recipes
Plugin URI: https://n41.plugins.com
Description: Gestion de recettes de cuisine
Version: 1.0
Author: N41
Author URI: https://n41.com
*/

/* Section pour la gestion des réglages dans l'administration
 * ==========================================================
 */
include ("n41-recipes-settings.php");

/* Section pour la gestion du widget qui affiche la dernière recette enregistrée
 * =============================================================================
 */

// chargement de la classe du widget
include ("class-n41-recipes-widget-news.php");

// Initialisation de tous les widgets de l'extension (ici un seul, N41_Recipes_Widget_News)  
function n41_recipes_register_widgets() {
	register_widget('N41_Recipes_Widget_News');
}

// déclenchement de la fonction d'initialisation des widgets de l'extension
// dans les actions du crochet widgets_init
add_action('widgets_init', 'n41_recipes_register_widgets'); 


/* Section pour l'activation de l'extension
 * ========================================
 */

register_activation_hook( __FILE__, 'n41_recipes_activate' );
 
/**
 * Traitements à l'activation de l'extension
 *
 * @param none
 * @return none
 */
function n41_recipes_activate() {
	n41_recipes_check_version();
	n41_recipes_create_table();
	n41_recipes_default_settings();
	n41_recipes_create_pages();
}

/**
 * Vérification de la version WP
 *
 * @param none
 * @return none
 */
function n41_recipes_check_version() {
	global $wp_version;
	if ( version_compare( $wp_version, '4.9', '<' ) ) {
		wp_die( 'Cette extension requiert WordPress version 4.9 ou plus.' );
	}
}

/**
 * Création de la table recipes
 *
 * @param none
 * @return none
 */
function n41_recipes_create_table() {
	global $wpdb;

	$sql = "CREATE TABLE $wpdb->prefix"."recipes (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL,
			ingredients text NOT NULL,
			instructions text NOT NULL,
			prep_time int(11) UNSIGNED NOT NULL,
			cook_time int(11) UNSIGNED NOT NULL,
			PRIMARY KEY(id)
			) ".$wpdb->get_charset_collate();
  
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql ); 
}

/**
 * Inilialisation de l'option n41_recipes_settings,
 * qui regroupe un tableau de réglages pour l'affichage des rubriques sur la page de liste
 *
 * @param none
 * @return none
 */
function n41_recipes_default_settings() {
	add_option(
		'n41_recipes_settings',
		array(
			"view_ingredients"  => 'yes',
			"view_instructions" => 'yes',
			"view_prep_time"    => 'yes',
			"view_cook_time"    => 'yes'
		)	
	);
}

/**
 *Création des pages de l'extension
 *
 * @param none 
 * @return none
 */
function n41_recipes_create_pages(){
	$n41_recipes_page = array(
	  'post_title'     => "Saisie d'une recette",
	  'post_name'      => "saisie-recette",  
  	  'post_content'   => "[saisie_recette]",
  	  'post_type'      => 'page',
  	  'post_status'    => 'publish',
  	  'comment_status' => 'closed',
  	  'ping_status'    => 'closed',
	  'meta_input'     => array('n41_recipes' => 'form')
	);
	wp_insert_post($n41_recipes_page);

	$n41_recipes_page = array(
	  'post_title'     => "Recettes",
	  'post_name'      => "recettes", 
	  'post_content'   => "[n41_recipes_list]",
	  'post_type'      => 'page',
	  'post_status'    => 'publish',
	  'comment_status' => 'closed',
	  'ping_status'    => 'closed',
	  'meta_input'     => array('n41_recipes' => 'list')
	);
	wp_insert_post($n41_recipes_page);

	$n41_recipes_page = array(
	  'post_title'     => "Recette",
	  'post_name'      => "recette", 
	  'post_content'   => "[n41_recipes_single]",
	  'post_type'      => 'page',
	  'post_status'    => 'publish',
	  'comment_status' => 'closed',
	  'ping_status'    => 'closed',
	  'meta_input'     => array('n41_recipes' => 'single')
	);
	wp_insert_post($n41_recipes_page);
}

/* Section pour la désactivation de l'extension
 * ============================================
 */

register_deactivation_hook( __FILE__, 'n41_recipes_deactivate' );
 
/**
 * Traitements à la désactivation de l'extension
 *
 * @param none
 * @return none
 */
function n41_recipes_deactivate() {
	n41_recipes_delete_pages();
}  

/**
 * Suppression des pages de l'extension, exceptés les posts des images
 *
 * @param none
 * @return none
 */
function n41_recipes_delete_pages(){
	global $wpdb;
	$postmetas = $wpdb->get_results(
                   "SELECT * FROM $wpdb->postmeta WHERE meta_key = 'n41_recipes'");
	$force_delete = true;
	foreach ($postmetas as $postmeta) {
		// suppression lorsque la métadonnée n41_recipes n'est pas celle d'un post image
		if ($postmeta->meta_value !== 'img') {
			wp_delete_post( $postmeta->post_id, $force_delete );
		}
	}
}

/* Section pour la désinstallation de l'extension
 * ==============================================
 */
 
register_uninstall_hook(__FILE__, 'n41_recipes_uninstall');

/**
 * Traitements à la désinstallation de l'extension
 *
 * @param none
 * @return none
 */
function n41_recipes_uninstall() {
	n41_recipes_drop_table();
	n41_recipes_delete_settings();
	n41_recipes_delete_images();
}

/**
 * Suppression de la table recipes
 *
 * @param none
 * @return none
 */
function n41_recipes_drop_table() {
	global $wpdb;
	$sql = "DROP TABLE $wpdb->prefix"."recipes";
	$wpdb->query($sql);
}

/**
 * Suppression de l'option n41_recipes_settings
 *
 * @param none
 * @return none
 */
function n41_recipes_delete_settings() {
	delete_option('n41_recipes_settings');
}

/**
 * Suppression des images (posts et fichiers) de l'extension
 *
 * @param none
 * @return none
 */
function n41_recipes_delete_images(){
	global $wpdb;
	$postmetas = $wpdb->get_results(
                   "SELECT * FROM $wpdb->postmeta WHERE meta_key = 'n41_recipes'");
	// booléen pour indiquer à la fonction wp_delete_post, la suppression des fichiers
	// et la suppression des informations dans les tables posts et postmeta
	$force_delete = true;
	foreach ($postmetas as $postmeta) {
		if ($postmeta->meta_value === 'img') {
			wp_delete_post( $postmeta->post_id, $force_delete );
		}
	}
}

/* Section pour l'utilisation de crochets (hooks)
 * ==============================================

/**
 * Ajoute le nom de la recette dans le titre via le crochet de filtres the_title
 * pour la page "Recette" uniquement, identifiée par la métadonnée n41_recipes = single
 * @param bool  $title    The current title
 * @param int   $post_id  The current post ID
 * @return bool false
 */
function n41_recipes_hook_the_title($title, $post_id) {
	$single = true;
	$n41_recipes = get_post_meta($post_id, 'n41_recipes', $single);
	if ($n41_recipes === 'single' && isset($_GET['id'])) {
		global $wpdb;
		$sql = "SELECT title FROM $wpdb->prefix"."recipes WHERE id =%d";
		$recipe = $wpdb->get_row($wpdb->prepare($sql, $_GET['id']));
		$title .= ':<br>'.stripslashes($recipe->title);
	}
	return $title;
}

// Ajout de la fonction n41_recipes_hook_title au crochet de filtres the_title 	
add_filter( 'the_title', 'n41_recipes_hook_the_title', 10, 2 );

/**
 * Force le crochet de filtres comments_open à faux pour les pages de l'extension,
 * ces pages sont identifiées par l'association de la métadonnée n41_recipes
 *
 * @param bool  $open    Whether the current post is open for comments
 * @param int   $post_id The current post ID
 * @return bool false
 */
function n41_recipes_close_hook_comments_open($open, $post_id) {
	$single = true;
	$n41_recipes = get_post_meta($post_id, 'n41_recipes', $single);
	if ($n41_recipes !== '') {
		$open = false;
	}
	return $open;
}

// Ajout de la fonction n41_recipes_close_hook_comments_open au crochet de filtres comments_open 	
add_filter( 'comments_open', 'n41_recipes_close_hook_comments_open', 10, 2 );


/* Section pour le traitement de la page de saisie d'une recette 
 * ============================================================= 
 */
 
/**
 * Création du formulaire de saisie d'une recette
 *
 * @param none
 * @return echo html form recipe code
 */
function html_form_recipe_code() {
?>
	<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ) ?>" method="post" enctype="multipart/form-data">
		<label>Nom de la recette</label>
		<input type="text" name="title" required>
		<label>Image de la recette</label>
		<input type="file" name="image" required>
		<label>Ingrédients</label>
		<textarea name="ingredients" placeholder="une ligne par ingrédient" required></textarea>
		<label>Instructions</label>
		<textarea name="instructions" required></textarea>		
		<label>Temps de préparation</label>
		<input type="text" name="prep_time" placeholder="nombre de minutes" required pattern="[1-9][0-9]*">
		<label>Temps de cuisson</label>
		<input type="text" name="cook_time" placeholder="nombre de minutes" required pattern="[1-9][0-9]*">
		<input type="submit" style="margin-top: 30px;" name="submitted" value="Envoyez"> 
	</form>
<?php
}

/**
 * Insertion d'une recette dans la table recipes
 *
 * @param none
 * @return none
 */
function insert_recipe() {
	global $post;
	// si le bouton submit est cliqué
	if ( isset( $_POST['submitted'] ) ) {
		// assainir les valeurs du formulaire
		$title        = sanitize_text_field( $_POST["title"] );
		$ingredients  = sanitize_textarea_field( $_POST["ingredients"] );
		$instructions = sanitize_textarea_field( $_POST["instructions"] );
		$prep_time    = sanitize_text_field( $_POST["prep_time"] );
		$cook_time    = sanitize_text_field( $_POST["cook_time"] );
		
		// insertion dans la table
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix.'recipes',
			array(
				'title' => $title,
				'ingredients' => $ingredients,
				'instructions' => $instructions,
				'prep_time' => $prep_time,
				'cook_time' => $cook_time
			),
			array(
				'%s',
				'%s',
				'%s',
				'%d',
				'%d'
			)
		);
		// génèrer le titre de l'image avec l'id de la recette insérée dans la table recipes
		$recipe_image_title = "recipe-".$wpdb->insert_id;
		// echo "<pre>".print_r($_FILES, true)."</pre>"; exit;
		// chargement des fichiers nécessaires à l'exécution de la fonction media_handle_upload
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		// déplacement du fichier image dans le dossier wp-content/uploads
		// et création d'un post de type attachment dans la table posts
		// le premier paramètre 'image' est le nom du champ input qui suit dans $_FILES['image']
		$recipe_image_post_id = media_handle_upload( 'image', 0, array('post_title' => $recipe_image_title));
		// ajouter une métadonnée n41_recipes dans la table postmeta, associée au post précédent,
		// pour rattacher ce post à l'extension   
		$unique = true;
		add_post_meta($recipe_image_post_id, 'n41_recipes', 'img', $unique);
?>
	<p>La recette a été enregistrée.</p>
<?php
	}
}

/**
 * Exécution du code court (shortcode) de saisie d'une recette 
 *
 * @param none
 * @return the content of the output buffer (end output buffering)
 */
function shortcode_input_form_recipe() {
	ob_start(); // temporisation de sortie
	insert_recipe();
	html_form_recipe_code();
	return ob_get_clean(); // fin de la temporisation de sortie pour l'envoi au navigateur
}

// créer un shortcode pour afficher et traiter le formulaire
add_shortcode( 'saisie_recette', 'shortcode_input_form_recipe' );


/* Section pour le traitement de la page de liste des recettes
 * =========================================================== 
 */

/**
 * Création de la page de liste des recettes
 *
 * @param none
 * @return echo html list recipes code
 */
function n41_recipes_html_list_code() {

	/* Affichage d'un lien vers le formulaire de saisie d'une recette pour l'administrateur du site
	   -------------------------------------------------------------------------------------------- */
?>
	<section style="margin: 0 auto; width: 80%; max-width: 100%; padding: 0">
<?php
	global $wpdb;
	if (current_user_can('administrator')) :
		$postmeta = $wpdb->get_row(
                   "SELECT * FROM $wpdb->postmeta WHERE meta_key = 'n41_recipes' AND meta_value = 'form'");
?>
		<a href="<?php echo get_permalink($postmeta->post_id)?>">Saisie d'une recette</a>
<?php					
	endif;

	$recipe_search = '';
	if (isset($_POST['recipe-search'])) :
		$recipe_search = trim($_POST['recipe-search']);
	endif;

	/* Affichage du formulaire de filtrage de recettes 
	   ----------------------------------------------- */
?>
		<form style="margin-top: 30px" action="<?= esc_url( $_SERVER['REQUEST_URI'] ) ?>" method="post">
			<input type="text"
			       style="display: inline-block; width: 500px; padding: 0 10px; line-height: 50px"
				   name="recipe-search"
				   placeholder="Filtrer les recettes contenant cette chaîne de caractères"
				   value="<?= $recipe_search?>">
			<input type="submit"
			       style="display: inline-block; margin-left: 20px; padding: 0 24px; line-height: 50px;"
				   name="submitted"
				   value="Envoyez">
		</form>
<?php

	/* Affichage de la liste des recettes 
	   ---------------------------------- */

	$sql  = "SELECT * FROM $wpdb->prefix"."recipes
			 WHERE title LIKE '%s'
	   		 ORDER BY title ASC";

	$recipes = $wpdb->get_results($wpdb->prepare($sql, '%'.$recipe_search.'%'));

	if (count($recipes) > 0) :
		$postmeta = $wpdb->get_row(
                   "SELECT * FROM $wpdb->postmeta WHERE meta_key = 'n41_recipes' AND meta_value = 'single'");
		$single_permalink = get_permalink($postmeta->post_id);

		$settings = get_option('n41_recipes_settings');

		foreach ($recipes as $recipe) :
?>
		<hr>
		<article style="display: flex">
			<h4 style="margin: 0; width: 300px;">
				<a href="<?php echo $single_permalink.'?page='.stripslashes($recipe->title).'&id='.$recipe->id?>"><?= stripslashes($recipe->title) ?></a>
			</h4>
			<div>
<?php
			if (isset($settings['view_ingredients']) && $settings['view_ingredients'] === 'yes') :
?>
				<div style="display: flex">
					<p style="width:250px; padding: 5px; color: #777">Ingrédients:</p>
					<p style="padding: 5px"><?= stripslashes(nl2br($recipe->ingredients)) ?></p>
				</div>
<?php
			endif;
			if (isset($settings['view_instructions']) && $settings['view_instructions'] === 'yes') :
?>
				<div style="display: flex">
					<p style="width:250px; padding: 5px; color: #777">Instructions:</p>
					<p style="padding: 5px"><?= stripslashes(nl2br($recipe->instructions)) ?></p>
				</div>
<?php
			endif;
			if (isset($settings['view_prep_time']) && $settings['view_prep_time'] === 'yes') :
?>
				<div style="display: flex">
					<p style="width:250px; padding: 5px; color: #777">Temps de préparation:</p>
					<p style="padding: 5px"><?= $recipe->prep_time ?> minutes</p>
				</div>
<?php
			endif;
			if (isset($settings['view_cook_time']) && $settings['view_cook_time'] === 'yes') :
?>
				<div style="display: flex">
					<p style="width:250px; padding: 5px; color: #777">Temps de cuisson:</p>
					<p style="padding: 5px"><?= $recipe->cook_time ?> minutes</p>
				</div>
<?php
			endif;
?>
			</div>
		</article>	
<?php
		endforeach;
?>
		</table>
<?php
	else :
?>
		<p>Aucune recette n'est enregistrée.</p>
<?php
	endif;
?>
	</section>
<?php
}

/**
 * Exécution du code court (shortcode) d'affichage de la liste des recettes
 *
 * @param none
 * @return the content of the output buffer (end output buffering)
 */
function n41_recipes_shortcode_list() {
	ob_start(); // temporisation de sortie
	n41_recipes_html_list_code();
	return ob_get_clean(); // fin de la temporisation de sortie pour l'envoi au navigateur
}

// créer un shortcode pour afficher la liste des recettes
add_shortcode( 'n41_recipes_list', 'n41_recipes_shortcode_list' );


/* Section pour le traitement de la page d'affichage d'une recette
 * =============================================================== 
 */

/**
 * Création de la page d'affichage d'une recette
 *
 * @param none
 * @return echo html single recipe code
 */
function n41_recipes_html_single_code() {

	/* Affichage d'un lien vers la page de liste des recettes
	   ------------------------------------------------------ */
	global $wpdb;
	$postmeta = $wpdb->get_row("SELECT * FROM $wpdb->postmeta WHERE meta_key = 'n41_recipes' AND meta_value = 'list'");
?>
	<section style="margin: 0 auto; width: 80%; max-width: 100%; padding: 0">
		<a style="display: inline-block; margin-bottom: 30px;" href="<?php echo get_permalink($postmeta->post_id)?>">Liste des recettes</a>
<?php					

	/* Affichage de la recette 
	   ----------------------- */
	
	$recipe_id = isset($_GET['id']) ? $_GET['id'] : null;
	$sql = "SELECT * FROM $wpdb->prefix"."recipes WHERE id =%d";
	
	$recipe = $wpdb->get_row($wpdb->prepare($sql, $recipe_id));
	if ($recipe !== null) :
		// récupération du post de l'image associée à la recette, dans la table posts,
		// à partir du post_name qui contient l'id de la recette 
		$recipe_image_post = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE post_name = 'recipe-".
		                                    $recipe->id."' AND post_type='attachment'");
?>
	<!–- l'url de ce média est obtenue avec wp_get_attachment_url --> 
		<img style="margin-bottom: 30px;" src="<?php echo  wp_get_attachment_url($recipe_image_post->ID)?>" alt="<?php echo $recipe->title ?>">
		<div style="display: flex">
			<p style="width:250px; padding: 5px; color: #777">Ingrédients:</p>
			<p style="padding: 5px"><?= stripslashes(nl2br($recipe->ingredients)) ?></p>
		</div>
		<div style="display: flex">
			<p style="width:250px; padding: 5px; color: #777">Instructions:</p>
			<p style="padding: 5px"><?= stripslashes(nl2br($recipe->instructions)) ?></p>
		</div>
		<div style="display: flex">
			<p style="width:250px; padding: 5px; color: #777">Temps de préparation:</p>
			<p style="padding: 5px"><?= $recipe->prep_time ?> minutes</p>
		</div>
		<div style="display: flex">
			<p style="width:250px; padding: 5px; color: #777">Temps de cuisson:</p>
			<p style="padding: 5px"><?= $recipe->cook_time ?> minutes</p>
		</div>
<?php
	else :
?>
		<p>Cette recette n'est pas enregistrée.</p>
<?php
	endif;
	?>
	</section>
<?php
}

/**
 * Exécution du code court (shortcode) d'affichage d'une recette
 *
 * @param none
 * @return the content of the output buffer (end output buffering)
 */
function n41_recipes_shortcode_single() {
	ob_start(); // temporisation de sortie
	n41_recipes_html_single_code();
	return ob_get_clean(); // fin de la temporisation de sortie pour l'envoi au navigateur
}

// créer un shortcode pour afficher une recette
add_shortcode( 'n41_recipes_single', 'n41_recipes_shortcode_single' );