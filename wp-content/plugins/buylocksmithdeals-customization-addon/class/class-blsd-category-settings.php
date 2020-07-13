<?php
defined('ABSPATH') || exit;
if( !class_exists('BLSDCategorySettings') ){
	class BLSDCategorySettings {

		protected static $_instance = null;

		/**
		 * provide class instance
		 * @return type
		 */
		public static function instance() {
			if (is_null(self::$_instance)) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Initialize class action and filters.
		 */
		public function __construct() {
			$this->init_hooks();
		}

		/**
		 * Filters and Actions are bundled.
		 * @return boolean
		 */
		private function init_hooks() {
			add_action('wp_enqueue_scripts', array( $this, 'blsd_fetch_bootstrap_css_cdn' ) );
			add_action('wp_enqueue_scripts', array( $this, 'blsd_cat_list_style' ) );
			add_shortcode('blsd_product_categories', array($this, 'blsd_product_categories_cb') );
			
			add_action('product_cat_add_form_fields', array( $this, 'add_category_restriction'), 10, 1);
			add_action('product_cat_edit_form_fields', array( $this, 'edit_category_restriction'), 10, 1);
			
			add_action('edited_product_cat', array( $this, 'save_category_restriction' ), 10, 1);
			add_action('create_product_cat', array( $this, 'save_category_restriction' ), 10, 1);
		}
		
		public function blsd_cat_list_style(){
			global $post;
			if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'blsd_product_categories')) {
				wp_enqueue_style( 'blsd-cat-list-style', BUYLOCKSMITH_DEALS_ASSETS_PATH . 'css/cat-list-style.css' );
			}
		}
		
		public function blsd_fetch_bootstrap_css_cdn() {
			global $post;
                        $template =  get_page_template_slug( $post->ID).'.....';
                        $template = explode('/blsd_page_template/', $template);
			if ((is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'blsd_product_categories')) || isset($template[1]) ) {
				wp_enqueue_style( 'blsd-bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' );
				wp_enqueue_style( 'blsd-bootstrap-css-theme', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css' );
				wp_enqueue_script( 'blsd-bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array('jquery'), '1.0', false );
			}
		}
		
		public static function blsd_product_categories_cb($atts, $content = ""){
			
			$shortcode_atts_arr = array(
				'att1'		=>	null,
				'att2'		=>	null,
			);
			
			if( isset( $atts['att1'] ) ){
				$shortcode_atts_arr['att1'] = $atts['att1'];
			}
			
			if( isset( $atts['att2'] ) ){
				$shortcode_atts_arr['att2'] = $atts['att2'];
			}
			
			$atts = shortcode_atts( $shortcode_atts_arr, $atts, 'blsd_product_categories' );
			
			$html = "";
			$content = do_shortcode($content);
			
			$cats = $this->get_cat_data_arr();
			/* $cats = $this->get_cat_data_arr_recursive(); */
			ob_start();
			/* output buffer, start */ ?>
			<div class="blsd_product_categories" id="blsd_product_categories">
			
				<!--div class="jumbotron text-center">
				  <h1>Categories</h1>
				  <p>Go for unending domains of shopping</p>
				</div-->

				<div class="container-fluid blsd_cat_list_container">
<!--				  <h1>Choose Category</h1>-->
				  
				  <div class="row blsd_cat_list_row"><?php
				  foreach($cats as $k => $v){ ?>
					<div class="col-sm-3 blsd_cat_list_column" data-id="<?php echo $v['slug']; ?>">
					  <p>
                                              <a href="<?php echo $v['term_link']; ?>" class="blsd_cat_link">
						<image src="<?php echo $v['image_url']; ?>" class="blsd_cat_thumb" />
						</a>
					  </p>
					  <p>
						<a href="<?php echo $v['term_link']; ?>" class="blsd_cat_link"><?php echo $v['name']; ?></a>
					  </p>
					</div><?php
					} ?>
				  </div>
				</div> <!-- container end -->
			</div>
			
			<?php
			/* output buffer, end */
			$html = ob_get_clean();

			return $html;
			
		}
		
		public function get_product_cat_list(){
			$terms = get_terms( array(
				'taxonomy' => 'product_cat',
				'hide_empty' => false,
				'parent' => 0,
				'exclude' => 'uncategorized',
				)
			);
			
			return $terms;
		}
		
		private function exclude_uncategorized( $cat_arr ){
			$t_cat_arr = $cat_arr;
			$cat_arr = array();
			
			$i = 0;
			
			foreach( $t_cat_arr as $k => $v ){
				if('uncategorized' == $v->slug){
					continue;
				}
				
				$cat_arr[$i++] = $v;
			}
			
			return $cat_arr;
		}
		
		private function get_cat_data_arr_recursive($exclude = true){
			$parent = 0;
			if( isset($_REQUEST['blsdcatid']) ){
				$parent_term_id = trim($_REQUEST['blsdcatid']);
				if( '' != $parent_term_id && is_numeric( $parent_term_id ) ){
					$parent = $parent_term_id;
				}
			}
			
			$terms = get_terms( array(
				'taxonomy' => 'product_cat',
				'hide_empty' => false,
				'parent' => $parent,
				'exclude' => 'uncategorized',
				)
			);
			
			$t_cat_arr = $terms;
			$cat_arr = array();
			
			$i = 0;
			foreach( $t_cat_arr as $k => $v ){
				if( $exclude ){
					if('uncategorized' == $v->slug){
						continue;
					}
				}
				
				$is_blsd_exclude = get_term_meta($v->term_id, 'blsd_exclude_cat_meta', true); 
				if( 'yes' == $is_blsd_exclude ){
					continue;
				}
				
				$cat_arr[$i] = get_object_vars($v);
				$cat_arr[$i]['term_link'] = $this->get_term_url($v->term_id);
				$cat_arr[$i]['image_url'] = $this->get_term_thumb_url($v->term_id);
				$cat_arr[$i]['has_child'] = $this->has_child($v->term_id);
				
				if( $this->has_child($v->term_id) ){
					$termurl = add_query_arg( array('blsdcatid' => $v->term_id), get_permalink() );
					$cat_arr[$i]['term_link'] = $termurl;
				}
				
				$i++;
			}
			
			return $cat_arr;
		}
		
		 function get_cat_data_arr($exclude = true){
                    $args = array(
				'taxonomy' => 'product_cat',
				'hide_empty' => false,
				'parent' => 0
				);
                    if($exclude==true){
                        $term_detail_exclude = get_term_by( 'slug', 'uncategorized',  'product_cat') ;
                       if(isset($term_detail_exclude->term_id)){
                            $args['exclude'] = array($term_detail_exclude->term_id);
                       }
                        }
			$terms = get_terms( 
                                $args
			);
			
			$t_cat_arr = $terms;
			$cat_arr = array();
			
			$i = 0;
			foreach( $t_cat_arr as $k => $v ){
//				if( $exclude ){
//					if('uncategorized' == $v->slug){
//						continue;
//					}
//				}
				
				$is_blsd_exclude = get_term_meta($v->term_id, 'blsd_exclude_cat_meta', true); 
				if( 'yes' == $is_blsd_exclude ){
					continue;
				}
				
				$cat_arr[$i] = get_object_vars($v);
				$cat_arr[$i]['term_link'] = $this->get_term_url($v->term_id);
				$cat_arr[$i]['image_url'] = $this->get_term_thumb_url($v->term_id);
				$cat_arr[$i]['has_child'] = $this->has_child($v->term_id);
				
				
				$i++;
			}
			
			return $cat_arr;
		}
		
		public function add_category_restriction(){ ?>
			<div class="form-field">
				<input type="checkbox" name="blsd_exclude_cat_meta" value="yes" id="blsd_exclude_cat_meta" />
				<label for="blsd_exclude_cat_meta"><?php _e('Do not show this category', 'buylocksmithdeals-addon'); ?></label>
			</div><?php
		}
		
		public function edit_category_restriction($term){
			
			$term_id = $term->term_id;
			
			$blsd_exclude_cat_meta = get_term_meta($term_id, 'blsd_exclude_cat_meta', true); ?>
			<tr class="form-field">
				<th scope="row" valign="top">
				
					<label for="blsd_exclude_cat_meta"><?php _e('Do not show this category', 'buylocksmithdeals-addon'); ?></label>
				</th>
				<td>
					<input type="checkbox" name="blsd_exclude_cat_meta" id="blsd_exclude_cat_meta" <?php checked('yes', $blsd_exclude_cat_meta); ?> />
				</td>
			</tr><?php
		}
		
		public function save_category_restriction($term_id){
			$blsd_exclude_cat_meta = 'no';
			
			if( isset($_REQUEST['blsd_exclude_cat_meta']) ){
				$blsd_exclude_cat_meta = 'yes';
			}
			
			update_term_meta($term_id, 'blsd_exclude_cat_meta', $blsd_exclude_cat_meta);
		}
		
		public function has_child($term_id, $taxonomy_name = 'product_cat'){
			$has_child = false;
			
			$term_children = get_term_children( $term_id, $taxonomy_name );
			
			if( !is_wp_error($term_children) ){
				if(!empty( $term_children)){
					$has_child = true;
				}
			}
			
			return $has_child;
		}
		
		public function get_term_url($term_id){
			$t_link = 'javascript:void(0)';
			$term_id = (int)$term_id;
			$term_link = get_term_link( $term_id );
			
			if ( !is_wp_error( $term_link ) ) {
				$t_link = $term_link;
			}
			
			return $t_link;
		}
		
		public function get_term_thumb_url($term_id){
			$image_url = BUYLOCKSMITH_DEALS_ASSETS_PATH . 'img/no_image_available.jpeg';
			$thumbnail_id = get_term_meta( $term_id, 'thumbnail_id', true );
			if($thumbnail_id){
				$image_url = wp_get_attachment_url( $thumbnail_id );
			}
			
			return $image_url;
		}
		
	} /* class end */
} /* if end */

function run_blsd_category_settings_func() {
	BLSDCategorySettings::instance();
}
run_blsd_category_settings_func();