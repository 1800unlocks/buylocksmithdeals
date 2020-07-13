<?php
/**
 *  Template Name: Search Steps
 */
// =============================================
// Define Constants
// =============================================
?>
<style>
    * {box-sizing:border-box}

    /* Slideshow container */
    .slideshow-container {
        max-width: 1000px;
        position: relative;
        margin: auto;
    }

    /* Hide the images by default */
    .mySlides {
        display: none;
    }
    .step1_active{
        display: block;
    }

    /* Next & previous buttons */
    .prev, .next_btn {
        cursor: pointer;
        /*  position: absolute;*/
        top: 60%;
        width: auto;
        margin-top: 30px;
        padding: 16px;
        color: white;
        font-weight: bold;
        font-size: 18px;
        transition: 0.6s ease;
        border-radius: 0 3px 3px 0;
        user-select: none;
    }

    .detail_slider_container{
        min-height: 250px;
        margin-bottom: 10px;
    }

    .blsd_cat_list_row {
        overflow: hidden;
        overflow-y: scroll;
        max-height: 300px
    }

    .selectedCategory{
        border: 1px solid red;
    }

    .button_area_main{
        text-align: center
    }


    /* Position the "next_btn button" to the right */
    .next_btn {
        right: 0;
        border-radius: 3px 0 0 3px;
    }

    /* On hover, add a black background color with a little bit see-through */
    .prev:hover, .next_btn:hover {
        background-color: rgba(0,0,0,0.8);
    }

    /* Caption text */
    .text {
        color: #f2f2f2;
        font-size: 15px;
        padding: 8px 12px;
        position: absolute;
        bottom: 8px;
        width: 100%;
        text-align: center;
    }

    /* Number text (1/3 etc) */
    .numbertext {
        color: #f2f2f2;
        font-size: 12px;
        padding: 8px 12px;
        position: absolute;
        top: 0;
    }

    /* The dots/bullets/indicators */
    .dot {
        cursor: pointer;
        height: 15px;
        width: 15px;
        margin: 0 2px;
        background-color: #bbb;
        border-radius: 50%;
        display: inline-block;
        transition: background-color 0.6s ease;
    }

    .active, .dot:hover {
        background-color: #717171;
    }

    /* Fading animation */
    .fade_slider {
        -webkit-animation-name: fade_slider;
        -webkit-animation-duration: 1.5s;
        animation-name: fade_slider;
        animation-duration: 1.5s;
    }
    .error_area .error_message {
        background: red;
        color: #fff;
        padding: 5px 10px;
        font-weight: 600;
        letter-spacing: 1px;
    }
    div#tertiary form .widget-area {
        padding: 0px 35px !important;
    }
    div#tertiary form .widget-area .widget-title h3 {
        font-size: 18px;
    }
    div#tertiary form input[type="submit"] {
        margin: 35px 0px;
        padding: 5px 35px;
    }

    .blsd_cat_list_row .col-sm-4.blsd_cat_list_column {
        padding: 0px;
        width: 30%;
        margin: 10px;
        box-shadow: 0 2px 5px 1px #ccc;
    }
    .blsd_cat_list_row .col-sm-4.blsd_cat_list_column p {
        padding: 0 10px;
    }
    .blsd_cat_list_row .col-sm-4.blsd_cat_list_column p:first-child {
        padding: 0;
    }
    form#blsd_regional_product_listing_frm .blsd_cat_list_row {
        overflow: unset;
        overflow-y: unset;
        max-height: unset;
    }


    @-webkit-keyframes fade_slider {
        from {opacity: .4}
        to {opacity: 1}
    }

    @keyframes fade_slider {
        from {opacity: .4}
        to {opacity: 1}
    }
    .slideshow-container h1 {
        font-size: 20px;
        text-align: center;
    }

    .slideshow-container .detail_slider_container.category_area input#slider_zip_code {
        width: 36%;
        margin: 0 auto;
    }


