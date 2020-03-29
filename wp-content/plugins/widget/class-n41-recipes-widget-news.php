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
class N41_Recipes_Widget_News extends WP_Widget {

	/**
	 * Constructeur d'une nouvelle instance de cette classe 
	 *
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'n41_recipes_widget_news',
			'description' => 'Affiche le nom de la dernière recette enregistrée.'
		);
		parent::__construct( 'n41_recipes_widget_news', 'N41 Recipes - Dernière recette', $widget_ops );
	}

	/**
	 * Affiche le contenu de l'instance courante du widget
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'
	 * @param array $instance Settings for the current widget instance
	 */
	public function widget( $args, $instance ) {
		$title = !empty( $instance['title'] ) ? $instance['title'] : __('Last recipe');

		/** Ce crochet de filtres est documenté dans wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		// le tableau args contient les codes html de mise en forme
		// enregistrés par la fonction WP register_sidebar dans le thème courant 
		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Affichage du lien vers la page de la dernière recette
		$this->get_last_recipe();

		echo $args['after_widget'];
	}

	/**
	 * Affichage du lien vers la page de la dernière recette
	 *
	 * @param none
	 */
	public function get_last_recipe() {
		global $wpdb;
		// récupération de la dernière recette dans la table recipes
		$sql = "SELECT * FROM $wpdb->prefix"."recipes ORDER BY id DESC LIMIT 1";

		$recipe = $wpdb->get_row($sql);
		if ($recipe  !== null) :
			// récupération du lien vers la page générique d'affichage d'une recette
			$postmeta = $wpdb->get_row("SELECT * FROM $wpdb->postmeta WHERE meta_key = 'n41_recipes' AND meta_value = 'single'");
			$single_permalink = get_permalink($postmeta->post_id);

			// feuille de style CSS pour être homogène avec le thème Twenty Twenty
			wp_register_style("n41_recipes_widget_news", plugins_url('css/n41-Recipes-widget-news.css', __FILE__));
			wp_enqueue_style("n41_recipes_widget_news");	
	?>

			<ul id="n41_Recipes_widget_news">
			  <li>
			    <a href="<?php echo $single_permalink.'?page='.stripslashes($recipe->title).'&id='.$recipe->id?>">
					<?php echo stripslashes($recipe->title) ?>
				</a>
			  </li>#
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
	public function form( $instance ) {
		// ici un seul paramètre de configuration: le titre du widget
		// qui est affiché dans la zone du widget sur les pages du site
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> 
				<input	class="widefat"
						id="<?php echo $this->get_field_id('title'); ?>"
						name="<?php echo $this->get_field_name('title'); ?>"
						type="text"
						value="<?php echo $title; ?>">
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
	public function update( $new_instance, $old_instance ) {
		
		// DEBUG N41
		$nfile = fopen(ABSPATH."n41-debug.log", "a");
		$value =date("Y-m-d H:i:s ").__METHOD__." : old_instance ".print_r($old_instance, true). "\n";
		fwrite($nfile, $value);
		$value =date("Y-m-d H:i:s ").__METHOD__." : new_instance ".print_r($new_instance, true). "\n";
		fwrite($nfile, $value);
		fclose($nfile);
		// FIN DEBUG N41
		
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		return $instance;
	}

}
