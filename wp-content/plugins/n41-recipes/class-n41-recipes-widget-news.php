<?php

/**
 * Widget API: classe N41_Recipes_Widget_News
 *
 * @package N41 Recipes
 */

/**
 * Classe qui implémente le widget N41_Recipes_Widget_News
 * ce widget affiche un lien vers la dernière recette enregistrée dans la table recipes 
 *
 * @see WP_Widget
 */
class N41_Recipes_Widget_News extends WP_Widget
{

	/**
	 * Constructeur d'une nouvelle instance de cette classe 
	 *
	 */
	public function __construct()
	{
		$widget_ops = array(
			'classname'   => 'n41_recipes_widget_news',
			'description' => 'Affiche les noms des dernières recettes enregistrées.'
		);
		parent::__construct('n41_recipes_widget_news', 'N41 Recipes - Dernières recettes', $widget_ops);
	}

	/**
	 * Affiche le contenu de l'instance courante du widget
	 *
	 * @param array $args Display arguments including 'before_title', 'after_title','before_widget', and 'after_widget'
	 * @param array $instance Settings for the current widget instance
	 */
	public function widget($args, $instance)
	{
		$title = !empty($instance['title']) ? $instance['title'] : __('Last recipe');
		$nombreAffiche = !empty($instance['nombreAffiche']) ? $instance['nombreAffiche'] : null;

		/** Ce crochet de filtres est documenté dans wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters('widget_title', $title, $instance["nombreAffiche"], $this->id_base);
		$nombreAffiche = apply_filters('widget_nombreAffiche', $nombreAffiche, $instance["nombreAffiche"], $this->id_base);

		// le tableau args contient les codes html de mise en forme
		// enregistrés par la fonction WP register_sidebar dans le thème courant 
		echo $args['before_widget'];
		if ($title) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Affichage du lien vers la page des dernières recettes
		$this->get_last_recipes($nombreAffiche);

		echo $args['after_widget'];
	}

	/**
	 * Affichage du lien vers la page de la dernière recette
	 *
	 * @param none
	 */
	public function get_last_recipes($nbr = 3)
	{
		global $wpdb;
		if (empty($nbr)) $nbr = 3;

		// récupération de la dernière recette dans la table recipes
		$sql = "SELECT * FROM {$wpdb->prefix}recipes ORDER BY id DESC LIMIT {$nbr}";

		$recipes = $wpdb->get_results($sql);
		if ($recipes  !== null) :
			// récupération du lien vers la page générique d'affichage d'une recette
			$postmeta = $wpdb->get_row("SELECT * FROM $wpdb->postmeta WHERE meta_key = 'n41_recipes' AND meta_value = 'single'");
			$single_permalink = get_permalink($postmeta->post_id);

			// feuille de style CSS pour être homogène avec le thème Twenty Twenty
			wp_register_style("n41_recipes_widget_news", plugins_url('css/n41-Recipes-widget-news.css', __FILE__));
			wp_enqueue_style("n41_recipes_widget_news"); ?>

			<ul id="n41_Recipes_widget_news">
				<?php foreach ($recipes as $recipe) : ?>
					<li>
						<a href="<?php echo $single_permalink . '?page=' . stripslashes($recipe->title) . '&id=' . $recipe->id ?>">
							<?php echo stripslashes($recipe->title) ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php
		else :
		?>
			<p>Aucune recette enregistrée.</p>
		<?php
		endif;
	}

	/**
	 * Affichage du formulaire de configuration du widget
	 *
	 * @param array $instance Current settings
	 */
	public function form($instance)
	{
		// ici un seul paramètre de configuration: le titre du widget
		// qui est affiché dans la zone du widget sur les pages du site
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$nombreAffiche = isset($instance['nombreAffiche']) ? esc_attr($instance['nombreAffiche']) : '';
		?>
		<p>
			<label for="<?= $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>
				<input class="widefat" id="<?= $this->get_field_id('title'); ?>" name="<?= $this->get_field_name('title'); ?>" type="text" value="<?= $title; ?>">
			</label>
			<label for="<?= $this->get_field_id('nombreAffiche'); ?>"><?php _e('nombreAffiche:'); ?>
				<input class="widefat" id="<?= $this->get_field_id('nombreAffiche'); ?>" name="<?= $this->get_field_name('nombreAffiche'); ?>" type="number" min=1 max=5 value="<?= !empty($nombreAffiche) ? $nombreAffiche : 3; ?>">
			</label>
		</p>
<?php
	}

	/**
	 * Modification de la configuration en retour du formulaire
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Updated settings
	 */
	public function update($new_instance, $old_instance)
	{

		// DEBUG N41
		$nfile = fopen(ABSPATH . "n41-debug.log", "a");
		$value = date("Y-m-d H:i:s ") . __METHOD__ . " : old_instance " . print_r($old_instance, true) . "\n";
		fwrite($nfile, $value);
		$value = date("Y-m-d H:i:s ") . __METHOD__ . " : new_instance " . print_r($new_instance, true) . "\n";
		fwrite($nfile, $value);
		fclose($nfile);
		// FIN DEBUG N41

		$instance = $old_instance;
		$instance['title'] = sanitize_text_field($new_instance['title']);
		$instance['nombreAffiche'] = sanitize_text_field($new_instance['nombreAffiche']);
		return $instance;
	}
}