</style>
<?php
if(!isset($_REQUEST['cat'])){
?>
<?php get_header(); ?>

<script>
    jQuery(document).ready(function () {
        jQuery('.blsd_cat_link').click(function (event) {
            event.preventDefault();
            jQuery('.blsd_cat_list_column').removeClass('selectedCategory');
            jQuery(this).parents('.blsd_cat_list_column').addClass('selectedCategory');
            jQuery('.error_area').html('');
            //  plusSlidesOnIndex(1);

        });
    });
    var slideIndex = 1;
    showSlides(slideIndex);
    var haveClassSelectedCategory = false;
    var slider_zip_code = '';
    var selectedCategory = '';
// Next/previous controls
    function plusSlides(n, that) {
        jQuery('.error_area').html('');
        var prev = jQuery(that).hasClass('prev');
        var next_btn = jQuery(that).hasClass('next_btn');
        if (prev) {
            if ((slideIndex > 1)) {
                showSlides(slideIndex += n);
            }
        }
        if (next_btn) {

            haveClassSelectedCategory = jQuery('.blsd_cat_list_column').hasClass('selectedCategory');
            slider_zip_code = jQuery('#slider_zip_code').val();

            if ((slideIndex < 2 && (haveClassSelectedCategory || slideIndex == 2))) {
                showSlides(slideIndex += n);
            } else {
                if (!haveClassSelectedCategory) {

                    jQuery('.error_area').html('<div class="error_message">Please select category first.</div>');
                } else if(slider_zip_code==''){
                  jQuery('.error_area').html('<div class="error_message">Please Enter zip code first.</div>');  
                }
                else {
                selectedCategory = jQuery('.selectedCategory').attr('data-id');
                 
                    
                    var url = window.location.href;    
                    window.location.href = url+'?cat='+selectedCategory+'&blsd_regional_product_listing_zip='+slider_zip_code
                    
                }
            }
        }

    }
    var myVar
    function plusSlidesOnIndex(n) {
        clearTimeout(myVar);
        myVar = setTimeout(function () {
            showSlides(slideIndex += n);
        }, 1500);


    }

// Thumbnail image controls
    function currentSlide(n) {
        showSlides(slideIndex = n);
    }

    function showSlides(n) {
        var i;
        var slides = document.getElementsByClassName("mySlides");

        if (n > slides.length) {
            slideIndex = 1
        }
        if (n < 1) {
            slideIndex = slides.length
        }
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }

        slides[slideIndex - 1].style.display = "block";

    }


</script>
  <?php
    $options = get_option(AZEXO_FRAMEWORK);
    if ($options['show_page_title']) {
        get_template_part('template-parts/general', 'title');
    }
    ?>
<div id="primary" class="content-area">
  
    <div id="content" class="site-content" role="main">
   
            <div id="post-<?php the_ID(); ?>" <?php post_class('entry'); ?>>
                <div class="entry-content">


                    <div class="slideshow-container">

                        <!-- Full-width images with number and caption text -->
                        <div class="mySlides step1_active fade_slider">
                            <!--    <div class="numbertext">1 / 3</div>-->
                            <h1>Select Deal Category</h1>
                            <div class="detail_slider_container category_area">

                                <?php
                                echo do_shortcode('[blsd_product_categories]');
                                ?>
                            </div>
                        </div>

                        <div class="mySlides fade_slider">
                            <h1>In what location you are looking for deal.</h1>

                            <div class="detail_slider_container category_area">
                                <input type="text" name="blsd_regional_product_listing_zip" id="slider_zip_code" placeholder="Enter Zip Code">
                            </div>
                        </div>

                        <!--  <div class="mySlides fade_slider">
                            slide 3
                          </div>-->

                        <!-- Next and previous buttons -->
                        <!--  <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
                          <a class="next_btn" onclick="plusSlides(1)">&#10095;</a>-->
                        <div class="error_area">

                        </div>
                        <div class="button_area_main">
                            <button  class="prev"  onclick="plusSlides(-1, jQuery(this))">Back</button>
                            <button  class="next_btn"  onclick="plusSlides(1, jQuery(this))">Next</button>
                        </div>

                    </div>
               
       
                 
                </div><!-- .entry-content -->
            </div><!-- #post -->
          
    </div><!-- #content -->
</div><!-- #primary -->
<?php get_footer(); ?>

     <?php
}else{
    ?>
<?php get_header(); $options = get_option(AZEXO_FRAMEWORK); ?>

<div class="<?php print ((isset($options['content_fullwidth']) && $options['content_fullwidth']) ? '' : 'container'); ?> active-sidebar right">
    <div id="primary" class="content-area">
        <?php        
        if ($options['show_page_title']) {
            get_template_part('template-parts/general', 'title');
        }
        ?>
        <div id="content" class="site-content" role="main">
            <?php while (have_posts()) : the_post(); ?>
                <div id="post-<?php the_ID(); ?>" <?php post_class('entry'); ?>>
                    <div class="entry-content">
                    <?php
                    echo do_shortcode('[blsd_product_listing]');
                    ?>
                    </div><!-- .entry-content -->
                </div><!-- #post -->
                <?php
             
                ?>
            <?php endwhile; ?>
        </div><!-- #content -->
    </div><!-- #primary -->
    <?php //get_sidebar(); ?>
  <?php 
$template = BUYLOCKSMITH_DEALS_PLUGIN_DIR.'/page_template/search_sidebar.php';
require_once $template;
  ?>
</div>
<?php get_footer(); ?>

<?php } ?>
